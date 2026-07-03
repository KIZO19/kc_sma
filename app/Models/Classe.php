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

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE classes SET nom_classe = :nom_classe, section_id = :section_id, option_id = :option_id WHERE id = :id'
        );

        return $stmt->execute([
            ':nom_classe' => $data['nom_classe'],
            ':section_id' => $data['section_id'],
            ':option_id' => $data['option_id'] ?? null,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM classes WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
