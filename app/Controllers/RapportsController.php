<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Ecole;
use App\Models\User;

class RapportsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'promoteur_école', 'comptable_école', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $reportCategories = $this->getReportCategories($role);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $school = null;
        if ($schoolId > 0) {
            Ecole::ensureSchoolCalendarColumns();
            $school = Ecole::findById($schoolId);
        }

        $periodError = '';
        try {
            $period = $this->resolvePeriod($_GET, $school);
        } catch (\InvalidArgumentException $exception) {
            $periodError = $exception->getMessage();
            $period = $this->resolvePeriod([], $school);
        }

        $this->view('rapports/index', [
            'title' => APP_NAME . ' - Rapports',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'reportCategories' => $reportCategories,
            'period' => $period,
            'periodError' => $periodError,
            'schoolCalendar' => $school,
            'reportData' => $this->buildReportData($period, $schoolId > 0 ? $schoolId : null),
        ]);
    }

    private function resolvePeriod(array $input, ?array $school): array
    {
        $period = is_string($input['period'] ?? null) ? $input['period'] : 'month';
        $today = new \DateTimeImmutable('today');

        if ($period === 'school_year') {
            $start = $this->parseDate((string) ($school['annee_scolaire_debut'] ?? ''));
            $end = $this->parseDate((string) ($school['annee_scolaire_fin'] ?? ''));
            if ($start === null || $end === null || $end < $start) {
                throw new \InvalidArgumentException('Les dates du calendrier scolaire doivent être définies dans les paramètres de l’école.');
            }

            return [
                'type' => $period,
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'label' => 'Année scolaire (' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y') . ')',
            ];
        }

        if ($period === 'custom') {
            $start = $this->parseDate((string) ($input['start_date'] ?? ''));
            $end = $this->parseDate((string) ($input['end_date'] ?? ''));
            if ($start === null || $end === null || $end < $start || $end > $today) {
                throw new \InvalidArgumentException('Saisissez une plage valide, avec une date de fin qui ne dépasse pas aujourd’hui.');
            }

            return [
                'type' => $period,
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'label' => 'Du ' . $start->format('d/m/Y') . ' au ' . $end->format('d/m/Y'),
            ];
        }

        $year = filter_var($input['year'] ?? $today->format('Y'), FILTER_VALIDATE_INT);
        if ($year === false || $year < 2000 || $year > (int) $today->format('Y')) {
            throw new \InvalidArgumentException('L’année sélectionnée est invalide.');
        }

        if ($period === 'year') {
            $start = new \DateTimeImmutable(sprintf('%04d-01-01', $year));
            $end = min(new \DateTimeImmutable(sprintf('%04d-12-31', $year)), $today);
            return [
                'type' => $period,
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'label' => 'Année ' . $year,
            ];
        }

        if ($period === 'quarter') {
            $quarter = filter_var($input['quarter'] ?? (int) ceil((int) $today->format('n') / 3), FILTER_VALIDATE_INT);
            $currentQuarter = (int) ceil((int) $today->format('n') / 3);
            if ($quarter === false || $quarter < 1 || $quarter > 4 || ($year === (int) $today->format('Y') && $quarter > $currentQuarter)) {
                throw new \InvalidArgumentException('Le trimestre sélectionné est invalide.');
            }
            $month = (($quarter - 1) * 3) + 1;
            $start = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
            $end = min($start->modify('+3 months -1 day'), $today);
            return [
                'type' => $period,
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'label' => 'Trimestre ' . $quarter . ' — ' . $year,
            ];
        }

        if ($period !== 'month') {
            throw new \InvalidArgumentException('La période sélectionnée est invalide.');
        }

        $month = filter_var($input['month'] ?? $today->format('n'), FILTER_VALIDATE_INT);
        if ($month === false || $month < 1 || $month > 12 || ($year === (int) $today->format('Y') && $month > (int) $today->format('n'))) {
            throw new \InvalidArgumentException('Le mois sélectionné est invalide.');
        }
        $start = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $end = min($start->modify('last day of this month'), $today);

        return [
            'type' => $period,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'label' => $start->format('m/Y'),
        ];
    }

    private function parseDate(string $value): ?\DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }

    private function buildReportData(array $period, ?int $schoolId): array
    {
        $db = Database::getConnection();
        $dateParams = [':start_date' => $period['start'], ':end_date' => $period['end']];
        $schoolFilter = $schoolId !== null ? ' AND %s = :school_id' : '';
        $withSchool = static function (string $column) use ($schoolFilter): string {
            return $schoolFilter === '' ? '' : sprintf($schoolFilter, $column);
        };
        $schoolParams = $schoolId !== null ? [':school_id' => $schoolId] : [];
        $fetchAll = static function (string $sql, array $params) use ($db): array {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        };
        $fetchOne = static function (string $sql, array $params) use ($db): array {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        };

        $payments = $fetchAll(
            'SELECT COALESCE(NULLIF(fs.devise, \'\'), \'USD\') AS devise, COUNT(*) AS nombre, SUM(ece.montant) AS total
             FROM ecritures_comptables_eleves ece
             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
             INNER JOIN eleves el ON el.id = ce.eleve_id
             LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id
             WHERE ece.type_mouvement = \'CREDIT\' AND ece.annulee_at IS NULL
               AND NULLIF(TRIM(ece.reference_recu), \'\') IS NOT NULL
               AND ece.reference_recu NOT LIKE \'REG-FRAIS-%\'
               AND ece.date_operation >= :start_date AND ece.date_operation < DATE_ADD(:end_date, INTERVAL 1 DAY)'
                . $withSchool('el.ecole_id') . ' GROUP BY devise ORDER BY devise',
            $dateParams + $schoolParams
        );
        $payroll = $fetchAll(
            'SELECT COALESCE(NULLIF(p.devise, \'\'), \'USD\') AS devise, COUNT(*) AS nombre, SUM(p.net_a_payer) AS total
             FROM paies_agents p
             WHERE p.date_paiement_effectif >= :start_date AND p.date_paiement_effectif < DATE_ADD(:end_date, INTERVAL 1 DAY)'
                . $withSchool('p.ecole_id') . ' GROUP BY devise ORDER BY devise',
            $dateParams + $schoolParams
        );

        $academicSchool = $withSchool('ac.ecole_id');
        $evaluations = $fetchOne(
            'SELECT COUNT(DISTINCT ev.id) AS nombre, COUNT(n.id) AS notes_saisies, ROUND(AVG(n.note_obtenue), 2) AS moyenne
             FROM evaluations ev
             INNER JOIN attribution_cours ac ON ac.id = ev.attribution_cours_id
             LEFT JOIN notes n ON n.evaluation_id = ev.id
             WHERE ev.date_evaluation >= :start_date AND ev.date_evaluation < DATE_ADD(:end_date, INTERVAL 1 DAY)'
                . $academicSchool,
            $dateParams + $schoolParams
        );
        $attendance = $fetchAll(
            'SELECT p.statut, COUNT(*) AS nombre
             FROM presences_eleves p
             INNER JOIN eleves el ON el.id = p.eleve_id
             WHERE p.date_jour >= :start_date AND p.date_jour < DATE_ADD(:end_date, INTERVAL 1 DAY)'
                . $withSchool('el.ecole_id') . ' GROUP BY p.statut ORDER BY p.statut',
            $dateParams + $schoolParams
        );
        $bulletins = $fetchOne(
            'SELECT COUNT(*) AS nombre
             FROM bulletins_scolaires b
             INNER JOIN parcours_scolaires ps ON ps.id = b.parcours_scolaire_id
             INNER JOIN inscriptions i ON i.id = ps.inscription_id
             INNER JOIN classes c ON c.id = i.classe_id
             WHERE b.created_at >= :start_date AND b.created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)'
                . $withSchool('c.ecole_id'),
            $dateParams + $schoolParams
        );
        $registrations = $fetchOne(
            'SELECT COUNT(*) AS nombre
             FROM inscriptions i
             INNER JOIN classes c ON c.id = i.classe_id
             WHERE i.date_inscription >= :start_date AND i.date_inscription < DATE_ADD(:end_date, INTERVAL 1 DAY)'
                . $withSchool('c.ecole_id'),
            $dateParams + $schoolParams
        );
        $users = $fetchOne(
            'SELECT COUNT(*) AS nombre FROM utilisateurs u
             WHERE u.created_at >= :start_date AND u.created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)'
                . $withSchool('u.ecole_id'),
            $dateParams + $schoolParams
        );
        $events = $fetchOne(
            'SELECT COUNT(*) AS nombre FROM evenements e
             WHERE e.date_debut >= :start_date AND e.date_debut < DATE_ADD(:end_date, INTERVAL 1 DAY)'
                . $withSchool('e.ecole_id'),
            $dateParams + $schoolParams
        );

        return [
            'finance' => ['payments' => $payments, 'payroll' => $payroll],
            'pedagogy' => [
                'evaluations' => (int) ($evaluations['nombre'] ?? 0),
                'scores' => (int) ($evaluations['notes_saisies'] ?? 0),
                'average' => $evaluations['moyenne'] ?? null,
                'attendance' => $attendance,
                'bulletins' => (int) ($bulletins['nombre'] ?? 0),
            ],
            'administration' => [
                'registrations' => (int) ($registrations['nombre'] ?? 0),
                'users' => (int) ($users['nombre'] ?? 0),
                'events' => (int) ($events['nombre'] ?? 0),
            ],
        ];
    }

    private function getReportCategories(string $role): array
    {
        $categories = [
            [
                'title' => 'Finances',
                'description' => 'Suivi des encaissements et des sommes restant à recouvrer.',
                'icon' => 'bi-cash-stack',
                'reports' => [
                    [
                        'title' => 'Paiements encaissés',
                        'description' => 'Consultez les paiements et exportez les résultats en CSV, Excel ou PDF.',
                        'path' => '/paiements',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'parent_ecole'],
                    ],
                    [
                        'title' => 'Créances et recouvrements',
                        'description' => 'Consultez la situation actuelle des dettes et exportez le suivi en CSV, Excel ou PDF.',
                        'path' => '/recouvrements',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école', 'comptable_école'],
                    ],
                    [
                        'title' => 'Paie des agents',
                        'description' => 'Consultez les paiements de salaires de la période.',
                        'path' => '/paies-agents',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école', 'comptable_école'],
                    ],
                ],
            ],
            [
                'title' => 'Pédagogie',
                'description' => 'Consultez les résultats, les bulletins et le suivi de présence.',
                'icon' => 'bi-mortarboard',
                'reports' => [
                    [
                        'title' => 'Notes et résultats',
                        'description' => 'Accédez aux notes et résultats des élèves.',
                        'path' => '/notes',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole'],
                    ],
                    [
                        'title' => 'Bulletins scolaires',
                        'description' => 'Consultez les bulletins et les résultats par période.',
                        'path' => '/bulletins',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole'],
                    ],
                    [
                        'title' => 'Présences et absences',
                        'description' => 'Consultez le suivi de présence des élèves.',
                        'path' => '/presences',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école'],
                    ],
                ],
            ],
            [
                'title' => 'Administration',
                'description' => 'Suivez les inscriptions, les comptes et les activités de l’établissement.',
                'icon' => 'bi-building-gear',
                'reports' => [
                    [
                        'title' => 'Inscriptions et dossiers',
                        'description' => 'Consultez les dossiers en attente et les élèves inscrits.',
                        'path' => '/inscriptions',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école', 'agent_ecole', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école'],
                    ],
                    [
                        'title' => 'Comptes utilisateurs',
                        'description' => 'Consultez les comptes et les rôles des utilisateurs de l’école.',
                        'path' => '/utilisateurs',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école'],
                    ],
                    [
                        'title' => 'Événements scolaires',
                        'description' => 'Consultez le calendrier et les événements de l’établissement.',
                        'path' => '/evenements',
                        'roles' => ['super_admin', 'ecole_admin', 'promoteur_école', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole'],
                    ],
                ],
            ],
        ];

        foreach ($categories as &$category) {
            $category['reports'] = array_values(array_filter(
                $category['reports'],
                static fn (array $report): bool => in_array($role, $report['roles'], true)
                    || ($role === 'promoteur_école' && in_array('ecole_admin', $report['roles'], true))
            ));
        }
        unset($category);

        return array_values(array_filter(
            $categories,
            static fn (array $category): bool => $category['reports'] !== []
        ));
    }
}
