<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use App\Entities\RoleFactory;

class User
{
    public static function authenticate(string $identifiant, string $motDePasse, string $portal = 'general'): ?array
    {
        self::cleanupExpiredPendingAccounts();

        $user = match ($portal) {
            'parent' => self::findByParentTelephone($identifiant)
                ?? self::findSuperAdminByIdentifiant($identifiant),
            'eleve' => self::findByEleveMatricule($identifiant)
                ?? self::findSuperAdminByIdentifiant($identifiant),
            'personnel' => self::findByIdentifiant($identifiant),
            default => null,
        };

        if ($portal === 'general') {
            $user = self::findByIdentifiant($identifiant)
                ?? self::findByEleveMatricule($identifiant)
                ?? self::findByParentTelephone($identifiant);
        }

        if (!$user) {
            return null;
        }

        if (
            $portal === 'personnel'
            && in_array($user['role'] ?? '', ['parent_ecole', 'parent_école', 'eleve_ecole', 'eleve_école'], true)
        ) {
            return null;
        }

        if (!password_verify($motDePasse, $user['mot_de_passe'])) {
            return null;
        }

        return $user;
    }

    private static function findSuperAdminByIdentifiant(string $identifiant): ?array
    {
        $user = self::findByIdentifiant($identifiant);

        return ($user['role'] ?? '') === 'super_admin' ? $user : null;
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
            . 'WHERE u.role IN (:role, :role_alias) AND e.matricule = :matricule LIMIT 1'
        );
        $stmt->execute([
            ':role' => 'eleve_ecole',
            ':role_alias' => 'eleve_école',
            ':matricule' => trim($matricule),
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function findByParentTelephone(string $telephone): ?array
    {
        $telephone = trim($telephone);
        if ($telephone === '') {
            return null;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT u.* FROM utilisateurs u '
            . 'INNER JOIN parents p ON u.reference_id = p.id AND u.ecole_id = p.ecole_id '
            . 'WHERE u.role IN (:role, :role_alias) AND p.telephone = :telephone LIMIT 2'
        );
        $stmt->execute([
            ':role' => 'parent_ecole',
            ':role_alias' => 'parent_école',
            ':telephone' => $telephone,
        ]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return count($users) === 1 ? $users[0] : null;
    }

    public static function findByIdentifiant(string $identifiant): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM utilisateurs WHERE identifiant = :identifiant LIMIT 1');
        $stmt->execute([':identifiant' => $identifiant]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    private static function assertAccountCapacity(string $role, ?int $schoolId, ?int $referenceId = null): void
    {
        if ($schoolId === null || $schoolId <= 0) {
            return;
        }

        if (in_array($role, ['parent_ecole', 'parent_école'], true)) {
            Ecole::assertSubscriptionCapacity($schoolId, 'parents');
        } elseif (in_array($role, ['eleve_ecole', 'eleve_école'], true)) {
            if ($referenceId !== null && $referenceId > 0) {
                $stmt = Database::getConnection()->prepare(
                    'SELECT 1 FROM eleves WHERE id = :reference_id AND ecole_id = :ecole_id LIMIT 1'
                );
                $stmt->execute([':reference_id' => $referenceId, ':ecole_id' => $schoolId]);
                if ($stmt->fetchColumn()) {
                    return;
                }
            }
            Ecole::assertSubscriptionCapacity($schoolId, 'students');
        } elseif (in_array($role, self::getSchoolScopedRoles(), true)) {
            Ecole::assertSubscriptionCapacity($schoolId, 'staff');
        }
    }

    public static function create(array $data): array
    {
        $db = Database::getConnection();
        $role = (string) ($data['role'] ?? '');
        if (isset($data['mot_de_passe'])) {
            $data['mot_de_passe'] = self::normalizePasswordHash((string) $data['mot_de_passe']);
        }
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

        self::assertAccountCapacity(
            $role,
            $resolvedSchoolId,
            isset($data['reference_id']) ? (int) $data['reference_id'] : null
        );

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

    public static function getDefaultSectionIdForRole(string $role): ?int
    {
        return match ($role) {
            'préfet_école', 'prefet_école', 'DE_école', 'DD_école' => Section::findIdByName('Secondaire'),
            'DP_école', 'DA_école' => Section::findIdByName('Primaire'),
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

    public static function setTemporaryPassword(int $userId, string $password): bool
    {
        if ($userId <= 0 || strlen($password) < 8 || strlen($password) > 72) {
            throw new \InvalidArgumentException('Le mot de passe temporaire est invalide.');
        }

        $stmt = Database::getConnection()->prepare(
            "UPDATE utilisateurs
             SET mot_de_passe = :password_hash, must_change_password = 1
             WHERE id = :id AND role IN ('eleve_ecole', 'eleve_école', 'parent_ecole', 'parent_école')"
        );
        $stmt->execute([
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $userId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public static function changeTemporaryPassword(int $userId, string $password): bool
    {
        if ($userId <= 0 || strlen($password) < 8 || strlen($password) > 72) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir entre 8 et 72 caractères.');
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                "SELECT mot_de_passe FROM utilisateurs
                 WHERE id = :id AND must_change_password = 1
                   AND role IN ('eleve_ecole', 'eleve_école', 'parent_ecole', 'parent_école')
                 LIMIT 1 FOR UPDATE"
            );
            $stmt->execute([':id' => $userId]);
            $currentHash = $stmt->fetchColumn();
            if (!is_string($currentHash)) {
                $db->rollBack();
                return false;
            }
            if (password_verify($password, $currentHash)) {
                throw new \DomainException('Choisissez un mot de passe différent du mot de passe temporaire.');
            }

            $update = $db->prepare(
                'UPDATE utilisateurs
                 SET mot_de_passe = :password_hash, must_change_password = 0
                 WHERE id = :id AND must_change_password = 1'
            );
            $update->execute([
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ':id' => $userId,
            ]);
            if ($update->rowCount() !== 1) {
                throw new \RuntimeException('Le changement du mot de passe n’a pas été enregistré.');
            }

            $db->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
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
        $roles = self::getReferenceRoleAliases($role);
        $rolePlaceholders = [];
        $params = [':reference_id' => $referenceId];
        foreach ($roles as $index => $roleValue) {
            $placeholder = ':role_' . $index;
            $rolePlaceholders[] = $placeholder;
            $params[$placeholder] = $roleValue;
        }
        $sql = 'SELECT * FROM utilisateurs WHERE role IN (' . implode(', ', $rolePlaceholders) . ') AND reference_id = :reference_id';

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

    private static function getReferenceRoleAliases(string $role): array
    {
        return match ($role) {
            'agent_ecole', 'agent_école' => ['agent_ecole', 'agent_école'],
            'eleve_ecole', 'eleve_école' => ['eleve_ecole', 'eleve_école'],
            'parent_ecole', 'parent_école' => ['parent_ecole', 'parent_école'],
            default => [$role],
        };
    }

    public static function createForReference(array $data): array
    {
        $db = Database::getConnection();
        self::assertAccountCapacity(
            (string) ($data['role'] ?? ''),
            isset($data['ecole_id']) ? (int) $data['ecole_id'] : null,
            isset($data['reference_id']) ? (int) $data['reference_id'] : null
        );

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

        // Hash password if it doesn't look hashed yet.
        if (isset($params[':mot_de_passe'])) {
            $params[':mot_de_passe'] = self::normalizePasswordHash((string) $params[':mot_de_passe']);
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
            'agent_école',
            'enseignant_école',
            'eleve_ecole',
            'parent_ecole',
            'comptable_école',
            'sec_école',
            'préfet_école',
            'prefet_école',
            'DE_école',
            'DD_école',
            'DP_école',
            'DA_école',
            'promoteur_école',
        ];
    }

    public static function getAgentAccountRoles(): array
    {
        return [
            'agent_école',
            'enseignant_école',
            'comptable_école',
            'sec_école',
            'prefet_école',
            'DE_école',
            'DD_école',
            'DP_école',
            'DA_école',
        ];
    }

    public static function getAgentAccountRoleAliases(): array
    {
        return array_merge(self::getAgentAccountRoles(), ['agent_ecole', 'préfet_école']);
    }

    public static function getAgentAccountRoleLabels(): array
    {
        return [
            'agent_école' => 'Agent de l’école',
            'enseignant_école' => 'Enseignant',
            'comptable_école' => 'Comptable',
            'sec_école' => 'Secrétaire',
            'prefet_école' => 'Préfet des études',
            'DE_école' => 'Directeur des études',
            'DD_école' => 'Directeur Département',
            'DP_école' => 'Directeur Principal',
            'DA_école' => 'Directeur Adjoint',
        ];
    }

    public static function configureAgentAccount(
        int $agentId,
        int $schoolId,
        string $role,
        string $identifier,
        string $password,
        ?int $existingAccountId = null
    ): array {
        if (!in_array($role, self::getAgentAccountRoles(), true)) {
            throw new \InvalidArgumentException('Le rôle sélectionné ne peut pas être attribué à un agent.');
        }

        $identifier = trim($identifier);
        if ($identifier === '' || mb_strlen($identifier, 'UTF-8') > 100) {
            throw new \InvalidArgumentException('L’identifiant est obligatoire et ne peut pas dépasser 100 caractères.');
        }
        if ($password !== '' && (strlen($password) < 8 || strlen($password) > 72)) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir entre 8 et 72 caractères.');
        }

        $db = Database::getConnection();
        $agentQuery = $db->prepare(
            'SELECT id, ecole_id, nom, postnom, prenom FROM agents WHERE id = :id AND ecole_id = :ecole_id LIMIT 1'
        );
        $agentQuery->execute([':id' => $agentId, ':ecole_id' => $schoolId]);
        $agent = $agentQuery->fetch(PDO::FETCH_ASSOC);
        if (!$agent) {
            throw new \RuntimeException('Cet agent n’appartient pas à votre école.');
        }

        $rolePlaceholders = [];
        $params = [':ecole_id' => $schoolId, ':reference_id' => $agentId];
        foreach (self::getAgentAccountRoleAliases() as $index => $accountRole) {
            $placeholder = ':role_' . $index;
            $rolePlaceholders[] = $placeholder;
            $params[$placeholder] = $accountRole;
        }
        $accountQuery = $db->prepare(
            'SELECT id FROM utilisateurs
             WHERE ecole_id = :ecole_id AND reference_id = :reference_id
               AND role IN (' . implode(',', $rolePlaceholders) . ')
             ORDER BY id ASC'
        );
        $accountQuery->execute($params);
        $accountIds = $accountQuery->fetchAll(PDO::FETCH_COLUMN);
        if (count($accountIds) > 1) {
            throw new \RuntimeException('Plusieurs comptes sont déjà liés à cet agent. Faites vérifier ces doublons avant de continuer.');
        }
        $accountId = $accountIds ? (int) $accountIds[0] : null;

        $existingAccount = null;
        if ($existingAccountId !== null && $existingAccountId > 0) {
            $existingParams = [
                ':id' => $existingAccountId,
                ':ecole_id' => $schoolId,
            ];
            $existingRolePlaceholders = [];
            foreach (self::getAgentAccountRoleAliases() as $index => $accountRole) {
                $placeholder = ':existing_role_' . $index;
                $existingRolePlaceholders[] = $placeholder;
                $existingParams[$placeholder] = $accountRole;
            }
            $existingQuery = $db->prepare(
                'SELECT id, reference_id, mot_de_passe FROM utilisateurs
                 WHERE id = :id AND ecole_id = :ecole_id
                   AND role IN (' . implode(',', $existingRolePlaceholders) . ')
                 LIMIT 1'
            );
            $existingQuery->execute($existingParams);
            $existingAccount = $existingQuery->fetch(PDO::FETCH_ASSOC);
            if (
                !$existingAccount
                || (
                    !empty($existingAccount['reference_id'])
                    && (int) $existingAccount['reference_id'] !== $agentId
                )
            ) {
                throw new \RuntimeException('Le compte sélectionné n’est pas disponible pour être rattaché à cet agent.');
            }
            if ($accountId !== null && $accountId !== $existingAccountId) {
                throw new \RuntimeException('Cet agent possède déjà un autre compte utilisateur.');
            }
            $accountId = $existingAccountId;
        }
        if ($password === '' && ($accountId === null || !$existingAccount)) {
            throw new \InvalidArgumentException('Un mot de passe initial est obligatoire pour créer un compte.');
        }

        $identifierQuery = $db->prepare(
            'SELECT id FROM utilisateurs WHERE identifiant = :identifiant AND id <> :account_id LIMIT 1'
        );
        $identifierQuery->execute([
            ':identifiant' => $identifier,
            ':account_id' => $accountId ?? 0,
        ]);
        if ($identifierQuery->fetchColumn() !== false) {
            throw new \DomainException('Cet identifiant est déjà attribué à un autre compte.');
        }

        if ($accountId === null) {
            self::assertAccountCapacity($role, $schoolId, $agentId);
        }

        $data = [
            ':nom_complet' => \App\Core\PersonName::format($agent),
            ':identifiant' => $identifier,
            ':mot_de_passe' => $password === ''
                ? (string) $existingAccount['mot_de_passe']
                : password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
            ':ecole_id' => $schoolId,
            ':reference_id' => $agentId,
            ':section_id' => self::getDefaultSectionIdForRole($role),
        ];

        if ($accountId !== null) {
            $stmt = $db->prepare(
                'UPDATE utilisateurs
                 SET nom_complet = :nom_complet, identifiant = :identifiant, mot_de_passe = :mot_de_passe,
                     role = :role, statut = \'Actif\', section_id = :section_id, reference_id = :reference_id
                 WHERE id = :id AND ecole_id = :ecole_id
                   AND (reference_id = :reference_id OR reference_id IS NULL OR reference_id = 0)'
            );
            $data[':id'] = $accountId;
        } else {
            $stmt = $db->prepare(
                'INSERT INTO utilisateurs
                    (nom_complet, identifiant, mot_de_passe, role, statut, ecole_id, reference_id, section_id, created_at)
                 VALUES
                    (:nom_complet, :identifiant, :mot_de_passe, :role, \'Actif\', :ecole_id, :reference_id, :section_id, NOW())'
            );
        }

        $stmt->execute($data);
        return self::findById($accountId ?? (int) $db->lastInsertId())
            ?? throw new \RuntimeException('Le compte a été enregistré mais ne peut pas être relu.');
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
            if ($ecoleId !== null && (int) ($existing['ecole_id'] ?? 0) !== $ecoleId) {
                self::assertAccountCapacity($role, $ecoleId, $referenceId);
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

        $identifier = trim((string) ($data['identifiant'] ?? ''));
        if ($identifier !== '' && self::findByIdentifiant($identifier) !== null) {
            $identifierLabel = in_array($role, ['eleve_ecole', 'eleve_école'], true)
                ? 'Ce matricule'
                : (in_array($role, ['parent_ecole', 'parent_école'], true)
                    ? 'Ce numéro de téléphone'
                    : 'Cet identifiant');
            throw new \DomainException(
                $identifierLabel
                . ' est déjà attribué à un autre compte. Aucun mot de passe n’a été modifié. '
                . 'Faites vérifier ce conflit d’identifiant par l’administrateur avant de renvoyer le lien.'
            );
        }

        return self::createForReference($data);
    }

    public static function syncReferenceIdentifier(
        string $role,
        int $referenceId,
        int $schoolId,
        string $identifier
    ): array {
        $identifier = trim($identifier);
        if ($referenceId <= 0 || $schoolId <= 0 || $identifier === '') {
            throw new \InvalidArgumentException('Les informations du compte lié sont incomplètes.');
        }

        $account = self::findByReference($role, $referenceId, $schoolId);
        if (!$account) {
            throw new \RuntimeException('Le compte utilisateur lié est introuvable.');
        }

        $db = Database::getConnection();
        $conflict = $db->prepare(
            'SELECT id FROM utilisateurs WHERE identifiant = :identifiant AND id <> :user_id LIMIT 1'
        );
        $conflict->execute([
            ':identifiant' => $identifier,
            ':user_id' => (int) $account['id'],
        ]);
        if ($conflict->fetchColumn() !== false) {
            throw new \DomainException('Cet identifiant est déjà attribué à un autre compte.');
        }

        if ((string) $account['identifiant'] !== $identifier) {
            $roles = self::getReferenceRoleAliases($role);
            $rolePlaceholders = [];
            $params = [
                ':identifiant' => $identifier,
                ':user_id' => (int) $account['id'],
                ':reference_id' => $referenceId,
                ':ecole_id' => $schoolId,
            ];
            foreach ($roles as $index => $roleValue) {
                $placeholder = ':role_' . $index;
                $rolePlaceholders[] = $placeholder;
                $params[$placeholder] = $roleValue;
            }
            $update = $db->prepare(
                'UPDATE utilisateurs SET identifiant = :identifiant '
                . 'WHERE id = :user_id AND role IN (' . implode(', ', $rolePlaceholders) . ') '
                . 'AND reference_id = :reference_id AND ecole_id = :ecole_id'
            );
            $update->execute($params);
        }

        return self::findById((int) $account['id']) ?: $account;
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
                $params[':mot_de_passe'] = self::normalizePasswordHash((string) $data['mot_de_passe']);
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
            error_log('User::updateProfile failed for user ID ' . $id . ': ' . $e->getMessage());
            return false;
        }
    }

    private static function normalizePasswordHash(string $password): string
    {
        $trimmed = trim($password);
        if ($trimmed === '') {
            return $trimmed;
        }

        if (preg_match('~^\$2[aby]\$\d{2}\$[./0-9A-Za-z]{53}$~', $trimmed) === 1) {
            return $trimmed;
        }

        return password_hash($trimmed, PASSWORD_DEFAULT);
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

    public static function updateAccess(
        int $id,
        string $role,
        string $statut,
        ?int $ecoleId,
        ?int $sectionId = null
    ): bool
    {
        try {
            $db = Database::getConnection();
            if ($sectionId === null) {
                $sectionId = self::getDefaultSectionIdForRole($role);
            }
            $stmt = $db->prepare(
                'UPDATE utilisateurs SET role = :role, statut = :statut, ecole_id = :ecole_id, section_id = :section_id WHERE id = :id'
            );

            return $stmt->execute([
                ':role' => $role,
                ':statut' => $statut,
                ':ecole_id' => $ecoleId,
                ':section_id' => $sectionId,
                ':id' => $id,
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getRoleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Administrateur',
            'ecole_admin' => 'Administrateur École',
            'préfet_école', 'prefet_école' => 'Préfet des études',
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
            'agent_ecole', 'agent_école' => 'Agent',
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

        $normalized = trim($identifiant);
        if ($normalized === '') {
            return false;
        }

        return match ($role) {
            'eleve_ecole' => self::hasEnrolledStudent($identifiant),
            'parent_ecole' => self::hasParentWithEnrolledChild($identifiant),
            'agent_ecole' => self::hasLinkedAgent($identifiant) || !self::existsByIdentifiant($identifiant),
            'ecole_admin' => self::hasSchoolRecord($identifiant),
            default => false,
        };
    }

    public static function getRegistrationEligibilityError(string $role, string $identifiant): string
    {
        return match ($role) {
            'eleve_ecole' => 'L’élève doit être inscrit dans une école avant de créer un compte.',
            'parent_ecole' => 'Le parent doit être lié à un élève inscrit dans une école avant de créer un compte.',
            'agent_ecole' => 'L’agent doit exister dans le système avant de créer un compte. Il sera validé ensuite par l’administrateur de l’école.',
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
