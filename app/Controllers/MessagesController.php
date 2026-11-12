<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DisciplineAudition;
use App\Models\NotificationAccess;
use App\Models\SchoolMessage;
use App\Models\UserMessage;
use App\Models\User;

class MessagesController extends Controller
{
    private const ACCESS_ROLES = [
        'super_admin',
        'ecole_admin',
        'agent_ecole',
        'préfet_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
        'comptable_école',
        'sec_école',
        'promoteur_école',
        'enseignant_école',
        'eleve_ecole',
        'parent_ecole',
        'parent_école',
        'agent_école',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $directMessagesEnabled = NotificationAccess::isDirectMessagingEnabled($schoolId);
        $isParent = in_array($role, ['parent_ecole', 'parent_école'], true);
        $isStaff = SchoolMessage::isStaffRole((string) $role);
        if (empty($_SESSION['school_messages_csrf'])) {
            $_SESSION['school_messages_csrf'] = bin2hex(random_bytes(32));
        }

        $this->view('messages/index', [
            'title' => APP_NAME . ' - Messages',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'messages' => UserMessage::getForUser((int) ($user['id'] ?? 0)),
            'threads' => $directMessagesEnabled
                ? SchoolMessage::getThreadsForUser((int) ($user['id'] ?? 0), $schoolId)
                : [],
            'children' => $directMessagesEnabled && $isParent
                ? SchoolMessage::getChildrenForParent((int) ($user['reference_id'] ?? 0), $schoolId)
                : [],
            'contacts' => !$directMessagesEnabled
                ? []
                : ($isParent
                ? SchoolMessage::getParentContacts((int) ($user['reference_id'] ?? 0), $schoolId)
                : (SchoolMessage::canMessageParents((string) $role)
                    ? SchoolMessage::getStaffParentContacts($schoolId, $this->getSectionScopeForUser($user))
                    : [])),
            'staffContacts' => $directMessagesEnabled && $isStaff
                ? SchoolMessage::getStaffContacts($schoolId, (int) ($user['id'] ?? 0))
                : [],
            'isParent' => $isParent,
            'canCompose' => $directMessagesEnabled && SchoolMessage::canComposeForRole((string) $role),
            'directMessagesEnabled' => $directMessagesEnabled,
            'csrfToken' => $_SESSION['school_messages_csrf'],
        ]);
    }

    public function thread(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        if (!$user || !NotificationAccess::isDirectMessagingEnabled((int) ($user['ecole_id'] ?? 0))) {
            $_SESSION['messages_error'] = 'La messagerie interne est désactivée pour votre école.';
            $this->redirect('/messages');
            return;
        }
        $threadId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $thread = $user && $threadId && $threadId > 0
            ? SchoolMessage::getThread((int) $threadId, (int) $user['id'], (int) ($user['ecole_id'] ?? 0))
            : null;
        if (!$thread) {
            http_response_code(404);
            $this->redirect('/messages');
            return;
        }

        if (empty($_SESSION['school_messages_csrf'])) {
            $_SESSION['school_messages_csrf'] = bin2hex(random_bytes(32));
        }
        $this->view('messages/thread', [
            'title' => APP_NAME . ' - ' . $thread['subject'],
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel((string) ($user['role'] ?? 'default')),
            'modules' => $this->getModulesForRole((string) ($user['role'] ?? 'default')),
            'thread' => $thread,
            'csrfToken' => $_SESSION['school_messages_csrf'],
        ]);
    }

    public function send(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        if (!$user || !NotificationAccess::isDirectMessagingEnabled((int) ($user['ecole_id'] ?? 0))) {
            $_SESSION['messages_error'] = 'La messagerie interne est désactivée pour votre école.';
            $this->redirect('/messages');
            return;
        }
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || !hash_equals((string) ($_SESSION['school_messages_csrf'] ?? ''), $token)
        ) {
            $_SESSION['messages_error'] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
            $this->redirect('/messages');
            return;
        }

        $studentId = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $isParent = in_array((string) ($user['role'] ?? ''), ['parent_ecole', 'parent_école'], true);
        $recipientUserId = 0;
        if ($isParent) {
            $recipientUserId = (int) ($_POST['recipient_user_id'] ?? 0);
        } else {
            $recipient = explode(':', (string) ($_POST['recipient'] ?? ''));
            if (count($recipient) === 2 && $recipient[0] === 'staff' && ctype_digit($recipient[1])) {
                $recipientUserId = (int) $recipient[1];
                $studentId = 0;
            } elseif (
                count($recipient) === 3
                && $recipient[0] === 'parent'
                && ctype_digit($recipient[1])
                && ctype_digit($recipient[2])
            ) {
                $recipientUserId = (int) $recipient[1];
                $studentId = (int) $recipient[2];
            } else {
                $legacyRecipient = explode(':', (string) ($_POST['recipient'] ?? ''), 2);
                if (
                    count($legacyRecipient) === 2
                    && ctype_digit($legacyRecipient[0])
                    && ctype_digit($legacyRecipient[1])
                ) {
                    $recipientUserId = (int) $legacyRecipient[0];
                    $studentId = (int) $legacyRecipient[1];
                }
            }
        }

