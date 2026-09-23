<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Derogation;
use App\Models\DetteEleve;
use App\Models\Eleve;
use App\Models\User;

class DerogationsController extends Controller
{
    private const REQUEST_ROLES = ['comptable_école', 'préfet_école'];
    private const VALIDATION_ROLES = [
        'promoteur_école',
        'préfet_école',
        'sec_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::REQUEST_ROLES, self::VALIDATION_ROLES));
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $students = $ecoleId > 0 ? Eleve::getAllBySchool($ecoleId) : [];

        $this->view('derogations/index', [
            'title' => APP_NAME . ' - Dérogations',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'students' => $students,
            'requests' => Derogation::getAllBySchool($ecoleId),
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
        $dateFin = trim((string) ($_POST['date_fin'] ?? ''));
        $motif = trim((string) ($_POST['motif'] ?? ''));
        $student = $ecoleId > 0 ? Eleve::findByIdAndSchool($eleveId, $ecoleId) : null;
        $errors = [];
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFin);
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if (!$student) $errors[] = 'Élève invalide pour cette école.';
        if ($student && DetteEleve::getTotalOutstandingByEleve($eleveId) <= 0) $errors[] = 'Cet élève n’a aucune dette restante.';
        if (!$date || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) $errors[] = 'La date de fin est invalide.';
        if ($date && $date < new \DateTimeImmutable('today')) $errors[] = 'La date de fin doit être aujourd’hui ou une date future.';
        if ($motif === '') $errors[] = 'Le motif est obligatoire.';
        if ($student && Derogation::hasPendingOrActive($eleveId, $ecoleId)) $errors[] = 'Cet élève dispose déjà d’une demande en attente ou d’une dérogation active.';

        if (!$errors) {
            Derogation::create([
                'ecole_id' => $ecoleId,
                'eleve_id' => $eleveId,
                'date_fin' => $dateFin,
                'motif' => $motif,
                'demandeur_id' => (int) ($user['id'] ?? 0),
            ]);
            $_SESSION['derogations_success'] = 'La demande de dérogation a été envoyée au promoteur.';
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
        $ok = Derogation::decide((int) ($_POST['id'] ?? 0), (int) ($user['ecole_id'] ?? 0), (int) ($user['id'] ?? 0), $status, trim((string) ($_POST['commentaire'] ?? '')));
        $_SESSION[$ok ? 'derogations_success' : 'derogations_errors'] = $ok
            ? ($status === 'Approuvee' ? 'Dérogation approuvée : l’élève est protégé jusqu’à la date indiquée.' : 'Demande de dérogation refusée.')
            : ['La décision n’a pas pu être enregistrée.'];
        $this->redirect('/derogations');
    }
}
