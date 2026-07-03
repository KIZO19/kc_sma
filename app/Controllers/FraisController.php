<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\FraisScolaire;
use App\Models\User;

class FraisController extends Controller
{
    private const MANAGEMENT_ROLES = ['super_admin', 'ecole_admin', 'comptable_école'];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $fees = $ecoleId > 0 ? FraisScolaire::getAllBySchool($ecoleId) : [];
        $school = $ecoleId > 0 ? \App\Models\Ecole::findById($ecoleId) : null;
        $schoolCurrency = $school['devise_principale'] ?? 'USD';

        $this->view('frais/index', [
            'title' => APP_NAME . ' - Frais',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'fees' => $fees,
            'schoolCurrency' => $schoolCurrency,
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $classes = $ecoleId > 0 ? Classe::getAllBySchool($ecoleId) : [];
        $years = $ecoleId > 0 ? AnneeScolaire::getAllBySchool($ecoleId) : [];
        $oldInput = $_SESSION['frais_old'] ?? [];
        unset($_SESSION['frais_old']);

        $this->view('frais/create', [
            'title' => APP_NAME . ' - Nouveau frais',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'classes' => $classes,
            'years' => $years,
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::refresh() ?: Auth::user();
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $typeFrais = trim($_POST['type_frais'] ?? '');
            $montantTotal = trim($_POST['montant_total'] ?? '');
            $classeId = (int) ($_POST['classe_id'] ?? 0);
            $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);

            $errors = [];
            if ($ecoleId <= 0) {
                $errors[] = 'École introuvable pour cet utilisateur.';
            }
            if ($typeFrais === '') {
                $errors[] = 'Le type de frais est requis.';
            }
            if ($montantTotal === '' || !is_numeric($montantTotal) || (float) $montantTotal < 0) {
                $errors[] = 'Le montant total doit être un nombre positif.';
            }
            if ($classeId <= 0) {
                $errors[] = 'La classe associée est requise.';
            }
            if ($anneeScolaireId <= 0) {
                $errors[] = 'L’année scolaire est requise.';
            }

            if (empty($errors)) {
                $created = FraisScolaire::create([
                    'classe_id' => $classeId,
                    'type_frais' => $typeFrais,
                    'montant_total' => (float) $montantTotal,
                    'annee_scolaire_id' => $anneeScolaireId,
                ]);

                if ($created) {
                    $_SESSION['frais_success'] = 'Frais créé avec succès.';
                    $this->redirect('/frais');
                    return;
                }

                $errors[] = 'Impossible de créer le frais. Veuillez réessayer.';
            }

            $_SESSION['frais_errors'] = $errors;
            $_SESSION['frais_old'] = $_POST;
        }

        $this->redirect('/frais/create');
    }
}
