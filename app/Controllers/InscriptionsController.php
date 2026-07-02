<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\ParentModel;
use App\Models\User;

class InscriptionsController extends Controller
{
    private const SUBMISSION_ROLES = [
        'super_admin',
        'agent_ecole',
        'comptable_école',
        'préfet_école',
        'DE_école',
        'DP_école',
        'DA_école',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'agent_ecole', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $pendingStudents = Eleve::getPending();

        $this->view('inscriptions/index', [
            'title' => APP_NAME . ' - Dossiers d’inscription',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pendingStudents' => $pendingStudents,
            'canSubmit' => in_array($role, self::SUBMISSION_ROLES, true),
            'canEdit' => in_array($role, self::SUBMISSION_ROLES, true),
            'canApprove' => in_array($role, ['super_admin', 'sec_école'], true),
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $parents = $ecoleId > 0 ? ParentModel::getAllBySchool($ecoleId) : ParentModel::getAll();
        $oldInput = $_SESSION['inscriptions_old'] ?? [];
        unset($_SESSION['inscriptions_old']);

        $this->view('inscriptions/create', [
            'title' => APP_NAME . ' - Nouvelle inscription',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'parents' => $parents,
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $postnom = trim($_POST['postnom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $dateNaissance = trim($_POST['date_naissance'] ?? '');
            $matricule = trim($_POST['matricule'] ?? '');
            $parentChoice = trim($_POST['parent_choice'] ?? 'existing');
            $parentId = (int) ($_POST['parent_id'] ?? 0);
            $newParentName = trim($_POST['new_parent_nom_responsable'] ?? '');
            $newParentTelephone = trim($_POST['new_parent_telephone'] ?? '');
            $newParentEmail = trim($_POST['new_parent_email'] ?? '');

            $errors = [];
            if ($nom === '') {
                $errors[] = 'Le nom de l’élève est requis.';
            }
            if ($postnom === '') {
                $errors[] = 'Le postnom de l’élève est requis.';
            }
            if ($genre === '') {
                $errors[] = 'Le genre de l’élève est requis.';
            }
            if ($dateNaissance === '') {
                $errors[] = 'La date de naissance est requise.';
            }

            if (empty($matricule)) {
                $matricule = $this->generateMatricule($nom . ' ' . $postnom);
            }

            if ($parentChoice === 'new') {
                if ($newParentName === '') {
                    $errors[] = 'Le nom du parent/tuteur est requis lorsque vous créez un nouveau parent.';
                }
                if ($newParentTelephone === '') {
                    $errors[] = 'Le téléphone du parent/tuteur est requis lorsque vous créez un nouveau parent.';
                }
            }

            if (empty($errors)) {
                if ($parentChoice === 'new' && $newParentName !== '' && $newParentTelephone !== '') {
                    $newParent = ParentModel::create([
                        'ecole_id' => $user['ecole_id'] ?? null,
                        'nom_responsable' => $newParentName,
                        'telephone' => $newParentTelephone,
                        'email' => $newParentEmail !== '' ? $newParentEmail : null,
                        'mot_de_passe' => password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT),
                    ]);

                    if (!empty($newParent['id'])) {
                        $parentId = (int) $newParent['id'];
                    }
                }

                if ($parentChoice !== 'existing') {
                    $parentId = $parentId > 0 ? $parentId : 0;
                }

                $newStudent = Eleve::create([
                    'matricule' => $matricule,
                    'nom' => $nom,
                    'postnom' => $postnom,
                    'prenom' => $prenom ?: null,
                    'genre' => $genre,
                    'lieu_naissance' => trim($_POST['lieu_naissance'] ?? null),
                    'nationalite' => trim($_POST['nationalite'] ?? 'CONGOLAISE'),
                    'adresse' => trim($_POST['adresse'] ?? null),
                    'date_naissance' => $dateNaissance,
                    'parent_id' => $parentId > 0 ? $parentId : null,
                    'nom_pere' => trim($_POST['nom_pere'] ?? null),
                    'nom_mere' => trim($_POST['nom_mere'] ?? null),
                    'province_origine' => trim($_POST['province_origine'] ?? null),
                    'territoire' => trim($_POST['territoire'] ?? null),
                    'secteur' => trim($_POST['secteur'] ?? null),
                    'groupement' => trim($_POST['groupement'] ?? null),
                    'village' => trim($_POST['village'] ?? null),
                    'num_permanent' => trim($_POST['num_permanent'] ?? null),
                    'statut_eleve' => 'inactif',
                ]);

                // Handle optional student photo upload (saved to public/uploads/avatars/eleve_{id}.{ext})
                if ($newStudent && !empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
                    $tmp = $_FILES['photo']['tmp_name'];
                    $mime = mime_content_type($tmp);
                    $maxBytes = 250 * 1024;
                    if (in_array($mime, array_keys($allowed), true) && empty($_FILES['photo']['size']) === false ? $_FILES['photo']['size'] <= $maxBytes : true) {
                        $ext = $allowed[$mime];
                        $projectRoot = dirname(__DIR__, 2);
                        $targetDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
                        if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
                        $fileName = 'eleve_' . (int) $newStudent['id'] . '.' . $ext;
                        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
                        if (move_uploaded_file($tmp, $targetPath)) {
                            // remove other extensions
                            foreach (['jpg','png'] as $e) {
                                if ($e === $ext) continue;
                                $other = $targetDir . DIRECTORY_SEPARATOR . 'eleve_' . (int) $newStudent['id'] . '.' . $e;
                                if (file_exists($other)) @unlink($other);
                            }
                            Eleve::update((int) $newStudent['id'], ['photo' => '/uploads/avatars/' . $fileName]);
                        }
                    }
                }

                unset($_SESSION['inscriptions_old']);
                $_SESSION['inscriptions_success'] = 'L’élève a été enregistré. La validation doit être effectuée par le secrétaire.';
                $this->redirect('/inscriptions');
            }

            $_SESSION['inscriptions_errors'] = $errors;
            $_SESSION['inscriptions_old'] = $_POST;
        }

        $this->redirect('/inscriptions/create');
    }

    public function edit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        $eleveId = (int) ($_GET['id'] ?? 0);
        if ($eleveId <= 0) {
            $this->redirect('/inscriptions');
        }

        $student = Eleve::findById($eleveId);
        if (!$student) {
            $_SESSION['inscriptions_errors'] = ['Élève introuvable.'];
            $this->redirect('/inscriptions');
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $parents = ParentModel::getAll();
        $oldInput = $_SESSION['inscriptions_old'] ?? [];
        unset($_SESSION['inscriptions_old']);

        $this->view('inscriptions/edit', [
            'title' => APP_NAME . ' - Éditer inscription',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'student' => $student,
            'parents' => $parents,
            'oldInput' => $oldInput,
        ]);
    }

    public function update(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eleveId = (int) ($_POST['eleve_id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $postnom = trim($_POST['postnom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $dateNaissance = trim($_POST['date_naissance'] ?? '');
            $matricule = trim($_POST['matricule'] ?? '');
            $parentId = (int) ($_POST['parent_id'] ?? 0);

            $errors = [];
            if ($eleveId <= 0) {
                $errors[] = 'Identifiant de l’élève invalide.';
            }
            if ($nom === '') {
                $errors[] = 'Le nom de l’élève est requis.';
            }
            if ($postnom === '') {
                $errors[] = 'Le postnom de l’élève est requis.';
            }
            if ($genre === '') {
                $errors[] = 'Le genre de l’élève est requis.';
            }
            if ($dateNaissance === '') {
                $errors[] = 'La date de naissance est requise.';
            }

            $student = Eleve::findById($eleveId);
            if (!$student) {
                $errors[] = 'Élève introuvable.';
            }

            if (empty($matricule)) {
                $matricule = $student['matricule'] ?? $this->generateMatricule($nom . ' ' . $postnom);
            }

            if (empty($errors)) {
                $updateData = [
                    'matricule' => $matricule,
                    'nom' => $nom,
                    'postnom' => $postnom,
                    'prenom' => $prenom ?: null,
                    'genre' => $genre,
                    'lieu_naissance' => trim($_POST['lieu_naissance'] ?? null),
                    'nationalite' => trim($_POST['nationalite'] ?? 'CONGOLAISE'),
                    'adresse' => trim($_POST['adresse'] ?? null),
                    'date_naissance' => $dateNaissance,
                    'parent_id' => $parentId > 0 ? $parentId : null,
                    'nom_pere' => trim($_POST['nom_pere'] ?? null),
                    'nom_mere' => trim($_POST['nom_mere'] ?? null),
                    'province_origine' => trim($_POST['province_origine'] ?? null),
                    'territoire' => trim($_POST['territoire'] ?? null),
                    'secteur' => trim($_POST['secteur'] ?? null),
                    'groupement' => trim($_POST['groupement'] ?? null),
                    'village' => trim($_POST['village'] ?? null),
                    'num_permanent' => trim($_POST['num_permanent'] ?? null),
                ];

                // Optional photo upload during edit
                if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
                    $tmp = $_FILES['photo']['tmp_name'];
                    $mime = mime_content_type($tmp);
                    $maxBytes = 250 * 1024;
                    if (in_array($mime, array_keys($allowed), true) && (empty($_FILES['photo']['size']) === false ? $_FILES['photo']['size'] <= $maxBytes : true)) {
                        $ext = $allowed[$mime];
                        $projectRoot = dirname(__DIR__, 2);
                        $targetDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
                        if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
                        $fileName = 'eleve_' . $eleveId . '.' . $ext;
                        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
                        if (move_uploaded_file($tmp, $targetPath)) {
                            foreach (['jpg','png'] as $e) {
                                if ($e === $ext) continue;
                                $other = $targetDir . DIRECTORY_SEPARATOR . 'eleve_' . $eleveId . '.' . $e;
                                if (file_exists($other)) @unlink($other);
                            }
                            $updateData['photo'] = '/uploads/avatars/' . $fileName;
                        }
                    }
                }

                Eleve::update($eleveId, $updateData);
                unset($_SESSION['inscriptions_old']);
                $_SESSION['inscriptions_success'] = 'Les informations de l’inscription ont été mises à jour.';
                $this->redirect('/inscriptions');
            }

            $_SESSION['inscriptions_errors'] = $errors;
            $_SESSION['inscriptions_old'] = $_POST;
            $this->redirect('/inscriptions/edit?id=' . $eleveId);
        }

        $this->redirect('/inscriptions');
    }

    // AJAX endpoints for cascading location selects
    public function provinces(): void
    {
        header('Content-Type: application/json');
        $provinces = [
            'Kinshasa', 'Nord-Kivu', 'Sud-Kivu', 'Haut-Katanga', 'Kongo Central', 'Équateur', 'Maniema', 'Ituri'
        ];
        echo json_encode(array_values($provinces));
        exit;
    }

    public function territoires(): void
    {
        header('Content-Type: application/json');
        $province = trim($_GET['province'] ?? '');
        $map = [
            'Kinshasa' => ['Funa','Kalamu','Kasa-Vubu','Lingwala'],
            'Nord-Kivu' => ['Goma','Beni','Masisi','Rutshuru'],
            'Sud-Kivu' => ['Uvira','Bukavu','Fizi'],
            'Haut-Katanga' => ['Lubumbashi','Kambove','Likasi'],
            'Kongo Central' => ['Matadi','Boma','Kimpese'],
        ];
        $list = $map[$province] ?? [];
        echo json_encode(array_values($list));
        exit;
    }

    public function secteurs(): void
    {
        header('Content-Type: application/json');
        $territoire = trim($_GET['territoire'] ?? '');
        $map = [
            'Goma' => ['Sector 1','Sector 2'],
            'Beni' => ['Beni-Centre','Mabalako'],
            'Lubumbashi' => ['Kawama','Katanga-Centre']
        ];
        $list = $map[$territoire] ?? [];
        echo json_encode(array_values($list));
        exit;
    }

    public function groupements(): void
    {
        header('Content-Type: application/json');
        $secteur = trim($_GET['secteur'] ?? '');
        $map = [
            'Sector 1' => ['Group A','Group B'],
            'Beni-Centre' => ['Group X','Group Y']
        ];
        $list = $map[$secteur] ?? [];
        echo json_encode(array_values($list));
        exit;
    }

    public function villages(): void
    {
        header('Content-Type: application/json');
        $groupement = trim($_GET['groupement'] ?? '');
        $map = [
            'Group A' => ['Village 1','Village 2'],
            'Group X' => ['Village X1','Village X2']
        ];
        $list = $map[$groupement] ?? [];
        echo json_encode(array_values($list));
        exit;
    }

    public function approve(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'sec_école']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eleveId = (int) ($_POST['eleve_id'] ?? 0);
            if ($eleveId > 0) {
                Eleve::approve($eleveId);
                $_SESSION['inscriptions_success'] = 'Inscription validée par le secrétaire.';
            }
        }

        $this->redirect('/inscriptions');
    }

    private function generateMatricule(string $name): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', mb_substr($name, 0, 6, 'UTF-8')));
        $prefix = $prefix === '' ? 'ELEVE' : $prefix;
        return sprintf('%s-%s-%s', $prefix, date('YmdHis'), substr(bin2hex(random_bytes(3)), 0, 6));
    }
}
