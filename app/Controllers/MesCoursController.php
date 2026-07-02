<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class MesCoursController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['enseignant_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Mes cours',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Mes cours',
            'pageDescription' => 'Consultez vos cours et votre emploi du temps.',
            'pageContent' => 'Liste des cours et matières assignées.',
            'pageNotes' => 'Accessible aux enseignants.',
        ]);
    }
}
