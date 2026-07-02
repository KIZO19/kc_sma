<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class PaiementsEnfantController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Paiements enfant',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Paiements enfant',
            'pageDescription' => 'Suivez les paiements effectués pour votre enfant.',
            'pageContent' => 'Détails financiers de votre enfant.',
            'pageNotes' => 'Accessible aux parents uniquement.',
        ]);
    }
}
