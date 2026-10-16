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

    private const MANAGEMENT_ROLES = [
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
            'canManage' => in_array($role, self::MANAGEMENT_ROLES, true),
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::CREATION_ROLES, self::PRIMARY_ROLES));

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sections = Section::getAll();
        $options = $ecoleId > 0 ? Option::getAllForSchool($ecoleId) : [];
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
            'isEditing' => false,
            'classe' => [],
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::CREATION_ROLES, self::PRIMARY_ROLES));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/classes');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $nomClasse = trim((string) ($_POST['nom_classe'] ?? ''));
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $optionId = (int) ($_POST['option_id'] ?? 0);
        $errors = $this->validateClassData($ecoleId, $role, $nomClasse, $sectionId, $optionId);

        if (empty($errors) && Classe::nameExists($ecoleId, $sectionId, $optionId, $nomClasse)) {
            $errors[] = 'Une classe portant ce nom existe déjà dans cette section et cette option.';
        }

        if (!empty($errors)) {
            $_SESSION['classes_errors'] = $errors;
            $_SESSION['class_old'] = $_POST;
            $this->redirect('/classes/create');
            return;
        }

        try {
            if (!Classe::create([
                'ecole_id' => $ecoleId,
                'nom_classe' => $nomClasse,
                'section_id' => $sectionId,
                'option_id' => $optionId > 0 ? $optionId : null,
            ])) {
                throw new \RuntimeException('Database refused class creation.');
            }
        } catch (\Throwable $e) {
            error_log('ClassesController::submit failed: ' . $e->getMessage());
            $_SESSION['classes_errors'] = ['La création de la classe a échoué. Vérifiez les informations et réessayez.'];
            $_SESSION['class_old'] = $_POST;
            $this->redirect('/classes/create');
            return;
        }

        $_SESSION['classes_success'] = 'Classe créée avec succès.';
        $this->redirect('/classes');
    }

    public function edit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $id = (int) ($_GET['id'] ?? 0);
        $classe = $id > 0 && $ecoleId > 0 ? Classe::findByIdForSchool($id, $ecoleId) : null;
        if (!$classe) {
            $_SESSION['classes_errors'] = ['Classe introuvable ou non rattachée à votre école.'];
            $this->redirect('/classes');
            return;
        }

        $role = $user['role'] ?? 'default';
        $oldInput = $_SESSION['class_old'] ?? [];
        unset($_SESSION['class_old']);
        $this->view('classes/create', [
            'title' => APP_NAME . ' - Modifier une classe',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'sections' => Section::getAll(),
            'options' => Option::getAllForSchool($ecoleId),
            'oldInput' => $oldInput,
            'isEditing' => true,
            'classe' => $classe,
        ]);
    }

    public function update(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/classes');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $id = (int) ($_POST['id'] ?? 0);
        $classe = $id > 0 && $ecoleId > 0 ? Classe::findByIdForSchool($id, $ecoleId) : null;
        if (!$classe) {
            $_SESSION['classes_errors'] = ['Classe introuvable ou non rattachée à votre école.'];
            $this->redirect('/classes');
            return;
        }

        $nomClasse = trim((string) ($_POST['nom_classe'] ?? ''));
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $optionId = (int) ($_POST['option_id'] ?? 0);
        $errors = $this->validateClassData($ecoleId, (string) ($user['role'] ?? ''), $nomClasse, $sectionId, $optionId);
        if (Classe::nameExists($ecoleId, $sectionId, $optionId, $nomClasse, $id)) {
            $errors[] = 'Une classe portant ce nom existe déjà dans cette section et cette option.';
        }

        $usage = Classe::getUsage($id);
        $hasHistory = array_sum(array_map('intval', $usage)) > 0;
        $oldOptionId = (int) ($classe['option_id'] ?? 0);
        if ($hasHistory && (
            (int) $classe['section_id'] !== $sectionId
            || $oldOptionId !== $optionId
        )) {
            $errors[] = 'La section ou l’option ne peut pas être modifiée, car cette classe possède déjà des inscriptions, des frais ou des cours attribués.';
        }

        if (!empty($errors)) {
            $_SESSION['classes_errors'] = $errors;
            $_SESSION['class_old'] = $_POST;
            $this->redirect('/classes/edit?id=' . $id);
            return;
        }

        try {
            if (!Classe::update($id, [
                'ecole_id' => $ecoleId,
                'nom_classe' => $nomClasse,
                'section_id' => $sectionId,
                'option_id' => $optionId > 0 ? $optionId : null,
            ])) {
                throw new \RuntimeException('Database refused class update.');
            }
        } catch (\Throwable $e) {
            error_log('ClassesController::update failed for class ID ' . $id . ': ' . $e->getMessage());
            $_SESSION['classes_errors'] = ['La modification de la classe a échoué.'];
            $_SESSION['class_old'] = $_POST;
            $this->redirect('/classes/edit?id=' . $id);
            return;
        }

        $_SESSION['classes_success'] = 'Classe modifiée avec succès.';
        $this->redirect('/classes');
    }

    public function destroy(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/classes');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $id = (int) ($_POST['id'] ?? 0);
        $classe = $id > 0 && $ecoleId > 0 ? Classe::findByIdForSchool($id, $ecoleId) : null;
        if (!$classe) {
            $_SESSION['classes_errors'] = ['Classe introuvable ou non rattachée à votre école.'];
            $this->redirect('/classes');
            return;
        }

        $usage = Classe::getUsage($id);
        if (array_sum(array_map('intval', $usage)) > 0) {
            $_SESSION['classes_errors'] = ['Cette classe ne peut pas être supprimée car elle possède des inscriptions, des frais ou des cours attribués.'];
            $this->redirect('/classes');
            return;
        }

        try {
            if (!Classe::delete($id, $ecoleId)) {
                throw new \RuntimeException('Database refused class deletion.');
            }
        } catch (\Throwable $e) {
            error_log('ClassesController::destroy failed for class ID ' . $id . ': ' . $e->getMessage());
            $_SESSION['classes_errors'] = ['La suppression de la classe a échoué.'];
            $this->redirect('/classes');
            return;
        }

        $_SESSION['classes_success'] = 'Classe supprimée avec succès.';
        $this->redirect('/classes');
    }

    private function validateClassData(int $ecoleId, string $role, string $nomClasse, int $sectionId, int $optionId): array
    {
        $errors = [];
        if ($ecoleId <= 0) {
            $errors[] = 'École introuvable pour cet utilisateur.';
        }
        if ($nomClasse === '') {
            $errors[] = 'Le nom de la classe est requis.';
        } elseif (strlen($nomClasse) > 100) {
            $errors[] = 'Le nom de la classe ne peut pas dépasser 100 caractères.';
        }
        if ($sectionId <= 0 || !Section::findById($sectionId)) {
            $errors[] = 'La section sélectionnée est invalide.';
        } elseif (!$this->isAllowedClassLevel($nomClasse, $sectionId)) {
            $errors[] = 'Le niveau doit respecter la section : maternelle 1 à 3, primaire 1 à 6, secondaire 7e, 8e ou 1e à 4e.';
        }
        if (!in_array($role, self::PRIMARY_ROLES, true) && $sectionId !== 1 && $sectionId !== 2) {
            $errors[] = 'Vous n’êtes pas autorisé à créer une classe hors primaire ou maternelle.';
        }
        if ($optionId > 0) {
            $option = $ecoleId > 0 ? Option::findByIdForSchool($optionId, $ecoleId) : null;
            if (!$option || (int) ($option['section_id'] ?? 0) !== $sectionId) {
                $errors[] = 'L’option sélectionnée est invalide ou ne correspond pas à cette section.';
            }
        }

        return $errors;
    }

    private function isAllowedClassLevel(string $className, int $sectionId): bool
    {
        if (!preg_match('/^\s*(\d+)\s*(?:e|er|ère|ere|ème|eme|ième|ieme)?/iu', trim($className), $matches)) {
            return false;
        }

        $level = (int) $matches[1];
        return match ($sectionId) {
            1 => $level >= 1 && $level <= 3,
            2 => $level >= 1 && $level <= 6,
            3 => $level === 7 || $level === 8 || ($level >= 1 && $level <= 4),
            default => false,
        };
    }
}
