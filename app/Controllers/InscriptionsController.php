<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Inscription;
use App\Models\Option;
use App\Models\ParentModel;
use App\Models\Section;
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
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $pendingStudents = $ecoleId > 0 ? Eleve::getPendingBySchool($ecoleId) : Eleve::getPending();
        $enrolledStudents = $ecoleId > 0 ? Eleve::getEnrolledBySchool($ecoleId) : [];
        $sections = Section::getAll();
        $options = Option::getAll();

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
            'canEdit' => in_array($role, self::SUBMISSION_ROLES, true),
            'canApprove' => in_array($role, ['super_admin', 'sec_école', 'préfet_école', 'DP_école', 'comptable_école'], true),
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
        $parents = $ecoleId > 0 ? ParentModel::getAllBySchool($ecoleId) : [];
        $sections = Section::getAll();
        $selectedSection = null;
        $selectedOption = null;
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        $optionId = (int) ($_GET['option_id'] ?? 0);
        if ($sectionId > 0) {
            $selectedSection = Section::findById($sectionId);
        }
        if ($optionId > 0) {
            $selectedOption = Option::findById($optionId);
        }

        $classes = [];
        if ($ecoleId > 0) {
            if ($selectedSection && $selectedOption) {
                $classes = Classe::getAllBySchoolSectionAndOption($ecoleId, $selectedSection['id'], $selectedOption['id']);
            } elseif ($selectedSection) {
                $classes = Classe::getAllBySchoolAndSection($ecoleId, $selectedSection['id']);
            } else {
                $classes = Classe::getAllBySchool($ecoleId);
            }
        }
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
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::SUBMISSION_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

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
                $matricule = $this->generateUniqueMatricule($nom . ' ' . $postnom);
            } else {
                try {
                    $matricule = $this->ensureUniqueMatricule($matricule);
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if ($classeId <= 0) {
                $errors[] = 'La classe associée est requise pour l’inscription.';
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
                $selectedClass = Classe::findById($classeId);
                if (!$selectedClass || (int) ($selectedClass['ecole_id'] ?? 0) !== $ecoleId) {
                    $errors[] = 'La classe sélectionnée est invalide ou n’appartient pas à votre école.';
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

                if ($parentChoice === 'existing' && $parentId > 0) {
                    $parentBelongsToSchool = ParentModel::findByIdAndSchool($parentId, $ecoleId);
                    if (!$parentBelongsToSchool) {
                        $errors[] = 'Le parent sélectionné n’appartient pas à votre école.';
                    }
                }

                if ($parentChoice !== 'existing') {
                    $parentId = $parentId > 0 ? $parentId : 0;
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
                        'province_origine' => trim($_POST['province_origine'] ?? null),
                        'territoire' => trim($_POST['territoire'] ?? null),
                        'secteur' => trim($_POST['secteur'] ?? null),
                        'groupement' => trim($_POST['groupement'] ?? null),
                        'village' => trim($_POST['village'] ?? null),
                        'num_permanent' => trim($_POST['num_permanent'] ?? null),
                        'statut_eleve' => 'inactif',
                    ]);
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                    $newStudent = null;
                }

                if ($newStudent && empty($newStudent['matricule'])) {
                    $school = Ecole::findById($ecoleId);
                    $activeYear = AnneeScolaire::getActiveBySchool($ecoleId);
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
                    Eleve::updateMatricule((int) $newStudent['id'], $generatedMatricule);
                    $newStudent['matricule'] = $generatedMatricule;
                }

                if ($newStudent) {
                    $activeYear = AnneeScolaire::getActiveBySchool($ecoleId);
                    if ($activeYear) {
                        Inscription::create([
                            'eleve_id' => (int) $newStudent['id'],
                            'classe_id' => $classeId,
                            'annee_scolaire_id' => (int) $activeYear['id'],
                        ]);
                    }
                }

                // After creating the inscription, assign applicable fees to the student's account
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
                            if ($scope === 'class') {
                                if (!empty($f['classe_id']) && (int) $f['classe_id'] === $classeId) $apply = true;
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

                                $currentBalance += $montant;
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
                $_SESSION['inscriptions_success'] = 'L’élève a été enregistré. La validation doit être effectuée par le secrétaire.';
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
        Auth::requireRoles(self::SUBMISSION_ROLES);

        $eleveId = (int) ($_GET['id'] ?? 0);
        if ($eleveId <= 0) {
            $this->redirect('/inscriptions');
        }

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $student = Eleve::findByIdAndSchool($eleveId, $ecoleId);
        if (!$student) {
            $_SESSION['inscriptions_errors'] = ['Élève introuvable ou n’appartenant pas à votre école.'];
            $this->redirect('/inscriptions');
        }

        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $parents = $ecoleId > 0 ? ParentModel::getAllBySchool($ecoleId) : [];
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

            $user = Auth::refresh() ?: Auth::user();
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $student = Eleve::findByIdAndSchool($eleveId, $ecoleId);
            if (!$student) {
                $errors[] = 'Élève introuvable ou n’appartenant pas à votre école.';
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
                    Eleve::update($eleveId, $updateData);
                    unset($_SESSION['inscriptions_old']);
                    $_SESSION['inscriptions_success'] = 'Les informations de l’inscription ont été mises à jour.';
                    $this->redirect('/inscriptions');
                } catch (\InvalidArgumentException $e) {
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
        header('Content-Type: application/json');
        $provinces = [
            'Kinshasa', 'Nord-Kivu', 'Sud-Kivu', 'Haut-Katanga', 'Kongo Central', 'Équateur', 'Maniema', 'Ituri'
        ];
        echo json_encode(array_values($provinces));
        exit;
    }

    public function territoires(): void
    {
        Auth::requireAuth();
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
        Auth::requireAuth();
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
        Auth::requireAuth();
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
        Auth::requireAuth();
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
        Auth::requireRoles(['super_admin', 'sec_école', 'préfet_école', 'DP_école', 'comptable_école']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::refresh() ?: Auth::user();
            $role = $user['role'] ?? 'default';
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $eleveId = (int) ($_POST['eleve_id'] ?? 0);

            if ($eleveId > 0) {
                if ($role === 'super_admin' || $ecoleId <= 0 || Eleve::findByIdAndSchool($eleveId, $ecoleId)) {
                    Eleve::approve($eleveId);
                    $_SESSION['inscriptions_success'] = 'Inscription validée par le personnel autorisé.';
                } else {
                    $_SESSION['inscriptions_errors'] = ['Vous n’êtes pas autorisé(e) à valider cette inscription.'];
                }
            }
        }

        $this->redirect('/inscriptions');
    }

    private function ensureUniqueMatricule(string $matricule, ?int $excludeId = null): string
    {
        $value = trim($matricule);
        if ($value === '') {
            return '';
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
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', mb_substr($name, 0, 6, 'UTF-8')));
        $prefix = $prefix === '' ? 'ELEVE' : $prefix;
        return sprintf('%s-%s-%s', $prefix, date('YmdHis'), substr(bin2hex(random_bytes(3)), 0, 6));
    }

    private function generateMatriculeFromSchool(string $schoolName, string $nom, string $postnom, string $prenom, int $eleveId, string $annee): string
    {
        $letters = $this->getRandomLettersFromSchool($schoolName, 3);
        $initials = $this->getStudentInitials($nom, $postnom, $prenom);
        $yearCode = $this->getYearCode($annee);
        return strtoupper($letters . $initials . $eleveId . $yearCode);
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
}
