<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\AdministrationAutorisation;
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($role !== 'ecole_admin') {
                $this->redirect('/error/accessDenied');
            }

            $moduleRole = (string) ($_POST['role'] ?? '');
            $module = (string) ($_POST['module'] ?? '');
            $enabled = isset($_POST['enabled']) && (string) $_POST['enabled'] === '1';
            if (
                ($_POST['admin_permission'] ?? '') !== 'module'
                || !AdministrationAutorisation::setModuleAccess(
                    (int) ($user['ecole_id'] ?? 0),
                    $moduleRole,
                    $module,
                    $enabled,
                    (int) ($user['id'] ?? 0)
                )
            ) {
                $_SESSION['dashboard_error'] = 'L’autorisation sélectionnée est invalide.';
            } else {
                $_SESSION['dashboard_success'] = 'Autorisation administrative mise à jour.';
            }
            $this->redirect('/dashboard');
        }

        $modules = $this->getModulesForRole($role);
        $paymentChartRange = $role === 'comptable_école' ? $this->getPaymentChartRange() : [];
        $dashboardData = $this->buildDashboardData($user, $role, $paymentChartRange);
        $adminRoleList = $role === 'ecole_admin' ? AdministrationAutorisation::allRoles() : [];
        $adminModules = $role === 'ecole_admin' ? AdministrationAutorisation::allModules() : [];
        $adminRoleModules = $role === 'ecole_admin' ? AdministrationAutorisation::getRoleModules() : [];
        $adminMatrix = $role === 'ecole_admin'
            ? AdministrationAutorisation::getAccessMatrix((int) ($user['ecole_id'] ?? 0))
            : [];

        $this->view('dashboard/index', [
            'title' => APP_NAME,
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'dashboardData' => $dashboardData,
            'adminRoleList' => $adminRoleList,
            'adminModules' => $adminModules,
            'adminRoleModules' => $adminRoleModules,
            'adminMatrix' => $adminMatrix,
        ]);
    }

    private function buildDashboardData(array $user, string $role, array $paymentChartRange = []): array
    {
        if ($role === 'super_admin') {
            return ['schoolManagement' => $this->getSchoolManagementData()];
        }

        $stats = $this->getStatsForRole($role, $user);
        $chart = $role === 'comptable_école' ? $this->getChartSeries($user, $paymentChartRange) : [];

        $data = [
            'stats' => $stats,
            'chart' => $chart,
        ];

        if (in_array($role, ['préfet_école', 'DP_école'], true)) {
            $school = Ecole::findById((int) ($user['ecole_id'] ?? 0));
            $data['direction'] = [
                'schoolName' => $school['nom_etablissement'] ?? 'Votre établissement',
                'isPrefect' => $role === 'préfet_école',
            ];
            $data['overview'] = $this->getOverviewSummary($role, $user);
        }

        if ($role === 'comptable_école') {
            $data['accounting'] = $this->getAccountingData($user);
        }

        return $data;
    }

    private function getSchoolManagementData(): array
    {
        $schools = Ecole::getAll();
        $openAccess = 0;
        $renewalSoon = 0;
        $today = new \DateTimeImmutable('today');
        $renewalLimit = $today->modify('+30 days');

        foreach ($schools as $school) {
            $status = (string) ($school['statut_systeme'] ?? '');
            $subscriptionStart = !empty($school['date_debut'])
                ? strtotime((string) $school['date_debut'])
                : false;
            $subscriptionEnd = !empty($school['date_fin'])
                ? strtotime((string) $school['date_fin'])
                : false;
            $hasCurrentSubscription = ($school['statut_abonnement'] ?? '') === 'Actif'
                && $subscriptionStart !== false
                && $subscriptionStart <= time()
                && $subscriptionEnd !== false
                && date('Y-m-d', $subscriptionEnd) >= $today->format('Y-m-d');

            if ($status === 'Actif' && $hasCurrentSubscription) {
                $openAccess++;
                if (date('Y-m-d', $subscriptionEnd) <= $renewalLimit->format('Y-m-d')) {
                    $renewalSoon++;
                }
            }
        }

        return [
            'stats' => [
                ['title' => 'Écoles enregistrées', 'value' => count($schools), 'icon' => 'bi-buildings', 'tone' => 'primary', 'hint' => 'Ensemble des établissements'],
                ['title' => 'Accès ouverts', 'value' => $openAccess, 'icon' => 'bi-check-circle', 'tone' => 'success', 'hint' => 'Abonnement valide et école active'],
                ['title' => 'Renouvellements proches', 'value' => $renewalSoon, 'icon' => 'bi-hourglass-split', 'tone' => 'warning', 'hint' => 'Échéance dans les 30 jours'],
                ['title' => 'Accès fermés', 'value' => max(0, count($schools) - $openAccess), 'icon' => 'bi-lock', 'tone' => 'danger', 'hint' => 'Abonnement ou statut à vérifier'],
            ],
            'recentSchools' => array_slice($schools, 0, 8),
        ];
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

        // Keep the dashboard debt total consistent with the recouvrements page.
        $totalOutstanding = $this->sumOutstandingDebt($ecole);

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
                        $sql = 'SELECT el.id AS eleve_id, el.nom, el.postnom, el.prenom, SUM(outstanding.montant_restant) AS debt
                                        FROM (
                                            SELECT d.eleve_id, d.frais_id,
                                                         GREATEST(0, MAX(d.montant_initial) - GREATEST(
                                                             COALESCE((SELECT SUM(ece.montant) FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce2 ON ce2.id = ece.compte_eleve_id WHERE ce2.eleve_id = d.eleve_id AND ece.frais_id = d.frais_id AND ece.type_mouvement = \'CREDIT\'), 0),
                                                             COALESCE((SELECT SUM(pe.montant_paye) FROM paiements_eleves pe WHERE pe.eleve_id = d.eleve_id AND pe.frais_id = d.frais_id), 0)
                                                         )) AS montant_restant
                                            FROM dettes_eleves d
                                            INNER JOIN eleves el2 ON el2.id = d.eleve_id
                                            WHERE 1 = 1 ' . (($schoolFilter !== '') ? 'AND (el2.ecole_id = :ecole_top OR EXISTS (SELECT 1 FROM inscriptions i2 INNER JOIN classes c2 ON i2.classe_id = c2.id WHERE i2.eleve_id = el2.id AND c2.ecole_id = :ecole_top))' : '') . '
                                            GROUP BY d.eleve_id, d.frais_id
                                            HAVING montant_restant > 0
                                        ) AS outstanding
                                        INNER JOIN eleves el ON el.id = outstanding.eleve_id
                                        GROUP BY el.id, el.nom, el.postnom, el.prenom
                                        ORDER BY debt DESC LIMIT 10';
                        $topParams = [];
                        if ($schoolFilter !== '') {
                                $topParams[':ecole_top'] = $ecole;
                        }
            $stmt = $db->prepare($sql);
                        foreach ($topParams as $k => $v) {
                $stmt->bindValue($k, $v, \PDO::PARAM_INT);
            }
            $stmt->execute();
            $topDebtors = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $topDebtors = [];
        }

        $debtBreakdown = $this->getDebtBreakdown($ecole);

        return [
            'totalOutstanding' => $totalOutstanding,
            'payments30d' => $payments30d,
            'recentPayments' => $recentPayments,
            'topDebtors' => $topDebtors,
            'debtBreakdown' => $debtBreakdown,
            'debtBySectionChart' => $this->buildDebtBySectionChart($debtBreakdown),
            'sectionStats' => $this->getEnrolledStudentsBySection($ecole),
        ];
    }

    private function buildDebtBySectionChart(array $breakdown): array
    {
        $sections = [];
        $currencies = [];
        $totals = [];

        foreach ($breakdown as $row) {
            $section = trim((string) ($row['nom_section'] ?? 'Section non définie')) ?: 'Section non définie';
            $currency = strtoupper(trim((string) ($row['devise'] ?? 'USD'))) ?: 'USD';
            $sections[$section] = true;
            $currencies[$currency] = true;
            $totals[$currency][$section] = ($totals[$currency][$section] ?? 0.0) + (float) ($row['dette_restante'] ?? 0);
        }

        $sectionLabels = array_keys($sections);
        $currencyCodes = array_keys($currencies);
        $palette = ['#0d6efd', '#198754', '#d08a00', '#dc3545', '#20c997', '#fd7e14'];
        $datasets = [];
        foreach ($currencyCodes as $index => $currency) {
            $datasets[] = [
                'label' => $currency,
                'data' => array_map(static fn (string $section): float => (float) ($totals[$currency][$section] ?? 0), $sectionLabels),
                'backgroundColor' => $palette[$index % count($palette)],
                'borderColor' => $palette[$index % count($palette)],
            ];
        }

        return [
            'labels' => $sectionLabels,
            'currencies' => $currencyCodes,
            'datasets' => $datasets,
        ];
    }

    private function getEnrolledStudentsBySection(int $schoolId): array
    {
        if ($schoolId <= 0) {
            return [];
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'SELECT s.id, s.nom_section, COUNT(DISTINCT i.eleve_id) AS total_eleves
                 FROM sections s
                 LEFT JOIN classes c ON c.section_id = s.id AND c.ecole_id = :classes_school
                 LEFT JOIN annees_scolaires a ON a.ecole_id = :years_school AND a.est_active = 1
                 LEFT JOIN inscriptions i ON i.classe_id = c.id AND i.annee_scolaire_id = a.id
                      AND i.id = (
                          SELECT latest.id FROM inscriptions latest
                          WHERE latest.eleve_id = i.eleve_id AND latest.annee_scolaire_id = a.id
                          ORDER BY latest.date_inscription DESC, latest.id DESC LIMIT 1
                      )
                 GROUP BY s.id, s.nom_section
                 ORDER BY s.id ASC'
            );
            $stmt->execute([
                ':classes_school' => $schoolId,
                ':years_school' => $schoolId,
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('DashboardController::getEnrolledStudentsBySection failed: ' . $e->getMessage());
            return [];
        }
    }

    private function getDebtBreakdown(int $schoolId): array
    {
        try {
            $db = Database::getConnection();
            $schoolFilter = '';
            $params = [];
            if ($schoolId > 0) {
                $schoolFilter = 'WHERE (el.ecole_id = :ecole_breakdown_a OR EXISTS (
                    SELECT 1 FROM inscriptions si INNER JOIN classes sc ON si.classe_id = sc.id
                    WHERE si.eleve_id = el.id AND sc.ecole_id = :ecole_breakdown_b
                ))';
                $params[':ecole_breakdown_a'] = $schoolId;
                $params[':ecole_breakdown_b'] = $schoolId;
            }

            $sql = 'SELECT fs.type_frais,
                           debt.devise,
                           COALESCE(c.nom_classe, \'Classe non définie\') AS nom_classe,
                           COALESCE(o.nom_option, \'Sans option\') AS nom_option,
                           COALESCE(s.nom_section, \'Section non définie\') AS nom_section,
                           SUM(debt.montant_restant) AS dette_restante
                    FROM (
                        SELECT d.eleve_id, d.frais_id, d.devise,
                               GREATEST(0, MAX(d.montant_initial) - GREATEST(
                                   COALESCE((SELECT SUM(ece.montant)
                                             FROM ecritures_comptables_eleves ece
                                             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                                             WHERE ce.eleve_id = d.eleve_id AND ece.frais_id = d.frais_id
                                               AND ece.type_mouvement = \'CREDIT\'), 0),
                                   COALESCE((SELECT SUM(pe.montant_paye)
                                             FROM paiements_eleves pe
                                             WHERE pe.eleve_id = d.eleve_id AND pe.frais_id = d.frais_id), 0)
                               )) AS montant_restant
                        FROM dettes_eleves d
                        INNER JOIN eleves el ON el.id = d.eleve_id
                        ' . $schoolFilter . '
                        GROUP BY d.eleve_id, d.frais_id, d.devise
                        HAVING montant_restant > 0
                    ) AS debt
                    INNER JOIN frais_scolaires fs ON fs.id = debt.frais_id
                    LEFT JOIN inscriptions i ON i.id = (
                        SELECT latest.id FROM inscriptions latest
                        WHERE latest.eleve_id = debt.eleve_id
                        ORDER BY latest.date_inscription DESC, latest.id DESC LIMIT 1
                    )
                    LEFT JOIN classes c ON c.id = i.classe_id
                    LEFT JOIN options o ON o.id = c.option_id
                    LEFT JOIN sections s ON s.id = c.section_id
                    GROUP BY fs.type_frais, debt.devise, c.nom_classe, o.nom_option, s.nom_section
                    ORDER BY s.nom_section ASC, o.nom_option ASC, c.nom_classe ASC, fs.type_frais ASC';

            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('DashboardController::getDebtBreakdown failed: ' . $e->getMessage());
            return [];
        }
    }

    private function getStatsForRole(string $role, array $user = []): array
    {
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $schoolRoles = ['ecole_admin', 'promoteur_école', 'DE_école', 'DD_école', 'DA_école', 'comptable_école', 'sec_école', 'préfet_école', 'DP_école'];
        if ($schoolId <= 0 || !in_array($role, $schoolRoles, true)) {
            return [];
        }

        $sectionScopeId = in_array($role, ['préfet_école', 'DE_école', 'DD_école'], true)
            ? $this->getSectionScopeForUser($user)
            : null;
        $studentCount = $this->countSchoolStudents($schoolId, $sectionScopeId);
        $teacherCount = $this->countSchoolUsers($schoolId, ['enseignant_école'], $sectionScopeId);
        $parentCount = $this->countSchoolUsers($schoolId, ['parent_ecole']);
        $classCount = $this->countSchoolClasses($schoolId, $sectionScopeId);
        $eventCount = $this->countSchoolEvents($schoolId);
        $debtTotal = $this->sumOutstandingDebt($schoolId);
        $payments30d = $this->sumPaymentsLast30Days($schoolId);

        return match ($role) {
            'ecole_admin' => [
                ['title' => 'Élèves inscrits', 'value' => $studentCount, 'icon' => 'bi-people-fill', 'bg' => 'bg-primary', 'hint' => 'Effectif total'],
                ['title' => 'Dette active', 'value' => $this->formatCurrencyCompact($debtTotal), 'icon' => 'bi-cash-stack', 'bg' => 'bg-danger', 'hint' => 'À régulariser'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-diagram-3', 'bg' => 'bg-info', 'hint' => 'Organisées'],
                ['title' => 'Paiements 30j', 'value' => $this->formatCurrencyCompact($payments30d), 'icon' => 'bi-wallet2', 'bg' => 'bg-success', 'hint' => 'Reçus récemment'],
            ],
            'promoteur_école', 'DE_école', 'DD_école', 'DA_école' => [
                ['title' => 'Élèves inscrits', 'value' => $studentCount, 'icon' => 'bi-people-fill', 'bg' => 'bg-primary', 'hint' => $sectionScopeId !== null ? 'Effectif du secondaire' : 'Effectif de l’école'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-diagram-3', 'bg' => 'bg-info', 'hint' => $sectionScopeId !== null ? 'Classes du secondaire' : 'Organisation scolaire'],
                ['title' => 'Enseignants', 'value' => $teacherCount, 'icon' => 'bi-person-workspace', 'bg' => 'bg-success', 'hint' => $sectionScopeId !== null ? 'Enseignants du secondaire' : 'Comptes enseignants'],
                ['title' => 'Événements', 'value' => $eventCount, 'icon' => 'bi-calendar-event', 'bg' => 'bg-warning', 'hint' => 'Activités enregistrées'],
            ],
            'comptable_école' => [
                ['title' => 'Dette actuelle', 'value' => $this->formatCurrencyCompact($debtTotal), 'icon' => 'bi-cash-coin', 'bg' => 'bg-danger', 'hint' => 'Solde débiteur'],
                ['title' => 'Paiements 30j', 'value' => $this->formatCurrencyCompact($payments30d), 'icon' => 'bi-currency-dollar', 'bg' => 'bg-success', 'hint' => 'Recouvrements'],
                ['title' => 'Élèves suivis', 'value' => $studentCount, 'icon' => 'bi-person-badge', 'bg' => 'bg-primary', 'hint' => 'Comptes actifs'],
                ['title' => 'Parents', 'value' => $parentCount, 'icon' => 'bi-people', 'bg' => 'bg-warning', 'hint' => 'Contacts'],
            ],
            'sec_école' => [
                ['title' => 'Élèves inscrits', 'value' => $studentCount, 'icon' => 'bi-person-plus', 'bg' => 'bg-primary', 'hint' => 'Effectif de l’école'],
                ['title' => 'Parents', 'value' => $parentCount, 'icon' => 'bi-people', 'bg' => 'bg-success', 'hint' => 'Contacts enregistrés'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-diagram-3', 'bg' => 'bg-info', 'hint' => 'Disponibles'],
                ['title' => 'Événements', 'value' => $eventCount, 'icon' => 'bi-calendar3', 'bg' => 'bg-warning', 'hint' => 'Activités enregistrées'],
            ],
            'préfet_école' => [
                ['title' => 'Élèves inscrits', 'value' => $studentCount, 'icon' => 'bi-people-fill', 'bg' => 'bg-primary', 'hint' => 'Effectif du secondaire'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-diagram-3', 'bg' => 'bg-info', 'hint' => 'Classes du secondaire'],
                ['title' => 'Options actives', 'value' => $schoolId > 0 ? $this->countSchoolOptions($schoolId, $sectionScopeId) : 0, 'icon' => 'bi-grid-1x2', 'bg' => 'bg-success', 'hint' => 'Options du secondaire'],
                ['title' => 'Enseignants', 'value' => $schoolId > 0 ? $this->countSchoolUsers($schoolId, ['enseignant_école'], $sectionScopeId) : 0, 'icon' => 'bi-person-workspace', 'bg' => 'bg-warning', 'hint' => 'Enseignants du secondaire'],
            ],
            'DP_école' => [
                ['title' => 'Élèves inscrits', 'value' => $studentCount, 'icon' => 'bi-people-fill', 'bg' => 'bg-primary', 'hint' => 'Effectif de l’école'],
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'bi-diagram-3', 'bg' => 'bg-info', 'hint' => 'Organisation scolaire'],
                ['title' => 'Enseignants', 'value' => $schoolId > 0 ? $this->countSchoolUsers($schoolId, ['enseignant_école']) : 0, 'icon' => 'bi-person-workspace', 'bg' => 'bg-success', 'hint' => 'Comptes enseignants'],
                ['title' => 'Événements', 'value' => $eventCount, 'icon' => 'bi-calendar-event', 'bg' => 'bg-warning', 'hint' => 'Activités enregistrées'],
            ],
            default => [],
        };
    }

    private function getPaymentChartRange(): array
    {
        $today = new \DateTimeImmutable('today');
        $defaultFrom = new \DateTimeImmutable('monday this week');
        $parseDate = static function (string $value): ?\DateTimeImmutable {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            return $date && $date->format('Y-m-d') === $value ? $date : null;
        };

        $from = $parseDate(trim((string) ($_GET['payment_from'] ?? ''))) ?? $defaultFrom;
        $to = $parseDate(trim((string) ($_GET['payment_to'] ?? ''))) ?? $today;
        $error = '';
        if ($from > $to) {
            $from = $defaultFrom;
            $to = $today;
            $error = 'La date de début doit précéder la date de fin.';
        }

        return [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'currency' => strtoupper(trim((string) ($_GET['payment_currency'] ?? ''))),
            'error' => $error,
        ];
    }

    private function getChartSeries(array $user, array $range): array
    {
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $from = new \DateTimeImmutable($range['from']);
        $to = new \DateTimeImmutable($range['to']);
        $dayNames = ['lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.', 'dim.'];
        $labels = [];
        $dates = [];
        for ($day = $from; $day <= $to; $day = $day->modify('+1 day')) {
            $date = $day->format('Y-m-d');
            $dates[] = $date;
            $labels[] = $dayNames[(int) $day->format('N') - 1] . ' ' . $day->format('d/m');
        }

        $db = Database::getConnection();
        $sectionStmt = $db->prepare(
            'SELECT DISTINCT s.id, s.nom_section FROM sections s
             INNER JOIN classes c ON c.section_id = s.id
             WHERE c.ecole_id = :ecole ORDER BY s.id ASC'
        );
        $sectionStmt->execute([':ecole' => $schoolId]);
        $sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);

        $currencyStmt = $db->prepare(
            'SELECT DISTINCT UPPER(COALESCE(fs.devise, \'USD\')) AS code
             FROM frais_scolaires fs
             WHERE fs.ecole_id = :fee_school OR EXISTS (
                 SELECT 1 FROM classes c WHERE c.ecole_id = :class_school AND (
                     fs.classe_id = c.id
                     OR (fs.scope = \'option\' AND fs.scope_id = c.option_id)
                     OR (fs.scope = \'section\' AND fs.scope_id = c.section_id)
                 )
             ) ORDER BY code ASC'
        );
        $currencyStmt->execute([':fee_school' => $schoolId, ':class_school' => $schoolId]);
        $currencies = array_map('strval', $currencyStmt->fetchAll(PDO::FETCH_COLUMN));
        if (!$currencies) {
            $currencies = ['USD'];
        }
        $currency = in_array($range['currency'], $currencies, true)
            ? $range['currency']
            : (in_array('USD', $currencies, true) ? 'USD' : $currencies[0]);

        $paymentsByDayAndSection = [];
        if ($schoolId > 0 && $sections) {
            $sql = 'SELECT DATE(ece.date_operation) AS payment_day, c.section_id,
                           COALESCE(SUM(ece.montant), 0) AS total_paye
                    FROM ecritures_comptables_eleves ece
                    INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                    INNER JOIN eleves el ON el.id = ce.eleve_id
                    INNER JOIN frais_scolaires fs ON fs.id = ece.frais_id
                    INNER JOIN inscriptions i ON i.id = (
                        SELECT latest.id FROM inscriptions latest
                        WHERE latest.eleve_id = el.id AND latest.annee_scolaire_id = ce.annee_scolaire_id
                        ORDER BY latest.date_inscription DESC, latest.id DESC LIMIT 1
                    )
                    INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = :school_id
                    WHERE ece.type_mouvement = \'CREDIT\'
                      AND UPPER(COALESCE(fs.devise, \'USD\')) = :currency
                      AND ece.date_operation >= :date_from
                      AND ece.date_operation < :date_to_exclusive
                    GROUP BY DATE(ece.date_operation), c.section_id';
            $paymentsStmt = $db->prepare($sql);
            $paymentsStmt->execute([
                ':school_id' => $schoolId,
                ':currency' => $currency,
                ':date_from' => $from->format('Y-m-d 00:00:00'),
                ':date_to_exclusive' => $to->modify('+1 day')->format('Y-m-d 00:00:00'),
            ]);
            foreach ($paymentsStmt->fetchAll(PDO::FETCH_ASSOC) as $payment) {
                $paymentsByDayAndSection[$payment['payment_day']][(int) $payment['section_id']] = (float) $payment['total_paye'];
            }
        }

        $palette = ['#0d6efd', '#198754', '#d08a00', '#dc3545', '#20c997', '#fd7e14', '#6f42c1'];
        $datasets = [];
        foreach ($sections as $index => $section) {
            $sectionId = (int) $section['id'];
            $values = array_map(
                static fn (string $date): float => $paymentsByDayAndSection[$date][$sectionId] ?? 0.0,
                $dates
            );
            $datasets[] = [
                'label' => $section['nom_section'] ?? 'Section',
                'data' => $values,
                'borderColor' => $palette[$index % count($palette)],
                'backgroundColor' => $palette[$index % count($palette)],
                'tension' => 0.3,
                'fill' => false,
            ];
        }

        return [
            'title' => 'Flux financier par section',
            'label' => 'Paiements quotidiens',
            'labels' => $labels,
            'datasets' => $datasets,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'currency' => $currency,
            'currencies' => $currencies,
            'rangeError' => $range['error'] ?? '',
        ];
    }

    private function getOverviewSummary(string $role, array $user): array
    {
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if ($schoolId <= 0) {
            return ['title' => 'Vue synthétique', 'items' => []];
        }

        $sectionScopeId = $role === 'préfet_école'
            ? $this->getSectionScopeForUser($user)
            : null;
        $students = $this->countSchoolStudents($schoolId, $sectionScopeId);
        $items = match ($role) {
            'préfet_école' => [
                ['label' => 'Effectif du secondaire', 'value' => (string) $students, 'color' => 'bg-primary'],
                ['label' => 'Classes du secondaire', 'value' => (string) $this->countSchoolClasses($schoolId, $sectionScopeId), 'color' => 'bg-info'],
                ['label' => 'Options du secondaire', 'value' => (string) $this->countSchoolOptions($schoolId, $sectionScopeId), 'color' => 'bg-success'],
            ],
            'DP_école' => [
                ['label' => 'Effectif scolaire', 'value' => (string) $students, 'color' => 'bg-primary'],
                ['label' => 'Enseignants', 'value' => (string) $this->countSchoolUsers($schoolId, ['enseignant_école']), 'color' => 'bg-success'],
                ['label' => 'Événements enregistrés', 'value' => (string) $this->countSchoolEvents($schoolId), 'color' => 'bg-warning'],
            ],
            default => [],
        };

        return [
            'title' => 'Vue synthétique',
            'items' => $items,
        ];
    }

    private function countSchoolStudents(int $schoolId, ?int $sectionId = null): int
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT COUNT(DISTINCT i.eleve_id) AS total FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id WHERE c.ecole_id = :ecole';
            $params = [':ecole' => $schoolId];
            if ($sectionId !== null) {
                $sql .= ' AND c.section_id = :section_id';
                $params[':section_id'] = $sectionId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            error_log('DashboardController::countSchoolStudents failed: ' . $e->getMessage());
            return 0;
        }
    }

    private function countSchoolClasses(int $schoolId, ?int $sectionId = null): int
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT COUNT(*) AS total FROM classes WHERE ecole_id = :ecole';
            $params = [':ecole' => $schoolId];
            if ($sectionId !== null) {
                $sql .= ' AND section_id = :section_id';
                $params[':section_id'] = $sectionId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countSchoolOptions(int $schoolId, ?int $sectionId = null): int
    {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT COUNT(DISTINCT option_id) FROM classes WHERE ecole_id = :ecole AND option_id IS NOT NULL';
            $params = [':ecole' => $schoolId];
            if ($sectionId !== null) {
                $sql .= ' AND section_id = :section_id';
                $params[':section_id'] = $sectionId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('DashboardController::countSchoolOptions failed: ' . $e->getMessage());
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

    private function countSchoolUsers(int $schoolId, array $roles, ?int $sectionId = null): int
    {
        if (empty($roles)) {
            return 0;
        }

        try {
            $db = Database::getConnection();
            $inClause = implode(',', array_fill(0, count($roles), '?'));
            $sql = 'SELECT COUNT(*) AS total FROM utilisateurs WHERE ecole_id = :ecole AND role IN (' . $inClause . ')';
            if ($sectionId !== null && in_array('enseignant_école', $roles, true)) {
                $sql .= ' AND EXISTS (
                    SELECT 1 FROM attribution_cours ac
                    INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
                    WHERE ac.agent_id = utilisateurs.reference_id
                      AND c.ecole_id = :section_school AND c.section_id = :section_id
                )';
            }
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':ecole', $schoolId, PDO::PARAM_INT);
            if ($sectionId !== null && in_array('enseignant_école', $roles, true)) {
                $stmt->bindValue(':section_school', $schoolId, PDO::PARAM_INT);
                $stmt->bindValue(':section_id', $sectionId, PDO::PARAM_INT);
            }
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
                        $sql = 'SELECT COALESCE(SUM(outstanding.montant_restant), 0) FROM (
                                             SELECT d.eleve_id, d.frais_id, d.devise,
                                                            GREATEST(0, MAX(d.montant_initial) - GREATEST(
                                                                COALESCE((SELECT SUM(ece.montant)
                                                                                    FROM ecritures_comptables_eleves ece
                                                                                    INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                                                                                    WHERE ce.eleve_id = d.eleve_id AND ece.frais_id = d.frais_id
                                                                                        AND ece.type_mouvement = \'CREDIT\'), 0),
                                                                COALESCE((SELECT SUM(pe.montant_paye)
                                                                                    FROM paiements_eleves pe
                                                                                    WHERE pe.eleve_id = d.eleve_id AND pe.frais_id = d.frais_id), 0)
                                                            )) AS montant_restant
                                             FROM dettes_eleves d
                                             INNER JOIN eleves el ON el.id = d.eleve_id
                                             WHERE 1 = 1';
            $params = [];
            if ($schoolId > 0) {
                                $sql .= ' AND (el.ecole_id = :ecole OR EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id WHERE i.eleve_id = el.id AND c.ecole_id = :ecole))';
                $params[':ecole'] = $schoolId;
            }
                        $sql .= ' GROUP BY d.eleve_id, d.frais_id, d.devise
                                            HAVING montant_restant > 0
                                        ) AS outstanding';
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

}
