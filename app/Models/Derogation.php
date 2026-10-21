<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Derogation
{
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
        $db = Database::getConnection();
        $sql =
            'UPDATE derogations
             SET statut = :statut, commentaire = :commentaire,
                 validateur_id = :validateur_id, decided_at = NOW()
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
