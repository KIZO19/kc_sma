<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\FraisScolaire;
use App\Models\Option;
use App\Models\Section;
use App\Models\User;

class FraisController extends Controller
{
    private const MANAGEMENT_ROLES = ['super_admin', 'ecole_admin', 'comptable_école'];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $fees = $ecoleId > 0 ? FraisScolaire::getAllBySchool($ecoleId) : [];
        $school = $ecoleId > 0 ? \App\Models\Ecole::findById($ecoleId) : null;
        $schoolCurrency = $school['devise_principale'] ?? 'USD';

        $this->view('frais/index', [
            'title' => APP_NAME . ' - Frais',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'fees' => $fees,
            'schoolCurrency' => $schoolCurrency,
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $classes = $ecoleId > 0 ? Classe::getAllBySchool($ecoleId) : [];
        $years = $ecoleId > 0 ? AnneeScolaire::getAllBySchool($ecoleId) : [];
        $activeYear = $ecoleId > 0 ? AnneeScolaire::getActiveBySchool($ecoleId) : null;
        $defaultYearId = $activeYear['id'] ?? 0;
        $school = $ecoleId > 0 ? \App\Models\Ecole::findById($ecoleId) : null;
        $schoolCurrency = $school['devise_principale'] ?? 'USD';
        $currencyOptions = $this->getCurrencyOptions();
        $options = $ecoleId > 0 ? Option::getAll() : [];
        $sections = $ecoleId > 0 ? Section::getAll() : [];
        $oldInput = $_SESSION['frais_old'] ?? [];
        unset($_SESSION['frais_old']);

        $this->view('frais/create', [
            'title' => APP_NAME . ' - Nouveau frais',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'classes' => $classes,
            'years' => $years,
            'defaultYearId' => $defaultYearId,
            'options' => $options,
            'sections' => $sections,
            'currencies' => $currencyOptions,
            'schoolCurrency' => $schoolCurrency,
            'oldInput' => $oldInput,
        ]);
    }

    public function submit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::refresh() ?: Auth::user();
            $ecoleId = (int) ($user['ecole_id'] ?? 0);
            $typeFrais = trim($_POST['type_frais'] ?? '');
            $montantTotal = trim($_POST['montant_total'] ?? '');
            $classeId = (int) ($_POST['classe_id'] ?? 0);
            $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);
            $scope = trim($_POST['scope'] ?? 'class');
            $scopeId = (int) ($_POST['scope_id'] ?? 0);
            $devise = trim($_POST['devise'] ?? '');

            $errors = [];
            if ($ecoleId <= 0) {
                $errors[] = 'École introuvable pour cet utilisateur.';
            }
            if ($typeFrais === '') {
                $errors[] = 'Le type de frais est requis.';
            }
            if ($montantTotal === '' || !is_numeric($montantTotal) || (float) $montantTotal < 0) {
                $errors[] = 'Le montant total doit être un nombre positif.';
            }
            // Validate scope and related identifiers
            $allowedScopes = ['class', 'option', 'section', 'school'];
            if (!in_array($scope, $allowedScopes, true)) {
                $errors[] = 'Portée de frais invalide.';
            }

            if ($scope === 'class') {
                if ($classeId <= 0) {
                    $errors[] = 'La classe associée est requise pour cette portée.';
                }
            } elseif ($scope === 'option') {
                if ($scopeId <= 0) {
                    $errors[] = 'L’option doit être sélectionnée pour cette portée.';
                }
            } elseif ($scope === 'section') {
                if ($scopeId <= 0) {
                    $errors[] = 'La section doit être sélectionnée pour cette portée.';
                }
            } else {
                // school-wide: clear class selection
                $classeId = 0;
            }

            // If provided year is missing or invalid, attempt to use the school's active year
            if ($anneeScolaireId <= 0) {
                $active = AnneeScolaire::getActiveBySchool($ecoleId);
                if ($active) {
                    $anneeScolaireId = (int) $active['id'];
                } else {
                    $errors[] = 'L’année scolaire est requise.';
                }
            } else {
                $yearRec = AnneeScolaire::findById($anneeScolaireId);
                if (!$yearRec || ((int) ($yearRec['ecole_id'] ?? 0) !== $ecoleId)) {
                    $active = AnneeScolaire::getActiveBySchool($ecoleId);
                    if ($active) {
                        $anneeScolaireId = (int) $active['id'];
                    } else {
                        $errors[] = 'L’année scolaire fournie est invalide.';
                    }
                }
            }
            if ($devise === '') {
                $errors[] = 'La devise est requise.';
            } elseif (!array_key_exists($devise, $this->getCurrencyOptions())) {
                $errors[] = 'La devise sélectionnée est invalide.';
            }

