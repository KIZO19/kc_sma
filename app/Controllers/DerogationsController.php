<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Derogation;
use App\Models\Eleve;
use App\Models\Exoneration;
use App\Models\SchoolMessage;
use App\Models\User;

class DerogationsController extends Controller
{
    private const REQUEST_ROLES = ['préfet_école', 'DE_école', 'DP_école'];
    private const PARENT_ROLES = ['parent_ecole', 'parent_école'];
    private const VALIDATION_ROLES = ['comptable_école'];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::REQUEST_ROLES, self::VALIDATION_ROLES, self::PARENT_ROLES));
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        if (empty($_SESSION['derogation_request_csrf'])) {
            $_SESSION['derogation_request_csrf'] = bin2hex(random_bytes(32));
        }
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $isParent = in_array($role, self::PARENT_ROLES, true);
        $sectionScopeId = $isParent ? null : $this->getSectionScopeForUser($user);
        $students = $ecoleId > 0
            ? ($isParent
                ? SchoolMessage::getChildrenForParent((int) ($user['reference_id'] ?? 0), $ecoleId)
                : Eleve::getAllBySchool($ecoleId, null, $sectionScopeId))
            : [];
        $financialSummary = $ecoleId > 0 ? Exoneration::getFinancialSummaryBySchool($ecoleId, $sectionScopeId) : [];
        $studentIds = array_fill_keys(array_map(static fn (array $student): int => (int) $student['id'], $students), true);
        if ($isParent) {
            $financialSummary = array_intersect_key($financialSummary, $studentIds);
        }

        $this->view('derogations/index', [
            'title' => APP_NAME . ' - Dérogations',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'students' => $students,
            'financialSummary' => $financialSummary,
            'requests' => Derogation::getAllBySchool(
                $ecoleId,
                $sectionScopeId,
                $isParent ? (int) ($user['reference_id'] ?? 0) : null
            ),
            'canRequest' => in_array($role, self::REQUEST_ROLES, true) || $isParent,
            'canValidate' => in_array($role, self::VALIDATION_ROLES, true),
            'isParent' => $isParent,
            'requestCsrfToken' => $_SESSION['derogation_request_csrf'],
        ]);
    }

    public function request(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::REQUEST_ROLES, self::PARENT_ROLES));
        $user = Auth::refresh() ?: Auth::user();
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || !hash_equals((string) ($_SESSION['derogation_request_csrf'] ?? ''), $token)
        ) {
            $_SESSION['derogations_errors'] = ['Le formulaire a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/derogations');
            return;
        }
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $isParent = in_array((string) ($user['role'] ?? ''), self::PARENT_ROLES, true);
        $parentId = (int) ($user['reference_id'] ?? 0);
        $sectionScopeId = $isParent ? null : $this->getSectionScopeForUser($user);
        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $dateFin = trim((string) ($_POST['date_fin'] ?? ''));
        $motif = trim((string) ($_POST['motif'] ?? ''));
        $student = $ecoleId > 0 ? Eleve::findByIdAndSchool($eleveId, $ecoleId, $sectionScopeId) : null;
        $errors = [];
        if ($isParent && (!$student || (int) ($student['parent_id'] ?? 0) !== $parentId)) {
            $errors[] = 'Vous ne pouvez demander une dérogation que pour votre enfant inscrit dans cette école.';
            $student = null;
        }
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFin);
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if (!$student) $errors[] = 'Élève invalide pour cette école.';
        $studentFinancial = $student ? (Exoneration::getFinancialSummaryBySchool($ecoleId, $sectionScopeId)[$eleveId] ?? []) : [];
        $hasCurrentDebt = (bool) array_filter(
            array_column($studentFinancial, 'remaining'),
            static fn ($amount): bool => (float) $amount > 0.005
        );
        if ($student && !$hasCurrentDebt) $errors[] = 'Cet élève n’a aucune dette restante après paiements et exonérations.';
        if (!$date || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) $errors[] = 'La date de fin est invalide.';
        if ($date && $date < new \DateTimeImmutable('today')) $errors[] = 'La date de fin doit être aujourd’hui ou une date future.';
        if ($motif === '') $errors[] = 'Le motif est obligatoire.';
        if ($student && Derogation::hasPendingOrActive($eleveId, $ecoleId)) $errors[] = 'Cet élève dispose déjà d’une demande en attente ou d’une dérogation active.';

        if (!$errors) {
            $derogationId = Derogation::create([
                'ecole_id' => $ecoleId,
                'eleve_id' => $eleveId,
                'date_fin' => $dateFin,
                'motif' => $motif,
                'demandeur_id' => (int) ($user['id'] ?? 0),
            ]);
            try {
                $recipientCount = \App\Models\UserMessage::notifyDerogationRecipients(
                    $ecoleId,
                    $derogationId,
                    \App\Core\PersonName::format($student),
                    (string) ($user['nom_complet'] ?? 'Un utilisateur'),
                    $dateFin
                );
                $_SESSION['derogations_success'] = $recipientCount > 0
                    ? 'La demande de dérogation a été envoyée aux destinataires configurés.'
                    : 'La demande est enregistrée, mais aucun destinataire actif ne peut recevoir la notification. Contactez l’administrateur de l’école.';
            } catch (\Throwable $e) {
                error_log('DerogationsController::request notification error: ' . $e->getMessage());
                $_SESSION['derogations_success'] = 'La demande est enregistrée, mais la notification n’a pas pu être distribuée. Contactez l’administrateur de l’école.';
            }
        } else {
            $_SESSION['derogations_errors'] = $errors;
        }
        $_SESSION['derogation_request_csrf'] = bin2hex(random_bytes(32));
        $this->redirect('/derogations');
    }

    public function decide(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::VALIDATION_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $status = ($_POST['decision'] ?? '') === 'Approuvee' ? 'Approuvee' : 'Refusee';
        $ok = Derogation::decide(
            (int) ($_POST['id'] ?? 0),
            (int) ($user['ecole_id'] ?? 0),
            (int) ($user['id'] ?? 0),
            $status,
            trim((string) ($_POST['commentaire'] ?? '')),
            $this->getSectionScopeForUser($user)
        );
        $_SESSION[$ok ? 'derogations_success' : 'derogations_errors'] = $ok
            ? ($status === 'Approuvee' ? 'Dérogation approuvée : l’élève est protégé jusqu’à la date indiquée.' : 'Demande de dérogation refusée.')
            : ['La décision n’a pas pu être enregistrée.'];
        $this->redirect('/derogations');
    }

    public function openMessage(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_merge(self::REQUEST_ROLES, self::PARENT_ROLES));
        $user = Auth::refresh() ?: Auth::user();
        $derogationId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$user || !$derogationId || $derogationId <= 0) {
            $this->redirect('/derogations');
        }

        Derogation::markDecisionMessageRead($derogationId, (int) $user['id']);
        $this->redirect('/derogations#derogation-' . $derogationId);
    }
}
