<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class DerogationsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Dérogations',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Dérogations',
            'pageDescription' => 'Validez et gérez les dérogations pour les frais et les paiements.',
            'pageContent' => 'Approuvez ou refusez les dérogations et suivez les demandes en attente.',
            'pageNotes' => 'Accessible aux comptables et à l’administration. Le comptable valide les dérogations.',
        ]);
    }
}
