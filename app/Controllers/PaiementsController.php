<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class PaiementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'sec_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Paiements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Paiements',
            'pageDescription' => 'Gérez les paiements et les reçus des élèves.',
            'pageContent' => 'Suivi des paiements scolaires, des plans de recouvrement et des écritures de trésorerie.',
            'pageNotes' => 'Page accessible aux services financiers et à l’administration. Le comptable planifie les recouvrements et gère les paiements.',
        ]);
    }
}
