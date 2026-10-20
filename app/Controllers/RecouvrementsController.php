<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Derogation;
use App\Models\SeuilRecouvrement;
use App\Models\User;

class RecouvrementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $filters = $this->getFilters();
        $fees = $this->fetchActiveFees($user);
        $currencies = array_values(array_unique(array_map(
            static fn (array $fee): string => strtoupper(trim((string) ($fee['devise'] ?? 'USD'))) ?: 'USD',
            $fees
        )));
        $thresholds = (int) ($user['ecole_id'] ?? 0) > 0
            ? SeuilRecouvrement::getBySchool((int) $user['ecole_id'])
            : [];
        $debts = $this->aggregateByStudent($this->fetchOutstandingDebts($user, $filters));
        $this->applyCollectionOrientation($debts, $thresholds, $this->fetchStudentProtection($user));
        $summary = $this->buildSummary($debts);
        $feeColumns = $this->getFeeColumns($debts);

        $this->view('recouvrements/index', [
            'title' => APP_NAME . ' - Recouvrements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'debts' => $debts,
            'summary' => $summary,
            'feeColumns' => $feeColumns,
            'filters' => $filters,
            'classes' => $this->fetchClasses($user),
            'sections' => $this->fetchSections($user),
            'fees' => $fees,
            'currencies' => $currencies,
            'thresholds' => $thresholds,
        ]);
    }

    public function saveThresholds(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if ($ecoleId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/recouvrements');
        }

        $allowedCurrencies = array_values(array_unique(array_map(
            static fn (array $fee): string => strtoupper(trim((string) ($fee['devise'] ?? 'USD'))) ?: 'USD',
            $this->fetchActiveFees($user)
        )));
        $submitted = is_array($_POST['thresholds'] ?? null) ? $_POST['thresholds'] : [];
        foreach ($allowedCurrencies as $currency) {
            $value = trim((string) ($submitted[$currency] ?? ''));
            if ($value !== '' && (!is_numeric($value) || (float) $value < 0)) {
                $_SESSION['recouvrements_error'] = 'Chaque seuil doit être un montant positif ou nul.';
                $this->redirect('/recouvrements');
            }
        }

        foreach ($allowedCurrencies as $currency) {
            $value = trim((string) ($submitted[$currency] ?? ''));
            if ($value === '') {
                SeuilRecouvrement::delete($ecoleId, $currency);
                continue;
            }
            if (!SeuilRecouvrement::save($ecoleId, $currency, (float) $value, (int) ($user['id'] ?? 0))) {
                $_SESSION['recouvrements_error'] = 'Impossible d’enregistrer les seuils de recouvrement.';
                $this->redirect('/recouvrements');
            }
        }

        $_SESSION['recouvrements_success'] = 'Les seuils de recouvrement ont été enregistrés.';
        $this->redirect('/recouvrements');
    }

    public function export(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $filters = $this->getFilters();
        $debts = $this->aggregateByStudent($this->fetchOutstandingDebts($user, $filters));
        $format = strtolower(trim((string) ($_GET['format'] ?? 'csv')));
        $columns = $this->getExportColumns();

        if ($format === 'csv' || $format === 'excel') {
            $extension = $format === 'excel' ? 'xls' : 'csv';
            header('Content-Type: ' . ($format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv') . '; charset=UTF-8');
            header('Content-Disposition: attachment; filename="recouvrements_' . date('Ymd_His') . '.' . $extension . '"');
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->getExportHeaders($columns));
            foreach ($debts as $debt) {
                fputcsv($out, $this->getExportRow($debt, $columns));
            }
            fclose($out);
            exit;
        }

        if ($format === 'pdf') {
            $html = $this->renderViewToString('recouvrements/export_pdf', [
                'debts' => $debts,
                'columns' => $columns,
                'title' => 'Liste des recouvrements',
                'generatedAt' => date('d/m/Y à H:i'),
            ]);
            if (class_exists('\\Dompdf\\Dompdf')) {
                $dompdfClass = '\\Dompdf\\Dompdf';
                $dompdf = new $dompdfClass();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="recouvrements_' . date('Ymd_His') . '.pdf"');
                echo $dompdf->output();
                exit;
            }

            $this->view('recouvrements/export_pdf', [
                'debts' => $debts,
                'columns' => $columns,
                'title' => 'Liste des recouvrements',
                'generatedAt' => date('d/m/Y à H:i'),
                'printFallback' => true,
            ]);
            return;
        }

        $this->redirect('/recouvrements');
    }

    private function getFilters(): array
    {
        $min = trim((string) ($_GET['montant_min'] ?? ''));
        $max = trim((string) ($_GET['montant_max'] ?? ''));

        return [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'section_id' => ctype_digit(trim((string) ($_GET['section_id'] ?? ''))) ? (int) $_GET['section_id'] : 0,
            'classe' => ctype_digit(trim((string) ($_GET['classe'] ?? ''))) ? (int) $_GET['classe'] : 0,
            'frais_id' => ctype_digit(trim((string) ($_GET['frais_id'] ?? ''))) ? (int) $_GET['frais_id'] : 0,
            'devise' => strtoupper(trim((string) ($_GET['devise'] ?? ''))),
            'montant_min' => is_numeric($min) ? max(0, (float) $min) : null,
            'montant_max' => is_numeric($max) ? max(0, (float) $max) : null,
        ];
    }

    private function fetchClasses(array $user): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT DISTINCT c.id, c.nom_classe,
                   CASE WHEN sec.nom_section = \'Secondaire\' THEN COALESCE(o.nom_option, \'Option non définie\') ELSE NULL END AS nom_option
            FROM classes c
            LEFT JOIN sections sec ON sec.id = c.section_id
            LEFT JOIN options o ON o.id = c.option_id';
        $params = [];
        if (($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            $sql .= ' WHERE c.ecole_id = :ecole_id';
            $params[':ecole_id'] = (int) $user['ecole_id'];
        }
        $sql .= ' ORDER BY c.nom_classe ASC, o.nom_option ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function fetchStudentProtection(array $user): array
    {
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        if ($ecoleId <= 0) {
            return [];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT eleve_id, statut, date_fin, motif
             FROM derogations
             WHERE ecole_id = :ecole_id
               AND (statut = 'En_attente' OR (statut = 'Approuvee' AND date_fin >= CURDATE()))
             ORDER BY CASE WHEN statut = 'Approuvee' THEN 0 ELSE 1 END, created_at DESC, id DESC"
        );
        $stmt->execute([':ecole_id' => $ecoleId]);

        $protectionByStudent = [];
        foreach ($stmt->fetchAll() as $request) {
            $studentId = (int) $request['eleve_id'];
            $protectionByStudent[$studentId] ??= $request;
        }
        return $protectionByStudent;
    }

    private function applyCollectionOrientation(array &$debts, array $thresholds, array $protectionByStudent): void
    {
        foreach ($debts as &$debt) {
            $studentId = (int) ($debt['eleve_id'] ?? 0);
            $remainingAmounts = $debt['remaining_by_currency'] ?? [];
            $hasDebt = (bool) array_filter($remainingAmounts, static fn ($amount): bool => (float) $amount > 0.005);
            $protection = $protectionByStudent[$studentId] ?? null;
            $debt['derogation'] = $protection;

            if (!$hasDebt) {
                $debt['collection_status'] = 'Dette réglée';
                $debt['collection_level'] = 'success';
                continue;
            }
            if (($protection['statut'] ?? '') === 'Approuvee') {
                $debt['collection_status'] = 'À laisser en classe';
                $debt['collection_reason'] = 'Dérogation active jusqu’au ' . date('d/m/Y', strtotime($protection['date_fin']));
                $debt['collection_level'] = 'success';
                continue;
            }
            if (($protection['statut'] ?? '') === 'En_attente') {
                $debt['collection_status'] = 'Décision en attente';
                $debt['collection_reason'] = 'Une demande de dérogation attend validation.';
                $debt['collection_level'] = 'warning';
                continue;
            }

            $missingThreshold = false;
            $aboveThreshold = false;
            foreach ($remainingAmounts as $currency => $amount) {
                if ((float) $amount <= 0.005) {
                    continue;
                }
                if (!array_key_exists(strtoupper((string) $currency), $thresholds)) {
                    $missingThreshold = true;
                    continue;
                }
                if ((float) $amount > (float) $thresholds[strtoupper((string) $currency)]) {
                    $aboveThreshold = true;
                }
            }

            if ($aboveThreshold) {
                $debt['collection_status'] = 'À renvoyer selon le seuil';
                $debt['collection_reason'] = 'Orientation proposée selon les seuils configurés ; décision à confirmer par l’autorité scolaire.';
                $debt['collection_level'] = 'danger';
            } elseif ($missingThreshold) {
                $debt['collection_status'] = 'Seuil à configurer';
                $debt['collection_reason'] = 'Configurez un seuil pour chaque devise concernée.';
                $debt['collection_level'] = 'secondary';
            } else {
                $debt['collection_status'] = 'À laisser en classe';
                $debt['collection_reason'] = 'Le solde reste sous les seuils configurés.';
                $debt['collection_level'] = 'success';
            }
        }
        unset($debt);
    }

    private function fetchSections(array $user): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT DISTINCT s.id, s.nom_section
                FROM sections s
                INNER JOIN classes c ON c.section_id = s.id
                INNER JOIN annees_scolaires a ON a.ecole_id = c.ecole_id AND a.est_active = 1
                INNER JOIN inscriptions i ON i.classe_id = c.id AND i.annee_scolaire_id = a.id
                WHERE 1 = 1';
        $params = [];
        if (($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            $sql .= ' AND c.ecole_id = :ecole_id';
            $params[':ecole_id'] = (int) $user['ecole_id'];
        }
        $sql .= ' ORDER BY s.id ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function fetchActiveFees(array $user): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT DISTINCT fs.id, fs.type_frais, fs.devise, fs.scope
                FROM frais_scolaires fs
                INNER JOIN annees_scolaires s ON s.id = fs.annee_scolaire_id AND s.est_active = 1
                WHERE 1 = 1';
        $params = [];
        if (($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            $sql .= ' AND (fs.ecole_id = :ecole_id OR EXISTS (
                         SELECT 1 FROM classes c WHERE c.ecole_id = :class_ecole_id AND (
                           fs.classe_id = c.id
                           OR (fs.scope = \'option\' AND fs.scope_id = c.option_id)
                           OR (fs.scope = \'section\' AND fs.scope_id = c.section_id)
                         )
                     ))';
            $params[':ecole_id'] = (int) $user['ecole_id'];
            $params[':class_ecole_id'] = (int) $user['ecole_id'];
        }
        $sql .= ' ORDER BY fs.type_frais ASC, fs.devise ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function fetchOutstandingDebts(array $user, array $filters = []): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT MAX(d.id) AS id, d.eleve_id, d.frais_id,
                       MAX(d.montant_initial) AS montant_initial,
                                             COALESCE((SELECT SUM(x.montant) FROM exonerations x
                                                                 WHERE x.eleve_id = d.eleve_id AND x.frais_id = d.frais_id
                                                                     AND x.statut = \'Approuvee\' AND UPPER(x.devise) = UPPER(d.devise)), 0) AS montant_exonere,
                       GREATEST(0, MAX(d.montant_initial) - GREATEST(
                         COALESCE((SELECT SUM(ece.montant) FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id WHERE ce.eleve_id = d.eleve_id AND ece.frais_id = d.frais_id AND ece.type_mouvement = \'CREDIT\'), 0),
                         COALESCE((SELECT SUM(pe.montant_paye) FROM paiements_eleves pe WHERE pe.eleve_id = d.eleve_id AND pe.frais_id = d.frais_id), 0)
                                             ) - COALESCE((SELECT SUM(x.montant) FROM exonerations x
                                                                         WHERE x.eleve_id = d.eleve_id AND x.frais_id = d.frais_id
                                                                             AND x.statut = \'Approuvee\' AND UPPER(x.devise) = UPPER(d.devise)), 0)) AS montant_restant,
                       d.devise, MAX(d.date_creation) AS date_creation,
                       e.matricule, e.nom, e.postnom, e.prenom,
                       fs.type_frais, s.annee AS annee_scolaire,
                       sec.nom_section, c.id AS classe_id, c.nom_classe,
                       CASE WHEN sec.nom_section = \'Secondaire\' THEN COALESCE(o.nom_option, \'Option non définie\') ELSE NULL END AS nom_option
                FROM dettes_eleves d
                INNER JOIN eleves e ON e.id = d.eleve_id
                INNER JOIN frais_scolaires fs ON fs.id = d.frais_id
                  INNER JOIN annees_scolaires s ON s.id = d.annee_scolaire_id AND s.est_active = 1
                  INNER JOIN inscriptions i ON i.eleve_id = e.id AND i.annee_scolaire_id = s.id
                  INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = e.ecole_id
                  LEFT JOIN sections sec ON sec.id = c.section_id
                  LEFT JOIN options o ON o.id = c.option_id
                WHERE 1 = 1';
        $params = [];
            $sql .= ' AND ' . $this->getApplicableFeeScopeSql('fs', 'c');

        if (($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            $sql .= ' AND e.ecole_id = :ecole_id';
            $params[':ecole_id'] = (int) $user['ecole_id'];
        }
        if (!empty($filters['section_id'])) {
            $sql .= ' AND c.section_id = :section_id';
            $params[':section_id'] = $filters['section_id'];
        }
        $sql .= ' AND i.id = (SELECT latest.id FROM inscriptions latest
                              WHERE latest.eleve_id = e.id AND latest.annee_scolaire_id = s.id
                              ORDER BY latest.date_inscription DESC, latest.id DESC LIMIT 1)';
        if (!empty($filters['q'])) {
            $sql .= ' AND CONCAT_WS(\' \', e.nom, e.postnom, e.prenom, e.matricule, fs.type_frais) LIKE :search';
            $params[':search'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['classe'])) {
            $sql .= ' AND c.id = :classe_id';
            $params[':classe_id'] = $filters['classe'];
        }
        if (!empty($filters['frais_id'])) {
            $sql .= ' AND d.frais_id = :frais_id';
            $params[':frais_id'] = $filters['frais_id'];
        }
        if (!empty($filters['devise'])) {
            $sql .= ' AND UPPER(d.devise) = :devise';
            $params[':devise'] = $filters['devise'];
        }

        $sql .= ' GROUP BY d.eleve_id, d.frais_id, d.devise, e.matricule, e.nom, e.postnom, e.prenom, fs.type_frais, s.annee, c.id, c.nom_classe, sec.nom_section, o.nom_option';
        $having = [];
        if ($filters['montant_min'] !== null && $filters['montant_min'] !== '') {
            $having[] = 'montant_restant >= :montant_min';
            $params[':montant_min'] = $filters['montant_min'];
        }
        if ($filters['montant_max'] !== null && $filters['montant_max'] !== '') {
            $having[] = 'montant_restant <= :montant_max';
            $params[':montant_max'] = $filters['montant_max'];
        }
        if ($having) {
            $sql .= ' HAVING ' . implode(' AND ', $having);
        }
        $sql .= ' ORDER BY nom_classe ASC, e.nom ASC, e.postnom ASC, e.prenom ASC, montant_restant DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return array_merge($stmt->fetchAll(), $this->fetchApplicableFeesWithoutDebtRows($user, $filters));
    }

    private function fetchApplicableFeesWithoutDebtRows(array $user, array $filters): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT * FROM (
                          SELECT i.eleve_id, fs.id AS frais_id, fs.montant_total AS montant_initial,
                              COALESCE((SELECT SUM(x.montant) FROM exonerations x
                                  WHERE x.eleve_id = i.eleve_id AND x.frais_id = fs.id
                                    AND x.statut = \'Approuvee\' AND UPPER(x.devise) = UPPER(fs.devise)), 0) AS montant_exonere,
                           GREATEST(0, fs.montant_total - GREATEST(
                             COALESCE((SELECT SUM(ece.montant) FROM ecritures_comptables_eleves ece
                                       INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                                       WHERE ce.eleve_id = i.eleve_id AND ece.frais_id = fs.id
                                         AND ece.type_mouvement = \'CREDIT\'), 0),
                             COALESCE((SELECT SUM(pe.montant_paye) FROM paiements_eleves pe
                                       WHERE pe.eleve_id = i.eleve_id AND pe.frais_id = fs.id), 0)
                                                     ) - COALESCE((SELECT SUM(x.montant) FROM exonerations x
                                                                                 WHERE x.eleve_id = i.eleve_id AND x.frais_id = fs.id
                                                                                     AND x.statut = \'Approuvee\' AND UPPER(x.devise) = UPPER(fs.devise)), 0)) AS montant_restant,
                           COALESCE(fs.devise, \'USD\') AS devise, i.date_inscription AS date_creation,
                           e.matricule, e.nom, e.postnom, e.prenom, fs.type_frais,
                           s.annee AS annee_scolaire, sec.nom_section, c.section_id AS section_id, i.classe_id AS classe_id, c.nom_classe,
                           CASE WHEN sec.nom_section = \'Secondaire\' THEN COALESCE(o.nom_option, \'Option non définie\') ELSE NULL END AS nom_option
                    FROM inscriptions i
                    INNER JOIN classes c ON c.id = i.classe_id
                    LEFT JOIN sections sec ON sec.id = c.section_id
                    LEFT JOIN options o ON o.id = c.option_id
                    INNER JOIN eleves e ON e.id = i.eleve_id
                    INNER JOIN annees_scolaires s ON s.id = i.annee_scolaire_id AND s.est_active = 1
                    INNER JOIN frais_scolaires fs ON fs.annee_scolaire_id = s.id AND ' . $this->getApplicableFeeScopeSql('fs', 'c') . '
                    WHERE i.id = (SELECT latest.id FROM inscriptions latest
                                  WHERE latest.eleve_id = e.id AND latest.annee_scolaire_id = s.id
                                  ORDER BY latest.date_inscription DESC, latest.id DESC LIMIT 1)
                      AND c.ecole_id = e.ecole_id
                      AND NOT EXISTS (SELECT 1 FROM dettes_eleves d
                                      WHERE d.eleve_id = i.eleve_id AND d.frais_id = fs.id
                                        AND d.annee_scolaire_id = s.id)';
        $params = [];
        if (($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            $sql .= ' AND e.ecole_id = :ecole_id';
            $params[':ecole_id'] = (int) $user['ecole_id'];
        }
        if (!empty($filters['section_id'])) {
            $sql .= ' AND c.section_id = :section_id';
            $params[':section_id'] = $filters['section_id'];
        }
        $sql .= ') applicable_debts WHERE 1 = 1';

        if (!empty($filters['q'])) {
            $sql .= ' AND CONCAT_WS(\' \', nom, postnom, prenom, matricule, type_frais) LIKE :search';
            $params[':search'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['classe'])) {
            $sql .= ' AND classe_id = :classe_id';
            $params[':classe_id'] = $filters['classe'];
        }
        if (!empty($filters['frais_id'])) {
            $sql .= ' AND frais_id = :frais_id';
            $params[':frais_id'] = $filters['frais_id'];
        }
        if (!empty($filters['devise'])) {
            $sql .= ' AND UPPER(devise) = :devise';
            $params[':devise'] = $filters['devise'];
        }
        if ($filters['montant_min'] !== null && $filters['montant_min'] !== '') {
            $sql .= ' AND montant_restant >= :montant_min';
            $params[':montant_min'] = $filters['montant_min'];
        }
        if ($filters['montant_max'] !== null && $filters['montant_max'] !== '') {
            $sql .= ' AND montant_restant <= :montant_max';
            $params[':montant_max'] = $filters['montant_max'];
        }
        $sql .= ' ORDER BY nom_classe ASC, nom ASC, postnom ASC, prenom ASC, montant_restant DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function getApplicableFeeScopeSql(string $feeAlias, string $classAlias): string
    {
        return '(' . $feeAlias . '.ecole_id = ' . $classAlias . '.ecole_id AND ('
            . 'COALESCE(' . $feeAlias . '.scope, \'class\') = \'school\' OR '
            . '(COALESCE(' . $feeAlias . '.scope, \'class\') = \'class\' AND ('
            . $feeAlias . '.classe_id = ' . $classAlias . '.id OR ' . $feeAlias . '.scope_id = ' . $classAlias . '.id)) OR '
            . '(COALESCE(' . $feeAlias . '.scope, \'class\') = \'section\' AND ' . $feeAlias . '.scope_id = ' . $classAlias . '.section_id) OR '
            . '(COALESCE(' . $feeAlias . '.scope, \'class\') = \'option\' AND ' . $feeAlias . '.scope_id = ' . $classAlias . '.option_id)'
            . '))';
    }

    private function getExportColumns(): array
    {
        $allowed = ['classe', 'eleve', 'matricule', 'frais', 'initial', 'paye', 'exonere', 'restant'];
        $requested = array_map('trim', explode(',', (string) ($_GET['colonnes'] ?? '')));
        $columns = array_values(array_intersect($allowed, $requested));
        return $columns ?: $allowed;
    }

    private function getExportHeaders(array $columns): array
    {
        return array_map(static fn (string $column): string => [
            'classe' => 'Classe', 'eleve' => 'Élève', 'matricule' => 'Matricule',
            'frais' => 'Frais', 'initial' => 'Montant initial',
            'paye' => 'Montant déjà payé', 'exonere' => 'Montant exonéré', 'restant' => 'Dette restante',
        ][$column], $columns);
    }

    private function getExportRow(array $debt, array $columns): array
    {
        $values = [
            'classe' => ($debt['nom_classe'] ?? 'Classe non définie')
                . (!empty($debt['nom_option']) ? ' - ' . $debt['nom_option'] : ''),
            'eleve' => \App\Core\PersonName::format($debt),
            'matricule' => $debt['matricule'] ?? '',
            'frais' => $debt['frais_details_liste'] ?? ($debt['frais_liste'] ?? ''),
            'initial' => $this->formatAmountsByCurrency($debt['initial_by_currency'] ?? []),
            'paye' => $this->formatAmountsByCurrency($debt['paid_by_currency'] ?? []),
            'exonere' => $this->formatAmountsByCurrency($debt['exonerated_by_currency'] ?? []),
            'restant' => $this->formatAmountsByCurrency($debt['remaining_by_currency'] ?? []),
        ];
        return array_map(static fn (string $column): string => $values[$column], $columns);
    }

    private function aggregateByStudent(array $debts): array
    {
        $grouped = [];
        foreach ($debts as $debt) {
            $studentId = (int) ($debt['eleve_id'] ?? 0);
            $currency = strtoupper(trim((string) ($debt['devise'] ?? 'USD'))) ?: 'USD';
            if (!isset($grouped[$studentId])) {
                $grouped[$studentId] = [
                    'eleve_id' => $studentId,
                    'matricule' => $debt['matricule'] ?? '',
                    'nom' => $debt['nom'] ?? '',
                    'postnom' => $debt['postnom'] ?? '',
                    'prenom' => $debt['prenom'] ?? '',
                    'nom_section' => $debt['nom_section'] ?? 'Section non définie',
                    'section_id' => (int) ($debt['section_id'] ?? 0),
                    'classe_id' => (int) ($debt['classe_id'] ?? 0),
                    'nom_classe' => $debt['nom_classe'] ?? 'Classe non définie',
                    'nom_option' => $debt['nom_option'] ?? null,
                    'frais' => [],
                    'frais_details' => [],
                    'initial_by_currency' => [],
                    'paid_by_currency' => [],
                    'exonerated_by_currency' => [],
                    'remaining_by_currency' => [],
                ];
            }

            $initial = (float) ($debt['montant_initial'] ?? 0);
            $remaining = (float) ($debt['montant_restant'] ?? 0);
            $exonerated = min($initial, max(0, (float) ($debt['montant_exonere'] ?? 0)));
            $paid = max(0, $initial - $remaining - $exonerated);
            $feeName = trim((string) ($debt['type_frais'] ?? 'Frais scolaire')) ?: 'Frais scolaire';
            $grouped[$studentId]['frais'][] = $feeName;
            if (!isset($grouped[$studentId]['frais_details'][$feeName])) {
                $grouped[$studentId]['frais_details'][$feeName] = [
                    'initial_by_currency' => [],
                    'paid_by_currency' => [],
                    'exonerated_by_currency' => [],
                    'remaining_by_currency' => [],
                ];
            }
            $grouped[$studentId]['frais_details'][$feeName]['initial_by_currency'][$currency] = ($grouped[$studentId]['frais_details'][$feeName]['initial_by_currency'][$currency] ?? 0) + $initial;
            $grouped[$studentId]['frais_details'][$feeName]['paid_by_currency'][$currency] = ($grouped[$studentId]['frais_details'][$feeName]['paid_by_currency'][$currency] ?? 0) + $paid;
            $grouped[$studentId]['frais_details'][$feeName]['exonerated_by_currency'][$currency] = ($grouped[$studentId]['frais_details'][$feeName]['exonerated_by_currency'][$currency] ?? 0) + $exonerated;
            $grouped[$studentId]['frais_details'][$feeName]['remaining_by_currency'][$currency] = ($grouped[$studentId]['frais_details'][$feeName]['remaining_by_currency'][$currency] ?? 0) + $remaining;
            $grouped[$studentId]['initial_by_currency'][$currency] = ($grouped[$studentId]['initial_by_currency'][$currency] ?? 0) + $initial;
            $grouped[$studentId]['paid_by_currency'][$currency] = ($grouped[$studentId]['paid_by_currency'][$currency] ?? 0) + $paid;
            $grouped[$studentId]['exonerated_by_currency'][$currency] = ($grouped[$studentId]['exonerated_by_currency'][$currency] ?? 0) + $exonerated;
            $grouped[$studentId]['remaining_by_currency'][$currency] = ($grouped[$studentId]['remaining_by_currency'][$currency] ?? 0) + $remaining;
        }

        foreach ($grouped as &$student) {
            $student['frais'] = array_values(array_unique(array_filter($student['frais'])));
            $student['frais_liste'] = implode(', ', $student['frais']);
            $details = [];
            foreach ($student['frais_details'] as $feeName => $feeAmounts) {
                $details[] = $feeName . ': payé ' . $this->formatAmountsByCurrency($feeAmounts['paid_by_currency'])
                    . ', reste ' . $this->formatAmountsByCurrency($feeAmounts['remaining_by_currency']);
            }
            $student['frais_details_liste'] = implode(' | ', $details);
        }
        unset($student);

        return array_values($grouped);
    }

    private function getFeeColumns(array $debts): array
    {
        $fees = [];
        foreach ($debts as $debt) {
            foreach (array_keys($debt['frais_details'] ?? []) as $feeName) {
                $fees[$feeName] = true;
            }
        }
        return array_keys($fees);
    }

    private function formatAmountsByCurrency(array $amounts): string
    {
        $formatted = [];
        foreach ($amounts as $currency => $amount) {
            $formatted[] = number_format((float) $amount, 2, '.', '') . ' ' . $currency;
        }
        return implode(' | ', $formatted);
    }

    private function buildSummary(array $debts): array
    {
        $summary = [
            'records' => 0,
            'students' => [],
            'initial' => [],
            'exonerated' => [],
            'remaining' => [],
        ];

        foreach ($debts as $debt) {
            $summary['students'][(int) $debt['eleve_id']] = true;
            $summary['records'] += count($debt['frais_details'] ?? []);
            foreach (($debt['initial_by_currency'] ?? []) as $currency => $amount) {
                $summary['initial'][$currency] = ($summary['initial'][$currency] ?? 0) + (float) $amount;
            }
            foreach (($debt['remaining_by_currency'] ?? []) as $currency => $amount) {
                $summary['remaining'][$currency] = ($summary['remaining'][$currency] ?? 0) + (float) $amount;
            }
            foreach (($debt['exonerated_by_currency'] ?? []) as $currency => $amount) {
                $summary['exonerated'][$currency] = ($summary['exonerated'][$currency] ?? 0) + (float) $amount;
            }
        }

        $summary['students'] = count($summary['students']);
        return $summary;
    }

    private function renderViewToString(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        require dirname(__DIR__) . '/Views/' . $view . '.php';
        return (string) ob_get_clean();
    }
}
