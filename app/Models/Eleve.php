<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Eleve
{
    public static function create(array $data): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO eleves (matricule, nom, postnom, prenom, genre, lieu_naissance, nationalite, adresse, date_naissance, parent_id, statut_eleve)
             VALUES (:matricule, :nom, :postnom, :prenom, :genre, :lieu_naissance, :nationalite, :adresse, :date_naissance, :parent_id, :statut_eleve)'
        );

        $stmt->execute([
            ':matricule' => $data['matricule'] ?? null,
            ':nom' => $data['nom'],
            ':postnom' => $data['postnom'],
            ':prenom' => $data['prenom'] ?? null,
            ':genre' => $data['genre'],
            ':lieu_naissance' => $data['lieu_naissance'] ?? null,
            ':nationalite' => $data['nationalite'] ?? 'CONGOLAISE',
            ':adresse' => $data['adresse'] ?? null,
            ':date_naissance' => $data['date_naissance'],
            ':parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
            ':statut_eleve' => $data['statut_eleve'] ?? 'inactif',
        ]);

        return self::findById((int) $db->lastInsertId());
    }

    public static function getPending(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM eleves WHERE statut_eleve = 'inactif' ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countPending(): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) AS total FROM eleves WHERE statut_eleve = 'inactif'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    public static function approve(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE eleves SET statut_eleve = :statut WHERE id = :id');
        return $stmt->execute([':statut' => 'actif', ':id' => $id]);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM eleves WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

        return $eleve ?: null;
    }
}
