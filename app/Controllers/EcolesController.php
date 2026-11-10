<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ecole;
use App\Models\ActivityLog;
use App\Models\Derogation;
use App\Models\NotificationAccess;
use App\Models\AnneeScolaire;
use App\Models\SchoolYearChangeRequest;
use App\Models\SchoolMessage;
use App\Models\SubscriptionRequest;
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
            $schoolId = (int) ($user['ecole_id'] ?? 0);
            $school = $schoolId > 0 ? Ecole::findById($schoolId) : null;
            if (!$school) {
                $this->redirect('/error/accessDenied');
                return;
            }

            $this->view('ecoles/school_details', [
                'title' => APP_NAME . ' - Détails de l’école',
                'user' => $user,
                'role' => $role,
                'roleLabel' => User::getRoleLabel($role),
                'modules' => $modules,
                'school' => $school,
                'subscription' => Ecole::getLatestSubscription($schoolId),
            ]);
            return;
        }

        $schools = Ecole::getAll();
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

    public function settings(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $school = $schoolId > 0 ? Ecole::findById($schoolId) : null;
        if (!$school) {
            $this->redirect('/error/accessDenied');
            return;
        }

        if (empty($_SESSION['school_settings_csrf'])) {
            $_SESSION['school_settings_csrf'] = bin2hex(random_bytes(32));
        }

        $this->view('ecoles/settings', [
            'title' => APP_NAME . ' - Paramètres de l’école',
            'user' => $user,
            'role' => $user['role'] ?? 'ecole_admin',
            'roleLabel' => User::getRoleLabel((string) ($user['role'] ?? 'ecole_admin')),
            'modules' => $this->getModulesForRole((string) ($user['role'] ?? 'ecole_admin')),
            'school' => $school,
            'activeSchoolYear' => AnneeScolaire::getActiveBySchool($schoolId),
            'latestSchoolYearRequest' => SchoolYearChangeRequest::getLatestForSchool($schoolId),
            'schoolFormValues' => $_SESSION['school_settings_old'] ?? [],
            'subscription' => Ecole::getLatestSubscription($schoolId),
            'notificationSettings' => NotificationAccess::getSchoolPreferences($schoolId),
            'derogationRecipientOptions' => Derogation::getRecipientOptions($schoolId),
            'derogationRecipientUserIds' => Derogation::getRecipientUserIds($schoolId),
            'messageRecipientOptions' => SchoolMessage::getParentRecipientOptions($schoolId),
            'messageRecipientUserIds' => SchoolMessage::getParentRecipientUserIds($schoolId),
            'csrfToken' => $_SESSION['school_settings_csrf'],
            'success' => $_SESSION['school_settings_success'] ?? null,
            'error' => $_SESSION['school_settings_error'] ?? null,
        ]);
        unset(
            $_SESSION['school_settings_success'],
            $_SESSION['school_settings_error'],
            $_SESSION['school_settings_old']
        );
    }

    public function setInitialSchoolYear(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $schoolId <= 0
            || $token === ''
            || !hash_equals((string) ($_SESSION['school_settings_csrf'] ?? ''), $token)
        ) {
            $_SESSION['school_settings_error'] = 'Le formulaire a expiré. Rechargez la page et réessayez.';
            $this->redirect('/ecoles/settings');
            return;
        }

        try {
            $year = AnneeScolaire::validateYear(\is_string($_POST['annee_scolaire'] ?? null)
                ? $_POST['annee_scolaire']
                : '');
            AnneeScolaire::setInitialForSchool($schoolId, $year);
            ActivityLog::record(
                (int) ($user['id'] ?? 0),
                'school_year_initialized',
                'school',
                $schoolId,
                (string) $year,
                'Année scolaire d’exercice initiale enregistrée.'
            );
            $_SESSION['school_settings_success'] = 'L’année scolaire d’exercice a été enregistrée.';
        } catch (\InvalidArgumentException|\DomainException $e) {
            $_SESSION['school_settings_error'] = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('EcolesController::setInitialSchoolYear failed: ' . $e->getMessage());
            $_SESSION['school_settings_error'] = 'Impossible d’enregistrer l’année scolaire. Réessayez.';
        }

        $_SESSION['school_settings_csrf'] = bin2hex(random_bytes(32));
        $this->redirect('/ecoles/settings');
    }

    public function requestSchoolYearChange(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $schoolId <= 0
            || $token === ''
            || !hash_equals((string) ($_SESSION['school_settings_csrf'] ?? ''), $token)
        ) {
            $_SESSION['school_settings_error'] = 'Le formulaire a expiré. Rechargez la page et réessayez.';
            $this->redirect('/ecoles/settings');
            return;
        }

        try {
            $year = AnneeScolaire::validateYear(\is_string($_POST['annee_scolaire'] ?? null)
                ? $_POST['annee_scolaire']
                : '');
            SchoolYearChangeRequest::create($schoolId, (int) ($user['id'] ?? 0), $year);
            ActivityLog::record(
                (int) ($user['id'] ?? 0),
                'school_year_change_requested',
                'school',
                $schoolId,
                $year,
                'Demande de changement d’année scolaire transmise au super-administrateur.'
            );
            $_SESSION['school_settings_success'] = 'Votre demande de changement d’année scolaire a été transmise au super-administrateur.';
        } catch (\InvalidArgumentException|\DomainException $e) {
            $_SESSION['school_settings_error'] = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('EcolesController::requestSchoolYearChange failed: ' . $e->getMessage());
            $_SESSION['school_settings_error'] = 'Impossible de transmettre la demande de changement. Réessayez.';
        }

        $_SESSION['school_settings_csrf'] = bin2hex(random_bytes(32));
        $this->redirect('/ecoles/settings');
    }

    public function updateSchoolProfile(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $schoolId <= 0
            || $token === ''
            || !hash_equals((string) ($_SESSION['school_settings_csrf'] ?? ''), $token)
        ) {
            $_SESSION['school_settings_error'] = 'Le formulaire a expiré. Rechargez la page et réessayez.';
            $this->redirect('/ecoles/settings');
            return;
        }

        $profile = [
            'nom_etablissement' => is_string($_POST['nom_etablissement'] ?? null)
                ? trim($_POST['nom_etablissement'])
                : '',
            'email_officiel' => is_string($_POST['email_officiel'] ?? null)
                ? trim($_POST['email_officiel'])
                : '',
            'telephone_contact' => is_string($_POST['telephone_contact'] ?? null)
                ? trim($_POST['telephone_contact'])
                : '',
            'adresse' => is_string($_POST['adresse'] ?? null)
                ? trim($_POST['adresse'])
                : '',
        ];
        $errors = [];

        if ($profile['nom_etablissement'] === '' || mb_strlen($profile['nom_etablissement'], 'UTF-8') > 150) {
            $errors[] = 'Le nom de l’établissement est requis et ne peut pas dépasser 150 caractères.';
        }
        if (
            mb_strlen($profile['email_officiel'], 'UTF-8') > 100
            || ($profile['email_officiel'] !== '' && !filter_var($profile['email_officiel'], FILTER_VALIDATE_EMAIL))
        ) {
            $errors[] = 'Saisissez une adresse e-mail valide de 100 caractères maximum.';
        }
        if (mb_strlen($profile['telephone_contact'], 'UTF-8') > 20) {
            $errors[] = 'Le numéro de téléphone ne peut pas dépasser 20 caractères.';
        }

        $logoFile = $_FILES['logo'] ?? null;
        if (is_array($logoFile) && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $errors[] = 'Erreur lors du téléchargement du logo.';
            } elseif (($logoFile['size'] ?? 0) > 250 * 1024) {
                $errors[] = 'Le logo doit faire au maximum 250 KB.';
            }
        }

        $logoUrl = null;
        if (empty($errors) && is_array($logoFile) && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $logoUrl = $this->uploadLogoFile($logoFile);
            if ($logoUrl === null) {
                $errors[] = 'Impossible de téléverser le logo. Utilisez une image PNG ou JPEG de 250 KB maximum.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['school_settings_error'] = implode(' ', $errors);
            $_SESSION['school_settings_old'] = $profile;
        } else {
            $data = $profile;
            if ($logoUrl !== null) {
                $data['logo_url'] = $logoUrl;
            }

            if (Ecole::update($schoolId, $data)) {
                ActivityLog::record(
                    (int) ($user['id'] ?? 0),
                    'school_profile_updated',
                    'school',
                    $schoolId,
                    $profile['nom_etablissement'],
                    'Informations de l’école mises à jour.'
                );
                $_SESSION['school_settings_success'] = 'Les informations de l’école ont été mises à jour.';
            } else {
                if ($logoUrl !== null) {
                    $uploadedPath = dirname(__DIR__, 2) . '/public' . $logoUrl;
                    if (is_file($uploadedPath)) {
                        unlink($uploadedPath);
                    }
                }
                $_SESSION['school_settings_error'] = 'Impossible d’enregistrer les informations de l’école. Réessayez.';
                $_SESSION['school_settings_old'] = $profile;
            }
        }

        $_SESSION['school_settings_csrf'] = bin2hex(random_bytes(32));
        $this->redirect('/ecoles/settings');
    }

    public function updateDerogationRecipients(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $schoolId <= 0
            || $token === ''
            || !hash_equals((string) ($_SESSION['school_settings_csrf'] ?? ''), $token)
        ) {
            $_SESSION['school_settings_error'] = 'Le formulaire a expiré. Rechargez la page et réessayez.';
            $this->redirect('/ecoles/settings');
            return;
        }

        try {
            $recipientIds = $_POST['recipient_user_ids'] ?? [];
            if (!is_array($recipientIds)) {
                throw new \DomainException('La liste des destinataires est invalide.');
            }
            Derogation::saveRecipientUserIds($schoolId, $recipientIds, (int) ($user['id'] ?? 0));
            $school = Ecole::findById($schoolId);
            ActivityLog::record(
                (int) ($user['id'] ?? 0),
                'derogation_recipients_updated',
                'school',
                $schoolId,
                (string) ($school['nom_etablissement'] ?? 'École'),
                'Destinataires des demandes de dérogation mis à jour.'
            );
            $_SESSION['school_settings_success'] = 'Les destinataires des demandes de dérogation ont été enregistrés.';
        } catch (\DomainException $e) {
            $_SESSION['school_settings_error'] = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('EcolesController::updateDerogationRecipients error: ' . $e->getMessage());
            $_SESSION['school_settings_error'] = 'Impossible d’enregistrer les destinataires. Réessayez.';
        }

        $_SESSION['school_settings_csrf'] = bin2hex(random_bytes(32));
        $this->redirect('/ecoles/settings');
    }

    public function updateMessageRecipients(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $schoolId <= 0
            || $token === ''
            || !hash_equals((string) ($_SESSION['school_settings_csrf'] ?? ''), $token)
        ) {
            $_SESSION['school_settings_error'] = 'Le formulaire a expiré. Rechargez la page et réessayez.';
            $this->redirect('/ecoles/settings');
            return;
        }

        try {
            $recipientIds = $_POST['message_recipient_user_ids'] ?? [];
            if (!is_array($recipientIds)) {
                throw new \DomainException('La liste des destinataires est invalide.');
            }
            SchoolMessage::saveParentRecipientUserIds(
                $schoolId,
                $recipientIds,
                (int) ($user['id'] ?? 0)
            );
            $school = Ecole::findById($schoolId);
            ActivityLog::record(
                (int) ($user['id'] ?? 0),
                'school_message_recipients_updated',
                'school',
                $schoolId,
                (string) ($school['nom_etablissement'] ?? 'École'),
                'Destinataires des messages des parents mis à jour.'
            );
            $_SESSION['school_settings_success'] = 'Les destinataires des messages des parents ont été enregistrés.';
        } catch (\DomainException $exception) {
            $_SESSION['school_settings_error'] = $exception->getMessage();
        } catch (\Throwable $exception) {
            error_log('EcolesController::updateMessageRecipients error: ' . $exception->getMessage());
            $_SESSION['school_settings_error'] = 'Impossible d’enregistrer les destinataires des messages. Réessayez.';
        }

        $_SESSION['school_settings_csrf'] = bin2hex(random_bytes(32));
        $this->redirect('/ecoles/settings');
    }

    public function updateSchoolNotificationAccess(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $schoolId <= 0
            || $token === ''
            || !hash_equals((string) ($_SESSION['school_settings_csrf'] ?? ''), $token)
        ) {
            $_SESSION['school_settings_error'] = 'Le formulaire a expiré ou les paramètres sont invalides. Rechargez la page et réessayez.';
            $this->redirect('/ecoles/settings');
            return;
        }

        $smsInput = $_POST['sms_enabled'] ?? null;
        $whatsappInput = $_POST['whatsapp_enabled'] ?? null;
        $currentSettings = NotificationAccess::getSchoolPreferences($schoolId);
        if (
            !in_array($smsInput, ['0', '1', 0, 1], true)
            || !in_array($whatsappInput, ['0', '1', 0, 1], true)
            || (
                (string) $smsInput === '1'
                && empty($currentSettings['sms_allowed'])
                && empty($currentSettings['sms_enabled'])
            )
            || (
                (string) $whatsappInput === '1'
                && empty($currentSettings['whatsapp_allowed'])
                && empty($currentSettings['whatsapp_enabled'])
            )
        ) {
            $_SESSION['school_settings_error'] = 'Un canal de notification demandé n’est pas autorisé par le super-administrateur.';
            $this->redirect('/ecoles/settings');
            return;
        }

        $smsEnabled = (string) $smsInput === '1';
        $whatsappEnabled = (string) $whatsappInput === '1';
        if (NotificationAccess::updateSchoolPreferences(
            $schoolId,
            $smsEnabled,
            $whatsappEnabled,
            (int) ($user['id'] ?? 0)
        )) {
            $school = Ecole::findById($schoolId);
            ActivityLog::record(
                (int) ($user['id'] ?? 0),
                'school_notification_preferences_changed',
                'school',
                $schoolId,
                (string) ($school['nom_etablissement'] ?? 'École'),
                'Préférences de notification modifiées : SMS ' . ($smsEnabled ? 'activé' : 'désactivé')
                    . ', WhatsApp ' . ($whatsappEnabled ? 'activé' : 'désactivé') . '.'
            );
            $_SESSION['school_settings_success'] = 'Les préférences de notification de votre école ont été enregistrées.';
        } else {
            $_SESSION['school_settings_error'] = 'Impossible d’enregistrer les préférences. Réessayez.';
        }

        $_SESSION['school_settings_csrf'] = bin2hex(random_bytes(32));
        $this->redirect('/ecoles/settings');
    }

    public function updateStatus(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['ecole_id'] ?? 0);
            $status = $_POST['statut_systeme'] ?? 'En_Attente';

            if ($id > 0 && in_array($status, ['Actif', 'Suspendu', 'En_Attente'], true)) {
                $school = Ecole::findById($id);
                if ($school && Ecole::updateSystemStatus($id, $status) && ($school['statut_systeme'] ?? '') !== $status) {
                    ActivityLog::record(
                        (int) (Auth::user()['id'] ?? 0),
                        'school_access_changed',
                        'school',
                        $id,
                        (string) $school['nom_etablissement'],
                        'Accès de l’école modifié : ' . (string) ($school['statut_systeme'] ?? 'inconnu') . ' → ' . $status . '.'
                    );
                }
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
                $school = Ecole::findById($id);
                if ($school && Ecole::updateSystemStatus($id, 'Actif') && ($school['statut_systeme'] ?? '') !== 'Actif') {
                    ActivityLog::record(
                        (int) (Auth::user()['id'] ?? 0),
                        'school_access_changed',
                        'school',
                        $id,
                        (string) $school['nom_etablissement'],
                        'École validée et accès activé.'
                    );
                }
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
                    ActivityLog::record(
                        (int) (Auth::user()['id'] ?? 0),
                        'school_created',
                        'school',
                        (int) $ecoleId,
                        $nom,
                        'École créée avec le statut En attente de validation.'
                    );
                    $existingAdminId = (int) ($_POST['existing_admin_id'] ?? 0);
                    $newAdminName = trim($_POST['admin_nom'] ?? '');
                    $newAdminIdent = trim($_POST['admin_identifiant'] ?? '');
                    $newAdminPass = trim($_POST['admin_mot_de_passe'] ?? '');
                    $adminUserId = null;

                    if ($existingAdminId > 0) {
                        if (\App\Models\User::assignToSchool($existingAdminId, (int) $ecoleId)) {
                            $adminUserId = $existingAdminId;
                            $existingAdmin = \App\Models\User::findById($existingAdminId);
                            ActivityLog::record(
                                (int) (Auth::user()['id'] ?? 0),
                                'school_admin_assigned',
                                'user',
                                $existingAdminId,
                                (string) ($existingAdmin['nom_complet'] ?? 'Administrateur d’école'),
                                'Compte administrateur associé à l’école ' . $nom . '.'
                            );
                        }
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
                            ActivityLog::record(
                                (int) (Auth::user()['id'] ?? 0),
                                'school_admin_created',
                                'user',
                                $adminUserId,
                                $newAdminName,
                                'Compte administrateur créé pour l’école ' . $nom . '.'
                            );
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
                $_SESSION['ecole_edit_old'] = ['id' => $id, 'data' => $data];
            } else {
                $ok = Ecole::update($id, $data);
                if ($ok) {
                    $oldStatus = (string) ($school['statut_systeme'] ?? '');
                    $newStatus = (string) ($data['statut_systeme'] ?? $oldStatus);
                    if ($oldStatus !== $newStatus) {
                        ActivityLog::record(
                            (int) (Auth::user()['id'] ?? 0),
                            'school_access_changed',
                            'school',
                            $id,
                            (string) $data['nom_etablissement'],
                            'Accès de l’école modifié : ' . $oldStatus . ' → ' . $newStatus . '.'
                        );
                    }
                    $_SESSION['ecole_edit_success'] = 'École mise à jour.';
                } else {
                    $_SESSION['ecole_edit_errors'] = ['Erreur lors de la mise à jour.'];
                    $_SESSION['ecole_edit_old'] = ['id' => $id, 'data' => $data];
                }
                $this->redirect('/ecoles/edit?id=' . $id);
                return;
            }
        }

        $school = Ecole::findById($id);
        if (!$school) {
            $this->redirect('/ecoles');
            return;
        }

        $oldInput = $_SESSION['ecole_edit_old'] ?? [];
        unset($_SESSION['ecole_edit_old']);
        if ((int) ($oldInput['id'] ?? 0) === $id && is_array($oldInput['data'] ?? null)) {
            $school = array_merge($school, $oldInput['data']);
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

        if (empty($_SESSION['subscription_request_csrf'])) {
            $_SESSION['subscription_request_csrf'] = bin2hex(random_bytes(32));
        }
        $total = Ecole::countBySystemStatus('En_Attente');
        $pending = Ecole::getPendingSchoolsPaged($perPage, $offset);
        $pendingSubscriptionRequests = array_values(array_filter(
            SubscriptionRequest::getAll(),
            static fn (array $request): bool => ($request['statut'] ?? '') === 'En_attente'
        ));
        $pendingSchoolYearRequests = SchoolYearChangeRequest::getPending();

        $this->view('ecoles/requests', [
            'title' => APP_NAME . ' - Demandes écoles',
            'user' => $user,
            'role' => $role,
            'roleLabel' => \App\Models\User::getRoleLabel($role),
            'modules' => $modules,
            'pending' => $pending,
            'pendingSubscriptionRequests' => $pendingSubscriptionRequests,
            'pendingSchoolYearRequests' => $pendingSchoolYearRequests,
            'csrfToken' => $_SESSION['subscription_request_csrf'],
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
        $generatedPassword = null;
        $matricule = '';
        $parentIdentifier = '';
        if (empty($_SESSION['generate_password_csrf'])) {
            $_SESSION['generate_password_csrf'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string) ($_POST['csrf_token'] ?? '');
            if ($token === '' || !hash_equals((string) $_SESSION['generate_password_csrf'], $token)) {
                $errors[] = 'Le formulaire a expiré. Rechargez la page puis réessayez.';
            } else {
                $accountType = (string) ($_POST['account_type'] ?? 'student');
                if ($accountType === 'parent') {
                    $parentIdentifier = trim((string) ($_POST['parent_identifier'] ?? ''));
                    if ($parentIdentifier === '') {
                        $errors[] = 'L’identifiant du compte parent est requis.';
                    } else {
                        $db = \App\Core\Database::getConnection();
                        $stmt = $db->prepare(
                            "SELECT u.id, u.nom_complet, u.identifiant, u.ecole_id, p.ecole_id AS parent_ecole_id
                             FROM utilisateurs u
                             INNER JOIN parents p ON p.id = u.reference_id
                             WHERE u.identifiant = :identifiant
                               AND u.role = 'parent_ecole'
                               AND u.statut = 'Actif'
                             LIMIT 1"
                        );
                        $stmt->execute([':identifiant' => $parentIdentifier]);
                        $parentUser = $stmt->fetch(\PDO::FETCH_ASSOC);

                        if (!$parentUser) {
                            $errors[] = 'Aucun compte parent actif trouvé pour cet identifiant.';
                        } elseif (
                            (int) ($parentUser['ecole_id'] ?? 0) <= 0
                            || (int) ($parentUser['ecole_id'] ?? 0) !== (int) ($parentUser['parent_ecole_id'] ?? 0)
                            || (
                                $role === 'ecole_admin'
                                && (int) ($parentUser['ecole_id'] ?? 0) !== (int) ($user['ecole_id'] ?? 0)
                            )
                        ) {
                            $errors[] = 'Vous ne pouvez générer un mot de passe que pour un parent lié à votre établissement.';
                        } else {
                            $generatedPassword = bin2hex(random_bytes(8));
                            if (User::updateProfile((int) $parentUser['id'], ['mot_de_passe' => $generatedPassword])) {
                                $success = 'Mot de passe temporaire généré pour ' . $parentUser['nom_complet']
                                    . ' (' . $parentUser['identifiant'] . ').';
                                ActivityLog::record(
                                    (int) ($user['id'] ?? 0),
                                    'parent_password_generated',
                                    'user',
                                    (int) $parentUser['id'],
                                    'Mot de passe parent généré',
                                    'Mot de passe temporaire généré par ' . (string) ($user['nom_complet'] ?? 'un administrateur') . '.'
                                );
                            } else {
                                $generatedPassword = null;
                                $errors[] = 'Impossible de mettre à jour le mot de passe. Réessayez.';
                            }
                        }
                    }
                } elseif ($accountType === 'student') {
                    $matricule = trim((string) ($_POST['matricule'] ?? ''));

                    if ($matricule === '') {
                        $errors[] = 'Le matricule de l\'élève est requis.';
                    } else {
                        $eleveUser = User::findByEleveMatricule($matricule);

                        if (!$eleveUser) {
                            $errors[] = 'Aucun élève trouvé pour ce matricule.';
                        } elseif ($role === 'ecole_admin' && (int) ($eleveUser['ecole_id'] ?? 0) !== (int) ($user['ecole_id'] ?? 0)) {
                            $errors[] = 'Vous ne pouvez générer un mot de passe que pour les élèves de votre établissement.';
                        } else {
                            $generatedPassword = bin2hex(random_bytes(8));
                            if (User::updateProfile((int) $eleveUser['id'], ['mot_de_passe' => $generatedPassword])) {
                                $success = 'Mot de passe temporaire généré pour le matricule ' . $matricule . '.';
                            } else {
                                $generatedPassword = null;
                                $errors[] = 'Impossible de mettre à jour le mot de passe. Réessayez.';
                            }
                        }
                    }
                } else {
                    $errors[] = 'Le type de compte demandé est invalide.';
                }
            }

            $_SESSION['generate_password_csrf'] = bin2hex(random_bytes(32));
        }

        $this->view('ecoles/generate_password', [
            'title' => APP_NAME . ' - Générer un mot de passe',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'errors' => $errors,
            'success' => $success,
            'generatedPassword' => $generatedPassword,
            'matricule' => $matricule,
            'parentIdentifier' => $parentIdentifier,
            'csrfToken' => $_SESSION['generate_password_csrf'],
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
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $tmpPath = $file['tmp_name'] ?? '';
        if (!is_string($tmpPath) || $tmpPath === '' || !is_file($tmpPath)) {
            return null;
        }

        $maxBytes = 250 * 1024;
        if (filesize($tmpPath) > $maxBytes) {
            return null;
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpPath);
        $extensionsByMimeType = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
        ];
        if (!is_string($mimeType) || !isset($extensionsByMimeType[$mimeType])) {
            return null;
        }

        $filename = 'school_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $extensionsByMimeType[$mimeType];
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
            $maxStudents = filter_var(trim((string) ($_POST['max_students'] ?? '0')), FILTER_VALIDATE_INT);
            $maxParentAccounts = filter_var(trim((string) ($_POST['max_parent_accounts'] ?? '0')), FILTER_VALIDATE_INT);
            $maxStaffAccounts = filter_var(trim((string) ($_POST['max_staff_accounts'] ?? '0')), FILTER_VALIDATE_INT);
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
            foreach ([
                'maxStudents' => [$maxStudents, 'élèves'],
                'maxParentAccounts' => [$maxParentAccounts, 'comptes parents'],
                'maxStaffAccounts' => [$maxStaffAccounts, 'comptes agents'],
            ] as [$value, $label]) {
                if ($value === false || $value < 0 || $value > 1000000) {
                    $errors[] = 'Le maximum de ' . $label . ' doit être compris entre 0 et 1 000 000 (0 = illimité).';
                }
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
                    'max_students' => (int) $maxStudents,
                    'max_parent_accounts' => (int) $maxParentAccounts,
                    'max_staff_accounts' => (int) $maxStaffAccounts,
                ], $planId > 0 ? $planId : null);
                if ($saved) {
                    $_SESSION['ecoles_plans_success'] = $planId > 0
                        ? 'Tarif mis à jour. Les nouveaux plafonds s’appliqueront aux prochains abonnements; les abonnements existants gardent leurs plafonds enregistrés.'
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
            $trialMaxStudents = filter_var(trim((string) ($_POST['max_students'] ?? '0')), FILTER_VALIDATE_INT);
            $trialMaxParentAccounts = filter_var(trim((string) ($_POST['max_parent_accounts'] ?? '0')), FILTER_VALIDATE_INT);
            $trialMaxStaffAccounts = filter_var(trim((string) ($_POST['max_staff_accounts'] ?? '0')), FILTER_VALIDATE_INT);
            $additionalDescription = trim((string) ($_POST['description'] ?? ''));

            if (mb_strlen($additionalDescription, 'UTF-8') > 2000) {
                $errors[] = 'Les détails complémentaires ne peuvent pas dépasser 2 000 caractères.';
            }
            $maxStudents = 0;
            $maxParentAccounts = 0;
            $maxStaffAccounts = 0;

            if ($ecoleId <= 0 || !Ecole::findById($ecoleId)) {
                $errors[] = 'Sélectionnez une école valide.';
            }
            $selectedPlan = null;
            if ($isTrial) {
                foreach ([
                    'trialMaxStudents' => [$trialMaxStudents, 'élèves'],
                    'trialMaxParentAccounts' => [$trialMaxParentAccounts, 'comptes parents'],
                    'trialMaxStaffAccounts' => [$trialMaxStaffAccounts, 'comptes agents'],
                ] as [$value, $label]) {
                    if ($value === false || $value < 0 || $value > 1000000) {
                        $errors[] = 'Le maximum de ' . $label . ' doit être compris entre 0 et 1 000 000 (0 = illimité).';
                    }
                }
                if ($durationDays === false || $durationDays <= 0 || $durationDays > 3650) {
                    $errors[] = 'La durée de l’essai doit être comprise entre 1 et 3 650 jours.';
                }
                $maxStudents = $trialMaxStudents === false ? 0 : (int) $trialMaxStudents;
                $maxParentAccounts = $trialMaxParentAccounts === false ? 0 : (int) $trialMaxParentAccounts;
                $maxStaffAccounts = $trialMaxStaffAccounts === false ? 0 : (int) $trialMaxStaffAccounts;
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
                    $maxStudents = (int) ($selectedPlan['max_students'] ?? 0);
                    $maxParentAccounts = (int) ($selectedPlan['max_parent_accounts'] ?? 0);
                    $maxStaffAccounts = (int) ($selectedPlan['max_staff_accounts'] ?? 0);
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
                $planName = $isTrial ? 'Essai gratuit' : (string) ($selectedPlan['nom_plan'] ?? 'Abonnement');
                $planDescription = $isTrial
                    ? 'Essai gratuit de ' . $durationDays . ' jour(s).'
                    : (string) ($selectedPlan['description'] ?? '');
                $subscriptionDescription = Ecole::buildSubscriptionDescription(
                    $planName,
                    $planDescription,
                    $amount,
                    $dateDebut,
                    $dateFin,
                    (int) $maxStudents,
                    (int) $maxParentAccounts,
                    (int) $maxStaffAccounts,
                    $additionalDescription
                );
                if (Ecole::addSubscription(
                    $ecoleId,
                    $planId,
                    $dateDebut,
                    $dateFin,
                    $amount,
                    (int) $maxStudents,
                    (int) $maxParentAccounts,
                    (int) $maxStaffAccounts,
                    $subscriptionDescription
                )) {
                    $school = Ecole::findById($ecoleId);
                    ActivityLog::record(
                        (int) (Auth::user()['id'] ?? 0),
                        'subscription_added',
                        'school_subscription',
                        $ecoleId,
                        (string) ($school['nom_etablissement'] ?? 'École'),
                        'Abonnement ' . ($isTrial ? 'd’essai' : 'payant') . ' ajouté du ' . $dateDebut . ' au ' . $dateFin . '.'
                    );
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
        Auth::requireRoles(['super_admin', 'ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        if ($role === 'ecole_admin') {
            $schoolId = (int) ($user['ecole_id'] ?? 0);
            if ($schoolId <= 0) {
                $this->redirect('/error/accessDenied');
                return;
            }
            if (empty($_SESSION['subscription_request_csrf'])) {
                $_SESSION['subscription_request_csrf'] = bin2hex(random_bytes(32));
            }
            $this->view('ecoles/subscriptions', [
                'title' => APP_NAME . ' - Mon abonnement',
                'user' => $user,
                'role' => $role,
                'roleLabel' => User::getRoleLabel($role),
                'modules' => $this->getModulesForRole($role),
                'isSuperAdmin' => false,
                'school' => Ecole::findById($schoolId),
                'subscriptions' => Ecole::getSubscriptions($schoolId),
                'currentSubscription' => Ecole::getCurrentSubscription($schoolId),
                'plans' => Ecole::getPlans(),
                'subscriptionRequests' => SubscriptionRequest::getForSchool($schoolId),
                'success' => $_SESSION['subscription_request_success'] ?? null,
                'errors' => $_SESSION['subscription_request_errors'] ?? [],
                'csrfToken' => $_SESSION['subscription_request_csrf'],
            ]);
            unset($_SESSION['subscription_request_success'], $_SESSION['subscription_request_errors']);
            return;
        }

        $selectedSchoolId = max(0, (int) ($_GET['ecole_id'] ?? 0));
        if ($selectedSchoolId > 0 && !Ecole::findById($selectedSchoolId)) {
            $selectedSchoolId = 0;
        }
        if (empty($_SESSION['subscription_request_csrf'])) {
            $_SESSION['subscription_request_csrf'] = bin2hex(random_bytes(32));
        }
        $this->view('ecoles/subscriptions', [
            'title' => APP_NAME . ' - Gestion des abonnements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'isSuperAdmin' => true,
            'subscriptions' => Ecole::getSubscriptions($selectedSchoolId > 0 ? $selectedSchoolId : null),
            'schools' => Ecole::getAll(),
            'plans' => Ecole::getPlans(),
            'subscriptionRequests' => SubscriptionRequest::getAll(),
            'selectedSchoolId' => $selectedSchoolId,
            'csrfToken' => $_SESSION['subscription_request_csrf'],
        ]);
    }

    public function submitSubscriptionRequest(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);
        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $schoolId <= 0
            || $token === ''
            || !hash_equals((string) ($_SESSION['subscription_request_csrf'] ?? ''), $token)
        ) {
            $_SESSION['subscription_request_errors'] = ['Le formulaire a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/ecoles/subscriptions');
            return;
        }

        $planId = filter_var($_POST['plan_id'] ?? null, FILTER_VALIDATE_INT);
        $message = trim((string) ($_POST['message'] ?? ''));
        if ($planId === false || $planId <= 0 || mb_strlen($message, 'UTF-8') > 1000) {
            $_SESSION['subscription_request_errors'] = ['Choisissez un tarif valide et saisissez un message de 1 000 caractères maximum.'];
            $this->redirect('/ecoles/subscriptions');
            return;
        }

        $availablePlanIds = array_map(
            static fn (array $plan): int => (int) $plan['id'],
            Ecole::getPlans()
        );
        if (!in_array((int) $planId, $availablePlanIds, true)) {
            $_SESSION['subscription_request_errors'] = ['Le tarif choisi n’est pas disponible. Rechargez la page puis réessayez.'];
            $this->redirect('/ecoles/subscriptions');
            return;
        }

        if (SubscriptionRequest::create(
            $schoolId,
            (int) ($user['id'] ?? 0),
            (int) $planId,
            'renouvellement',
            $message
        )) {
            $school = Ecole::findById($schoolId);
            ActivityLog::record(
                (int) ($user['id'] ?? 0),
                'subscription_requested',
                'school_subscription',
                $schoolId,
                (string) ($school['nom_etablissement'] ?? 'École'),
                'Demande de renouvellement ou de changement de tarif soumise.'
            );
            $_SESSION['subscription_request_success'] = 'Votre demande concernant l’abonnement a été transmise à KC_SMA.';
        } else {
            $_SESSION['subscription_request_errors'] = [
                'La demande n’a pas pu être soumise. Une demande peut déjà être en attente pour votre école; vérifiez l’historique puis réessayez.'
            ];
        }

        $_SESSION['subscription_request_csrf'] = bin2hex(random_bytes(32));
        $this->redirect('/ecoles/subscriptions');
    }

    public function reviewSubscriptionRequest(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || !hash_equals((string) ($_SESSION['subscription_request_csrf'] ?? ''), $token)
        ) {
            $_SESSION['ecoles_subscription_errors'] = ['Le formulaire a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/ecoles/requests');
            return;
        }

        $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
        $status = (string) ($_POST['statut'] ?? '');
        $note = trim((string) ($_POST['reponse'] ?? ''));
        if (
            $requestId === false
            || $requestId <= 0
            || !in_array($status, ['Approuvee', 'Refusee'], true)
            || mb_strlen($note, 'UTF-8') > 1000
        ) {
            $_SESSION['ecoles_subscription_errors'] = ['Les données de traitement de la demande sont invalides.'];
            $this->redirect('/ecoles/requests');
            return;
        }

        $request = null;
        foreach (SubscriptionRequest::getAll() as $candidate) {
            if ((int) ($candidate['id'] ?? 0) === (int) $requestId) {
                $request = $candidate;
                break;
            }
        }
        if (!$request || ($request['statut'] ?? '') !== 'En_attente') {
            $_SESSION['ecoles_subscription_errors'] = ['Cette demande est introuvable ou a déjà été traitée.'];
            $this->redirect('/ecoles/requests');
            return;
        }

        if (SubscriptionRequest::review(
            (int) $requestId,
            (int) (Auth::user()['id'] ?? 0),
            $status,
            $note
        )) {
            ActivityLog::record(
                (int) (Auth::user()['id'] ?? 0),
                $status === 'Approuvee' ? 'subscription_request_approved' : 'subscription_request_rejected',
                'school_subscription',
                (int) $request['ecole_id'],
                (string) ($request['nom_etablissement'] ?? 'École'),
                'Demande de ' . ($request['type_demande'] === 'renouvellement' ? 'renouvellement' : 'nouvel abonnement')
                    . ($status === 'Approuvee' ? ' approuvée.' : ' refusée.')
            );
            $_SESSION['ecoles_subscription_success'] = $status === 'Approuvee'
                ? 'Demande approuvée : le tarif et ses plafonds configurés dans les plans ont été appliqués.'
                : 'Demande refusée.';
        } else {
            $_SESSION['ecoles_subscription_errors'] = ['Impossible de traiter cette demande. Vérifiez le tarif et réessayez.'];
        }

        $this->redirect('/ecoles/requests');
    }

    public function reviewSchoolYearChange(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || $token === ''
            || !hash_equals((string) ($_SESSION['subscription_request_csrf'] ?? ''), $token)
        ) {
            $_SESSION['ecoles_subscription_errors'] = ['Le formulaire a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/ecoles/requests');
            return;
        }

        $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
        $status = (string) ($_POST['statut'] ?? '');
        $note = is_string($_POST['reponse'] ?? null) ? trim($_POST['reponse']) : '';
        if (
            $requestId === false
            || $requestId <= 0
            || !in_array($status, ['Approuvee', 'Refusee'], true)
            || mb_strlen($note, 'UTF-8') > 1000
        ) {
            $_SESSION['ecoles_subscription_errors'] = ['Les données de traitement de la demande d’année scolaire sont invalides.'];
            $this->redirect('/ecoles/requests');
            return;
        }

        try {
            $reviewed = SchoolYearChangeRequest::review(
                (int) $requestId,
                (int) (Auth::user()['id'] ?? 0),
                $status,
                $note
            );
            if (!$reviewed) {
                $_SESSION['ecoles_subscription_errors'] = ['Cette demande est introuvable ou a déjà été traitée.'];
            } else {
                ActivityLog::record(
                    (int) (Auth::user()['id'] ?? 0),
                    $status === 'Approuvee' ? 'school_year_change_approved' : 'school_year_change_rejected',
                    'school_year_request',
                    (int) $requestId,
                    'Demande de changement d’année scolaire',
                    $status === 'Approuvee'
                        ? 'La demande de changement d’année scolaire a été approuvée.'
                        : 'La demande de changement d’année scolaire a été refusée.'
                );
                $_SESSION['ecoles_subscription_success'] = $status === 'Approuvee'
                    ? 'La demande a été approuvée et la nouvelle année scolaire est maintenant active.'
                    : 'La demande de changement d’année scolaire a été refusée.';
            }
        } catch (\Throwable $e) {
            error_log('EcolesController::reviewSchoolYearChange failed: ' . $e->getMessage());
            $_SESSION['ecoles_subscription_errors'] = ['Impossible de traiter cette demande. Réessayez.'];
        }

        $this->redirect('/ecoles/requests');
    }

    public function notificationAccess(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);
        $user = Auth::refresh() ?: Auth::user();
        if (empty($_SESSION['notification_access_form_token'])) {
            $_SESSION['notification_access_form_token'] = bin2hex(random_bytes(32));
        }

        $this->view('ecoles/notification_access', [
            'title' => APP_NAME . ' - Accès aux fonctionnalités de communication',
            'user' => $user,
            'role' => $user['role'] ?? 'super_admin',
            'roleLabel' => User::getRoleLabel((string) ($user['role'] ?? 'super_admin')),
            'modules' => $this->getModulesForRole((string) ($user['role'] ?? 'super_admin')),
            'schoolNotificationAccess' => NotificationAccess::getAllSchools(),
            'formToken' => $_SESSION['notification_access_form_token'],
            'success' => $_SESSION['notification_access_success'] ?? null,
            'error' => $_SESSION['notification_access_error'] ?? null,
        ]);
        unset($_SESSION['notification_access_success'], $_SESSION['notification_access_error']);
    }

    public function updateNotificationAccess(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);
        $user = Auth::refresh() ?: Auth::user();
        $submittedToken = (string) ($_POST['notification_access_form_token'] ?? '');
        $expectedToken = (string) ($_SESSION['notification_access_form_token'] ?? '');
        if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
            $_SESSION['notification_access_error'] = 'Le formulaire a expiré. Rechargez la page avant de réessayer.';
            $this->redirect('/ecoles/notificationAccess');
        }

        $schoolId = filter_var($_POST['ecole_id'] ?? null, FILTER_VALIDATE_INT);
        $smsInput = $_POST['sms_enabled'] ?? null;
        $whatsappInput = $_POST['whatsapp_enabled'] ?? null;
        $directMessagesInput = $_POST['direct_messages_enabled'] ?? null;
        if (
            $schoolId === false || $schoolId === null || $schoolId <= 0
            || !in_array($smsInput, ['0', '1', 0, 1], true)
            || !in_array($whatsappInput, ['0', '1', 0, 1], true)
            || !in_array($directMessagesInput, ['0', '1', 0, 1], true)
            || !Ecole::findById((int) $schoolId)
        ) {
            $_SESSION['notification_access_error'] = 'École ou paramètres de notification invalides.';
            $this->redirect('/ecoles/notificationAccess');
        }

        if (NotificationAccess::update(
            (int) $schoolId,
            (string) $smsInput === '1',
            (string) $whatsappInput === '1',
            (string) $directMessagesInput === '1',
            (int) ($user['id'] ?? 0)
        )) {
            $school = Ecole::findById((int) $schoolId);
            ActivityLog::record(
                (int) ($user['id'] ?? 0),
                'school_notification_access_changed',
                'school',
                (int) $schoolId,
                (string) ($school['nom_etablissement'] ?? 'École'),
                'Accès aux notifications mis à jour : SMS ' . ((string) $smsInput === '1' ? 'activé' : 'désactivé')
                    . ', WhatsApp ' . ((string) $whatsappInput === '1' ? 'activé' : 'désactivé')
                    . ', messagerie interne ' . ((string) $directMessagesInput === '1' ? 'activée' : 'désactivée') . '.'
            );
            $_SESSION['notification_access_success'] = 'Les accès de communication de l’école ont été mis à jour.';
        } else {
            $_SESSION['notification_access_error'] = 'Impossible d’enregistrer les accès de notification. Réessayez.';
        }
        $_SESSION['notification_access_form_token'] = bin2hex(random_bytes(32));
        $this->redirect('/ecoles/notificationAccess');
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
            $subscription = null;
            foreach (Ecole::getSubscriptions() as $candidate) {
                if ((int) ($candidate['id'] ?? 0) === $subscriptionId) {
                    $subscription = $candidate;
                    break;
                }
            }
            ActivityLog::record(
                (int) (Auth::user()['id'] ?? 0),
                'subscription_updated',
                'school_subscription',
                (int) ($subscription['ecole_id'] ?? 0),
                (string) ($subscription['nom_etablissement'] ?? 'École'),
                'Abonnement modifié : statut ' . $status . ', du ' . $dateDebut . ' au ' . $dateFin . '.'
            );
            $_SESSION['ecoles_subscription_success'] = 'Abonnement mis à jour. Le montant déjà enregistré comme payé a été conservé.';
        } else {
            if (empty($errors)) {
                $errors[] = 'Impossible de mettre à jour cet abonnement. Réessayez.';
            }
            $_SESSION['ecoles_subscription_errors'] = $errors;
        }

        $this->redirect('/ecoles/requests');
    }
}
