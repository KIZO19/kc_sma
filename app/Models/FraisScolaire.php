<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class FraisScolaire
{
    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT f.*, c.nom_classe, s.annee AS annee_scolaire
             FROM frais_scolaires f
             LEFT JOIN classes c ON c.id = f.classe_id
             LEFT JOIN annees_scolaires s ON s.id = f.annee_scolaire_id
             WHERE c.ecole_id = :ecole_id
             ORDER BY s.annee DESC, c.nom_classe ASC, f.type_frais ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO frais_scolaires (classe_id, type_frais, montant_total, annee_scolaire_id)
             VALUES (:classe_id, :type_frais, :montant_total, :annee_scolaire_id)'
        );

        return $stmt->execute([
            ':classe_id' => $data['classe_id'],
            ':type_frais' => $data['type_frais'],
            ':montant_total' => $data['montant_total'],
            ':annee_scolaire_id' => $data['annee_scolaire_id'],
        ]);
    }
}
