<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use App\Entities\RoleFactory;

class User
{
    public static function authenticate(string $identifiant, string $motDePasse): ?array
    {
        $user = self::findByIdentifiant($identifiant);

        if (!$user) {
            $user = self::findByEleveMatricule($identifiant);
        }

        if (!$user) {
            return null;
        }

        if (!password_verify($motDePasse, $user['mot_de_passe'])) {
            return null;
        }

        return $user;
    }

    public static function existsByIdentifiant(string $identifiant): bool
    {
        return self::findByIdentifiant($identifiant) !== null;
    }

    public static function findByEleveMatricule(string $matricule): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT u.* FROM utilisateurs u '
            . 'INNER JOIN eleves e ON u.reference_id = e.id '
            . 'WHERE u.role = :role AND e.matricule = :matricule LIMIT 1'
        );
        $stmt->execute([':role' => 'eleve_ecole', ':matricule' => $matricule]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function findByIdentifiant(string $identifiant): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM utilisateurs WHERE identifiant = :identifiant LIMIT 1');
        $stmt->execute([':identifiant' => $identifiant]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function create(array $data): array
    {
        $db = Database::getConnection();
        // Allow optional ecole_id when creating a user
        $fields = ['nom_complet', 'identifiant', 'mot_de_passe', 'role', 'statut'];
        $placeholders = [':nom_complet', ':identifiant', ':mot_de_passe', ':role', ':statut'];
        $params = [
            ':nom_complet' => $data['nom_complet'],
            ':identifiant' => $data['identifiant'],
            ':mot_de_passe' => $data['mot_de_passe'],
            ':role' => $data['role'],
            ':statut' => $data['statut'],
        ];

        if (isset($data['ecole_id'])) {
            $fields[] = 'ecole_id';
            $placeholders[] = ':ecole_id';
            $params[':ecole_id'] = $data['ecole_id'];
        }

        $defaultSection = self::getDefaultSectionIdForRole($data['role']);
        if (isset($data['section_id'])) {
            $sectionId = $data['section_id'];
        } else {
            $sectionId = $defaultSection;
        }

        if ($sectionId !== null) {
            $fields[] = 'section_id';
            $placeholders[] = ':section_id';
            $params[':section_id'] = $sectionId;
        }

        $sql = 'INSERT INTO utilisateurs (' . implode(', ', $fields) . ', created_at) VALUES (' . implode(', ', $placeholders) . ', NOW())';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return self::findById((int) $db->lastInsertId());
    }

    private static function getDefaultSectionIdForRole(string $role): ?int
    {
        return match ($role) {
            'préfet_école', 'DE_école', 'DD_école' => 3,
            'DP_école', 'DA_école' => 2,
            default => null,
        };
    }

    public static function getAvailableEcoleAdmins(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id, nom_complet, identifiant FROM utilisateurs WHERE role = 'ecole_admin' AND (ecole_id IS NULL OR ecole_id = 0)");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM utilisateurs WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function getRoleEntityByRole(string $role)
    {
        return RoleFactory::make($role);
    }

    public static function getRoleEntityForUserById(int $id)
    {
        $user = self::findById($id);
        if (!$user) {
            return null;
        }

        return self::getRoleEntityByRole($user['role']);
    }

    public static function findByReference(string $role, int $referenceId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM utilisateurs WHERE role = :role AND reference_id = :reference_id LIMIT 1');
        $stmt->execute([':role' => $role, ':reference_id' => $referenceId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function createForReference(array $data): array
    {
        $db = Database::getConnection();

        $fields = ['nom_complet', 'identifiant', 'mot_de_passe', 'role', 'statut', 'reference_id'];
        $placeholders = [':nom_complet', ':identifiant', ':mot_de_passe', ':role', ':statut', ':reference_id'];

        $params = [
            ':nom_complet' => $data['nom_complet'] ?? $data['identifiant'],
            ':identifiant' => $data['identifiant'],
            ':mot_de_passe' => $data['mot_de_passe'],
            ':role' => $data['role'],
            ':statut' => $data['statut'] ?? 'Actif',
            ':reference_id' => $data['reference_id'],
        ];

        if (isset($data['ecole_id'])) {
            $fields[] = 'ecole_id';
            $placeholders[] = ':ecole_id';
            $params[':ecole_id'] = $data['ecole_id'];
        }

        $defaultSection = self::getDefaultSectionIdForRole($data['role']);
        if (isset($data['section_id'])) {
            $sectionId = $data['section_id'];
        } else {
            $sectionId = $defaultSection;
        }

        if ($sectionId !== null) {
            $fields[] = 'section_id';
            $placeholders[] = ':section_id';
            $params[':section_id'] = $sectionId;
        }

        // Hash password if it doesn't look hashed yet
        if (isset($params[':mot_de_passe']) && strpos($params[':mot_de_passe'], '$2y$') !== 0) {
            $params[':mot_de_passe'] = password_hash($params[':mot_de_passe'], PASSWORD_DEFAULT);
        }

        $sql = 'INSERT INTO utilisateurs (' . implode(', ', $fields) . ', created_at) VALUES (' . implode(', ', $placeholders) . ', NOW())';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return self::findById((int) $db->lastInsertId());
    }

    public static function findOrCreateForReference(array $data): array
    {
        $existing = self::findByReference($data['role'], (int) $data['reference_id']);
        if ($existing) {
            return $existing;
        }

        return self::createForReference($data);
    }

    public static function updateProfile(int $id, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => $id];

            if (isset($data['nom_complet'])) {
                $fields[] = 'nom_complet = :nom_complet';
                $params[':nom_complet'] = $data['nom_complet'];
            }
            if (isset($data['identifiant'])) {
                $fields[] = 'identifiant = :identifiant';
                $params[':identifiant'] = $data['identifiant'];
            }
            if (isset($data['mot_de_passe'])) {
                $fields[] = 'mot_de_passe = :mot_de_passe';
                $params[':mot_de_passe'] = $data['mot_de_passe'];
            }
            if (isset($data['avatar'])) {
                $fields[] = 'avatar = :avatar';
                $params[':avatar'] = $data['avatar'];
            }
            if (isset($data['statut'])) {
                $fields[] = 'statut = :statut';
                $params[':statut'] = $data['statut'];
            }

            if (empty($fields)) {
                return false;
            }

            $stmt = $db->prepare('UPDATE utilisateurs SET ' . implode(', ', $fields) . ' WHERE id = :id');
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getInactiveUsers(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM utilisateurs WHERE statut = :statut ORDER BY created_at ASC');
        $stmt->execute([':statut' => 'Inactif']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnassignedPersonalAccounts(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT * FROM utilisateurs WHERE role IN ('agent_ecole', 'parent_ecole', 'enseignant_école') "
            . 'AND (ecole_id IS NULL OR ecole_id = 0) ORDER BY role, created_at ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateStatus(int $id, string $statut): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE utilisateurs SET statut = :statut WHERE id = :id');
            return $stmt->execute([':statut' => $statut, ':id' => $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function assignToSchool(int $userId, int $ecoleId): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE utilisateurs SET ecole_id = :ecole_id WHERE id = :id');
            return $stmt->execute([':ecole_id' => $ecoleId, ':id' => $userId]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getRoleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Administrateur',
            'ecole_admin' => 'Administrateur École',
            'préfet_école' => 'Préfet des études',
            'DE_école' => 'Directeur des études',
            'DD_école' => 'Directeur Département',
            'DP_école' => 'Directeur Principal',
            'DA_école' => 'Directeur Adjoint',
            'comptable_école' => 'Comptable',
            'sec_école' => 'Secrétaire',
            'promoteur_école' => 'Promoteur',
            'enseignant_école' => 'Enseignant',
            'eleve_ecole' => 'Élève',
            'parent_ecole' => 'Parent',
            'agent_ecole' => 'Agent',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    public static function getEligibleRoles(): array
    {
        return [
            'parent_ecole' => 'Parent',
            'agent_ecole' => 'Agent',
            'ecole_admin' => 'École',
        ];
    }

    public static function isEligibleForRegistration(string $role, string $identifiant): bool
    {
        if (!in_array($role, array_keys(self::getEligibleRoles()), true)) {
            return false;
        }

        return match ($role) {
            'eleve_ecole' => self::hasEnrolledStudent($identifiant),
            'parent_ecole' => self::hasParentWithEnrolledChild($identifiant),
            'agent_ecole' => self::hasLinkedAgent($identifiant),
            'ecole_admin' => self::hasSchoolRecord($identifiant),
            default => false,
        };
    }

    public static function getRegistrationEligibilityError(string $role, string $identifiant): string
    {
        return match ($role) {
            'eleve_ecole' => 'L’élève doit être inscrit dans une école avant de créer un compte.',
            'parent_ecole' => 'Le parent doit être lié à un élève inscrit dans une école avant de créer un compte.',
            'agent_ecole' => 'L’agent doit être rattaché à une école avant de créer un compte.',
            'ecole_admin' => 'L’école doit exister dans la base avant de créer un compte.',
            default => 'Rôle invalide pour l’inscription.',
        };
    }

    private static function hasEnrolledStudent(string $identifiant): bool
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare('SELECT e.id FROM eleves e INNER JOIN ecoles c ON e.ecole_id = c.id WHERE e.matricule = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $identifiant]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $stmt = $db->prepare('SELECT id FROM eleves WHERE matricule = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $identifiant]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    private static function hasParentWithEnrolledChild(string $identifiant): bool
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare('SELECT p.id FROM parents p INNER JOIN eleves e ON p.id = e.parent_id INNER JOIN ecoles c ON e.ecole_id = c.id WHERE p.identifiant = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $identifiant]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $stmt = $db->prepare('SELECT id FROM parents WHERE identifiant = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $identifiant]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    private static function hasLinkedAgent(string $identifiant): bool
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare('SELECT a.id FROM agents a INNER JOIN ecoles c ON a.ecole_id = c.id WHERE a.identifiant = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $identifiant]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $stmt = $db->prepare('SELECT id FROM agents WHERE identifiant = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $identifiant]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    private static function hasSchoolRecord(string $identifiant): bool
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare('SELECT id FROM ecoles WHERE identifiant = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $identifiant]);
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }
}
