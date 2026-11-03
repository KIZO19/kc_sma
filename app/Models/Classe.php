<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Classe
{
    public static function getAllBySchool(int $ecoleId, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT c.*, s.nom_section, o.nom_option
                FROM classes c
                LEFT JOIN sections s ON c.section_id = s.id
                LEFT JOIN options o ON c.option_id = o.id
                WHERE c.ecole_id = :ecole_id';
        $params = [':ecole_id' => $ecoleId];
        if ($sectionId !== null) {
            $sql .= ' AND c.section_id = :section_id';
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' ORDER BY c.nom_classe ASC');
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllBySchoolAndSection(int $ecoleId, int $sectionId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT c.*, s.nom_section, o.nom_option
             FROM classes c
             LEFT JOIN sections s ON c.section_id = s.id
             LEFT JOIN options o ON c.option_id = o.id
             WHERE c.ecole_id = :ecole_id AND c.section_id = :section_id
             ORDER BY c.nom_classe ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId, ':section_id' => $sectionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllBySchoolSectionAndOption(int $ecoleId, int $sectionId, int $optionId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT c.*, s.nom_section, o.nom_option
             FROM classes c
             LEFT JOIN sections s ON c.section_id = s.id
             LEFT JOIN options o ON c.option_id = o.id
             WHERE c.ecole_id = :ecole_id AND c.section_id = :section_id AND c.option_id = :option_id
             ORDER BY c.nom_classe ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId, ':section_id' => $sectionId, ':option_id' => $optionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO classes (ecole_id, nom_classe, section_id, option_id)
             VALUES (:ecole_id, :nom_classe, :section_id, :option_id)'
        );

        return $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':nom_classe' => $data['nom_classe'],
            ':section_id' => $data['section_id'],
            ':option_id' => $data['option_id'] ?? null,
        ]);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM classes WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $classe = $stmt->fetch(PDO::FETCH_ASSOC);

        return $classe ?: null;
    }

    public static function findByIdForSchool(int $id, int $ecoleId, ?int $sectionId = null): ?array
    {
        $db = Database::getConnection();
        $sql = 'SELECT * FROM classes WHERE id = :id AND ecole_id = :ecole_id';
        $params = [':id' => $id, ':ecole_id' => $ecoleId];
        if ($sectionId !== null) {
            $sql .= ' AND section_id = :section_id';
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $classe = $stmt->fetch(PDO::FETCH_ASSOC);

        return $classe ?: null;
    }

    public static function nameExists(int $ecoleId, int $sectionId, int $optionId, string $name, ?int $excludeId = null): bool
    {
        $db = Database::getConnection();
        $sql = 'SELECT 1 FROM classes
                WHERE ecole_id = :ecole_id
                  AND section_id = :section_id
                  AND (option_id = :option_id OR (option_id IS NULL AND :empty_option_id = 0))
                  AND nom_classe = :nom_classe';
        $params = [
            ':ecole_id' => $ecoleId,
            ':section_id' => $sectionId,
            ':option_id' => $optionId > 0 ? $optionId : null,
            ':empty_option_id' => $optionId,
            ':nom_classe' => $name,
        ];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public static function getUsage(int $id): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT
               (SELECT COUNT(*) FROM inscriptions WHERE classe_id = :inscription_class_id) AS inscriptions,
               (SELECT COUNT(*) FROM frais_scolaires WHERE classe_id = :fee_class_id) AS frais,
               (SELECT COUNT(*) FROM attribution_cours WHERE classe_id = :course_class_id) AS attributions'
        );
        $stmt->execute([
            ':inscription_class_id' => $id,
            ':fee_class_id' => $id,
            ':course_class_id' => $id,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['inscriptions' => 0, 'frais' => 0, 'attributions' => 0];
    }

    public static function getUsageDetails(int $id, int $limit = 10): array
    {
        $db = Database::getConnection();
        $limit = max(1, min(50, $limit));
        $queries = [
            'inscriptions' => [
                'SELECT CONCAT_WS(\' \', e.nom, e.postnom, e.prenom) AS label, a.annee AS school_year,
                        i.decision_finale AS detail
                 FROM inscriptions i
                 INNER JOIN eleves e ON e.id = i.eleve_id
                 INNER JOIN annees_scolaires a ON a.id = i.annee_scolaire_id
                 WHERE i.classe_id = :class_id
                 ORDER BY a.annee DESC, e.nom, e.postnom, e.prenom',
            ],
            'frais' => [
                'SELECT f.type_frais AS label, a.annee AS school_year,
                        CONCAT(f.montant_total, \' \', f.devise) AS detail
                 FROM frais_scolaires f
                 INNER JOIN annees_scolaires a ON a.id = f.annee_scolaire_id
                 WHERE f.classe_id = :class_id
                 ORDER BY a.annee DESC, f.type_frais',
            ],
            'attributions' => [
                'SELECT c.nom_cours AS label, a.annee AS school_year,
                        CONCAT_WS(\' \', ag.nom, ag.postnom, ag.prenom) AS detail
                 FROM attribution_cours ac
                 INNER JOIN cours c ON c.id = ac.cours_id
                 INNER JOIN agents ag ON ag.id = ac.agent_id
                 INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id
                 WHERE ac.classe_id = :class_id
                 ORDER BY a.annee DESC, c.nom_cours, ag.nom, ag.postnom',
            ],
        ];

        $details = [];
        foreach ($queries as $type => [$sql]) {
            $stmt = $db->prepare($sql . ' LIMIT :limit');
            $stmt->bindValue(':class_id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $details[$type] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $details;
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE classes
             SET nom_classe = :nom_classe, section_id = :section_id, option_id = :option_id
             WHERE id = :id AND ecole_id = :ecole_id'
        );

        return $stmt->execute([
            ':nom_classe' => $data['nom_classe'],
            ':section_id' => $data['section_id'],
            ':option_id' => $data['option_id'] ?? null,
            ':id' => $id,
            ':ecole_id' => $data['ecole_id'],
        ]);
    }

    public static function delete(int $id, int $ecoleId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM classes WHERE id = :id AND ecole_id = :ecole_id');

        return $stmt->execute([':id' => $id, ':ecole_id' => $ecoleId]);
    }
}
