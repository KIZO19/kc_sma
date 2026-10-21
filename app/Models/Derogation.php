<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Derogation
{
    private static bool $requesterMessageColumnReady = false;

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

    public static function create(array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO derogations (ecole_id, eleve_id, date_fin, motif, statut, demandeur_id)
             VALUES (:ecole_id, :eleve_id, :date_fin, :motif, :statut, :demandeur_id)'
        );

        return (bool) $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':eleve_id' => $data['eleve_id'],
            ':date_fin' => $data['date_fin'],
            ':motif' => $data['motif'],
            ':statut' => 'En_attente',
            ':demandeur_id' => $data['demandeur_id'],
        ]);
    }

    public static function getAllBySchool(int $ecoleId, ?int $sectionId = null): array
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
