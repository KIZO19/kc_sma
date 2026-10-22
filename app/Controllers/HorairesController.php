<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AnneeScolaire;
use App\Models\Ecole;
use App\Models\Section;
use App\Models\SchoolTimetable;
use App\Models\User;

class HorairesController extends Controller
{
    private const AUTHORITY_ROLES = ['DE_école', 'DP_école', 'DA_école'];

    private const DAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
    ];

    public function exportPdf(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::AUTHORITY_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $sectionId = $this->getSectionScopeForUser($user);
        if ($schoolId <= 0 || $sectionId === null || $sectionId <= 0) {
            $this->redirect('/error/accessDenied');
        }

        $year = AnneeScolaire::getActiveBySchool($schoolId);
        if (!$year) {
            $_SESSION['timetable_error'] = 'Aucune année scolaire active n’est configurée.';
            $this->redirect('/horaires');
        }

        $school = Ecole::findById($schoolId);
        $section = Section::findById($sectionId);
        $resources = SchoolTimetable::getAssignmentResources($schoolId, $sectionId);
        $hourDuration = SchoolTimetable::getHourDuration($schoolId, $sectionId);
        $pdfData = [
            'title' => 'Horaire global des classes',
            'schoolName' => (string) ($school['nom_etablissement'] ?? APP_NAME),
            'sectionName' => (string) ($section['nom_section'] ?? 'Section'),
            'schoolYear' => (string) ($year['annee'] ?? ''),
            'hourDuration' => $hourDuration,
            'dailySchedule' => SchoolTimetable::getDailySchedule($hourDuration),
            'classes' => $resources['classes'],
            'schedule' => SchoolTimetable::getScheduleForSection(
                $schoolId,
                (int) $year['id'],
                $sectionId
            ),
            'teacherWorkloads' => SchoolTimetable::getTeacherWorkloads(
                $schoolId,
                (int) $year['id'],
                $sectionId
            ),
            'days' => self::DAYS,
            'generatedAt' => date('d/m/Y à H:i'),
        ];
        $html = $this->renderSchedulePdfView($pdfData);

        if (class_exists('\\Dompdf\\Dompdf')) {
            $dompdfClass = '\\Dompdf\\Dompdf';
            $dompdf = new $dompdfClass();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A3', 'landscape');
            $dompdf->render();
            header('Content-Type: application/pdf');
            header(
                'Content-Disposition: attachment; filename="horaire_global_'
                . date('Ymd_His') . '.pdf"'
            );
            echo $dompdf->output();
            exit;
        }

        $pdfData['printFallback'] = true;
        $this->view('horaires/global_pdf', $pdfData);
    }

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::AUTHORITY_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $sectionId = $this->getSectionScopeForUser($user);
        if ($schoolId <= 0 || $sectionId === null || $sectionId <= 0) {
            $this->redirect('/error/accessDenied');
        }

        $year = AnneeScolaire::getActiveBySchool($schoolId);
        $hourDuration = SchoolTimetable::getHourDuration($schoolId, $sectionId);
        $resources = $year
            ? SchoolTimetable::getAssignmentResources($schoolId, $sectionId)
            : ['classes' => [], 'teachers' => []];
        $classIds = array_map(static fn (array $class): int => (int) $class['id'], $resources['classes']);
        $requestedClassId = (int) ($_GET['classe_id'] ?? ($classIds[0] ?? 0));
        $classId = $requestedClassId === 0 || in_array($requestedClassId, $classIds, true)
            ? $requestedClassId
            : ($classIds[0] ?? 0);

        $assignments = $year
            ? SchoolTimetable::getAssignments($schoolId, (int) $year['id'], $sectionId)
            : [];
        $coursePlans = $year
            ? SchoolTimetable::getCoursePlans($schoolId, (int) $year['id'], $sectionId)
            : [];
        $editPlanId = (int) ($_GET['edit_plan'] ?? 0);
        $editingCoursePlan = null;
        foreach ($coursePlans as $coursePlan) {
            if ((int) $coursePlan['id'] === $editPlanId) {
                $editingCoursePlan = $coursePlan;
                break;
            }
        }
        $classAssignments = $classId > 0
            ? array_values(array_filter(
                $assignments,
                static fn (array $assignment): bool => (int) $assignment['classe_id'] === $classId
            ))
            : [];
        $plannableAssignments = array_values(array_filter(
            $classAssignments,
            static fn (array $assignment): bool =>
                (int) ($assignment['planned_hours'] ?? 0) < (int) $assignment['volume_horaire_hebdo']
        ));
        $slots = $year
            ? ($classId === 0
                ? SchoolTimetable::getScheduleForSection($schoolId, (int) $year['id'], $sectionId)
                : SchoolTimetable::getScheduleForClass($schoolId, (int) $year['id'], $classId))
            : [];
        $schedule = [];
        foreach ($slots as $slot) {
            if ($classId === 0) {
                $schedule[(int) $slot['jour_id']][(int) $slot['periode']][] = $slot;
            } else {
                $schedule[(int) $slot['jour_id']][(int) $slot['periode']] = $slot;
            }
        }

        if (empty($_SESSION['timetable_csrf'])) {
            $_SESSION['timetable_csrf'] = bin2hex(random_bytes(32));
        }

        $this->view('horaires/index', [
            'title' => APP_NAME . ' - Gestion des horaires',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel((string) ($user['role'] ?? 'default')),
            'modules' => $this->getModulesForRole((string) ($user['role'] ?? 'default')),
            'year' => $year,
            'sectionId' => $sectionId,
            'sectionName' => (string) (Section::findById($sectionId)['nom_section'] ?? 'Section'),
            'hourDuration' => $hourDuration,
            'dailyHours' => SchoolTimetable::getDailyHours(),
            'dailySchedule' => SchoolTimetable::getDailySchedule($hourDuration),
            'classes' => $resources['classes'],
            'teachers' => $resources['teachers'],
            'assignments' => $assignments,
            'coursePlans' => $coursePlans,
            'editingCoursePlan' => $editingCoursePlan,
            'classAssignments' => $classAssignments,
            'plannableAssignments' => $plannableAssignments,
            'classId' => $classId,
            'isGlobalSchedule' => $classId === 0,
            'schedule' => $schedule,
            'teacherWorkloads' => $year
                ? SchoolTimetable::getTeacherWorkloads($schoolId, (int) $year['id'], $sectionId)
                : [],
            'days' => self::DAYS,
            'csrfToken' => $_SESSION['timetable_csrf'],
            'success' => $_SESSION['timetable_success'] ?? null,
            'error' => $_SESSION['timetable_error'] ?? null,
        ]);
        unset($_SESSION['timetable_success'], $_SESSION['timetable_error']);
    }

    public function saveAssignment(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        $_SESSION['timetable_error'] = 'Créez d’abord le cours et définissez son volume, puis attribuez-lui un enseignant.';
        $this->redirect('/horaires');
    }

    public function saveCoursePlan(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            $classIds = $_POST['classe_ids'] ?? [];
            if (!is_array($classIds)) {
                throw new \InvalidArgumentException('Sélectionnez au moins une classe.');
            }
            $planId = (int) ($_POST['programme_id'] ?? 0);
            $courseName = (string) ($_POST['nom_cours'] ?? '');
            $courseCode = (string) ($_POST['code_cours'] ?? '');
            $courseType = (string) ($_POST['nature_cours'] ?? '');
            $weeklyHours = (int) ($_POST['volume_horaire_hebdo'] ?? 0);
            if ($planId > 0) {
                SchoolTimetable::updateCoursePlan(
                    $context['schoolId'],
                    (int) $context['year']['id'],
                    $context['sectionId'],
                    $planId,
                    $courseName,
                    $courseCode,
                    $courseType,
                    $classIds,
                    $weeklyHours
                );
                $_SESSION['timetable_success'] = 'Cours, classes et volume hebdomadaire modifiés.';
            } else {
                SchoolTimetable::createCoursePlan(
                    $context['schoolId'],
                    (int) $context['year']['id'],
                    $context['sectionId'],
                    $courseName,
                    $courseCode,
                    $courseType,
                    $classIds,
                    $weeklyHours
                );
                $_SESSION['timetable_success'] = 'Cours créé, classes et volume horaire enregistrés. Vous pouvez maintenant lui attribuer un enseignant.';
            }
        } catch (\Throwable $exception) {
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $this->redirect('/horaires');
    }

    public function deleteCoursePlan(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            SchoolTimetable::deleteCoursePlan(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId'],
                (int) ($_POST['programme_id'] ?? 0)
            );
            $_SESSION['timetable_success'] = 'Cours supprimé de cette section et de cette année scolaire.';
        } catch (\Throwable $exception) {
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $this->redirect('/horaires');
    }

    public function assignCourseTeacher(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            SchoolTimetable::assignCourseTeacher(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId'],
                (int) ($_POST['programme_id'] ?? 0),
                (int) ($_POST['agent_id'] ?? 0)
            );
            $_SESSION['timetable_success'] = 'Enseignant attribué à toutes les classes du cours. Sa charge hebdomadaire a été mise à jour.';
        } catch (\Throwable $exception) {
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $this->redirect('/horaires');
    }

    public function saveDuration(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            SchoolTimetable::setHourDuration(
                $context['schoolId'],
                $context['sectionId'],
                (int) ($_POST['duree_heure_minutes'] ?? 0)
            );
            $_SESSION['timetable_success'] = 'La durée d’une heure a été enregistrée pour votre section.';
        } catch (\Throwable $exception) {
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $this->redirect('/horaires');
    }

    public function saveSlot(): void
    {
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            $assignmentId = (int) ($_POST['attribution_id'] ?? 0);
            $assignments = SchoolTimetable::getAssignments(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId']
            );
            $assignment = null;
            foreach ($assignments as $candidate) {
                if ((int) $candidate['id'] === $assignmentId) {
                    $assignment = $candidate;
                    break;
                }
            }
            if (!$assignment) {
                throw new \InvalidArgumentException('Cette affectation est introuvable ou hors de votre section.');
            }

            SchoolTimetable::saveSlot(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId'],
                $assignmentId,
                (int) ($_POST['jour_id'] ?? 0),
                (int) ($_POST['periode'] ?? 0)
            );
            $message = 'Créneau enregistré. Pour un cours de tronc commun, il est synchronisé dans toutes les classes concernées.';
            if ($isAjax) {
                $classId = (int) $assignment['classe_id'];
                $dayId = (int) ($_POST['jour_id'] ?? 0);
                $period = (int) ($_POST['periode'] ?? 0);
                $slot = null;
                foreach (
                    SchoolTimetable::getScheduleForClass(
                        $context['schoolId'],
                        (int) $context['year']['id'],
                        $classId
                    ) as $scheduledSlot
                ) {
                    if (
                        (int) $scheduledSlot['jour_id'] === $dayId
                        && (int) $scheduledSlot['periode'] === $period
                        && (int) $scheduledSlot['attribution_cours_id'] === $assignmentId
                    ) {
                        $slot = $scheduledSlot;
                        break;
                    }
                }
                if (!$slot) {
                    throw new \RuntimeException('Le créneau a été enregistré mais n’a pas pu être relu.');
                }
                $updatedAssignments = SchoolTimetable::getAssignments(
                    $context['schoolId'],
                    (int) $context['year']['id'],
                    $context['sectionId'],
                    $classId
                );
                $this->jsonResponse([
                    'success' => true,
                    'message' => $message,
                    'csrfToken' => (string) $_SESSION['timetable_csrf'],
                    'slot' => [
                        'id' => (int) $slot['id'],
                        'classe_id' => $classId,
                        'nom_cours' => (string) $assignment['nom_cours'],
                        'indice' => (int) $assignment['indice'],
                    ],
                    'assignments' => array_map(
                        static fn (array $updatedAssignment): array => [
                            'id' => (int) $updatedAssignment['id'],
                            'remaining' => max(
                                0,
                                (int) $updatedAssignment['volume_horaire_hebdo']
                                - (int) $updatedAssignment['planned_hours']
                            ),
                            'nom_cours' => (string) $updatedAssignment['nom_cours'],
                            'indice' => (int) $updatedAssignment['indice'],
                        ],
                        $updatedAssignments
                    ),
                ]);
            }
            $_SESSION['timetable_success'] = $message;
        } catch (\Throwable $exception) {
            if ($isAjax) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'csrfToken' => (string) ($_SESSION['timetable_csrf'] ?? ''),
                ], 422);
            }
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $classId = (int) ($_POST['classe_id'] ?? 0);
        $this->redirect('/horaires' . ($classId > 0 ? '?classe_id=' . $classId : ''));
    }

    public function deleteAssignment(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            SchoolTimetable::deleteAssignment(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId'],
                (int) ($_POST['assignment_id'] ?? 0)
            );
            $_SESSION['timetable_success'] = 'Affectation et créneaux associés supprimés.';
        } catch (\Throwable $exception) {
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $classId = (int) ($_POST['classe_id'] ?? 0);
        $this->redirect('/horaires' . ($classId > 0 ? '?classe_id=' . $classId : ''));
    }

    public function deleteSlot(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            $deleted = SchoolTimetable::deleteSlot(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId'],
                (int) ($_POST['slot_id'] ?? 0)
            );
            $_SESSION[$deleted ? 'timetable_success' : 'timetable_error'] = $deleted
                ? 'Créneau supprimé. Pour un cours de tronc commun, les créneaux synchronisés des autres classes ont aussi été supprimés.'
                : 'Le créneau n’a pas pu être supprimé.';
        } catch (\Throwable $exception) {
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $classId = (int) ($_POST['classe_id'] ?? 0);
        $this->redirect('/horaires' . ($classId > 0 ? '?classe_id=' . $classId : ''));
    }

    public function saveDayOff(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            $day = (string) ($_POST['jour_id'] ?? '');
            SchoolTimetable::setWeeklyDayOff(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId'],
                (int) ($_POST['agent_id'] ?? 0),
                $day === '' ? null : (int) $day
            );
            $_SESSION['timetable_success'] = 'Journée hebdomadaire enregistrée.';
        } catch (\Throwable $exception) {
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $this->redirect('/horaires');
    }

    private function postContext(): ?array
    {
        Auth::requireAuth();
        Auth::requireRoles(self::AUTHORITY_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $sectionId = $this->getSectionScopeForUser($user);
        if ($schoolId <= 0 || $sectionId === null || $sectionId <= 0) {
            $this->redirect('/error/accessDenied');
        }
        $submittedToken = (string) ($_POST['csrf_token'] ?? '');
        $expectedToken = (string) ($_SESSION['timetable_csrf'] ?? '');
        if ($submittedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
            $_SESSION['timetable_error'] = 'Votre session a expiré. Rechargez la page avant de réessayer.';
            $this->redirect('/horaires');
        }
        $_SESSION['timetable_csrf'] = bin2hex(random_bytes(32));

        return [
            'schoolId' => $schoolId,
            'sectionId' => $sectionId,
            'year' => AnneeScolaire::getActiveBySchool($schoolId),
        ];
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    private function renderSchedulePdfView(array $data): string
    {
        extract($data);
        ob_start();
        require dirname(__DIR__) . '/Views/horaires/global_pdf.php';

        return (string) ob_get_clean();
    }
}
