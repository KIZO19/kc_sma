<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class DevisesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Devises',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Devises',
            'pageDescription' => 'Gérez les devises et les taux appliqués aux frais.',
            'pageContent' => 'Définissez les devises, les taux de change et les conversions pour la facturation.',
            'pageNotes' => 'Accessible aux comptables et à l’administration. Le comptable gère les devises et les taux de change.',
        ]);
    }
}
