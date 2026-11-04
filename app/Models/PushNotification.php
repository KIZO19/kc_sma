<?php

namespace App\Models;

use App\Core\Database;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use PDO;

class PushNotification
{
    private static bool $tableEnsured = false;

    public static function isConfigured(): bool
    {
        return PUSH_VAPID_PUBLIC_KEY !== ''
            && PUSH_VAPID_PRIVATE_KEY !== ''
            && PUSH_VAPID_SUBJECT !== '';
    }

    public static function saveSubscription(int $userId, array $subscription): void
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Compte utilisateur invalide.');
        }

        $endpoint = trim((string) ($subscription['endpoint'] ?? ''));
        $endpointUrl = filter_var($endpoint, FILTER_VALIDATE_URL);
        $endpointHost = $endpointUrl !== false ? parse_url($endpoint, PHP_URL_HOST) : false;
        $keys = $subscription['keys'] ?? null;
        $publicKey = is_array($keys) ? trim((string) ($keys['p256dh'] ?? '')) : '';
        $authToken = is_array($keys) ? trim((string) ($keys['auth'] ?? '')) : '';
        if (
            $endpointUrl === false
            || strlen($endpoint) > 2048
            || parse_url($endpoint, PHP_URL_SCHEME) !== 'https'
            || !is_string($endpointHost)
            || !self::isTrustedPushHost($endpointHost)
            || $publicKey === ''
            || $authToken === ''
            || strlen($publicKey) > 255
            || strlen($authToken) > 255
            || !preg_match('/^[A-Za-z0-9_-]+$/', $publicKey)
            || !preg_match('/^[A-Za-z0-9_-]+$/', $authToken)
        ) {
            throw new \InvalidArgumentException('L’abonnement aux notifications système est invalide.');
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO notifications_push_utilisateurs
                (utilisateur_id, endpoint, endpoint_hash, public_key, auth_token)
             VALUES (:utilisateur_id, :endpoint, :endpoint_hash, :public_key, :auth_token)
             ON DUPLICATE KEY UPDATE utilisateur_id = VALUES(utilisateur_id),
                endpoint = VALUES(endpoint), public_key = VALUES(public_key),
                auth_token = VALUES(auth_token), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            ':utilisateur_id' => $userId,
            ':endpoint' => $endpoint,
            ':endpoint_hash' => hash('sha256', $endpoint),
            ':public_key' => $publicKey,
            ':auth_token' => $authToken,
        ]);
    }

    private static function isTrustedPushHost(string $host): bool
    {
        $host = strtolower(rtrim($host, '.'));
        $allowedHosts = [
            'fcm.googleapis.com',
            'push.apple.com',
            'notify.windows.com',
            'push.windows.com',
        ];
        if (in_array($host, $allowedHosts, true)) {
            return true;
        }

        foreach ([
            '.push.services.mozilla.com',
            '.push.services.mozilla.net',
            '.push.apple.com',
            '.notify.windows.com',
            '.push.windows.com',
        ] as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return true;
            }
        }

        return false;
    }

    public static function removeSubscription(int $userId, string $endpoint): void
    {
        if ($userId <= 0 || $endpoint === '') {
            throw new \InvalidArgumentException('Abonnement aux notifications invalide.');
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'DELETE FROM notifications_push_utilisateurs
             WHERE utilisateur_id = :utilisateur_id AND endpoint_hash = :endpoint_hash'
        );
        $stmt->execute([
            ':utilisateur_id' => $userId,
            ':endpoint_hash' => hash('sha256', $endpoint),
        ]);
    }

    public static function sendToUser(
        int $userId,
        string $title,
        string $body,
        string $url,
        string $tag = ''
    ): void
    {
        if ($userId <= 0 || !self::isConfigured()) {
            return;
        }

        self::ensureTable();
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'SELECT id, endpoint, public_key, auth_token
                 FROM notifications_push_utilisateurs
                 WHERE utilisateur_id = :utilisateur_id'
            );
            $stmt->execute([':utilisateur_id' => $userId]);
            $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($subscriptions === []) {
                return;
            }

            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => PUSH_VAPID_SUBJECT,
                    'publicKey' => PUSH_VAPID_PUBLIC_KEY,
                    'privateKey' => PUSH_VAPID_PRIVATE_KEY,
                ],
            ], ['TTL' => 60, 'urgency' => 'normal']);
            $payload = json_encode([
                'title' => mb_substr($title, 0, 120, 'UTF-8'),
                'body' => mb_substr($body, 0, 500, 'UTF-8'),
                'url' => $url,
                'tag' => $tag !== '' ? $tag : 'kc-sma-' . bin2hex(random_bytes(8)),
            ], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);

            foreach ($subscriptions as $row) {
                $subscription = Subscription::create([
                    'endpoint' => (string) $row['endpoint'],
                    'keys' => [
                        'p256dh' => (string) $row['public_key'],
                        'auth' => (string) $row['auth_token'],
                    ],
                    'contentEncoding' => 'aes128gcm',
                ]);
                $report = $webPush->sendOneNotification($subscription, $payload);
                if ($report->isSubscriptionExpired()) {
                    $delete = $db->prepare(
                        'DELETE FROM notifications_push_utilisateurs WHERE id = :id'
                    );
                    $delete->execute([':id' => (int) $row['id']]);
                } elseif (!$report->isSuccess()) {
                    error_log('Push notification delivery failed: ' . $report->getReason());
                }
            }
        } catch (\Throwable $exception) {
            error_log('PushNotification::sendToUser failed: ' . $exception->getMessage());
        }
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS notifications_push_utilisateurs (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                utilisateur_id INT NOT NULL,
                endpoint TEXT NOT NULL,
                endpoint_hash CHAR(64) NOT NULL,
                public_key VARCHAR(255) NOT NULL,
                auth_token VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_push_endpoint_hash (endpoint_hash),
                KEY idx_push_user (utilisateur_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
