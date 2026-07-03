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

    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, nom_responsable, telephone, email FROM parents WHERE ecole_id = :ecole_id ORDER BY nom_responsable ASC');
        $stmt->execute([':ecole_id' => $ecoleId]);
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

    public static function findByIdAndSchool(int $id, int $ecoleId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM parents WHERE id = :id AND ecole_id = :ecole_id LIMIT 1');
        $stmt->execute([':id' => $id, ':ecole_id' => $ecoleId]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        return $parent ?: null;
    }

    public static function create(array $data): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO parents (ecole_id, nom_responsable, telephone, email, mot_de_passe) VALUES (:ecole_id, :nom_responsable, :telephone, :email, :mot_de_passe)'
        );

        $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':nom_responsable' => $data['nom_responsable'],
            ':telephone' => $data['telephone'],
            ':email' => $data['email'] ?? null,
            ':mot_de_passe' => $data['mot_de_passe'],
        ]);

        return self::findById((int) $db->lastInsertId());
    }

    public static function createUserAccount(int $parentId): array
    {
        $parent = self::findById($parentId);
        if (!$parent) {
            throw new \RuntimeException('Parent not found');
        }
        $ecoleId = $parent['ecole_id'] ?? null;
        if (empty($ecoleId)) {
            throw new \RuntimeException('Parent ' . $parent['id'] . ' has no ecole_id');
        }

        $identifiant = $parent['email'] ?: $parent['telephone'] ?: 'parent' . $parent['id'] . '@local';
        $password = bin2hex(random_bytes(4));

        return \App\Models\User::findOrCreateForReference([
            'role' => 'parent_ecole',
            'reference_id' => $parent['id'],
            'ecole_id' => $ecoleId,
            'identifiant' => $identifiant,
            'mot_de_passe' => $password,
            'nom_complet' => $parent['nom_responsable'] ?? $identifiant,
        ]);
    }
}
