<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Derogation
{
    private static bool $requesterMessageColumnReady = false;
    private static bool $recipientTablesReady = false;

    private const RECIPIENT_ROLES = ['comptable_école', 'préfet_école', 'DE_école', 'DP_école'];

    public static function getRecipientOptions(int $ecoleId): array
    {
        if ($ecoleId <= 0) {
            return [];
        }

        $rolePlaceholders = [];
        $params = [':ecole_id' => $ecoleId];
        foreach (self::RECIPIENT_ROLES as $index => $role) {
            $key = ':role_' . $index;
            $rolePlaceholders[] = $key;
            $params[$key] = $role;
        }
        $stmt = Database::getConnection()->prepare(
            'SELECT id, nom_complet, role FROM utilisateurs '
            . 'WHERE ecole_id = :ecole_id AND statut = \'Actif\' '
            . 'AND role IN (' . implode(', ', $rolePlaceholders) . ') '
            . 'ORDER BY role ASC, nom_complet ASC'
        );
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRecipientUserIds(int $ecoleId): array
    {
        if ($ecoleId <= 0) {
            return [];
        }
        self::ensureRecipientTables();

        $db = Database::getConnection();
        $configured = $db->prepare('SELECT 1 FROM ecole_derogation_recipient_settings WHERE ecole_id = :ecole_id');
        $configured->execute([':ecole_id' => $ecoleId]);
        if ($configured->fetchColumn()) {
            $stmt = $db->prepare(
                'SELECT r.utilisateur_id FROM ecole_derogation_recipients r '
                . 'INNER JOIN utilisateurs u ON u.id = r.utilisateur_id AND u.ecole_id = r.ecole_id '
                . 'WHERE r.ecole_id = :ecole_id AND u.statut = \'Actif\' '
                . 'ORDER BY r.utilisateur_id ASC'
            );
            $stmt->execute([':ecole_id' => $ecoleId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }

        $stmt = $db->prepare(
            'SELECT id FROM utilisateurs WHERE ecole_id = :ecole_id '
            . 'AND statut = \'Actif\' AND role = \'comptable_école\' ORDER BY id ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function saveRecipientUserIds(int $ecoleId, array $userIds, int $updatedBy): bool
    {
        if ($ecoleId <= 0 || $updatedBy <= 0) {
            throw new \DomainException('École ou administrateur invalide.');
        }

        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds), static fn (int $id): bool => $id > 0)));
        if ($userIds === []) {
            throw new \DomainException('Sélectionnez au moins un destinataire pour ne manquer aucune demande.');
        }

        $optionsById = [];
        foreach (self::getRecipientOptions($ecoleId) as $option) {
            $optionsById[(int) $option['id']] = true;
        }
        foreach ($userIds as $userId) {
            if (!isset($optionsById[$userId])) {
                throw new \DomainException('Un des destinataires sélectionnés n’est pas un compte actif autorisé de cette école.');
            }
        }

        self::ensureRecipientTables();
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $setting = $db->prepare(
                'INSERT INTO ecole_derogation_recipient_settings (ecole_id, updated_by_user_id) '
                . 'VALUES (:ecole_id, :updated_by) ON DUPLICATE KEY UPDATE '
                . 'updated_by_user_id = VALUES(updated_by_user_id), updated_at = CURRENT_TIMESTAMP'
            );
            $setting->execute([':ecole_id' => $ecoleId, ':updated_by' => $updatedBy]);
            $delete = $db->prepare('DELETE FROM ecole_derogation_recipients WHERE ecole_id = :ecole_id');
            $delete->execute([':ecole_id' => $ecoleId]);
            $insert = $db->prepare(
                'INSERT INTO ecole_derogation_recipients (ecole_id, utilisateur_id) '
                . 'VALUES (:ecole_id, :utilisateur_id)'
            );
            foreach ($userIds as $userId) {
                $insert->execute([':ecole_id' => $ecoleId, ':utilisateur_id' => $userId]);
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

    private static function ensureRecipientTables(): void
    {
        if (self::$recipientTablesReady) {
            return;
        }

        $db = Database::getConnection();
        $db->exec(
            'CREATE TABLE IF NOT EXISTS ecole_derogation_recipient_settings (
                ecole_id INT NOT NULL PRIMARY KEY,
                updated_by_user_id INT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_derogation_recipient_settings_updated_by (updated_by_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS ecole_derogation_recipients (
                ecole_id INT NOT NULL,
                utilisateur_id INT NOT NULL,
                PRIMARY KEY (ecole_id, utilisateur_id),
                KEY idx_derogation_recipients_user (utilisateur_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$recipientTablesReady = true;
    }

    private static function ensureRequesterMessageColumn(): void
    {
        if (self::$requesterMessageColumnReady) {
            return;
        }

        $db = Database::getConnection();
        $column = $db->query("SHOW COLUMNS FROM derogations LIKE 'demandeur_message_lu'")->fetch();
        if (!$column) {
            $db->exec(
                'ALTER TABLE derogations ADD COLUMN demandeur_message_lu TINYINT(1) NOT NULL DEFAULT 1 AFTER decided_at'
            );
        }

        self::$requesterMessageColumnReady = true;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO derogations (ecole_id, eleve_id, date_fin, motif, statut, demandeur_id)
             VALUES (:ecole_id, :eleve_id, :date_fin, :motif, :statut, :demandeur_id)'
        );

        $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':eleve_id' => $data['eleve_id'],
            ':date_fin' => $data['date_fin'],
            ':motif' => $data['motif'],
            ':statut' => 'En_attente',
            ':demandeur_id' => $data['demandeur_id'],
        ]);

        return (int) $db->lastInsertId();
    }

    public static function getAllBySchool(int $ecoleId, ?int $sectionId = null, ?int $parentId = null): array
    {
        $db = Database::getConnection();
        $sql =
            'SELECT d.*, CONCAT_WS(\' \', e.nom, e.postnom, e.prenom) AS eleve_nom,
                    e.matricule, u.nom_complet AS demandeur_nom,
                    v.nom_complet AS validateur_nom
             FROM derogations d
             INNER JOIN eleves e ON e.id = d.eleve_id
             LEFT JOIN utilisateurs u ON u.id = d.demandeur_id
             LEFT JOIN utilisateurs v ON v.id = d.validateur_id
             WHERE d.ecole_id = :ecole_id';
        $params = [':ecole_id' => $ecoleId];
        if ($parentId !== null && $parentId > 0) {
            $sql .= ' AND e.parent_id = :parent_id';
            $params[':parent_id'] = $parentId;
        }
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
                . 'WHERE i.eleve_id = d.eleve_id AND c.ecole_id = :section_school AND c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' ORDER BY d.date_fin ASC, d.created_at DESC, d.id DESC');
        $stmt->execute($params);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $financialByStudent = Exoneration::getFinancialSummaryBySchool($ecoleId, $sectionId);
        foreach ($requests as &$request) {
            $request['financial_summary'] = $financialByStudent[(int) $request['eleve_id']] ?? [];
        }
        unset($request);
        return $requests;
    }

    public static function getForStudent(int $studentId, int $schoolId): array
    {
        if ($studentId <= 0 || $schoolId <= 0) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT d.*, v.nom_complet AS validateur_nom
             FROM derogations d
             LEFT JOIN utilisateurs v ON v.id = d.validateur_id
             WHERE d.eleve_id = :eleve_id AND d.ecole_id = :ecole_id
             ORDER BY d.created_at DESC, d.id DESC'
        );
        $stmt->execute([':eleve_id' => $studentId, ':ecole_id' => $schoolId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPendingForAccountant(int $ecoleId, int $limit = 5): array
    {
        if ($ecoleId <= 0) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            "SELECT d.id, d.eleve_id, d.date_fin, d.motif, d.created_at,
                    e.nom, e.postnom, e.prenom, u.nom_complet AS demandeur_nom
             FROM derogations d
             INNER JOIN eleves e ON e.id = d.eleve_id
             LEFT JOIN utilisateurs u ON u.id = d.demandeur_id
             WHERE d.ecole_id = :ecole_id AND d.statut = 'En_attente'
             ORDER BY d.created_at DESC, d.id DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':ecole_id', $ecoleId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(20, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countPendingForAccountant(int $ecoleId): int
    {
        if ($ecoleId <= 0) {
            return 0;
        }

        $stmt = Database::getConnection()->prepare(
            "SELECT COUNT(*) FROM derogations
             WHERE ecole_id = :ecole_id AND statut = 'En_attente'"
        );
        $stmt->execute([':ecole_id' => $ecoleId]);

        return (int) $stmt->fetchColumn();
    }

    public static function getUnreadDecisionMessages(int $requesterId, int $limit = 5): array
    {
        if ($requesterId <= 0) {
            return [];
        }

        self::ensureRequesterMessageColumn();
        $stmt = Database::getConnection()->prepare(
            "SELECT d.id, d.statut, d.date_fin, d.commentaire, d.decided_at,
                    e.nom, e.postnom, e.prenom, u.nom_complet AS validateur_nom
             FROM derogations d
             INNER JOIN eleves e ON e.id = d.eleve_id
             LEFT JOIN utilisateurs u ON u.id = d.validateur_id
             WHERE d.demandeur_id = :demandeur_id
               AND d.statut IN ('Approuvee', 'Refusee') AND d.demandeur_message_lu = 0
             ORDER BY d.decided_at DESC, d.id DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':demandeur_id', $requesterId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(20, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countUnreadDecisionMessages(int $requesterId): int
    {
        if ($requesterId <= 0) {
            return 0;
        }

        self::ensureRequesterMessageColumn();
        $stmt = Database::getConnection()->prepare(
            "SELECT COUNT(*) FROM derogations
             WHERE demandeur_id = :demandeur_id
               AND statut IN ('Approuvee', 'Refusee') AND demandeur_message_lu = 0"
        );
        $stmt->execute([':demandeur_id' => $requesterId]);

        return (int) $stmt->fetchColumn();
    }

    public static function markDecisionMessageRead(int $derogationId, int $requesterId): bool
    {
        self::ensureRequesterMessageColumn();
        $stmt = Database::getConnection()->prepare(
            "UPDATE derogations SET demandeur_message_lu = 1
             WHERE id = :id AND demandeur_id = :demandeur_id
               AND statut IN ('Approuvee', 'Refusee')"
        );
        $stmt->execute([
            ':id' => $derogationId,
            ':demandeur_id' => $requesterId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function hasPendingOrActive(int $eleveId, int $ecoleId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT id FROM derogations
             WHERE eleve_id = :eleve_id AND ecole_id = :ecole_id
               AND (statut = 'En_attente' OR (statut = 'Approuvee' AND date_fin >= CURDATE()))
             LIMIT 1"
        );
        $stmt->execute([':eleve_id' => $eleveId, ':ecole_id' => $ecoleId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function decide(int $id, int $ecoleId, int $validateurId, string $status, string $commentaire, ?int $sectionId = null): bool
    {
        self::ensureRequesterMessageColumn();
        $db = Database::getConnection();
        $sql =
            'UPDATE derogations
             SET statut = :statut, commentaire = :commentaire,
                 validateur_id = :validateur_id, decided_at = NOW(), demandeur_message_lu = 0
                         WHERE id = :id AND ecole_id = :ecole_id AND statut = :pending
                             AND demandeur_id <> :demandeur_exclu';
        $params = [
            ':statut' => $status,
            ':commentaire' => $commentaire !== '' ? $commentaire : null,
            ':validateur_id' => $validateurId,
            ':demandeur_exclu' => $validateurId,
            ':id' => $id,
            ':ecole_id' => $ecoleId,
            ':pending' => 'En_attente',
        ];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
                . 'WHERE i.eleve_id = derogations.eleve_id AND c.ecole_id = :section_school AND c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }
}
