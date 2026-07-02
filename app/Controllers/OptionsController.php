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
        $options = Option::getAll();

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
}
