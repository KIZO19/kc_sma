<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
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
        $debts = $this->aggregateByStudent($this->fetchOutstandingDebts($user, $filters));
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
        ]);
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
            'classe' => trim((string) ($_GET['classe'] ?? '')),
            'devise' => strtoupper(trim((string) ($_GET['devise'] ?? ''))),
            'montant_min' => is_numeric($min) ? max(0, (float) $min) : null,
            'montant_max' => is_numeric($max) ? max(0, (float) $max) : null,
        ];
    }

    private function fetchClasses(array $user): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT DISTINCT c.id, c.nom_classe FROM classes c';
        $params = [];
        if (($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            $sql .= ' WHERE c.ecole_id = :ecole_id';
            $params[':ecole_id'] = (int) $user['ecole_id'];
        }
        $sql .= ' ORDER BY c.nom_classe ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function fetchOutstandingDebts(array $user, array $filters = []): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT d.id, d.eleve_id, d.frais_id, d.montant_initial, d.montant_restant, d.devise, d.date_creation,
                       e.matricule, e.nom, e.postnom, e.prenom,
                       fs.type_frais, s.annee AS annee_scolaire,
                       COALESCE((SELECT c.nom_classe
                                 FROM inscriptions i
                                 INNER JOIN classes c ON c.id = i.classe_id
                                 WHERE i.eleve_id = e.id
                                 ORDER BY i.date_inscription DESC, i.id DESC
                                 LIMIT 1), \'Classe non définie\') AS nom_classe
                FROM dettes_eleves d
                INNER JOIN eleves e ON e.id = d.eleve_id
                INNER JOIN frais_scolaires fs ON fs.id = d.frais_id
                LEFT JOIN annees_scolaires s ON s.id = d.annee_scolaire_id
                WHERE d.montant_restant > 0';
        $params = [];

        if (($user['role'] ?? '') !== 'super_admin' && (int) ($user['ecole_id'] ?? 0) > 0) {
            $sql .= ' AND e.ecole_id = :ecole_id';
            $params[':ecole_id'] = (int) $user['ecole_id'];
        }

        if (!empty($filters['q'])) {
            $sql .= ' AND CONCAT_WS(\' \', e.nom, e.postnom, e.prenom, e.matricule, fs.type_frais) LIKE :search';
            $params[':search'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['classe'])) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions fi INNER JOIN classes fc ON fc.id = fi.classe_id WHERE fi.eleve_id = e.id AND fc.nom_classe = :classe)';
            $params[':classe'] = $filters['classe'];
        }
        if (!empty($filters['devise'])) {
            $sql .= ' AND UPPER(d.devise) = :devise';
            $params[':devise'] = $filters['devise'];
        }
        if ($filters['montant_min'] !== null && $filters['montant_min'] !== '') {
            $sql .= ' AND d.montant_restant >= :montant_min';
            $params[':montant_min'] = $filters['montant_min'];
        }
        if ($filters['montant_max'] !== null && $filters['montant_max'] !== '') {
            $sql .= ' AND d.montant_restant <= :montant_max';
            $params[':montant_max'] = $filters['montant_max'];
        }

        $sql .= ' ORDER BY nom_classe ASC, e.nom ASC, e.postnom ASC, e.prenom ASC, d.montant_restant DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private function getExportColumns(): array
    {
        $allowed = ['classe', 'eleve', 'matricule', 'frais', 'initial', 'paye', 'restant'];
        $requested = array_map('trim', explode(',', (string) ($_GET['colonnes'] ?? '')));
        $columns = array_values(array_intersect($allowed, $requested));
        return $columns ?: $allowed;
    }

    private function getExportHeaders(array $columns): array
    {
        return array_map(static fn (string $column): string => [
            'classe' => 'Classe', 'eleve' => 'Élève', 'matricule' => 'Matricule',
            'frais' => 'Frais', 'initial' => 'Montant initial',
            'paye' => 'Montant déjà payé', 'restant' => 'Dette restante',
        ][$column], $columns);
    }

    private function getExportRow(array $debt, array $columns): array
    {
        $values = [
            'classe' => $debt['nom_classe'] ?? 'Classe non définie',
            'eleve' => trim(($debt['nom'] ?? '') . ' ' . ($debt['postnom'] ?? '') . ' ' . ($debt['prenom'] ?? '')),
            'matricule' => $debt['matricule'] ?? '',
            'frais' => $debt['frais_details_liste'] ?? ($debt['frais_liste'] ?? ''),
            'initial' => $this->formatAmountsByCurrency($debt['initial_by_currency'] ?? []),
            'paye' => $this->formatAmountsByCurrency($debt['paid_by_currency'] ?? []),
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
                    'nom_classe' => $debt['nom_classe'] ?? 'Classe non définie',
                    'frais' => [],
                    'frais_details' => [],
                    'initial_by_currency' => [],
                    'paid_by_currency' => [],
                    'remaining_by_currency' => [],
                ];
            }

            $initial = (float) ($debt['montant_initial'] ?? 0);
            $remaining = (float) ($debt['montant_restant'] ?? 0);
            $feeName = trim((string) ($debt['type_frais'] ?? 'Frais scolaire')) ?: 'Frais scolaire';
            $grouped[$studentId]['frais'][] = $feeName;
            if (!isset($grouped[$studentId]['frais_details'][$feeName])) {
                $grouped[$studentId]['frais_details'][$feeName] = [
                    'initial_by_currency' => [],
                    'paid_by_currency' => [],
                    'remaining_by_currency' => [],
                ];
            }
            $grouped[$studentId]['frais_details'][$feeName]['initial_by_currency'][$currency] = ($grouped[$studentId]['frais_details'][$feeName]['initial_by_currency'][$currency] ?? 0) + $initial;
            $grouped[$studentId]['frais_details'][$feeName]['paid_by_currency'][$currency] = ($grouped[$studentId]['frais_details'][$feeName]['paid_by_currency'][$currency] ?? 0) + max(0, $initial - $remaining);
            $grouped[$studentId]['frais_details'][$feeName]['remaining_by_currency'][$currency] = ($grouped[$studentId]['frais_details'][$feeName]['remaining_by_currency'][$currency] ?? 0) + $remaining;
            $grouped[$studentId]['initial_by_currency'][$currency] = ($grouped[$studentId]['initial_by_currency'][$currency] ?? 0) + $initial;
            $grouped[$studentId]['paid_by_currency'][$currency] = ($grouped[$studentId]['paid_by_currency'][$currency] ?? 0) + max(0, $initial - $remaining);
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
            'records' => count($debts),
            'students' => [],
            'initial' => [],
            'remaining' => [],
        ];

        foreach ($debts as $debt) {
            $summary['students'][(int) $debt['eleve_id']] = true;
            foreach (($debt['initial_by_currency'] ?? []) as $currency => $amount) {
                $summary['initial'][$currency] = ($summary['initial'][$currency] ?? 0) + (float) $amount;
            }
            foreach (($debt['remaining_by_currency'] ?? []) as $currency => $amount) {
                $summary['remaining'][$currency] = ($summary['remaining'][$currency] ?? 0) + (float) $amount;
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
