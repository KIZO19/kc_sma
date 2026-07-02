<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use PDO;

class ActivitiesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $activities = $this->fetchRecentActivities(50);

        $this->view('activities/index', [
            'title' => APP_NAME . ' - Activités',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'activities' => $activities,
        ]);
    }

    private function fetchRecentActivities(int $limit = 50): array
    {
        try {
            $db = Database::getConnection();

            $queries = [
                "SELECT id, nom_etablissement AS title, 'école' AS type, date_creation_compte AS created_at, '/ecoles' AS link FROM ecoles",
                "SELECT id, nom_complet AS title, role AS type, created_at AS created_at, '/comptes' AS link FROM utilisateurs",
                "SELECT id, CONCAT(nom,' ',prenom) AS title, 'agent' AS type, created_at AS created_at, '/agents' AS link FROM agents",
                "SELECT id, CONCAT(prenom,' ',nom) AS title, 'élève' AS type, created_at AS created_at, '/eleves' AS link FROM eleves",
                "SELECT id, CONCAT('Paiement #', id) AS title, 'paiement' AS type, date_operation AS created_at, '/paiements' AS link FROM paiements",
            ];

            $union = implode(' UNION ALL ', $queries) . ' ORDER BY created_at DESC LIMIT ' . (int) $limit;
            $stmt = $db->prepare($union);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
