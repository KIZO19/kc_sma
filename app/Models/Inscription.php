<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Inscription
{
    public static function create(array $data): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO inscriptions (eleve_id, classe_id, annee_scolaire_id) VALUES (:eleve_id, :classe_id, :annee_scolaire_id)'
        );

        $stmt->execute([
            ':eleve_id' => $data['eleve_id'],
            ':classe_id' => $data['classe_id'],
            ':annee_scolaire_id' => $data['annee_scolaire_id'],
        ]);

        return self::findById((int) $db->lastInsertId());
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM inscriptions WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $inscription = $stmt->fetch(PDO::FETCH_ASSOC);

        return $inscription ?: null;
    }
}
