<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Devise;
use App\Models\Ecole;
use App\Models\User;

class DevisesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $school = Ecole::findById((int) ($user['ecole_id'] ?? 0)) ?: [];
        $devises = Devise::getAll();

        $this->view('devises/index', [
            'title' => APP_NAME . ' - Devises',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'devises' => $devises,
            'school' => $school,
            'currencyOptions' => $this->getCurrencyOptions(),
        ]);
    }

    public function store(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/devises');
            return;
        }

        $formType = trim($_POST['form_type'] ?? '');
        $errors = [];

        if ($formType === 'default_currency') {
            $devise = strtoupper(trim($_POST['devise_principale'] ?? ''));
            if ($devise === '' || !array_key_exists($devise, $this->getCurrencyOptions())) {
                $errors[] = 'La devise principale est invalide.';
            }

            if (empty($errors)) {
                $user = Auth::refresh() ?: Auth::user();
                $schoolId = (int) ($user['ecole_id'] ?? 0);
                if ($schoolId > 0) {
                    Ecole::updateDefaultCurrency($schoolId, $devise);
                    $_SESSION['devises_success'] = 'Devise principale mise à jour.';
                } else {
                    $errors[] = 'Impossible de déterminer l’école.';
                }
            }
        } else {
            $id = (int) ($_POST['id'] ?? 0);
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $libelle = trim($_POST['libelle'] ?? '');
            $taux = (float) ($_POST['taux'] ?? 0);
            $actif = isset($_POST['actif']) ? 1 : 0;

            if ($code === '') {
                $errors[] = 'Le code de la devise est requis.';
            }
            if ($libelle === '') {
                $errors[] = 'Le libellé de la devise est requis.';
            }
            if ($taux <= 0) {
                $errors[] = 'Le taux de change doit être supérieur à zéro.';
            }

            if (empty($errors)) {
                $existing = Devise::findByCode($code);
                if ($id === 0 && $existing !== null) {
                    $errors[] = 'Le code de la devise « ' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . ' » existe déjà.';
                }
                if ($id > 0 && $existing !== null && (int) $existing['id'] !== $id) {
                    $errors[] = 'Le code de la devise « ' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . ' » est déjà utilisé par un autre taux.';
                }
            }

            if (empty($errors)) {
                if ($id > 0) {
                    if (!Devise::update($id, ['code' => $code, 'libelle' => $libelle, 'taux' => $taux, 'actif' => $actif])) {
                        $errors[] = 'Impossible de mettre à jour le taux de change.';
                    }
                } else {
                    if (!Devise::create(['code' => $code, 'libelle' => $libelle, 'taux' => $taux, 'actif' => $actif])) {
                        $errors[] = 'Impossible de créer le taux de change.';
                    }
                }
            }

            if (empty($errors)) {
                $_SESSION['devises_success'] = $id > 0 ? 'Taux de change mis à jour.' : 'Taux de change ajouté.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['devises_errors'] = $errors;
        }

        $this->redirect('/devises');
    }

    private function getCurrencyOptions(): array
    {
        return [
            'USD' => 'Dollar américain',
            'EUR' => 'Euro',
            'CDF' => 'Franc congolais',
            'XAF' => 'Franc CFA BEAC',
            'XOF' => 'Franc CFA BCEAO',
        ];
    }
}
