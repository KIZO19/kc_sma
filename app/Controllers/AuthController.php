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

            if (!User::isEligibleForRegistration($role, $identifiant)) {
                $errors[] = User::getRegistrationEligibilityError($role, $identifiant);
            }

            if (in_array($role, ['ecole_admin', 'agent_ecole', 'parent_ecole', 'eleve_ecole'], true)) {
                $resolvedSchoolId = $ecoleId > 0 ? $ecoleId : User::resolveSchoolIdForRole($role, $identifiant);
                if ($resolvedSchoolId <= 0) {
                    $errors[] = 'Veuillez sélectionner une école valide pour ce compte.';
                }
            }

            if (empty($errors)) {
                try {
                    $user = User::create([
                        'nom_complet' => $nomComplet,
                        'identifiant' => $identifiant,
                        'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
                        'role' => $role,
                        'statut' => 'Inactif',
                        'ecole_id' => $ecoleId > 0 ? $ecoleId : User::resolveSchoolIdForRole($role, $identifiant),
                    ]);

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

            $message = 'Votre compte a été créé. Il est en attente de validation par le super administrateur.';
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
