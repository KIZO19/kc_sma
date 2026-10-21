<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AnneeScolaire;
use App\Models\SchoolTimetable;
use App\Models\User;

class HorairesController extends Controller
{
    private const DAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['DE_école']);
        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $sectionId = $this->getSectionScopeForUser($user);
        if ($schoolId <= 0 || $sectionId === null) {
            $this->redirect('/error/accessDenied');
        }

        $year = AnneeScolaire::getActiveBySchool($schoolId);
        $resources = $year
            ? SchoolTimetable::getAssignmentResources($schoolId, $sectionId)
            : ['classes' => [], 'courses' => [], 'teachers' => []];
        $classIds = array_map(static fn (array $class): int => (int) $class['id'], $resources['classes']);
        $classId = (int) ($_GET['classe_id'] ?? ($classIds[0] ?? 0));
        if (!in_array($classId, $classIds, true)) {
            $classId = $classIds[0] ?? 0;
        }

        $assignments = $year
            ? SchoolTimetable::getAssignments($schoolId, (int) $year['id'], $sectionId)
            : [];
        $classAssignments = array_values(array_filter(
            $assignments,
            static fn (array $assignment): bool => (int) $assignment['classe_id'] === $classId
        ));
        $slots = $year && $classId > 0
            ? SchoolTimetable::getScheduleForClass($schoolId, (int) $year['id'], $classId)
            : [];
        $schedule = [];
        foreach ($slots as $slot) {
            $schedule[(int) $slot['jour_id']][(int) $slot['periode']] = $slot;
        }

        $editId = (int) ($_GET['edit_id'] ?? 0);
        $editingAssignment = null;
        foreach ($assignments as $assignment) {
            if ((int) $assignment['id'] === $editId) {
                $editingAssignment = $assignment;
                break;
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
            'classes' => $resources['classes'],
            'courses' => $resources['courses'],
            'teachers' => $resources['teachers'],
            'assignments' => $assignments,
            'classAssignments' => $classAssignments,
            'classId' => $classId,
            'schedule' => $schedule,
            'editingAssignment' => $editingAssignment,
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
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            $assignmentId = filter_var($_POST['assignment_id'] ?? null, FILTER_VALIDATE_INT);
            SchoolTimetable::saveAssignment(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId'],
                (int) ($_POST['classe_id'] ?? 0),
                (int) ($_POST['cours_id'] ?? 0),
                (int) ($_POST['agent_id'] ?? 0),
                (int) ($_POST['volume_horaire_hebdo'] ?? 0),
                $assignmentId && $assignmentId > 0 ? $assignmentId : null
            );
            $_SESSION['timetable_success'] = 'Affectation enregistrée.';
        } catch (\Throwable $exception) {
            $_SESSION['timetable_error'] = $exception->getMessage();
        }

        $classId = (int) ($_POST['classe_id'] ?? 0);
        $this->redirect('/horaires' . ($classId > 0 ? '?classe_id=' . $classId : ''));
    }

    public function saveSlot(): void
    {
        $context = $this->postContext();
        if (!$context) {
            return;
        }
        try {
            if (!$context['year']) {
                throw new \RuntimeException('Aucune année scolaire active n’est configurée.');
            }
            SchoolTimetable::saveSlot(
                $context['schoolId'],
                (int) $context['year']['id'],
                $context['sectionId'],
                (int) ($_POST['attribution_id'] ?? 0),
                (int) ($_POST['jour_id'] ?? 0),
                (int) ($_POST['periode'] ?? 0)
            );
            $_SESSION['timetable_success'] = 'Créneau enregistré.';
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
                ? 'Créneau supprimé.'
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
        Auth::requireRoles(['DE_école']);
        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $sectionId = $this->getSectionScopeForUser($user);
        if ($schoolId <= 0 || $sectionId === null) {
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
}
