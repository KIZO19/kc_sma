<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\User;

class PaiementsEnfantController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['parent_ecole', 'parent_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $parentId = (int) ($user['reference_id'] ?? 0);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $children = Eleve::getChildrenForParent($parentId, $schoolId);

        $this->view('paiements_enfant/index', [
            'title' => APP_NAME . ' - Paiements enfant',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'children' => $children,
            'paymentsByChild' => Eleve::getPaymentsForParent($parentId, $schoolId),
            'solvencyByChild' => Eleve::getOutstandingByChild($children),
        ]);
    }
}
