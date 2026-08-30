<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use App\Entities\RoleFactory;

class User
{
    public static function authenticate(string $identifiant, string $motDePasse): ?array
    {
        self::cleanupExpiredPendingAccounts();

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
        $role = (string) ($data['role'] ?? '');
        $schoolScopedRoles = ['ecole_admin', 'agent_ecole', 'enseignant_école', 'eleve_ecole', 'parent_ecole', 'comptable_école', 'sec_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'promoteur_école'];

        $resolvedSchoolId = null;
        if (in_array($role, $schoolScopedRoles, true)) {
            if (isset($data['ecole_id']) && (int) $data['ecole_id'] > 0) {
                $resolvedSchoolId = (int) $data['ecole_id'];
            } elseif ($role === 'agent_ecole' && (($data['statut'] ?? '') === 'Inactif')) {
                $resolvedSchoolId = null;
            } else {
                $resolvedSchoolId = self::resolveSchoolIdForRole($role, (string) ($data['identifiant'] ?? ''), isset($data['reference_id']) ? (int) $data['reference_id'] : null);
                if ($resolvedSchoolId <= 0) {
                    throw new \InvalidArgumentException('L’école est obligatoire pour ce rôle.');
                }
            }
        }

        $fields = ['nom_complet', 'identifiant', 'mot_de_passe', 'role', 'statut'];
        $placeholders = [':nom_complet', ':identifiant', ':mot_de_passe', ':role', ':statut'];
        $params = [
            ':nom_complet' => $data['nom_complet'],
            ':identifiant' => $data['identifiant'],
            ':mot_de_passe' => $data['mot_de_passe'],
            ':role' => $role,
            ':statut' => $data['statut'],
        ];

        if ($resolvedSchoolId !== null) {
            $fields[] = 'ecole_id';
            $placeholders[] = ':ecole_id';
            $params[':ecole_id'] = $resolvedSchoolId;
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

    public static function findByReference(string $role, int $referenceId, ?int $ecoleId = null): ?array
    {
        $db = Database::getConnection();
        $sql = 'SELECT * FROM utilisateurs WHERE role = :role AND reference_id = :reference_id';
        $params = [':role' => $role, ':reference_id' => $referenceId];

        if ($ecoleId !== null) {
            $sql .= ' AND ecole_id = :ecole_id';
            $params[':ecole_id'] = $ecoleId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
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

    public static function getSchoolScopedRoles(): array
    {
        return [
            'ecole_admin',
            'agent_ecole',
            'enseignant_école',
            'eleve_ecole',
            'parent_ecole',
            'comptable_école',
            'sec_école',
            'préfet_école',
            'DE_école',
            'DD_école',
            'DP_école',
            'DA_école',
            'promoteur_école',
        ];
    }

    public static function findOrCreateForReference(array $data): array
    {
        $role = (string) ($data['role'] ?? '');
        $referenceId = (int) ($data['reference_id'] ?? 0);
        $ecoleId = isset($data['ecole_id']) ? (int) $data['ecole_id'] : null;

        if ($referenceId <= 0) {
            throw new \InvalidArgumentException('reference_id obligatoire pour créer un compte utilisateur lié à une école.');
        }

        $existing = self::findByReference($role, $referenceId, $ecoleId);
        if ($existing) {
            if ($ecoleId !== null && !empty($existing['ecole_id']) && (int) $existing['ecole_id'] !== $ecoleId) {
                $db = Database::getConnection();
                $stmt = $db->prepare('UPDATE utilisateurs SET ecole_id = :ecole_id WHERE id = :id');
                $stmt->execute([':ecole_id' => $ecoleId, ':id' => (int) $existing['id']]);
                return self::findById((int) $existing['id']) ?: $existing;
            }
            return $existing;
        }

        if ($ecoleId === null && in_array($role, self::getSchoolScopedRoles(), true) && $role !== 'agent_ecole') {
            $fallback = self::findByReference($role, $referenceId, null);
            if ($fallback && !empty($fallback['ecole_id'])) {
                $data['ecole_id'] = (int) $fallback['ecole_id'];
                $ecoleId = (int) $fallback['ecole_id'];
            }
        }

        if ($ecoleId === null && in_array($role, self::getSchoolScopedRoles(), true) && $role !== 'agent_ecole') {
            throw new \InvalidArgumentException('ecole_id obligatoire pour créer un compte utilisateur lié à une école.');
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

    public static function getAllUsers(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM utilisateurs ORDER BY CASE WHEN statut = :active THEN 0 ELSE 1 END, role ASC, nom_complet ASC');
        $stmt->execute([':active' => 'Actif']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public static function getUsersBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM utilisateurs WHERE ecole_id = :ecole_id ORDER BY role, nom_complet ASC');
        $stmt->execute([':ecole_id' => $ecoleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getInactiveUsersBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM utilisateurs WHERE statut = :statut AND ecole_id = :ecole_id ORDER BY created_at ASC');
        $stmt->execute([':statut' => 'Inactif', ':ecole_id' => $ecoleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function cleanupExpiredPendingAccounts(): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "DELETE FROM utilisateurs WHERE role = 'agent_ecole' AND statut = 'Inactif' AND (ecole_id IS NULL OR ecole_id = 0) AND created_at < DATE_SUB(NOW(), INTERVAL 6 DAY)"
            );
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
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
            'eleve_ecole' => 'Élève',
            'ecole_admin' => 'École',
        ];
    }

    public static function resolveSchoolIdForRole(string $role, string $identifiant, ?int $referenceId = null): int
    {
        $normalized = trim($identifiant);

        if ($normalized === '' && $referenceId === null) {
            return 0;
        }

        $db = Database::getConnection();

        if ($role === 'eleve_ecole') {
            if ($referenceId > 0) {
                $stmt = $db->prepare('SELECT ecole_id FROM eleves WHERE id = :reference_id LIMIT 1');
                $stmt->execute([':reference_id' => $referenceId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($row['ecole_id'])) {
                    return (int) $row['ecole_id'];
                }
            }

            $stmt = $db->prepare('SELECT e.ecole_id FROM eleves e WHERE e.matricule = :identifiant OR e.email = :identifiant OR e.telephone = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $normalized]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($row['ecole_id']) ? (int) $row['ecole_id'] : 0;
        }

        if ($role === 'parent_ecole') {
            if ($referenceId > 0) {
                $stmt = $db->prepare('SELECT ecole_id FROM parents WHERE id = :reference_id LIMIT 1');
                $stmt->execute([':reference_id' => $referenceId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($row['ecole_id'])) {
                    return (int) $row['ecole_id'];
                }
            }

            $stmt = $db->prepare('SELECT ecole_id FROM parents WHERE email = :identifiant OR telephone = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $normalized]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($row['ecole_id']) ? (int) $row['ecole_id'] : 0;
        }

        if ($role === 'agent_ecole' || $role === 'enseignant_école') {
            if ($referenceId > 0) {
                $stmt = $db->prepare('SELECT ecole_id FROM agents WHERE id = :reference_id LIMIT 1');
                $stmt->execute([':reference_id' => $referenceId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($row['ecole_id'])) {
                    return (int) $row['ecole_id'];
                }
            }

            $stmt = $db->prepare('SELECT ecole_id FROM agents WHERE email = :identifiant OR telephone = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $normalized]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($row['ecole_id']) ? (int) $row['ecole_id'] : 0;
        }

        if ($role === 'ecole_admin') {
            $stmt = $db->prepare('SELECT id FROM ecoles WHERE identifiant = :identifiant OR email_officiel = :identifiant LIMIT 1');
            $stmt->execute([':identifiant' => $normalized]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($row['id']) ? (int) $row['id'] : 0;
        }

        return 0;
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
        $normalized = trim($identifiant);

        $stmt = $db->prepare(
            'SELECT p.id FROM parents p '
            . 'INNER JOIN eleves e ON p.id = e.parent_id '
            . 'INNER JOIN ecoles c ON e.ecole_id = c.id '
            . 'WHERE p.email = :identifiant OR p.telephone = :identifiant OR p.nom_responsable = :identifiant '
            . 'LIMIT 1'
        );
        $stmt->execute([':identifiant' => $normalized]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private static function hasLinkedAgent(string $identifiant): bool
    {
        $db = Database::getConnection();
        $normalized = trim($identifiant);

        $stmt = $db->prepare(
            'SELECT a.id FROM agents a '
            . 'INNER JOIN ecoles c ON a.ecole_id = c.id '
            . 'WHERE a.email = :identifiant OR a.telephone = :identifiant OR a.nom = :identifiant OR a.prenom = :identifiant '
            . 'LIMIT 1'
        );
        $stmt->execute([':identifiant' => $normalized]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
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
