<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class UtilisateursController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $inactiveUsers = User::getInactiveUsers();

        $this->view('utilisateurs/index', [
            'title' => APP_NAME . ' - Validation des comptes',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'inactiveUsers' => $inactiveUsers,
        ]);
    }

    public function validate(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            if ($userId > 0) {
                User::updateStatus($userId, 'Actif');
                $_SESSION['utilisateurs_success'] = 'Compte utilisateur validé avec succès.';
            }
        }

        $this->redirect('/utilisateurs');
    }
}
