<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DetteEleve;
use App\Models\Eleve;
use App\Models\User;

class ElevesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $students = $ecoleId > 0 ? Eleve::getAllBySchool($ecoleId) : Eleve::getAll();

        $this->view('eleves/index', [
            'title' => APP_NAME . ' - Élèves',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'students' => $students,
        ]);
    }

    public function show(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $eleveId = (int) ($_GET['id'] ?? 0);

        if ($eleveId <= 0) {
            header('Location: ' . BASE_URL . '/eleves');
            exit;
        }

        // Ensure the eleve belongs to the same school unless super_admin
        if (($user['role'] ?? '') !== 'super_admin' && $ecoleId > 0 && !\App\Models\Eleve::findByIdAndSchool($eleveId, $ecoleId)) {
            header('Location: ' . BASE_URL . '/error/accessDenied');
            exit;
        }

        $eleve = \App\Models\Eleve::findById($eleveId);
        if (!$eleve) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        $compte = \App\Models\Eleve::getAccount($eleveId);
        $ecritures = \App\Models\Eleve::getAccountingEntries($eleveId);
        $totalPaid = 0.0;
        foreach ($ecritures as $ec) {
            $amount = (float) ($ec['montant'] ?? 0);
            if (strtoupper((string) ($ec['type_mouvement'] ?? '')) === 'CREDIT') {
                $totalPaid += $amount;
            }
        }
        $dettes = DetteEleve::getOutstandingByEleve($eleveId);
        $totalPaidByCurrency = $this->getPaidTotalsByCurrency($eleveId);
        $totalDebtByCurrency = DetteEleve::getTotalOutstandingGroupedByDevise($eleveId);
        if (empty($totalDebtByCurrency)) {
            $totalDebtByCurrency = DetteEleve::computeOutstandingFromApplicableFees($eleveId);
        }
        $entryTotalsByCurrency = $this->getEntryTotalsByCurrency($eleveId);
        $notes = \App\Models\Eleve::getNotes($eleveId);
        $discipline = \App\Models\Eleve::getDiscipline($eleveId);

        $this->view('eleves/show', [
            'title' => APP_NAME . ' - Fiche élève',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'eleve' => $eleve,
            'compte' => $compte,
            'ecritures' => $ecritures,
            'dettes' => $dettes,
            'totalPaid' => $totalPaid,
            'totalPaidByCurrency' => $totalPaidByCurrency,
            'totalDebtByCurrency' => $totalDebtByCurrency,
            'entryTotalsByCurrency' => $entryTotalsByCurrency,
            'notes' => $notes,
            'discipline' => $discipline,
        ]);
    }

    private function getPaidTotalsByCurrency(int $eleveId): array
    {
        $db = \App\Core\Database::getConnection();
        $totals = [];
        $queries = [
            'SELECT COALESCE(fs.devise, \'USD\') AS devise, SUM(ece.montant) AS total
             FROM ecritures_comptables_eleves ece
             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
             LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id
             WHERE ce.eleve_id = :eleve AND ece.type_mouvement = \'CREDIT\'
             GROUP BY COALESCE(fs.devise, \'USD\')',
            'SELECT COALESCE(fs.devise, \'USD\') AS devise, SUM(pe.montant_paye) AS total
             FROM paiements_eleves pe
             LEFT JOIN frais_scolaires fs ON fs.id = pe.frais_id
             WHERE pe.eleve_id = :eleve
             GROUP BY COALESCE(fs.devise, \'USD\')',
        ];

        foreach ($queries as $query) {
            try {
                $stmt = $db->prepare($query);
                $stmt->execute([':eleve' => $eleveId]);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $currency = strtoupper(trim($row['devise'] ?? 'USD')) ?: 'USD';
                    $totals[$currency] = max($totals[$currency] ?? 0.0, (float) ($row['total'] ?? 0));
                }
            } catch (\Throwable $e) {
                // Keep the totals available if a legacy table is unavailable.
            }
        }
        ksort($totals);
        return $totals;
    }

    private function getEntryTotalsByCurrency(int $eleveId): array
    {
        $db = \App\Core\Database::getConnection();
        $totals = [];
        $queries = [
            'SELECT ece.type_mouvement, COALESCE(fs.devise, \'USD\') AS devise, SUM(ece.montant) AS total
             FROM ecritures_comptables_eleves ece
             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
             LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id
             WHERE ce.eleve_id = :eleve GROUP BY ece.type_mouvement, COALESCE(fs.devise, \'USD\')',
            'SELECT \'CREDIT\' AS type_mouvement, COALESCE(fs.devise, \'USD\') AS devise, SUM(pe.montant_paye) AS total
             FROM paiements_eleves pe
             LEFT JOIN frais_scolaires fs ON fs.id = pe.frais_id
             WHERE pe.eleve_id = :eleve GROUP BY COALESCE(fs.devise, \'USD\')',
        ];

        foreach ($queries as $query) {
            try {
                $stmt = $db->prepare($query);
                $stmt->execute([':eleve' => $eleveId]);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $type = strtoupper((string) ($row['type_mouvement'] ?? ''));
                    $currency = strtoupper(trim($row['devise'] ?? 'USD')) ?: 'USD';
                    if ($type === '') {
                        continue;
                    }
                    $totals[$currency][$type] = max($totals[$currency][$type] ?? 0.0, (float) ($row['total'] ?? 0));
                }
            } catch (\Throwable $e) {
                // Keep accounting totals available when the legacy table is absent.
            }
        }
        ksort($totals);
        return $totals;
    }

    
}
