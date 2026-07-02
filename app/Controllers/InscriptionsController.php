<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\ParentModel;
use App\Models\User;

class InscriptionsController extends Controller
{
    private const SUBMISSION_ROLES = [
        'super_admin',
        'agent_ecole',
        'comptable_école',
        'préfet_école',
        'DE_école',
        'DP_école',
        'DA_école',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'agent_ecole', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $pendingStudents = Eleve::getPending();

        $this->view('inscriptions/index', [
            'title' => APP_NAME . ' - Dossiers d’inscription',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pendingStudents' => $pendingStudents,
            'canSubmit' => in_array($role, self::SUBMISSION_ROLES, true),
            'canEdit' => in_array($role, self::SUBMISSION_ROLES, true),
            'canApprove' => in_array($role, ['super_admin', 'sec_école'], true),
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $parents = ParentModel::getAll();
        $oldInput = $_SESSION['inscriptions_old'] ?? [];
        unset($_SESSION['inscriptions_old']);

        $this->view('inscriptions/create', [
            'title' => APP_NAME . ' - Nouvelle inscription',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'parents' => $parents,
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $postnom = trim($_POST['postnom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $dateNaissance = trim($_POST['date_naissance'] ?? '');
            $matricule = trim($_POST['matricule'] ?? '');
            $parentId = (int) ($_POST['parent_id'] ?? 0);

            $errors = [];
            if ($nom === '') {
                $errors[] = 'Le nom de l’élève est requis.';
            }
            if ($postnom === '') {
                $errors[] = 'Le postnom de l’élève est requis.';
            }
            if ($genre === '') {
                $errors[] = 'Le genre de l’élève est requis.';
            }
            if ($dateNaissance === '') {
                $errors[] = 'La date de naissance est requise.';
            }

            if (empty($matricule)) {
                $matricule = $this->generateMatricule($nom . ' ' . $postnom);
            }

            if (empty($errors)) {
                Eleve::create([
                    'matricule' => $matricule,
                    'nom' => $nom,
                    'postnom' => $postnom,
                    'prenom' => $prenom ?: null,
                    'genre' => $genre,
                    'lieu_naissance' => trim($_POST['lieu_naissance'] ?? null),
                    'nationalite' => trim($_POST['nationalite'] ?? 'CONGOLAISE'),
                    'adresse' => trim($_POST['adresse'] ?? null),
                    'date_naissance' => $dateNaissance,
                    'parent_id' => $parentId > 0 ? $parentId : null,
                    'nom_pere' => trim($_POST['nom_pere'] ?? null),
                    'nom_mere' => trim($_POST['nom_mere'] ?? null),
                    'province_origine' => trim($_POST['province_origine'] ?? null),
                    'territoire' => trim($_POST['territoire'] ?? null),
                    'secteur' => trim($_POST['secteur'] ?? null),
                    'groupement' => trim($_POST['groupement'] ?? null),
                    'village' => trim($_POST['village'] ?? null),
                    'num_permanent' => trim($_POST['num_permanent'] ?? null),
                    'statut_eleve' => 'inactif',
                ]);
                unset($_SESSION['inscriptions_old']);
                $_SESSION['inscriptions_success'] = 'L’élève a été enregistré. La validation doit être effectuée par le secrétaire.';
                $this->redirect('/inscriptions');
            }

            $_SESSION['inscriptions_errors'] = $errors;
            $_SESSION['inscriptions_old'] = $_POST;
        }

        $this->redirect('/inscriptions/create');
    }

    public function edit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        $eleveId = (int) ($_GET['id'] ?? 0);
        if ($eleveId <= 0) {
            $this->redirect('/inscriptions');
        }

        $student = Eleve::findById($eleveId);
        if (!$student) {
            $_SESSION['inscriptions_errors'] = ['Élève introuvable.'];
            $this->redirect('/inscriptions');
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $parents = ParentModel::getAll();
        $oldInput = $_SESSION['inscriptions_old'] ?? [];
        unset($_SESSION['inscriptions_old']);

        $this->view('inscriptions/edit', [
            'title' => APP_NAME . ' - Éditer inscription',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'student' => $student,
            'parents' => $parents,
            'oldInput' => $oldInput,
        ]);
    }

    public function update(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eleveId = (int) ($_POST['eleve_id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $postnom = trim($_POST['postnom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $dateNaissance = trim($_POST['date_naissance'] ?? '');
            $matricule = trim($_POST['matricule'] ?? '');
            $parentId = (int) ($_POST['parent_id'] ?? 0);

            $errors = [];
            if ($eleveId <= 0) {
                $errors[] = 'Identifiant de l’élève invalide.';
            }
            if ($nom === '') {
                $errors[] = 'Le nom de l’élève est requis.';
            }
            if ($postnom === '') {
                $errors[] = 'Le postnom de l’élève est requis.';
            }
            if ($genre === '') {
                $errors[] = 'Le genre de l’élève est requis.';
            }
            if ($dateNaissance === '') {
                $errors[] = 'La date de naissance est requise.';
            }

            $student = Eleve::findById($eleveId);
            if (!$student) {
                $errors[] = 'Élève introuvable.';
            }

            if (empty($matricule)) {
                $matricule = $student['matricule'] ?? $this->generateMatricule($nom . ' ' . $postnom);
            }

            if (empty($errors)) {
                Eleve::update($eleveId, [
                    'matricule' => $matricule,
                    'nom' => $nom,
                    'postnom' => $postnom,
                    'prenom' => $prenom ?: null,
                    'genre' => $genre,
                    'lieu_naissance' => trim($_POST['lieu_naissance'] ?? null),
                    'nationalite' => trim($_POST['nationalite'] ?? 'CONGOLAISE'),
                    'adresse' => trim($_POST['adresse'] ?? null),
                    'date_naissance' => $dateNaissance,
                    'parent_id' => $parentId > 0 ? $parentId : null,
                    'nom_pere' => trim($_POST['nom_pere'] ?? null),
                    'nom_mere' => trim($_POST['nom_mere'] ?? null),
                    'province_origine' => trim($_POST['province_origine'] ?? null),
                    'territoire' => trim($_POST['territoire'] ?? null),
                    'secteur' => trim($_POST['secteur'] ?? null),
                    'groupement' => trim($_POST['groupement'] ?? null),
                    'village' => trim($_POST['village'] ?? null),
                    'num_permanent' => trim($_POST['num_permanent'] ?? null),
                ]);
                unset($_SESSION['inscriptions_old']);
                $_SESSION['inscriptions_success'] = 'Les informations de l’inscription ont été mises à jour.';
                $this->redirect('/inscriptions');
            }

            $_SESSION['inscriptions_errors'] = $errors;
            $_SESSION['inscriptions_old'] = $_POST;
            $this->redirect('/inscriptions/edit?id=' . $eleveId);
        }

        $this->redirect('/inscriptions');
    }

    public function approve(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'sec_école']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eleveId = (int) ($_POST['eleve_id'] ?? 0);
            if ($eleveId > 0) {
                Eleve::approve($eleveId);
                $_SESSION['inscriptions_success'] = 'Inscription validée par le secrétaire.';
            }
        }

        $this->redirect('/inscriptions');
    }

    private function generateMatricule(string $name): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', mb_substr($name, 0, 6, 'UTF-8')));
        $prefix = $prefix === '' ? 'ELEVE' : $prefix;
        return sprintf('%s-%s-%s', $prefix, date('YmdHis'), substr(bin2hex(random_bytes(3)), 0, 6));
    }
}
