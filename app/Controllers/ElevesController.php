<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\User;

class ElevesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $students = $ecoleId > 0 ? Eleve::getAllBySchool($ecoleId) : Eleve::getAll();

        $this->view('eleves/index', [
            'title' => $this->buildSchoolPageTitle('Élèves', $user),
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'students' => $students,
        ]);
    }

    private function buildSchoolPageTitle(string $pageLabel, array $user): string
    {
        $schoolName = APP_NAME;
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        if ($ecoleId > 0) {
            $school = \App\Models\Ecole::findById($ecoleId);
            if (!empty($school['nom_etablissement'])) {
                $schoolName = $school['nom_etablissement'];
            }
        }

        return $schoolName . ' - ' . $pageLabel;
    }

    public function show(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $eleveId = (int) ($_GET['id'] ?? 0);

        if ($eleveId <= 0) {
            header('Location: ' . BASE_URL . '/eleves');
            exit;
        }

        // Ensure the eleve belongs to the same school unless super_admin
        if (($user['role'] ?? '') !== 'super_admin' && $ecoleId > 0 && !\App\Models\Eleve::findByIdAndSchool($eleveId, $ecoleId)) {
            header('Location: ' . BASE_URL . '/error/accessDenied');
            exit;
        }

        $eleve = \App\Models\Eleve::findById($eleveId);
        if (!$eleve) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        $compte = \App\Models\Eleve::getAccount($eleveId);
        $ecritures = \App\Models\Eleve::getAccountingEntries($eleveId);
        $notes = \App\Models\Eleve::getNotes($eleveId);
        $discipline = \App\Models\Eleve::getDiscipline($eleveId);

        $this->view('eleves/show', [
            'title' => $this->buildSchoolPageTitle('Fiche élève', $user),
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'eleve' => $eleve,
            'compte' => $compte,
            'ecritures' => $ecritures,
            'notes' => $notes,
            'discipline' => $discipline,
        ]);
    }
}
