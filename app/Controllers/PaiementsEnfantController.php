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

        $this->view('paiements_enfant/index', [
            'title' => APP_NAME . ' - Paiements enfant',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'children' => Eleve::getChildrenForParent(
                (int) ($user['reference_id'] ?? 0),
                (int) ($user['ecole_id'] ?? 0)
            ),
            'paymentsByChild' => Eleve::getPaymentsForParent(
                (int) ($user['reference_id'] ?? 0),
                (int) ($user['ecole_id'] ?? 0)
            ),
        ]);
    }
}
