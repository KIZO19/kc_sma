<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ecole;
use App\Models\User;

class UtilisateursController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'promoteur_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $inactiveUsers = User::getInactiveUsers();
        $unassignedUsers = User::getUnassignedPersonalAccounts();
        $schools = Ecole::getAll();
        $schoolPopulations = Ecole::getSchoolPopulationCounts();

        $this->view('utilisateurs/index', [
            'title' => APP_NAME . ' - Validation des comptes',
            'schoolPopulations' => $schoolPopulations,
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'inactiveUsers' => $inactiveUsers,
            'unassignedUsers' => $unassignedUsers,
            'schools' => $schools,
            'isLocalAdmin' => false,
        ]);
    }

    public function validate(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'promoteur_école']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            if ($userId > 0) {
                User::updateStatus($userId, 'Actif');
                $_SESSION['utilisateurs_success'] = 'Compte utilisateur validé avec succès.';
            }
        }

        $this->redirect('/utilisateurs');
    }

    public function link(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $ecoleId = (int) ($_POST['ecole_id'] ?? 0);

            if ($userId > 0 && $ecoleId > 0) {
                User::assignToSchool($userId, $ecoleId);
                $_SESSION['utilisateurs_success'] = 'Compte lié à l’école avec succès.';
            }
        }

        $this->redirect('/utilisateurs');
    }
}
