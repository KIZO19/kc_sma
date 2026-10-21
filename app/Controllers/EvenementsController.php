<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Entities\Roles;
use App\Models\AnneeScolaire;
use App\Models\Evenement;
use App\Models\User;

class EvenementsController extends Controller
{
    private const ACCESS_ROLES = [
        'super_admin',
        'ecole_admin',
        'comptable_école',
        'préfet_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
        'sec_école',
        'enseignant_école',
        'eleve_ecole',
        'parent_ecole',
    ];

    private const PLANNER_ROLES = ['comptable_école', 'préfet_école'];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::ACCESS_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = (string) ($user['role'] ?? 'default');
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if ($schoolId <= 0 && $role !== 'super_admin') {
            $this->redirect('/error/accessDenied');
        }

        Evenement::ensureAuthorColumn();
        Evenement::ensureAudienceRolesColumn();
        $activeYear = AnneeScolaire::getActiveBySchool($schoolId);
        $token = (string) ($_SESSION['evenement_form_token'] ?? '');
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $_SESSION['evenement_form_token'] = $token;
        }

        $success = (string) ($_SESSION['evenement_success'] ?? '');
        $error = (string) ($_SESSION['evenement_error'] ?? '');
        unset($_SESSION['evenement_success'], $_SESSION['evenement_error']);

        $this->view('evenements/index', [
            'title' => APP_NAME . ' - Événements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'events' => Evenement::getAllBySchool($schoolId),
            'categories' => Evenement::getCategories(),
            'audienceRoles' => array_diff_key(Roles::list(), ['super_admin' => true]),
            'activeYear' => $activeYear,
            'canPlan' => in_array($role, self::PLANNER_ROLES, true),
            'formToken' => $token,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function openNotification(): void
    {
        Auth::requireAuth();
        $user = Auth::refresh() ?: Auth::user();
        $notificationId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$user || !$notificationId || $notificationId <= 0) {
            $this->redirect('/dashboard');
        }

        $eventId = \App\Models\EvenementNotification::markReadAndGetEventId(
            $notificationId,
            (int) $user['id']
        );
        if ($eventId === null) {
            $this->redirect('/evenements');
        }

        $this->redirect('/evenements#event-' . $eventId);
    }

    public function store(): void
    {
        $user = $this->requirePlanner();
        if (!$this->validateFormToken()) {
            $this->fail('Le formulaire a expiré. Rechargez la page puis réessayez.');
        }

        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $activeYear = AnneeScolaire::getActiveBySchool($schoolId);
        if (!$activeYear) {
            $this->fail('Aucune année scolaire active n’est configurée pour votre école.');
        }

        $event = $this->readEventData();
        $error = $this->validateEventData($event);
        if ($error !== null) {
            $this->fail($error);
        }
        if ($event['nouvelle_categorie'] !== '') {
            $event['categorie_id'] = Evenement::findOrCreateCategory($event['nouvelle_categorie']);
        }
        if (!Evenement::categoryExists((int) $event['categorie_id'])) {
            $this->fail('La catégorie sélectionnée n’est pas valide.');
        }

        Evenement::create(array_merge($event, [
            'ecole_id' => $schoolId,
            'annee_scolaire_id' => (int) $activeYear['id'],
            'auteur_user_id' => (int) $user['id'],
        ]));

        $_SESSION['evenement_form_token'] = bin2hex(random_bytes(32));
        $_SESSION['evenement_success'] = 'Votre événement a été planifié.';
        $this->redirect('/evenements');
    }

    public function update(): void
    {
        $user = $this->requirePlanner();
        if (!$this->validateFormToken()) {
            $this->fail('Le formulaire a expiré. Rechargez la page puis réessayez.');
        }

        $eventId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$eventId || $eventId <= 0) {
            $this->fail('L’événement demandé est invalide.');
        }

        $event = $this->readEventData();
        $error = $this->validateEventData($event);
        if ($error !== null) {
            $this->fail($error);
        }
        if (!Evenement::categoryExists((int) $event['categorie_id'])) {
            $this->fail('La catégorie sélectionnée n’est pas valide.');
        }

        $updated = Evenement::updateOwned(
            $eventId,
            (int) $user['ecole_id'],
            (int) $user['id'],
            $event
        );
        if (!$updated) {
            $this->fail('Cet événement n’existe pas ou ne vous appartient pas.');
        }

        $_SESSION['evenement_form_token'] = bin2hex(random_bytes(32));
        $_SESSION['evenement_success'] = 'Votre événement a été modifié.';
        $this->redirect('/evenements');
    }

    public function delete(): void
    {
        $user = $this->requirePlanner();
        if (!$this->validateFormToken()) {
            $this->fail('Le formulaire a expiré. Rechargez la page puis réessayez.');
        }

        $eventId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$eventId || $eventId <= 0) {
            $this->fail('L’événement demandé est invalide.');
        }
        if (!Evenement::deleteOwned($eventId, (int) $user['ecole_id'], (int) $user['id'])) {
            $this->fail('Cet événement n’existe pas ou ne vous appartient pas.');
        }

        $_SESSION['evenement_form_token'] = bin2hex(random_bytes(32));
        $_SESSION['evenement_success'] = 'Votre événement a été supprimé.';
        $this->redirect('/evenements');
    }

    private function requirePlanner(): array
    {
        Auth::requireAuth();
        Auth::requireRoles(self::PLANNER_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        if (!$user || (int) ($user['ecole_id'] ?? 0) <= 0) {
            $this->redirect('/error/accessDenied');
        }

        Evenement::ensureAuthorColumn();
        Evenement::ensureAudienceRolesColumn();
        return $user;
    }

    private function validateFormToken(): bool
    {
        $submitted = (string) ($_POST['form_token'] ?? '');
        $expected = (string) ($_SESSION['evenement_form_token'] ?? '');
        return $submitted !== '' && $expected !== '' && hash_equals($expected, $submitted);
    }

    private function readEventData(): array
    {
        return [
            'titre' => trim((string) ($_POST['titre'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'date_debut' => trim((string) ($_POST['date_debut'] ?? '')),
            'date_fin' => trim((string) ($_POST['date_fin'] ?? '')),
            'lieu' => trim((string) ($_POST['lieu'] ?? '')),
            'categorie_id' => (int) ($_POST['categorie_id'] ?? 0),
            'nouvelle_categorie' => trim((string) ($_POST['nouvelle_categorie'] ?? '')),
            'audience_roles' => $this->readAudienceRoles(),
        ];
    }

    private function readAudienceRoles(): array
    {
        $submitted = $_POST['audience_roles'] ?? [];
        if (!is_array($submitted)) {
            return [];
        }

        $allowedRoles = array_keys(array_diff_key(Roles::list(), ['super_admin' => true]));
        $roles = array_values(array_unique(array_filter(
            array_map('strval', $submitted),
            static fn (string $role): bool => in_array($role, $allowedRoles, true)
        )));
        sort($roles);

        return $roles;
    }

    private function validateEventData(array &$event): ?string
    {
        if ($event['titre'] === '' || mb_strlen($event['titre']) > 150) {
            return 'Le titre est obligatoire et ne peut dépasser 150 caractères.';
        }
        if (mb_strlen($event['description']) > 5000) {
            return 'La description ne peut dépasser 5 000 caractères.';
        }
        if (mb_strlen($event['lieu']) > 150) {
            return 'Le lieu ne peut dépasser 150 caractères.';
        }
        if (mb_strlen($event['nouvelle_categorie']) > 100) {
            return 'Le nom de la catégorie ne peut dépasser 100 caractères.';
        }
        if ((int) $event['categorie_id'] <= 0 && $event['nouvelle_categorie'] === '') {
            return 'Sélectionnez une catégorie ou saisissez-en une nouvelle.';
        }
        if ($event['audience_roles'] === []) {
            return 'Sélectionnez au moins un rôle destinataire pour l’événement.';
        }

        $start = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $event['date_debut']);
        $startErrors = \DateTimeImmutable::getLastErrors();
        $end = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $event['date_fin']);
        $endErrors = \DateTimeImmutable::getLastErrors();
        if (
            !$start || !$end
            || ($startErrors !== false && ($startErrors['warning_count'] > 0 || $startErrors['error_count'] > 0))
            || ($endErrors !== false && ($endErrors['warning_count'] > 0 || $endErrors['error_count'] > 0))
            || $start->format('Y-m-d\TH:i') !== $event['date_debut']
            || $end->format('Y-m-d\TH:i') !== $event['date_fin']
        ) {
            return 'Veuillez saisir des dates de début et de fin valides.';
        }
        if ($end <= $start) {
            return 'La date de fin doit être postérieure à la date de début.';
        }

        $event['date_debut'] = $start->format('Y-m-d H:i:s');
        $event['date_fin'] = $end->format('Y-m-d H:i:s');
        return null;
    }

    private function fail(string $message): void
    {
        $_SESSION['evenement_error'] = $message;
        $this->redirect('/evenements');
    }
}
