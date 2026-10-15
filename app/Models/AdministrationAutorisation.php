<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class AdministrationAutorisation
{
    private const MODULES = [
        'inscriptions' => 'Inscriptions',
        'notes' => 'Notes des élèves',
    ];

    private const ROLE_MODULES = [
        'agent_ecole' => ['inscriptions'],
        'sec_école' => ['inscriptions', 'notes'],
        'préfet_école' => ['inscriptions', 'notes'],
        'DE_école' => ['inscriptions', 'notes'],
        'DD_école' => ['inscriptions', 'notes'],
        'DP_école' => ['inscriptions', 'notes'],
        'DA_école' => ['inscriptions', 'notes'],
        'enseignant_école' => ['notes'],
    ];

    public static function ensureTable(): void
    {
        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS administration_autorisations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                role VARCHAR(50) NOT NULL,
                module VARCHAR(50) NOT NULL,
                actif TINYINT(1) NOT NULL DEFAULT 1,
                granted_by_user_id INT NULL,
                granted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_administration_autorisations_ecole_role_module (ecole_id, role, module),
                KEY idx_administration_autorisations_ecole_role (ecole_id, role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public static function allModules(): array
    {
        return self::MODULES;
    }

    public static function allRoles(): array
    {
        return [
            'agent_ecole' => 'Agent administratif',
            'sec_école' => 'Secrétaire',
            'préfet_école' => 'Préfet',
            'DE_école' => 'Directeur des études',
            'DD_école' => 'Directeur département',
            'DP_école' => 'Directeur principal',
            'DA_école' => 'Directeur adjoint',
            'enseignant_école' => 'Enseignant',
        ];
    }

    public static function getRoleModules(): array
    {
        return self::ROLE_MODULES;
    }

    public static function canAccessModule(array $user, string $module): bool
    {
        $role = (string) ($user['role'] ?? '');
        $roleModules = self::ROLE_MODULES[$role] ?? [];

        if (!in_array($module, $roleModules, true)) {
            return true;
        }

        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if ($ecoleId <= 0) {
            return false;
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT actif FROM administration_autorisations
             WHERE ecole_id = :ecole_id AND role = :role AND module = :module
             LIMIT 1'
        );
        $stmt->execute([
            ':ecole_id' => $ecoleId,
            ':role' => $role,
            ':module' => $module,
        ]);
        $active = $stmt->fetchColumn();

        return $active === false || (int) $active === 1;
    }

    public static function getAccessMatrix(int $ecoleId): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT role, module, actif FROM administration_autorisations
             WHERE ecole_id = :ecole_id'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);

        $saved = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $saved[(string) $row['role']][(string) $row['module']] = (int) $row['actif'] === 1;
        }

        $matrix = [];
        foreach (self::ROLE_MODULES as $role => $modules) {
            foreach ($modules as $module) {
                $matrix[$role][$module] = $saved[$role][$module] ?? true;
            }
        }

        return $matrix;
    }

    public static function setModuleAccess(
        int $ecoleId,
        string $role,
        string $module,
        bool $active,
        ?int $grantedByUserId = null
    ): bool {
        if (
            $ecoleId <= 0
            || !in_array($module, self::ROLE_MODULES[$role] ?? [], true)
            || !array_key_exists($module, self::MODULES)
        ) {
            return false;
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO administration_autorisations
                (ecole_id, role, module, actif, granted_by_user_id, granted_at)
             VALUES (:ecole_id, :role, :module, :actif, :granted_by_user_id, NOW())
             ON DUPLICATE KEY UPDATE
                actif = VALUES(actif),
                granted_by_user_id = VALUES(granted_by_user_id),
                granted_at = NOW()'
        );

        return $stmt->execute([
            ':ecole_id' => $ecoleId,
            ':role' => $role,
            ':module' => $module,
            ':actif' => $active ? 1 : 0,
            ':granted_by_user_id' => $grantedByUserId,
        ]);
    }
}
