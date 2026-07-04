<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Eleve;
use App\Models\User;

class PaiementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'sec_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        // fetch a large history so the page can retracer tous les paiements
        $payments = $this->fetchPaymentsForUser($user, 10000);

        $this->view('paiements/index', [
            'title' => APP_NAME . ' - Paiements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'payments' => $payments,
        ]);
    }

    public function export(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();

        $format = strtolower(trim($_GET['format'] ?? 'csv'));
        $payments = $this->fetchPaymentsForUser($user, 10000);

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
                fputcsv($out, [$p['reference_recu'] ?? '', $name, $p['date_operation'] ?? '', (float) ($p['montant'] ?? 0), $p['nom_compte'] ?? '', $p['agent_nom'] ?? '', $p['libelle'] ?? '']);
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
        $payments = $this->fetchPaymentsForUser($user, 10000);

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

    private function fetchPaymentsForUser(array $user, int $limit = 200): array
    {
        $db = \App\Core\Database::getConnection();
        $sql = 'SELECT ece.id, ece.reference_recu, ece.date_operation, ece.montant, ece.libelle, ce.eleve_id, el.nom, el.postnom, el.prenom, cb.nom_compte, u.nom_complet AS agent_nom '
            . 'FROM ecritures_comptables_eleves ece '
            . 'INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id '
            . 'INNER JOIN eleves el ON ce.eleve_id = el.id '
            . 'LEFT JOIN caisses_banques cb ON ece.caisse_banque_id = cb.id '
            . 'LEFT JOIN utilisateurs u ON ece.agent_saisie_id = u.reference_id AND u.role NOT IN (\'eleve_ecole\', \'parent_ecole\') '
            . 'WHERE ece.type_mouvement = :type ';

        $params = [':type' => 'CREDIT'];
        if (($user['role'] ?? '') !== 'super_admin') {
            $sql .= 'AND (el.ecole_id = :ecole OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole)) ';
            $params[':ecole'] = (int) ($user['ecole_id'] ?? 0);
        }

        $sql .= 'ORDER BY ece.date_operation DESC LIMIT :limit';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Also include legacy paiements_eleves table if present
        try {
            $legacySql = 'SELECT pe.id AS legacy_id, pe.eleve_id, pe.frais_id, pe.montant_paye AS montant, pe.date_paiement AS date_operation, fs.type_frais AS libelle_frais, el.nom, el.postnom, el.prenom '
                . 'FROM paiements_eleves pe '
                . 'INNER JOIN eleves el ON pe.eleve_id = el.id '
                . 'LEFT JOIN frais_scolaires fs ON pe.frais_id = fs.id '
                . 'WHERE 1=1 ';

            $legacyParams = [];
            if (($user['role'] ?? '') !== 'super_admin') {
                $legacySql .= 'AND (el.ecole_id = :ecole OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole)) ';
                $legacyParams[':ecole'] = (int) ($user['ecole_id'] ?? 0);
            }
            $legacySql .= 'ORDER BY pe.date_paiement DESC LIMIT :limit';
            $lstmt = $db->prepare($legacySql);
            $lstmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
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

        return array_slice($records, 0, $limit);
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
            $eleve = Eleve::findById($eleveId);
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
        $agentId = $user['reference_id'] ?? null;

        if (!$agentId) {
            $_SESSION['flash_error'] = 'Impossible d\'identifier l\'agent en cours. Assurez-vous d\'être connecté en tant qu\'agent.';
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $fraisId = !empty($_POST['frais_id']) ? (int) $_POST['frais_id'] : null;
        $libelle = '';
        if ($fraisId) {
            $fee = \App\Models\FraisScolaire::findById($fraisId);
            if ($fee) {
                $libelle = $fee['type_frais'] . ' - ' . number_format((float) ($fee['montant_total'] ?? 0), 2) . ' ' . ($fee['devise'] ?? '');
                // if montant not provided, use fee amount as default
                if ($montant <= 0) {
                    $montant = (float) ($fee['montant_total'] ?? 0);
                }
            }
        }
        if ($libelle === '') {
            $libelle = trim($_POST['libelle'] ?? 'Paiement élève');
        }
        $caisseId = !empty($_POST['caisse_id']) ? (int) $_POST['caisse_id'] : null;

        if ($eleveId <= 0 || $montant <= 0) {
            $_SESSION['flash_error'] = 'Élève ou montant invalide.';
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $db = Database::getConnection();

        // Ensure compte exists
        $compte = Eleve::getAccount($eleveId);
        if (!$compte) {
            // Create a compte (use latest active school year if available)
            $stmtYear = $db->prepare('SELECT id FROM annees_scolaires WHERE est_active = 1 AND ecole_id = :ecole_id LIMIT 1');
            $stmtYear->execute([':ecole_id' => $user['ecole_id'] ?? 0]);
            $year = $stmtYear->fetch();
            $anneeId = $year['id'] ?? 1;

            $ins = $db->prepare('INSERT INTO comptes_eleves (eleve_id, annee_scolaire_id, solde_debiteur) VALUES (:eleve, :annee, 0)');
            $ins->execute([':eleve' => $eleveId, ':annee' => $anneeId]);
            $compteId = (int) $db->lastInsertId();
        } else {
            $compteId = (int) $compte['id'];
        }

        $reference = 'REC-' . date('YmdHis') . '-' . random_int(100, 999);

        $stmt = $db->prepare('INSERT INTO ecritures_comptables_eleves (compte_eleve_id, frais_id, caisse_banque_id, type_mouvement, montant, reference_recu, libelle, agent_saisie_id) VALUES (:compte, :frais, :caisse, :type, :montant, :ref, :libelle, :agent)');
        $stmt->execute([
            ':compte' => $compteId,
            ':frais' => $fraisId,
            ':caisse' => $caisseId,
            ':type' => 'CREDIT',
            ':montant' => $montant,
            ':ref' => $reference,
            ':libelle' => $libelle,
            ':agent' => $agentId,
        ]);

        $ecritureId = (int) $db->lastInsertId();

        // Update compte solde_debiteur (subtract payment)
        $upd = $db->prepare('UPDATE comptes_eleves SET solde_debiteur = solde_debiteur - :montant WHERE id = :id');
        $upd->execute([':montant' => $montant, ':id' => $compteId]);

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
