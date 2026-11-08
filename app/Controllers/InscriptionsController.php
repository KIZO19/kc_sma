<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Eleve;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Inscription;
use App\Models\Option;
use App\Models\OriginesRDC;
use App\Models\PasswordReset;
use App\Models\ParentModel;
use App\Models\Section;
use App\Models\User;
use App\Services\PaymentParentNotifier;

class InscriptionsController extends Controller
{
    private const SUBMISSION_ROLES = [
        'super_admin',
        'ecole_admin',
        'agent_ecole',
        'comptable_école',
        'préfet_école',
        'DE_école',
        'DP_école',
        'DA_école',
        'sec_école',
    ];

    private const IDENTITY_EDIT_ROLES = ['super_admin', 'comptable_école', 'préfet_école', 'sec_école'];

    private function getMandatorySchoolItems(int $schoolId, int $yearId): array
    {
        if ($schoolId <= 0 || $yearId <= 0) {
            return [];
        }

        return array_values(array_filter(
            \App\Models\FraisScolaire::getAllBySchool($schoolId),
            static fn (array $fee): bool => ($fee['scope'] ?? '') === 'school'
                && (int) ($fee['annee_scolaire_id'] ?? 0) === $yearId
                && str_starts_with((string) ($fee['encodage'] ?? ''), 'ART-SCOL-')
        ));
    }

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'agent_ecole', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');
        if (empty($_SESSION['inscriptions_access_csrf'])) {
            $_SESSION['inscriptions_access_csrf'] = bin2hex(random_bytes(32));
        }
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $pendingStudents = $ecoleId > 0
            ? Eleve::getPendingBySchool($ecoleId, $sectionScopeId)
            : ($role === 'super_admin' ? Eleve::getPending() : []);
        $enrolledStudents = $ecoleId > 0
            ? Eleve::getEnrolledBySchool($ecoleId, $sectionScopeId)
            : [];
        $sections = $sectionScopeId !== null
            ? array_values(array_filter(Section::getAll(), static fn (array $section): bool => (int) $section['id'] === $sectionScopeId))
            : Section::getAll();
        $options = $ecoleId > 0
            ? ($sectionScopeId !== null
                ? Option::getAllForSchoolAndSection($ecoleId, $sectionScopeId)
                : Option::getAllForSchool($ecoleId))
            : ($role === 'super_admin' ? Option::getAll() : []);

