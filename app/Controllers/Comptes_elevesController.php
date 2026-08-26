<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

class Comptes_elevesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $db = Database::getConnection();
        $params = [];
        $schoolFilter = '';
        if (($user['role'] ?? '') !== 'super_admin' && !empty($user['ecole_id'])) {
            $schoolFilter = 'WHERE (el.ecole_id = :ecole OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole))';
            $params[':ecole'] = (int) $user['ecole_id'];
        }

        $sql = 'SELECT el.id AS eleve_id, el.nom, el.postnom, el.prenom, COALESCE(SUM(ce.solde_debiteur),0) AS solde FROM eleves el LEFT JOIN comptes_eleves ce ON ce.eleve_id = el.id INNER JOIN annees_scolaires a ON ce.annee_scolaire_id = a.id AND a.est_active = 1 ' . $schoolFilter . ' GROUP BY el.id ORDER BY solde DESC, el.nom ASC';

        try {
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, \PDO::PARAM_INT);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $rows = [];
        }

        $this->view('comptes_eleves/index', [
            'title' => APP_NAME . ' - Comptes élèves',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'accounts' => $rows,
        ]);
    }
}
