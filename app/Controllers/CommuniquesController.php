<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Communique;
use App\Models\User;
use App\Services\PaymentParentNotifier;

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
        $this->view('communiques/index', [
            'title' => APP_NAME . ' - Communiqués aux parents',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'communiques' => Communique::latestBySchool($ecoleId),
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
        if ($errors !== []) {
            $_SESSION['communique_error'] = implode(' ', $errors);
            $this->redirect('/communiques');
        }

        try {
            $result = (new PaymentParentNotifier())->sendSchoolAnnouncement($ecoleId, $title, $message);
            Communique::record([
                'ecole_id' => $ecoleId,
                'auteur_user_id' => (int) ($user['id'] ?? 0),
                'titre' => $title,
                'message' => $message,
                'destinataires_total' => (int) ($result['recipients_total'] ?? 0),
                'destinataires_livres' => (int) ($result['recipients_delivered'] ?? 0),
                'destinataires_echec' => (int) ($result['recipients_failed'] ?? 0),
                'statut' => (string) ($result['status'] ?? 'failed'),
            ]);
            $_SESSION['communique_success'] = ($result['message'] ?? 'Résultat d’envoi indisponible.')
                . ' ' . ($result['details'] ?? '');
        } catch (\Throwable $e) {
            error_log('CommuniquesController::send failed: ' . $e->getMessage());
            $_SESSION['communique_error'] = 'Le communiqué n’a pas pu être envoyé ou son résultat n’a pas pu être enregistré. Consultez les journaux du serveur avant de réessayer pour éviter un envoi en double.';
        }

        $this->redirect('/communiques');
    }
}
