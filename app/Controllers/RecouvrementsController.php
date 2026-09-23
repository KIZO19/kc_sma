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
        $debts = $this->fetchOutstandingDebts($user, $filters);
        $summary = $this->buildSummary($debts);

        $this->view('recouvrements/index', [
            'title' => APP_NAME . ' - Recouvrements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'debts' => $debts,
            'summary' => $summary,
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
        $debts = $this->fetchOutstandingDebts($user, $filters);
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
            ]);
            if (class_exists('\\Dompdf\\Dompdf')) {
                $dompdf = new \\Dompdf\\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
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
            $sql .= ' AND CONCAT_WS(\' \", e.nom, e.postnom, e.prenom, e.matricule, fs.type_frais) LIKE :search';
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
        $allowed = ['classe', 'eleve', 'matricule', 'frais', 'annee', 'initial', 'paye', 'restant'];
        $requested = array_map('trim', explode(',', (string) ($_GET['colonnes'] ?? '')));
        $columns = array_values(array_intersect($allowed, $requested));
        return $columns ?: $allowed;
    }

    private function getExportHeaders(array $columns): array
    {
        return array_map(static fn (string $column): string => [
            'classe' => 'Classe', 'eleve' => 'Élève', 'matricule' => 'Matricule',
            'frais' => 'Frais', 'annee' => 'Année scolaire', 'initial' => 'Montant initial',
            'paye' => 'Montant déjà payé', 'restant' => 'Dette restante',
        ][$column], $columns);
    }

    private function getExportRow(array $debt, array $columns): array
    {
        $initial = (float) ($debt['montant_initial'] ?? 0);
        $remaining = (float) ($debt['montant_restant'] ?? 0);
        $values = [
            'classe' => $debt['nom_classe'] ?? 'Classe non définie',
            'eleve' => trim(($debt['nom'] ?? '') . ' ' . ($debt['postnom'] ?? '') . ' ' . ($debt['prenom'] ?? '')),
            'matricule' => $debt['matricule'] ?? '',
            'frais' => $debt['type_frais'] ?? '',
            'annee' => $debt['annee_scolaire'] ?? '',
            'initial' => number_format($initial, 2, '.', '') . ' ' . ($debt['devise'] ?? 'USD'),
            'paye' => number_format(max(0, $initial - $remaining), 2, '.', '') . ' ' . ($debt['devise'] ?? 'USD'),
            'restant' => number_format($remaining, 2, '.', '') . ' ' . ($debt['devise'] ?? 'USD'),
        ];
        return array_map(static fn (string $column): string => $values[$column], $columns);
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
            $currency = strtoupper(trim((string) ($debt['devise'] ?? 'USD'))) ?: 'USD';
            $summary['students'][(int) $debt['eleve_id']] = true;
            $summary['initial'][$currency] = ($summary['initial'][$currency] ?? 0) + (float) $debt['montant_initial'];
            $summary['remaining'][$currency] = ($summary['remaining'][$currency] ?? 0) + (float) $debt['montant_restant'];
        }

        $summary['students'] = count($summary['students']);
        return $summary;
    }
}
