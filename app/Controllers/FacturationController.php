<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class FacturationController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Facturation',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Facturation',
            'pageDescription' => 'Gérez les frais, les devises et les dérogations.',
            'pageContent' => 'Suivi des factures, création des frais et gestion des taux de devises.',
            'pageNotes' => 'Accessible aux comptables et à l’administration. Le comptable gère les frais, les devises et les dérogations.',
        ]);
    }
}
