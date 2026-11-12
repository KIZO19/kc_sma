<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SchoolYearChangeRequest
{
    private static bool $tableEnsured = false;

    public static function create(int $schoolId, int $requesterId, string $requestedYear): void
    {
        $requestedYear = AnneeScolaire::validateYear($requestedYear);
        if ($schoolId <= 0 || $requesterId <= 0) {
            throw new \InvalidArgumentException('Les informations de la demande sont invalides.');
        }

        self::ensureTable();
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $school = $db->prepare('SELECT id FROM ecoles WHERE id = :id FOR UPDATE');
            $school->execute([':id' => $schoolId]);
            if (!$school->fetchColumn()) {
                throw new \DomainException('École introuvable.');
            }

            $activeYear = AnneeScolaire::getActiveBySchool($schoolId);
            if ($activeYear === null) {
                throw new \DomainException('Définissez d’abord l’année scolaire d’exercice dans les paramètres de l’école.');
            }
            if ((string) $activeYear['annee'] === $requestedYear) {
                throw new \DomainException('Cette année scolaire est déjà l’année d’exercice.');
            }

            $pending = $db->prepare(
                "SELECT id FROM demandes_changement_annee_scolaire
                 WHERE ecole_id = :ecole_id AND statut = 'En_attente'
                 LIMIT 1"
            );
            $pending->execute([':ecole_id' => $schoolId]);
            if ($pending->fetchColumn()) {
                throw new \DomainException('Une demande de changement d’année scolaire est déjà en attente.');
            }

            $insert = $db->prepare(
                'INSERT INTO demandes_changement_annee_scolaire
                    (ecole_id, annee_actuelle, annee_demandee, demandeur_id)
                 VALUES (:ecole_id, :annee_actuelle, :annee_demandee, :demandeur_id)'
            );
            $insert->execute([
                ':ecole_id' => $schoolId,
                ':annee_actuelle' => (string) $activeYear['annee'],
                ':annee_demandee' => $requestedYear,
                ':demandeur_id' => $requesterId,
            ]);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function getLatestForSchool(int $schoolId): ?array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT r.*, reviewer.nom_complet AS reviewer_name
             FROM demandes_changement_annee_scolaire r
             LEFT JOIN utilisateurs reviewer ON reviewer.id = r.traite_par
             WHERE r.ecole_id = :ecole_id
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT 1'
        );
        $stmt->execute([':ecole_id' => $schoolId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function getPending(): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->query(
            "SELECT r.*, e.nom_etablissement, requester.nom_complet AS requester_name
             FROM demandes_changement_annee_scolaire r
             INNER JOIN ecoles e ON e.id = r.ecole_id
             LEFT JOIN utilisateurs requester ON requester.id = r.demandeur_id
             WHERE r.statut = 'En_attente'
             ORDER BY r.created_at ASC, r.id ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPendingForNotifications(int $limit = 10): array
    {
        self::ensureTable();
        $limit = max(1, min($limit, 50));
        $stmt = Database::getConnection()->query(
            "SELECT r.id, r.annee_actuelle, r.annee_demandee, r.created_at,
                    e.nom_etablissement, requester.nom_complet AS requester_name
             FROM demandes_changement_annee_scolaire r
             INNER JOIN ecoles e ON e.id = r.ecole_id
             LEFT JOIN utilisateurs requester ON requester.id = r.demandeur_id
             WHERE r.statut = 'En_attente'
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT {$limit}"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countPending(): int
    {
        self::ensureTable();
        $stmt = Database::getConnection()->query(
            "SELECT COUNT(*) FROM demandes_changement_annee_scolaire
             WHERE statut = 'En_attente'"
        );

        return (int) $stmt->fetchColumn();
    }

    public static function review(int $requestId, int $reviewerId, string $status, string $note): bool
    {
        if (
            $requestId <= 0
            || $reviewerId <= 0
            || !\in_array($status, ['Approuvee', 'Refusee'], true)
            || mb_strlen($note, 'UTF-8') > 1000
        ) {
            throw new \InvalidArgumentException('Les données de traitement de la demande sont invalides.');
        }

        self::ensureTable();
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $schoolIdQuery = $db->prepare(
                "SELECT ecole_id FROM demandes_changement_annee_scolaire
                 WHERE id = :id AND statut = 'En_attente'"
            );
            $schoolIdQuery->execute([':id' => $requestId]);
            $schoolId = $schoolIdQuery->fetchColumn();
            if ($schoolId === false) {
                $db->rollBack();
                return false;
            }

            $schoolLock = $db->prepare('SELECT id FROM ecoles WHERE id = :id FOR UPDATE');
            $schoolLock->execute([':id' => (int) $schoolId]);
            if (!$schoolLock->fetchColumn()) {
                $db->rollBack();
                return false;
            }

            $requestQuery = $db->prepare(
                "SELECT * FROM demandes_changement_annee_scolaire
                 WHERE id = :id AND statut = 'En_attente'
                 FOR UPDATE"
            );
            $requestQuery->execute([':id' => $requestId]);
            $request = $requestQuery->fetch(PDO::FETCH_ASSOC);
            if (!$request) {
                $db->rollBack();
                return false;
            }

            if ($status === 'Approuvee') {
                AnneeScolaire::activateForSchool(
                    $db,
                    (int) $request['ecole_id'],
                    (string) $request['annee_demandee']
                );
            }

            $update = $db->prepare(
                'UPDATE demandes_changement_annee_scolaire
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
                throw new \RuntimeException('La demande a été modifiée pendant son traitement.');
            }

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS demandes_changement_annee_scolaire (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                annee_actuelle VARCHAR(20) NOT NULL,
                annee_demandee VARCHAR(20) NOT NULL,
                demandeur_id INT NOT NULL,
                statut ENUM(\'En_attente\', \'Approuvee\', \'Refusee\') NOT NULL DEFAULT \'En_attente\',
                traite_par INT NULL,
                traite_le DATETIME NULL,
                reponse VARCHAR(1000) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_demandes_annee_ecole (ecole_id, created_at),
                KEY idx_demandes_annee_statut (statut, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
