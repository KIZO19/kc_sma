<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Eleve;
use App\Models\FraisScolaire;
use App\Models\User;
use App\Models\Eleve as EleveModel;

class PaiementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'sec_école', 'parent_ecole']);

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

        $userSchool = (int) ($user['ecole_id'] ?? 0);
        $students = $userSchool > 0 ? Eleve::getAllBySchool($userSchool) : Eleve::getAll();
        $fees = $userSchool > 0 ? FraisScolaire::getAllBySchool($userSchool) : [];

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
        ]);
    }

    public function export(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'sec_école']);

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
            fputcsv($out, ['Réf reçu', 'Élève', 'Date', 'Montant', 'Caisse', 'Agent', 'Libellé']);
            foreach ($payments as $p) {
                $name = trim(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['postnom'] ?? ''));
                fputcsv($out, [
                    $p['reference_recu'] ?? '',
                    $name,
                    $p['date_operation'] ?? '',
                    $p['montant_affiche'] ?? number_format((float) ($p['montant'] ?? 0), 2),
                    $p['nom_compte'] ?? '',
                    $p['agent_nom'] ?? '',
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
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'sec_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $eleveId = !empty($_GET['eleve_id']) ? (int) $_GET['eleve_id'] : null;
        $fraisId = !empty($_GET['frais_id']) ? (int) $_GET['frais_id'] : null;
        if ($eleveId && ($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            if (!Eleve::findByIdAndSchool($eleveId, (int) $user['ecole_id'])) {
                $eleveId = null;
            }
        }
        $payments = $this->fetchPaymentsForUser($user, 0, $eleveId, $fraisId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['payments' => $payments], JSON_UNESCAPED_UNICODE);
        exit;
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
        $sql = 'SELECT ece.id, ece.reference_recu, ece.date_operation, ece.montant, ece.libelle, ce.eleve_id, el.nom, el.postnom, el.prenom, cb.nom_compte, u.nom_complet AS agent_nom, fs.devise AS frais_devise, COALESCE(fs.devise, ecole.devise_principale, \'USD\') AS transaction_devise '
            . 'FROM ecritures_comptables_eleves ece '
            . 'INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id '
            . 'INNER JOIN eleves el ON ce.eleve_id = el.id '
            . 'LEFT JOIN ecoles ecole ON el.ecole_id = ecole.id '
            . 'LEFT JOIN caisses_banques cb ON ece.caisse_banque_id = cb.id '
            . 'LEFT JOIN frais_scolaires fs ON ece.frais_id = fs.id '
            . 'LEFT JOIN utilisateurs u ON ece.agent_saisie_id = u.reference_id AND u.role NOT IN (\'eleve_ecole\', \'parent_ecole\') '
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
                $records[] = [
                    'id' => 'legacy-' . ($l['legacy_id'] ?? ''),
                    'reference_recu' => null,
                    'date_operation' => $l['date_operation'] ?? null,
                    'montant' => $l['montant'] ?? 0,
                    'libelle' => $l['libelle_frais'] ?? 'Paiement',
                    'eleve_id' => $l['eleve_id'] ?? null,
                    'nom' => $l['nom'] ?? null,
                    'postnom' => $l['postnom'] ?? null,
                    'prenom' => $l['prenom'] ?? null,
                    'nom_compte' => null,
                    'agent_nom' => null,
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

    private function persistPaymentEntry(\PDO $db, int $compteId, int $eleveId, ?int $fraisId, ?int $caisseId, float $montant, string $libelle, int $agentId, string $reference): int
    {
        $db->beginTransaction();
        try {
            $agentFkId = null;
            if ($agentId > 0) {
                $agentCheck = $db->prepare('SELECT id FROM agents WHERE id = :id LIMIT 1');
                $agentCheck->execute([':id' => $agentId]);
                if ($agentCheck->fetch()) {
                    $agentFkId = $agentId;
                }
            }
            if ($agentFkId === null) {
                $fallbackAgent = $db->query('SELECT id FROM agents ORDER BY id LIMIT 1')->fetch();
                $agentFkId = $fallbackAgent['id'] ?? null;
            }

            $stmt = $db->prepare('INSERT INTO ecritures_comptables_eleves (compte_eleve_id, frais_id, caisse_banque_id, type_mouvement, montant, reference_recu, libelle, agent_saisie_id) VALUES (:compte, :frais, :caisse, :type, :montant, :ref, :libelle, :agent)');
            $stmt->execute([
                ':compte' => $compteId,
                ':frais' => $fraisId,
                ':caisse' => $caisseId,
                ':type' => 'CREDIT',
                ':montant' => $montant,
                ':ref' => $reference,
                ':libelle' => $libelle,
                ':agent' => $agentFkId,
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

            $db->commit();
            return $ecritureId;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $eleveId = (int) ($_GET['eleve_id'] ?? $_GET['id'] ?? 0);
        $eleve = null;
        $students = [];
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
        } else {
            // No specific eleve requested: provide a selector (scope to school)
            $userSchool = (int) ($user['ecole_id'] ?? 0);
            $students = $userSchool > 0 ? Eleve::getAllBySchool($userSchool) : Eleve::getAll();
            $compte = null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM caisses_banques WHERE ecole_id = :ecole_id OR ecole_id IS NULL');
        $stmt->execute([':ecole_id' => $user['ecole_id'] ?? 0]);
        $caisses = $stmt->fetchAll();

        // Fetch fees for this school to populate motif picklist
        $fees = [];
        try {
            $fees = \App\Models\FraisScolaire::getAllBySchool((int) ($user['ecole_id'] ?? 0));

            if ($eleveId > 0 && !empty($fees)) {
                $paymentsByFee = [];

                try {
                    $stmtPaid = $db->prepare('SELECT ece.frais_id, SUM(ece.montant) AS paid FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id WHERE ce.eleve_id = :eleve GROUP BY ece.frais_id');
                    $stmtPaid->execute([':eleve' => $eleveId]);
                    foreach ($stmtPaid->fetchAll() as $row) {
                        $paymentsByFee[(int) ($row['frais_id'] ?? 0)] = (float) ($row['paid'] ?? 0);
                    }
                } catch (\Throwable $e) {
                    // ignore legacy query issues
                }

                try {
                    $stmtPaid2 = $db->prepare('SELECT pe.frais_id, SUM(pe.montant_paye) AS paid FROM paiements_eleves pe WHERE pe.eleve_id = :eleve GROUP BY pe.frais_id');
                    $stmtPaid2->execute([':eleve' => $eleveId]);
                    foreach ($stmtPaid2->fetchAll() as $row) {
                        $feeId = (int) ($row['frais_id'] ?? 0);
                        $paymentsByFee[$feeId] = ($paymentsByFee[$feeId] ?? 0.0) + (float) ($row['paid'] ?? 0);
                    }
                } catch (\Throwable $e) {
                    // ignore legacy table issues
                }

                foreach ($fees as &$feeItem) {
                    $paid = $paymentsByFee[(int) ($feeItem['id'] ?? 0)] ?? 0.0;
                    $feeItem['remaining'] = max(0.0, (float) ($feeItem['montant_total'] ?? 0) - $paid);
                }
                unset($feeItem);
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
            'compte' => $compte,
            'caisses' => $caisses,
            'fees' => $fees,
        ]);
    }

    public function store(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
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

        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $fraisId = !empty($_POST['frais_id']) ? (int) $_POST['frais_id'] : null;
        $libelle = '';
        $caisseId = !empty($_POST['caisse_id']) ? (int) $_POST['caisse_id'] : null;
        $oldInput = [
            'eleve_id' => $eleveId,
            'montant' => $montant,
            'frais_id' => $fraisId,
            'libelle' => trim($_POST['libelle'] ?? ''),
            'caisse_id' => $caisseId,
        ];

        $errors = [];
        $fee = null;
        $db = Database::getConnection();
        $userSchool = (int) ($user['ecole_id'] ?? 0);

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
            $fee = (($user['role'] ?? '') !== 'super_admin' && $userSchool > 0)
                ? \App\Models\FraisScolaire::findByIdAndSchool($fraisId, $userSchool)
                : \App\Models\FraisScolaire::findById($fraisId);
            if ($fee) {
                $feeTotal = (float) ($fee['montant_total'] ?? 0);
                $paid = 0.0;
                try {
                    $stmtPaid = $db->prepare('SELECT SUM(ece.montant) AS paid FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id WHERE ce.eleve_id = :eleve AND ece.frais_id = :frais');
                    $stmtPaid->execute([':eleve' => $eleveId, ':frais' => $fraisId]);
                    $paid += (float) ($stmtPaid->fetchColumn() ?: 0);
                } catch (\Throwable $e) {
                    // ignore missing legacy or query issues
                }
                try {
                    $stmtPaid2 = $db->prepare('SELECT SUM(pe.montant_paye) AS paid FROM paiements_eleves pe WHERE pe.eleve_id = :eleve AND pe.frais_id = :frais');
                    $stmtPaid2->execute([':eleve' => $eleveId, ':frais' => $fraisId]);
                    $paid += (float) ($stmtPaid2->fetchColumn() ?: 0);
                } catch (\Throwable $e) {
                    // ignore missing legacy table
                }

                $remaining = max(0.0, $feeTotal - $paid);
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
            } elseif (($user['role'] ?? '') !== 'super_admin' && $userSchool > 0) {
                $errors[] = 'Le frais scolaire sélectionné est invalide pour votre école.';
            }
        }

        if ($libelle === '') {
            $libelle = trim($_POST['libelle'] ?? 'Paiement élève');
        }
        $caisseId = !empty($_POST['caisse_id']) ? (int) $_POST['caisse_id'] : null;

        if ($eleveId <= 0 || $montant <= 0) {
            if (!in_array('Élève invalide.', $errors, true)) {
                $errors[] = 'Élève ou montant invalide.';
            }
        }

        if ($eleveId <= 0 || $montant <= 0) {
            $errors[] = 'Élève ou montant invalide.';
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

        $compteId = $this->ensureStudentAccount($db, $eleveId, $userSchool);
        $reference = 'REC-' . date('YmdHis') . '-' . random_int(100, 999);
        $ecritureId = $this->persistPaymentEntry($db, $compteId, $eleveId, $fraisId, $caisseId, $montant, $libelle, $agentId, $reference);

        header('Location: ' . BASE_URL . '/paiements/receipt?id=' . $ecritureId);
        exit;
    }

    public function receipt(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école', 'sec_école']);

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
                    'caisse_name' => null,
                ];
            }
        } else {
            $ecritureId = (int) $idParam;
            if ($ecritureId > 0) {
                $stmt = $db->prepare('SELECT ece.*, ce.eleve_id, el.nom, el.postnom, el.prenom, cb.nom_compte AS caisse_name FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id INNER JOIN eleves el ON ce.eleve_id = el.id LEFT JOIN caisses_banques cb ON ece.caisse_banque_id = cb.id WHERE ece.id = :id LIMIT 1');
                $stmt->execute([':id' => $ecritureId]);
                $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        if (!$data) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
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
        try {
            $eleve = Eleve::findById((int) ($data['eleve_id'] ?? 0));
            if (!empty($eleve['ecole_id'])) {
                $ecole = \App\Models\Ecole::findById((int) $eleve['ecole_id']);
                if ($ecole && !empty($ecole['nom_etablissement'])) {
                    $ecoleName = $ecole['nom_etablissement'];
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
                }
            }
        } catch (\Throwable $e) {
            $ecoleName = APP_NAME;
        }

        // Calculate remaining amount for the related fee (if any)
        $reste = null;
        try {
            $fraisId = $data['frais_id'] ?? null;
            if (!empty($fraisId) && !empty($data['eleve_id'])) {
                $fee = \App\Models\FraisScolaire::findById((int) $fraisId);
                $feeTotal = $fee ? (float) ($fee['montant_total'] ?? 0) : 0.0;

                // Sum payments from ecritures
                $stmtPaid = $db->prepare('SELECT SUM(ece.montant) AS paid FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id WHERE ce.eleve_id = :eleve AND ece.frais_id = :frais');
                $stmtPaid->execute([':eleve' => (int) $data['eleve_id'], ':frais' => (int) $fraisId]);
                $paid1 = (float) ($stmtPaid->fetchColumn() ?: 0);

                // Sum payments from legacy table
                $stmtPaid2 = $db->prepare('SELECT SUM(pe.montant_paye) AS paid FROM paiements_eleves pe WHERE pe.eleve_id = :eleve AND pe.frais_id = :frais');
                $stmtPaid2->execute([':eleve' => (int) $data['eleve_id'], ':frais' => (int) $fraisId]);
                $paid2 = (float) ($stmtPaid2->fetchColumn() ?: 0);

                $totalPaid = $paid1 + $paid2;
                $reste = max(0.0, $feeTotal - $totalPaid);
            }
        } catch (\Throwable $e) {
            $reste = null;
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
            'reste_a_payer' => $reste,
        ]);
    }
}
