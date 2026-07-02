<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class RapportsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Rapports',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Rapports',
            'pageDescription' => 'Consultez les rapports et les analyses.',
            'pageContent' => 'Tableaux de bord et exports financiers/pédagogiques.',
            'pageNotes' => 'Utilisé pour les bilans mensuels et trimestriels.',
        ]);
    }
}
