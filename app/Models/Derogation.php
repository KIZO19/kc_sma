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

    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT d.*, CONCAT_WS(\' \', e.nom, e.postnom, e.prenom) AS eleve_nom,
                    e.matricule, u.nom_complet AS demandeur_nom,
                    v.nom_complet AS validateur_nom
             FROM derogations d
             INNER JOIN eleves e ON e.id = d.eleve_id
             LEFT JOIN utilisateurs u ON u.id = d.demandeur_id
             LEFT JOIN utilisateurs v ON v.id = d.validateur_id
             WHERE d.ecole_id = :ecole_id
             ORDER BY d.date_fin ASC, d.created_at DESC, d.id DESC'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public static function decide(int $id, int $ecoleId, int $validateurId, string $status, string $commentaire): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE derogations
             SET statut = :statut, commentaire = :commentaire,
                 validateur_id = :validateur_id, decided_at = NOW()
             WHERE id = :id AND ecole_id = :ecole_id AND statut = :pending'
        );
        return $stmt->execute([
            ':statut' => $status,
            ':commentaire' => $commentaire !== '' ? $commentaire : null,
            ':validateur_id' => $validateurId,
            ':id' => $id,
            ':ecole_id' => $ecoleId,
            ':pending' => 'En_attente',
        ]);
    }
}
