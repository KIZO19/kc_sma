<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class NotificationAccess
{
    private static bool $tableEnsured = false;
    private static bool $preferencesTableEnsured = false;

    public static function getAllSchools(): array
    {
        self::ensureTable();
        self::ensurePreferencesTable();
        $stmt = Database::getConnection()->query(
            'SELECT e.id AS ecole_id, e.nom_etablissement,
                    COALESCE(a.sms_enabled, 1) AS sms_enabled,
                    COALESCE(a.whatsapp_enabled, 1) AS whatsapp_enabled,
                    COALESCE(a.direct_messages_enabled, 1) AS direct_messages_enabled,
                    COALESCE(p.sms_enabled, 1) AS school_sms_enabled,
                    COALESCE(p.whatsapp_enabled, 1) AS school_whatsapp_enabled
             FROM ecoles e
             LEFT JOIN ecole_notification_access a ON a.ecole_id = e.id
             LEFT JOIN ecole_notification_preferences p ON p.ecole_id = e.id
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
        self::ensurePreferencesTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT
                    COALESCE(a.sms_enabled, 1) AND COALESCE(p.sms_enabled, 1) AS sms_enabled,
                    COALESCE(a.whatsapp_enabled, 1) AND COALESCE(p.whatsapp_enabled, 1) AS whatsapp_enabled
             FROM ecoles e
             LEFT JOIN ecole_notification_access a ON a.ecole_id = e.id
             LEFT JOIN ecole_notification_preferences p ON p.ecole_id = e.id
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

    public static function isDirectMessagingEnabled(int $ecoleId): bool
    {
        if ($ecoleId <= 0) {
            return false;
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT COALESCE(a.direct_messages_enabled, 1)
             FROM ecoles e
             LEFT JOIN ecole_notification_access a ON a.ecole_id = e.id
             WHERE e.id = :ecole_id
             LIMIT 1'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
        $enabled = $stmt->fetchColumn();

        return $enabled !== false && (bool) $enabled;
    }

    public static function getSchoolPreferences(int $ecoleId): array
    {
        if ($ecoleId <= 0) {
            return [
                'sms_allowed' => false,
                'whatsapp_allowed' => false,
                'direct_messages_allowed' => false,
                'sms_enabled' => false,
                'whatsapp_enabled' => false,
            ];
        }

        self::ensureTable();
        self::ensurePreferencesTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT COALESCE(a.sms_enabled, 1) AS sms_allowed,
                    COALESCE(a.whatsapp_enabled, 1) AS whatsapp_allowed,
                    COALESCE(a.direct_messages_enabled, 1) AS direct_messages_allowed,
                    COALESCE(p.sms_enabled, 1) AS sms_enabled,
                    COALESCE(p.whatsapp_enabled, 1) AS whatsapp_enabled
             FROM ecoles e
             LEFT JOIN ecole_notification_access a ON a.ecole_id = e.id
             LEFT JOIN ecole_notification_preferences p ON p.ecole_id = e.id
             WHERE e.id = :ecole_id
             LIMIT 1'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$settings) {
            return [
                'sms_allowed' => false,
                'whatsapp_allowed' => false,
                'direct_messages_allowed' => false,
                'sms_enabled' => false,
                'whatsapp_enabled' => false,
            ];
        }

        return array_map(
            static fn ($value): bool => (bool) $value,
            $settings
        );
    }

    public static function updateSchoolPreferences(
        int $ecoleId,
        bool $smsEnabled,
        bool $whatsappEnabled,
        int $userId
    ): bool {
        self::ensurePreferencesTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO ecole_notification_preferences
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

    public static function update(
        int $ecoleId,
        bool $smsEnabled,
        bool $whatsappEnabled,
        bool $directMessagesEnabled,
        int $userId
    ): bool
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO ecole_notification_access
                (ecole_id, sms_enabled, whatsapp_enabled, direct_messages_enabled, updated_by_user_id)
             SELECT id, :sms_enabled, :whatsapp_enabled, :direct_messages_enabled, :updated_by_user_id
             FROM ecoles WHERE id = :ecole_id
             ON DUPLICATE KEY UPDATE
                sms_enabled = VALUES(sms_enabled),
                whatsapp_enabled = VALUES(whatsapp_enabled),
                direct_messages_enabled = VALUES(direct_messages_enabled),
                updated_by_user_id = VALUES(updated_by_user_id),
                updated_at = CURRENT_TIMESTAMP'
        );

        return $stmt->execute([
            ':sms_enabled' => $smsEnabled ? 1 : 0,
            ':whatsapp_enabled' => $whatsappEnabled ? 1 : 0,
            ':direct_messages_enabled' => $directMessagesEnabled ? 1 : 0,
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
                direct_messages_enabled TINYINT(1) NOT NULL DEFAULT 1,
                updated_by_user_id INT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_ecole_notification_access_updated_by (updated_by_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $column = Database::getConnection()->query(
            "SHOW COLUMNS FROM ecole_notification_access LIKE 'direct_messages_enabled'"
        )->fetch(PDO::FETCH_ASSOC);
        if ($column === false) {
            Database::getConnection()->exec(
                'ALTER TABLE ecole_notification_access
                 ADD COLUMN direct_messages_enabled TINYINT(1) NOT NULL DEFAULT 1
                 AFTER whatsapp_enabled'
            );
        }
        self::$tableEnsured = true;
    }

    private static function ensurePreferencesTable(): void
    {
        if (self::$preferencesTableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS ecole_notification_preferences (
                ecole_id INT NOT NULL PRIMARY KEY,
                sms_enabled TINYINT(1) NOT NULL DEFAULT 1,
                whatsapp_enabled TINYINT(1) NOT NULL DEFAULT 1,
                updated_by_user_id INT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_ecole_notification_preferences_updated_by (updated_by_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$preferencesTableEnsured = true;
    }
}
