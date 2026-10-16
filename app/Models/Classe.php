<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Classe
{
    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT c.*, s.nom_section, o.nom_option
             FROM classes c
             LEFT JOIN sections s ON c.section_id = s.id
             LEFT JOIN options o ON c.option_id = o.id
             WHERE c.ecole_id = :ecole_id
             ORDER BY c.nom_classe ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
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

    public static function findByIdForSchool(int $id, int $ecoleId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM classes WHERE id = :id AND ecole_id = :ecole_id LIMIT 1');
        $stmt->execute([':id' => $id, ':ecole_id' => $ecoleId]);
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
