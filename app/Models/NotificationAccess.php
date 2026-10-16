<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class NotificationAccess
{
    private static bool $tableEnsured = false;

    public static function getAllSchools(): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->query(
            'SELECT e.id AS ecole_id, e.nom_etablissement,
                    COALESCE(a.sms_enabled, 1) AS sms_enabled,
                    COALESCE(a.whatsapp_enabled, 1) AS whatsapp_enabled
             FROM ecoles e
             LEFT JOIN ecole_notification_access a ON a.ecole_id = e.id
             ORDER BY e.nom_etablissement ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEnabledChannels(int $ecoleId): array
    {
        if ($ecoleId <= 0) {
            return ['sms' => false, 'whatsapp' => false];
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT COALESCE(sms_enabled, 1) AS sms_enabled,
                    COALESCE(whatsapp_enabled, 1) AS whatsapp_enabled
             FROM ecoles e
             LEFT JOIN ecole_notification_access a ON a.ecole_id = e.id
             WHERE e.id = :ecole_id
             LIMIT 1'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
        $access = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$access) {
            return ['sms' => false, 'whatsapp' => false];
        }

        return [
            'sms' => (bool) $access['sms_enabled'],
            'whatsapp' => (bool) $access['whatsapp_enabled'],
        ];
    }

    public static function update(int $ecoleId, bool $smsEnabled, bool $whatsappEnabled, int $userId): bool
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO ecole_notification_access
                (ecole_id, sms_enabled, whatsapp_enabled, updated_by_user_id)
             SELECT id, :sms_enabled, :whatsapp_enabled, :updated_by_user_id
             FROM ecoles WHERE id = :ecole_id
             ON DUPLICATE KEY UPDATE
                sms_enabled = VALUES(sms_enabled),
                whatsapp_enabled = VALUES(whatsapp_enabled),
                updated_by_user_id = VALUES(updated_by_user_id),
                updated_at = CURRENT_TIMESTAMP'
        );

        return $stmt->execute([
            ':sms_enabled' => $smsEnabled ? 1 : 0,
            ':whatsapp_enabled' => $whatsappEnabled ? 1 : 0,
            ':updated_by_user_id' => $userId > 0 ? $userId : null,
            ':ecole_id' => $ecoleId,
        ]);
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS ecole_notification_access (
                ecole_id INT NOT NULL PRIMARY KEY,
                sms_enabled TINYINT(1) NOT NULL DEFAULT 1,
                whatsapp_enabled TINYINT(1) NOT NULL DEFAULT 1,
                updated_by_user_id INT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_ecole_notification_access_updated_by (updated_by_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
