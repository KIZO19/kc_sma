<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ecole;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $schools = [];
        $agents = [];
        if ($role === 'super_admin') {
            $schools = Ecole::getAll();
            $agents = array_values(array_filter(
                User::getAllUsers(),
                static fn (array $account): bool => ($account['role'] ?? '') === 'agent_ecole'
            ));
        }
        $profileOld = $_SESSION['profile_old'] ?? [];
        unset($_SESSION['profile_old']);
        if (is_array($user)) {
            foreach (['nom_complet', 'identifiant'] as $field) {
                if (array_key_exists($field, $profileOld)) {
                    $user[$field] = $profileOld[$field];
                }
            }
        }

        $this->view('profile/index', [
            'title' => APP_NAME . ' - Mon profil',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'schools' => $schools,
            'agents' => $agents,
            'success' => $_GET['success'] ?? null,
            'errors' => $_SESSION['profile_errors'] ?? [],
        ]);

        unset($_SESSION['profile_errors']);
    }

    public function assignAgent(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
        }

        $agentId = (int) ($_POST['agent_id'] ?? 0);
        $schoolId = (int) ($_POST['ecole_id'] ?? 0);
        $agent = $agentId > 0 ? User::findById($agentId) : null;
        $school = $schoolId > 0 ? Ecole::findById($schoolId) : null;

        if (!$agent || ($agent['role'] ?? '') !== 'agent_ecole') {
            $_SESSION['profile_errors'] = ['L’utilisateur sélectionné n’est pas un agent valide.'];
            $this->redirect('/profile');
        }

        if (!$school) {
            $_SESSION['profile_errors'] = ['L’école sélectionnée est introuvable.'];
            $this->redirect('/profile');
        }

        if (!User::assignToSchool($agentId, $schoolId)) {
            $_SESSION['profile_errors'] = ['L’affectation de l’agent a échoué.'];
            $this->redirect('/profile');
        }

        $this->redirect('/profile?success=agent');
    }

    public function update(): void
    {
        Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
        }

        $user = Auth::refresh() ?: Auth::user();
        $id = (int) ($user['id'] ?? 0);

        $nomComplet = trim($_POST['nom_complet'] ?? '');
        $identifiant = trim($_POST['identifiant'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $motDePasseConfirm = $_POST['mot_de_passe_confirm'] ?? '';

        $errors = [];

        if ($nomComplet === '') {
            $errors[] = 'Le nom complet est requis.';
        }

        if ($identifiant === '') {
            $errors[] = 'L’identifiant est requis.';
        }

        $nameLength = function_exists('mb_strlen') ? mb_strlen($nomComplet) : strlen($nomComplet);
        $identifierLength = function_exists('mb_strlen') ? mb_strlen($identifiant) : strlen($identifiant);

        if ($nameLength > 150) {
            $errors[] = 'Le nom complet ne peut pas dépasser 150 caractères.';
        }

        if ($identifierLength > 100) {
            $errors[] = 'L’identifiant ne peut pas dépasser 100 caractères.';
        }

        if ($identifiant !== ($user['identifiant'] ?? '') && User::existsByIdentifiant($identifiant)) {
            $errors[] = 'Cet identifiant est déjà utilisé.';
        }

        if ($motDePasse !== '' && $motDePasse !== $motDePasseConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
            $_SESSION['profile_old'] = [
                'nom_complet' => $nomComplet,
                'identifiant' => $identifiant,
            ];
            $this->redirect('/profile');
        }

        // Validate avatar file size and type (max 250 KB)
        if (!empty($_FILES['avatar']['name'])) {
            if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Erreur lors du téléchargement de l\'avatar.';
            } else {
                $maxBytes = 250 * 1024; // 250 KB
                if ($_FILES['avatar']['size'] > $maxBytes) {
                    $errors[] = 'L\'image doit faire au maximum 250 KB.';
                }
                $allowed = ['image/jpeg', 'image/png'];
                $tmpCheck = $_FILES['avatar']['tmp_name'];
                $mimeCheck = mime_content_type($tmpCheck);
                if (!in_array($mimeCheck, $allowed, true)) {
                    $errors[] = 'Seuls les formats PNG et JPEG sont autorisés pour l\'avatar.';
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
            $_SESSION['profile_old'] = [
                'nom_complet' => $nomComplet,
                'identifiant' => $identifiant,
            ];
            $this->redirect('/profile');
        }

        $updateData = [
            'nom_complet' => $nomComplet,
            'identifiant' => $identifiant,
        ];

        if ($motDePasse !== '') {
            $updateData['mot_de_passe'] = password_hash($motDePasse, PASSWORD_DEFAULT);
        }
        // Save the avatar in the public directory and persist its URL with the profile.
        if (!empty($_FILES['avatar']['name']) && empty($_FILES['avatar']['error'])) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            $tmp = $_FILES['avatar']['tmp_name'];
            $mime = mime_content_type($tmp);
            if (isset($allowed[$mime])) {
                $ext = $allowed[$mime];
                $avatarVersion = hash_file('sha256', $tmp);
                if ($avatarVersion === false) {
                    $_SESSION['profile_errors'] = ['La photo n’a pas pu être vérifiée avant son enregistrement.'];
                    $this->redirect('/profile');
                }

                $projectRoot = dirname(__DIR__, 2);
                $targetDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
                if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                    $_SESSION['profile_errors'] = ['Le dossier de stockage de la photo n’a pas pu être créé.'];
                    $this->redirect('/profile');
                }
                $targetPath = $targetDir . DIRECTORY_SEPARATOR . $id . '.' . $ext;
                if (!move_uploaded_file($tmp, $targetPath)) {
                    $_SESSION['profile_errors'] = ['La photo n’a pas pu être enregistrée. Vérifiez les droits du dossier de stockage.'];
                    $this->redirect('/profile');
                }

                $updateData['avatar'] = '/uploads/avatars/' . $id . '.' . $ext . '?v=' . $avatarVersion;
            }
        }

        if (!User::updateProfile($id, $updateData)) {
            $_SESSION['profile_errors'] = ['La mise à jour du profil a échoué. Aucune confirmation de sauvegarde n’a été effectuée.'];
            $this->redirect('/profile');
        }

        if (isset($updateData['avatar'])) {
            foreach (['jpg', 'png'] as $otherExtension) {
                if ($otherExtension === $ext) {
                    continue;
                }
                $otherPath = $targetDir . DIRECTORY_SEPARATOR . $id . '.' . $otherExtension;
                if (is_file($otherPath) && !unlink($otherPath)) {
                    error_log('ProfileController::update could not remove old avatar: ' . $otherPath);
                }
            }
        }

        $updatedUser = User::findById($id);
        if (!$updatedUser) {
            error_log('ProfileController::update: updated account could not be reloaded for user ID ' . $id);
            $_SESSION['profile_errors'] = ['Le profil a été enregistré, mais le compte n’a pas pu être rechargé. Reconnectez-vous.'];
            $this->redirect('/profile');
        }

        Auth::login($updatedUser);

        $this->redirect('/profile?success=1');
    }
}
