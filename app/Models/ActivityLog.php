<?php

namespace App\Models;

use App\Core\Database;
class ActivityLog
{
    private static bool $tableEnsured = false;

    public static function record(
        int $actorId,
        string $action,
        string $targetType,
        int $targetId,
        string $title,
        string $details
    ): bool {
        try {
            self::ensureTable();
            $stmt = Database::getConnection()->prepare(
                'INSERT INTO super_admin_activities
                    (actor_user_id, action, target_type, target_id, title, details)
                 VALUES (:actor_user_id, :action, :target_type, :target_id, :title, :details)'
            );

            return $stmt->execute([
                ':actor_user_id' => $actorId > 0 ? $actorId : null,
                ':action' => $action,
                ':target_type' => $targetType,
                ':target_id' => $targetId,
                ':title' => $title,
                ':details' => $details,
            ]);
        } catch (\Throwable $e) {
            error_log('ActivityLog::record failed: ' . $e->getMessage());
            $_SESSION['activity_log_warning'] = 'Une opération a été effectuée, mais son inscription dans le journal des activités a échoué.';
            return false;
        }
    }

    public static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS super_admin_activities (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                actor_user_id INT NULL,
                action VARCHAR(50) NOT NULL,
                target_type VARCHAR(30) NOT NULL,
                target_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                details VARCHAR(1000) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_super_admin_activities_created_at (created_at),
                KEY idx_super_admin_activities_target (target_type, target_id),
                KEY idx_super_admin_activities_actor (actor_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
