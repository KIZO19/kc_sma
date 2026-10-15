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
        $pending = $role === 'super_admin' ? Ecole::getPendingSchools(6) : [];
        $plans = $role === 'super_admin' ? Ecole::getPlans() : [];
        $availableAdmins = $role === 'super_admin' ? User::getAvailableEcoleAdmins() : [];

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
            $errors = [];
            $nom = trim($_POST['nom_etablissement'] ?? '');
            $email = trim($_POST['email_officiel'] ?? '');
            $identifiant = trim($_POST['identifiant'] ?? '');
            $matricule = trim($_POST['matricule'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $logoUrl = null;

            if (!empty($_FILES['logo']['name'])) {
                if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Erreur lors du téléchargement du logo.';
                } else {
                    $maxBytes = 250 * 1024;
                    if (!empty($_FILES['logo']['size']) && $_FILES['logo']['size'] > $maxBytes) {
                        $errors[] = 'Le logo doit faire au maximum 250 KB.';
                    }
                    $tmp = $_FILES['logo']['tmp_name'];
                    $mime = mime_content_type($tmp);
                    $allowed = ['image/png', 'image/jpeg'];
                    if (!in_array($mime, $allowed, true)) {
                        $errors[] = 'Seuls les formats PNG et JPEG sont autorisés pour le logo.';
                    }
                }

                if (empty($errors)) {
                    $logoUrl = $this->uploadLogoFile($_FILES['logo']);
                    if ($logoUrl === null) {
                        $errors[] = 'Impossible de téléverser le logo (format/taille incorrect).';
                    }
                }
            }

            if ($nom === '') {
                $errors[] = 'Le nom de l\'établissement est requis.';
            }
            if ($email === '') {
                $errors[] = 'L\'email officiel est requis.';
            }
            if ($identifiant === '') {
                $errors[] = 'L\'identifiant est requis.';
            }

            if (empty($errors)) {
                $matricule = $this->generateSchoolMatricule();

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
                    $errors[] = 'Impossible de créer l\'école, réessayez.';
                } else {
                    $existingAdminId = (int) ($_POST['existing_admin_id'] ?? 0);
                    $newAdminName = trim($_POST['admin_nom'] ?? '');
                    $newAdminIdent = trim($_POST['admin_identifiant'] ?? '');
                    $newAdminPass = trim($_POST['admin_mot_de_passe'] ?? '');
                    $adminUserId = null;

                    if ($existingAdminId > 0) {
                        \App\Models\User::assignToSchool($existingAdminId, (int) $ecoleId);
                        $adminUserId = $existingAdminId;
                    } elseif ($newAdminIdent !== '' && $newAdminName !== '') {
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
                            $_SESSION['ecoles_create_success'] = 'École créée. Mot de passe temporaire pour l\'admin: ' . $newAdminPass;
                        }
                    }

                    if ($adminUserId) {
                        Ecole::setAdmin((int) $ecoleId, $adminUserId);
                    }
                }
            }

            if (!empty($errors)) {
                $_SESSION['ecoles_create_errors'] = $errors;
                $this->redirect('/ecoles/create');
                return;
            }

            $this->redirect('/ecoles');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $availableAdmins = \App\Models\User::getAvailableEcoleAdmins();

        $this->view('ecoles/create', [
            'title' => APP_NAME . ' - Créer une école',
            'user' => $user,
            'role' => $role,
            'roleLabel' => \App\Models\User::getRoleLabel($role),
            'modules' => $modules,
            'availableAdmins' => $availableAdmins,
        ]);
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
            $school = Ecole::findById($id);
            $matricule = trim($_POST['matricule'] ?? '');
            if ($matricule === '' && (!empty($school['matricule']) || $school === null)) {
                $matricule = $school['matricule'] ?? $this->generateSchoolMatricule();
            } elseif ($matricule === '') {
                $matricule = $this->generateSchoolMatricule();
            }

            $data = [
                'nom_etablissement' => trim($_POST['nom_etablissement'] ?? ''),
                'email_officiel' => trim($_POST['email_officiel'] ?? ''),
                'matricule' => $matricule,
                'telephone_contact' => trim($_POST['telephone'] ?? ''),
                'adresse' => trim($_POST['adresse'] ?? ''),
                'statut_systeme' => $_POST['statut_systeme'] ?? null,
            ];
            $errors = [];

            if ($data['nom_etablissement'] === '') $errors[] = 'Le nom de l\'établissement est requis.';

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

    public function generatePassword(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $errors = [];
        $success = null;
        $matricule = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matricule = trim($_POST['matricule'] ?? '');

            if ($matricule === '') {
                $errors[] = 'Le matricule de l\'élève est requis.';
            } else {
                $eleveUser = User::findByEleveMatricule($matricule);

                if (!$eleveUser) {
                    $errors[] = 'Aucun élève trouvé pour ce matricule.';
                } else {
                    if ($role === 'ecole_admin' && !empty($user['ecole_id']) && $eleveUser['ecole_id'] !== $user['ecole_id']) {
                        $errors[] = 'Vous ne pouvez générer un mot de passe que pour les élèves de votre établissement.';
                    } else {
                        $tempPassword = bin2hex(random_bytes(4));
                        $ok = User::updateProfile((int) $eleveUser['id'], ['mot_de_passe' => password_hash($tempPassword, PASSWORD_DEFAULT)]);
                        if ($ok) {
                            $success = 'Mot de passe généré pour ' . htmlspecialchars($matricule) . ' : ' . $tempPassword;
                        } else {
                            $errors[] = 'Impossible de mettre à jour le mot de passe. Réessayez.';
                        }
                    }
                }
            }
        }

        $this->view('ecoles/generate_password', [
            'title' => APP_NAME . ' - Générer mot de passe élève',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'errors' => $errors,
            'success' => $success,
            'matricule' => $matricule,
        ]);
    }

    private function generateMatricule(string $name): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', mb_substr($name, 0, 6, 'UTF-8')));
        $prefix = $prefix === '' ? 'SCH' : $prefix;
        return sprintf('%s-%s-%s', $prefix, date('YmdHis'), substr(bin2hex(random_bytes(4)), 0, 6));
    }

    private function generateSchoolMatricule(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $length = strlen($chars);

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $matricule = '';
            for ($i = 0; $i < 5; $i++) {
                $matricule .= $chars[random_int(0, $length - 1)];
            }

            if (!Ecole::findByMatricule($matricule)) {
                return $matricule;
            }
        }

        return 'SCH' . substr(bin2hex(random_bytes(2)), 0, 3);
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

    public function landing(): void
    {
        $this->view('ecoles/landing', [
            'title' => APP_NAME . ' - Landing',
        ]);
    }

    public function subscriptionRequired(): void
    {
        Auth::requireAuth();
        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $school = $ecoleId > 0 ? Ecole::findById($ecoleId) : null;

        if (($user['role'] ?? '') === 'super_admin' || ($ecoleId > 0 && Ecole::hasPlatformAccess($ecoleId))) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('ecoles/subscription_required', [
            'title' => APP_NAME . ' - Accès école suspendu',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'school' => $school,
            'subscription' => $ecoleId > 0 ? Ecole::getLatestSubscription($ecoleId) : null,
        ]);
    }

    public function plans(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $planId = (int) ($_POST['plan_id'] ?? 0);
            $name = trim((string) ($_POST['nom_plan'] ?? ''));
            $durationInput = trim((string) ($_POST['duree_mois'] ?? ''));
            $duration = filter_var($durationInput, FILTER_VALIDATE_INT);
            $priceInput = trim((string) ($_POST['prix'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $errors = [];

            if ($name === '' || mb_strlen($name, 'UTF-8') > 50) {
                $errors[] = 'Le nom du tarif est requis et ne peut pas dépasser 50 caractères.';
            }
            if ($duration === false || $duration <= 0 || $duration > 1200) {
                $errors[] = 'La durée doit être comprise entre 1 et 1 200 mois.';
            }
            if ($priceInput === '' || !is_numeric($priceInput) || (float) $priceInput < 0) {
                $errors[] = 'Le tarif doit être un montant positif ou nul.';
            }

            $existingPlanIds = array_map(static fn (array $plan): int => (int) ($plan['id'] ?? 0), Ecole::getPlans());
            if ($planId > 0 && !in_array($planId, $existingPlanIds, true)) {
                $errors[] = 'Le tarif à modifier est introuvable.';
            }

            if (empty($errors)) {
                $saved = Ecole::savePlan([
                    'nom_plan' => $name,
                    'duree_mois' => $duration,
                    'prix' => (float) $priceInput,
                    'description' => $description !== '' ? $description : null,
                ], $planId > 0 ? $planId : null);
                if ($saved) {
                    $_SESSION['ecoles_plans_success'] = $planId > 0
                        ? 'Tarif mis à jour. Les abonnements existants conservent leur montant payé.'
                        : 'Nouveau tarif créé.';
                } else {
                    $_SESSION['ecoles_plans_errors'] = ['Impossible d’enregistrer ce tarif. Vérifiez les données et réessayez.'];
                }
            } else {
                $_SESSION['ecoles_plans_errors'] = $errors;
            }

            $this->redirect('/ecoles/plans');
            return;
        }

        $this->view('ecoles/plans', [
            'title' => APP_NAME . ' - Tarifs des abonnements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'plans' => Ecole::getPlans(),
        ]);
    }

    public function addSubscription(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $ecoleId = (int) ($_POST['ecole_id'] ?? 0);
            $subscriptionType = (string) ($_POST['type_abonnement'] ?? 'plan');
            $isTrial = $subscriptionType === 'trial';
            $planId = $isTrial ? null : (int) ($_POST['plan_id'] ?? 0);
            $dateDebut = trim($_POST['date_debut'] ?? '');
            $dateFin = '';
            $amount = 0.0;
            $durationDays = filter_var(trim((string) ($_POST['duree_essai_jours'] ?? '')), FILTER_VALIDATE_INT);

            if ($ecoleId <= 0 || !Ecole::findById($ecoleId)) {
                $errors[] = 'Sélectionnez une école valide.';
            }
            $selectedPlan = null;
            if ($isTrial) {
                if ($durationDays === false || $durationDays <= 0 || $durationDays > 3650) {
                    $errors[] = 'La durée de l’essai doit être comprise entre 1 et 3 650 jours.';
                }
            } elseif ($subscriptionType === 'plan') {
                foreach (Ecole::getPlans() as $plan) {
                    if ((int) ($plan['id'] ?? 0) === $planId) {
                        $selectedPlan = $plan;
                        break;
                    }
                }
                if ($planId <= 0 || $selectedPlan === null) {
                    $errors[] = 'Sélectionnez un plan valide.';
                } else {
                    $amount = (float) $selectedPlan['prix'];
                }
            } else {
                $errors[] = 'Sélectionnez un type d’abonnement valide.';
            }

            $startDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateDebut);
            if (!$startDate || $startDate->format('Y-m-d') !== $dateDebut) {
                $errors[] = 'La date de début de l’abonnement est invalide.';
            }
            if ($startDate && $isTrial && $durationDays !== false && $durationDays > 0 && $durationDays <= 3650) {
                $dateFin = $startDate->modify('+' . ($durationDays - 1) . ' days')->format('Y-m-d');
            } elseif ($selectedPlan !== null && $startDate) {
                $durationMonths = (int) ($selectedPlan['duree_mois'] ?? 0);
                if ($durationMonths <= 0) {
                    $errors[] = 'La durée du tarif sélectionné est invalide.';
                } else {
                    $endMonth = $startDate->modify('first day of this month')->modify('+' . $durationMonths . ' months');
                    $endDay = min(
                        (int) $startDate->format('d'),
                        (int) $endMonth->format('t')
                    );
                    $endDate = $endMonth
                        ->setDate((int) $endMonth->format('Y'), (int) $endMonth->format('m'), $endDay)
                        ->modify('-1 day');
                    $dateFin = $endDate->format('Y-m-d');
                }
            }

            if (empty($errors)) {
                if (Ecole::addSubscription($ecoleId, $planId, $dateDebut, $dateFin, $amount)) {
                    $_SESSION['ecoles_subscription_success'] = $isTrial
                        ? 'Période d’essai gratuite ajoutée avec succès.'
                        : 'Abonnement ajouté avec succès.';
                    $this->redirect('/ecoles');
                    return;
                }
                $errors[] = 'Impossible d’enregistrer l’abonnement. Vérifiez les données et réessayez.';
            }

            $_SESSION['ecoles_subscription_errors'] = $errors;
            $this->redirect('/ecoles/addSubscription');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $schools = Ecole::getAll();
        $plans = Ecole::getPlans();
        $selectedSchoolId = (int) ($_GET['ecole_id'] ?? 0);

        $this->view('ecoles/add_subscription', [
            'title' => APP_NAME . ' - Ajouter un abonnement',
            'user' => $user,
            'role' => $role,
            'roleLabel' => \App\Models\User::getRoleLabel($role),
            'modules' => $modules,
            'schools' => $schools,
            'plans' => $plans,
            'selectedSchoolId' => $selectedSchoolId,
        ]);
    }

    public function subscriptions(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $selectedSchoolId = max(0, (int) ($_GET['ecole_id'] ?? 0));
        if ($selectedSchoolId > 0 && !Ecole::findById($selectedSchoolId)) {
            $selectedSchoolId = 0;
        }
        $this->view('ecoles/subscriptions', [
            'title' => APP_NAME . ' - Gestion des abonnements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'subscriptions' => Ecole::getSubscriptions($selectedSchoolId > 0 ? $selectedSchoolId : null),
            'schools' => Ecole::getAll(),
            'plans' => Ecole::getPlans(),
            'selectedSchoolId' => $selectedSchoolId,
        ]);
    }

    public function updateSubscription(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        $subscriptionId = (int) ($_POST['subscription_id'] ?? 0);
        $planInput = trim((string) ($_POST['plan_id'] ?? ''));
        $planId = $planInput === '' ? null : filter_var($planInput, FILTER_VALIDATE_INT);
        $dateDebut = trim((string) ($_POST['date_debut'] ?? ''));
        $dateFin = trim((string) ($_POST['date_fin'] ?? ''));
        $status = (string) ($_POST['statut_abonnement'] ?? '');
        $errors = [];

        $subscriptionIds = array_map(
            static fn (array $subscription): int => (int) ($subscription['id'] ?? 0),
            Ecole::getSubscriptions()
        );
        if ($subscriptionId <= 0 || !in_array($subscriptionId, $subscriptionIds, true)) {
            $errors[] = 'L’abonnement sélectionné est introuvable.';
        }

        if ($planId !== null) {
            $planIds = array_map(
                static fn (array $plan): int => (int) ($plan['id'] ?? 0),
                Ecole::getPlans()
            );
            if ($planId === false || !in_array($planId, $planIds, true)) {
                $errors[] = 'Sélectionnez un plan valide ou l’essai gratuit.';
            }
        }

        $startDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateDebut);
        $endDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateFin);
        if (!$startDate || $startDate->format('Y-m-d') !== $dateDebut) {
            $errors[] = 'La date de début est invalide.';
        }
        if (!$endDate || $endDate->format('Y-m-d') !== $dateFin) {
            $errors[] = 'La date de fin est invalide.';
        }
        if ($startDate && $endDate && $endDate < $startDate) {
            $errors[] = 'La date de fin doit être égale ou postérieure à la date de début.';
        }
        if (!in_array($status, ['Actif', 'Expire', 'Resilie'], true)) {
            $errors[] = 'Le statut d’abonnement sélectionné est invalide.';
        }

        if (empty($errors) && Ecole::updateSubscription(
            $subscriptionId,
            $planId,
            $dateDebut,
            $dateFin,
            $status
        )) {
            $_SESSION['ecoles_subscription_success'] = 'Abonnement mis à jour. Le montant déjà enregistré comme payé a été conservé.';
        } else {
            if (empty($errors)) {
                $errors[] = 'Impossible de mettre à jour cet abonnement. Réessayez.';
            }
            $_SESSION['ecoles_subscription_errors'] = $errors;
        }

        $this->redirect('/ecoles/subscriptions');
    }
}
