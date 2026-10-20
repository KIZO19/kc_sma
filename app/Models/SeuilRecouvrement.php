<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SeuilRecouvrement
{
    public static function getBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT devise, montant FROM seuils_recouvrement WHERE ecole_id = :ecole_id');
        $stmt->execute([':ecole_id' => $ecoleId]);

        $thresholds = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $thresholds[strtoupper((string) $row['devise'])] = (float) $row['montant'];
        }
        return $thresholds;
    }

    public static function save(int $ecoleId, string $devise, float $montant, int $userId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO seuils_recouvrement (ecole_id, devise, montant, updated_by)
             VALUES (:ecole_id, :devise, :montant, :updated_by)
             ON DUPLICATE KEY UPDATE montant = VALUES(montant), updated_by = VALUES(updated_by)'
        );

        return $stmt->execute([
            ':ecole_id' => $ecoleId,
            ':devise' => strtoupper(trim($devise)),
            ':montant' => $montant,
            ':updated_by' => $userId > 0 ? $userId : null,
        ]);
    }

    public static function delete(int $ecoleId, string $devise): bool
    {
        $stmt = Database::getConnection()->prepare(
            'DELETE FROM seuils_recouvrement WHERE ecole_id = :ecole_id AND devise = :devise'
        );
        return $stmt->execute([
            ':ecole_id' => $ecoleId,
            ':devise' => strtoupper(trim($devise)),
        ]);
    }
}