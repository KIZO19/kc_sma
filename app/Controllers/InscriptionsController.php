<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class InscriptionsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Inscriptions',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Inscriptions',
            'pageDescription' => 'Inscrivez de nouveaux élèves et suivez les dossiers scolaires.',
            'pageContent' => 'Ici vous pouvez gérer les inscriptions des élèves de l’école.',
            'pageNotes' => 'Page réservée aux rôles qui gèrent les inscriptions.',
        ]);
    }
}
