<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DetteEleve;
use App\Models\Eleve;
use App\Models\User;

class MesPaiementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['eleve_ecole', 'eleve_école', 'parent_ecole', 'parent_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        if (in_array($role, ['eleve_ecole', 'eleve_école'], true)) {
            $studentId = (int) ($user['reference_id'] ?? 0);
            $schoolId = (int) ($user['ecole_id'] ?? 0);
            $student = $studentId > 0 && $schoolId > 0
                ? Eleve::findByIdAndSchool($studentId, $schoolId)
                : null;
            if (!$student) {
                $this->redirect('/error/accessDenied');
            }

            $payments = array_values(array_filter(
                Eleve::getAccountingEntries($studentId),
                static fn (array $entry): bool => strtoupper((string) ($entry['type_mouvement'] ?? '')) === 'CREDIT'
                    && empty($entry['annulee_at'])
            ));

            $this->view('espace_eleve/paiements', [
                'title' => APP_NAME . ' - Mes paiements',
                'user' => $user,
                'role' => $role,
                'roleLabel' => User::getRoleLabel($role),
                'modules' => $modules,
                'student' => $student,
                'payments' => $payments,
                'outstandingByCurrency' => DetteEleve::getTotalOutstandingGroupedByDevise($studentId),
            ]);
            return;
        }

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Mes paiements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Mes paiements',
            'pageDescription' => 'Suivez vos paiements et reçus.',
            'pageContent' => 'Détails des paiements effectués.',
            'pageNotes' => 'Pour les élèves et les parents.',
        ]);
    }
}
