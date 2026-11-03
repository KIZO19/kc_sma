<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class UserPresence
{
    private static bool $tableEnsured = false;

    public static function recordLogin(array $user): void
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO utilisateur_presence (user_id, ecole_id, last_login_at, last_seen_at)
             VALUES (:user_id, :ecole_id, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                ecole_id = VALUES(ecole_id),
                last_login_at = NOW(),
                last_seen_at = NOW()'
        );
        $stmt->execute(self::userParams($user));
    }

    public static function recordDashboardAccess(array $user): void
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO utilisateur_presence (user_id, ecole_id, dashboard_accessed_at, last_seen_at)
             VALUES (:user_id, :ecole_id, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                ecole_id = VALUES(ecole_id),
                dashboard_accessed_at = NOW(),
                last_seen_at = NOW()'
        );
        $stmt->execute(self::userParams($user));
    }

    public static function recordHeartbeat(array $user): void
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO utilisateur_presence (user_id, ecole_id, last_seen_at)
             VALUES (:user_id, :ecole_id, NOW())
             ON DUPLICATE KEY UPDATE
                ecole_id = VALUES(ecole_id),
                last_seen_at = NOW()'
        );
        $stmt->execute(self::userParams($user));
    }

    public static function recordPageVisit(array $user, string $path, string $title): void
    {
        self::ensureTable();
        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        if ($basePath !== '' && strpos($path, $basePath . '/') === 0) {
            $path = substr($path, strlen($basePath));
        }
        $path = substr(preg_replace('/[\x00-\x1F\x7F]/', '', $path) ?? '/', 0, 255);
        $title = trim(substr(preg_replace('/[\x00-\x1F\x7F]/', ' ', $title) ?? '', 0, 180));

        $stmt = Database::getConnection()->prepare(
            'INSERT INTO utilisateur_page_visits (user_id, ecole_id, page_path, page_title)
             VALUES (:user_id, :ecole_id, :page_path, :page_title)'
        );
        $stmt->execute([
            ':user_id' => (int) ($user['id'] ?? 0),
            ':ecole_id' => !empty($user['ecole_id']) ? (int) $user['ecole_id'] : null,
            ':page_path' => $path,
            ':page_title' => $title !== '' ? $title : $path,
        ]);
    }

    public static function recordLocation(array $user, float $latitude, float $longitude, float $accuracy): void
    {
        self::ensureTable();
        $db = Database::getConnection();
        $location = [
            ':user_id' => (int) ($user['id'] ?? 0),
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':accuracy' => $accuracy,
        ];

        $db->beginTransaction();
        try {
            $historyStmt = $db->prepare(
                'INSERT INTO utilisateur_location_history
                    (user_id, latitude, longitude, accuracy_meters, shared_at)
                 VALUES (:user_id, :latitude, :longitude, :accuracy, NOW())'
            );
            $historyStmt->execute($location);

            $latestStmt = $db->prepare(
                'INSERT INTO utilisateur_locations (user_id, latitude, longitude, accuracy_meters, shared_at)
                 VALUES (:user_id, :latitude, :longitude, :accuracy, NOW())
                 ON DUPLICATE KEY UPDATE
                    latitude = VALUES(latitude),
                    longitude = VALUES(longitude),
                    accuracy_meters = VALUES(accuracy_meters),
                    shared_at = NOW()'
            );
            $latestStmt->execute($location);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function getUserHistory(int $userId): ?array
    {
        self::ensureTable();
        $db = Database::getConnection();

        $userStmt = $db->prepare(
            'SELECT u.id, u.nom_complet, u.role, e.nom_etablissement AS school_name,
                    p.last_login_at, p.dashboard_accessed_at
             FROM utilisateurs u
             LEFT JOIN ecoles e ON e.id = u.ecole_id
             LEFT JOIN utilisateur_presence p ON p.user_id = u.id
             WHERE u.id = :user_id
             LIMIT 1'
        );
        $userStmt->execute([':user_id' => $userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return null;
        }

        $visitsStmt = $db->prepare(
            'SELECT page_path, page_title, visited_at
             FROM utilisateur_page_visits
             WHERE user_id = :user_id
             ORDER BY visited_at DESC, id DESC
             LIMIT 500'
        );
        $visitsStmt->execute([':user_id' => $userId]);

        $locationsStmt = $db->prepare(
            'SELECT latitude, longitude, accuracy_meters, shared_at
             FROM utilisateur_location_history
             WHERE user_id = :user_id
             ORDER BY shared_at DESC, id DESC
             LIMIT 500'
        );
        $locationsStmt->execute([':user_id' => $userId]);

        return [
            'user' => $user,
            'visits' => $visitsStmt->fetchAll(PDO::FETCH_ASSOC),
            'locations' => $locationsStmt->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public static function getOverview(int $onlineWindowMinutes = 5): array
    {
        self::ensureTable();
        $db = Database::getConnection();
        $onlineWindowMinutes = max(1, $onlineWindowMinutes);
        $window = $db->quote($onlineWindowMinutes);

        $schoolsStmt = $db->query(
            'SELECT e.id, e.nom_etablissement, e.logo_url, COUNT(CASE
                    WHEN p.last_seen_at >= DATE_SUB(NOW(), INTERVAL ' . $window . ' MINUTE) THEN p.user_id
                 END) AS connected_users,
                 MAX(p.last_seen_at) AS last_seen_at
             FROM ecoles e
             LEFT JOIN utilisateurs u ON u.ecole_id = e.id
             LEFT JOIN utilisateur_presence p ON p.user_id = u.id AND p.dashboard_accessed_at IS NOT NULL
             GROUP BY e.id, e.nom_etablissement, e.logo_url
             HAVING connected_users > 0
             ORDER BY connected_users DESC, e.nom_etablissement ASC'
        );

        $usersStmt = $db->query(
            'SELECT u.id AS user_id, u.nom_complet, u.role,
                    e.nom_etablissement AS school_name,
                    e.logo_url AS school_logo_url,
                    p.last_login_at, p.dashboard_accessed_at, p.last_seen_at,
                    l.latitude, l.longitude, l.accuracy_meters, l.shared_at AS location_shared_at,
                    CASE WHEN p.last_seen_at >= DATE_SUB(NOW(), INTERVAL ' . $window . ' MINUTE)
                         THEN 1 ELSE 0 END AS is_online
             FROM utilisateur_presence p
             INNER JOIN utilisateurs u ON u.id = p.user_id
             LEFT JOIN ecoles e ON e.id = u.ecole_id
             LEFT JOIN utilisateur_locations l ON l.user_id = u.id
             WHERE p.dashboard_accessed_at IS NOT NULL
             ORDER BY p.last_seen_at DESC, p.dashboard_accessed_at DESC
             LIMIT 100'
        );

        $connectedSchools = $schoolsStmt->fetchAll(PDO::FETCH_ASSOC);
        $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
        $connectedUsers = array_sum(array_map(
            static fn (array $school): int => (int) $school['connected_users'],
            $connectedSchools
        ));

        return [
            'connectedSchools' => $connectedSchools,
            'users' => $users,
            'connectedSchoolCount' => count($connectedSchools),
            'connectedUserCount' => $connectedUsers,
        ];
    }

    private static function userParams(array $user): array
    {
        return [
            ':user_id' => (int) ($user['id'] ?? 0),
            ':ecole_id' => !empty($user['ecole_id']) ? (int) $user['ecole_id'] : null,
        ];
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS utilisateur_presence (
                user_id INT NOT NULL PRIMARY KEY,
                ecole_id INT NULL,
                last_login_at DATETIME NULL,
                dashboard_accessed_at DATETIME NULL,
                last_seen_at DATETIME NULL,
                KEY idx_utilisateur_presence_school_seen (ecole_id, last_seen_at),
                KEY idx_utilisateur_presence_dashboard (dashboard_accessed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS utilisateur_page_visits (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                ecole_id INT NULL,
                page_path VARCHAR(255) NOT NULL,
                page_title VARCHAR(180) NOT NULL,
                visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_utilisateur_page_visits_user_date (user_id, visited_at),
                KEY idx_utilisateur_page_visits_ecole_date (ecole_id, visited_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS utilisateur_locations (
                user_id INT NOT NULL PRIMARY KEY,
                latitude DECIMAL(10, 7) NOT NULL,
                longitude DECIMAL(10, 7) NOT NULL,
                accuracy_meters DECIMAL(10, 2) NOT NULL,
                shared_at DATETIME NOT NULL,
                KEY idx_utilisateur_locations_shared_at (shared_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS utilisateur_location_history (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                latitude DECIMAL(10, 7) NOT NULL,
                longitude DECIMAL(10, 7) NOT NULL,
                accuracy_meters DECIMAL(10, 2) NOT NULL,
                shared_at DATETIME NOT NULL,
                KEY idx_utilisateur_location_history_user_date (user_id, shared_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
