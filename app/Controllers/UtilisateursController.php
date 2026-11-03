<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Entities\Roles;
use App\Models\Ecole;
use App\Models\ActivityLog;
use App\Models\Section;
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
        if (empty($_SESSION['user_account_csrf'])) {
            $_SESSION['user_account_csrf'] = bin2hex(random_bytes(32));
        }

        $isSuperAdmin = ($role === 'super_admin');
        $schoolId = Auth::getSchoolId();

        if ($isSuperAdmin) {
            $allUsers = User::getAllUsers();
            $inactiveUsers = User::getInactiveUsers();
            $unassignedUsers = User::getUnassignedPersonalAccounts();
        } else {
            $schoolUsers = User::getUsersBySchool((int) $schoolId);
            $unassignedUsers = array_values(array_filter(
                User::getUnassignedPersonalAccounts(),
                static fn (array $pendingUser): bool =>
                    ($pendingUser['role'] ?? '') === 'agent_ecole'
                    && ($pendingUser['statut'] ?? '') === 'Inactif'
            ));
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
        $schools = $isSuperAdmin
            ? Ecole::getAll()
            : array_values(array_filter([Ecole::findById((int) $schoolId)]));
        $schoolPopulations = $isSuperAdmin ? Ecole::getSchoolPopulationCounts() : [];

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
        $manageableSchoolRoles = array_filter(
            Roles::list(),
            static fn (string $role): bool =>
                in_array($role, User::getSchoolScopedRoles(), true)
                && !in_array($role, ['ecole_admin', 'promoteur_école'], true),
            ARRAY_FILTER_USE_KEY
        );

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
            'sections' => Section::getAll(),
            'schoolNamesById' => $schoolNamesById,
            'schoolDetailsById' => $schoolDetailsById,
            'currentSchoolLogoUrl' => $currentSchoolLogoUrl,
            'isLocalAdmin' => !$isSuperAdmin,
            'availableRoles' => Roles::list(),
            'manageableSchoolRoles' => $manageableSchoolRoles,
            'accountCsrfToken' => $_SESSION['user_account_csrf'],
        ]);
    }

    private function getEditableAccount(int $userId, array $currentUser): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $account = User::findById($userId);
        if (!$account) {
            return null;
        }

        if (($currentUser['role'] ?? '') === 'super_admin') {
            return $account;
        }

        $schoolId = (int) ($currentUser['ecole_id'] ?? 0);
        return $schoolId > 0 && (int) ($account['ecole_id'] ?? 0) === $schoolId
            ? $account
            : null;
    }

    public function editAccount(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin']);
        $currentUser = Auth::refresh() ?: Auth::user();
        $account = $this->getEditableAccount((int) ($_GET['id'] ?? 0), $currentUser);
        if (!$account) {
            $this->redirect('/error/accessDenied');
        }

        $this->view('utilisateurs/edit_account', [
            'title' => APP_NAME . ' - Modifier le compte',
            'user' => $currentUser,
            'role' => $currentUser['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($currentUser['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($currentUser['role'] ?? 'default'),
            'account' => $account,
            'csrfToken' => $_SESSION['user_account_csrf'] ?? '',
        ]);
    }

    public function updateAccountInfo(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin']);
        $currentUser = Auth::refresh() ?: Auth::user();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/utilisateurs');
        }

        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if ($postedToken === '' || !hash_equals((string) ($_SESSION['user_account_csrf'] ?? ''), $postedToken)) {
            $_SESSION['utilisateurs_errors'] = ['Votre session a expiré. Rechargez le formulaire puis réessayez.'];
            $this->redirect('/utilisateurs');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $account = $this->getEditableAccount($userId, $currentUser);
        if (!$account) {
            $this->redirect('/error/accessDenied');
        }

        $name = trim((string) ($_POST['nom_complet'] ?? ''));
        $identifier = trim((string) ($_POST['identifiant'] ?? ''));
        $password = (string) ($_POST['mot_de_passe'] ?? '');
        $passwordConfirm = (string) ($_POST['mot_de_passe_confirm'] ?? '');
        $errors = [];

        if ($name === '' || mb_strlen($name, 'UTF-8') > 150) {
            $errors[] = 'Le nom complet est requis et doit contenir au maximum 150 caractères.';
        }
        if ($identifier === '' || mb_strlen($identifier, 'UTF-8') > 100) {
            $errors[] = 'L’identifiant est requis et doit contenir au maximum 100 caractères.';
        } elseif ($identifier !== (string) ($account['identifiant'] ?? '') && User::existsByIdentifiant($identifier)) {
            $errors[] = 'Cet identifiant est déjà utilisé.';
        }
        if ($password !== '' && strlen($password) < 8) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors) {
            $_SESSION['utilisateurs_errors'] = $errors;
            $this->redirect('/utilisateurs/editAccount?id=' . $userId);
        }

        $data = ['nom_complet' => $name, 'identifiant' => $identifier];
        if ($password !== '') {
            $data['mot_de_passe'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (User::updateProfile($userId, $data)) {
            if (($currentUser['role'] ?? '') === 'super_admin' && ($account['role'] ?? '') === 'ecole_admin') {
                ActivityLog::record(
                    (int) ($currentUser['id'] ?? 0),
                    $password !== '' ? 'school_admin_password_reset' : 'school_admin_account_updated',
                    'user',
                    $userId,
                    $name,
                    $password !== ''
                        ? 'Mot de passe de l’administrateur d’école réinitialisé.'
                        : 'Informations du compte administrateur d’école mises à jour.'
                );
            }
            $_SESSION['utilisateurs_success'] = 'Les informations du compte ont été mises à jour.';
            $this->redirect('/utilisateurs');
        }

        $_SESSION['utilisateurs_errors'] = ['Impossible de mettre à jour les informations du compte.'];
        $this->redirect('/utilisateurs/editAccount?id=' . $userId);
    }

    public function updateAccess(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin']);

        $currentUser = Auth::refresh() ?: Auth::user();
        $isSchoolAdmin = ($currentUser['role'] ?? '') === 'ecole_admin';
        $currentSchoolId = (int) ($currentUser['ecole_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = (string) ($_POST['role'] ?? '');
        $statut = (string) ($_POST['statut'] ?? '');
        $ecoleId = (int) ($_POST['ecole_id'] ?? 0);
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $targetUser = $userId > 0 ? User::findById($userId) : null;
        $errors = [];
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        $expectedToken = (string) ($_SESSION['user_account_csrf'] ?? '');
        if ($postedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $postedToken)) {
            $_SESSION['utilisateurs_errors'] = ['Votre session a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/utilisateurs');
            return;
        }

        if (!$targetUser) {
            $errors[] = 'Utilisateur introuvable.';
        } elseif ($userId === (int) ($currentUser['id'] ?? 0)) {
            $errors[] = 'Vous ne pouvez pas modifier vos propres accès depuis cette page.';
        }

        if ($isSchoolAdmin) {
            $manageableRoles = array_keys(array_filter(
                Roles::list(),
                static fn (string $candidateRole): bool =>
                    in_array($candidateRole, User::getSchoolScopedRoles(), true)
                    && !in_array($candidateRole, ['ecole_admin', 'promoteur_école'], true),
                ARRAY_FILTER_USE_KEY
            ));
            if (
                $currentSchoolId <= 0
                || !$targetUser
                || (int) ($targetUser['ecole_id'] ?? 0) !== $currentSchoolId
                || in_array($targetUser['role'] ?? '', ['ecole_admin', 'promoteur_école'], true)
            ) {
                $errors[] = 'Vous ne pouvez gérer que les comptes rattachés à votre école, hors administrateurs et promoteurs.';
            }
            if (!in_array($role, $manageableRoles, true)) {
                $errors[] = 'Le rôle sélectionné ne peut pas être attribué par un administrateur d’école.';
            }
            $ecoleId = $currentSchoolId;
        } elseif (!array_key_exists($role, Roles::list())) {
            $errors[] = 'Rôle invalide.';
        }
        if (!in_array($statut, ['Actif', 'Inactif'], true)) {
            $errors[] = 'Statut invalide.';
        }

        $schoolScoped = in_array($role, User::getSchoolScopedRoles(), true);
        if (!$isSchoolAdmin && $role === 'super_admin') {
            $ecoleId = null;
        } elseif ($schoolScoped && $role !== 'agent_ecole' && $ecoleId <= 0) {
            $errors[] = 'Une école est obligatoire pour ce rôle.';
        } elseif (!$isSchoolAdmin && $ecoleId > 0 && !Ecole::findById($ecoleId)) {
            $errors[] = 'École invalide.';
        } elseif (!$isSchoolAdmin && !$schoolScoped) {
            $ecoleId = null;
        }

        if (empty($errors) && $ecoleId > 0) {
            $capacityForRole = static function (string $accountRole): ?string {
                if (in_array($accountRole, ['eleve_ecole', 'eleve_école'], true)) {
                    return 'students';
                }
                if (in_array($accountRole, ['parent_ecole', 'parent_école'], true)) {
                    return 'parents';
                }
                return in_array($accountRole, User::getSchoolScopedRoles(), true) ? 'staff' : null;
            };
            $targetCapacity = $capacityForRole($role);
            $previousCapacity = $targetUser ? $capacityForRole((string) ($targetUser['role'] ?? '')) : null;
            $previousSchoolId = (int) ($targetUser['ecole_id'] ?? 0);
            if (
                $targetCapacity !== null
                && ($previousSchoolId !== $ecoleId || $previousCapacity !== $targetCapacity)
            ) {
                try {
                    Ecole::assertSubscriptionCapacity($ecoleId, $targetCapacity);
                } catch (\DomainException $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        }

        $sectionScopedRoles = ['préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école'];
        if (in_array($role, $sectionScopedRoles, true)) {
            $section = $sectionId > 0 ? Section::findById($sectionId) : null;
            $sectionName = mb_strtolower(trim((string) ($section['nom_section'] ?? '')), 'UTF-8');
            $allowedSectionNames = in_array($role, ['préfet_école', 'DE_école', 'DD_école'], true)
                ? ['secondaire']
                : ['primaire', 'maternelle'];
            if (!$section || !in_array($sectionName, $allowedSectionNames, true)) {
                $errors[] = 'La section sélectionnée ne correspond pas aux responsabilités de ce rôle.';
            }
        } else {
            $sectionId = 0;
        }

        if (
            empty($errors)
            && User::updateAccess(
                $userId,
                $role,
                $statut,
                $ecoleId > 0 ? $ecoleId : null,
                $sectionId > 0 ? $sectionId : null
            )
        ) {
            if (
                $isSchoolAdmin
                || ($targetUser['role'] ?? '') === 'ecole_admin'
                || $role === 'ecole_admin'
            ) {
                $previousSchool = !empty($targetUser['ecole_id'])
                    ? Ecole::findById((int) $targetUser['ecole_id'])
                    : null;
                $newSchool = $ecoleId > 0 ? Ecole::findById($ecoleId) : null;
                $description = sprintf(
                    'Accès modifiés : %s → %s ; statut %s → %s ; école %s → %s.',
                    User::getRoleLabel((string) ($targetUser['role'] ?? '')),
                    User::getRoleLabel($role),
                    (string) ($targetUser['statut'] ?? 'inconnu'),
                    $statut,
                    (string) ($previousSchool['nom_etablissement'] ?? 'aucune'),
                    (string) ($newSchool['nom_etablissement'] ?? 'aucune')
                );
                ActivityLog::record(
                    (int) ($currentUser['id'] ?? 0),
                    'school_admin_access_changed',
                    'user',
                    $userId,
                    (string) ($targetUser['nom_complet'] ?? 'Compte utilisateur'),
                    $description
                );
            }
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
            $currentUser = Auth::user();
            $currentSchoolId = (int) ($currentUser['ecole_id'] ?? 0);
            $user = $userId > 0 ? User::findById($userId) : null;
            $isSuperAdmin = ($currentUser['role'] ?? '') === 'super_admin';
            $isSchoolManager = in_array(
                $currentUser['role'] ?? '',
                ['ecole_admin', 'promoteur_école'],
                true
            );
            $isPendingUnassignedAgent = $user
                && ($user['role'] ?? '') === 'agent_ecole'
                && ($user['statut'] ?? '') === 'Inactif'
                && (empty($user['ecole_id']) || (int) $user['ecole_id'] === 0);
            $hasSchoolAssignment = $user && (int) ($user['ecole_id'] ?? 0) > 0;
            $requiresSchoolAssignment = $user
                && in_array((string) ($user['role'] ?? ''), User::getSchoolScopedRoles(), true);
            $belongsToCurrentSchool = $user
                && $currentSchoolId > 0
                && (int) ($user['ecole_id'] ?? 0) === $currentSchoolId;
            $canValidate = $user
                && ($user['statut'] ?? '') === 'Inactif'
                && (
                    ($isSuperAdmin && (!$requiresSchoolAssignment || $hasSchoolAssignment))
                    || ($isSchoolManager && $currentSchoolId > 0 && ($belongsToCurrentSchool || $isPendingUnassignedAgent))
                );

            if ($canValidate) {
                if ($isPendingUnassignedAgent && $isSchoolManager) {
                    try {
                        Ecole::assertSubscriptionCapacity($currentSchoolId, 'staff');
                    } catch (\DomainException $exception) {
                        $_SESSION['utilisateurs_errors'] = [$exception->getMessage()];
                        $this->redirect('/utilisateurs');
                        return;
                    }
                    if (!User::assignToSchool($userId, $currentSchoolId)) {
                        $_SESSION['utilisateurs_errors'] = ['Impossible d’affecter ce compte à votre école.'];
                        $this->redirect('/utilisateurs');
                    }
                }

                if (!User::updateStatus($userId, 'Actif')) {
                    $_SESSION['utilisateurs_errors'] = ['Impossible de valider ce compte utilisateur.'];
                    $this->redirect('/utilisateurs');
                }
                if ($isSuperAdmin && ($user['role'] ?? '') === 'ecole_admin') {
                    ActivityLog::record(
                        (int) ($currentUser['id'] ?? 0),
                        'school_admin_validated',
                        'user',
                        $userId,
                        (string) ($user['nom_complet'] ?? 'Administrateur d’école'),
                        'Compte administrateur d’école activé.'
                    );
                }
                $_SESSION['utilisateurs_success'] = 'Compte utilisateur validé avec succès.';
            } else {
                $_SESSION['utilisateurs_errors'] = [
                    'Ce compte ne peut pas être validé depuis votre rôle ou votre école.'
                ];
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
                $account = User::findById($userId);
                $school = Ecole::findById($ecoleId);
                if ($account && $school && User::assignToSchool($userId, $ecoleId)) {
                    if (($account['role'] ?? '') === 'ecole_admin') {
                        ActivityLog::record(
                            (int) (Auth::user()['id'] ?? 0),
                            'school_admin_assigned',
                            'user',
                            $userId,
                            (string) ($account['nom_complet'] ?? 'Administrateur d’école'),
                            'Compte administrateur associé à l’école ' . (string) $school['nom_etablissement'] . '.'
                        );
                    }
                    $_SESSION['utilisateurs_success'] = 'Compte lié à l’école avec succès.';
                } else {
                    $_SESSION['utilisateurs_errors'] = ['Impossible de lier ce compte à l’école sélectionnée.'];
                }
            }
        }

        $this->redirect('/utilisateurs');
    }
}
