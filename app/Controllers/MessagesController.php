<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
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
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';

        $this->view('messages/index', [
            'title' => APP_NAME . ' - Messages',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'messages' => UserMessage::getForUser((int) ($user['id'] ?? 0)),
        ]);
    }

    public function open(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $messageId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$user || !$messageId || $messageId <= 0) {
            $this->redirect('/messages');
        }

        UserMessage::markRead($messageId, (int) $user['id']);
        $this->redirect('/messages#message-' . $messageId);
    }
}
