<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AnneeScolaire;
use App\Models\SchoolTimetable;
use App\Models\User;

class MonEmploiController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['eleve_ecole', 'eleve_école', 'enseignant_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = (string) ($user['role'] ?? 'default');
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $year = $schoolId > 0 ? AnneeScolaire::getActiveBySchool($schoolId) : null;
        $schedule = [];
        $className = null;
        $weeklyDayOff = null;
        $hourDurations = [];
        $classSectionId = null;

        if ($year && $role === 'enseignant_école') {
            $agentId = (int) ($user['reference_id'] ?? 0);
            $schedule = SchoolTimetable::getScheduleForTeacher(
                $schoolId,
                (int) $year['id'],
                $agentId
            );
            $weeklyDayOff = SchoolTimetable::getTeacherDayOff($schoolId, (int) $year['id'], $agentId);
            foreach ($schedule as $slot) {
                $sectionId = (int) $slot['section_id'];
                $hourDurations[$sectionId] = SchoolTimetable::getHourDuration($schoolId, $sectionId);
            }
        } elseif ($year && in_array($role, ['eleve_ecole', 'eleve_école'], true)) {
            $class = SchoolTimetable::getStudentClass(
                $schoolId,
                (int) ($user['reference_id'] ?? 0)
            );
            if ($class) {
                $className = (string) $class['nom_classe'];
                $classSectionId = (int) $class['section_id'];
                $hourDurations[$classSectionId] = SchoolTimetable::getHourDuration($schoolId, $classSectionId);
                $schedule = SchoolTimetable::getScheduleForClass(
                    $schoolId,
                    (int) $class['annee_scolaire_id'],
                    (int) $class['classe_id']
                );
            }
        }

        $this->view('emploi/index', [
            'title' => APP_NAME . ' - Mon emploi du temps',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'year' => $year,
            'schedule' => $schedule,
            'className' => $className,
            'weeklyDayOff' => $weeklyDayOff,
            'hourDurations' => $hourDurations,
            'classSectionId' => $classSectionId,
            'isStudent' => in_array($role, ['eleve_ecole', 'eleve_école'], true),
            'days' => [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'],
        ]);
    }
}