        $this->view('inscriptions/index', [
            'title' => APP_NAME . ' - Dossiers d’inscription',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pendingStudents' => $pendingStudents,
            'enrolledStudents' => $enrolledStudents,
            'sections' => $sections,
            'options' => $options,
            'canSubmit' => in_array($role, self::SUBMISSION_ROLES, true),
            'canManageAccountAccess' => in_array($role, ['super_admin', 'ecole_admin', 'sec_école'], true),
            'accountAccessCsrf' => $_SESSION['inscriptions_access_csrf'],
            'canEdit' => in_array($role, self::IDENTITY_EDIT_ROLES, true),
            'canApprove' => in_array($role, ['super_admin', 'sec_école', 'préfet_école', 'DP_école', 'comptable_école'], true),
        ]);
    }

    public function managePasswords(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);
        if ($ecoleId <= 0) {
            $this->redirect('/error/accessDenied');
        }

        if (empty($_SESSION['inscriptions_access_csrf'])) {
            $_SESSION['inscriptions_access_csrf'] = bin2hex(random_bytes(32));
        }

        $this->view('inscriptions/manage_passwords', [
            'title' => APP_NAME . ' - Gérer les mots de passe',
            'user' => $user,
            'role' => $user['role'] ?? '',
            'roleLabel' => User::getRoleLabel((string) ($user['role'] ?? '')),
            'modules' => $this->getModulesForRole((string) ($user['role'] ?? '')),
            'students' => Eleve::getAllBySchool($ecoleId, null, $sectionScopeId),
            'accountAccessCsrf' => $_SESSION['inscriptions_access_csrf'],
        ]);
    }

    public function sendAccountAccess(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'sec_école']);
        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/inscriptions');
        }

        $returnPath = ($_POST['return_to'] ?? '') === 'passwords'
            ? '/inscriptions/managePasswords'
            : '/inscriptions';
        $postedCsrf = (string) ($_POST['csrf_token'] ?? '');
        if (
            $postedCsrf === ''
            || !hash_equals((string) ($_SESSION['inscriptions_access_csrf'] ?? ''), $postedCsrf)
        ) {
            $_SESSION['inscriptions_errors'] = ['Le formulaire a expiré. Rechargez la page et réessayez.'];
            $this->redirect($returnPath);
        }

        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $studentId = (int) ($_POST['eleve_id'] ?? 0);
        $accountType = trim((string) ($_POST['account_type'] ?? ''));
        $sectionScopeId = $this->getSectionScopeForUser($user);

        try {
            if ($ecoleId <= 0 || $studentId <= 0 || !in_array($accountType, ['student', 'parent'], true)) {
                throw new \InvalidArgumentException('Élève ou type de compte invalide.');
            }

            $student = Eleve::findByIdAndSchool($studentId, $ecoleId, $sectionScopeId);
            if (!$student) {
                throw new \RuntimeException('Cet élève n’appartient pas à votre école ou à votre section.');
            }

            if ($accountType === 'student') {
                $matricule = trim((string) ($student['matricule'] ?? ''));
                if ($matricule === '') {
                    throw new \RuntimeException('L’élève n’a pas encore de matricule. Attribuez-lui un matricule avant de créer son accès.');
                }
                $account = Eleve::createUserAccount($studentId);
                $accountLabel = 'élève';
            } else {
                $parentId = (int) ($student['parent_id'] ?? 0);
                $parent = $parentId > 0 ? ParentModel::findById($parentId) : null;
                if (!$parent || (int) ($parent['ecole_id'] ?? 0) !== $ecoleId) {
                    throw new \RuntimeException('Aucun parent/tuteur de cette école n’est associé à l’élève.');
                }
                if (trim((string) ($parent['telephone'] ?? '')) === '') {
                    throw new \RuntimeException('Le parent/tuteur doit avoir un numéro de téléphone renseigné.');
                }
                $account = ParentModel::createUserAccount($parentId);
                $accountLabel = 'parent/tuteur';
            }

            if (($account['statut'] ?? '') !== 'Actif') {
                throw new \RuntimeException('Le compte lié est inactif. Activez-le avant d’envoyer un lien.');
            }

            $routeBase = Auth::getSchoolRouteBaseUrl();
            if ($routeBase === null) {
                throw new \RuntimeException('L’école associée à votre compte est introuvable.');
            }

            $publicUrl = parse_url(APP_PUBLIC_URL);
            if (
                is_array($publicUrl)
                && in_array(strtolower((string) ($publicUrl['scheme'] ?? '')), ['http', 'https'], true)
                && !empty($publicUrl['host'])
                && !isset($publicUrl['user'])
                && !isset($publicUrl['pass'])
            ) {
                $origin = strtolower((string) $publicUrl['scheme']) . '://' . $publicUrl['host']
                    . (isset($publicUrl['port']) ? ':' . $publicUrl['port'] : '');
            } else {
                $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
                if (
                    $host === ''
                    || preg_match('/^(?:[a-zA-Z0-9.-]+|\[[a-fA-F0-9:]+\])(?::\d{1,5})?$/D', $host) !== 1
                ) {
                    throw new \RuntimeException('Impossible de construire le lien sécurisé pour cet hôte.');
                }

                $scheme = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off'
                    ? 'https'
                    : 'http';
                $origin = $scheme . '://' . $host;
            }

            $token = PasswordReset::createToken((int) $account['id'], 1440);
            $link = $origin . $routeBase . '/reset-password?token=' . rawurlencode($token);
            $result = (new PaymentParentNotifier())->sendAccountSetupLink(
                $studentId,
                $ecoleId,
                $accountType,
                $link
            );
            $status = (string) ($result['status'] ?? 'failed');
            if (!in_array($status, ['success', 'partial'], true)) {
                PasswordReset::revokeToken($token);
                $token = null;
                $_SESSION['inscriptions_errors'] = [
                    (string) ($result['message'] ?? 'Le lien n’a pas pu être envoyé. Vérifiez le téléphone du parent et la configuration SMS/WhatsApp.'),
                ];
            } else {
                $_SESSION['account_access_notification'] = [
                    'status' => $status,
                    'message' => 'Le lien de configuration du compte ' . $accountLabel . ' a été accepté par le fournisseur. La livraison au téléphone n’est pas confirmée.',
                ];
            }
        } catch (\PDOException $exception) {
            error_log('InscriptionsController::sendAccountAccess database error: ' . $exception->getMessage());
            $_SESSION['inscriptions_errors'] = ['Impossible de préparer ou d’envoyer le lien du compte. Vérifiez la base de données et la configuration des notifications.'];
        } catch (\DomainException|\InvalidArgumentException|\RuntimeException $exception) {
            $_SESSION['inscriptions_errors'] = [$exception->getMessage()];
        }

        $this->redirect($returnPath);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $parents = $ecoleId > 0 ? ParentModel::getAllBySchool($ecoleId, $sectionScopeId) : [];
        $sections = $sectionScopeId !== null
            ? array_values(array_filter(Section::getAll(), static fn (array $section): bool => (int) $section['id'] === $sectionScopeId))
            : Section::getAll();
        $options = $ecoleId > 0
            ? ($sectionScopeId !== null
                ? Option::getAllForSchoolAndSection($ecoleId, $sectionScopeId)
                : Option::getAllForSchool($ecoleId))
            : ($role === 'super_admin' ? Option::getAll() : []);
        $selectedSection = null;
        $selectedOption = null;
        $sectionId = $sectionScopeId ?? (int) ($_GET['section_id'] ?? 0);
        $optionId = (int) ($_GET['option_id'] ?? 0);
        if ($sectionId > 0) {
            $selectedSection = Section::findById($sectionId);
        }
        if ($optionId > 0) {
            $selectedOption = $ecoleId > 0
                ? Option::findByIdForSchool($optionId, $ecoleId)
                : Option::findById($optionId);
            if ($sectionScopeId !== null && (int) ($selectedOption['section_id'] ?? 0) !== $sectionScopeId) {
                $selectedOption = null;
            }
        }

        $classes = $ecoleId > 0 ? Classe::getAllBySchool($ecoleId, $sectionScopeId) : [];
        $activeYear = $ecoleId > 0 ? AnneeScolaire::getActiveBySchool($ecoleId) : null;
        $schoolItems = $this->getMandatorySchoolItems($ecoleId, (int) ($activeYear['id'] ?? 0));
        $oldInput = $_SESSION['inscriptions_old'] ?? [];
        unset($_SESSION['inscriptions_old']);

        $this->view('inscriptions/create', [
            'title' => APP_NAME . ' - Nouvelle inscription',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'parents' => $parents,
            'classes' => $classes,
            'selectedSection' => $selectedSection,
            'selectedOption' => $selectedOption,
            'sections' => $sections,
            'options' => $options,
            'schoolItems' => $schoolItems,
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $postnom = trim($_POST['postnom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $dateNaissance = trim($_POST['date_naissance'] ?? '');
            $matricule = trim($_POST['matricule'] ?? '');
            $parentChoice = trim($_POST['parent_choice'] ?? 'existing');
            $parentId = (int) ($_POST['parent_id'] ?? 0);
            $classeId = (int) ($_POST['classe_id'] ?? 0);
            $newParentName = trim($_POST['new_parent_nom_responsable'] ?? '');
            $newParentTelephone = trim($_POST['new_parent_telephone'] ?? '');
            $newParentEmail = trim($_POST['new_parent_email'] ?? '');
            $sectionId = (int) ($_POST['section_id'] ?? 0);
            $optionId = (int) ($_POST['option_id'] ?? 0);

            $errors = [];
            $errors = array_merge($errors, $this->validateOriginSelection($_POST));
            $activeYear = null;
            if ($nom === '') {
                $errors[] = 'Le nom de l’élève est requis.';
            }
            if ($postnom === '') {
                $errors[] = 'Le postnom de l’élève est requis.';
            }
            if (!in_array($genre, ['M', 'F'], true)) {
                $errors[] = 'Le genre de l’élève est requis.';
            }
            if ($classeId <= 0) {
                $errors[] = 'La classe associée est requise pour l’inscription.';
            }
            if ($sectionId <= 0) {
                $errors[] = 'La section de l’élève est requise pour l’inscription.';
            }
            if ($sectionScopeId !== null && $sectionId !== $sectionScopeId) {
                $errors[] = 'Vous ne pouvez inscrire des élèves que dans la section secondaire.';
            }
            $selectedSection = $sectionId > 0 ? Section::findById($sectionId) : null;
            $isSecondarySection = mb_strtolower(
                trim((string) ($selectedSection['nom_section'] ?? '')),
                'UTF-8'
            ) === 'secondaire';
            if ($sectionId > 0 && !$selectedSection) {
                $errors[] = 'La section sélectionnée est invalide.';
            }
            if ($isSecondarySection && $optionId <= 0) {
                $errors[] = 'L’option de l’élève est requise pour l’inscription.';
            } elseif (!$isSecondarySection && $optionId > 0) {
                $errors[] = 'Les options sont réservées aux inscriptions en section secondaire.';
            } elseif ($isSecondarySection) {
                $option = $ecoleId > 0
                    ? Option::findByIdForSchool($optionId, $ecoleId)
                    : Option::findById($optionId);
                if (!$option || (int) ($option['section_id'] ?? 0) !== $sectionId) {
                    $errors[] = 'L’option sélectionnée ne correspond pas à la section secondaire.';
                }
            }
            $birthDate = \DateTime::createFromFormat('Y-m-d', $dateNaissance);
            if (!$birthDate || $birthDate->format('Y-m-d') !== $dateNaissance) {
                $errors[] = 'La date de naissance est invalide.';
            } elseif ($birthDate > new \DateTime('today')) {
                $errors[] = 'La date de naissance ne peut pas être dans le futur.';
            }
            if (!in_array($parentChoice, ['existing', 'new'], true)) {
                $errors[] = 'Le choix du parent est invalide.';
            }
            if ($newParentEmail !== '' && !filter_var($newParentEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L’adresse email du parent est invalide.';
            }
            if ($parentChoice === 'new' && $newParentTelephone !== '' && !preg_match('/^[0-9+() .-]{7,25}$/', $newParentTelephone)) {
                $errors[] = 'Le téléphone du parent contient des caractères invalides.';
            }

            if (empty($matricule)) {
                $matricule = $this->generateUniqueMatricule($nom . ' ' . $postnom);
            } else {
                try {
                    $matricule = $this->ensureUniqueMatricule($matricule);
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if ($parentChoice === 'new') {
                if ($newParentName === '') {
                    $errors[] = 'Le nom du parent/tuteur est requis lorsque vous créez un nouveau parent.';
                }
                if ($newParentTelephone === '') {
                    $errors[] = 'Le téléphone du parent/tuteur est requis lorsque vous créez un nouveau parent.';
                } elseif ($ecoleId > 0) {
                    $existingParent = ParentModel::findByTelephoneAndSchool($newParentTelephone, $ecoleId);
                    if ($existingParent) {
                        $errors[] = 'Ce numéro appartient déjà à '
                            . $existingParent['nom_responsable']
                            . '. Sélectionnez ce parent dans « Parent existant » au lieu de créer un doublon.';
                    }
                }
            }

            if (empty($errors)) {
                $selectedClass = Classe::findByIdForSchool($classeId, $ecoleId, $sectionScopeId);
                if (!$selectedClass) {
                    $errors[] = 'La classe sélectionnée est invalide ou n’appartient pas à votre école.';
                } else {
                    if ($sectionId > 0 && (int) ($selectedClass['section_id'] ?? 0) !== $sectionId) {
                        $errors[] = 'La classe sélectionnée ne correspond pas à la section demandée.';
                    }
                    if ($optionId > 0 && (int) ($selectedClass['option_id'] ?? 0) !== $optionId) {
                        $errors[] = 'La classe sélectionnée ne correspond pas à l’option demandée.';
                    }
                        if (!$this->isAllowedClassLevel((string) ($selectedClass['nom_classe'] ?? ''), $sectionId)) {
                            $errors[] = 'Le niveau de la classe ne correspond pas à la section sélectionnée.';
                        }
                }
            }

            if (empty($errors)) {
                $activeYear = AnneeScolaire::getActiveBySchool($ecoleId);
                if (!$activeYear || empty($activeYear['id'])) {
                    $errors[] = 'Aucune année scolaire active n’est configurée pour cette école.';
                } elseif ($ecoleId > 0 && empty($this->getMandatorySchoolItems($ecoleId, (int) $activeYear['id']))) {
                    $errors[] = 'La fiche des objets classiques obligatoires n’est pas configurée pour l’année active. Contactez le comptable avant de finaliser l’inscription.';
                }
            }

            if (empty($errors)) {
                try {
                    Ecole::assertSubscriptionCapacity($ecoleId, 'students');
                    if ($parentChoice === 'new') {
                        Ecole::assertSubscriptionCapacity($ecoleId, 'parents');
                    }
                } catch (\DomainException $exception) {
                    $_SESSION['inscriptions_errors'] = [$exception->getMessage()];
                    $_SESSION['inscriptions_old'] = $_POST;
                    $this->redirect('/inscriptions/create');
                    return;
                }

                $registrationDb = Database::getConnection();
                $registrationDb->beginTransaction();

                if ($parentChoice === 'new') {
                    try {
                        $newParent = ParentModel::create([
                            'ecole_id' => $user['ecole_id'] ?? null,
                            'nom_responsable' => $newParentName,
                            'telephone' => $newParentTelephone,
                            'email' => $newParentEmail !== '' ? $newParentEmail : null,
                            'mot_de_passe' => password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT),
                        ]);

                        if (!empty($newParent['id'])) {
                            $parentId = (int) $newParent['id'];
                        } else {
                            $errors[] = 'Impossible de créer le parent/tuteur.';
                        }
                    } catch (\Throwable $e) {
                        error_log('InscriptionsController::create parent error: ' . $e->getMessage());
                        $errors[] = 'Impossible de créer le parent/tuteur. Vérifiez ses coordonnées.';
                    }
                }

                if ($parentChoice === 'existing' && $parentId > 0) {
                    $parentBelongsToSchool = ParentModel::findByIdAndSchool($parentId, $ecoleId);
                    if (!$parentBelongsToSchool) {
                        $errors[] = 'Le parent sélectionné n’appartient pas à votre école.';
                    }
                }

                if ($parentChoice !== 'existing') {
                    $parentId = $parentId > 0 ? $parentId : 0;
                }

                if (!empty($errors)) {
                    if ($registrationDb->inTransaction()) {
                        $registrationDb->rollBack();
                    }
                    $_SESSION['inscriptions_errors'] = $errors;
                    $_SESSION['inscriptions_old'] = $_POST;
                    $this->redirect('/inscriptions/create');
                    return;
                }

                $studentSchoolId = $ecoleId;
                if ($studentSchoolId <= 0 && !empty($selectedClass['ecole_id'])) {
                    $studentSchoolId = (int) $selectedClass['ecole_id'];
                }

                try {
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
                        'ecole_id' => $studentSchoolId > 0 ? $studentSchoolId : null,
                        'nom_pere' => trim($_POST['nom_pere'] ?? null),
                        'nom_mere' => trim($_POST['nom_mere'] ?? null),
                        'province_origine' => OriginesRDC::normalizeProvince(trim((string) ($_POST['province_origine'] ?? ''))),
                        'territoire' => trim($_POST['territoire'] ?? null),
                        'secteur' => isset($_POST['secteur']) ? trim((string) $_POST['secteur']) : null,
                        'groupement' => isset($_POST['groupement']) ? trim((string) $_POST['groupement']) : null,
                        'village' => isset($_POST['village']) ? trim((string) $_POST['village']) : null,
                        'num_permanent' => trim($_POST['num_permanent'] ?? null),
                        'statut_eleve' => 'inactif',
                    ]);
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                    $newStudent = null;
                } catch (\DomainException $e) {
                    $errors[] = $e->getMessage();
                    $newStudent = null;
                } catch (\PDOException $e) {
                    error_log('InscriptionsController::create student error: ' . $e->getMessage());
                    $errors[] = 'Impossible d’enregistrer l’élève dans la base de données.';
                    $newStudent = null;
                }

                if (!$newStudent || !empty($errors)) {
                    if ($registrationDb->inTransaction()) {
                        $registrationDb->rollBack();
                    }
                    $_SESSION['inscriptions_errors'] = $errors ?: ['Impossible de créer le dossier de l’élève.'];
                    $_SESSION['inscriptions_old'] = $_POST;
                    $this->redirect('/inscriptions/create');
                    return;
                }

                try {
                    if (empty($newStudent['matricule'])) {
                        $school = Ecole::findById($ecoleId);
                        $yearString = $activeYear['annee'] ?? date('Y') . '-' . (date('Y') + 1);
                        $generatedMatricule = $this->generateUniqueMatriculeFromSchool(
                            $school['nom_etablissement'] ?? 'ECOLE',
                            $nom,
                            $postnom,
                            $prenom,
                            (int) $newStudent['id'],
                            $yearString,
                            (int) $newStudent['id']
                        );
                        if (!Eleve::updateMatricule((int) $newStudent['id'], $generatedMatricule)) {
                            throw new \RuntimeException('Impossible d’attribuer un matricule à l’élève.');
                        }
                        User::syncReferenceIdentifier(
                            'eleve_ecole',
                            (int) $newStudent['id'],
                            $studentSchoolId,
                            $generatedMatricule
                        );
                        $newStudent['matricule'] = $generatedMatricule;
                    }

                    $createdRegistration = Inscription::create([
                        'eleve_id' => (int) $newStudent['id'],
                        'classe_id' => $classeId,
                        'annee_scolaire_id' => (int) $activeYear['id'],
                    ]);
                    if (!$createdRegistration) {
                        throw new \RuntimeException('La création de l’inscription n’a retourné aucun dossier.');
                    }
                    $registrationDb->commit();
                } catch (\PDOException $e) {
                    if ($registrationDb->inTransaction()) {
                        $registrationDb->rollBack();
                    }
                    error_log('InscriptionsController::create registration error: ' . $e->getMessage());
                    $_SESSION['inscriptions_errors'] = ['Impossible d’enregistrer l’inscription dans la base de données.'];
                    $_SESSION['inscriptions_old'] = $_POST;
                    $this->redirect('/inscriptions/create');
                    return;
                } catch (\InvalidArgumentException $e) {
                    if ($registrationDb->inTransaction()) {
                        $registrationDb->rollBack();
                    }
                    $_SESSION['inscriptions_errors'] = [$e->getMessage()];
                    $_SESSION['inscriptions_old'] = $_POST;
                    $this->redirect('/inscriptions/create');
                    return;
                } catch (\RuntimeException $e) {
                    if ($registrationDb->inTransaction()) {
                        $registrationDb->rollBack();
                    }
                    error_log('InscriptionsController::create registration error: ' . $e->getMessage());
                    $_SESSION['inscriptions_errors'] = ['Impossible de finaliser le dossier d’inscription.'];
                    $_SESSION['inscriptions_old'] = $_POST;
                    $this->redirect('/inscriptions/create');
                    return;
                }

                // After creating the inscription, assign applicable fees to the student's account
                $appliedFees = [];
                $appliedTotal = 0.0;
                try {
                    $db = \App\Core\Database::getConnection();
                    $activeYear = $activeYear ?? AnneeScolaire::getActiveBySchool($ecoleId);
                    $yearId = $activeYear['id'] ?? null;
                    $studentId = (int) ($newStudent['id'] ?? 0);
                    if ($studentId > 0 && $yearId) {
                        // load class details to determine option/section
                        $classRec = Classe::findById($classeId);
                        $classOption = (int) ($classRec['option_id'] ?? 0);
                        $classSection = (int) ($classRec['section_id'] ?? 0);

                        // fetch fees for the school and filter by applicability and year
                        $schoolIdForFees = $studentSchoolId > 0 ? $studentSchoolId : ($ecoleId > 0 ? $ecoleId : 0);
                        $fees = \App\Models\FraisScolaire::getAllBySchool((int) $schoolIdForFees);

                        // determine agent id for ecritures
                        $agentId = $user['reference_id'] ?? null;
                        if (empty($agentId)) {
                            $agstmt = $db->prepare('SELECT id FROM agents WHERE ecole_id = :ecole LIMIT 1');
                            $agstmt->execute([':ecole' => $ecoleId]);
                            $found = $agstmt->fetch(\PDO::FETCH_ASSOC);
                            $agentId = $found['id'] ?? null;
                        }
                        if (empty($agentId)) $agentId = 1;

                        // ensure compte exists for this student and year
                        $cstmt = $db->prepare('SELECT id, solde_debiteur FROM comptes_eleves WHERE eleve_id = :eleve AND annee_scolaire_id = :annee LIMIT 1');
                        $cstmt->execute([':eleve' => $studentId, ':annee' => $yearId]);
                        $compte = $cstmt->fetch(\PDO::FETCH_ASSOC);
                        if (!$compte) {
                            $ist = $db->prepare('INSERT INTO comptes_eleves (eleve_id, annee_scolaire_id, solde_debiteur) VALUES (:eleve, :annee, 0.00)');
                            $ist->execute([':eleve' => $studentId, ':annee' => $yearId]);
                            $compteId = (int) $db->lastInsertId();
                            $currentBalance = 0.00;
                        } else {
                            $compteId = (int) $compte['id'];
                            $currentBalance = (float) $compte['solde_debiteur'];
                        }

                        foreach ($fees as $f) {
                            $apply = false;
                            $scope = $f['scope'] ?? 'class';
                            $scopeId = isset($f['scope_id']) ? (int) $f['scope_id'] : null;
                            // only consider fees for the active year
                            if (!empty($f['annee_scolaire_id']) && (int) $f['annee_scolaire_id'] !== (int) $yearId) {
                                continue;
                            }

                            // For class-scoped fees we accept either explicit classe_id or scope_id (backwards compatibility)
                            if ($scope === 'class') {
                                $feeClasseId = isset($f['classe_id']) ? (int) $f['classe_id'] : null;
                                if ($feeClasseId === $classeId || $scopeId === $classeId) {
                                    $apply = true;
                                }
                            } elseif ($scope === 'option') {
                                if (!empty($scopeId) && $scopeId === $classOption) $apply = true;
                            } elseif ($scope === 'section') {
                                if (!empty($scopeId) && $scopeId === $classSection) $apply = true;
                            } elseif ($scope === 'school') {
                                $apply = true;
                            }

                            if ($apply) {
                                $montant = (float) ($f['montant_total'] ?? 0);
                                if ($montant <= 0) continue;
                                $libelle = 'Facturation inscription: ' . ($f['type_frais'] ?? 'Frais');
                                $est = $db->prepare('INSERT INTO ecritures_comptables_eleves (compte_eleve_id, frais_id, caisse_banque_id, type_mouvement, montant, reference_recu, libelle, agent_saisie_id) VALUES (:compte, :frais, NULL, :type, :montant, NULL, :libelle, :agent)');
                                $est->execute([
                                    ':compte' => $compteId,
                                    ':frais' => $f['id'] ?? null,
                                    ':type' => 'DEBIT',
                                    ':montant' => $montant,
                                    ':libelle' => $libelle,
                                    ':agent' => $agentId,
                                ]);

                                $debtStmt = $db->prepare(
                                    'INSERT INTO dettes_eleves
                                        (eleve_id, frais_id, annee_scolaire_id, montant_initial, montant_restant, devise)
                                     VALUES (:eleve_id, :frais_id, :year_id, :initial, :remaining, :devise)'
                                );
                                $debtStmt->execute([
                                    ':eleve_id' => $studentId,
                                    ':frais_id' => (int) $f['id'],
                                    ':year_id' => (int) $yearId,
                                    ':initial' => $montant,
                                    ':remaining' => $montant,
                                    ':devise' => strtoupper(trim((string) ($f['devise'] ?? 'USD'))) ?: 'USD',
                                ]);

                                $currentBalance += $montant;
                                $appliedFees[] = [
                                    'id' => $f['id'] ?? null,
                                    'label' => $f['type_frais'] ?? 'Frais',
                                    'amount' => $montant,
                                ];
                                $appliedTotal += $montant;
                            }
                        }

                        // update compte balance if changed
                        $ust = $db->prepare('UPDATE comptes_eleves SET solde_debiteur = :solde WHERE id = :id');
                        $ust->execute([':solde' => $currentBalance, ':id' => $compteId]);
                    }
                } catch (\Throwable $e) {
                    error_log('InscriptionsController::assign fees error: ' . $e->getMessage());
                    // non-blocking: continue without failing the inscription
                }

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
                if (!empty($appliedTotal) && $appliedTotal > 0) {
                    $_SESSION['inscriptions_success'] = 'L’élève a été enregistré. Frais appliqués: ' . number_format($appliedTotal, 2) . ' (' . count($appliedFees) . ' frais). La validation doit être effectuée par le secrétaire.';
                } else {
                    $_SESSION['inscriptions_success'] = 'L’élève a été enregistré. La validation doit être effectuée par le secrétaire.';
                }
                $this->redirect('/inscriptions');
            }

            $_SESSION['inscriptions_errors'] = $errors;
            $_SESSION['inscriptions_old'] = $_POST;
        }

        $this->redirect('/inscriptions/create');
    }

    public function matriculePreview(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        header('Content-Type: application/json');
        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $nom = trim($_GET['nom'] ?? '');
        $postnom = trim($_GET['postnom'] ?? '');
        $prenom = trim($_GET['prenom'] ?? '');

        if ($ecoleId <= 0 || $nom === '' || $postnom === '') {
            echo json_encode(['error' => 'Nom, postnom et école requis pour générer le matricule.']);
            exit;
        }

        $school = Ecole::findById($ecoleId);
        $activeYear = AnneeScolaire::getActiveBySchool($ecoleId);
        $yearString = $activeYear['annee'] ?? date('Y') . '-' . (date('Y') + 1);
        $preview = $this->generateMatriculeFromSchool(
            $school['nom_etablissement'] ?? 'ECOLE',
            $nom,
            $postnom,
            $prenom,
            random_int(1, 9999),
            $yearString
        );

        echo json_encode(['matricule' => $preview]);
        exit;
    }

    public function edit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::IDENTITY_EDIT_ROLES);

        $eleveId = (int) ($_GET['id'] ?? 0);
        if ($eleveId <= 0) {
            $this->redirect('/inscriptions');
        }

        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $student = Eleve::findByIdAndSchool($eleveId, $ecoleId, $sectionScopeId);
        if (!$student) {
            $_SESSION['inscriptions_errors'] = ['Élève introuvable ou n’appartenant pas à votre école.'];
            $this->redirect('/inscriptions');
        }

        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $parents = $ecoleId > 0 ? ParentModel::getAllBySchool($ecoleId, $sectionScopeId) : [];
        $classes = $ecoleId > 0 ? Classe::getAllBySchool($ecoleId, $sectionScopeId) : [];
        $sections = $sectionScopeId !== null
            ? array_values(array_filter(Section::getAll(), static fn (array $section): bool => (int) $section['id'] === $sectionScopeId))
            : Section::getAll();
        $options = $ecoleId > 0
            ? ($sectionScopeId !== null
                ? Option::getAllForSchoolAndSection($ecoleId, $sectionScopeId)
                : Option::getAllForSchool($ecoleId))
            : [];
        $db = Database::getConnection();
        $inscriptionStmt = $db->prepare(
            'SELECT i.*, c.section_id, c.option_id
             FROM inscriptions i
             INNER JOIN classes c ON c.id = i.classe_id
             WHERE i.eleve_id = :eleve_id'
             . ($sectionScopeId !== null ? ' AND c.section_id = :section_id' : '')
             . ' ORDER BY i.date_inscription DESC, i.id DESC LIMIT 1'
        );
        $inscriptionParams = [':eleve_id' => $eleveId];
        if ($sectionScopeId !== null) {
            $inscriptionParams[':section_id'] = $sectionScopeId;
        }
        $inscriptionStmt->execute($inscriptionParams);
        $currentInscription = $inscriptionStmt->fetch() ?: [];
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
            'classes' => $classes,
            'sections' => $sections,
            'options' => $options,
            'currentInscription' => $currentInscription,
            'oldInput' => $oldInput,
        ]);
    }

    public function update(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::IDENTITY_EDIT_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eleveId = (int) ($_POST['eleve_id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $postnom = trim($_POST['postnom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $dateNaissance = trim($_POST['date_naissance'] ?? '');
            $matricule = trim($_POST['matricule'] ?? '');
            $parentId = (int) ($_POST['parent_id'] ?? 0);
            $classeId = (int) ($_POST['classe_id'] ?? 0);
            $sectionId = (int) ($_POST['section_id'] ?? 0);
            $optionId = (int) ($_POST['option_id'] ?? 0);

            $errors = [];
            $errors = array_merge($errors, $this->validateOriginSelection($_POST));
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
            if ($classeId <= 0) {
                $errors[] = 'La classe est requise.';
            }
            if ($sectionId <= 0) {
                $errors[] = 'La section est requise.';
            }
            if ($optionId <= 0) {
                $errors[] = 'L’option est requise.';
            }

            $user = Auth::refresh() ?: Auth::user();
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $sectionScopeId = $this->getSectionScopeForUser($user);
            $db = Database::getConnection();
            $student = Eleve::findByIdAndSchool($eleveId, $ecoleId, $sectionScopeId);
            if (!$student) {
                $errors[] = 'Élève introuvable ou n’appartenant pas à votre école.';
            }

            $parentUpdateData = null;
            if ($parentId > 0) {
                $parent = ParentModel::findByIdAndSchool($parentId, $ecoleId, $sectionScopeId);
                if (!$parent) {
                    $errors[] = 'Le parent sélectionné n’appartient pas à votre école.';
                } else {
                    $parentName = trim($_POST['parent_nom_responsable'] ?? '');
                    $parentTelephone = trim($_POST['parent_telephone'] ?? '');
                    $parentEmail = trim($_POST['parent_email'] ?? '');
                    if ($parentName === '') {
                        $errors[] = 'Le nom du parent/tuteur est requis.';
                    }
                    if ($parentTelephone === '') {
                        $errors[] = 'Le téléphone du parent/tuteur est requis.';
                    } elseif (!preg_match('/^[0-9+() .-]{7,25}$/', $parentTelephone)) {
                        $errors[] = 'Le téléphone du parent contient des caractères invalides.';
                    }
                    if ($parentEmail !== '' && !filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'L’adresse email du parent est invalide.';
                    }
                    if (strlen($parentName) > 150 || strlen($parentEmail) > 100) {
                        $errors[] = 'Le nom ou l’email du parent dépasse la longueur autorisée.';
                    }
                    $contactConflict = $parentName !== ''
                        && $parentTelephone !== ''
                        && ParentModel::hasContactConflict(
                            $parentId,
                            $ecoleId,
                            $parentTelephone,
                            $parentEmail !== '' ? $parentEmail : null
                        );
                    if (
                        $parentName !== ''
                        && $parentTelephone !== ''
                        && !$contactConflict
                    ) {
                        $parentUpdateData = [
                            'nom_responsable' => $parentName,
                            'telephone' => $parentTelephone,
                            'email' => $parentEmail !== '' ? $parentEmail : null,
                        ];
                    } elseif (
                        $parentName !== ''
                        && $parentTelephone !== ''
                        && $contactConflict
                    ) {
                        $errors[] = 'Ce numéro de téléphone ou cette adresse email est déjà utilisé par un autre parent.';
                    }
                }
            }

            $selectedClass = $classeId > 0 ? Classe::findByIdForSchool($classeId, $ecoleId, $sectionScopeId) : null;
            if (!$selectedClass) {
                $errors[] = 'La classe sélectionnée est invalide ou n’appartient pas à votre école.';
            } elseif ((int) ($selectedClass['section_id'] ?? 0) !== $sectionId || (int) ($selectedClass['option_id'] ?? 0) !== $optionId) {
                $errors[] = 'La classe sélectionnée ne correspond pas à la section et à l’option choisies.';
            } elseif (!$this->isAllowedClassLevel((string) ($selectedClass['nom_classe'] ?? ''), $sectionId)) {
                $errors[] = 'Le niveau de la classe ne correspond pas à la section sélectionnée.';
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
                    'province_origine' => OriginesRDC::normalizeProvince(trim((string) ($_POST['province_origine'] ?? ''))),
                    'territoire' => trim($_POST['territoire'] ?? null),
                    'secteur' => isset($_POST['secteur']) ? trim((string) $_POST['secteur']) : null,
                    'groupement' => isset($_POST['groupement']) ? trim((string) $_POST['groupement']) : null,
                    'village' => isset($_POST['village']) ? trim((string) $_POST['village']) : null,
                    'num_permanent' => trim($_POST['num_permanent'] ?? null),
                ];

                $inscriptionSql = 'SELECT i.id FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id WHERE i.eleve_id = :eleve_id';
                $inscriptionParams = [':eleve_id' => $eleveId];
                if ($sectionScopeId !== null) {
                    $inscriptionSql .= ' AND c.section_id = :section_id';
                    $inscriptionParams[':section_id'] = $sectionScopeId;
                }
                $inscriptionStmt = $db->prepare($inscriptionSql . ' ORDER BY i.date_inscription DESC, i.id DESC LIMIT 1');
                $inscriptionStmt->execute($inscriptionParams);
                $currentInscriptionId = (int) ($inscriptionStmt->fetchColumn() ?: 0);
                if ($currentInscriptionId <= 0) {
                    $errors[] = 'Aucune inscription scolaire à mettre à jour n’a été trouvée.';
                } else {
                    $updateInscription = $db->prepare('UPDATE inscriptions SET classe_id = :classe_id WHERE id = :id');
                    $updateInscription->execute([':classe_id' => $classeId, ':id' => $currentInscriptionId]);
                }

                $existingStudent = Eleve::findById($eleveId);
                $existingMatricule = $existingStudent['matricule'] ?? '';
                if ($matricule === '') {
                    $matricule = $existingMatricule;
                }
                try {
                    $matricule = $this->ensureUniqueMatricule($matricule, $eleveId);
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                }

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

                try {
                    $db->beginTransaction();
                    if ($parentUpdateData !== null && !ParentModel::updateForSchool($parentId, $ecoleId, $parentUpdateData)) {
                        throw new \RuntimeException('Impossible de mettre à jour la fiche du parent sélectionné.');
                    }
                    Eleve::update($eleveId, $updateData);
                    $db->commit();
                    unset($_SESSION['inscriptions_old']);
                    $_SESSION['inscriptions_success'] = $parentUpdateData !== null
                        ? 'Les informations de l’inscription et du parent ont été mises à jour.'
                        : 'Les informations de l’inscription ont été mises à jour.';
                    $this->redirect('/inscriptions');
                } catch (\InvalidArgumentException $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $errors[] = $e->getMessage();
                } catch (\PDOException $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    error_log('InscriptionsController::update parent/student error: ' . $e->getMessage());
                    $errors[] = 'La mise à jour a échoué. Vérifiez que le téléphone et l’email du parent ne sont pas déjà utilisés.';
                } catch (\RuntimeException $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $errors[] = $e->getMessage();
                }
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
        Auth::requireAuth();
        $this->requireAdministrationModuleAccess(Auth::refresh() ?: Auth::user(), 'inscriptions');
        header('Content-Type: application/json');
        echo json_encode(OriginesRDC::provinces(), JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function territoires(): void
    {
        Auth::requireAuth();
        $this->requireAdministrationModuleAccess(Auth::refresh() ?: Auth::user(), 'inscriptions');
        header('Content-Type: application/json');
        $province = trim($_GET['province'] ?? '');
        echo json_encode(OriginesRDC::territoriesFor($province), JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function findParentByPhone(): void
    {
        Auth::requireAuth();
        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $telephone = trim((string) ($_GET['telephone'] ?? ''));
        if (!preg_match('/^[0-9+() .-]{7,25}$/', $telephone)) {
            http_response_code(400);
            echo json_encode(['error' => 'Numéro de téléphone invalide.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $parent = $ecoleId > 0
            ? ParentModel::findByTelephoneAndSchool($telephone, $ecoleId)
            : null;

        echo json_encode(
            ['parent' => $parent ? [
                'id' => (int) $parent['id'],
                'nom_responsable' => (string) $parent['nom_responsable'],
                'telephone' => (string) $parent['telephone'],
            ] : null],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    public function approve(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'sec_école', 'préfet_école', 'DP_école', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'inscriptions');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = $user['role'] ?? 'default';
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $eleveId = (int) ($_POST['eleve_id'] ?? 0);

            if ($eleveId > 0) {
                $student = $role === 'super_admin'
                    ? Eleve::findById($eleveId)
                    : ($ecoleId > 0
                        ? Eleve::findByIdAndSchool($eleveId, $ecoleId, $this->getSectionScopeForUser($user))
                        : null);
                if ($student) {
                    $wasAlreadyActive = ($student['statut_eleve'] ?? '') === 'actif';
                    if ($wasAlreadyActive) {
                        $_SESSION['inscriptions_success'] = 'Cette inscription était déjà validée.';
                    } elseif (Eleve::approve($eleveId)) {
                        $_SESSION['inscriptions_success'] = 'Inscription validée par le personnel autorisé.';
                        try {
                            $studentName = \App\Core\PersonName::format($student);
                            $recipientCount = \App\Models\UserMessage::notifyParentForStudent(
                                $eleveId,
                                'inscription',
                                $eleveId,
                                'Inscription confirmée pour ' . $studentName,
                                'L’inscription de ' . $studentName . ' a été confirmée par le secrétariat.'
                            );
                            $_SESSION['inscriptions_notification'] = [
                                'status' => $recipientCount > 0 ? 'success' : 'failed',
                                'message' => $recipientCount > 0
                                    ? 'Un message de confirmation a été envoyé dans la messagerie du parent.'
                                    : 'Aucun compte parent actif n’a été trouvé pour recevoir le message.',
                            ];
                        } catch (\Throwable $e) {
                            error_log('InscriptionsController::approve notification failed: ' . $e->getMessage());
                            $_SESSION['inscriptions_notification'] = [
                                'sent' => false,
                                'status' => 'failed',
                                'message' => 'L’inscription est validée, mais le message interne au parent n’a pas pu être créé.',
                                'details' => 'Consultez les journaux du serveur.',
                            ];
                        }
                    } else {
                        $_SESSION['inscriptions_errors'] = ['Cette inscription a déjà été validée ou ne peut plus être modifiée.'];
                    }
                } else {
                    $_SESSION['inscriptions_errors'] = ['Vous n’êtes pas autorisé(e) à valider cette inscription.'];
                }
            }
        }

        $this->redirect('/inscriptions');
    }

    private function validateOriginSelection(array $input): array
    {
        $province = OriginesRDC::normalizeProvince(trim((string) ($input['province_origine'] ?? '')));
        $territory = trim((string) ($input['territoire'] ?? ''));
        if ($province === '') {
            return $territory === '' ? [] : ['Sélectionnez une province avant de choisir un territoire ou une commune.'];
        }
        if (!OriginesRDC::isKnownProvince($province)) {
            return ['La province d’origine sélectionnée est invalide.'];
        }
        if ($territory !== '' && !in_array($territory, OriginesRDC::territoriesFor($province), true)) {
            return ['Le territoire ou la commune ne correspond pas à la province sélectionnée.'];
        }

        return [];
    }

    private function ensureUniqueMatricule(string $matricule, ?int $excludeId = null): string
    {
        $value = trim($matricule);
        if ($value === '') {
            return '';
        }
        if (!Eleve::isValidMatriculeFormat($value)) {
            throw new \InvalidArgumentException('Le matricule doit contenir 4 lettres majuscules suivies de 5 chiffres.');
        }
        if (Eleve::isMatriculeTaken($value, $excludeId)) {
            throw new \InvalidArgumentException('Le matricule saisi est déjà utilisé.');
        }

        return $value;
    }

    private function generateUniqueMatricule(string $name, ?int $excludeId = null): string
    {
        $value = $this->generateMatricule($name);
        $attempts = 0;
        while (Eleve::isMatriculeTaken($value, $excludeId) && $attempts < 10) {
            $value = $this->generateMatricule($name);
            $attempts++;
        }

        return $value;
    }

    private function generateUniqueMatriculeFromSchool(string $schoolName, string $nom, string $postnom, string $prenom, int $eleveId, string $annee, ?int $excludeId = null): string
    {
        $value = $this->generateMatriculeFromSchool($schoolName, $nom, $postnom, $prenom, $eleveId, $annee);
        $attempts = 0;
        while (Eleve::isMatriculeTaken($value, $excludeId) && $attempts < 10) {
            $value = $this->generateMatriculeFromSchool($schoolName, $nom, $postnom, $prenom, $eleveId, $annee);
            $attempts++;
        }

        return $value;
    }

    private function generateMatricule(string $name): string
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($name));
        if ($letters === '') {
            $letters = 'ELEVE';
        }
        $letters = substr(str_pad($letters, 4, 'X'), 0, 4);
        $numbers = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        return $letters . $numbers;
    }

    private function generateMatriculeFromSchool(string $schoolName, string $nom, string $postnom, string $prenom, int $eleveId, string $annee): string
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($schoolName . $nom . $postnom . $prenom));
        if ($letters === '') {
            $letters = 'ECOLE';
        }
        $letters = substr(str_pad($letters, 4, 'X'), 0, 4);
        $numbers = str_pad((string) ($eleveId % 100000), 5, '0', STR_PAD_LEFT);
        return $letters . $numbers;
    }

    private function getRandomLettersFromSchool(string $schoolName, int $length): string
    {
        $letters = preg_replace('/[^A-Z]/', '', mb_strtoupper($schoolName, 'UTF-8'));
        if ($letters === '') {
            return str_repeat('X', $length);
        }

        $chars = preg_split('//u', $letters, -1, PREG_SPLIT_NO_EMPTY);
        $result = '';
        $max = count($chars);
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max - 1)];
        }

        return $result;
    }

    private function getStudentInitials(string $nom, string $postnom, string $prenom): string
    {
        $parts = [$nom, $postnom, $prenom];
        $initials = '';
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
        }

        return $initials ?: 'XXX';
    }

    private function getYearCode(string $annee): string
    {
        if (preg_match('/(\d{4})[^\d]+(\d{4})/', $annee, $matches)) {
            return substr($matches[1], -2) . substr($matches[2], -2);
        }

        return substr((string) date('Y'), -2) . substr((string) (date('Y') + 1), -2);
    }

    private function isAllowedClassLevel(string $className, int $sectionId): bool
    {
        if (!preg_match('/^\s*(\d+)\s*(?:e|er|ère|ere|ème|eme|ième|ieme)?/iu', trim($className), $matches)) {
            return false;
        }

        $level = (int) $matches[1];
        return match ($sectionId) {
            1 => $level >= 1 && $level <= 3,
            2 => $level >= 1 && $level <= 6,
            3 => $level === 7 || $level === 8 || ($level >= 1 && $level <= 4),
            default => false,
        };
    }
}
