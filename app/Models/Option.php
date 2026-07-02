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

    public static function create(array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO options (nom_option) VALUES (:nom_option)');
        return $stmt->execute([':nom_option' => $data['nom_option'] ?? '']);
    }
}