            if (empty($errors)) {
                try {
                    $insertId = FraisScolaire::create([
                    'classe_id' => $scope === 'class' ? $classeId : null,
                    'type_frais' => $typeFrais,
                    'montant_total' => (float) $montantTotal,
                    'annee_scolaire_id' => $anneeScolaireId,
                    'devise' => $devise,
                    'scope' => $scope,
                    'scope_id' => $scope === 'class' ? $classeId : ($scope === 'option' || $scope === 'section' ? $scopeId : null),
                    ]);
                } catch (\PDOException $e) {
                    $errors[] = 'Erreur base de données : colonne manquante ou migration non appliquée. Exécutez les migrations SQL depuis app/Config et réessayez.';
                    // log exception in error log for debugging
                    error_log('FraisController::submit DB error: ' . $e->getMessage());
                    $insertId = false;
                }

                if ($insertId !== false && $insertId > 0) {
                    // assign debts to students according to scope
                    $db = \App\Core\Database::getConnection();
                    // build student list SQL based on scope
                    if ($scope === 'class') {
                        $stmt = $db->prepare('SELECT eleve_id FROM inscriptions WHERE classe_id = :classe AND annee_scolaire_id = :annee');
                        $stmt->execute([':classe' => $classeId, ':annee' => $anneeScolaireId]);
                    } elseif ($scope === 'option') {
                        $stmt = $db->prepare('SELECT i.eleve_id FROM inscriptions i JOIN classes c ON c.id = i.classe_id WHERE c.option_id = :opt AND i.annee_scolaire_id = :annee');
                        $stmt->execute([':opt' => $scopeId, ':annee' => $anneeScolaireId]);
                    } elseif ($scope === 'section') {
                        $stmt = $db->prepare('SELECT i.eleve_id FROM inscriptions i JOIN classes c ON c.id = i.classe_id WHERE c.section_id = :sec AND i.annee_scolaire_id = :annee');
                        $stmt->execute([':sec' => $scopeId, ':annee' => $anneeScolaireId]);
                    } else { // school-wide
                        $stmt = $db->prepare('SELECT i.eleve_id FROM inscriptions i JOIN classes c ON c.id = i.classe_id WHERE c.ecole_id = :ecole AND i.annee_scolaire_id = :annee');
                        $stmt->execute([':ecole' => $ecoleId, ':annee' => $anneeScolaireId]);
                    }

                    $eleves = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
                    // Determine a valid agent id for agent_saisie_id (agents.id required by FK)
                    $agentId = $user['reference_id'] ?? null;
                    if (empty($agentId)) {
                        // try to pick any agent from the same school
                        $agstmt = $db->prepare('SELECT id FROM agents WHERE ecole_id = :ecole LIMIT 1');
                        $agstmt->execute([':ecole' => $ecoleId]);
                        $found = $agstmt->fetch(\PDO::FETCH_ASSOC);
                        $agentId = $found['id'] ?? null;
                    }
                    // final fallback: use agent id 1 if exists (legacy/system)
                    if (empty($agentId)) {
                        $agentId = 1;
                    }

                    foreach ($eleves as $eleveId) {
                        // ensure compte_eleve exists
                        $cstmt = $db->prepare('SELECT id, solde_debiteur FROM comptes_eleves WHERE eleve_id = :eleve AND annee_scolaire_id = :annee LIMIT 1');
                        $cstmt->execute([':eleve' => $eleveId, ':annee' => $anneeScolaireId]);
                        $compte = $cstmt->fetch(\PDO::FETCH_ASSOC);
                        if (!$compte) {
                            $ist = $db->prepare('INSERT INTO comptes_eleves (eleve_id, annee_scolaire_id, solde_debiteur) VALUES (:eleve, :annee, 0.00)');
                            $ist->execute([':eleve' => $eleveId, ':annee' => $anneeScolaireId]);
                            $compteId = (int) $db->lastInsertId();
                            $currentBalance = 0.00;
                        } else {
                            $compteId = (int) $compte['id'];
                            $currentBalance = (float) $compte['solde_debiteur'];
                        }

                        // insert debit ecriture
                        $libelle = 'Facturation: ' . $typeFrais;
                        $est = $db->prepare('INSERT INTO ecritures_comptables_eleves (compte_eleve_id, frais_id, caisse_banque_id, type_mouvement, montant, reference_recu, libelle, agent_saisie_id) VALUES (:compte, :frais, NULL, :type, :montant, NULL, :libelle, :agent)');
                        $est->execute([
                            ':compte' => $compteId,
                            ':frais' => $insertId,
                            ':type' => 'DEBIT',
                            ':montant' => (float) $montantTotal,
                            ':libelle' => $libelle,
                            ':agent' => $agentId,
                        ]);

                        // update compte_eleve solde_debiteur
                        $newBalance = $currentBalance + (float) $montantTotal;
                        $ust = $db->prepare('UPDATE comptes_eleves SET solde_debiteur = :solde WHERE id = :id');
                        $ust->execute([':solde' => $newBalance, ':id' => $compteId]);
                    }

                    $_SESSION['frais_success'] = 'Frais créé et assigné aux élèves concernés.';
                    $this->redirect('/frais');
                    return;
                }

                $errors[] = 'Impossible de créer le frais. Veuillez réessayer.';
            }

