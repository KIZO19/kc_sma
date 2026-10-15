<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\FraisScolaire;
use App\Models\Option;
use App\Models\Section;
use App\Models\User;
use PDO;

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
        if ($defaultYearId <= 0 && !empty($years)) {
            $defaultYearId = (int) ($years[0]['id'] ?? 0);
        }
        $school = $ecoleId > 0 ? \App\Models\Ecole::findById($ecoleId) : null;
        $schoolCurrency = $school['devise_principale'] ?? 'USD';
        $currencyOptions = $this->getCurrencyOptions();
        $options = $ecoleId > 0 ? Option::getAllForSchool($ecoleId) : [];
        $sections = $ecoleId > 0 ? Section::getAllForSchool($ecoleId) : [];
        $oldInput = $_SESSION['frais_old'] ?? [];
        unset($_SESSION['frais_old']);

        // generate a suggested encodage for the new fee (unique per school)
        $suggestedEncodage = null;
        try {
            $suggestedEncodage = $this->generateUniqueEncodage($ecoleId);
        } catch (\Throwable $e) {
            $suggestedEncodage = '';
        }

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
            'suggestedEncodage' => $suggestedEncodage,
        ]);
    }

    private function generateUniqueEncodage(int $ecoleId): string
    {
        $base = 'FRAIS-' . date('Y') . '-';
        $attempts = 0;
        while ($attempts < 20) {
            $candidate = $base . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            try {
                if (!FraisScolaire::existsEncodage($candidate, $ecoleId)) {
                    return $candidate;
                }
            } catch (\Throwable $e) {
                // if DB not ready, fallback to candidate without checking
                return $candidate;
            }
            $attempts++;
        }
        // fallback deterministic
        return $base . uniqid();
    }

    private function isScopeValidForSchool(int $ecoleId, string $scope, int $scopeId): bool
    {
        if ($ecoleId <= 0) {
            return false;
        }
        if ($scope === 'school') {
            return true;
        }
        if (!in_array($scope, ['class', 'option', 'section'], true) || $scopeId <= 0) {
            return false;
        }

        $column = [
            'class' => 'id',
            'option' => 'option_id',
            'section' => 'section_id',
        ][$scope];
        $stmt = Database::getConnection()->prepare(
            'SELECT 1 FROM classes WHERE ecole_id = :ecole_id AND ' . $column . ' = :scope_id LIMIT 1'
        );
        $stmt->execute([':ecole_id' => $ecoleId, ':scope_id' => $scopeId]);
        return (bool) $stmt->fetchColumn();
    }

    private function getStudentsForScope(PDO $db, int $ecoleId, int $yearId, string $scope, int $scopeId): array
    {
        $scopeCondition = [
            'class' => 'AND c.id = :scope_id',
            'option' => 'AND c.option_id = :scope_id',
            'section' => 'AND c.section_id = :scope_id',
            'school' => '',
        ][$scope];
        $sql = 'SELECT DISTINCT i.eleve_id
                FROM inscriptions i
                INNER JOIN classes c ON c.id = i.classe_id
                WHERE c.ecole_id = :ecole_id
                  AND i.annee_scolaire_id = :annee_scolaire_id ' . $scopeCondition;
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':ecole_id', $ecoleId, PDO::PARAM_INT);
        $stmt->bindValue(':annee_scolaire_id', $yearId, PDO::PARAM_INT);
        if ($scope !== 'school') {
            $stmt->bindValue(':scope_id', $scopeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function resolveAgentId(PDO $db, array $user, int $ecoleId): int
    {
        $referenceId = (int) ($user['reference_id'] ?? 0);
        if ($referenceId > 0) {
            $stmt = $db->prepare('SELECT id FROM agents WHERE id = :id AND ecole_id = :ecole_id LIMIT 1');
            $stmt->execute([':id' => $referenceId, ':ecole_id' => $ecoleId]);
            $agentId = (int) ($stmt->fetchColumn() ?: 0);
            if ($agentId > 0) {
                return $agentId;
            }
        }

        $stmt = $db->prepare('SELECT id FROM agents WHERE ecole_id = :ecole_id ORDER BY id ASC LIMIT 1');
        $stmt->execute([':ecole_id' => $ecoleId]);
        $agentId = (int) ($stmt->fetchColumn() ?: 0);
        if ($agentId <= 0) {
            throw new \RuntimeException('Aucun agent n’est associé à cette école pour enregistrer les écritures.');
        }
        return $agentId;
    }

    private function getOrCreateStudentAccount(PDO $db, int $eleveId, int $yearId): int
    {
        $stmt = $db->prepare(
            'SELECT id FROM comptes_eleves WHERE eleve_id = :eleve_id AND annee_scolaire_id = :annee_id LIMIT 1'
        );
        $stmt->execute([':eleve_id' => $eleveId, ':annee_id' => $yearId]);
        $accountId = (int) ($stmt->fetchColumn() ?: 0);
        if ($accountId > 0) {
            return $accountId;
        }

        $stmt = $db->prepare(
            'INSERT INTO comptes_eleves (eleve_id, annee_scolaire_id, solde_debiteur) VALUES (:eleve_id, :annee_id, 0.00)'
        );
        $stmt->execute([':eleve_id' => $eleveId, ':annee_id' => $yearId]);
        return (int) $db->lastInsertId();
    }

    private function synchronizeFeeAssignments(
        PDO $db,
        int $feeId,
        int $ecoleId,
        int $yearId,
        string $scope,
        int $scopeId,
        float $amount,
        string $currency,
        array $user
    ): void {
        $targetStudents = $this->getStudentsForScope($db, $ecoleId, $yearId, $scope, $scopeId);
        $targetSet = array_fill_keys($targetStudents, true);

        $stmt = $db->prepare(
            'SELECT eleve_id FROM dettes_eleves WHERE frais_id = :fee_id
             UNION
             SELECT ce.eleve_id FROM ecritures_comptables_eleves ece
             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
             WHERE ece.frais_id = :ledger_fee_id'
        );
        $stmt->execute([':fee_id' => $feeId, ':ledger_fee_id' => $feeId]);
        $students = array_fill_keys(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);
        foreach ($targetStudents as $studentId) {
            $students[$studentId] = true;
        }
        if (!$students) {
            return;
        }

        $stmt = $db->prepare(
            'SELECT ce.eleve_id, ce.id AS compte_id, ce.annee_scolaire_id,
                    SUM(CASE WHEN ece.type_mouvement = \'DEBIT\' THEN ece.montant ELSE 0 END) AS total_debit,
                    SUM(CASE WHEN ece.type_mouvement = \'CREDIT\' THEN ece.montant ELSE 0 END) AS total_credit,
                    SUM(CASE WHEN ece.type_mouvement = \'CREDIT\'
                                  AND COALESCE(ece.reference_recu, \'\') NOT LIKE \'REG-FRAIS-%\'
                             THEN ece.montant ELSE 0 END) AS total_paiements
             FROM ecritures_comptables_eleves ece
             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
             WHERE ece.frais_id = :fee_id
             GROUP BY ce.eleve_id, ce.id, ce.annee_scolaire_id'
        );
        $stmt->execute([':fee_id' => $feeId]);
        $ledgerByStudent = [];
        $students = array_fill_keys(array_map('intval', array_keys($students)), true);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entry) {
            $studentId = (int) $entry['eleve_id'];
            $accountId = (int) $entry['compte_id'];
            $net = (float) $entry['total_debit'] - (float) $entry['total_credit'];
            $ledgerByStudent[$studentId]['accounts'][$accountId] = [
                'year_id' => (int) $entry['annee_scolaire_id'],
                'net' => $net,
            ];
            $ledgerByStudent[$studentId]['credits'] =
                ($ledgerByStudent[$studentId]['credits'] ?? 0.0) + (float) $entry['total_paiements'];
        }

        $legacyPaidByStudent = [];
        $stmt = $db->prepare(
            'SELECT eleve_id, COALESCE(SUM(montant_paye), 0) AS total_paye
             FROM paiements_eleves WHERE frais_id = :fee_id GROUP BY eleve_id'
        );
        $stmt->execute([':fee_id' => $feeId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $payment) {
            $legacyPaidByStudent[(int) $payment['eleve_id']] = (float) $payment['total_paye'];
        }

        $agentId = $this->resolveAgentId($db, $user, $ecoleId);
        $insertEntry = $db->prepare(
            'INSERT INTO ecritures_comptables_eleves
                (compte_eleve_id, frais_id, caisse_banque_id, type_mouvement, montant, reference_recu, libelle, agent_saisie_id)
             VALUES (:compte_id, :fee_id, NULL, :type, :montant, :reference_recu, :libelle, :agent_id)'
        );
        $updateAccount = $db->prepare(
            'UPDATE comptes_eleves SET solde_debiteur = solde_debiteur + :delta WHERE id = :account_id'
        );

        foreach (array_keys($students) as $studentId) {
            $studentId = (int) $studentId;
            $isTargeted = isset($targetSet[$studentId]);
            $ledger = $ledgerByStudent[$studentId] ?? ['accounts' => [], 'credits' => 0.0];
            $paid = max(
                (float) $ledger['credits'],
                (float) ($legacyPaidByStudent[$studentId] ?? 0.0)
            );
            $targetDue = $isTargeted ? max(0.0, $amount - $paid) : 0.0;
            $targetAccounts = [];

            foreach ($ledger['accounts'] as $accountId => $account) {
                $targetAccounts[$accountId] = $account['net'] < 0 ? $account['net'] : 0.0;
            }

            if ($isTargeted) {
                $accountId = $this->getOrCreateStudentAccount($db, $studentId, $yearId);
                $targetAccounts[$accountId] = $targetDue;
            }

            foreach ($targetAccounts as $accountId => $targetNet) {
                $currentNet = (float) ($ledger['accounts'][$accountId]['net'] ?? 0.0);
                if ($targetNet <= 0 && $currentNet < 0) {
                    $targetNet = $currentNet;
                }
                $delta = round($targetNet - $currentNet, 2);
                if (abs($delta) < 0.005) {
                    continue;
                }

                $type = $delta > 0 ? 'DEBIT' : 'CREDIT';
                $insertEntry->execute([
                    ':compte_id' => $accountId,
                    ':fee_id' => $feeId,
                    ':type' => $type,
                    ':montant' => abs($delta),
                    ':reference_recu' => 'REG-FRAIS-' . $feeId . '-' . bin2hex(random_bytes(5)),
                    ':libelle' => 'Régularisation de portée : ' . ($scope === 'school' ? 'école entière' : $scope),
                    ':agent_id' => $agentId,
                ]);
                $updateAccount->execute([':delta' => $delta, ':account_id' => $accountId]);
            }

            $debtStmt = $db->prepare(
                'SELECT id FROM dettes_eleves WHERE eleve_id = :eleve_id AND frais_id = :fee_id ORDER BY id ASC'
            );
            $debtStmt->execute([':eleve_id' => $studentId, ':fee_id' => $feeId]);
            $debtIds = array_map('intval', $debtStmt->fetchAll(PDO::FETCH_COLUMN));
            $db->prepare(
                'UPDATE dettes_eleves SET montant_initial = 0, montant_restant = 0
                 WHERE eleve_id = :eleve_id AND frais_id = :fee_id'
            )->execute([':eleve_id' => $studentId, ':fee_id' => $feeId]);

            if ($debtIds) {
                $debtUpdate = $db->prepare(
                    'UPDATE dettes_eleves
                     SET annee_scolaire_id = :year_id, montant_initial = :amount,
                         montant_restant = :remaining, devise = :currency
                     WHERE id = :id'
                );
                $debtUpdate->execute([
                    ':year_id' => $yearId,
                    ':amount' => $isTargeted ? $amount : 0,
                    ':remaining' => $isTargeted ? $targetDue : 0,
                    ':currency' => $currency,
                    ':id' => $debtIds[0],
                ]);
            } elseif ($isTargeted) {
                $debtInsert = $db->prepare(
                    'INSERT INTO dettes_eleves
                        (eleve_id, frais_id, annee_scolaire_id, montant_initial, montant_restant, devise)
                     VALUES (:eleve_id, :fee_id, :year_id, :amount, :remaining, :currency)'
                );
                $debtInsert->execute([
                    ':eleve_id' => $studentId,
                    ':fee_id' => $feeId,
                    ':year_id' => $yearId,
                    ':amount' => $amount,
                    ':remaining' => $targetDue,
                    ':currency' => $currency,
                ]);
            }
        }
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
            $encodage = trim($_POST['encodage'] ?? '');
            $classeId = (int) ($_POST['classe_id'] ?? 0);
            $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);
            $scope = trim($_POST['scope'] ?? 'class');
            $scopeId = (int) ($_POST['scope_id'] ?? 0);
            $devise = trim($_POST['devise'] ?? '');
            if ($scope === 'class') {
                $scopeId = $classeId > 0 ? $classeId : $scopeId;
                $classeId = $scopeId;
            } else {
                $classeId = 0;
            }

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

            if (in_array($scope, $allowedScopes, true) && !$this->isScopeValidForSchool($ecoleId, $scope, $scopeId)) {
                $errors[] = 'La portée sélectionnée ne fait pas partie de votre école.';
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
                    $errors[] = 'L’année scolaire fournie n’appartient pas à votre école.';
                }
            }
            if ($devise === '') {
                $errors[] = 'La devise est requise.';
            } elseif (!array_key_exists($devise, $this->getCurrencyOptions())) {
                $errors[] = 'La devise sélectionnée est invalide.';
            }
            // encodage: if not provided, generate one automatically
            if ($encodage === '') {
                try {
                    $encodage = $this->generateUniqueEncodage($ecoleId);
                } catch (\Throwable $e) {
                    $encodage = 'FRAIS-' . date('Y') . '-' . uniqid();
                }
            }
            // basic sanitization: no spaces
            if (preg_match('/\s/', $encodage)) {
                $errors[] = 'L\'encodage ne doit pas contenir d\'espaces.';
            }

            if (empty($errors)) {
                // ensure encodage uniqueness
                try {
                    if (FraisScolaire::existsEncodage($encodage, $ecoleId)) {
                        $errors[] = 'Un frais avec cet encodage existe déjà pour votre école.';
                    }
                } catch (\Throwable $e) {
                    // log and show a neutral error so we don't give misleading migration instructions
                    error_log('FraisController::submit existsEncodage error: ' . $e->getMessage());
                    $errors[] = 'Erreur interne lors de la vérification de l\'encodage. Veuillez réessayer.';
                }
            }

            if (empty($errors)) {
                $db = Database::getConnection();
                try {
                    $db->beginTransaction();
                    $insertId = FraisScolaire::create([
                        'classe_id' => $scope === 'class' ? $classeId : null,
                        'type_frais' => $typeFrais,
                        'montant_total' => (float) $montantTotal,
                        'annee_scolaire_id' => $anneeScolaireId,
                        'devise' => $devise,
                        'scope' => $scope,
                        'scope_id' => in_array($scope, ['class', 'option', 'section'], true) ? $scopeId : null,
                        'encodage' => $encodage,
                        'ecole_id' => $ecoleId,
                    ]);
                    if (!$insertId) {
                        throw new \RuntimeException('La création du frais a échoué.');
                    }
                    $this->synchronizeFeeAssignments(
                        $db,
                        $insertId,
                        $ecoleId,
                        $anneeScolaireId,
                        $scope,
                        $scopeId,
                        (float) $montantTotal,
                        $devise,
                        $user
                    );
                    $db->commit();
                    $_SESSION['frais_success'] = 'Frais créé et attribué aux élèves inscrits concernés.';
                    $this->redirect('/frais');
                    return;
                } catch (\Throwable $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    error_log('FraisController::submit failed: ' . $e->getMessage());
                    $errors[] = 'Le frais n’a pas pu être créé. Vérifiez les données et réessayez.';
                }
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
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/frais');
            return;
        }

        $fee = FraisScolaire::findByIdAndSchool($id, $ecoleId);
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

        $fee = FraisScolaire::findByIdAndSchool($id, $ecoleId);
        if (!$fee) {
            $this->redirect('/error/notFound');
            return;
        }

        $classes = $ecoleId > 0 ? Classe::getAllBySchool($ecoleId) : [];
        $years = $ecoleId > 0 ? AnneeScolaire::getAllBySchool($ecoleId) : [];
        $options = $ecoleId > 0 ? Option::getAllForSchool($ecoleId) : [];
        $sections = $ecoleId > 0 ? Section::getAllForSchool($ecoleId) : [];

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
        $encodage = trim($_POST['encodage'] ?? '');
        $classeId = (int) ($_POST['classe_id'] ?? 0);
        $anneeScolaireId = (int) ($_POST['annee_scolaire_id'] ?? 0);
        $scope = trim($_POST['scope'] ?? 'class');
        $scopeId = (int) ($_POST['scope_id'] ?? 0);
        $devise = trim($_POST['devise'] ?? '');
        $fee = $id > 0 ? FraisScolaire::findByIdAndSchool($id, $ecoleId) : null;
        if (!$fee) {
            $_SESSION['frais_errors'] = ['Frais introuvable pour cette école.'];
            $this->redirect('/frais');
            return;
        }

        if ($scope === 'class') {
            $scopeId = $classeId > 0 ? $classeId : $scopeId;
            $classeId = $scopeId;
        } else {
            $classeId = 0;
        }

        $errors = [];
        if ($id <= 0) $errors[] = 'Frais invalide.';
        if ($typeFrais === '') $errors[] = 'Le type de frais est requis.';
        if ($montantTotal === '' || !is_numeric($montantTotal) || (float) $montantTotal < 0) $errors[] = 'Le montant total doit être un nombre positif.';

        $allowedScopes = ['class', 'option', 'section', 'school'];
        if (!in_array($scope, $allowedScopes, true)) $errors[] = 'Portée de frais invalide.';
        if (in_array($scope, $allowedScopes, true) && !$this->isScopeValidForSchool($ecoleId, $scope, $scopeId)) {
            $errors[] = 'La portée sélectionnée ne fait pas partie de votre école.';
        }

        if ($anneeScolaireId <= 0) {
            $active = AnneeScolaire::getActiveBySchool($ecoleId);
            if ($active) $anneeScolaireId = (int) $active['id'];
            else $errors[] = 'L\'année scolaire est requise.';
        }

        if ($anneeScolaireId > 0) {
            $yearRec = AnneeScolaire::findById($anneeScolaireId);
            if (!$yearRec || (int) ($yearRec['ecole_id'] ?? 0) !== $ecoleId) {
                $errors[] = 'L’année scolaire fournie n’appartient pas à votre école.';
            }
        }

        if ($devise === '' || !array_key_exists($devise, $this->getCurrencyOptions())) {
            $errors[] = 'La devise sélectionnée est invalide.';
        }

        if (!empty($errors)) {
            $_SESSION['frais_errors'] = $errors;
            $_SESSION['frais_old'] = $_POST;
            $this->redirect('/frais/edit?id=' . $id);
            return;
        }

        // validate encodage uniqueness on update
        if ($encodage === '') {
            $_SESSION['frais_errors'] = ['L\'encodage du frais est requis.'];
            $_SESSION['frais_old'] = $_POST;
            $this->redirect('/frais/edit?id=' . $id);
            return;
        }
        if (preg_match('/\s/', $encodage)) {
            $_SESSION['frais_errors'] = ['L\'encodage ne doit pas contenir d\'espaces.'];
            $_SESSION['frais_old'] = $_POST;
            $this->redirect('/frais/edit?id=' . $id);
            return;
        }
        try {
            if (FraisScolaire::existsEncodage($encodage, $ecoleId, $id)) {
                $_SESSION['frais_errors'] = ['Un frais avec cet encodage existe déjà pour votre école.'];
                $_SESSION['frais_old'] = $_POST;
                $this->redirect('/frais/edit?id=' . $id);
                return;
            }
        } catch (\Throwable $e) {
            error_log('FraisController::update existsEncodage error: ' . $e->getMessage());
            $_SESSION['frais_errors'] = ['Erreur interne lors de la vérification de l\'encodage. Veuillez réessayer.'];
            $_SESSION['frais_old'] = $_POST;
            $this->redirect('/frais/edit?id=' . $id);
            return;
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            $ok = FraisScolaire::update($id, [
                'classe_id' => $scope === 'class' ? $classeId : null,
                'type_frais' => $typeFrais,
                'montant_total' => (float) $montantTotal,
                'annee_scolaire_id' => $anneeScolaireId,
                'devise' => $devise,
                'scope' => $scope,
                'scope_id' => in_array($scope, ['class', 'option', 'section'], true) ? $scopeId : null,
                'ecole_id' => $ecoleId,
                'encodage' => $encodage,
            ]);
            if (!$ok) {
                throw new \RuntimeException('La mise à jour du frais a échoué.');
            }
            $this->synchronizeFeeAssignments(
                $db,
                $id,
                $ecoleId,
                $anneeScolaireId,
                $scope,
                $scopeId,
                (float) $montantTotal,
                $devise,
                $user
            );
            $db->commit();
            $_SESSION['frais_success'] = 'Frais mis à jour avec succès.';
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('FraisController::update failed: ' . $e->getMessage());
            $_SESSION['frais_errors'] = ['Impossible de mettre à jour le frais.'];
        }

        $this->redirect('/frais');
    }

    public function destroy(): void
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
        if ($id <= 0 || $ecoleId <= 0) {
            $_SESSION['frais_errors'] = ['Frais invalide ou école introuvable.'];
            $this->redirect('/frais');
            return;
        }

        if (!FraisScolaire::findByIdAndSchool($id, $ecoleId)) {
            $_SESSION['frais_errors'] = ['Frais introuvable pour cette école.'];
            $this->redirect('/frais');
            return;
        }

        try {
            if (!FraisScolaire::deleteByIdAndSchool($id, $ecoleId)) {
                $_SESSION['frais_errors'] = ['Le frais n’a pas pu être supprimé.'];
                $this->redirect('/frais');
                return;
            }
            $_SESSION['frais_success'] = 'Frais supprimé avec succès.';
        } catch (\PDOException $e) {
            error_log('FraisController::destroy failed: ' . $e->getMessage());
            if ($e->getCode() === '23000') {
                $_SESSION['frais_errors'] = ['Ce frais ne peut pas être supprimé, car il est lié à des paiements ou à des écritures comptables.'];
            } else {
                $_SESSION['frais_errors'] = ['Une erreur est survenue lors de la suppression du frais. Veuillez réessayer.'];
            }
        }

        $this->redirect('/frais');
    }
}
