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
            'INSERT INTO eleves (matricule, nom, postnom, prenom, genre, lieu_naissance, nationalite, adresse, date_naissance, parent_id, nom_pere, nom_mere, province_origine, territoire, secteur, groupement, village, num_permanent, photo, statut_eleve)
             VALUES (:matricule, :nom, :postnom, :prenom, :genre, :lieu_naissance, :nationalite, :adresse, :date_naissance, :parent_id, :nom_pere, :nom_mere, :province_origine, :territoire, :secteur, :groupement, :village, :num_permanent, :photo, :statut_eleve)'
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
            ':nom_pere' => $data['nom_pere'] ?? null,
            ':nom_mere' => $data['nom_mere'] ?? null,
            ':province_origine' => $data['province_origine'] ?? null,
            ':territoire' => $data['territoire'] ?? null,
            ':secteur' => $data['secteur'] ?? null,
            ':groupement' => $data['groupement'] ?? null,
            ':village' => $data['village'] ?? null,
            ':num_permanent' => $data['num_permanent'] ?? null,
            ':photo' => $data['photo'] ?? null,
            ':statut_eleve' => $data['statut_eleve'] ?? 'inactif',
        ]);

        return self::findById((int) $db->lastInsertId());
    }

    public static function getPending(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.*, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . "WHERE e.statut_eleve = 'inactif' ORDER BY e.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE eleves SET matricule = :matricule, nom = :nom, postnom = :postnom, prenom = :prenom, genre = :genre, lieu_naissance = :lieu_naissance, nationalite = :nationalite, adresse = :adresse, date_naissance = :date_naissance, parent_id = :parent_id, nom_pere = :nom_pere, nom_mere = :nom_mere, province_origine = :province_origine, territoire = :territoire, secteur = :secteur, groupement = :groupement, village = :village, num_permanent = :num_permanent, photo = :photo WHERE id = :id'
        );

        return $stmt->execute([
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
            ':nom_pere' => $data['nom_pere'] ?? null,
            ':nom_mere' => $data['nom_mere'] ?? null,
            ':province_origine' => $data['province_origine'] ?? null,
            ':territoire' => $data['territoire'] ?? null,
            ':secteur' => $data['secteur'] ?? null,
            ':groupement' => $data['groupement'] ?? null,
            ':village' => $data['village'] ?? null,
            ':num_permanent' => $data['num_permanent'] ?? null,
            ':photo' => $data['photo'] ?? null,
            ':id' => $id,
        ]);
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
