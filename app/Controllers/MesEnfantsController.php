<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\ParentModel;
use App\Models\User;

class MesEnfantsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['parent_ecole', 'parent_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $parentId = (int) ($user['reference_id'] ?? 0);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if (empty($_SESSION['parent_child_request_csrf'])) {
            $_SESSION['parent_child_request_csrf'] = bin2hex(random_bytes(32));
        }
        $children = Eleve::getChildrenForParent(
            $parentId,
            $schoolId
        );
        $paymentsByChild = Eleve::getPaymentsForParent(
            $parentId,
            $schoolId
        );
        foreach ($children as &$child) {
            $childId = (int) $child['id'];
            $child['notes'] = Eleve::getNotes($childId);
            $child['discipline'] = Eleve::getDiscipline($childId);
            $child['payment_summary'] = $paymentsByChild[$childId] ?? ['payments' => [], 'total_by_currency' => []];
        }
        unset($child);

        $this->view('mes_enfants/index', [
            'title' => APP_NAME . ' - Enfants',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'children' => $children,
            'pendingChildRequests' => ParentModel::getPendingChildRequestsForParent($parentId, $schoolId),
            'csrfToken' => (string) $_SESSION['parent_child_request_csrf'],
        ]);
    }

    public function respondToRequest(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['parent_ecole', 'parent_école']);

        $user = Auth::refresh() ?: Auth::user();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/mes-enfants');
        }

        $token = (string) ($_POST['csrf_token'] ?? '');
        if ($token === '' || !hash_equals((string) ($_SESSION['parent_child_request_csrf'] ?? ''), $token)) {
            $_SESSION['mes_enfants_errors'] = ['Votre session a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/mes-enfants');
        }

        $decision = (string) ($_POST['decision'] ?? '');
        try {
            ParentModel::respondToChildAssignmentRequest(
                (int) ($_POST['request_id'] ?? 0),
                (int) ($user['reference_id'] ?? 0),
                (int) ($user['ecole_id'] ?? 0),
                $decision
            );
            $_SESSION['mes_enfants_success'] = $decision === 'accepted'
                ? 'La demande a été acceptée. L’élève apparaît maintenant dans votre liste.'
                : 'La demande a été refusée.';
            $_SESSION['parent_child_request_csrf'] = bin2hex(random_bytes(32));
        } catch (\DomainException $e) {
            $_SESSION['mes_enfants_errors'] = [$e->getMessage()];
        } catch (\Throwable $e) {
            error_log('MesEnfantsController::respondToRequest error: ' . $e->getMessage());
            $_SESSION['mes_enfants_errors'] = ['Impossible de traiter cette demande pour le moment.'];
        }

        $this->redirect('/mes-enfants');
    }
}
