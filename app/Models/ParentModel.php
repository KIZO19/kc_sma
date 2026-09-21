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

    public static function getChildren(int $parentId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.id, e.matricule, e.nom, e.postnom, e.prenom, e.statut_eleve, '
            . '(SELECT c.nom_classe FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id ORDER BY i.date_inscription DESC, i.id DESC LIMIT 1) AS nom_classe '
            . 'FROM eleves e WHERE e.parent_id = :parent_id ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC'
        );
        $stmt->execute([':parent_id' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalPaidByCurrencyForChild(int $eleveId): array
    {
        $db = Database::getConnection();
        $totals = [];

        try {
            $stmt = $db->prepare(
                'SELECT COALESCE(fs.devise, "USD") AS devise, COALESCE(SUM(ece.montant), 0) AS total '
                . 'FROM ecritures_comptables_eleves ece '
                . 'INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id '
                . 'LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id '
                . 'WHERE ce.eleve_id = :eleve_id AND ece.type_mouvement = :type_mouvement '
                . 'GROUP BY COALESCE(fs.devise, "USD")'
            );
            $stmt->execute([':eleve_id' => $eleveId, ':type_mouvement' => 'CREDIT']);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $currency = strtoupper(trim($row['devise'] ?? 'USD')) ?: 'USD';
                $totals[$currency]['accounting'] = (float) ($row['total'] ?? 0);
            }
        } catch (\Throwable $e) {
            // The accounting table may be unavailable in legacy installations.
        }

        try {
            $stmt = $db->prepare(
                'SELECT COALESCE(fs.devise, "USD") AS devise, COALESCE(SUM(pe.montant_paye), 0) AS total '
                . 'FROM paiements_eleves pe LEFT JOIN frais_scolaires fs ON fs.id = pe.frais_id '
                . 'WHERE pe.eleve_id = :eleve_id GROUP BY COALESCE(fs.devise, "USD")'
            );
            $stmt->execute([':eleve_id' => $eleveId]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $currency = strtoupper(trim($row['devise'] ?? 'USD')) ?: 'USD';
                $totals[$currency]['legacy'] = (float) ($row['total'] ?? 0);
            }
        } catch (\Throwable $e) {
            // The legacy table may be unavailable in newer installations.
        }

        $result = [];
        foreach ($totals as $currency => $values) {
            $result[$currency] = max((float) ($values['accounting'] ?? 0), (float) ($values['legacy'] ?? 0));
        }
        ksort($result);
        return $result;
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

        $created = self::findById((int) $db->lastInsertId());
        if ($created) {
            try {
                self::createUserAccount((int) $created['id']);
            } catch (\Throwable $e) {
                error_log('ParentModel::create user account error: ' . $e->getMessage());
            }
        }

        return $created;
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
