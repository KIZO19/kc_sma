<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\TeacherAssignment;
use App\Models\User;

class NotesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $this->requireAdministrationModuleAccess($user, 'notes');
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        if ($role === 'enseignant_école') {
            $schoolId = (int) ($user['ecole_id'] ?? 0);
            $agentId = (int) ($user['reference_id'] ?? 0);
            $courses = TeacherAssignment::getCourses($schoolId, $agentId);
            $courseIds = array_map(static fn (array $course): int => (int) $course['attribution_id'], $courses);
            $assignmentId = (int) ($_POST['attribution_id'] ?? $_GET['attribution_id'] ?? ($courseIds[0] ?? 0));
            if (!in_array($assignmentId, $courseIds, true)) {
                $assignmentId = $_SERVER['REQUEST_METHOD'] === 'POST' ? 0 : ($courseIds[0] ?? 0);
            }
            $selectedCourse = null;
            foreach ($courses as $course) {
                if ((int) $course['attribution_id'] === $assignmentId) {
                    $selectedCourse = $course;
                    break;
                }
            }

            $periods = $assignmentId > 0 ? TeacherAssignment::getPeriods($assignmentId, $agentId) : [];
            $periodIds = array_map(static fn (array $period): int => (int) $period['id'], $periods);
            $selectedPeriodId = (int) ($_POST['period_id'] ?? $_GET['period_id'] ?? ($periodIds[0] ?? 0));
            if (!in_array($selectedPeriodId, $periodIds, true)) {
                $selectedPeriodId = $_SERVER['REQUEST_METHOD'] === 'POST' ? 0 : ($periodIds[0] ?? 0);
            }

            $message = '';
            $error = '';
            $csrfToken = (string) ($_SESSION['teacher_notes_csrf'] ?? '');
            if ($csrfToken === '') {
                $csrfToken = bin2hex(random_bytes(32));
                $_SESSION['teacher_notes_csrf'] = $csrfToken;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $postedToken = (string) ($_POST['csrf_token'] ?? '');
                try {
                    if ($postedToken === '' || !hash_equals($csrfToken, $postedToken)) {
                        throw new \RuntimeException('Votre session a expiré. Rechargez la page puis réessayez.');
                    }
                    $action = (string) ($_POST['action'] ?? '');
                    if ($action === 'create_evaluation') {
                        $type = (string) ($_POST['type_evaluation'] ?? '');
                        $dateValue = (string) ($_POST['date_evaluation'] ?? '');
                        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateValue);
                        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $dateValue || $parsedDate > new \DateTimeImmutable('today')) {
                            throw new \InvalidArgumentException('La date de l’évaluation est invalide.');
                        }
                        $maximum = (int) ($_POST['ponderation_max'] ?? 0);
                        $courseMaximum = $type === 'Examen'
                            ? (int) ($selectedCourse['max_examen'] ?? 0)
                            : (int) ($selectedCourse['max_evaluation'] ?? 0);
                        if ($maximum <= 0 || $maximum > $courseMaximum) {
                            throw new \InvalidArgumentException('Le maximum dépasse le plafond autorisé pour ce cours.');
                        }
                        $evaluationId = TeacherAssignment::createEvaluation(
                            $assignmentId,
                            $agentId,
                            $selectedPeriodId,
                            $type,
                            $dateValue,
                            $maximum
                        );
                        $message = 'Évaluation créée. Vous pouvez maintenant saisir les notes.';
                    } elseif ($action === 'update_scores') {
                        $evaluationId = (int) ($_POST['evaluation_id'] ?? 0);
                        $saved = TeacherAssignment::saveScores($evaluationId, $agentId, (array) ($_POST['scores'] ?? []));
                        $message = $saved . ' note(s) mise(s) à jour.';
                    } else {
                        throw new \InvalidArgumentException('Action inconnue.');
                    }
                    $_SESSION['teacher_notes_success'] = $message;
                    $_SESSION['teacher_notes_csrf'] = bin2hex(random_bytes(32));
                    $redirect = '/notes?attribution_id=' . $assignmentId . '&period_id=' . $selectedPeriodId;
                    if (!empty($evaluationId)) {
                        $redirect .= '&evaluation_id=' . (int) $evaluationId;
                    }
                    $this->redirect($redirect);
                } catch (\Throwable $exception) {
                    $error = $exception->getMessage();
                }
            }

            $evaluationId = (int) ($_GET['evaluation_id'] ?? ($evaluationId ?? 0));
            $selectedEvaluation = $evaluationId > 0
                ? TeacherAssignment::findEvaluation($evaluationId, $agentId)
                : null;
            if ($selectedEvaluation && (int) $selectedEvaluation['attribution_cours_id'] !== $assignmentId) {
                $selectedEvaluation = null;
            }
            $evaluations = $assignmentId > 0 ? TeacherAssignment::getEvaluations($assignmentId, $agentId) : [];
            $students = $selectedEvaluation
                ? TeacherAssignment::getStudentsForEvaluation($selectedEvaluation)
                : [];
            if (!empty($_SESSION['teacher_notes_success'])) {
                $message = (string) $_SESSION['teacher_notes_success'];
                unset($_SESSION['teacher_notes_success']);
            }

            $this->view('notes/teacher', [
                'title' => APP_NAME . ' - Notes de mes cours',
                'user' => $user,
                'role' => $role,
                'roleLabel' => User::getRoleLabel($role),
                'modules' => $modules,
                'courses' => $courses,
                'selectedCourse' => $selectedCourse,
                'assignmentId' => $assignmentId,
                'periods' => $periods,
                'periodId' => $selectedPeriodId,
                'evaluations' => $evaluations,
                'selectedEvaluation' => $selectedEvaluation,
                'students' => $students,
                'csrfToken' => $csrfToken,
                'success' => $message,
                'error' => $error,
            ]);
            return;
        }

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Notes',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Notes',
            'pageDescription' => 'Créez des évaluations et enregistrez les notes.',
            'pageContent' => 'Gestion des notes et des évaluations.',
            'pageNotes' => 'Page accessible aux enseignants, aux élèves et aux parents selon le rôle.',
        ]);
    }
}
