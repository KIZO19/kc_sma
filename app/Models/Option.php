<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Option
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, nom_option FROM options ORDER BY nom_option ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, nom_option FROM options WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $option = $stmt->fetch(PDO::FETCH_ASSOC);

        return $option ?: null;
    }

    public static function getAllForSchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        // Return options that are linked to classes of the given school
        $stmt = $db->prepare('SELECT DISTINCT o.id, o.nom_option FROM options o JOIN classes c ON c.option_id = o.id WHERE c.ecole_id = :ecoleId ORDER BY o.nom_option ASC');
        $stmt->execute([':ecoleId' => $ecoleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function isLinkedToSchool(int $optionId, int $ecoleId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT 1 FROM classes WHERE option_id = :optionId AND ecole_id = :ecoleId LIMIT 1');
        $stmt->execute([':optionId' => $optionId, ':ecoleId' => $ecoleId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function create(array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO options (nom_option) VALUES (:nom_option)');
        return $stmt->execute([':nom_option' => $data['nom_option'] ?? '']);
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE options SET nom_option = :nom_option WHERE id = :id');
        return $stmt->execute([
            ':nom_option' => $data['nom_option'] ?? '',
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM options WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
