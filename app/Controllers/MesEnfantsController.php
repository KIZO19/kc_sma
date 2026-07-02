<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class MesEnfantsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Enfants',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Enfants',
            'pageDescription' => 'Accédez aux dossiers scolaires de vos enfants.',
            'pageContent' => 'Liste des enfants et informations associées.',
            'pageNotes' => 'Réservé aux parents.',
        ]);
    }
}
