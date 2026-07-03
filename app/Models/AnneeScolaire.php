<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class AnneeScolaire
{
    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM annees_scolaires WHERE ecole_id = :ecole_id ORDER BY annee DESC');
        $stmt->execute([':ecole_id' => $ecoleId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM annees_scolaires WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
