<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class ComptesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Caisses & Banques',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Caisses & Banques',
            'pageDescription' => 'Gérez les caisses et les comptes bancaires.',
            'pageContent' => 'Suivi des flux financiers de l’école.',
            'pageNotes' => 'Réservé aux comptables et aux responsables financiers.',
        ]);
    }
}
