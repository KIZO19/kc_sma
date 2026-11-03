<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\PersonName;
use App\Models\Derogation;
use App\Models\EvenementNotification;
use App\Models\NotificationAccess;
use App\Models\SchoolMessage;
use App\Models\UserMessage;

class NotificationsController
{
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
