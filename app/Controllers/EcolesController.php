<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ecole;
use App\Models\User;

class EcolesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        if ($role === 'ecole_admin') {
            $schools = [];
            $myId = $user['ecole_id'] ?? null;
            if ($myId) {
                $s = Ecole::findById((int) $myId);
                if ($s) $schools = [$s];
            }
        } else {
            $schools = Ecole::getAll();
        }
        $pending = Ecole::getPendingSchools(6);
        $plans = Ecole::getPlans();
        $availableAdmins = \App\Models\User::getAvailableEcoleAdmins();

        $this->view('dashboard/ecoles', [
            'title' => APP_NAME,
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'schools' => $schools,
            'pending' => $pending,
            'plans' => $plans,
            'availableAdmins' => $availableAdmins,
        ]);
    }

    public function updateStatus(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['ecole_id'] ?? 0);
            $status = $_POST['statut_systeme'] ?? 'En_Attente';

            if ($id > 0 && in_array($status, ['Actif', 'Suspendu', 'En_Attente'], true)) {
                Ecole::updateSystemStatus($id, $status);
            }
        }

        $this->redirect('/ecoles');
    }

    public function confirm(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['ecole_id'] ?? 0);

            if ($id > 0) {
                Ecole::updateSystemStatus($id, 'Actif');
            }
        }

        $this->redirect('/ecoles');
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom_etablissement'] ?? '');
            $email = trim($_POST['email_officiel'] ?? '');
            $identifiant = trim($_POST['identifiant'] ?? '');
            $matricule = trim($_POST['matricule'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $logoUrl = null;

            if (!empty($_FILES['logo']['name'])) {
                    // Validate logo before attempting upload: only PNG and JPEG, max 250 KB
                    $logoErrors = [];
                    if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                        $logoErrors[] = 'Erreur lors du téléchargement du logo.';
                    } else {
                        $maxBytes = 250 * 1024;
                        if (!empty($_FILES['logo']['size']) && $_FILES['logo']['size'] > $maxBytes) {
                            $logoErrors[] = 'Le logo doit faire au maximum 250 KB.';
                        }
                        $tmp = $_FILES['logo']['tmp_name'];
                        $mime = mime_content_type($tmp);
                        $allowed = ['image/png', 'image/jpeg'];
                        if (!in_array($mime, $allowed, true)) {
                            $logoErrors[] = 'Seuls les formats PNG et JPEG sont autorisés pour le logo.';
                        }
                    }

                    if (!empty($logoErrors)) {
                        $_SESSION['ecoles_errors'] = $logoErrors;
                        $this->redirect('/ecoles');
                        return;
                    }

                    $logoUrl = $this->uploadLogoFile($_FILES['logo']);
            }

            if ($nom !== '' && $email !== '' && $identifiant !== '') {
                if ($matricule === '') {
                    $matricule = $this->generateMatricule($nom);
                }

                // Create school first to obtain id
                $ecoleId = Ecole::create([
                    'nom_etablissement' => $nom,
                    'email_officiel' => $email,
                    'identifiant' => $identifiant,
                    'matricule' => $matricule,
                    'telephone_contact' => $telephone,
                    'adresse' => $adresse,
                    'logo_url' => $logoUrl,
                    'statut_systeme' => 'En_Attente',
                ]);

                if ($ecoleId === false) {
                    $_SESSION['ecoles_errors'] = ['Impossible de créer l\'école, réessayez.'];
                    $this->redirect('/ecoles');
                    return;
                }

                // Handle admin: create new or assign existing
                $existingAdminId = (int) ($_POST['existing_admin_id'] ?? 0);
                $newAdminName = trim($_POST['admin_nom'] ?? '');
                $newAdminIdent = trim($_POST['admin_identifiant'] ?? '');
                $newAdminPass = trim($_POST['admin_mot_de_passe'] ?? '');

                $adminUserId = null;

                if ($existingAdminId > 0) {
                    // assign existing admin
                    \App\Models\User::assignToSchool($existingAdminId, (int) $ecoleId);
                    $adminUserId = $existingAdminId;
                } elseif ($newAdminIdent !== '' && $newAdminName !== '') {
                    // create new admin (generate password if empty)
                    if ($newAdminPass === '') {
                        $newAdminPass = bin2hex(random_bytes(4));
                    }
                    $user = \App\Models\User::create([
                        'nom_complet' => $newAdminName,
                        'identifiant' => $newAdminIdent,
                        'mot_de_passe' => password_hash($newAdminPass, PASSWORD_DEFAULT),
                        'role' => 'ecole_admin',
                        'statut' => 'Actif',
                        'ecole_id' => (int) $ecoleId,
                    ]);
                    if (!empty($user['id'])) {
                        $adminUserId = (int) $user['id'];
                        // Optionally: store temp password somewhere or display it (not emailing)
                        $_SESSION['ecoles_success'] = 'École créée. Mot de passe temporaire pour l\'admin: ' . $newAdminPass;
                    }
                }

                if ($adminUserId) {
                    Ecole::setAdmin((int) $ecoleId, $adminUserId);
                }
            }
        }

        $this->redirect('/ecoles');
    }

    public function edit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            // redirect to list
            $this->redirect('/ecoles');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom_etablissement' => trim($_POST['nom_etablissement'] ?? ''),
                'email_officiel' => trim($_POST['email_officiel'] ?? ''),
                'identifiant' => trim($_POST['identifiant'] ?? ''),
                'matricule' => trim($_POST['matricule'] ?? ''),
                'telephone_contact' => trim($_POST['telephone'] ?? ''),
                'adresse' => trim($_POST['adresse'] ?? ''),
                'statut_systeme' => $_POST['statut_systeme'] ?? null,
            ];
            $errors = [];

            if ($data['nom_etablissement'] === '') $errors[] = 'Le nom de l\'établissement est requis.';
            if ($data['identifiant'] === '') $errors[] = 'L\'identifiant est requis.';

            // Handle optional logo upload
            if (!empty($_FILES['logo']['name'])) {
                $logoUrl = $this->uploadLogoFile($_FILES['logo']);
                if ($logoUrl) {
                    $data['logo_url'] = $logoUrl;
                } else {
                    $errors[] = 'Impossible de téléverser le logo (format/taille).';
                }
            }

            if (!empty($errors)) {
                $_SESSION['ecole_edit_errors'] = $errors;
            } else {
                $ok = Ecole::update($id, $data);
                if ($ok) {
                    $_SESSION['ecole_edit_success'] = 'École mise à jour.';
                } else {
                    $_SESSION['ecole_edit_errors'] = ['Erreur lors de la mise à jour.'];
                }
                $this->redirect('/ecoles');
                return;
            }
        }

        $school = Ecole::findById($id);
        if (!$school) {
            $this->redirect('/ecoles');
            return;
        }

        $this->view('ecoles/edit', [
            'title' => APP_NAME . ' - Modifier école',
            'user' => $user,
            'role' => $role,
            'roleLabel' => \App\Models\User::getRoleLabel($role),
            'modules' => $modules,
            'school' => $school,
        ]);
    }

    public function requests(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $total = Ecole::countBySystemStatus('En_Attente');
        $pending = Ecole::getPendingSchoolsPaged($perPage, $offset);

        $this->view('ecoles/requests', [
            'title' => APP_NAME . ' - Demandes écoles',
            'user' => $user,
            'role' => $role,
            'roleLabel' => \App\Models\User::getRoleLabel($role),
            'modules' => $modules,
            'pending' => $pending,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function delete(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['ecole_id'] ?? 0);
            if ($id > 0) {
                Ecole::delete($id);
            }
        }

        $this->redirect('/ecoles/requests');
    }

    private function generateMatricule(string $name): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', mb_substr($name, 0, 6, 'UTF-8')));
        $prefix = $prefix === '' ? 'SCH' : $prefix;
        return sprintf('%s-%s-%s', $prefix, date('YmdHis'), substr(bin2hex(random_bytes(4)), 0, 6));
    }

    private function uploadLogoFile(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowedTypes = ['image/png', 'image/jpeg'];
        if (!in_array($file['type'], $allowedTypes, true)) {
            return null;
        }

        // Enforce max size 250 KB
        $maxBytes = 250 * 1024;
        if (!empty($file['size']) && $file['size'] > $maxBytes) {
            return null;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'school_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . strtolower($extension);
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/school_logos';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return null;
        }

        $targetPath = $uploadDir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/uploads/school_logos/' . $filename;
        }

        return null;
    }

    public function addSubscription(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ecoleId = (int) ($_POST['ecole_id'] ?? 0);
            $planId = (int) ($_POST['plan_id'] ?? 0);
            $montant = (float) ($_POST['montant_paye'] ?? 0);
            $dateDebut = trim($_POST['date_debut'] ?? '');
            $dateFin = trim($_POST['date_fin'] ?? '');

            if ($ecoleId > 0 && $planId > 0 && $dateDebut !== '' && $dateFin !== '') {
                Ecole::addSubscription($ecoleId, $planId, $dateDebut, $dateFin, $montant);
            }
        }

        $this->redirect('/ecoles');
    }
}
