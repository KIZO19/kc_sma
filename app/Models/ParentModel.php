<?php

namespace App\Models;

use App\Core\Database;
use App\Models\DetteEleve;
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

    public static function getChildrenFinancialSummaries(array $children): array
    {
        $summaries = [];
        $db = Database::getConnection();

        foreach ($children as $child) {
            $eleveId = (int) ($child['id'] ?? 0);
            if ($eleveId <= 0) {
                continue;
            }

            $paid = [];
            $queries = [
                'SELECT COALESCE(fs.devise, "USD") AS devise, SUM(ece.montant) AS total '
                . 'FROM ecritures_comptables_eleves ece '
                . 'INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id '
                . 'LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id '
                . 'WHERE ce.eleve_id = :eleve AND ece.type_mouvement = "CREDIT" '
                . 'GROUP BY COALESCE(fs.devise, "USD")',
                'SELECT COALESCE(fs.devise, "USD") AS devise, SUM(pe.montant_paye) AS total '
                . 'FROM paiements_eleves pe LEFT JOIN frais_scolaires fs ON fs.id = pe.frais_id '
                . 'WHERE pe.eleve_id = :eleve GROUP BY COALESCE(fs.devise, "USD")',
            ];

            foreach ($queries as $query) {
                try {
                    $stmt = $db->prepare($query);
                    $stmt->execute([':eleve' => $eleveId]);
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $devise = strtoupper(trim($row['devise'] ?? 'USD')) ?: 'USD';
                        $paid[$devise] = max($paid[$devise] ?? 0.0, (float) ($row['total'] ?? 0));
                    }
                } catch (\Throwable $e) {
                    // Legacy payment tables may not exist in every installation.
                }
            }

            $debt = DetteEleve::getTotalOutstandingGroupedByDevise($eleveId);
            if (empty($debt)) {
                $debt = DetteEleve::computeOutstandingFromApplicableFees($eleveId);
            }
            ksort($paid);
            ksort($debt);
            $summaries[$eleveId] = ['paid' => $paid, 'debt' => $debt];
        }

        return $summaries;
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
