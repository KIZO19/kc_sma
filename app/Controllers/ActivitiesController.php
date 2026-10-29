<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\ActivityLog;
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
        $prioritySummary = $this->fetchPrioritySummary();

        $this->view('activities/index', [
            'title' => APP_NAME . ' - Activités',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'activities' => $activities,
            'prioritySummary' => $prioritySummary,
        ]);
    }

    private function fetchRecentActivities(int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));
        ActivityLog::ensureTable();
        $db = Database::getConnection();
        $query = "
            SELECT a.id, a.title, a.details, a.action AS type, a.created_at,
                   actor.nom_complet AS actor_name,
                   CASE a.target_type
                       WHEN 'school' THEN CONCAT('/ecoles/edit?id=', a.target_id)
                       WHEN 'user' THEN CONCAT('/utilisateurs/editAccount?id=', a.target_id)
                       WHEN 'school_subscription' THEN CONCAT('/ecoles/subscriptions?ecole_id=', a.target_id)
                       WHEN 'subscription' THEN '/ecoles/subscriptions'
                       ELSE '/activities'
                   END AS link
            FROM super_admin_activities a
            LEFT JOIN utilisateurs actor ON actor.id = a.actor_user_id
            UNION ALL
            SELECT e.id, e.nom_etablissement AS title,
                   CASE e.statut_systeme
                       WHEN 'En_Attente' THEN 'En attente de validation'
                       WHEN 'Suspendu' THEN 'Accès suspendu'
                       ELSE 'École active'
                   END AS details,
                   CASE e.statut_systeme
                       WHEN 'En_Attente' THEN 'ecole_en_attente'
                       WHEN 'Suspendu' THEN 'ecole_suspendue'
                       ELSE 'ecole'
                   END AS type,
                   e.date_creation_compte AS created_at,
                   NULL AS actor_name,
                   CONCAT('/ecoles/edit?id=', e.id) AS link
            FROM ecoles e
            UNION ALL
            SELECT a.id, e.nom_etablissement AS title,
                   CONCAT(
                       COALESCE(p.nom_plan, 'Essai gratuit'),
                       ' · ',
                       CASE
                           WHEN a.statut_abonnement = 'Resilie' THEN 'Résilié'
                           WHEN a.statut_abonnement = 'Expire' OR a.date_fin < NOW() THEN 'Expiré'
                           WHEN a.date_fin <= DATE_ADD(NOW(), INTERVAL 14 DAY) THEN 'Expire dans 14 jours ou moins'
                           ELSE 'Actif'
                       END,
                       ' · fin le ',
                       DATE_FORMAT(a.date_fin, '%d/%m/%Y')
                   ) AS details,
                   CASE
                       WHEN a.statut_abonnement = 'Resilie' THEN 'abonnement_resilie'
                       WHEN a.statut_abonnement = 'Expire' OR a.date_fin < NOW() THEN 'abonnement_expire'
                       WHEN a.date_fin <= DATE_ADD(NOW(), INTERVAL 14 DAY) THEN 'abonnement_echeance'
                       ELSE 'abonnement'
                   END AS type,
                   a.date_debut AS created_at,
                   NULL AS actor_name,
                   CONCAT('/ecoles/subscriptions?ecole_id=', e.id) AS link
            FROM abonnements_ecoles a
            INNER JOIN ecoles e ON e.id = a.ecole_id
            LEFT JOIN plans_abonnement p ON p.id = a.plan_id
            ORDER BY created_at DESC, type ASC, id DESC
            LIMIT {$limit}";

        $stmt = $db->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchPrioritySummary(): array
    {
        SubscriptionRequest::ensureTable();
        $query = "
            SELECT
                (SELECT COUNT(*) FROM ecoles WHERE statut_systeme = 'En_Attente') AS pending_schools,
                (SELECT COUNT(*) FROM ecoles WHERE statut_systeme = 'Suspendu') AS suspended_schools,
                (SELECT COUNT(*) FROM utilisateurs WHERE role = 'ecole_admin' AND statut = 'Actif') AS active_school_admins,
                (SELECT COUNT(*) FROM utilisateurs WHERE role = 'ecole_admin' AND statut = 'Inactif') AS inactive_school_admins,
                (SELECT COUNT(*) FROM demandes_abonnement_ecoles WHERE statut = 'En_attente') AS pending_subscription_requests,
                (SELECT COUNT(*) FROM abonnements_ecoles
                 a
                 WHERE a.statut_abonnement = 'Actif'
                   AND a.date_debut <= NOW()
                   AND a.date_fin >= NOW()
                   AND a.date_fin <= DATE_ADD(NOW(), INTERVAL 14 DAY)
                   AND NOT EXISTS (
                       SELECT 1 FROM abonnements_ecoles newer
                       WHERE newer.ecole_id = a.ecole_id
                         AND (newer.date_debut > a.date_debut
                              OR (newer.date_debut = a.date_debut AND newer.id > a.id))
                   )) AS expiring_subscriptions,
                (SELECT COUNT(*) FROM abonnements_ecoles a
                 WHERE (a.statut_abonnement = 'Expire'
                        OR (a.statut_abonnement = 'Actif' AND a.date_fin < NOW()))
                   AND NOT EXISTS (
                       SELECT 1 FROM abonnements_ecoles newer
                       WHERE newer.ecole_id = a.ecole_id
                         AND (newer.date_debut > a.date_debut
                              OR (newer.date_debut = a.date_debut AND newer.id > a.id))
                   )) AS expired_subscriptions";

        $summary = Database::getConnection()->query($query)->fetch(PDO::FETCH_ASSOC);

        return $summary ?: [
            'pending_schools' => 0,
            'suspended_schools' => 0,
            'active_school_admins' => 0,
            'inactive_school_admins' => 0,
            'pending_subscription_requests' => 0,
            'expiring_subscriptions' => 0,
            'expired_subscriptions' => 0,
        ];
    }
}
