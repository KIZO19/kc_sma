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
        $stats = $this->getStatsForRole($role, $user);
        $chart = $this->getChartSeries($role, $user);
        $table = $this->getTableDataForRole($role);
        $insights = $this->getRoleInsights($role, $user);
        $overview = $this->getOverviewSummary($role, $user);

        $data = [
            'stats' => $stats,
            'chart' => $chart,
            'table' => $table,
            'insights' => $insights,
            'overview' => $overview,
        ];

        if ($role === 'comptable_école') {
            $data['accounting'] = $this->getAccountingData($user);
        }

        return $data;
    }

    private function getAccountingData(array $user): array
    {
        $db = Database::getConnection();
        $ecole = (int) ($user['ecole_id'] ?? 0);
        $params = [];

        $schoolFilter = '';
        if (($user['role'] ?? '') !== 'super_admin' && $ecole > 0) {
            $schoolFilter = 'AND (el.ecole_id = :ecole OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole))';
            $params[':ecole'] = $ecole;
        }

        // Total outstanding (active school year)
        try {
            $sql = 'SELECT COALESCE(SUM(ce.solde_debiteur),0) FROM comptes_eleves ce INNER JOIN eleves el ON ce.eleve_id = el.id INNER JOIN annees_scolaires a ON ce.annee_scolaire_id = a.id AND a.est_active = 1 WHERE 1=1 ' . $schoolFilter;
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, \PDO::PARAM_INT);
            }
            $stmt->execute();
            $totalOutstanding = (float) ($stmt->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            $totalOutstanding = 0.0;
        }

        // Total payments last 30 days
        try {
            $since = date('Y-m-d H:i:s', strtotime('-30 days'));
            $sql = 'SELECT COALESCE(SUM(ece.montant),0) FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id INNER JOIN eleves el ON ce.eleve_id = el.id WHERE ece.type_mouvement = :type AND ece.date_operation >= :since ' . $schoolFilter;
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':type', 'CREDIT');
            $stmt->bindValue(':since', $since);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, \PDO::PARAM_INT);
            }
            $stmt->execute();
            $payments30d = (float) ($stmt->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            $payments30d = 0.0;
        }

        // Recent payments (limit 10)
        $recentPayments = [];
        try {
            $sql = 'SELECT ece.id, ece.reference_recu, ece.date_operation, ece.montant, ece.libelle, el.id AS eleve_id, el.nom, el.postnom, el.prenom, cb.nom_compte FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id INNER JOIN eleves el ON ce.eleve_id = el.id LEFT JOIN caisses_banques cb ON ece.caisse_banque_id = cb.id WHERE ece.type_mouvement = :type ' . $schoolFilter . ' ORDER BY ece.date_operation DESC LIMIT 10';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':type', 'CREDIT');
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, \PDO::PARAM_INT);
            }
            $stmt->execute();
            $recentPayments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $recentPayments = [];
        }

        // Top debtors
        $topDebtors = [];
        try {
            $sql = 'SELECT el.id AS eleve_id, el.nom, el.postnom, el.prenom, SUM(ce.solde_debiteur) AS debt FROM comptes_eleves ce INNER JOIN eleves el ON ce.eleve_id = el.id INNER JOIN annees_scolaires a ON ce.annee_scolaire_id = a.id AND a.est_active = 1 WHERE ce.solde_debiteur > 0 ' . $schoolFilter . ' GROUP BY el.id ORDER BY debt DESC LIMIT 10';
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, \PDO::PARAM_INT);
            }
            $stmt->execute();
            $topDebtors = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $topDebtors = [];
        }

        return [
            'totalOutstanding' => $totalOutstanding,
            'payments30d' => $payments30d,
            'recentPayments' => $recentPayments,
            'topDebtors' => $topDebtors,
        ];
    }

    private function getStatsForRole(string $role, array $user = []): array
    {
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $studentCount = $schoolId > 0 ? $this->countSchoolStudents($schoolId) : $this->countTable('eleves');
        $teacherCount = $schoolId > 0 ? $this->countSchoolUsers($schoolId, ['agent_ecole','enseignant_école','ecole_admin','comptable_école','sec_école','préfet_école','DE_école','DD_école','DP_école','DA_école']) : $this->countTable('agents');
        $parentCount = $schoolId > 0 ? $this->countSchoolUsers($schoolId, ['parent_ecole']) : $this->countTable('parents');
        $classCount = $schoolId > 0 ? $this->countSchoolClasses($schoolId) : $this->countTable('classes');
        $eventCount = $schoolId > 0 ? $this->countSchoolEvents($schoolId) : $this->countTable('evenements');
        $schoolCount = $this->countTable('ecoles');
        $debtTotal = $this->sumOutstandingDebt($schoolId);
        $payments30d = $this->sumPaymentsLast30Days($schoolId);

        return match ($role) {
            'super_admin', 'ecole_admin' => [
                ['title' => 'Élèves inscrits', 'value' => $studentCount, 'icon' => 'bi-people-fill', 'bg' => 'bg-primary', 'hint' => 'Effectif total'],
                ['title' => 'Dette active', 'value' => $this->formatCurrencyCompact($debtTotal), 'icon' => 'bi-cash-stack', 'bg' => 'bg-danger', 'hint' => 'À régulariser'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-diagram-3', 'bg' => 'bg-info', 'hint' => 'Organisées'],
                ['title' => 'Paiements 30j', 'value' => $this->formatCurrencyCompact($payments30d), 'icon' => 'bi-wallet2', 'bg' => 'bg-success', 'hint' => 'Reçus récemment'],
            ],
            'comptable_école' => [
                ['title' => 'Dette actuelle', 'value' => $this->formatCurrencyCompact($debtTotal), 'icon' => 'bi-cash-coin', 'bg' => 'bg-danger', 'hint' => 'Solde débiteur'],
                ['title' => 'Paiements 30j', 'value' => $this->formatCurrencyCompact($payments30d), 'icon' => 'bi-currency-dollar', 'bg' => 'bg-success', 'hint' => 'Recouvrements'],
                ['title' => 'Élèves suivis', 'value' => $studentCount, 'icon' => 'bi-person-badge', 'bg' => 'bg-primary', 'hint' => 'Comptes actifs'],
                ['title' => 'Parents', 'value' => $parentCount, 'icon' => 'bi-people', 'bg' => 'bg-warning', 'hint' => 'Contacts'],
            ],
            'sec_école' => [
                ['title' => 'Inscriptions', 'value' => $studentCount, 'icon' => 'bi-person-plus', 'bg' => 'bg-primary', 'hint' => 'Dossiers actifs'],
                ['title' => 'Parents', 'value' => $parentCount, 'icon' => 'bi-people', 'bg' => 'bg-success', 'hint' => 'Contacts actifs'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-diagram-3', 'bg' => 'bg-info', 'hint' => 'Disponibles'],
                ['title' => 'Événements', 'value' => $eventCount, 'icon' => 'bi-calendar3', 'bg' => 'bg-warning', 'hint' => 'À venir'],
            ],
            'enseignant_école' => [
                ['title' => 'Cours', 'value' => $classCount, 'icon' => 'bi-book', 'bg' => 'bg-primary', 'hint' => 'Programmes actifs'],
                ['title' => 'Élèves', 'value' => $studentCount, 'icon' => 'bi-person-badge', 'bg' => 'bg-success', 'hint' => 'Suivis'],
                ['title' => 'Parents', 'value' => $parentCount, 'icon' => 'bi-people', 'bg' => 'bg-info', 'hint' => 'Contacts'],
                ['title' => 'Événements', 'value' => $eventCount, 'icon' => 'bi-calendar-event', 'bg' => 'bg-warning', 'hint' => 'Planifiés'],
            ],
            'eleve_ecole' => [
                ['title' => 'Moyenne', 'value' => '15,4/20', 'icon' => 'bi-bar-chart-line', 'bg' => 'bg-primary', 'hint' => 'Dernier trimestre'],
                ['title' => 'Présences', 'value' => '96%', 'icon' => 'bi-check2-square', 'bg' => 'bg-success', 'hint' => 'Ce mois'],
                ['title' => 'Paiements', 'value' => $this->formatCurrencyCompact($payments30d), 'icon' => 'bi-wallet2', 'bg' => 'bg-info', 'hint' => 'Derniers 30j'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-calendar2-week', 'bg' => 'bg-warning', 'hint' => 'Programmes'],
            ],
            'parent_ecole' => [
                ['title' => 'Enfants', 'value' => $studentCount, 'icon' => 'bi-people', 'bg' => 'bg-primary', 'hint' => 'Suivis'],
                ['title' => 'Paiements', 'value' => $this->formatCurrencyCompact($payments30d), 'icon' => 'bi-currency-dollar', 'bg' => 'bg-info', 'hint' => 'Derniers 30j'],
                ['title' => 'Dette', 'value' => $this->formatCurrencyCompact($debtTotal), 'icon' => 'bi-wallet2', 'bg' => 'bg-warning', 'hint' => 'À régulariser'],
                ['title' => 'Messages', 'value' => $eventCount, 'icon' => 'bi-chat-dots', 'bg' => 'bg-success', 'hint' => 'Activités'],
            ],
            default => [
                ['title' => 'Tableau', 'value' => 'Actif', 'icon' => 'bi-speedometer2', 'bg' => 'bg-primary', 'hint' => 'Vue principale'],
                ['title' => 'Élèves', 'value' => $studentCount, 'icon' => 'bi-person-badge', 'bg' => 'bg-success', 'hint' => 'Inscrits'],
                ['title' => 'Écoles', 'value' => $schoolCount, 'icon' => 'bi-building', 'bg' => 'bg-info', 'hint' => 'Disponibles'],
                ['title' => 'Agents', 'value' => $teacherCount, 'icon' => 'bi-people-fill', 'bg' => 'bg-warning', 'hint' => 'Actifs'],
            ],
        };
    }

    private function getChartSeries(string $role, array $user = []): array
    {
        $labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui'];
        $baseSeries = [18, 22, 26, 31, 34, 42];
        $financeSeries = [12, 18, 24, 29, 33, 41];

        if ($role === 'comptable_école') {
            $sectionChart = $this->getPaymentEvolutionBySection($user);
            if (!empty($sectionChart['datasets'])) {
                return $sectionChart;
            }
        }

        $series = match ($role) {
            'super_admin', 'ecole_admin' => $baseSeries,
            'comptable_école' => $financeSeries,
            'enseignant_école' => [70, 75, 79, 82, 86, 90],
            'eleve_ecole' => [11, 13, 12, 15, 14, 16],
            'parent_ecole' => [2, 3, 2, 4, 5, 4],
            'sec_école' => [12, 14, 16, 17, 19, 22],
            default => [12, 19, 15, 24, 18, 30],
        };

        $chartConfig = match ($role) {
            'super_admin', 'ecole_admin' => ['title' => 'Suivi de l’école', 'label' => 'Évolution de l’effectif', 'border' => '#0d6efd', 'background' => 'rgba(13, 110, 253, 0.18)'],
            'comptable_école' => ['title' => 'Flux financier', 'label' => 'Paiements et recouvrements', 'border' => '#198754', 'background' => 'rgba(25, 135, 84, 0.18)'],
            'sec_école' => ['title' => 'Inscriptions et affectations', 'label' => 'Nouveaux dossiers', 'border' => '#0dcaf0', 'background' => 'rgba(13, 202, 240, 0.18)'],
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

    private function getPaymentEvolutionBySection(array $user): array
    {
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date('Y-m-01 00:00:00', strtotime('-' . $i . ' months'));
        }

        $sections = \App\Models\Section::getAll();
        if (empty($sections)) {
            return ['title' => 'Flux financier', 'label' => 'Paiements par section', 'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui'], 'datasets' => []];
        }

        $palette = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14'];
        $datasets = [];

        foreach ($sections as $index => $section) {
            $sectionId = (int) ($section['id'] ?? 0);
            $values = [];

            foreach ($months as $monthStart) {
                $monthEnd = date('Y-m-t 23:59:59', strtotime($monthStart));
                $values[] = $this->sumPaymentsBySectionAndPeriod($schoolId, $sectionId, $monthStart, $monthEnd);
            }

            $datasets[] = [
                'label' => $section['nom_section'] ?? 'Section',
                'data' => $values,
                'borderColor' => $palette[$index % count($palette)],
                'backgroundColor' => $palette[$index % count($palette)],
                'tension' => 0.3,
                'fill' => false,
            ];
        }

        $labels = [];
        foreach ($months as $monthStart) {
            $labels[] = date('M', strtotime($monthStart));
        }

        return [
            'title' => 'Flux financier par section',
            'label' => 'Paiements',
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function sumPaymentsBySectionAndPeriod(int $schoolId, int $sectionId, string $from, string $to): float
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT COALESCE(SUM(m.montant), 0) FROM (
                SELECT DISTINCT ece.id, ece.montant
                FROM ecritures_comptables_eleves ece
                INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                INNER JOIN eleves el ON el.id = ce.eleve_id
                INNER JOIN inscriptions i ON i.eleve_id = el.id
                INNER JOIN classes c ON c.id = i.classe_id
                WHERE ece.type_mouvement = :type
                  AND c.section_id = :section_id
                  AND ece.date_operation BETWEEN :from AND :to
                  AND (el.ecole_id = :ecole OR c.ecole_id = :ecole)
            ) AS m';

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':type', 'CREDIT', PDO::PARAM_STR);
            $stmt->bindValue(':section_id', $sectionId, PDO::PARAM_INT);
            $stmt->bindValue(':from', $from, PDO::PARAM_STR);
            $stmt->bindValue(':to', $to, PDO::PARAM_STR);
            $stmt->bindValue(':ecole', $schoolId, PDO::PARAM_INT);
            $stmt->execute();

            return (float) ($stmt->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            return 0.0;
        }
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
                'Vue d’ensemble de l’établissement : effectifs, suivi financier et activités clés.',
                'Les indicateurs ci-dessus vous permettent de piloter les opérations importantes en un seul endroit.',
            ],
            'comptable_école' => [
                'Suivi clair des dettes, paiements reçus et dossiers comptables à traiter.',
                'Les actions rapides facilitent la validation des règlements et la gestion de la trésorerie.',
            ],
            'sec_école' => [
                'Suivi simplifié des inscriptions, parents et classes de l’établissement.',
                'Les informations affichées permettent de traiter les dossiers plus rapidement et plus proprement.',
            ],
            'enseignant_école' => [
                'Vue rapide des classes, événements et élèves suivis dans votre enseignement.',
                'Le tableau de bord centralise les éléments de suivi pédagogique et de planification.',
            ],
            'eleve_ecole' => [
                'Consultation rapide de vos performances, présences et situation de paiement.',
                'Cette vue met en avant les informations utiles pour suivre votre scolarité au quotidien.',
            ],
            'parent_ecole' => [
                'Suivi simplifié du parcours scolaire de vos enfants et de leurs paiements.',
                'Vous accédez ici aux informations clés sans passer par plusieurs écrans.',
            ],
            default => [
                'Bienvenue sur votre tableau de bord personnalisé.',
                'Les modules affichés ci-dessous sont adaptés à votre rôle et à vos besoins.',
            ],
        };
    }

    private function getOverviewSummary(string $role, array $user): array
    {
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $students = $schoolId > 0 ? $this->countSchoolStudents($schoolId) : $this->countTable('eleves');
        $totalDebt = $this->sumOutstandingDebt($schoolId);
        $payments30d = $this->sumPaymentsLast30Days($schoolId);
        $recoveryRate = ($totalDebt > 0 || $payments30d > 0) ? (int) min(100, round(($payments30d / max(1, $payments30d + $totalDebt)) * 100)) : 100;

        $items = match ($role) {
            'super_admin', 'ecole_admin' => [
                ['label' => 'Taux de recouvrement', 'value' => $recoveryRate . '%', 'color' => 'bg-success'],
                ['label' => 'Élèves suivis', 'value' => (string) $students, 'color' => 'bg-primary'],
                ['label' => 'Dette active', 'value' => $this->formatCurrencyCompact($totalDebt), 'color' => 'bg-warning'],
            ],
            'comptable_école' => [
                ['label' => 'Paiements 30j', 'value' => $this->formatCurrencyCompact($payments30d), 'color' => 'bg-success'],
                ['label' => 'Dette active', 'value' => $this->formatCurrencyCompact($totalDebt), 'color' => 'bg-danger'],
                ['label' => 'Élèves suivis', 'value' => (string) $students, 'color' => 'bg-primary'],
            ],
            'sec_école' => [
                ['label' => 'Dossiers actifs', 'value' => (string) $students, 'color' => 'bg-primary'],
                ['label' => 'Classes', 'value' => (string) ($schoolId > 0 ? $this->countSchoolClasses($schoolId) : $this->countTable('classes')), 'color' => 'bg-info'],
                ['label' => 'Parents', 'value' => (string) ($schoolId > 0 ? $this->countSchoolUsers($schoolId, ['parent_ecole']) : $this->countTable('parents')), 'color' => 'bg-success'],
            ],
            default => [
                ['label' => 'Vue globale', 'value' => 'Active', 'color' => 'bg-primary'],
                ['label' => 'Élèves', 'value' => (string) $students, 'color' => 'bg-success'],
                ['label' => 'Rôle', 'value' => User::getRoleLabel($role), 'color' => 'bg-info'],
            ],
        };

        return [
            'title' => 'Vue synthétique',
            'items' => $items,
        ];
    }

    private function countSchoolStudents(int $schoolId): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(DISTINCT i.eleve_id) AS total FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id WHERE c.ecole_id = :ecole');
            $stmt->execute([':ecole' => $schoolId]);
            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return $this->countTable('eleves');
        }
    }

    private function countSchoolClasses(int $schoolId): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM classes WHERE ecole_id = :ecole');
            $stmt->execute([':ecole' => $schoolId]);
            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countSchoolEvents(int $schoolId): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM evenements WHERE ecole_id = :ecole');
            $stmt->execute([':ecole' => $schoolId]);
            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countSchoolUsers(int $schoolId, array $roles): int
    {
        if (empty($roles)) {
            return 0;
        }

        try {
            $db = Database::getConnection();
            $inClause = implode(',', array_fill(0, count($roles), '?'));
            $sql = 'SELECT COUNT(*) AS total FROM utilisateurs WHERE ecole_id = :ecole AND role IN (' . $inClause . ')';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':ecole', $schoolId, PDO::PARAM_INT);
            foreach ($roles as $index => $role) {
                $stmt->bindValue($index + 1, $role, PDO::PARAM_STR);
            }
            $stmt->execute();
            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function sumOutstandingDebt(int $schoolId): float
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT COALESCE(SUM(ce.solde_debiteur),0) FROM comptes_eleves ce INNER JOIN eleves el ON el.id = ce.eleve_id';
            $params = [];
            if ($schoolId > 0) {
                $sql .= ' WHERE (el.ecole_id = :ecole OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole))';
                $params[':ecole'] = $schoolId;
            }
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            return (float) ($stmt->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private function sumPaymentsLast30Days(int $schoolId): float
    {
        try {
            $db = Database::getConnection();
            $since = date('Y-m-d H:i:s', strtotime('-30 days'));
            $sql = 'SELECT COALESCE(SUM(ece.montant),0) FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id INNER JOIN eleves el ON el.id = ce.eleve_id WHERE ece.type_mouvement = :type AND ece.date_operation >= :since';
            $params = [':type' => 'CREDIT', ':since' => $since];
            if ($schoolId > 0) {
                $sql .= ' AND (el.ecole_id = :ecole OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole))';
                $params[':ecole'] = $schoolId;
            }
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            return (float) ($stmt->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private function formatCurrencyCompact(float $value): string
    {
        $amount = (float) $value;
        if ($amount >= 1000000) {
            return number_format($amount / 1000000, 1, ',', ' ') . 'M';
        }

        if ($amount >= 1000) {
            return number_format($amount / 1000, 1, ',', ' ') . 'K';
        }

        return number_format($amount, 0, ',', ' ');
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
