<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Ecole;
use App\Models\User;
use PDO;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireAssignedSchool();

        $user = Auth::user();
        $user = Auth::refresh() ?: $user;

        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $dashboardData = $this->buildDashboardData($user, $role);

        $this->view('dashboard/index', [
            'title' => APP_NAME,
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'dashboardData' => $dashboardData,
        ]);
    }

    private function buildDashboardData(array $user, string $role): array
    {
        $stats = $this->getStatsForRole($role);
        $chart = $this->getChartSeries($role);
        $table = $this->getTableDataForRole($role);
        $insights = $this->getRoleInsights($role, $user);

        return [
            'stats' => $stats,
            'chart' => $chart,
            'table' => $table,
            'insights' => $insights,
        ];
    }

    private function getStatsForRole(string $role): array
    {
        $schoolCount = $this->countTable('ecoles');
        $studentCount = $this->countTable('eleves');
        $teacherCount = $this->countTable('agents');
        $parentCount = $this->countTable('parents');
        $classCount = $this->countTable('classes');
        $paymentCount = $this->countTable('paiements');
        $eventCount = $this->countTable('evenements');

        return match ($role) {
            'super_admin', 'ecole_admin' => [
                ['title' => 'Écoles actives', 'value' => Ecole::countBySystemStatus('Actif'), 'icon' => 'bi-building', 'bg' => 'bg-primary', 'hint' => 'Établissements autorisés'],
                ['title' => 'Comptes en attente', 'value' => Ecole::countBySystemStatus('En_Attente'), 'icon' => 'bi-clock', 'bg' => 'bg-warning', 'hint' => 'Demandes à valider'],
                ['title' => 'Abonnements actifs', 'value' => Ecole::countSubscriptionByStatus('Actif'), 'icon' => 'bi-check-circle', 'bg' => 'bg-success', 'hint' => 'Abonnements en cours'],
                ['title' => 'Abonnements expirés', 'value' => Ecole::countSubscriptionByStatus('Expire'), 'icon' => 'bi-exclamation-triangle', 'bg' => 'bg-danger', 'hint' => 'Actions nécessaires'],
            ],
            'comptable_école' => [
                ['title' => 'Revenus', 'value' => 'FCFA 4,8M', 'icon' => 'bi-wallet2', 'bg' => 'bg-success', 'hint' => 'Mois en cours'],
                ['title' => 'Paiements', 'value' => $paymentCount, 'icon' => 'bi-currency-dollar', 'bg' => 'bg-info', 'hint' => 'Reçus cette semaine'],
                ['title' => 'Écoles', 'value' => $schoolCount, 'icon' => 'bi-bank', 'bg' => 'bg-primary', 'hint' => 'Structures suivies'],
                ['title' => 'Événements', 'value' => $eventCount, 'icon' => 'bi-calendar-event', 'bg' => 'bg-secondary', 'hint' => 'Planifiés'],
            ],
            'sec_école' => [
                ['title' => 'Inscriptions', 'value' => $studentCount, 'icon' => 'bi-person-plus', 'bg' => 'bg-primary', 'hint' => 'Demandes à traiter'],
                ['title' => 'Parents', 'value' => $parentCount, 'icon' => 'bi-people', 'bg' => 'bg-success', 'hint' => 'Contacts actifs'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-diagram-3', 'bg' => 'bg-info', 'hint' => 'Disponibles'],
                ['title' => 'Événements', 'value' => $eventCount, 'icon' => 'bi-calendar3', 'bg' => 'bg-warning', 'hint' => 'À venir'],
            ],
            'enseignant_école' => [
                ['title' => 'Cours', 'value' => $classCount, 'icon' => 'bi-book', 'bg' => 'bg-primary', 'hint' => 'Programmes actifs'],
                ['title' => 'Présences', 'value' => '92%', 'icon' => 'bi-calendar-check', 'bg' => 'bg-success', 'hint' => 'Taux moyen'],
                ['title' => 'Notes', 'value' => '18', 'icon' => 'bi-pencil-square', 'bg' => 'bg-info', 'hint' => 'À saisir'],
                ['title' => 'Élèves', 'value' => $studentCount, 'icon' => 'bi-person-badge', 'bg' => 'bg-warning', 'hint' => 'Suivis'],
            ],
            'eleve_ecole' => [
                ['title' => 'Moyenne', 'value' => '15,4/20', 'icon' => 'bi-bar-chart-line', 'bg' => 'bg-primary', 'hint' => 'Dernier trimestre'],
                ['title' => 'Présences', 'value' => '96%', 'icon' => 'bi-check2-square', 'bg' => 'bg-success', 'hint' => 'Ce mois'],
                ['title' => 'Paiements', 'value' => '2', 'icon' => 'bi-wallet2', 'bg' => 'bg-info', 'hint' => 'En attente'],
                ['title' => 'Cours', 'value' => $classCount, 'icon' => 'bi-calendar2-week', 'bg' => 'bg-warning', 'hint' => 'Programmes'],
            ],
            'parent_ecole' => [
                ['title' => 'Enfants', 'value' => '2', 'icon' => 'bi-people', 'bg' => 'bg-primary', 'hint' => 'Suivis'],
                ['title' => 'Bulletins', 'value' => '3', 'icon' => 'bi-file-earmark-text', 'bg' => 'bg-success', 'hint' => 'Disponibles'],
                ['title' => 'Paiements', 'value' => '1', 'icon' => 'bi-currency-dollar', 'bg' => 'bg-info', 'hint' => 'À venir'],
                ['title' => 'Messages', 'value' => '4', 'icon' => 'bi-chat-dots', 'bg' => 'bg-warning', 'hint' => 'Nouveaux'],
            ],
            default => [
                ['title' => 'Tableau', 'value' => 'Actif', 'icon' => 'bi-speedometer2', 'bg' => 'bg-primary', 'hint' => 'Vue principale'],
                ['title' => 'Élèves', 'value' => $studentCount, 'icon' => 'bi-person-badge', 'bg' => 'bg-success', 'hint' => 'Inscrits'],
                ['title' => 'Écoles', 'value' => $schoolCount, 'icon' => 'bi-building', 'bg' => 'bg-info', 'hint' => 'Disponibles'],
                ['title' => 'Agents', 'value' => $teacherCount, 'icon' => 'bi-people-fill', 'bg' => 'bg-warning', 'hint' => 'Actifs'],
            ],
        };
    }

    private function getChartSeries(string $role): array
    {
        $labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui'];

        $series = match ($role) {
            'super_admin', 'ecole_admin' => [40, 52, 47, 56, 62, 68],
            'comptable_école' => [18, 24, 21, 29, 33, 41],
            'enseignant_école' => [78, 81, 84, 83, 88, 91],
            'eleve_ecole' => [11, 13, 12, 15, 14, 16],
            'parent_ecole' => [2, 3, 2, 4, 5, 4],
            'sec_école' => [12, 14, 16, 17, 19, 22],
            default => [12, 19, 15, 24, 18, 30],
        };

        $chartConfig = match ($role) {
            'super_admin', 'ecole_admin' => ['title' => 'Performance des écoles', 'label' => 'Évolution scolaire', 'border' => '#0d6efd', 'background' => 'rgba(13, 110, 253, 0.18)'],
            'comptable_école' => ['title' => 'Flux financier', 'label' => 'Paiements et revenus', 'border' => '#198754', 'background' => 'rgba(25, 135, 84, 0.18)'],
            'sec_école' => ['title' => 'Inscriptions et effectifs', 'label' => 'Nouveaux dossiers', 'border' => '#0dcaf0', 'background' => 'rgba(13, 202, 240, 0.18)'],
            'enseignant_école' => ['title' => 'Suivi des présences', 'label' => 'Taux de présence', 'border' => '#ffc107', 'background' => 'rgba(255, 193, 7, 0.18)'],
            'eleve_ecole' => ['title' => 'Progression des notes', 'label' => 'Moyenne trimestrielle', 'border' => '#6610f2', 'background' => 'rgba(102, 16, 242, 0.18)'],
            'parent_ecole' => ['title' => 'Suivi des enfants', 'label' => 'Évolution scolaire', 'border' => '#6f42c1', 'background' => 'rgba(111, 66, 193, 0.18)'],
            default => ['title' => 'Performance', 'label' => 'Indicateur', 'border' => '#0d6efd', 'background' => 'rgba(13, 110, 253, 0.18)'],
        };

        return [
            'title' => $chartConfig['title'],
            'label' => $chartConfig['label'],
            'labels' => $labels,
            'values' => $series,
            'borderColor' => $chartConfig['border'],
            'backgroundColor' => $chartConfig['background'],
        ];
    }

    private function getTableDataForRole(string $role): array
    {
        $rows = $this->getRecentRows('ecoles', 6);

        $columns = [
            ['key' => 'name', 'label' => 'Nom'],
            ['key' => 'status', 'label' => 'Statut'],
            ['key' => 'updated', 'label' => 'Mise à jour'],
        ];

        $tableTitle = 'Données récentes';
        $emptyMessage = 'Aucune donnée disponible pour le moment.';

        if ($role === 'enseignant_école') {
            $tableTitle = 'Mes classes et évaluations';
            $rows = [
                ['name' => '3e A - Mathématiques', 'status' => 'En cours', 'updated' => 'Il y a 2h'],
                ['name' => '2e B - Physique', 'status' => 'À préparer', 'updated' => 'Hier'],
                ['name' => '1re C - SVT', 'status' => 'Validé', 'updated' => 'Il y a 1j'],
            ];
        } elseif ($role === 'eleve_ecole') {
            $tableTitle = 'Mes dernières évaluations';
            $rows = [
                ['name' => 'Mathématiques', 'status' => '15.5/20', 'updated' => 'Cette semaine'],
                ['name' => 'Français', 'status' => '16.0/20', 'updated' => 'Cette semaine'],
                ['name' => 'Histoire', 'status' => '14.8/20', 'updated' => 'La semaine dernière'],
            ];
        } elseif ($role === 'parent_ecole') {
            $tableTitle = 'Suivi de mes enfants';
            $rows = [
                ['name' => 'Mina Diop', 'status' => 'Bonne progression', 'updated' => 'Aujourd’hui'],
                ['name' => 'Yann Diop', 'status' => 'Paiement à jour', 'updated' => 'Hier'],
            ];
        } elseif ($role === 'comptable_école') {
            $tableTitle = 'Transactions récentes';
            $rows = [
                ['name' => 'Paiement école A', 'status' => 'Validé', 'updated' => 'Il y a 30 min'],
                ['name' => 'Facture 2026-04', 'status' => 'En attente', 'updated' => 'Il y a 3h'],
            ];
        } elseif ($role === 'sec_école') {
            $tableTitle = 'Demandes d’inscription';
            $rows = [
                ['name' => 'Awa Sarr', 'status' => 'Nouvelle', 'updated' => 'À l’instant'],
                ['name' => 'Moussa Diallo', 'status' => 'À confirmer', 'updated' => 'Hier'],
            ];
        }

        return [
            'title' => $tableTitle,
            'columns' => $columns,
            'rows' => $rows,
            'emptyMessage' => $emptyMessage,
        ];
    }

    private function getRoleInsights(string $role, array $user): array
    {
        return match ($role) {
            'super_admin', 'ecole_admin' => [
                'Vue globale des établissements et des performances de l’ensemble des écoles.',
                'Suivi rapide des agents, des effectifs et des paiements depuis un seul tableau de bord.',
            ],
            'comptable_école' => [
                'Visualisation de la trésorerie et des paiements à traiter pour chaque école.',
                'Accès rapide aux factures, aux états de caisse et aux suivis financiers.',
            ],
            'sec_école' => [
                'Gestion simplifiée des inscriptions, des parents et des élèves.',
                'Répartition claire des dossiers à traiter et des rendez-vous à planifier.',
            ],
            'enseignant_école' => [
                'Suivi des cours, des présences et des notes à saisir en un seul endroit.',
                'Vue plus rapide des évaluations à préparer pour chaque classe.',
            ],
            'eleve_ecole' => [
                'Consultation directe de vos notes, présences et échéances de paiement.',
                'Bénéficiez d’un tableau de bord plus clair pour suivre votre scolarité.',
            ],
            'parent_ecole' => [
                'Visualisation du parcours scolaire de vos enfants et de leurs performances.',
                'Accès rapide aux bulletins, paiements et messages de l’établissement.',
            ],
            default => [
                'Bienvenue sur votre tableau de bord personnalisé.',
                'Les modules affichés ci-dessous sont adaptés à votre rôle et à vos besoins.',
            ],
        };
    }

    private function countTable(string $table): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM ' . $table);
            $stmt->execute();

            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countTableWhere(string $table, string $column, string $value): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM ' . $table . ' WHERE ' . $column . ' = :value');
            $stmt->execute([':value' => $value]);

            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getRecentRows(string $table, int $limit = 5): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM ' . $table . ' ORDER BY id DESC LIMIT ' . $limit);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

}
