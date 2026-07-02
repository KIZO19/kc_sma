<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class BulletinsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Bulletins',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Bulletins',
            'pageDescription' => 'Consultez et publiez les bulletins scolaires.',
            'pageContent' => 'Gestion des bulletins et de la communication des résultats.',
            'pageNotes' => 'Page accessible aux rôles pédagogiques et aux parents.',
        ]);
    }
}
