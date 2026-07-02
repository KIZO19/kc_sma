<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('profile/index', [
            'title' => APP_NAME . ' - Mon profil',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'success' => $_GET['success'] ?? null,
            'errors' => $_SESSION['profile_errors'] ?? [],
        ]);

        unset($_SESSION['profile_errors']);
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

        if ($identifiant !== ($user['identifiant'] ?? '') && User::existsByIdentifiant($identifiant)) {
            $errors[] = 'Cet identifiant est déjà utilisé.';
        }

        if ($motDePasse !== '' && $motDePasse !== $motDePasseConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
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
            $this->redirect('/profile');
        }

        $updateData = [
            'nom_complet' => $nomComplet,
            'identifiant' => $identifiant,
        ];

        if ($motDePasse !== '') {
            $updateData['mot_de_passe'] = password_hash($motDePasse, PASSWORD_DEFAULT);
        }
        // Handle avatar upload (saved to public/uploads/avatars/{id}.{ext}) and persist URL
        if (!empty($_FILES['avatar']['name']) && empty($_FILES['avatar']['error'])) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            $tmp = $_FILES['avatar']['tmp_name'];
            $mime = mime_content_type($tmp);
            if (isset($allowed[$mime])) {
                $ext = $allowed[$mime];
                $projectRoot = dirname(__DIR__, 2); // app/Controllers -> app -> project root
                $targetDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                $targetPath = $targetDir . DIRECTORY_SEPARATOR . $id . '.' . $ext;
                if (move_uploaded_file($tmp, $targetPath)) {
                    // remove other extensions for this user
                    foreach (['jpg','png'] as $e) {
                        if ($e === $ext) continue;
                        $other = $targetDir . DIRECTORY_SEPARATOR . $id . '.' . $e;
                        if (file_exists($other)) @unlink($other);
                    }
                    $updateData['avatar'] = '/uploads/avatars/' . $id . '.' . $ext;
                }
            }
        }

        User::updateProfile($id, $updateData);
        Auth::login(User::findById($id));

        $this->redirect('/profile?success=1');
    }
}
