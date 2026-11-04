<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class EvenementNotification
{
    private static bool $tableEnsured = false;

    public static function deliverDueForUser(array $user): void
    {
        $userId = (int) ($user['id'] ?? 0);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $role = (string) ($user['role'] ?? '');
        if ($userId <= 0 || $schoolId <= 0 || $role === '') {
            return;
        }

        self::ensureTable();
        Evenement::ensureAudienceRolesColumn();
        $stmt = Database::getConnection()->prepare(
            'SELECT id, audience_roles
             FROM evenements
             WHERE ecole_id = :ecole_id AND date_debut <= NOW()
               AND audience_roles IS NOT NULL AND audience_roles <> \'\''
        );
        $stmt->execute([':ecole_id' => $schoolId]);
        $insert = Database::getConnection()->prepare(
            'INSERT IGNORE INTO notifications_evenements (utilisateur_id, evenement_id)
             VALUES (:utilisateur_id, :evenement_id)'
        );

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $event) {
            $audienceRoles = json_decode((string) $event['audience_roles'], true);
            if (!is_array($audienceRoles) || !in_array($role, $audienceRoles, true)) {
                continue;
            }

            $insert->execute([
                ':utilisateur_id' => $userId,
                ':evenement_id' => (int) $event['id'],
            ]);
            if ($insert->rowCount() > 0) {
                $notificationId = (int) Database::getConnection()->lastInsertId();
                PushNotification::sendToUser(
                    $userId,
                    (string) ($event['titre'] ?? 'Nouvel événement'),
                    'Un événement commence maintenant.',
                    BASE_URL . '/evenements/open-notification?id=' . $notificationId,
                    'kc-sma-event-' . $notificationId
                );
            }
        }
    }

    public static function getForUser(int $userId, int $limit = 5): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT n.id, n.is_read, n.created_at, e.id AS evenement_id, e.titre, e.date_debut, e.lieu
             FROM notifications_evenements n
             INNER JOIN evenements e ON e.id = n.evenement_id
             WHERE n.utilisateur_id = :utilisateur_id
             ORDER BY n.created_at DESC, n.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':utilisateur_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(20, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function markReadAndGetEventId(int $notificationId, int $userId): ?int
    {
        self::ensureTable();
        $db = Database::getConnection();
        $markRead = $db->prepare(
            'UPDATE notifications_evenements SET is_read = 1
             WHERE id = :id AND utilisateur_id = :utilisateur_id'
        );
        $markRead->execute([
            ':id' => $notificationId,
            ':utilisateur_id' => $userId,
        ]);

        $findEvent = $db->prepare(
            'SELECT evenement_id FROM notifications_evenements
             WHERE id = :id AND utilisateur_id = :utilisateur_id LIMIT 1'
        );
        $findEvent->execute([
            ':id' => $notificationId,
            ':utilisateur_id' => $userId,
        ]);
        $eventId = $findEvent->fetchColumn();

        return $eventId === false ? null : (int) $eventId;
    }

    public static function countUnreadForUser(int $userId): int
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) FROM notifications_evenements
             WHERE utilisateur_id = :utilisateur_id AND is_read = 0'
        );
        $stmt->execute([':utilisateur_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS notifications_evenements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                utilisateur_id INT NOT NULL,
                evenement_id INT NOT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_notification_evenement_utilisateur (utilisateur_id, evenement_id),
                KEY idx_notification_evenement_utilisateur_date (utilisateur_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $readColumn = Database::getConnection()->query(
            "SHOW COLUMNS FROM notifications_evenements LIKE 'is_read'"
        )->fetch();
        if (!$readColumn) {
            Database::getConnection()->exec(
                'ALTER TABLE notifications_evenements ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER evenement_id'
            );
        }
        self::$tableEnsured = true;
    }
}
