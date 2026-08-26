<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Option;
use App\Models\User;

class OptionsController extends Controller
{
    private const CREATION_ROLES = [
        'promoteur_école',
        'préfet_école',
        'sec_école',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::CREATION_ROLES, ['super_admin', 'ecole_admin']));

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        // Only show options relevant to the user's school unless super_admin
        if (($role === 'super_admin') || ($role === 'ecole_admin' && empty($user['ecole_id']))) {
            $options = Option::getAll();
        } else {
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $options = $ecoleId > 0 ? Option::getAllForSchool($ecoleId) : Option::getAll();
        }

        $this->view('options/index', [
            'title' => APP_NAME . ' - Options',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'options' => $options,
            'canCreate' => in_array($role, self::CREATION_ROLES, true),
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CREATION_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $oldInput = $_SESSION['option_old'] ?? [];
        unset($_SESSION['option_old']);

        $this->view('options/create', [
            'title' => APP_NAME . ' - Nouvelle option',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CREATION_ROLES);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nomOption = trim($_POST['nom_option'] ?? '');
            $errors = [];

            if ($nomOption === '') {
                $errors[] = 'Le nom de l’option est requis.';
            }

            if (empty($errors)) {
                Option::create(['nom_option' => $nomOption]);
                $_SESSION['options_success'] = 'Option créée avec succès.';
                $this->redirect('/options');
                return;
            }

            $_SESSION['options_errors'] = $errors;
            $_SESSION['option_old'] = $_POST;
        }

        $this->redirect('/options/create');
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

        // Authorization: only allow editing if option is linked to user's school or user is super_admin
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if ($role !== 'super_admin') {
            $linked = $ecoleId > 0 ? Option::isLinkedToSchool((int) $option['id'], $ecoleId) : false;
            if (!$linked) {
                $_SESSION['options_errors'] = ['Vous n\'êtes pas autorisé(e) à modifier cette option.'];
                $this->redirect('/options');
                return;
            }
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $oldInput = $_SESSION['option_old'] ?? [];
        unset($_SESSION['option_old']);

        $this->view('options/edit', [
            'title' => APP_NAME . ' - Modifier l\'option',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'option' => $option,
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

        $id = (int) ($_POST['id'] ?? 0);
        $nomOption = trim($_POST['nom_option'] ?? '');
        $errors = [];

        if ($id <= 0) {
            $errors[] = 'Identifiant invalide.';
        }
        if ($nomOption === '') {
            $errors[] = 'Le nom de l’option est requis.';
        }

        if (!empty($errors)) {
            $_SESSION['options_errors'] = $errors;
            $_SESSION['option_old'] = $_POST;
            $this->redirect('/options/edit?id=' . $id);
            return;
        }
        // Authorization: ensure user can update this option
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if ($role !== 'super_admin') {
            $linked = $ecoleId > 0 ? Option::isLinkedToSchool($id, $ecoleId) : false;
            if (!$linked) {
                $_SESSION['options_errors'] = ['Vous n\'êtes pas autorisé(e) à modifier cette option.'];
                $this->redirect('/options');
                return;
            }
        }

        Option::update($id, ['nom_option' => $nomOption]);
        $_SESSION['options_success'] = 'Option mise à jour.';
        $this->redirect('/options');
    }

    public function destroy(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::CREATION_ROLES);

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['options_errors'] = ['Identifiant invalide.'];
            $this->redirect('/options');
            return;
        }
        // Authorization: ensure user can delete this option
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if ($role !== 'super_admin') {
            $linked = $ecoleId > 0 ? Option::isLinkedToSchool($id, $ecoleId) : false;
            if (!$linked) {
                $_SESSION['options_errors'] = ['Vous n\'êtes pas autorisé(e) à supprimer cette option.'];
                $this->redirect('/options');
                return;
            }
        }

        Option::delete($id);
        $_SESSION['options_success'] = 'Option supprimée.';
        $this->redirect('/options');
    }
}
