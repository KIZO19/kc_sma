<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function login(): void
    {
        Auth::requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifiant = trim($_POST['identifiant'] ?? '');
            $motDePasse = $_POST['mot_de_passe'] ?? '';

            $user = User::authenticate($identifiant, $motDePasse);
            if ($user && $user['statut'] === 'Actif') {
                Auth::login($user);
                $this->redirect('/dashboard');
            }

            $error = 'Identifiant ou mot de passe incorrect.';
            if ($user && $user['statut'] !== 'Actif') {
                $error = 'Votre compte est actuellement ' . htmlspecialchars($user['statut']) . '. Contactez l’administrateur.';
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

            if (!User::isEligibleForRegistration($role, $identifiant)) {
                $errors[] = User::getRegistrationEligibilityError($role, $identifiant);
            }

            if (empty($errors)) {
                $user = User::create([
                    'nom_complet' => $nomComplet,
                    'identifiant' => $identifiant,
                    'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
                    'role' => $role,
                    'statut' => 'Actif',
                ]);

                Auth::login($user);
                $this->redirect('/dashboard');
            }

            $this->view('auth/register', [
                'errors' => $errors,
                'title' => APP_NAME,
                'old' => [
                    'nom_complet' => $nomComplet,
                    'identifiant' => $identifiant,
                    'role' => $role,
                ],
            ]);
            return;
        }

        $this->view('auth/register', ['title' => APP_NAME]);
    }

    public function forgotPassword(): void
    {
        Auth::requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifiant = trim($_POST['identifiant'] ?? '');
            $message = 'Si cet identifiant existe, un lien de réinitialisation a été envoyé.';

            if ($identifiant !== '' && User::findByIdentifiant($identifiant)) {
                $message = 'Un lien de réinitialisation a été envoyé à votre adresse si elle est enregistrée.';
            }

            $this->view('auth/forgot', [
                'title' => APP_NAME,
                'message' => $message,
                'identifiant' => $identifiant,
            ]);
            return;
        }

        $this->view('auth/forgot', ['title' => APP_NAME]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
