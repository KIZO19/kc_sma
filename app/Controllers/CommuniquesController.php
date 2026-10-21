<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Communique;
use App\Models\Section;
use App\Models\UserMessage;
use App\Models\User;
use App\Services\PaymentParentNotifier;
use App\Entities\Roles;

class CommuniquesController extends Controller
{
    private const SENDER_ROLES = [
        'ecole_admin',
        'préfet_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
        'sec_école',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SENDER_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if ($ecoleId <= 0) {
            $this->redirect('/error/accessDenied');
        }

        if (empty($_SESSION['communique_form_token'])) {
            $_SESSION['communique_form_token'] = bin2hex(random_bytes(32));
        }

        $role = (string) ($user['role'] ?? 'default');
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $sections = Section::getAllForSchool($ecoleId);
        if ($sectionScopeId !== null) {
            $sections = array_values(array_filter(
                $sections,
                static fn (array $section): bool => (int) $section['id'] === $sectionScopeId
            ));
        }
        $this->view('communiques/index', [
            'title' => APP_NAME . ' - Communiqués aux parents',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'communiques' => Communique::latestBySchool($ecoleId),
            'audienceRoles' => array_diff_key(Roles::list(), ['super_admin' => true]),
            'sections' => $sections,
            'formToken' => $_SESSION['communique_form_token'],
            'success' => $_SESSION['communique_success'] ?? null,
            'error' => $_SESSION['communique_error'] ?? null,
        ]);
        unset($_SESSION['communique_success'], $_SESSION['communique_error']);
    }

    public function send(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SENDER_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $submittedToken = (string) ($_POST['communique_form_token'] ?? '');
        $expectedToken = (string) ($_SESSION['communique_form_token'] ?? '');
        if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
            $_SESSION['communique_error'] = 'Le formulaire a expiré. Rechargez la page avant de réessayer.';
            $this->redirect('/communiques');
        }
        $_SESSION['communique_form_token'] = bin2hex(random_bytes(32));

        $title = trim((string) ($_POST['titre'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $audienceRoles = $this->readAudienceRoles();
        $sectionIds = $this->readSectionIds($user);
        $errors = [];
        if ($ecoleId <= 0) {
            $errors[] = 'Aucune école n’est associée à votre compte.';
        }
        if ($title === '' || mb_strlen($title) > 160) {
            $errors[] = 'Le titre est obligatoire et ne peut dépasser 160 caractères.';
        }
        if ($message === '' || mb_strlen($message) > 5000) {
            $errors[] = 'Le communiqué est obligatoire et ne peut dépasser 5 000 caractères.';
        }
        if ($audienceRoles === null) {
            $errors[] = 'La liste des rôles destinataires est invalide.';
        } elseif ($audienceRoles === []) {
            $errors[] = 'Sélectionnez au moins un rôle destinataire.';
        }
        if ($sectionIds === null) {
            $errors[] = 'Une section destinataire est invalide ou hors de votre périmètre.';
        }
        if ($errors !== []) {
            $_SESSION['communique_error'] = implode(' ', $errors);
            $this->redirect('/communiques');
        }

        try {
            if (in_array('parent_ecole', $audienceRoles, true)) {
                $result = (new PaymentParentNotifier())->sendSchoolAnnouncement($ecoleId, $title, $message, $sectionIds);
            } else {
                $result = [
                    'status' => 'success',
                    'message' => 'Aucun SMS envoyé : les parents ne font pas partie des destinataires.',
                    'details' => '',
                    'recipients_total' => 0,
                    'recipients_delivered' => 0,
                    'recipients_failed' => 0,
                ];
            }
            $communiqueId = Communique::record([
                'ecole_id' => $ecoleId,
                'auteur_user_id' => (int) ($user['id'] ?? 0),
                'titre' => $title,
                'message' => $message,
                'destinataires_roles' => $audienceRoles,
                'destinataires_sections' => $sectionIds,
                'destinataires_total' => (int) ($result['recipients_total'] ?? 0),
                'destinataires_livres' => (int) ($result['recipients_delivered'] ?? 0),
                'destinataires_echec' => (int) ($result['recipients_failed'] ?? 0),
                'statut' => (string) ($result['status'] ?? 'failed'),
            ]);
            UserMessage::notifyCommunique($ecoleId, $communiqueId, $title, $message, $audienceRoles, $sectionIds);
            $_SESSION['communique_success'] = ($result['message'] ?? 'Résultat d’envoi indisponible.')
                . ' ' . ($result['details'] ?? '');
        } catch (\Throwable $e) {
            error_log('CommuniquesController::send failed: ' . $e->getMessage());
            $_SESSION['communique_error'] = 'Le communiqué n’a pas pu être envoyé ou son résultat n’a pas pu être enregistré. Consultez les journaux du serveur avant de réessayer pour éviter un envoi en double.';
        }

        $this->redirect('/communiques');
    }

    private function readAudienceRoles(): ?array
    {
        if (!isset($_POST['destinataires_roles'])) {
            return [];
        }
        $submitted = $_POST['destinataires_roles'];
        if (!is_array($submitted)) {
            return null;
        }

        $allowedRoles = array_keys(array_diff_key(Roles::list(), ['super_admin' => true]));
        $roles = [];
        foreach ($submitted as $role) {
            if (!is_string($role) || !in_array($role, $allowedRoles, true)) {
                return null;
            }
            $roles[] = $role;
        }
        $roles = array_values(array_unique($roles));
        sort($roles);

        return $roles;
    }

    private function readSectionIds(array $user): ?array
    {
        $allowedSections = Section::getAllForSchool((int) ($user['ecole_id'] ?? 0));
        $allowedIds = array_map('intval', array_column($allowedSections, 'id'));
        $scopeId = $this->getSectionScopeForUser($user);
        if ($scopeId !== null) {
            $allowedIds = [$scopeId];
        }

        if (!isset($_POST['destinataires_sections'])) {
            return $scopeId !== null ? [$scopeId] : $allowedIds;
        }
        $submitted = $_POST['destinataires_sections'];
        if (!is_array($submitted)) {
            return null;
        }
        if ($submitted === []) {
            return $scopeId !== null ? [$scopeId] : $allowedIds;
        }

        $ids = [];
        foreach ($submitted as $sectionId) {
            $sectionId = filter_var($sectionId, FILTER_VALIDATE_INT);
            if ($sectionId === false || $sectionId <= 0 || !in_array($sectionId, $allowedIds, true)) {
                return null;
            }
            $ids[] = $sectionId;
        }
        $ids = array_values(array_unique($ids));
        sort($ids);

        return $ids;
    }
}
