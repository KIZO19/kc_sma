<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\Exoneration;
use App\Models\FraisScolaire;
use App\Models\User;

class DerogationsController extends Controller
{
    private const REQUEST_ROLES = ['comptable_école', 'préfet_école'];
    private const VALIDATION_ROLES = ['promoteur_école'];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::REQUEST_ROLES, self::VALIDATION_ROLES));
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $students = $ecoleId > 0 ? Eleve::getAllBySchool($ecoleId) : [];
        $fees = $ecoleId > 0 ? FraisScolaire::getAllBySchool($ecoleId) : [];

        $this->view('derogations/index', [
            'title' => APP_NAME . ' - Dérogations',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'students' => $students,
            'fees' => $fees,
            'requests' => Exoneration::getAllBySchool($ecoleId),
            'canRequest' => in_array($role, self::REQUEST_ROLES, true),
            'canValidate' => in_array($role, self::VALIDATION_ROLES, true),
        ]);
    }

    public function request(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::REQUEST_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $fraisId = (int) ($_POST['frais_id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $motif = trim((string) ($_POST['motif'] ?? ''));
        $student = $ecoleId > 0 ? Eleve::findByIdAndSchool($eleveId, $ecoleId) : null;
        $fees = $ecoleId > 0 ? FraisScolaire::getAllBySchool($ecoleId) : [];
        $fee = null;
        foreach ($fees as $candidate) {
            if ((int) ($candidate['id'] ?? 0) === $fraisId) {
                $fee = $candidate;
                break;
            }
        }
        $errors = [];
        if (!$student || !$fee) $errors[] = 'Élève ou frais invalide pour cette école.';
        if ($montant <= 0) $errors[] = 'Le montant exonéré doit être supérieur à zéro.';
        if ($motif === '') $errors[] = 'Le motif est obligatoire.';
        if ($student && $fee && $montant > (float) ($fee['montant_total'] ?? 0)) $errors[] = 'Le montant dépasse le montant du frais.';

        if (!$errors) {
            Exoneration::create([
                'ecole_id' => $ecoleId,
                'eleve_id' => $eleveId,
                'frais_id' => $fraisId,
                'montant' => $montant,
                'devise' => strtoupper((string) ($fee['devise'] ?? 'USD')),
                'motif' => $motif,
                'demandeur_id' => (int) ($user['id'] ?? 0),
            ]);
            $_SESSION['derogations_success'] = 'La demande d’exonération a été envoyée au promoteur.';
        } else {
            $_SESSION['derogations_errors'] = $errors;
        }
        $this->redirect('/derogations');
    }

    public function decide(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::VALIDATION_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $status = ($_POST['decision'] ?? '') === 'Approuvee' ? 'Approuvee' : 'Refusee';
        $ok = Exoneration::decide((int) ($_POST['id'] ?? 0), (int) ($user['id'] ?? 0), $status, trim((string) ($_POST['commentaire'] ?? '')));
        $_SESSION[$ok ? 'derogations_success' : 'derogations_errors'] = $ok
            ? ($status === 'Approuvee' ? 'Exonération approuvée et dette réduite.' : 'Demande d’exonération refusée.')
            : ['La décision n’a pas pu être enregistrée.'];
        $this->redirect('/derogations');
    }
}
