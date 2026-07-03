<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class FraisScolaire
{
    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare(
                "SELECT f.*, 
                        c.nom_classe,
                        ao.nom_option AS option_scope_name,
                        ss.nom_section AS section_scope_name,
                        s.annee AS annee_scolaire,
                        CASE 
                          WHEN f.scope = 'class' THEN COALESCE(c.nom_classe, 'N/A')
                          WHEN f.scope = 'option' THEN COALESCE(ao.nom_option, 'N/A')
                          WHEN f.scope = 'section' THEN COALESCE(ss.nom_section, 'N/A')
                          WHEN f.scope = 'school' THEN 'Toutes les options'
                          ELSE 'N/A' END AS scope_label
                 FROM frais_scolaires f
                 LEFT JOIN classes c ON c.id = f.classe_id
                 LEFT JOIN options ao ON (f.scope = 'option' AND ao.id = f.scope_id)
                 LEFT JOIN sections ss ON (f.scope = 'section' AND ss.id = f.scope_id)
                 LEFT JOIN annees_scolaires s ON s.id = f.annee_scolaire_id
                 WHERE (c.ecole_id = :ecole_id OR f.scope IN ('option','section','school'))
                 ORDER BY s.annee DESC, c.nom_classe ASC, f.type_frais ASC"
            );
            $stmt->execute([':ecole_id' => $ecoleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Fallback for older schema without 'scope' column: return basic fees for school's classes
            error_log('FraisScolaire::getAllBySchool fallback due to DB error: ' . $e->getMessage());
            $stmt = $db->prepare(
                "SELECT f.*, c.nom_classe, s.annee AS annee_scolaire, 'N/A' AS scope_label
                 FROM frais_scolaires f
                 LEFT JOIN classes c ON c.id = f.classe_id
                 LEFT JOIN annees_scolaires s ON s.id = f.annee_scolaire_id
                 WHERE c.ecole_id = :ecole_id OR f.classe_id IS NULL
                 ORDER BY s.annee DESC, c.nom_classe ASC, f.type_frais ASC"
            );
            $stmt->execute([':ecole_id' => $ecoleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public static function create(array $data)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO frais_scolaires (classe_id, type_frais, montant_total, annee_scolaire_id, devise, scope, scope_id)
             VALUES (:classe_id, :type_frais, :montant_total, :annee_scolaire_id, :devise, :scope, :scope_id)'
        );

        $ok = $stmt->execute([
            ':classe_id' => $data['classe_id'] ?? null,
            ':type_frais' => $data['type_frais'],
            ':montant_total' => $data['montant_total'],
            ':annee_scolaire_id' => $data['annee_scolaire_id'],
            ':devise' => $data['devise'],
            ':scope' => $data['scope'] ?? 'class',
            ':scope_id' => $data['scope_id'] ?? null,
        ]);

        if ($ok) {
            return (int) $db->lastInsertId();
        }

        return false;
    }
}
