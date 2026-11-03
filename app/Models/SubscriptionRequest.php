<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SubscriptionRequest
{
    private static bool $tableEnsured = false;

    public static function create(int $schoolId, int $userId, int $planId, string $type, string $message): bool
    {
        self::ensureTable();
        if ($type === 'renouvellement') {
            UserMessage::ensureTable();
        }
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $schoolLock = $db->prepare('SELECT id FROM ecoles WHERE id = :id FOR UPDATE');
            $schoolLock->execute([':id' => $schoolId]);
            if (!$schoolLock->fetchColumn()) {
                $db->rollBack();
                return false;
            }

            $pending = $db->prepare(
                "SELECT id FROM demandes_abonnement_ecoles
                 WHERE ecole_id = :ecole_id AND statut = 'En_attente'
                 LIMIT 1"
            );
            $pending->execute([':ecole_id' => $schoolId]);
            if ($pending->fetchColumn()) {
                $db->rollBack();
                return false;
            }

            $stmt = $db->prepare(
                'INSERT INTO demandes_abonnement_ecoles
                    (ecole_id, demandeur_id, plan_id, type_demande, message)
                 VALUES (:ecole_id, :demandeur_id, :plan_id, :type_demande, :message)'
            );
            $stmt->execute([
                ':ecole_id' => $schoolId,
                ':demandeur_id' => $userId,
                ':plan_id' => $planId,
                ':type_demande' => $type,
                ':message' => $message !== '' ? $message : null,
            ]);
            if ($type === 'renouvellement') {
                UserMessage::notifyAdministratorsOfSubscriptionRequest(
                    (int) $db->lastInsertId(),
                    $schoolId,
                    $planId,
                    $userId
                );
            }
            $db->commit();

            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('SubscriptionRequest::create failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function getForSchool(int $schoolId): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT r.*, p.nom_plan, p.prix, p.duree_mois,
                    reviewer.nom_complet AS reviewer_name
             FROM demandes_abonnement_ecoles r
             INNER JOIN plans_abonnement p ON p.id = r.plan_id
             LEFT JOIN utilisateurs reviewer ON reviewer.id = r.traite_par
             WHERE r.ecole_id = :ecole_id
             ORDER BY r.created_at DESC, r.id DESC'
        );
        $stmt->execute([':ecole_id' => $schoolId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAll(): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->query(
            'SELECT r.*, e.nom_etablissement, p.nom_plan, p.prix, p.duree_mois,
                    requester.nom_complet AS requester_name,
                    reviewer.nom_complet AS reviewer_name
             FROM demandes_abonnement_ecoles r
             INNER JOIN ecoles e ON e.id = r.ecole_id
             INNER JOIN plans_abonnement p ON p.id = r.plan_id
             LEFT JOIN utilisateurs requester ON requester.id = r.demandeur_id
             LEFT JOIN utilisateurs reviewer ON reviewer.id = r.traite_par
             ORDER BY (r.statut = \'En_attente\') DESC, r.created_at DESC, r.id DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPendingCount(): int
    {
        self::ensureTable();
        return (int) Database::getConnection()->query(
            "SELECT COUNT(*) FROM demandes_abonnement_ecoles WHERE statut = 'En_attente'"
        )->fetchColumn();
    }

    public static function review(int $requestId, int $reviewerId, string $status, string $note): bool
    {
        self::ensureTable();
        if (!in_array($status, ['Approuvee', 'Refusee'], true)) {
            return false;
        }

        UserMessage::ensureTable();
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                'SELECT r.*, p.duree_mois, p.prix, p.nom_plan, e.nom_etablissement
                 FROM demandes_abonnement_ecoles r
                 INNER JOIN plans_abonnement p ON p.id = r.plan_id
                 INNER JOIN ecoles e ON e.id = r.ecole_id
                 WHERE r.id = :id AND r.statut = \'En_attente\'
                 FOR UPDATE'
            );
            $stmt->execute([':id' => $requestId]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$request) {
                $db->rollBack();
                return false;
            }

            if ($status === 'Approuvee') {
                $today = new \DateTimeImmutable('today');
                $startDate = $today;
                $latest = $db->prepare(
                    'SELECT date_fin, statut_abonnement
                     FROM abonnements_ecoles
                     WHERE ecole_id = :ecole_id
                     ORDER BY date_debut DESC, id DESC
                     LIMIT 1'
                );
                $latest->execute([':ecole_id' => (int) $request['ecole_id']]);
                $latestSubscription = $latest->fetch(PDO::FETCH_ASSOC);
                if ($latestSubscription && $latestSubscription['statut_abonnement'] === 'Actif') {
                    $latestEnd = new \DateTimeImmutable(substr((string) $latestSubscription['date_fin'], 0, 10));
                    if ($latestEnd >= $today) {
                        $startDate = $latestEnd->modify('+1 day');
                    }
                }

                $durationMonths = (int) $request['duree_mois'];
                if ($durationMonths <= 0) {
                    throw new \RuntimeException('Invalid subscription plan duration.');
                }
                $endMonth = $startDate->modify('first day of this month')->modify('+' . $durationMonths . ' months');
                $endDay = min((int) $startDate->format('d'), (int) $endMonth->format('t'));
                $endDate = $endMonth
                    ->setDate((int) $endMonth->format('Y'), (int) $endMonth->format('m'), $endDay)
                    ->modify('-1 day');

                $subscription = $db->prepare(
                    "INSERT INTO abonnements_ecoles
                        (ecole_id, plan_id, date_debut, date_fin, statut_abonnement, montant_paye)
                     VALUES (:ecole_id, :plan_id, :date_debut, :date_fin, 'Actif', :montant_paye)"
                );
                $subscription->execute([
                    ':ecole_id' => (int) $request['ecole_id'],
                    ':plan_id' => (int) $request['plan_id'],
                    ':date_debut' => $startDate->format('Y-m-d'),
                    ':date_fin' => $endDate->format('Y-m-d'),
                    ':montant_paye' => (float) ($request['prix'] ?? 0),
                ]);
            }

            $update = $db->prepare(
                'UPDATE demandes_abonnement_ecoles
                 SET statut = :statut, traite_par = :traite_par, traite_le = NOW(), reponse = :reponse
                 WHERE id = :id AND statut = \'En_attente\''
            );
            $update->execute([
                ':statut' => $status,
                ':traite_par' => $reviewerId,
                ':reponse' => $note !== '' ? $note : null,
                ':id' => $requestId,
            ]);
            if ($update->rowCount() !== 1) {
                throw new \RuntimeException('Subscription request could not be updated.');
            }

            UserMessage::notifySchoolAdministratorsOfSubscriptionDecision($request, $status, $note);

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('SubscriptionRequest::review failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS demandes_abonnement_ecoles (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                demandeur_id INT NOT NULL,
                plan_id INT NOT NULL,
                type_demande ENUM(\'nouvel_abonnement\', \'renouvellement\') NOT NULL,
                message VARCHAR(1000) NULL,
                statut ENUM(\'En_attente\', \'Approuvee\', \'Refusee\') NOT NULL DEFAULT \'En_attente\',
                traite_par INT NULL,
                traite_le DATETIME NULL,
                reponse VARCHAR(1000) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_demandes_abonnement_ecole (ecole_id, created_at),
                KEY idx_demandes_abonnement_statut (statut, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
