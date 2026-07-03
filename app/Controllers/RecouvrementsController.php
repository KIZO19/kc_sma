<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class RecouvrementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Recouvrements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Recouvrements',
            'pageDescription' => 'Planifiez et suivez les recouvrements financiers de l’école.',
            'pageContent' => 'Organisez les relances, les échéances de paiement et les actions de recouvrement.',
            'pageNotes' => 'Accessible aux comptables et à l’administration. Le comptable planifie les recouvrements.',
        ]);
    }
}
