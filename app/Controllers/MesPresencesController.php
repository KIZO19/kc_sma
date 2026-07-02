<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class MesPresencesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['eleve_ecole', 'enseignant_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Mes présences',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Mes présences',
            'pageDescription' => 'Suivez votre présence en cours.',
            'pageContent' => 'Historique des présences et absences.',
            'pageNotes' => 'Pour les élèves et les enseignants.',
        ]);
    }
}