            $_SESSION['frais_errors'] = $errors;
            $_SESSION['frais_old'] = $_POST;
        }

        $this->redirect('/frais/create');
    }

    private function getCurrencyOptions(): array
    {
        return [
            'USD' => 'USD - Dollar américain',
            'EUR' => 'EUR - Euro',
            'CDF' => 'CDF - Franc congolais',
            'XAF' => 'XAF - Franc CFA BEAC',
            'XOF' => 'XOF - Franc CFA BCEAO',
        ];
    }

    public function show(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/frais');
            return;
        }

        $fee = FraisScolaire::findById($id);
        if (!$fee) {
            $this->redirect('/error/notFound');
            return;
        }

        $this->view('frais/show', [
            'title' => APP_NAME . ' - Détail frais',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'fee' => $fee,
        ]);
    }

    public function edit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/frais');
            return;
        }

        $fee = FraisScolaire::findById($id);
        if (!$fee) {
            $this->redirect('/error/notFound');
            return;
        }

        $classes = $ecoleId > 0 ? Classe::getAllBySchool($ecoleId) : [];
        $years = $ecoleId > 0 ? AnneeScolaire::getAllBySchool($ecoleId) : [];
        $options = $ecoleId > 0 ? Option::getAll() : [];
        $sections = $ecoleId > 0 ? Section::getAll() : [];

        $this->view('frais/edit', [
            'title' => APP_NAME . ' - Modifier frais',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'fee' => $fee,
            'classes' => $classes,
            'years' => $years,
            'options' => $options,
            'sections' => $sections,
            'currencies' => $this->getCurrencyOptions(),
        ]);
    }

    public function update(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::MANAGEMENT_ROLES);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/frais');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        $id = (int) ($_POST['id'] ?? 0);
        $typeFrais = trim($_POST['type_frais'] ?? '');
        $montantTotal = trim($_POST['montant_total'] ?? '');
        $classeId = (int) ($_POST['classe_id'] ?? 0);
        $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);
        $scope = trim($_POST['scope'] ?? 'class');
        $scopeId = (int) ($_POST['scope_id'] ?? 0);
        $devise = trim($_POST['devise'] ?? '');

        $errors = [];
        if ($id <= 0) $errors[] = 'Frais invalide.';
        if ($typeFrais === '') $errors[] = 'Le type de frais est requis.';
        if ($montantTotal === '' || !is_numeric($montantTotal) || (float) $montantTotal < 0) $errors[] = 'Le montant total doit être un nombre positif.';

        $allowedScopes = ['class', 'option', 'section', 'school'];
        if (!in_array($scope, $allowedScopes, true)) $errors[] = 'Portée de frais invalide.';

        if ($scope === 'class' && $classeId <= 0) $errors[] = 'La classe est requise pour cette portée.';
        if ($scope !== 'class') $classeId = 0;

        if ($anneeScolaireId <= 0) {
            $active = AnneeScolaire::getActiveBySchool($ecoleId);
            if ($active) $anneeScolaireId = (int) $active['id'];
            else $errors[] = 'L\'année scolaire est requise.';
        }

        if ($devise === '') $errors[] = 'La devise est requise.';

        if (!empty($errors)) {
            $_SESSION['frais_errors'] = $errors;
            $_SESSION['frais_old'] = $_POST;
            $this->redirect('/frais/edit?id=' . $id);
            return;
        }

        $ok = FraisScolaire::update($id, [
            'classe_id' => $classeId ?: null,
            'type_frais' => $typeFrais,
            'montant_total' => (float) $montantTotal,
            'annee_scolaire_id' => $anneeScolaireId,
            'devise' => $devise,
            'scope' => $scope,
            'scope_id' => $scope === 'class' ? $classeId : ($scope === 'option' || $scope === 'section' ? $scopeId : null),
        ]);

        if ($ok) {
            $_SESSION['frais_success'] = 'Frais mis à jour avec succès.';
        } else {
            $_SESSION['frais_errors'] = ['Impossible de mettre à jour le frais.'];
        }

        $this->redirect('/frais');
    }
}
