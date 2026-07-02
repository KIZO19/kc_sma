<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class ElevesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Élèves',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Élèves',
            'pageDescription' => 'Affichez et gérez les informations des élèves de l’école.',
            'pageContent' => 'Liste des élèves, profils et actions de gestion.',
            'pageNotes' => 'Page utilisée pour la gestion des dossiers scolaires.',
        ]);
    }
}
