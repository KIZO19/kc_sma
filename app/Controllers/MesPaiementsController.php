<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class MesPaiementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['eleve_ecole', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Mes paiements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Mes paiements',
            'pageDescription' => 'Suivez vos paiements et reçus.',
            'pageContent' => 'Détails des paiements effectués.',
            'pageNotes' => 'Pour les élèves et les parents.',
        ]);
    }
}
