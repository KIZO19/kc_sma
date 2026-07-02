<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class BulletinsEnfantController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Bulletins enfant',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Bulletins de mon enfant',
            'pageDescription' => 'Consultez les bulletins scolaires de votre enfant.',
            'pageContent' => 'Bulletins et appréciations par période.',
            'pageNotes' => 'Réservé aux parents.',
        ]);
    }
}
