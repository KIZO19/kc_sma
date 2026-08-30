<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PaiementAutorisation
{
    public static function allRoles(): array
    {
        return [
            'sec_école' => 'Secrétaire',
            'préfet_école' => 'Préfet',
            'DE_école' => 'Directeur des études',
            'DD_école' => 'Directeur département',
            'DP_école' => 'Directeur principal',
            'DA_école' => 'Directeur adjoint',
        ];
    }

    public static function ensureTable(): void
    {
        $db = Database::getConnection();
        $db->exec(
            'CREATE TABLE IF NOT EXISTS paiements_autorisations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                role VARCHAR(50) NOT NULL,
                frais_id INT NOT NULL,
                actif TINYINT(1) NOT NULL DEFAULT 1,
                granted_by_user_id INT NULL,
                granted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_paiements_autorisations_ecole_role_frais (ecole_id, role, frais_id),
                KEY idx_paiements_autorisations_ecole_role (ecole_id, role),
                KEY idx_paiements_autorisations_frais (frais_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public static function canUserRecordFee(array $user, ?int $fraisId): bool
    {
        self::ensureTable();

        $role = (string) ($user['role'] ?? '');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        if (in_array($role, ['super_admin', 'comptable_école'], true)) {
            return true;
        }

        if ($fraisId === null || $fraisId <= 0 || $ecoleId <= 0 || !isset(self::allRoles()[$role])) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT 1 FROM paiements_autorisations WHERE ecole_id = :ecole_id AND role = :role AND frais_id = :frais_id AND actif = 1 LIMIT 1'
        );
        $stmt->execute([
            ':ecole_id' => $ecoleId,
            ':role' => $role,
            ':frais_id' => $fraisId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public static function getAuthorizedFeeIdsForUser(array $user): array
    {
        self::ensureTable();

        $role = (string) ($user['role'] ?? '');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        if (in_array($role, ['super_admin', 'comptable_école'], true)) {
            return [];
        }

        if ($ecoleId <= 0 || !isset(self::allRoles()[$role])) {
            return [];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT frais_id FROM paiements_autorisations WHERE ecole_id = :ecole_id AND role = :role AND actif = 1 ORDER BY frais_id ASC'
        );
        $stmt->execute([
            ':ecole_id' => $ecoleId,
            ':role' => $role,
        ]);

        $ids = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $feeId) {
            $ids[] = (int) $feeId;
        }

        return $ids;
    }

    public static function getRoleAccessMatrix(int $ecoleId): array
    {
        self::ensureTable();

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT role, frais_id FROM paiements_autorisations WHERE ecole_id = :ecole_id AND actif = 1 ORDER BY role, frais_id'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);

        $matrix = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $role = (string) ($row['role'] ?? '');
            $feeId = (int) ($row['frais_id'] ?? 0);
            if ($role !== '' && $feeId > 0) {
                $matrix[$role][] = $feeId;
            }
        }

        return $matrix;
    }

    public static function setRoleAccess(int $ecoleId, string $role, int $fraisId, bool $active, ?int $grantedByUserId = null): bool
    {
        self::ensureTable();

        if ($ecoleId <= 0 || $fraisId <= 0 || !isset(self::allRoles()[$role])) {
            return false;
        }

        $db = Database::getConnection();

        if ($active) {
            $stmt = $db->prepare(
                'INSERT INTO paiements_autorisations (ecole_id, role, frais_id, actif, granted_by_user_id, granted_at) VALUES (:ecole_id, :role, :frais_id, 1, :granted_by_user_id, NOW()) ON DUPLICATE KEY UPDATE actif = 1, granted_by_user_id = VALUES(granted_by_user_id), granted_at = NOW()'
            );
            $stmt->execute([
                ':ecole_id' => $ecoleId,
                ':role' => $role,
                ':frais_id' => $fraisId,
                ':granted_by_user_id' => $grantedByUserId,
            ]);
            return true;
        }

        $stmt = $db->prepare(
            'DELETE FROM paiements_autorisations WHERE ecole_id = :ecole_id AND role = :role AND frais_id = :frais_id'
        );
        $stmt->execute([
            ':ecole_id' => $ecoleId,
            ':role' => $role,
            ':frais_id' => $fraisId,
        ]);

        return true;
    }
}
