<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\PasswordResetMailer;

class AuthController extends Controller
{
    public function login(): void
    {
        Auth::requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifiant = trim($_POST['identifiant'] ?? '');
            $motDePasse = $_POST['mot_de_passe'] ?? '';

            $user = User::authenticate($identifiant, $motDePasse);
            $isPendingUnassignedAgent = $user && ($user['role'] ?? '') === 'agent_ecole' && (($user['statut'] ?? 'Actif') === 'Inactif') && (empty($user['ecole_id']) || (int) $user['ecole_id'] === 0);

            if ($user && ($user['statut'] === 'Actif' || $isPendingUnassignedAgent)) {
                if (($user['role'] ?? '') !== 'super_admin' && empty($user['ecole_id']) && !$isPendingUnassignedAgent) {
                    $error = 'Votre compte n’est pas encore affecté à une école. Contactez l’administration.';
                } else {
                    Auth::login($user);
                    $this->redirect('/dashboard');
                    return;
                }
            }

            $error = $error ?? 'Identifiant ou mot de passe incorrect.';
            if ($user && $user['statut'] !== 'Actif' && !$isPendingUnassignedAgent) {
                $error = 'Votre compte est actuellement ' . htmlspecialchars($user['statut']) . '. Contactez l’administrateur.';
            }
            if ($isPendingUnassignedAgent) {
                $error = 'Votre compte est en attente de validation par l’administrateur de l’école.';
            }

            $this->view('auth/login', [
                'error' => $error,
                'identifiant' => $identifiant,
                'title' => APP_NAME,
            ]);
            return;
        }

