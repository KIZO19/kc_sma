<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Option;
use App\Models\Section;
use App\Models\User;

class OptionsController extends Controller
{
    private const CREATION_ROLES = [
        'promoteur_école',
        'préfet_école',
        'sec_école',
    ];

    private function canManage(string $role): bool
    {
        return in_array($role, self::CREATION_ROLES, true);
    }

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::CREATION_ROLES, ['super_admin', 'ecole_admin']));

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        if ($role === 'super_admin') {
            $options = Option::getAll();
        } else {
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $options = $ecoleId > 0 ? Option::getAllForSchool($ecoleId) : [];
        }

        $this->view('options/index', [
            'title' => APP_NAME . ' - Options',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'options' => $options,
            'canCreate' => $this->canManage($role),
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CREATION_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sections = Section::getAll();
        $oldInput = $_SESSION['option_old'] ?? [];
        unset($_SESSION['option_old']);

        $this->view('options/create', [
            'title' => APP_NAME . ' - Nouvelle option',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'sections' => $sections,
            'ecoleId' => $ecoleId,
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CREATION_ROLES);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/options');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = (string) ($user['role'] ?? '');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $nomOption = trim((string) ($_POST['nom_option'] ?? ''));
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $errors = $this->validateOptionData($ecoleId, $nomOption, $sectionId);
        if (empty($errors) && Option::nameExists($ecoleId, $sectionId, $nomOption)) {
            $errors[] = 'Une option portant ce nom existe déjà dans cette section.';
        }

        if (!empty($errors)) {
            $_SESSION['options_errors'] = $errors;
            $_SESSION['option_old'] = $_POST;
            $this->redirect('/options/create');
            return;
        }

        try {
            if (!Option::create([
                'ecole_id' => $ecoleId,
                'nom_option' => $nomOption,
                'section_id' => $sectionId,
            ])) {
                throw new \RuntimeException('Database refused option creation.');
            }
        } catch (\Throwable $e) {
            error_log('OptionsController::submit failed: ' . $e->getMessage());
            $_SESSION['options_errors'] = ['La création de l’option a échoué.'];
            $_SESSION['option_old'] = $_POST;
            $this->redirect('/options/create');
            return;
        }

        $_SESSION['options_success'] = 'Option créée avec succès.';
        $this->redirect('/options');
    }

    public function edit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CREATION_ROLES);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['options_errors'] = ['Option introuvable.'];
            $this->redirect('/options');
            return;
        }

        $option = Option::findById($id);
        if (!$option) {
            $_SESSION['options_errors'] = ['Option introuvable.'];
            $this->redirect('/options');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if ($role !== 'super_admin') {
            $option = $ecoleId > 0 ? Option::findByIdForSchool($id, $ecoleId) : null;
            if (!$option || !$this->canManage((string) $role)) {
                $_SESSION['options_errors'] = ['Cette option n’existe pas dans votre école ou vous n’êtes pas autorisé(e) à la modifier.'];
                $this->redirect('/options');
                return;
            }
        }

        $modules = $this->getModulesForRole($role);
        $sections = Section::getAll();
        $usage = Option::getUsage($id);

        $oldInput = $_SESSION['option_old'] ?? [];
        unset($_SESSION['option_old']);

        $this->view('options/edit', [
            'title' => APP_NAME . ' - Modifier l\'option',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'sections' => $sections,
            'option' => $option,
            'usage' => $usage,
            'oldInput' => $oldInput,
        ]);
    }

    public function update(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CREATION_ROLES);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/options');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = (string) ($user['role'] ?? '');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $id = (int) ($_POST['id'] ?? 0);
        $nomOption = trim($_POST['nom_option'] ?? '');
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $errors = $this->validateOptionData($ecoleId, $nomOption, $sectionId);

        if ($id <= 0) {
            $errors[] = 'Identifiant invalide.';
        }

        if (!empty($errors)) {
            $_SESSION['options_errors'] = $errors;
            $_SESSION['option_old'] = $_POST;
            $this->redirect('/options/edit?id=' . $id);
            return;
        }

        $option = $ecoleId > 0 ? Option::findByIdForSchool($id, $ecoleId) : null;
        if (!$option || !$this->canManage($role)) {
            $_SESSION['options_errors'] = ['Cette option n’existe pas dans votre école ou vous n’êtes pas autorisé(e) à la modifier.'];
            $this->redirect('/options');
            return;
        }
        if (Option::nameExists($ecoleId, $sectionId, $nomOption, $id)) {
            $_SESSION['options_errors'] = ['Une option portant ce nom existe déjà dans cette section.'];
            $_SESSION['option_old'] = $_POST;
            $this->redirect('/options/edit?id=' . $id);
            return;
        }

        $usage = Option::getUsage($id);
        if ((int) $option['section_id'] !== $sectionId && (int) $usage['classes'] > 0) {
            $_SESSION['options_errors'] = ['La section de cette option ne peut pas être modifiée tant qu’elle est utilisée par une classe.'];
            $_SESSION['option_old'] = $_POST;
            $this->redirect('/options/edit?id=' . $id);
            return;
        }

        try {
            if (!Option::update($id, [
                'ecole_id' => $ecoleId,
                'nom_option' => $nomOption,
                'section_id' => $sectionId,
            ])) {
                throw new \RuntimeException('Database refused option update.');
            }
        } catch (\Throwable $e) {
            error_log('OptionsController::update failed for option ID ' . $id . ': ' . $e->getMessage());
            $_SESSION['options_errors'] = ['La modification de l’option a échoué.'];
            $_SESSION['option_old'] = $_POST;
            $this->redirect('/options/edit?id=' . $id);
            return;
        }

        $_SESSION['options_success'] = 'Option mise à jour.';
        $this->redirect('/options');
    }

    public function destroy(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CREATION_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = (string) ($user['role'] ?? '');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['options_errors'] = ['Identifiant invalide.'];
            $this->redirect('/options');
            return;
        }
        $option = $ecoleId > 0 ? Option::findByIdForSchool($id, $ecoleId) : null;
        if (!$option || !$this->canManage($role)) {
            $_SESSION['options_errors'] = ['Cette option n’existe pas dans votre école ou vous n’êtes pas autorisé(e) à la supprimer.'];
            $this->redirect('/options');
            return;
        }
        $usage = Option::getUsage($id);
        if ((int) $usage['classes'] > 0 || (int) $usage['frais'] > 0) {
            $_SESSION['options_errors'] = ['Cette option ne peut pas être supprimée tant qu’elle est utilisée par une classe ou un frais scolaire.'];
            $this->redirect('/options/edit?id=' . $id);
            return;
        }

        try {
            if (!Option::delete($id, $ecoleId)) {
                throw new \RuntimeException('Database refused option deletion.');
            }
        } catch (\Throwable $e) {
            error_log('OptionsController::destroy failed for option ID ' . $id . ': ' . $e->getMessage());
            $_SESSION['options_errors'] = ['La suppression de l’option a échoué.'];
            $this->redirect('/options');
            return;
        }

        $_SESSION['options_success'] = 'Option supprimée.';
        $this->redirect('/options');
    }

    private function validateOptionData(int $ecoleId, string $nomOption, int $sectionId): array
    {
        $errors = [];
        if ($ecoleId <= 0) {
            $errors[] = 'École introuvable pour cet utilisateur.';
        }
        if ($nomOption === '') {
            $errors[] = 'Le nom de l’option est requis.';
        } elseif (strlen($nomOption) > 100) {
            $errors[] = 'Le nom de l’option ne peut pas dépasser 100 caractères.';
        }
        if ($sectionId <= 0 || !Section::findById($sectionId)) {
            $errors[] = 'La section sélectionnée est invalide.';
        }

        return $errors;
    }
}
