<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Entities\Roles;
use App\Models\Ecole;
use App\Models\User;

class UtilisateursController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'promoteur_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $isSuperAdmin = ($role === 'super_admin');
        $schoolId = Auth::getSchoolId();

        if ($isSuperAdmin) {
            $allUsers = User::getAllUsers();
            $inactiveUsers = User::getInactiveUsers();
            $unassignedUsers = User::getUnassignedPersonalAccounts();
        } else {
            $schoolUsers = User::getUsersBySchool((int) $schoolId);
            $unassignedUsers = User::getUnassignedPersonalAccounts();
            $allUsers = $schoolUsers;
            foreach ($unassignedUsers as $pendingUser) {
                $alreadyPresent = false;
                foreach ($allUsers as $existingUser) {
                    if ((int) ($existingUser['id'] ?? 0) === (int) ($pendingUser['id'] ?? 0)) {
                        $alreadyPresent = true;
                        break;
                    }
                }
                if (!$alreadyPresent) {
                    $allUsers[] = $pendingUser;
                }
            }

            $inactiveUsers = User::getInactiveUsersBySchool((int) $schoolId);
            foreach ($unassignedUsers as $pendingUser) {
                if (($pendingUser['statut'] ?? '') === 'Inactif') {
                    $alreadyPresent = false;
                    foreach ($inactiveUsers as $existingUser) {
                        if ((int) ($existingUser['id'] ?? 0) === (int) ($pendingUser['id'] ?? 0)) {
                            $alreadyPresent = true;
                            break;
                        }
                    }
                    if (!$alreadyPresent) {
                        $inactiveUsers[] = $pendingUser;
                    }
                }
            }
        }
        $schools = Ecole::getAll();
        $schoolPopulations = Ecole::getSchoolPopulationCounts();

        $summaryStats = [
            'total' => count($allUsers),
            'actifs' => 0,
            'inactifs' => 0,
            'personnels' => 0,
            'non_lies' => 0,
        ];

        foreach ($allUsers as $account) {
            $roleName = $account['role'] ?? '';
            if (($account['statut'] ?? '') === 'Actif') {
                $summaryStats['actifs']++;
            } else {
                $summaryStats['inactifs']++;
            }

            if (in_array($roleName, ['agent_ecole', 'parent_ecole', 'enseignant_école'], true)) {
                $summaryStats['personnels']++;
            }

            if (in_array($roleName, ['agent_ecole', 'parent_ecole', 'enseignant_école'], true) && (empty($account['ecole_id']) || (int) $account['ecole_id'] === 0)) {
                $summaryStats['non_lies']++;
            }
        }

        $schoolNamesById = [];
        $schoolDetailsById = [];
        foreach ($schools as $school) {
            $schoolId = (int) ($school['id'] ?? 0);
            $schoolNamesById[$schoolId] = $school['nom_etablissement'] ?? 'École';
            $schoolDetailsById[$schoolId] = $school;
        }

        $currentSchoolLogoUrl = '';
        $currentSchoolId = (int) ($user['ecole_id'] ?? 0);
        if ($currentSchoolId > 0 && isset($schoolDetailsById[$currentSchoolId])) {
            $currentSchoolLogo = $schoolDetailsById[$currentSchoolId]['logo_url'] ?? '';
            if (!empty($currentSchoolLogo)) {
                $currentSchoolLogoUrl = strpos($currentSchoolLogo, 'http') === 0 ? $currentSchoolLogo : BASE_URL . '/' . ltrim($currentSchoolLogo, '/');
            }
        }

        foreach ($allUsers as $account) {
            $schoolIdForUser = (int) ($account['ecole_id'] ?? 0);
            if ($schoolIdForUser > 0 && !isset($schoolDetailsById[$schoolIdForUser])) {
                $schoolDetails = Ecole::findById($schoolIdForUser);
                if ($schoolDetails) {
                    $schoolNamesById[$schoolIdForUser] = $schoolDetails['nom_etablissement'] ?? 'École';
                    $schoolDetailsById[$schoolIdForUser] = $schoolDetails;
                }
            }
        }

        $this->view('utilisateurs/index', [
            'title' => APP_NAME . ' - Utilisateurs',
            'schoolPopulations' => $schoolPopulations,
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'allUsers' => $allUsers,
            'summaryStats' => $summaryStats,
            'inactiveUsers' => $inactiveUsers,
            'unassignedUsers' => $unassignedUsers,
            'schools' => $schools,
            'schoolNamesById' => $schoolNamesById,
            'schoolDetailsById' => $schoolDetailsById,
            'currentSchoolLogoUrl' => $currentSchoolLogoUrl,
            'isLocalAdmin' => !$isSuperAdmin,
            'availableRoles' => Roles::list(),
        ]);
    }

    public function updateAccess(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        $currentUser = Auth::user();
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = (string) ($_POST['role'] ?? '');
        $statut = (string) ($_POST['statut'] ?? '');
        $ecoleId = (int) ($_POST['ecole_id'] ?? 0);
        $targetUser = $userId > 0 ? User::findById($userId) : null;
        $errors = [];

        if (!$targetUser) {
            $errors[] = 'Utilisateur introuvable.';
        } elseif ($userId === (int) ($currentUser['id'] ?? 0)) {
            $errors[] = 'Vous ne pouvez pas modifier vos propres accès depuis cette page.';
        }

        if (!array_key_exists($role, Roles::list())) {
            $errors[] = 'Rôle invalide.';
        }
        if (!in_array($statut, ['Actif', 'Inactif'], true)) {
            $errors[] = 'Statut invalide.';
        }

        $schoolScoped = in_array($role, User::getSchoolScopedRoles(), true);
        if ($role === 'super_admin') {
            $ecoleId = null;
        } elseif ($schoolScoped && $role !== 'agent_ecole' && $ecoleId <= 0) {
            $errors[] = 'Une école est obligatoire pour ce rôle.';
        } elseif ($ecoleId > 0 && !Ecole::findById($ecoleId)) {
            $errors[] = 'École invalide.';
        } elseif (!$schoolScoped) {
            $ecoleId = null;
        }

        if (empty($errors) && User::updateAccess($userId, $role, $statut, $ecoleId > 0 ? $ecoleId : null)) {
            $_SESSION['utilisateurs_success'] = 'Les accès de l’utilisateur ont été mis à jour.';
        } else {
            $_SESSION['utilisateurs_errors'] = $errors ?: ['Impossible de mettre à jour les accès.'];
        }

        $this->redirect('/utilisateurs');
    }

    public function validate(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'promoteur_école']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $user = $userId > 0 ? User::findById($userId) : null;
            $currentUser = Auth::user();
            $currentSchoolId = (int) ($currentUser['ecole_id'] ?? 0);

            if ($userId > 0 && $user) {
                $isPendingUnassignedAgent = ($user['role'] ?? '') === 'agent_ecole'
                    && (($user['statut'] ?? '') === 'Inactif')
                    && (empty($user['ecole_id']) || (int) $user['ecole_id'] === 0);

                if ($isPendingUnassignedAgent && $currentSchoolId > 0) {
                    User::assignToSchool($userId, $currentSchoolId);
                }

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
