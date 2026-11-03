<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ParentModel;
use App\Models\User;

class ParentsController extends Controller
{
    private const PROFILE_ACCESS_ROLES = [
        'super_admin',
        'ecole_admin',
        'promoteur_école',
        'préfet_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
        'sec_école',
        'comptable_école',
        'enseignant_école',
    ];

    private const PARENT_LIST_ACCESS_ROLES = [
        'super_admin',
        'ecole_admin',
        'promoteur_école',
        'préfet_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
        'sec_école',
        'comptable_école',
    ];

    private const CHILD_ASSIGNMENT_ROLES = [
        'super_admin',
        'ecole_admin',
        'promoteur_école',
        'préfet_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
        'sec_école',
        'comptable_école',
    ];

    public function show(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::PROFILE_ACCESS_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $parentId = (int) ($_GET['id'] ?? 0);
        $role = $user['role'] ?? 'default';
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $canAssignChildren = in_array($role, self::CHILD_ASSIGNMENT_ROLES, true);
        $parent = $role === 'super_admin'
            ? ParentModel::findById($parentId)
            : ($schoolId > 0
                ? ParentModel::findByIdAndSchool($parentId, $schoolId, $sectionScopeId)
                : null);

        if (!$parent) {
            $this->redirect('/error/notFound');
        }

        if ($role === 'super_admin') {
            $schoolId = (int) ($parent['ecole_id'] ?? 0);
        }
        $children = ParentModel::getChildren(
            $parentId,
            $role === 'super_admin' ? null : $schoolId,
            $sectionScopeId
        );
        $csrfToken = null;
        $unassignedChildren = [];
        if ($canAssignChildren) {
            if (empty($_SESSION['parents_assign_csrf'])) {
                $_SESSION['parents_assign_csrf'] = bin2hex(random_bytes(32));
            }
            $csrfToken = (string) $_SESSION['parents_assign_csrf'];
            $unassignedChildren = ParentModel::getUnassignedChildrenBySchool(
                $schoolId,
                $role === 'super_admin' ? null : $sectionScopeId
            );
        }

        $this->view('parents/show', [
            'title' => APP_NAME . ' - Profil parent',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'parent' => $parent,
            'children' => $children,
            'childrenFinancialSummaries' => ParentModel::getChildrenFinancialSummaries($children),
            'pendingChildRequests' => ParentModel::getPendingChildRequestsForParentProfile(
                $parentId,
                (int) ($parent['ecole_id'] ?? 0),
                $role === 'super_admin' ? null : $sectionScopeId
            ),
            'canAssignChildren' => $canAssignChildren,
            'unassignedChildren' => $unassignedChildren,
            'csrfToken' => $csrfToken,
        ]);
    }

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::PARENT_LIST_ACCESS_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $parents = $role === 'super_admin'
            ? ParentModel::getAll()
            : ($schoolId > 0 ? ParentModel::getAllBySchool($schoolId, $sectionScopeId) : []);

        $this->view('parents/index', [
            'title' => APP_NAME . ' - Parents',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'parents' => $parents,
        ]);
    }

    public function assignChild(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CHILD_ASSIGNMENT_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $parentId = (int) ($_POST['parent_id'] ?? 0);
        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $role === 'super_admin' ? null : $this->getSectionScopeForUser($user);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/parents/show?id=' . $parentId);
        }
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if ($postedToken === '' || !hash_equals((string) ($_SESSION['parents_assign_csrf'] ?? ''), $postedToken)) {
            $_SESSION['parents_errors'] = ['Votre session a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/parents/show?id=' . $parentId);
        }

        if ($role === 'super_admin') {
            $parent = ParentModel::findById($parentId);
            $schoolId = (int) ($parent['ecole_id'] ?? 0);
        } else {
            $parent = $schoolId > 0 ? ParentModel::findByIdAndSchool($parentId, $schoolId) : null;
        }

        if (!$parent || $schoolId <= 0) {
            $_SESSION['parents_errors'] = ['Parent introuvable dans cette école.'];
            $this->redirect('/parents/show?id=' . $parentId);
        }

        try {
            ParentModel::createChildAssignmentRequest(
                $parentId,
                $eleveId,
                $schoolId,
                (int) ($user['id'] ?? 0),
                $sectionScopeId
            );
            $_SESSION['parents_success'] = 'La demande a été envoyée au parent pour validation.';
        } catch (\DomainException $e) {
            $_SESSION['parents_errors'] = [$e->getMessage()];
        } catch (\Throwable $e) {
            error_log('ParentsController::assignChild error: ' . $e->getMessage());
            $_SESSION['parents_errors'] = ['Impossible d’envoyer la demande pour le moment.'];
        }

        $this->redirect('/parents/show?id=' . $parentId);
    }
}