<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class MessagesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['parent_ecole', 'enseignant_école', 'sec_école', 'ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Messages',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Messages',
            'pageDescription' => 'Envoyez et recevez des messages avec l’administration.',
            'pageContent' => 'Messagerie interne pour l’école.',
            'pageNotes' => 'Facilite la communication entre utilisateurs.',
        ]);
    }
}
