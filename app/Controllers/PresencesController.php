<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\PresenceAgent;
use App\Models\PresenceEleve;
use App\Models\User;

class PresencesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $isTeacher = $role === 'enseignant_école';
        $teacherAgentId = $isTeacher ? (int) ($user['reference_id'] ?? 0) : null;
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $attendanceMode = (string) ($_GET['mode'] ?? $_POST['mode'] ?? ($role === 'DE_école' ? 'enseignants' : 'eleves'));
        $classes = ($role === 'super_admin' || $schoolId > 0)
            ? PresenceEleve::getAvailableClasses($role === 'super_admin' ? null : $schoolId, $teacherAgentId, $sectionScopeId)
            : [];
        $csrfToken = (string) ($_SESSION['attendance_csrf_token'] ?? '');
        if ($csrfToken === '') {
            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['attendance_csrf_token'] = $csrfToken;
        }

        if ($attendanceMode === 'enseignants') {
            if (!in_array($role, ['ecole_admin', 'DE_école'], true)) {
                $this->redirect('/error/accessDenied');
            }

            $date = (string) ($_POST['date_jour'] ?? $_GET['date'] ?? date('Y-m-d'));
            $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date || $parsedDate > new \DateTimeImmutable('today')) {
                $date = date('Y-m-d');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $postedToken = (string) ($_POST['csrf_token'] ?? '');
                if ($postedToken === '' || !hash_equals($csrfToken, $postedToken)) {
                    $_SESSION['presence_error'] = 'Votre session a expiré. Rechargez la page puis réessayez.';
                    $this->redirect('/presences?mode=enseignants');
                }

                try {
                    $savedCount = PresenceAgent::saveForDate(
                        $schoolId,
                        $sectionScopeId,
                        $date,
                        (array) ($_POST['attendance'] ?? []),
                        (array) ($_POST['justification'] ?? [])
                    );
                    $_SESSION['presence_success'] = $savedCount . ' présence(s) enseignante(s) enregistrée(s).';
                } catch (\Throwable $exception) {
                    error_log('PresencesController teacher attendance save failed: ' . $exception->getMessage());
                    $_SESSION['presence_error'] = 'Impossible d’enregistrer les présences des enseignants.';
                }
                $_SESSION['attendance_csrf_token'] = bin2hex(random_bytes(32));
                $this->redirect('/presences?mode=enseignants&date=' . rawurlencode($date));
            }

            $teachers = PresenceAgent::getTeachersForDate($schoolId, $sectionScopeId, $date);
            $success = (string) ($_SESSION['presence_success'] ?? '');
            $error = (string) ($_SESSION['presence_error'] ?? '');
            unset($_SESSION['presence_success'], $_SESSION['presence_error']);

            $this->view('presences/teachers', [
                'title' => APP_NAME . ' - Présences des enseignants',
                'user' => $user,
                'role' => $role,
                'roleLabel' => User::getRoleLabel($role),
                'modules' => $modules,
                'date' => $date,
                'teachers' => $teachers,
                'csrfToken' => $csrfToken,
                'success' => $success,
                'error' => $error,
            ]);
            return;
        }

        $selectedClassId = (int) ($_POST['class_id'] ?? $_GET['class_id'] ?? 0);
        $selectedClass = null;
        foreach ($classes as $class) {
            if ((int) $class['id'] === $selectedClassId) {
                $selectedClass = $class;
                break;
            }
        }
        if ($selectedClass === null && $classes !== [] && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $selectedClass = $classes[0];
            $selectedClassId = (int) $selectedClass['id'];
        }

        $date = (string) ($_POST['date_jour'] ?? $_GET['date'] ?? date('Y-m-d'));
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date || $parsedDate > new \DateTimeImmutable('today')) {
            $date = date('Y-m-d');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postedToken = (string) ($_POST['csrf_token'] ?? '');
            if ($postedToken === '' || !hash_equals($csrfToken, $postedToken)) {
                $_SESSION['presence_error'] = 'Votre session a expiré. Rechargez la page puis réessayez.';
                $this->redirect('/presences');
            }
            if ($selectedClass === null) {
                $_SESSION['presence_error'] = 'Aucune classe autorisée n’est disponible pour votre compte.';
                $this->redirect('/presences');
            }

            $students = PresenceEleve::getStudentsForClass(
                (int) $selectedClass['ecole_id'],
                $selectedClassId,
                $date
            );
            $studentIds = array_map(static fn (array $student): int => (int) $student['id'], $students);
            $savedCount = PresenceEleve::saveForDate($studentIds, $date, (array) ($_POST['attendance'] ?? []));
            $_SESSION['presence_success'] = $savedCount . ' présence(s) enregistrée(s).';
            $_SESSION['attendance_csrf_token'] = bin2hex(random_bytes(32));
            $this->redirect('/presences?class_id=' . $selectedClassId . '&date=' . rawurlencode($date));
        }

        $students = $selectedClass !== null
            ? PresenceEleve::getStudentsForClass((int) $selectedClass['ecole_id'], $selectedClassId, $date)
            : [];
        $success = (string) ($_SESSION['presence_success'] ?? '');
        $error = (string) ($_SESSION['presence_error'] ?? '');
        unset($_SESSION['presence_success'], $_SESSION['presence_error']);

        $this->view('presences/index', [
            'title' => APP_NAME . ' - Présences',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'selectedClass' => $selectedClass,
            'date' => $date,
            'students' => $students,
            'csrfToken' => $csrfToken,
            'success' => $success,
            'error' => $error,
            'isTeacher' => $isTeacher,
        ]);
    }
}
