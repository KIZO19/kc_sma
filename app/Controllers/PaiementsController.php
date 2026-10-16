<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\DetteEleve;
use App\Models\Eleve;
use App\Models\FraisScolaire;
use App\Models\Classe;
use App\Models\User;
use App\Models\Eleve as EleveModel;
use App\Models\PaiementAutorisation;
use App\Services\PaymentParentNotifier;

class PaiementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $eleveId = !empty($_GET['eleve_id']) ? (int) $_GET['eleve_id'] : null;
        $fraisId = !empty($_GET['frais_id']) ? (int) $_GET['frais_id'] : null;
        if ($eleveId && ($role !== 'super_admin') && (int) ($user['ecole_id'] ?? 0) > 0) {
            if (!Eleve::findByIdAndSchool($eleveId, (int) $user['ecole_id'])) {
                $eleveId = null;
            }
        }

        $payments = $this->fetchPaymentsForUser($user, 0, $eleveId, $fraisId);
        $eleveFilter = $eleveId ? Eleve::findById($eleveId) : null;
        $fraisFilter = $fraisId ? FraisScolaire::findById($fraisId) : null;
        $totalPaid = $eleveId !== null ? $this->fetchTotalPaidForEleve($user, $eleveId) : null;
        $totalPaidByCurrency = $eleveId !== null ? $this->fetchTotalPaidByCurrency($user, $eleveId) : [];
        $totalDebtByCurrency = $eleveId !== null ? DetteEleve::computeOutstandingFromApplicableFees($eleveId) : [];
        $totalDebt = $eleveId !== null ? array_sum($totalDebtByCurrency) : null;

        $userSchool = (int) ($user['ecole_id'] ?? 0);
        // Show only enrolled (validated) students who still have outstanding debts for selection
        $db = Database::getConnection();
        if ($userSchool > 0) {
            $stmt = $db->prepare(
                'SELECT DISTINCT e.* FROM eleves e '
                . 'INNER JOIN dettes_eleves d ON d.eleve_id = e.id '
                . 'LEFT JOIN inscriptions i ON i.eleve_id = e.id '
                . 'LEFT JOIN classes c ON i.classe_id = c.id '
                . "WHERE d.montant_restant > 0 AND e.statut_eleve = :statut AND (e.ecole_id = :ecole_id OR c.ecole_id = :ecole_id) "
                . 'ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC'
            );
            $stmt->execute([':statut' => 'actif', ':ecole_id' => $userSchool]);
            $students = $stmt->fetchAll();
        } else {
            $stmt = $db->prepare('SELECT DISTINCT e.* FROM eleves e INNER JOIN dettes_eleves d ON d.eleve_id = e.id WHERE d.montant_restant > 0 AND e.statut_eleve = :statut ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC');
            $stmt->execute([':statut' => 'actif']);
            $students = $stmt->fetchAll();
        }
        $fees = $userSchool > 0 ? FraisScolaire::getAllBySchool($userSchool) : [];

        $authorizedFeeIds = in_array($role, ['super_admin', 'comptable_école'], true)
            ? []
            : PaiementAutorisation::getAuthorizedFeeIdsForUser($user);

        $this->view('paiements/index', [
            'title' => APP_NAME . ' - Paiements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'payments' => $payments,
            'eleveFilter' => $eleveFilter,
            'fraisFilter' => $fraisFilter,
            'eleveId' => $eleveId,
            'fraisId' => $fraisId,
            'students' => $students,
            'fees' => $fees,
            'totalPaid' => $totalPaid,
            'totalPaidByCurrency' => $totalPaidByCurrency,
            'totalDebt' => $totalDebt,
            'totalDebtByCurrency' => $totalDebtByCurrency,
            'canManageAccounting' => ($role === 'comptable_école' || $role === 'super_admin' || !empty($authorizedFeeIds)),
        ]);
    }

    public function export(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $eleveId = !empty($_GET['eleve_id']) ? (int) $_GET['eleve_id'] : null;
        $fraisId = !empty($_GET['frais_id']) ? (int) $_GET['frais_id'] : null;
        if ($eleveId && ($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            if (!Eleve::findByIdAndSchool($eleveId, (int) $user['ecole_id'])) {
                $eleveId = null;
            }
        }

        $format = strtolower(trim($_GET['format'] ?? 'csv'));
        $payments = $this->fetchPaymentsForUser($user, 0, $eleveId, $fraisId);

        if ($format === 'csv' || $format === 'excel') {
            // output CSV (works with Excel). For `excel` we set XLS content-disposition for convenience.
            $filename = 'paiements_' . date('Ymd_His') . ($format === 'excel' ? '.xls' : '.csv');
            header('Content-Type: ' . ($format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv') . '; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            // BOM for Excel compatibility with UTF-8
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Réf reçu', 'Élève', 'Date', 'Montant', 'Perçu par', 'Fonction', 'Libellé']);
            foreach ($payments as $p) {
                $name = trim(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['postnom'] ?? ''));
                fputcsv($out, [
                    $p['reference_recu'] ?? '',
                    $name,
                    $p['date_operation'] ?? '',
                    $p['montant_affiche'] ?? number_format((float) ($p['montant'] ?? 0), 2),
                    $p['agent_nom'] ?? '',
                    $p['agent_fonction'] ?? '',
                    $p['libelle'] ?? '',
                ]);
            }
            fclose($out);
            exit;
        }

        if ($format === 'pdf') {
            // If dompdf is available, render a PDF; otherwise render printable HTML fallback
            if (class_exists('\Dompdf\Dompdf')) {
                $html = $this->renderViewToString('paiements/export_pdf', ['payments' => $payments]);
                $dompdfClass = '\\Dompdf\\Dompdf';
                $dompdf = new $dompdfClass();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $filename = 'paiements_' . date('Ymd_His') . '.pdf';
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo $dompdf->output();
                exit;
            }

            // Fallback: show printable HTML page that user can print to PDF
            $this->view('paiements/export_pdf', [
                'payments' => $payments,
            ]);
            return;
        }

        // unknown format => redirect back
        $this->redirect('/paiements');
    }

    public function listJson(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $eleveId = !empty($_GET['eleve_id']) ? (int) $_GET['eleve_id'] : null;
        $fraisId = !empty($_GET['frais_id']) ? (int) $_GET['frais_id'] : null;
        if ($eleveId && ($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            if (!Eleve::findByIdAndSchool($eleveId, (int) $user['ecole_id'])) {
                $eleveId = null;
            }
        }
        $payments = $this->fetchPaymentsForUser($user, 0, $eleveId, $fraisId);

        $totalPaid = $eleveId !== null ? $this->fetchTotalPaidForEleve($user, $eleveId) : null;
        $totalPaidByCurrency = $eleveId !== null ? $this->fetchTotalPaidByCurrency($user, $eleveId) : [];
        $totalDebtByCurrency = $eleveId !== null ? DetteEleve::computeOutstandingFromApplicableFees($eleveId) : [];
        $totalDebt = $eleveId !== null ? array_sum($totalDebtByCurrency) : null;

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['payments' => $payments, 'totalPaid' => $totalPaid, 'totalPaidByCurrency' => $totalPaidByCurrency, 'totalDebt' => $totalDebt, 'totalDebtByCurrency' => $totalDebtByCurrency], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fetchTotalPaidForEleve(array $user, int $eleveId): float
    {
        $db = \App\Core\Database::getConnection();
        $sql = 'SELECT SUM(ece.montant) AS total_paid FROM ecritures_comptables_eleves ece '
            . 'INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id '
            . 'INNER JOIN eleves el ON ce.eleve_id = el.id '
            . 'WHERE ece.type_mouvement = :type AND ce.eleve_id = :eleveId ';
        $params = [':type' => 'CREDIT', ':eleveId' => $eleveId];

        if (($user['role'] ?? '') !== 'super_admin') {
            $sql .= 'AND (
                el.ecole_id = :ecole
                OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole)
                OR EXISTS (SELECT 1 FROM frais_scolaires fs2 INNER JOIN classes c2 ON c2.id = fs2.classe_id WHERE fs2.id = ece.frais_id AND c2.ecole_id = :ecole)
            ) ';
            $params[':ecole'] = (int) ($user['ecole_id'] ?? 0);
        }

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        $paid1 = (float) ($stmt->fetchColumn() ?: 0);

        try {
            $legacySql = 'SELECT SUM(pe.montant_paye) AS total_paid FROM paiements_eleves pe '
                . 'INNER JOIN eleves el ON pe.eleve_id = el.id '
                . 'WHERE pe.eleve_id = :eleveId ';
            $legacyParams = [':eleveId' => $eleveId];
            if (($user['role'] ?? '') !== 'super_admin') {
                $legacySql .= 'AND (
                    el.ecole_id = :ecole
                    OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole)
                    OR EXISTS (SELECT 1 FROM frais_scolaires fs2 INNER JOIN classes c2 ON c2.id = fs2.classe_id WHERE fs2.id = pe.frais_id AND c2.ecole_id = :ecole)
                ) ';
                $legacyParams[':ecole'] = (int) ($user['ecole_id'] ?? 0);
            }
            $lstmt = $db->prepare($legacySql);
            foreach ($legacyParams as $key => $value) {
                $lstmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }
            $lstmt->execute();
            $paid2 = (float) ($lstmt->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            $paid2 = 0.0;
        }

        // The application historically used two payment tables. Prefer the larger
        // total so a payment mirrored in both tables is not counted twice.
        return max($paid1, $paid2);
    }

    private function fetchTotalPaidByCurrency(array $user, int $eleveId): array
    {
        $totals = [];
        foreach ($this->fetchPaymentsForUser($user, 0, $eleveId) as $payment) {
            $currency = strtoupper(trim($payment['transaction_devise'] ?? $payment['frais_devise'] ?? 'USD')) ?: 'USD';
            if (!isset($totals[$currency])) {
                $totals[$currency] = 0.0;
            }
            $totals[$currency] += (float) ($payment['montant'] ?? 0);
        }
        ksort($totals);
        return $totals;
    }

    private function renderViewToString(string $viewPath, array $params = []): string
    {
        extract($params, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../Views/' . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $viewPath) . '.php';
        return ob_get_clean() ?: '';
    }

    private function fetchPaymentsForUser(array $user, int $limit = 0, ?int $eleveId = null, ?int $fraisId = null): array
    {
        $db = \App\Core\Database::getConnection();
        $sql = 'SELECT ece.id, ece.frais_id, ece.reference_recu, ece.date_operation, ece.montant, ece.libelle, ce.eleve_id, el.nom, el.postnom, el.prenom, COALESCE(NULLIF(us.nom_complet, \'\'), NULLIF(CONCAT_WS(\' \', a.nom, a.postnom, a.prenom), \'\'), u.nom_complet, \'Agent non identifié\') AS agent_nom, COALESCE(ra.titre_role, us.role, u.role, \'Agent\') AS agent_fonction, fs.devise AS frais_devise, COALESCE(fs.devise, ecole.devise_principale, \'USD\') AS transaction_devise '
            . 'FROM ecritures_comptables_eleves ece '
            . 'INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id '
            . 'INNER JOIN eleves el ON ce.eleve_id = el.id '
            . 'LEFT JOIN ecoles ecole ON el.ecole_id = ecole.id '
            . 'LEFT JOIN frais_scolaires fs ON ece.frais_id = fs.id '
            . 'LEFT JOIN agents a ON ece.agent_saisie_id = a.id '
            . 'LEFT JOIN roles_administration ra ON a.role_id = ra.id '
            . 'LEFT JOIN utilisateurs us ON ece.utilisateur_saisie_id = us.id '
            . 'LEFT JOIN (SELECT reference_id, MAX(nom_complet) AS nom_complet, MAX(role) AS role FROM utilisateurs WHERE role NOT IN (\'eleve_ecole\', \'parent_ecole\') GROUP BY reference_id) u ON ece.agent_saisie_id = u.reference_id '
            . 'WHERE ece.type_mouvement = :type ';

        $params = [':type' => 'CREDIT'];
        if (($user['role'] ?? '') !== 'super_admin') {
            $sql .= 'AND (
                el.ecole_id = :ecole
                OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole)
                OR EXISTS (SELECT 1 FROM frais_scolaires fs2 INNER JOIN classes c2 ON c2.id = fs2.classe_id WHERE fs2.id = ece.frais_id AND c2.ecole_id = :ecole)
            ) ';
            $params[':ecole'] = (int) ($user['ecole_id'] ?? 0);
        }
        if ($eleveId !== null) {
            $sql .= 'AND ce.eleve_id = :eleveId ';
            $params[':eleveId'] = $eleveId;
        }
        if ($fraisId !== null) {
            $sql .= 'AND ece.frais_id = :fraisId ';
            $params[':fraisId'] = $fraisId;
        }

        $sql .= 'ORDER BY ece.date_operation DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT :limit';
        }
        $stmt = $db->prepare($sql);
        if ($limit > 0) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Also include legacy paiements_eleves table if present
        try {
            $legacySql = 'SELECT pe.id AS legacy_id, pe.eleve_id, pe.frais_id, pe.montant_paye AS montant, pe.date_paiement AS date_operation, fs.type_frais AS libelle_frais, fs.devise AS frais_devise, COALESCE(fs.devise, ecole.devise_principale, \'USD\') AS transaction_devise, el.nom, el.postnom, el.prenom '
                . 'FROM paiements_eleves pe '
                . 'INNER JOIN eleves el ON pe.eleve_id = el.id '
                . 'LEFT JOIN ecoles ecole ON el.ecole_id = ecole.id '
                . 'LEFT JOIN frais_scolaires fs ON pe.frais_id = fs.id '
                . 'WHERE 1=1 ';

            $legacyParams = [];
            if (($user['role'] ?? '') !== 'super_admin') {
                $legacySql .= 'AND (
                    el.ecole_id = :ecole
                    OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole)
                    OR EXISTS (SELECT 1 FROM frais_scolaires fs2 INNER JOIN classes c2 ON c2.id = fs2.classe_id WHERE fs2.id = pe.frais_id AND c2.ecole_id = :ecole)
                ) ';
                $legacyParams[':ecole'] = (int) ($user['ecole_id'] ?? 0);
            }
            if ($eleveId !== null) {
                $legacySql .= 'AND el.id = :eleveId ';
                $legacyParams[':eleveId'] = $eleveId;
            }
            $legacySql .= 'ORDER BY pe.date_paiement DESC';
            if ($limit > 0) {
                $legacySql .= ' LIMIT :limit';
            }
            $lstmt = $db->prepare($legacySql);
            if ($limit > 0) {
                $lstmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            }
            foreach ($legacyParams as $k => $v) {
                $lstmt->bindValue($k, $v, is_int($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }
            $lstmt->execute();
            $legacy = $lstmt->fetchAll(\PDO::FETCH_ASSOC);

            // Normalize legacy rows to match ecritures structure
            foreach ($legacy as $l) {
                $duplicate = false;
                foreach ($records as $record) {
                    if ((int) ($record['eleve_id'] ?? 0) !== (int) ($l['eleve_id'] ?? 0)
                        || (int) ($record['frais_id'] ?? 0) !== (int) ($l['frais_id'] ?? 0)
                        || abs((float) ($record['montant'] ?? 0) - (float) ($l['montant'] ?? 0)) > 0.001) {
                        continue;
                    }
                    $recordTime = strtotime($record['date_operation'] ?? '');
                    $legacyTime = strtotime($l['date_operation'] ?? '');
                    if ($recordTime !== false && $legacyTime !== false && abs($recordTime - $legacyTime) <= 2) {
                        $duplicate = true;
                        break;
                    }
                }
                if ($duplicate) {
                    continue;
                }
                $records[] = [
                    'id' => 'legacy-' . ($l['legacy_id'] ?? ''),
                    'reference_recu' => null,
                    'date_operation' => $l['date_operation'] ?? null,
                    'montant' => $l['montant'] ?? 0,
                    'libelle' => $l['libelle_frais'] ?? 'Paiement',
                    'eleve_id' => $l['eleve_id'] ?? null,
                    'frais_id' => $l['frais_id'] ?? null,
                    'nom' => $l['nom'] ?? null,
                    'postnom' => $l['postnom'] ?? null,
                    'prenom' => $l['prenom'] ?? null,
                    'agent_nom' => null,
                    'agent_fonction' => null,
                    'frais_devise' => $l['frais_devise'] ?? 'USD',
                ];
            }
        } catch (\Throwable $e) {
            // ignore if legacy table not present or query fails
        }

        // Sort merged records by date_operation desc and limit
        usort($records, function ($a, $b) {
            $ta = strtotime($a['date_operation'] ?? '1970-01-01 00:00:00');
            $tb = strtotime($b['date_operation'] ?? '1970-01-01 00:00:00');
            return $tb <=> $ta;
        });

        foreach ($records as &$rec) {
            $currency = strtoupper(trim($rec['transaction_devise'] ?? $rec['frais_devise'] ?? 'USD')) ?: 'USD';
            $rec['transaction_devise'] = $currency;
            $rec['montant_usd_equivalent'] = $currency !== 'USD'
                ? \App\Models\Devise::convertToUsd((float) ($rec['montant'] ?? 0), $currency)
                : null;
            $rec['montant_affiche'] = \App\Models\Devise::formatAmountWithCurrency(
                (float) ($rec['montant'] ?? 0),
                $currency,
                $rec['montant_usd_equivalent']
            );
        }
        unset($rec);

        if ($limit > 0) {
            return array_slice($records, 0, $limit);
        }

        return $records;
    }

    private function ensureStudentAccount(\PDO $db, int $eleveId, int $ecoleId): int
    {
        $compte = $ecoleId > 0 ? EleveModel::getAccountForSchool($eleveId, $ecoleId) : EleveModel::getAccount($eleveId);
        if ($compte) {
            return (int) $compte['id'];
        }

        $stmtYear = $db->prepare('SELECT id FROM annees_scolaires WHERE est_active = 1 AND ecole_id = :ecole_id LIMIT 1');
        $stmtYear->execute([':ecole_id' => $ecoleId]);
        $year = $stmtYear->fetch();
        $anneeId = $year['id'] ?? 1;

        $ins = $db->prepare('INSERT INTO comptes_eleves (eleve_id, annee_scolaire_id, solde_debiteur) VALUES (:eleve, :annee, 0)');
        $ins->execute([':eleve' => $eleveId, ':annee' => $anneeId]);
        return (int) $db->lastInsertId();
    }

    private function persistPaymentEntry(\PDO $db, int $eleveId, ?int $fraisId, float $montant, string $libelle, int $agentId, string $reference, array $fee, int $userSchool, int $userId): int
    {
        $db->beginTransaction();
        try {
            $studentLock = $db->prepare('SELECT id, ecole_id FROM eleves WHERE id = :id FOR UPDATE');
            $studentLock->execute([':id' => $eleveId]);
            $student = $studentLock->fetch(\PDO::FETCH_ASSOC);
            if (!$student || $fraisId === null) {
                throw new \RuntimeException('Élève ou frais scolaire invalide.');
            }

            $enrollment = DetteEleve::getLatestEnrollmentForEleve($eleveId);
            $schoolId = $userSchool > 0 ? $userSchool : (int) ($student['ecole_id'] ?? 0);
            if ($schoolId <= 0) {
                $schoolId = (int) ($enrollment['ecole_id'] ?? $fee['ecole_id'] ?? 0);
            }
            $detteStmt = $db->prepare(
                'SELECT * FROM dettes_eleves
                 WHERE eleve_id = :eleve AND frais_id = :frais
                 ORDER BY id ASC LIMIT 1 FOR UPDATE'
            );
            $detteStmt->execute([':eleve' => $eleveId, ':frais' => $fraisId]);
            $dette = $detteStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$dette) {
                if (!DetteEleve::isFeeApplicableToEnrollment($fee, $enrollment)) {
                    throw new \RuntimeException('Ce frais ne s’applique pas à la classe, à l’option ou à la section actuelle de cet élève.');
                }
                $feeTotal = (float) ($fee['montant_total'] ?? 0);
                if ($feeTotal <= 0) {
                    throw new \RuntimeException('Le montant du frais sélectionné est invalide.');
                }
                $createDebt = $db->prepare(
                    'INSERT INTO dettes_eleves (eleve_id, frais_id, annee_scolaire_id, montant_initial, montant_restant, devise)
                     VALUES (:eleve, :frais, :annee, :initial, :restant, :devise)'
                );
                $createDebt->execute([
                    ':eleve' => $eleveId,
                    ':frais' => $fraisId,
                    ':annee' => (int) ($fee['annee_scolaire_id'] ?? 0),
                    ':initial' => $feeTotal,
                    ':restant' => $feeTotal,
                    ':devise' => (string) ($fee['devise'] ?? 'USD'),
                ]);
                $dette = [
                    'id' => (int) $db->lastInsertId(),
                    'montant_initial' => $feeTotal,
                    'montant_restant' => $feeTotal,
                ];
            }

            $remaining = (float) ($dette['montant_restant'] ?? 0);
            $initial = (float) ($dette['montant_initial'] ?? 0);
            if ($montant <= 0 || $remaining <= 0 || $montant > $remaining || $montant > $initial) {
                throw new \RuntimeException('Le montant du paiement dépasse le reste à payer pour ce frais.');
            }

            $compteId = $this->ensureStudentAccount($db, $eleveId, $schoolId);

            $agentFkId = null;
            if ($agentId > 0) {
                $agentCheck = $db->prepare('SELECT id FROM agents WHERE id = :id AND ecole_id = :ecole LIMIT 1');
                $agentCheck->execute([':id' => $agentId, ':ecole' => $schoolId]);
                if ($agentCheck->fetch()) {
                    $agentFkId = $agentId;
                }
            }

            $stmt = $db->prepare('INSERT INTO ecritures_comptables_eleves (compte_eleve_id, frais_id, caisse_banque_id, type_mouvement, montant, reference_recu, libelle, agent_saisie_id, utilisateur_saisie_id) VALUES (:compte, :frais, NULL, :type, :montant, :ref, :libelle, :agent, :utilisateur)');
            $stmt->execute([
                ':compte' => $compteId,
                ':frais' => $fraisId,
                ':type' => 'CREDIT',
                ':montant' => $montant,
                ':ref' => $reference,
                ':libelle' => $libelle,
                ':agent' => $agentFkId,
                ':utilisateur' => $userId > 0 ? $userId : null,
            ]);
            $ecritureId = (int) $db->lastInsertId();

            $legacyStmt = $db->prepare('INSERT INTO paiements_eleves (eleve_id, frais_id, montant_paye, date_paiement) VALUES (:eleve, :frais, :montant, :date_paiement)');
            $legacyStmt->execute([
                ':eleve' => $eleveId,
                ':frais' => $fraisId,
                ':montant' => $montant,
                ':date_paiement' => date('Y-m-d H:i:s'),
            ]);

            $upd = $db->prepare('UPDATE comptes_eleves SET solde_debiteur = solde_debiteur - :montant WHERE id = :id');
            $upd->execute([':montant' => $montant, ':id' => $compteId]);

            $detteUpdate = $db->prepare('UPDATE dettes_eleves SET montant_restant = GREATEST(0, montant_restant - :montant) WHERE id = :id');
            $detteUpdate->execute([':montant' => $montant, ':id' => $dette['id']]);

            $db->commit();
            return $ecritureId;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if ($e instanceof \PDOException && (stripos($e->getMessage(), 'duplicate entry') !== false || stripos($e->getMessage(), 'reference_recu') !== false)) {
                throw new \RuntimeException('Le numéro de reçu est déjà utilisé. Veuillez réessayer.', 0, $e);
            }
            throw $e;
        }
    }

    private function generateUniqueReceiptReference(\PDO $db): string
    {
        $base = 'REC-' . date('YmdHis');
        $attempts = 0;

        while ($attempts < 10) {
            $reference = $base . '-' . random_int(100, 999);
            $stmt = $db->prepare('SELECT id FROM ecritures_comptables_eleves WHERE reference_recu = :reference LIMIT 1');
            $stmt->execute([':reference' => $reference]);
            if (!$stmt->fetchColumn()) {
                return $reference;
            }
            $attempts++;
        }

        throw new \RuntimeException('Impossible de générer un numéro de reçu unique.');
    }

    private function canCreatePayment(array $user, ?int $fraisId = null): bool
    {
        $role = (string) ($user['role'] ?? '');
        if (in_array($role, ['super_admin', 'comptable_école'], true)) {
            return true;
        }

        return PaiementAutorisation::canUserRecordFee($user, $fraisId);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école', 'sec_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $fraisIdFromRequest = !empty($_GET['frais_id']) ? (int) $_GET['frais_id'] : null;
        if (!$this->canCreatePayment($user, $fraisIdFromRequest)) {
            $_SESSION['flash_error'] = 'Vous n’avez pas l’autorisation de saisir un paiement pour ce frais.';
            $this->redirect('/paiements');
            return;
        }
        $modules = $this->getModulesForRole($role);

        $eleveId = (int) ($_GET['eleve_id'] ?? $_GET['id'] ?? 0);
        // Access is restricted by delegated fee authorization, not by a blanket "comptable-only" rule.
        if ($eleveId > 0 && !$this->canCreatePayment($user, $fraisIdFromRequest)) {
            $_SESSION['flash_error'] = 'Ce rôle n’a pas encore reçu l’autorisation pour ce type de paiement.';
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }
        $eleve = null;
        $students = [];
        $feeDebtDetails = [];
        if ($eleveId > 0) {
            if (($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
                $eleve = Eleve::findByIdAndSchool($eleveId, (int) $user['ecole_id']);
            } else {
                $eleve = Eleve::findById($eleveId);
            }
            if (!$eleve) {
                header('Location: ' . BASE_URL . '/error/notFound');
                exit;
            }
            $compte = Eleve::getAccount($eleveId);
            // Include recorded balances and any applicable fee not yet materialized as a debt row.
            try {
                $feeDebtDetails = DetteEleve::getOutstandingFeeDetails($eleveId);
                $totalDebtByCurrency = [];
                foreach ($feeDebtDetails as $feeDebt) {
                    $currency = strtoupper(trim((string) ($feeDebt['devise'] ?? 'USD'))) ?: 'USD';
                    $totalDebtByCurrency[$currency] = ($totalDebtByCurrency[$currency] ?? 0.0)
                        + max(0.0, (float) ($feeDebt['montant_restant'] ?? 0));
                }
                $totalDebt = array_sum($totalDebtByCurrency);
            } catch (\Throwable $e) {
                $totalDebt = null;
                $totalDebtByCurrency = [];
            }
        } else {
            // No specific eleve requested: show all students from the school, but visually separate those already settled.
            $userSchool = (int) ($user['ecole_id'] ?? 0);
            $allStudents = $userSchool > 0 ? Eleve::getAllBySchool($userSchool) : Eleve::getAll();
            $students = [];
            foreach ($allStudents as $student) {
                $studentId = (int) ($student['id'] ?? 0);
                if ($studentId <= 0) {
                    continue;
                }

                try {
                    $feeDebtDetails = DetteEleve::getOutstandingFeeDetails($studentId);
                    $debtByCurrency = [];
                    foreach ($feeDebtDetails as $feeDebt) {
                        $currency = strtoupper(trim((string) ($feeDebt['devise'] ?? 'USD'))) ?: 'USD';
                        $debtByCurrency[$currency] = ($debtByCurrency[$currency] ?? 0.0)
                            + max(0.0, (float) ($feeDebt['montant_restant'] ?? 0));
                    }
                    $remaining = (float) array_sum($debtByCurrency);
                    $student['remaining_debt_by_currency'] = $debtByCurrency;
                    $student['outstanding_fee_details'] = $feeDebtDetails;
                    $student['remaining_debt'] = $remaining;
                    $student['is_settled'] = $remaining <= 0.0;
                    $students[] = $student;
                } catch (\Throwable $e) {
                    $dette = DetteEleve::findByEleve($studentId);
                    $debtByCurrency = [];
                    foreach ($dette as $row) {
                        $currency = strtoupper(trim((string) ($row['devise'] ?? 'USD'))) ?: 'USD';
                        $debtByCurrency[$currency] = ($debtByCurrency[$currency] ?? 0.0)
                            + max(0.0, (float) ($row['montant_restant'] ?? 0));
                    }
                    $remaining = (float) array_sum($debtByCurrency);
                    $student['remaining_debt_by_currency'] = $debtByCurrency;
                    $student['remaining_debt'] = $remaining;
                    $student['is_settled'] = $remaining <= 0.0;
                    $students[] = $student;
                }
            }
            $compte = null;
        }

        $db = Database::getConnection();
        $paymentFormToken = bin2hex(random_bytes(16));
        $_SESSION['payment_form_token'] = $paymentFormToken;

        $authorizedFeeIds = in_array($role, ['super_admin', 'comptable_école'], true)
            ? []
            : PaiementAutorisation::getAuthorizedFeeIdsForUser($user);
        if (!empty($authorizedFeeIds)) {
            foreach ($students as &$student) {
                $student['outstanding_fee_details'] = array_values(array_filter(
                    $student['outstanding_fee_details'] ?? [],
                    static fn (array $feeDebt): bool => in_array((int) ($feeDebt['frais_id'] ?? 0), $authorizedFeeIds, true)
                ));
                $student['remaining_debt_by_currency'] = [];
                foreach ($student['outstanding_fee_details'] as $feeDebt) {
                    $currency = strtoupper(trim((string) ($feeDebt['devise'] ?? 'USD'))) ?: 'USD';
                    $student['remaining_debt_by_currency'][$currency] =
                        ($student['remaining_debt_by_currency'][$currency] ?? 0.0)
                        + max(0.0, (float) ($feeDebt['montant_restant'] ?? 0));
                }
                $student['remaining_debt'] = array_sum($student['remaining_debt_by_currency']);
                $student['is_settled'] = $student['remaining_debt'] <= 0.0;
            }
            unset($student);
        }

        // Fetch fees applicable to populate motif picklist
        $fees = [];
        try {
            $schoolIdForFees = (int) ($user['ecole_id'] ?? 0);
            if ($eleveId > 0) {
                $allFees = \App\Models\FraisScolaire::getAllBySchool($schoolIdForFees);
                $feeDebtsById = [];
                foreach ($feeDebtDetails as $feeDebt) {
                    $feeDebtsById[(int) ($feeDebt['frais_id'] ?? 0)] = $feeDebt;
                }
                foreach ($allFees as $f) {
                    $feeId = (int) ($f['id'] ?? 0);
                    if (!isset($feeDebtsById[$feeId])) {
                        continue;
                    }
                    if (!empty($authorizedFeeIds) && !in_array($feeId, $authorizedFeeIds, true)) {
                        continue;
                    }
                    $f['remaining'] = (float) $feeDebtsById[$feeId]['montant_restant'];
                    $fees[] = $f;
                }
            } else {
                $allFees = \App\Models\FraisScolaire::getAllBySchool($schoolIdForFees);
                foreach ($allFees as $f) {
                    $feeId = (int) ($f['id'] ?? 0);
                    if (!empty($authorizedFeeIds) && !in_array($feeId, $authorizedFeeIds, true)) {
                        continue;
                    }
                    $fees[] = $f;
                }
            }
        } catch (\Throwable $e) {
            // ignore, view will show empty list
        }

        $this->view('paiements/create', [
            'title' => APP_NAME . ' - Enregistrer paiement',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'eleve' => $eleve,
            'students' => $students,
            'compte' => $compte,
            'totalDebt' => $totalDebt ?? null,
            'totalDebtByCurrency' => $totalDebtByCurrency ?? [],
            'feeDebtDetails' => $feeDebtDetails,
            'fees' => $fees,
            'paymentRecorderName' => trim((string) ($user['nom_complet'] ?? '')),
            'paymentFormToken' => $paymentFormToken,
            'authorizedFeeIds' => $authorizedFeeIds,
        ]);
    }

    public function store(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école', 'sec_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école']);

        $user = Auth::refresh() ?: Auth::user();

        $submittedToken = (string) ($_POST['payment_form_token'] ?? '');
        $expectedToken = (string) ($_SESSION['payment_form_token'] ?? '');
        if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
            $_SESSION['paiements_errors'] = ['Ce formulaire a déjà été traité ou a expiré. Rechargez la page et réessayez.'];
            header('Location: ' . BASE_URL . '/paiements/create');
            exit;
        }

        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $fraisId = !empty($_POST['frais_id']) ? (int) $_POST['frais_id'] : null;
        $libelle = '';
        $oldInput = [
            'eleve_id' => $eleveId,
            'montant' => $montant,
            'frais_id' => $fraisId,
            'libelle' => trim($_POST['libelle'] ?? ''),
        ];

        if (!$this->canCreatePayment($user, $fraisId)) {
            $_SESSION['paiements_errors'] = ['Vous n’avez pas l’autorisation de saisir un paiement pour ce frais.'];
            $_SESSION['paiements_old'] = $oldInput;
            $redirectUrl = BASE_URL . '/paiements/create';
            if ($eleveId > 0) {
                $redirectUrl .= '?eleve_id=' . urlencode($eleveId);
            }
            header('Location: ' . $redirectUrl);
            exit;
        }

        $agentId = null;
        if (!empty($user['reference_id'])) {
            $agentId = (int) $user['reference_id'];
        } elseif (!empty($user['id'])) {
            $agentId = (int) $user['id'];
        }

        if (!$agentId) {
            $_SESSION['flash_error'] = 'Impossible d\'identifier l\'agent en cours. Assurez-vous d\'être connecté en tant qu\'agent.';
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $errors = [];
        $fee = null;
        $dette = null;
        $feeTotal = 0.0;
        $db = Database::getConnection();
        $userSchool = (int) ($user['ecole_id'] ?? 0);
        $eleve = null;

        if ($eleveId <= 0) {
            $errors[] = 'Élève invalide.';
        } else {
            $eleve = ($userSchool > 0 && ($user['role'] ?? '') !== 'super_admin')
                ? Eleve::findByIdAndSchool($eleveId, $userSchool)
                : Eleve::findById($eleveId);
            if (!$eleve) {
                $errors[] = 'Élève invalide ou hors périmètre.';
            }
        }

        if (empty($fraisId)) {
            $errors[] = 'Un frais scolaire doit être sélectionné.';
        }

        if ($fraisId) {
            if (($user['role'] ?? '') !== 'super_admin' && $userSchool > 0) {
                $fee = \App\Models\FraisScolaire::findByIdAndSchool($fraisId, $userSchool);
            } else {
                $fee = \App\Models\FraisScolaire::findById($fraisId);
            }
            $dette = DetteEleve::findByEleveAndFrais($eleveId, $fraisId);
            if ($fee && $dette) {
                $feeTotal = (float) ($dette['montant_initial'] ?? 0);
                $remaining = (float) ($dette['montant_restant'] ?? 0);
                if ($montant <= 0) {
                    $montant = $remaining > 0 ? $remaining : $feeTotal;
                }
                $libelle = $fee['type_frais'] . ' - ' . number_format($feeTotal, 2) . ' ' . ($fee['devise'] ?? '');
                if ($remaining <= 0) {
                    $errors[] = 'Ce frais est déjà soldé pour cet élève.';
                } elseif ($montant > $remaining) {
                    $errors[] = 'Le montant saisi ne peut pas dépasser le reste à payer pour ce frais. Reste à payer : ' . number_format($remaining, 2) . ' ' . ($fee['devise'] ?? 'USD') . '.';
                } elseif ($montant > $feeTotal) {
                    $errors[] = 'Le montant saisi ne peut pas être supérieur au montant total du frais scolaire sélectionné.';
                }
            } elseif ($fee && !$dette) {
                $feeTotal = (float) ($fee['montant_total'] ?? 0);
                if (!DetteEleve::isFeeApplicableToEleve($eleveId, $fee)) {
                    $errors[] = 'Ce frais ne s’applique pas à la classe, à l’option ou à la section actuelle de cet élève.';
                } elseif ($feeTotal <= 0) {
                    $errors[] = 'Le montant du frais sélectionné est invalide.';
                } elseif ($montant > 0 && $montant > $feeTotal) {
                    $errors[] = 'Le montant saisi ne peut pas être supérieur au montant total du frais scolaire sélectionné.';
                } else {
                    $libelle = $fee['type_frais'] . ' - ' . number_format($feeTotal, 2) . ' ' . ($fee['devise'] ?? '');
                    if ($montant <= 0) {
                        $montant = $feeTotal;
                    }
                }
            } else {
                $errors[] = 'Le frais scolaire sélectionné est invalide pour votre école.';
            }
        }

        if ($libelle === '') {
            $libelle = trim($_POST['libelle'] ?? 'Paiement élève');
        }
        if ($eleveId <= 0 || $montant <= 0) {
            if (!in_array('Élève ou montant invalide.', $errors, true)) {
                $errors[] = 'Élève ou montant invalide.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['paiements_errors'] = $errors;
            $_SESSION['paiements_old'] = $oldInput;
            $redirectUrl = BASE_URL . '/paiements/create';
            if ($eleveId > 0) {
                $redirectUrl .= '?eleve_id=' . urlencode($eleveId);
            }
            header('Location: ' . $redirectUrl);
            exit;
        }

        $db = Database::getConnection();

        $reference = $this->generateUniqueReceiptReference($db);
        try {
            $ecritureId = $this->persistPaymentEntry(
                $db,
                $eleveId,
                $fraisId,
                $montant,
                $libelle,
                $agentId,
                $reference,
                $fee,
                $userSchool,
                (int) ($user['id'] ?? 0)
            );
            unset($_SESSION['payment_form_token']);
        } catch (\Throwable $e) {
            $_SESSION['paiements_errors'] = [$e->getMessage()];
            $_SESSION['paiements_old'] = $oldInput;
            $redirectUrl = BASE_URL . '/paiements/create';
            if ($eleveId > 0) {
                $redirectUrl .= '?eleve_id=' . urlencode($eleveId);
            }
            header('Location: ' . $redirectUrl);
            exit;
        }

        try {
            $notifier = new PaymentParentNotifier();
            $_SESSION['payment_notification_status'] = $notifier->notifyAfterPayment(
                $eleveId,
                (int) $fraisId,
                $montant,
                $libelle,
                $fee['devise'] ?? 'USD'
            );
        } catch (\Throwable $e) {
            $_SESSION['payment_notification_status'] = [
                'sent' => false,
                'status' => 'failed',
                'reason' => 'controller_exception',
                'message' => 'Échec de l’envoi du message au parent.',
                'details' => $e->getMessage(),
            ];
            error_log('PaiementsController::store notification failed: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/paiements/receipt?id=' . $ecritureId);
        exit;
    }

    public function gestionAutorisations(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        if (!in_array($user['role'] ?? '', ['super_admin', 'ecole_admin', 'comptable_école'], true)) {
            $this->redirect('/paiements');
            return;
        }

        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if (($user['role'] ?? '') === 'super_admin') {
            $ecoleId = (int) ($_REQUEST['ecole_id'] ?? $ecoleId);
        }
        if ($ecoleId <= 0 && ($user['role'] ?? '') !== 'super_admin') {
            $this->redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = (string) ($_POST['role'] ?? '');
            $enabled = isset($_POST['enabled']) && (string) $_POST['enabled'] === '1';
            $permissionType = (string) ($_POST['permission_type'] ?? 'fee');
            $grantedBy = (int) ($user['id'] ?? 0);

            if ($ecoleId <= 0) {
                $_SESSION['paiements_error'] = 'Sélectionnez une école avant de modifier les autorisations.';
            } elseif ($permissionType === 'fee') {
                $fraisId = (int) ($_POST['frais_id'] ?? 0);
                if (
                    $fraisId <= 0
                    || !FraisScolaire::findByIdAndSchool($fraisId, $ecoleId)
                    || !PaiementAutorisation::setRoleAccess($ecoleId, $role, $fraisId, $enabled, $grantedBy)
                ) {
                    $_SESSION['paiements_error'] = 'Le rôle ou le frais sélectionné est invalide pour cette école.';
                } else {
                    $_SESSION['paiements_success'] = 'Droits d’enregistrement mis à jour.';
                }
            } else {
                $_SESSION['paiements_error'] = 'Type d’autorisation invalide.';
            }
            $this->redirect('/paiements/gestionAutorisations' . ($ecoleId > 0 ? '?ecole_id=' . $ecoleId : ''));
        }

        $fees = $ecoleId > 0 ? FraisScolaire::getAllBySchool($ecoleId) : [];
        $matrix = $ecoleId > 0 ? PaiementAutorisation::getRoleAccessMatrix($ecoleId) : [];
        $roleList = PaiementAutorisation::allRoles();
        $this->view('paiements/gestion_autorisations', [
            'title' => APP_NAME . ' - Autorisations paiements',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'fees' => $fees,
            'roleList' => $roleList,
            'matrix' => $matrix,
            'authorizationSchoolId' => $ecoleId,
        ]);
    }

    public function edit(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();
        if (($user['role'] ?? '') !== 'comptable_école') {
            $this->redirect('/paiements');
            return;
        }

        $id = (int) ($_GET['id'] ?? 0);
        $db = Database::getConnection();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $stmt = $db->prepare(
            'SELECT ece.*, ce.eleve_id, el.nom, el.postnom, el.prenom, fs.type_frais, fs.devise
             FROM ecritures_comptables_eleves ece
             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
             INNER JOIN eleves el ON el.id = ce.eleve_id
             LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id
             WHERE ece.id = :id AND ece.type_mouvement = \'CREDIT\'
               AND (el.ecole_id = :ecole OR EXISTS (
                   SELECT 1 FROM inscriptions i
                   INNER JOIN classes c ON c.id = i.classe_id
                   WHERE i.eleve_id = el.id AND c.ecole_id = :inscription_ecole
               ))
             LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':ecole' => $schoolId, ':inscription_ecole' => $schoolId]);
        $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$payment) {
            $this->redirect('/paiements');
            return;
        }

        $this->view('paiements/edit', [
            'title' => APP_NAME . ' - Modifier paiement',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'payment' => $payment,
        ]);
    }

    public function update(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();
        if (($user['role'] ?? '') !== 'comptable_école') {
            $this->redirect('/paiements');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $newAmount = (float) ($_POST['montant'] ?? 0);
        $newLabel = trim((string) ($_POST['libelle'] ?? 'Paiement élève'));
        $db = Database::getConnection();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                'SELECT ece.*, ce.eleve_id FROM ecritures_comptables_eleves ece
                 INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                 INNER JOIN eleves el ON el.id = ce.eleve_id
                 WHERE ece.id = :id AND ece.type_mouvement = \'CREDIT\'
                   AND (el.ecole_id = :ecole OR EXISTS (
                       SELECT 1 FROM inscriptions i
                       INNER JOIN classes c ON c.id = i.classe_id
                       WHERE i.eleve_id = el.id AND c.ecole_id = :inscription_ecole
                   ))
                 FOR UPDATE'
            );
            $stmt->execute([':id' => $id, ':ecole' => $schoolId, ':inscription_ecole' => $schoolId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$payment || $newAmount <= 0) {
                throw new \RuntimeException('Paiement ou montant invalide.');
            }

            $studentLock = $db->prepare('SELECT id FROM eleves WHERE id = :id FOR UPDATE');
            $studentLock->execute([':id' => (int) $payment['eleve_id']]);

            $oldAmount = (float) $payment['montant'];
            $delta = $newAmount - $oldAmount;
            $dette = null;
            if (!empty($payment['frais_id'])) {
                $debtStmt = $db->prepare(
                    'SELECT * FROM dettes_eleves
                     WHERE eleve_id = :eleve AND frais_id = :frais
                     ORDER BY id ASC LIMIT 1 FOR UPDATE'
                );
                $debtStmt->execute([':eleve' => $payment['eleve_id'], ':frais' => $payment['frais_id']]);
                $dette = $debtStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            }
            if ($dette) {
                $available = (float) $dette['montant_restant'] + $oldAmount;
                if ($newAmount > $available || $newAmount > (float) $dette['montant_initial']) {
                    throw new \RuntimeException('Le nouveau montant dépasse le reste à payer de ce frais.');
                }
            }

            $update = $db->prepare('UPDATE ecritures_comptables_eleves SET montant = :montant, libelle = :libelle WHERE id = :id');
            $update->execute([':montant' => $newAmount, ':libelle' => $newLabel, ':id' => $id]);

            // Keep the mirrored legacy row synchronized when it can be identified.
            $legacy = $db->prepare('SELECT pe.id FROM paiements_eleves pe WHERE pe.eleve_id = :eleve AND pe.frais_id = :frais AND ABS(pe.montant_paye - :montant) < 0.001 ORDER BY ABS(TIMESTAMPDIFF(SECOND, pe.date_paiement, :date_operation)) ASC LIMIT 1');
            $legacy->execute([':eleve' => $payment['eleve_id'], ':frais' => $payment['frais_id'], ':montant' => $oldAmount, ':date_operation' => $payment['date_operation']]);
            $legacyId = $legacy->fetchColumn();
            if ($legacyId) {
                $legacyUpdate = $db->prepare('UPDATE paiements_eleves SET montant_paye = :montant WHERE id = :id');
                $legacyUpdate->execute([':montant' => $newAmount, ':id' => $legacyId]);
            }

            if (abs($delta) > 0.0001) {
                $account = $db->prepare('UPDATE comptes_eleves SET solde_debiteur = solde_debiteur - :delta WHERE id = :id');
                $account->execute([':delta' => $delta, ':id' => $payment['compte_eleve_id']]);
                if ($dette) {
                    $debtUpdate = $db->prepare('UPDATE dettes_eleves SET montant_restant = GREATEST(0, montant_restant - :delta) WHERE id = :id');
                    $debtUpdate->execute([':delta' => $delta, ':id' => $dette['id']]);
                }
            }
            $db->commit();
            $_SESSION['paiements_success'] = 'Paiement modifié avec succès.';
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['paiements_errors'] = [$e->getMessage()];
        }
        $this->redirect('/paiements');
    }

    public function receipt(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $idParam = $_GET['id'] ?? null;
        if (empty($idParam)) {
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $db = Database::getConnection();
        $data = null;

        // legacy id format: legacy-<id>
        if (is_string($idParam) && strpos($idParam, 'legacy-') === 0) {
            $legacyId = (int) substr($idParam, strlen('legacy-'));
            if ($legacyId <= 0) {
                header('Location: ' . BASE_URL . '/paiements');
                exit;
            }
            $lstmt = $db->prepare('SELECT pe.*, el.id AS eleve_id, el.nom, el.postnom, el.prenom, pe.frais_id AS frais_id, fs.type_frais AS libelle FROM paiements_eleves pe INNER JOIN eleves el ON pe.eleve_id = el.id LEFT JOIN frais_scolaires fs ON pe.frais_id = fs.id WHERE pe.id = :id LIMIT 1');
            $lstmt->execute([':id' => $legacyId]);
            $legacy = $lstmt->fetch(\PDO::FETCH_ASSOC);
            if ($legacy) {
                // normalize to ecritures shape used by the view
                $data = [
                    'id' => 'legacy-' . $legacyId,
                    'reference_recu' => null,
                    'date_operation' => $legacy['date_paiement'] ?? null,
                    'montant' => $legacy['montant_paye'] ?? 0,
                    'libelle' => $legacy['libelle'] ?? 'Paiement',
                    'eleve_id' => $legacy['eleve_id'] ?? null,
                    'frais_id' => $legacy['frais_id'] ?? null,
                    'nom' => $legacy['nom'] ?? null,
                    'postnom' => $legacy['postnom'] ?? null,
                    'prenom' => $legacy['prenom'] ?? null,
                ];
            }
        } else {
            $ecritureId = (int) $idParam;
            if ($ecritureId > 0) {
                $stmt = $db->prepare('SELECT ece.*, ce.eleve_id, el.nom, el.postnom, el.prenom, COALESCE(NULLIF(us.nom_complet, \'\'), NULLIF(CONCAT_WS(\' \', a.nom, a.postnom, a.prenom), \'\'), u.nom_complet, \'Agent non identifié\') AS agent_nom, COALESCE(ra.titre_role, us.role, u.role, \'Agent\') AS agent_fonction FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id INNER JOIN eleves el ON ce.eleve_id = el.id LEFT JOIN agents a ON ece.agent_saisie_id = a.id LEFT JOIN roles_administration ra ON a.role_id = ra.id LEFT JOIN utilisateurs us ON ece.utilisateur_saisie_id = us.id LEFT JOIN (SELECT reference_id, MAX(nom_complet) AS nom_complet, MAX(role) AS role FROM utilisateurs WHERE role NOT IN (\'eleve_ecole\', \'parent_ecole\') GROUP BY reference_id) u ON ece.agent_saisie_id = u.reference_id WHERE ece.id = :id LIMIT 1');
                $stmt->execute([':id' => $ecritureId]);
                $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        if (!$data) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        $data['agent_nom'] = 'Agent non identifié';
        if (!empty($data['utilisateur_saisie_id'])) {
            $recorderStmt = $db->prepare('SELECT nom_complet, role FROM utilisateurs WHERE id = :id LIMIT 1');
            $recorderStmt->execute([':id' => (int) $data['utilisateur_saisie_id']]);
            $recorder = $recorderStmt->fetch(\PDO::FETCH_ASSOC);
            if ($recorder) {
                $data['agent_nom'] = trim((string) ($recorder['nom_complet'] ?? '')) ?: $data['agent_nom'];
                $data['agent_fonction'] = User::getRoleLabel((string) ($recorder['role'] ?? ''));
            }
        }
        if ($data['agent_nom'] === 'Agent non identifié' && !empty($data['agent_saisie_id'])) {
            $agentStmt = $db->prepare(
                'SELECT CONCAT_WS(\' \', a.nom, a.postnom, a.prenom) AS nom, ra.titre_role
                 FROM agents a LEFT JOIN roles_administration ra ON ra.id = a.role_id
                 WHERE a.id = :id LIMIT 1'
            );
            $agentStmt->execute([':id' => (int) $data['agent_saisie_id']]);
            $agent = $agentStmt->fetch(\PDO::FETCH_ASSOC);
            if ($agent && trim((string) ($agent['nom'] ?? '')) !== '') {
                $data['agent_nom'] = trim((string) $agent['nom']);
                $data['agent_fonction'] = $agent['titre_role'] ?? 'Agent';
            }
        }

        // Ensure user can only view receipts for élèves in their school (unless super_admin)
        try {
            $userSchool = (int) ($user['ecole_id'] ?? 0);
            if (($user['role'] ?? '') !== 'super_admin' && $userSchool > 0) {
                $paymentEleveId = (int) ($data['eleve_id'] ?? 0);
                if ($paymentEleveId <= 0 || !Eleve::findByIdAndSchool($paymentEleveId, $userSchool)) {
                    header('Location: ' . BASE_URL . '/error/notFound');
                    exit;
                }
            }
        } catch (\Throwable $e) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        // Fetch the latest compte for this élève to display solde / dette
        $compte = null;
        try {
            $compte = Eleve::getAccount((int) ($data['eleve_id'] ?? 0));
        } catch (\Throwable $e) {
            $compte = null;
        }

        // Determine school name for this élève (fallback to APP_NAME)
        $ecoleName = APP_NAME;
        $ecoleLogo = null;
        try {
            $eleve = Eleve::findById((int) ($data['eleve_id'] ?? 0));
            if (!empty($eleve['ecole_id'])) {
                $ecole = \App\Models\Ecole::findById((int) $eleve['ecole_id']);
                if ($ecole && !empty($ecole['nom_etablissement'])) {
                    $ecoleName = $ecole['nom_etablissement'];
                }
                if (!empty($ecole['logo_url'])) {
                    $ecoleLogo = strpos($ecole['logo_url'], 'http') === 0
                        ? $ecole['logo_url']
                        : BASE_URL . '/' . ltrim($ecole['logo_url'], '/');
                }
            } else {
                // try to infer from latest inscription -> classe -> ecole
                $db = \App\Core\Database::getConnection();
                $s = $db->prepare('SELECT c.ecole_id FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = :id ORDER BY i.date_inscription DESC LIMIT 1');
                $s->execute([':id' => $data['eleve_id'] ?? 0]);
                $row = $s->fetch();
                if (!empty($row['ecole_id'])) {
                    $ec = \App\Models\Ecole::findById((int) $row['ecole_id']);
                    if ($ec && !empty($ec['nom_etablissement'])) {
                        $ecoleName = $ec['nom_etablissement'];
                    }
                    if (!empty($ec['logo_url'])) {
                        $ecoleLogo = strpos($ec['logo_url'], 'http') === 0
                            ? $ec['logo_url']
                            : BASE_URL . '/' . ltrim($ec['logo_url'], '/');
                    }
                }
            }
        } catch (\Throwable $e) {
            $ecoleName = APP_NAME;
        }

        // Calculate debt remaining according to the student’s applicable fees.
        $reste = null;
        $resteParFrais = null;
        try {
            $eleveId = (int) ($data['eleve_id'] ?? 0);
            $fraisId = !empty($data['frais_id']) ? (int) $data['frais_id'] : 0;

            if ($eleveId > 0) {
                $reste = DetteEleve::getTotalOutstandingByEleve($eleveId);
            }

            if ($eleveId > 0 && $fraisId > 0) {
                $dette = DetteEleve::findByEleveAndFrais($eleveId, $fraisId);
                if ($dette) {
                    $resteParFrais = (float) ($dette['montant_restant'] ?? 0);
                } else {
                    $fee = \App\Models\FraisScolaire::findById($fraisId);
                    $feeTotal = $fee ? (float) ($fee['montant_total'] ?? 0) : 0.0;
                    $stmtPaid = $db->prepare('SELECT SUM(ece.montant) AS paid FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id WHERE ce.eleve_id = :eleve AND ece.frais_id = :frais AND ece.type_mouvement = :type');
                    $stmtPaid->execute([':eleve' => $eleveId, ':frais' => $fraisId, ':type' => 'CREDIT']);
                    $paid1 = (float) ($stmtPaid->fetchColumn() ?: 0);

                    $stmtPaid2 = $db->prepare('SELECT SUM(pe.montant_paye) AS paid FROM paiements_eleves pe WHERE pe.eleve_id = :eleve AND pe.frais_id = :frais');
                    $stmtPaid2->execute([':eleve' => $eleveId, ':frais' => $fraisId]);
                    $paid2 = (float) ($stmtPaid2->fetchColumn() ?: 0);

                    $resteParFrais = max(0.0, $feeTotal - max($paid1, $paid2));
                }

                if ($resteParFrais !== null) {
                    $reste = $resteParFrais;
                }
            }
        } catch (\Throwable $e) {
            $reste = null;
            $resteParFrais = null;
        }

        $this->view('paiements/receipt', [
            'title' => $ecoleName . ' - Reçu paiement',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'ecriture' => $data,
            'compte' => $compte,
            'ecole_name' => $ecoleName,
            'ecole_logo' => $ecoleLogo,
            'reste_a_payer' => $reste,
            'reste_par_frais' => $resteParFrais,
        ]);
    }

    public function qrSummary(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $idParam = $_GET['id'] ?? null;
        if (empty($idParam)) {
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $db = Database::getConnection();
        $data = null;

        if (is_string($idParam) && strpos($idParam, 'legacy-') === 0) {
            $legacyId = (int) substr($idParam, strlen('legacy-'));
            if ($legacyId <= 0) {
                header('Location: ' . BASE_URL . '/paiements');
                exit;
            }

            $lstmt = $db->prepare('SELECT pe.*, el.id AS eleve_id, el.nom, el.postnom, el.prenom, pe.frais_id AS frais_id FROM paiements_eleves pe INNER JOIN eleves el ON pe.eleve_id = el.id WHERE pe.id = :id LIMIT 1');
            $lstmt->execute([':id' => $legacyId]);
            $legacy = $lstmt->fetch(\PDO::FETCH_ASSOC);
            if ($legacy) {
                $data = [
                    'id' => 'legacy-' . $legacyId,
                    'reference_recu' => null,
                    'date_operation' => $legacy['date_paiement'] ?? null,
                    'montant' => $legacy['montant_paye'] ?? 0,
                    'libelle' => $legacy['libelle'] ?? 'Paiement',
                    'eleve_id' => $legacy['eleve_id'] ?? null,
                    'frais_id' => $legacy['frais_id'] ?? null,
                    'nom' => $legacy['nom'] ?? null,
                    'postnom' => $legacy['postnom'] ?? null,
                    'prenom' => $legacy['prenom'] ?? null,
                ];
            }
        } else {
            $ecritureId = (int) $idParam;
            if ($ecritureId > 0) {
                $stmt = $db->prepare('SELECT ece.*, ce.eleve_id, el.nom, el.postnom, el.prenom FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id INNER JOIN eleves el ON ce.eleve_id = el.id WHERE ece.id = :id LIMIT 1');
                $stmt->execute([':id' => $ecritureId]);
                $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        if (!$data) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        $eleveId = (int) ($data['eleve_id'] ?? 0);
        $fraisId = !empty($data['frais_id']) ? (int) $data['frais_id'] : 0;
        $reste = null;
        if ($eleveId > 0) {
            $reste = DetteEleve::getTotalOutstandingByEleve($eleveId);
            if ($fraisId > 0) {
                $dette = DetteEleve::findByEleveAndFrais($eleveId, $fraisId);
                if ($dette) {
                    $reste = (float) ($dette['montant_restant'] ?? 0);
                }
            }
        }

        $parts = array_filter([
            $data['nom'] ?? '',
            $data['postnom'] ?? '',
            $data['prenom'] ?? '',
        ], fn($value) => $value !== null && trim((string) $value) !== '');
        $eleveName = implode(' ', array_map('trim', $parts));

        $historiquePaiements = [];
        if ($eleveId > 0) {
            $historiquePaiements = $this->fetchPaymentsForUser($user, 0, $eleveId);
            $historiquePaiements = array_values(array_filter($historiquePaiements, function ($payment) use ($idParam, $data) {
                $currentId = (string) ($payment['id'] ?? '');
                $samePaymentId = ($currentId === (string) $idParam || $currentId === (string) ($data['id'] ?? ''));
                $sameReference = (string) ($payment['reference_recu'] ?? '') === (string) ($data['reference_recu'] ?? '');
                return !$samePaymentId && !$sameReference;
            }));
            usort($historiquePaiements, function ($a, $b) {
                $ta = strtotime((string) ($a['date_operation'] ?? '1970-01-01 00:00:00'));
                $tb = strtotime((string) ($b['date_operation'] ?? '1970-01-01 00:00:00'));
                return $tb <=> $ta;
            });
            $historiquePaiements = array_slice($historiquePaiements, 0, 5);
        }

        $this->view('paiements/qr_summary', [
            'title' => 'Paiement reçu',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'ecriture' => $data,
            'eleve_name' => $eleveName,
            'montant_paye' => (float) ($data['montant'] ?? 0),
            'date_paiement' => $data['date_operation'] ?? null,
            'reste_a_payer' => $reste,
            'historique_paiements' => $historiquePaiements,
        ]);
    }
}
