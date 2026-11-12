<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Agent
{
    public static function getAll(int $ecoleId = null): array
    {
        $db = Database::getConnection();
        if ($ecoleId) {
            $stmt = $db->prepare(
                'SELECT a.*, r.titre_role AS fonction
                 FROM agents a
                 LEFT JOIN roles_administration r ON r.id = a.role_id
                 WHERE a.ecole_id = :ecole_id
                 ORDER BY a.nom, a.postnom, a.prenom'
            );
            $stmt->execute([':ecole_id' => $ecoleId]);
        } else {
            $stmt = $db->prepare(
                'SELECT a.*, r.titre_role AS fonction
                 FROM agents a
                 LEFT JOIN roles_administration r ON r.id = a.role_id
                 ORDER BY a.ecole_id, a.nom, a.postnom, a.prenom'
            );
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM agents WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);

        return $agent ?: null;
    }

    public static function getAdministrativeRoles(): array
    {
        $stmt = Database::getConnection()->query(
            'SELECT id, titre_role FROM roles_administration ORDER BY titre_role'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function suggestAdministrativeRoleId(string $accountRole, array $administrativeRoles): ?int
    {
        $roleTerms = [
            'enseignant_école' => ['enseign'],
            'préfet_école' => ['prefet'],
            'prefet_école' => ['prefet'],
            'sec_école' => ['secretaire'],
            'comptable_école' => ['comptab'],
            'DE_école' => ['directeur des etudes'],
            'DD_école' => ['directeur de discipline', 'directeur discipline'],
            'DP_école' => ['directeur principal'],
            'DA_école' => ['directeur adjoint'],
        ];
        $terms = $roleTerms[$accountRole] ?? [];
        if (!$terms) {
            return null;
        }

        foreach ($administrativeRoles as $administrativeRole) {
            $title = strtolower((string) ($administrativeRole['titre_role'] ?? ''));
            if (function_exists('iconv')) {
                $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);
                if ($transliterated !== false) {
                    $title = strtolower($transliterated);
                }
            }
            $title = str_replace("'", '', $title);
            $title = preg_replace('/[^a-z0-9]+/', ' ', $title) ?? $title;
            foreach ($terms as $term) {
                if (strpos($title, $term) !== false) {
                    return (int) $administrativeRole['id'];
                }
            }
        }

        return null;
    }

    public static function suggestAccountRole(string $function): string
    {
        $normalizedFunction = strtolower($function);
        if (function_exists('iconv')) {
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalizedFunction);
            if ($transliterated !== false) {
                $normalizedFunction = strtolower($transliterated);
            }
        }
        $normalizedFunction = str_replace("'", '', $normalizedFunction);
        $normalizedFunction = preg_replace('/[^a-z0-9]+/', ' ', $normalizedFunction) ?? $normalizedFunction;

        if (strpos($normalizedFunction, 'enseign') !== false) {
            return 'enseignant_école';
        }
        if (strpos($normalizedFunction, 'prefet') !== false) {
            return 'prefet_école';
        }
        if (strpos($normalizedFunction, 'comptab') !== false) {
            return 'comptable_école';
        }
        if (strpos($normalizedFunction, 'secretaire') !== false) {
            return 'sec_école';
        }
        if (strpos($normalizedFunction, 'directeur') !== false) {
            if (strpos($normalizedFunction, 'adjoint') !== false) {
                return 'DA_école';
            }
            if (strpos($normalizedFunction, 'principal') !== false) {
                return 'DP_école';
            }
            if (strpos($normalizedFunction, 'discipline') !== false) {
                return 'DD_école';
            }
            if (strpos($normalizedFunction, 'etude') !== false) {
                return 'DE_école';
            }
        }

        return 'agent_école';
    }

    public static function create(array $data, bool $createUserAccount = true): ?array
    {
        Ecole::assertSubscriptionCapacity((int) ($data['ecole_id'] ?? 0), 'staff');
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO agents (ecole_id, nom, postnom, prenom, telephone, email, mot_de_passe, role_id, salaire_de_base, est_enseignant_secondaire) VALUES (:ecole_id, :nom, :postnom, :prenom, :telephone, :email, :mot_de_passe, :role_id, :salaire_de_base, :est_enseignant_secondaire)'
        );

        $stmt->execute([
            ':ecole_id' => $data['ecole_id'] ?? null,
            ':nom' => $data['nom'] ?? null,
            ':postnom' => $data['postnom'] ?? null,
            ':prenom' => $data['prenom'] ?? null,
            ':telephone' => $data['telephone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':mot_de_passe' => isset($data['mot_de_passe']) ? $data['mot_de_passe'] : null,
            ':role_id' => isset($data['role_id']) ? (int) $data['role_id'] : 1,
            ':salaire_de_base' => $data['salaire_de_base'] ?? 0,
            ':est_enseignant_secondaire' => !empty($data['est_enseignant_secondaire']) ? 1 : 0,
        ]);

        $created = self::findById((int) $db->lastInsertId());
        if ($created && $createUserAccount) {
            try {
                self::createUserAccount((int) $created['id']);
            } catch (\Throwable $e) {
                error_log('Agent::create user account error: ' . $e->getMessage());
            }
        }

        return $created;
    }

    public static function createUserAccount(int $agentId): array
    {
        $agent = self::findById($agentId);
        if (!$agent) {
            throw new \RuntimeException('Agent not found');
        }

        $identifiant = $agent['email'] ?: $agent['telephone'] ?: 'agent' . $agent['id'] . '@local';
        $password = bin2hex(random_bytes(4));

        return \App\Models\User::findOrCreateForReference([
            'role' => 'agent_école',
            'reference_id' => $agent['id'],
            'ecole_id' => (int) $agent['ecole_id'],
            'identifiant' => $identifiant,
            'mot_de_passe' => $password,
            'nom_complet' => \App\Core\PersonName::format($agent),
            'statut' => 'Inactif',
        ]);
    }
}
