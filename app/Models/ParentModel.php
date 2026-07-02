<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ParentModel
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, nom_responsable, telephone, email FROM parents ORDER BY nom_responsable ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM parents WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        return $parent ?: null;
    }
}
