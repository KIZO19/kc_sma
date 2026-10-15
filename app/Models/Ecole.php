<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Ecole
{
    public static function getAll(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT e.*, a.plan_id, CASE
                         WHEN a.id IS NULL THEN NULL
                         WHEN a.plan_id IS NULL THEN 'Essai gratuit'
                         ELSE p.nom_plan
                     END AS plan_name,
                     p.prix AS plan_prix, a.date_debut, a.date_fin, a.statut_abonnement
                 FROM ecoles e
                 LEFT JOIN abonnements_ecoles a ON a.id = (
                    SELECT id FROM abonnements_ecoles
                    WHERE ecole_id = e.id
                    ORDER BY date_debut DESC, id DESC
                    LIMIT 1
                 )
                 LEFT JOIN plans_abonnement p ON p.id = a.plan_id
                 ORDER BY e.date_creation_compte DESC"
            );
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function hasPlatformAccess(int $ecoleId): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT 1
                 FROM ecoles e
                 WHERE e.id = :ecole_id
                   AND e.statut_systeme = 'Actif'
                   AND EXISTS (
                       SELECT 1
                       FROM abonnements_ecoles a
                       WHERE a.ecole_id = e.id
                         AND a.statut_abonnement = 'Actif'
                         AND a.date_debut <= NOW()
                         AND a.date_fin >= CURRENT_DATE
                   )
                 LIMIT 1"
            );
            $stmt->execute([':ecole_id' => $ecoleId]);

            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('Ecole::hasPlatformAccess failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function getLatestSubscription(int $ecoleId): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT a.*, COALESCE(p.nom_plan, 'Essai gratuit') AS nom_plan
                 FROM abonnements_ecoles a
                 LEFT JOIN plans_abonnement p ON p.id = a.plan_id
                 WHERE a.ecole_id = :ecole_id
                 ORDER BY a.date_debut DESC, a.id DESC
                 LIMIT 1"
            );
            $stmt->execute([':ecole_id' => $ecoleId]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('Ecole::getLatestSubscription failed: ' . $e->getMessage());
            return null;
        }
    }

    public static function getPendingSchools(int $limit = 5): array
    {
        try {
            $db = Database::getConnection();
            if ($limit === 0) {
                $stmt = $db->prepare('SELECT * FROM ecoles WHERE statut_systeme = :status ORDER BY date_creation_compte DESC');
                $stmt->execute([':status' => 'En_Attente']);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // For pagination use LIMIT :limit OFFSET :offset via prepared statement
            // Default offset is 0 (handled by caller building SQL)
            $stmt = $db->prepare('SELECT * FROM ecoles WHERE statut_systeme = :status ORDER BY date_creation_compte DESC LIMIT :limit OFFSET :offset');
            $stmt->bindValue(':status', 'En_Attente', PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', 0, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function getPendingSchoolsPaged(int $limit, int $offset): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM ecoles WHERE statut_systeme = :status ORDER BY date_creation_compte DESC LIMIT :limit OFFSET :offset');
            $stmt->bindValue(':status', 'En_Attente', PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function delete(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM ecoles WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function findById(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM ecoles WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function findByMatricule(string $matricule): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM ecoles WHERE matricule = :matricule LIMIT 1');
            $stmt->execute([':matricule' => $matricule]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function countBySystemStatus(string $status): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM ecoles WHERE statut_systeme = :status');
            $stmt->execute([':status' => $status]);

            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function countSubscriptionByStatus(string $status): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM abonnements_ecoles WHERE statut_abonnement = :status');
            $stmt->execute([':status' => $status]);

            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function getPlans(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT * FROM plans_abonnement ORDER BY prix ASC');

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function savePlan(array $data, ?int $planId = null): bool
    {
        try {
            $db = Database::getConnection();
            if ($planId !== null) {
                $stmt = $db->prepare(
                    'UPDATE plans_abonnement
                     SET nom_plan = :nom_plan, duree_mois = :duree_mois, prix = :prix, description = :description
                     WHERE id = :id'
                );
                $stmt->bindValue(':id', $planId, PDO::PARAM_INT);
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO plans_abonnement (nom_plan, duree_mois, prix, description)
                     VALUES (:nom_plan, :duree_mois, :prix, :description)'
                );
            }
            $stmt->bindValue(':nom_plan', $data['nom_plan'], PDO::PARAM_STR);
            $stmt->bindValue(':duree_mois', (int) $data['duree_mois'], PDO::PARAM_INT);
            $stmt->bindValue(':prix', (float) $data['prix']);
            $description = $data['description'] ?? null;
            $stmt->bindValue(':description', $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('Ecole::savePlan failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function getSchoolPopulationCounts(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'SELECT e.id, e.nom_etablissement, COUNT(u.id) AS total_personnels '
                . 'FROM ecoles e '
                . 'LEFT JOIN utilisateurs u ON u.ecole_id = e.id '
                . 'GROUP BY e.id, e.nom_etablissement '
                . 'ORDER BY total_personnels DESC'
            );
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function updateSystemStatus(int $id, string $status): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE ecoles SET statut_systeme = :status WHERE id = :id');
            return $stmt->execute([':status' => $status, ':id' => $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function create(array $data)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'INSERT INTO ecoles (nom_etablissement, matricule, adresse, telephone_contact, email_officiel, identifiant, logo_url, statut_systeme, date_creation_compte)
                 VALUES (:nom_etablissement, :matricule, :adresse, :telephone_contact, :email_officiel, :identifiant, :logo_url, :statut_systeme, NOW())'
            );

            $ok = $stmt->execute([
                ':nom_etablissement' => $data['nom_etablissement'] ?? '',
                ':matricule' => $data['matricule'] ?? null,
                ':adresse' => $data['adresse'] ?? null,
                ':telephone_contact' => $data['telephone_contact'] ?? ($data['telephone'] ?? ''),
                ':email_officiel' => $data['email_officiel'] ?? '',
                ':identifiant' => $data['identifiant'] ?? null,
                ':logo_url' => $data['logo_url'] ?? null,
                ':statut_systeme' => $data['statut_systeme'] ?? 'En_Attente',
            ]);

            if ($ok) {
                return (int) $db->lastInsertId();
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function setAdmin(int $ecoleId, int $userId): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE ecoles SET admin_ecole_id = :user WHERE id = :id');
            return $stmt->execute([':user' => $userId, ':id' => $ecoleId]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function addSubscription(int $ecoleId, ?int $planId, string $dateDebut, string $dateFin, float $montant): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'INSERT INTO abonnements_ecoles (ecole_id, plan_id, date_debut, date_fin, statut_abonnement, montant_paye)
                 VALUES (:ecole_id, :plan_id, :date_debut, :date_fin, :statut_abonnement, :montant_paye)'
            );

            $stmt->bindValue(':ecole_id', $ecoleId, PDO::PARAM_INT);
            $stmt->bindValue(':plan_id', $planId, $planId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':date_debut', $dateDebut, PDO::PARAM_STR);
            $stmt->bindValue(':date_fin', $dateFin, PDO::PARAM_STR);
            $stmt->bindValue(':statut_abonnement', 'Actif', PDO::PARAM_STR);
            $stmt->bindValue(':montant_paye', $montant);

            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('Ecole::addSubscription failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function update(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => $id];

            $mapping = [
                'nom_etablissement' => 'nom_etablissement',
                'matricule' => 'matricule',
                'adresse' => 'adresse',
                'telephone_contact' => 'telephone_contact',
                'email_officiel' => 'email_officiel',
                'identifiant' => 'identifiant',
                'logo_url' => 'logo_url',
                'statut_systeme' => 'statut_systeme',
            ];

            foreach ($mapping as $k => $col) {
                if (array_key_exists($k, $data)) {
                    $fields[] = $col . ' = :' . $k;
                    $params[':' . $k] = $data[$k];
                }
            }

            if (empty($fields)) return false;

            $sql = 'UPDATE ecoles SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function updateDefaultCurrency(int $id, string $currency): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE ecoles SET devise_principale = :devise WHERE id = :id');
            return $stmt->execute([':devise' => strtoupper(trim($currency)), ':id' => $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
