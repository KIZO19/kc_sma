<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class MonEmploiController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['eleve_ecole', 'enseignant_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Mon emploi du temps',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Mon emploi du temps',
            'pageDescription' => 'Consultez votre emploi du temps et vos créneaux.',
            'pageContent' => 'Vue de l’emploi du temps personnel.',
            'pageNotes' => 'Pour élèves et enseignants selon le rôle.',
        ]);
    }
}
