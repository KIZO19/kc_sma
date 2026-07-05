<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Classe;
use App\Models\Option;
use App\Models\Section;
use App\Models\User;

class ClassesController extends Controller
{
    private const CREATION_ROLES = [
        'promoteur_école',
        'préfet_école',
        'sec_école',
    ];

    private const PRIMARY_ROLES = [
        'promoteur_école',
        'préfet_école',
        'DE_école',
        'DA_école',
        'sec_école',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::CREATION_ROLES, self::PRIMARY_ROLES, ['super_admin', 'ecole_admin']));

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $classes = [];

        if ($ecoleId > 0) {
            $classes = Classe::getAllBySchool($ecoleId);
        }

        $this->view('classes/index', [
            'title' => APP_NAME . ' - Classes',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'classes' => $classes,
            'canCreate' => in_array($role, self::CREATION_ROLES, true) || in_array($role, self::PRIMARY_ROLES, true),
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::CREATION_ROLES, self::PRIMARY_ROLES));

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $sections = Section::getAll();
        $options = Option::getAll();
        $oldInput = $_SESSION['class_old'] ?? [];
        unset($_SESSION['class_old']);

        $this->view('classes/create', [
            'title' => APP_NAME . ' - Nouvelle classe',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'sections' => $sections,
            'options' => $options,
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::CREATION_ROLES, self::PRIMARY_ROLES));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::refresh() ?: Auth::user();
            $role = $user['role'] ?? 'default';
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $nomClasse = trim($_POST['nom_classe'] ?? '');
            $sectionId = (int) ($_POST['section_id'] ?? 0);
            $optionId = (int) ($_POST['option_id'] ?? 0);

            $errors = [];
            if ($ecoleId <= 0) {
                $errors[] = 'École introuvable pour cet utilisateur.';
            }
            if ($nomClasse === '') {
                $errors[] = 'Le nom de la classe est requis.';
            }
            if ($sectionId <= 0) {
                $errors[] = 'La section est requise.';
            }

            if (!in_array($role, self::PRIMARY_ROLES, true) && $sectionId !== 1 && $sectionId !== 2) {
                $errors[] = 'Vous n’êtes pas autorisé à créer une classe hors primaire ou maternelle.';
            }

            if ($sectionId === 1 || $sectionId === 2) {
                if (!in_array($role, self::PRIMARY_ROLES, true) && !in_array($role, self::CREATION_ROLES, true)) {
                    $errors[] = 'Vous n’êtes pas autorisé à créer une classe pour la maternelle ou le primaire.';
                }
            }

            if (empty($errors)) {
                Classe::create([
                    'ecole_id' => $ecoleId,
                    'nom_classe' => $nomClasse,
                    'section_id' => $sectionId,
                    'option_id' => $optionId > 0 ? $optionId : null,
                ]);
                $_SESSION['classes_success'] = 'Classe créée avec succès.';
                $this->redirect('/classes');
                return;
            }

            $_SESSION['classes_errors'] = $errors;
            $_SESSION['class_old'] = $_POST;
        }

        $this->redirect('/classes/create');
    }
}
