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
        $debts = $this->fetchOutstandingDebts($user);
        $summary = $this->buildSummary($debts);

        $this->view('recouvrements/index', [
            'title' => APP_NAME . ' - Recouvrements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'debts' => $debts,
            'summary' => $summary,
        ]);
    }

    private function fetchOutstandingDebts(array $user): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT d.id, d.eleve_id, d.montant_initial, d.montant_restant, d.devise, d.date_creation,
                       e.matricule, e.nom, e.postnom, e.prenom,
                       fs.type_frais, s.annee AS annee_scolaire
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

        $sql .= ' ORDER BY d.montant_restant DESC, e.nom ASC, e.postnom ASC, e.prenom ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
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
