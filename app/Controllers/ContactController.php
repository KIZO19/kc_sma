<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\PlatformContact;
use App\Models\User;

class ContactController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::refresh() ?: Auth::user();
        if (($user['role'] ?? '') === 'super_admin') {
            $this->redirect('/messages');
            return;
        }

        if (empty($_SESSION['kc_sma_contact_csrf'])) {
            $_SESSION['kc_sma_contact_csrf'] = bin2hex(random_bytes(32));
        }

        $role = (string) ($user['role'] ?? 'default');
        $this->view('contact/index', [
            'title' => APP_NAME . ' - Contacter KC_SMA',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'csrfToken' => $_SESSION['kc_sma_contact_csrf'],
            'success' => $_SESSION['kc_sma_contact_success'] ?? null,
            'error' => $_SESSION['kc_sma_contact_error'] ?? null,
        ]);
        unset($_SESSION['kc_sma_contact_success'], $_SESSION['kc_sma_contact_error']);
    }

    public function send(): void
    {
        Auth::requireAuth();
        $user = Auth::refresh() ?: Auth::user();
        if (($user['role'] ?? '') === 'super_admin') {
            $this->redirect('/messages');
            return;
        }

        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || !hash_equals((string) ($_SESSION['kc_sma_contact_csrf'] ?? ''), $token)
        ) {
            $_SESSION['kc_sma_contact_error'] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
            $this->redirect('/contact');
            return;
        }

        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        if (
            $subject === ''
            || mb_strlen($subject, 'UTF-8') > 160
            || $body === ''
            || mb_strlen($body, 'UTF-8') > 5000
        ) {
            $_SESSION['kc_sma_contact_error'] = 'Saisissez un objet et un message de 5 000 caractères maximum.';
            $this->redirect('/contact');
            return;
        }

        try {
            PlatformContact::send($user, $subject, $body);
            $_SESSION['kc_sma_contact_success'] = 'Votre message a été transmis à KC_SMA.';
            $_SESSION['kc_sma_contact_csrf'] = bin2hex(random_bytes(32));
        } catch (\DomainException $exception) {
            $_SESSION['kc_sma_contact_error'] = $exception->getMessage();
        } catch (\Throwable $exception) {
            error_log('ContactController::send failed: ' . $exception->getMessage());
            $_SESSION['kc_sma_contact_error'] = 'Votre message n’a pas pu être envoyé. Réessayez.';
        }

        $this->redirect('/contact');
    }
}
