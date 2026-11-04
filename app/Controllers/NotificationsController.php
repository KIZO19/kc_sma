<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\PersonName;
use App\Models\Derogation;
use App\Models\EvenementNotification;
use App\Models\NotificationAccess;
use App\Models\PushNotification;
use App\Models\SchoolMessage;
use App\Models\UserMessage;

class NotificationsController
{
    public function pushSubscribe(): void
    {
        $this->handlePushSubscription(true);
    }

    public function pushUnsubscribe(): void
    {
        $this->handlePushSubscription(false);
    }

    private function handlePushSubscription(bool $subscribe): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        header('X-Content-Type-Options: nosniff');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée.']);
            return;
        }

        Auth::requireAuth();
        $user = Auth::user();
        if ($user === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentification requise.']);
            return;
        }

        $csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (
            $csrfToken === ''
            || !hash_equals((string) ($_SESSION['push_notifications_csrf'] ?? ''), $csrfToken)
        ) {
            http_response_code(403);
            echo json_encode(['error' => 'La session a expiré. Rechargez la page puis réessayez.']);
            return;
        }

        try {
            $payload = json_decode((string) file_get_contents('php://input'), true, 16, JSON_THROW_ON_ERROR);
            if (!is_array($payload)) {
                throw new \InvalidArgumentException('Données d’abonnement invalides.');
            }
            if ($subscribe) {
                if (!PushNotification::isConfigured()) {
                    http_response_code(503);
                    echo json_encode(['error' => 'Les notifications push ne sont pas encore configurées sur le serveur.']);
                    return;
                }
                PushNotification::saveSubscription((int) $user['id'], $payload);
            } else {
                PushNotification::removeSubscription(
                    (int) $user['id'],
                    (string) ($payload['endpoint'] ?? '')
                );
            }

            echo json_encode(['success' => true]);
        } catch (\JsonException | \InvalidArgumentException $exception) {
            http_response_code(400);
            echo json_encode(['error' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            error_log('NotificationsController::handlePushSubscription failed: ' . $exception->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de modifier l’abonnement aux notifications système.']);
        }
    }

    public function poll(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        if ($user === null) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Authentification requise.']);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        header('X-Content-Type-Options: nosniff');

        try {
            $userId = (int) $user['id'];
            $schoolId = (int) ($user['ecole_id'] ?? 0);
            $role = (string) ($user['role'] ?? '');
            $directMessagingEnabled = NotificationAccess::isDirectMessagingEnabled($schoolId);

            if ($schoolId > 0) {
                EvenementNotification::deliverDueForUser($user);
            }

            $messages = UserMessage::getUnreadForUser($userId, 10);
            $directMessages = $directMessagingEnabled
                ? SchoolMessage::getUnreadForUser($userId, $schoolId, 10)
                : [];
            $pendingDerogations = $role === 'comptable_école'
                ? Derogation::getPendingForAccountant($schoolId, 10)
                : [];
            $decisionMessages = in_array($role, ['préfet_école', 'DE_école', 'DP_école', 'parent_ecole', 'parent_école'], true)
                ? Derogation::getUnreadDecisionMessages($userId, 10)
                : [];
            $events = $schoolId > 0
                ? EvenementNotification::getForUser($userId, 10)
                : [];

            echo json_encode([
                'messages' => $messages,
                'messageCount' => UserMessage::countUnreadForUser($userId),
                'directMessages' => $directMessages,
                'directMessageCount' => $directMessagingEnabled
                    ? SchoolMessage::countUnreadForUser($userId, $schoolId)
                    : 0,
                'pendingDerogations' => array_map(
                    static function (array $item): array {
                        $item['eleve_nom'] = PersonName::format($item);
                        return $item;
                    },
                    $pendingDerogations
                ),
                'pendingDerogationCount' => $role === 'comptable_école'
                    ? Derogation::countPendingForAccountant($schoolId)
                    : null,
                'decisionMessages' => array_map(
                    static function (array $item): array {
                        $item['eleve_nom'] = PersonName::format($item);
                        return $item;
                    },
                    $decisionMessages
                ),
                'decisionMessageCount' => in_array($role, ['préfet_école', 'DE_école', 'DP_école', 'parent_ecole', 'parent_école'], true)
                    ? Derogation::countUnreadDecisionMessages($userId)
                    : null,
                'events' => $events,
                'eventCount' => $schoolId > 0
                    ? EvenementNotification::countUnreadForUser($userId)
                    : 0,
            ], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $exception) {
            error_log('NotificationsController::poll failed: ' . $exception->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de charger les notifications.']);
        }
    }
}