        $this->view('auth/login', ['title' => APP_NAME]);
    }

    public function register(): void
    {
        Auth::requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nomComplet = trim($_POST['nom_complet'] ?? '');
            $identifiant = trim($_POST['identifiant'] ?? '');
            $motDePasse = $_POST['mot_de_passe'] ?? '';
            $motDePasseConfirm = $_POST['mot_de_passe_confirm'] ?? '';
            $role = $_POST['role'] ?? 'eleve_ecole';
            $ecoleId = isset($_POST['ecole_id']) ? (int) $_POST['ecole_id'] : 0;

            $errors = [];

            if ($nomComplet === '') {
                $errors[] = 'Le nom complet est requis.';
            }
            if ($identifiant === '') {
                $errors[] = 'L’identifiant est requis.';
            }
            if ($motDePasse === '' || $motDePasseConfirm === '') {
                $errors[] = 'Le mot de passe et la confirmation sont requis.';
            }
            if ($motDePasse !== $motDePasseConfirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }
            if (User::existsByIdentifiant($identifiant)) {
                $errors[] = 'Cet identifiant est déjà utilisé.';
            }

            $isPendingAgentRegistration = $role === 'agent_ecole';
            if (!$isPendingAgentRegistration && !User::isEligibleForRegistration($role, $identifiant)) {
                $errors[] = User::getRegistrationEligibilityError($role, $identifiant);
            }
            if ($isPendingAgentRegistration && User::existsByIdentifiant($identifiant)) {
                $errors[] = 'Cet identifiant est déjà utilisé.';
            }

            if (!$isPendingAgentRegistration && in_array($role, ['ecole_admin', 'agent_ecole', 'parent_ecole', 'eleve_ecole'], true)) {
                $resolvedSchoolId = $ecoleId > 0 ? $ecoleId : User::resolveSchoolIdForRole($role, $identifiant);
                if ($resolvedSchoolId <= 0) {
                    $errors[] = 'Veuillez sélectionner une école valide pour ce compte.';
                }
            }

            if (empty($errors)) {
                try {
                    $userData = [
                        'nom_complet' => $nomComplet,
                        'identifiant' => $identifiant,
                        'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
                        'role' => $role,
                        'statut' => 'Inactif',
                    ];

                    if ($ecoleId > 0) {
                        $userData['ecole_id'] = $ecoleId;
                    } elseif (!$isPendingAgentRegistration) {
                        $userData['ecole_id'] = User::resolveSchoolIdForRole($role, $identifiant);
                    }

                    $user = User::create($userData);

                    if (empty($user['id'] ?? null)) {
                        $errors[] = 'Le compte n’a pas pu être créé avec l’école sélectionnée.';
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Le compte n’a pas pu être créé avec l’école sélectionnée.';
                }
            }

            if (!empty($errors)) {
                $this->view('auth/register', [
                    'errors' => $errors,
                    'title' => APP_NAME,
                    'old' => [
                        'nom_complet' => $nomComplet,
                        'identifiant' => $identifiant,
                        'role' => $role,
                        'ecole_id' => $ecoleId,
                    ],
                ]);
                return;
            }

            $message = match ($role) {
                'ecole_admin' => 'Votre compte a été créé. Il est en attente de validation par le super-administrateur.',
                'agent_ecole' => 'Votre compte a été créé. Il est en attente d’affectation et de validation par l’administrateur de l’école.',
                default => 'Votre compte a été créé. Il est en attente de validation par l’administration de votre école.',
            };
            $this->view('auth/register', [
                'errors' => [],
                'message' => $message,
                'title' => APP_NAME,
                'old' => [
                    'nom_complet' => $nomComplet,
                    'identifiant' => $identifiant,
                    'role' => $role,
                    'ecole_id' => $ecoleId,
                ],
            ]);
            return;
        }

        $this->view('auth/register', ['title' => APP_NAME]);
    }

    public function forgotPassword(): void
    {
        Auth::requireGuest();
        $csrfToken = $this->getPasswordResetCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postedToken = (string) ($_POST['csrf_token'] ?? '');
            if ($postedToken === '' || !hash_equals($csrfToken, $postedToken)) {
                $this->view('auth/forgot', [
                    'title' => APP_NAME,
                    'error' => 'Le formulaire a expiré. Rechargez la page et réessayez.',
                    'csrfToken' => $csrfToken,
                ]);
                return;
            }

            $identifiant = trim((string) ($_POST['identifiant'] ?? ''));
            $message = 'Si un compte correspond aux coordonnées fournies, un lien de réinitialisation sera envoyé à son adresse courriel.';
            $error = null;

            try {
                if (!PasswordResetMailer::isConfigured()) {
                    $error = 'Le service de récupération est momentanément indisponible. Veuillez réessayer plus tard.';
                } else {
                    $account = $this->isRecoveryIdentifier($identifiant)
                        ? PasswordReset::findAccountByRecoveryIdentifier($identifiant)
                        : null;
                    $remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

                    if (PasswordReset::recordRequest($remoteAddress, isset($account['id']) ? (int) $account['id'] : null) && $account !== null) {
                        $email = trim((string) ($account['recovery_email'] ?? ''));
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $token = PasswordReset::createToken((int) $account['id']);
                            try {
                                PasswordResetMailer::sendResetLink($email, (string) $account['nom_complet'], $token);
                            } catch (\RuntimeException $exception) {
                                PasswordReset::revokeToken($token);
                                error_log('Password reset email delivery failed: ' . $exception->getMessage());
                                $error = 'Le service de récupération est momentanément indisponible. Veuillez réessayer plus tard.';
                            }
                        }
                    }
                }
            } catch (\PDOException|\RuntimeException $exception) {
                error_log('Password reset request failed: ' . $exception->getMessage());
                $error = 'Le service de récupération est momentanément indisponible. Veuillez réessayer plus tard.';
            }

            $this->view('auth/forgot', [
                'title' => APP_NAME,
                'message' => $error === null ? $message : '',
                'error' => $error,
                'identifiant' => $identifiant,
                'csrfToken' => $this->rotatePasswordResetCsrfToken(),
            ]);
            return;
        }

        $this->view('auth/forgot', ['title' => APP_NAME, 'csrfToken' => $csrfToken]);
    }

    public function resetPassword(): void
    {
        Auth::requireGuest();
        header('Cache-Control: no-store, private');
        header('Referrer-Policy: no-referrer');
        $csrfToken = $this->getPasswordResetCsrfToken();
        $token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? ''));
        $errors = [];
        $message = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postedToken = (string) ($_POST['csrf_token'] ?? '');
            if ($postedToken === '' || !hash_equals($csrfToken, $postedToken)) {
                $errors[] = 'Le formulaire a expiré. Rechargez la page et réessayez.';
            } else {
                $password = (string) ($_POST['mot_de_passe'] ?? '');
                $passwordConfirm = (string) ($_POST['mot_de_passe_confirm'] ?? '');

                if (strlen($password) < 8) {
                    $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
                }
                if ($password !== $passwordConfirm) {
                    $errors[] = 'Les mots de passe ne correspondent pas.';
                }
                if (!$errors && !PasswordReset::resetPassword($token, password_hash($password, PASSWORD_DEFAULT))) {
                    $errors[] = 'Ce lien est invalide ou a expiré. Demandez un nouveau lien de réinitialisation.';
                    $token = '';
                }
                if (!$errors) {
                    $message = 'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.';
                    $token = '';
                }
            }
        } elseif (!PasswordReset::isTokenValid($token)) {
            $errors[] = 'Ce lien est invalide ou a expiré. Demandez un nouveau lien de réinitialisation.';
            $token = '';
        }

        $this->view('auth/reset_password', [
            'title' => APP_NAME,
            'csrfToken' => $this->rotatePasswordResetCsrfToken(),
            'token' => $token,
            'errors' => $errors,
            'message' => $message,
        ]);
    }

    private function getPasswordResetCsrfToken(): string
    {
        if (empty($_SESSION['password_reset_csrf'])) {
            $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['password_reset_csrf'];
    }

    private function rotatePasswordResetCsrfToken(): string
    {
        $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
        return (string) $_SESSION['password_reset_csrf'];
    }

    private function isRecoveryIdentifier(string $identifier): bool
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return (bool) preg_match('/^\+?[0-9][0-9\s().-]{5,24}$/', $identifier);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