        if (
            (!$isParent && $studentId === false)
            || ($isParent && (!$studentId || $studentId <= 0))
            || $recipientUserId <= 0
            || $subject === '' || mb_strlen($subject, 'UTF-8') > 160
            || $body === '' || mb_strlen($body, 'UTF-8') > 5000
        ) {
            $_SESSION['messages_error'] = 'Complétez le destinataire, l’objet et un message de 5 000 caractères maximum.';
            $this->redirect('/messages');
            return;
        }

        try {
            $threadId = SchoolMessage::createThread(
                $user,
                $recipientUserId,
                (int) $studentId,
                $subject,
                $body,
                $this->getSectionScopeForUser($user)
            );
            $_SESSION['messages_success'] = 'Votre message a été envoyé.';
            $_SESSION['school_messages_csrf'] = bin2hex(random_bytes(32));
            $this->redirect('/messages/thread?id=' . $threadId);
        } catch (\InvalidArgumentException $exception) {
            $_SESSION['messages_error'] = $exception->getMessage();
            $this->redirect('/messages');
        } catch (\Throwable $exception) {
            error_log('MessagesController::send failed: ' . $exception->getMessage());
            $_SESSION['messages_error'] = 'Le message n’a pas pu être envoyé. Réessayez.';
            $this->redirect('/messages');
        }
    }

    public function reply(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        if (!$user || !NotificationAccess::isDirectMessagingEnabled((int) ($user['ecole_id'] ?? 0))) {
            $_SESSION['messages_error'] = 'La messagerie interne est désactivée pour votre école.';
            $this->redirect('/messages');
            return;
        }
        $token = (string) ($_POST['csrf_token'] ?? '');
        $threadId = filter_var($_POST['thread_id'] ?? null, FILTER_VALIDATE_INT);
        $body = trim((string) ($_POST['body'] ?? ''));
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || !hash_equals((string) ($_SESSION['school_messages_csrf'] ?? ''), $token)
            || !$threadId
            || $threadId <= 0
            || $body === ''
            || mb_strlen($body, 'UTF-8') > 5000
        ) {
            $_SESSION['messages_error'] = 'Le message est invalide ou le formulaire a expiré.';
            $this->redirect('/messages');
            return;
        }

        try {
            if (!SchoolMessage::reply((int) $threadId, $user, $body)) {
                $_SESSION['messages_error'] = 'Cette conversation n’est pas accessible à votre compte.';
                $this->redirect('/messages');
                return;
            }
            $_SESSION['school_messages_csrf'] = bin2hex(random_bytes(32));
            $this->redirect('/messages/thread?id=' . (int) $threadId . '#conversation');
        } catch (\Throwable $exception) {
            error_log('MessagesController::reply failed: ' . $exception->getMessage());
            $_SESSION['messages_error'] = 'La réponse n’a pas pu être envoyée. Réessayez.';
            $this->redirect('/messages/thread?id=' . (int) $threadId);
        }
    }

    public function chatPoll(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $threadId = filter_var($_GET['thread_id'] ?? null, FILTER_VALIDATE_INT);
        $afterId = filter_var($_GET['after_id'] ?? 0, FILTER_VALIDATE_INT);
        $this->jsonResponse(function () use ($user, $threadId, $afterId): array {
            if (!$user || !NotificationAccess::isDirectMessagingEnabled((int) ($user['ecole_id'] ?? 0))) {
                http_response_code(403);
                return ['error' => 'La messagerie interne est désactivée pour votre école.'];
            }
            if (!$user || !$threadId || $threadId <= 0 || $afterId === false || $afterId < 0) {
                http_response_code(400);
                return ['error' => 'Paramètres de conversation invalides.'];
            }

            $updates = SchoolMessage::getMessagesAfter(
                (int) $threadId,
                (int) $user['id'],
                (int) ($user['ecole_id'] ?? 0),
                (int) $afterId
            );
            if ($updates === null) {
                http_response_code(404);
                return ['error' => 'Cette conversation n’est pas accessible à votre compte.'];
            }

            return $updates;
        });
    }

    public function chatReply(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $token = (string) ($_POST['csrf_token'] ?? '');
        $threadId = filter_var($_POST['thread_id'] ?? null, FILTER_VALIDATE_INT);
        $body = trim((string) ($_POST['body'] ?? ''));
        $this->jsonResponse(function () use ($user, $token, $threadId, $body): array {
            if (!$user || !NotificationAccess::isDirectMessagingEnabled((int) ($user['ecole_id'] ?? 0))) {
                http_response_code(403);
                return ['error' => 'La messagerie interne est désactivée pour votre école.'];
            }
            if (
                ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
                || !$user
                || $token === ''
                || !hash_equals((string) ($_SESSION['school_messages_csrf'] ?? ''), $token)
                || !$threadId
                || $threadId <= 0
                || $body === ''
                || mb_strlen($body, 'UTF-8') > 5000
            ) {
                http_response_code(400);
                return ['error' => 'Le message est invalide ou le formulaire a expiré.'];
            }

            if (!SchoolMessage::reply((int) $threadId, $user, $body)) {
                http_response_code(404);
                return ['error' => 'Cette conversation n’est pas accessible à votre compte.'];
            }

            return ['sent' => true];
        });
    }

    public function chatTyping(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $token = (string) ($_POST['csrf_token'] ?? '');
        $threadId = filter_var($_POST['thread_id'] ?? null, FILTER_VALIDATE_INT);
        $this->jsonResponse(function () use ($user, $token, $threadId): array {
            if (!$user || !NotificationAccess::isDirectMessagingEnabled((int) ($user['ecole_id'] ?? 0))) {
                http_response_code(403);
                return ['error' => 'La messagerie interne est désactivée pour votre école.'];
            }
            if (
                ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
                || !$user
                || $token === ''
                || !hash_equals((string) ($_SESSION['school_messages_csrf'] ?? ''), $token)
                || !$threadId
                || $threadId <= 0
            ) {
                http_response_code(400);
                return ['error' => 'Paramètres de présence invalides.'];
            }

            $typingValue = filter_var($_POST['typing'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($typingValue === null || !SchoolMessage::setTyping(
                (int) $threadId,
                (int) $user['id'],
                (int) ($user['ecole_id'] ?? 0),
                $typingValue
            )) {
                http_response_code(404);
                return ['error' => 'Cette conversation n’est pas accessible à votre compte.'];
            }

            return ['updated' => true];
        });
    }

    private function jsonResponse(callable $callback): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        header('X-Content-Type-Options: nosniff');
        try {
            echo json_encode($callback(), JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $exception) {
            error_log('MessagesController chat endpoint failed: ' . $exception->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Une erreur est survenue pendant la mise à jour du chat.']);
        }
    }

    public function open(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $messageId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$user || !$messageId || $messageId <= 0) {
            $this->redirect('/messages');
            return;
        }

        $message = UserMessage::findForUser($messageId, (int) $user['id']);
        if (!$message) {
            http_response_code(404);
            $this->redirect('/messages');
            return;
        }
        if ($message && ($message['type'] ?? '') === 'derogation_demande') {
            UserMessage::markRead($messageId, (int) $user['id']);
            $this->redirect('/derogations');
            return;
        }

        UserMessage::markRead($messageId, (int) $user['id']);
        if (
            in_array(
                (string) ($message['type'] ?? ''),
                ['subscription_renewal_request', 'subscription_request_approved', 'subscription_request_rejected'],
                true
            )
        ) {
            $this->redirect(($user['role'] ?? '') === 'super_admin'
                ? '/ecoles/requests'
                : '/ecoles/subscriptions');
            return;
        }

        $role = (string) ($user['role'] ?? 'default');
        $disciplineReportUrl = null;
        if (($message['type'] ?? '') === 'discipline_audition') {
            $schoolId = (int) ($user['ecole_id'] ?? 0);
            $auditionId = (int) ($message['reference_id'] ?? 0);
            $audition = DisciplineAudition::findByIdAndSchool(
                $auditionId,
                $schoolId,
                $this->getSectionScopeForUser($user)
            );
            $isReportRecipient = in_array(
                (int) ($user['id'] ?? 0),
                DisciplineAudition::getReportRecipientIds($auditionId),
                true
            );
            if (
                $audition !== null
                && (
                    in_array($role, ['DD_école', 'ecole_admin'], true)
                    || $isReportRecipient
                )
            ) {
                $disciplineReportUrl = BASE_URL . '/eleves/disciplineReport?id=' . $auditionId;
            }
        }

        $this->view('messages/notification', [
            'title' => APP_NAME . ' - ' . $message['title'],
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'message' => $message,
            'disciplineReportUrl' => $disciplineReportUrl,
        ]);
    }
}
