<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class DetteEleve
{
    public static function create(int $eleveId, int $fraisId, int $anneeScolaireId, float $montant, string $devise): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO dettes_eleves (eleve_id, frais_id, annee_scolaire_id, montant_initial, montant_restant, devise)
             VALUES (:eleve_id, :frais_id, :annee_scolaire_id, :montant_initial, :montant_restant, :devise)'
        );

        return (bool) $stmt->execute([
            ':eleve_id' => $eleveId,
            ':frais_id' => $fraisId,
            ':annee_scolaire_id' => $anneeScolaireId,
            ':montant_initial' => $montant,
            ':montant_restant' => $montant,
            ':devise' => $devise,
        ]);
    }

    public static function getOutstandingByEleve(int $eleveId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT d.*, fs.type_frais, fs.montant_total, fs.devise, fs.scope, fs.scope_id,
                    c.nom_classe,
                    ao.nom_option AS option_scope_name,
                    ss.nom_section AS section_scope_name,
                    s.annee AS annee_scolaire,
                    CASE
                      WHEN fs.scope = 'class' THEN COALESCE(c.nom_classe, 'N/A')
                      WHEN fs.scope = 'option' THEN COALESCE(ao.nom_option, 'N/A')
                      WHEN fs.scope = 'section' THEN COALESCE(ss.nom_section, 'N/A')
                      WHEN fs.scope = 'school' THEN 'École entière'
                      ELSE 'N/A'
                    END AS scope_label
             FROM dettes_eleves d
             INNER JOIN frais_scolaires fs ON fs.id = d.frais_id
             LEFT JOIN classes c ON c.id = fs.classe_id
             LEFT JOIN options ao ON (fs.scope = 'option' AND ao.id = fs.scope_id)
             LEFT JOIN sections ss ON (fs.scope = 'section' AND ss.id = fs.scope_id)
             LEFT JOIN annees_scolaires s ON s.id = d.annee_scolaire_id
             WHERE d.eleve_id = :eleve_id AND d.montant_restant > 0
             ORDER BY d.date_creation DESC"
        );
        $stmt->execute([':eleve_id' => $eleveId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalOutstandingByEleve(int $eleveId): float
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT SUM(montant_restant) AS total_outstanding FROM dettes_eleves WHERE eleve_id = :eleve_id');
        $stmt->execute([':eleve_id' => $eleveId]);
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public static function findByEleveAndFrais(int $eleveId, int $fraisId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM dettes_eleves WHERE eleve_id = :eleve_id AND frais_id = :frais_id LIMIT 1');
        $stmt->execute([':eleve_id' => $eleveId, ':frais_id' => $fraisId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function reduceRemaining(int $detteId, float $montant): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE dettes_eleves SET montant_restant = GREATEST(0, montant_restant - :montant) WHERE id = :id');
        return (bool) $stmt->execute([':montant' => $montant, ':id' => $detteId]);
    }
}
