<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Section
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, nom_section FROM sections ORDER BY id ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllForSchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT DISTINCT s.id, s.nom_section
             FROM sections s
             INNER JOIN classes c ON c.section_id = s.id
             WHERE c.ecole_id = :ecole_id
             ORDER BY s.nom_section ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, nom_section FROM sections WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $section = $stmt->fetch(PDO::FETCH_ASSOC);

        return $section ?: null;
    }
}
