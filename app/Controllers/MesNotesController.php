<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\User;

class MesNotesController extends Controller
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

            $this->view('espace_eleve/notes', [
                'title' => APP_NAME . ' - Mes notes',
                'user' => $user,
                'role' => $role,
                'roleLabel' => User::getRoleLabel($role),
                'modules' => $modules,
                'student' => $student,
                'notes' => Eleve::getNotes($studentId),
            ]);
            return;
        }

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Mes notes',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Mes notes',
            'pageDescription' => 'Consultez vos notes scolaires.',
            'pageContent' => 'Historique des notes et moyennes.',
            'pageNotes' => 'Accessible aux élèves et aux parents.',
        ]);
    }
}
