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
        Auth::requireRoles(['eleve_ecole', 'enseignant_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = (string) ($user['role'] ?? 'default');
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $year = $schoolId > 0 ? AnneeScolaire::getActiveBySchool($schoolId) : null;
        $schedule = [];
        $className = null;

        if ($year && $role === 'enseignant_école') {
            $schedule = SchoolTimetable::getScheduleForTeacher(
                $schoolId,
                (int) $year['id'],
                (int) ($user['reference_id'] ?? 0)
            );
        } elseif ($year && $role === 'eleve_ecole') {
            $class = SchoolTimetable::getStudentClass(
                $schoolId,
                (int) ($user['reference_id'] ?? 0)
            );
            if ($class) {
                $className = (string) $class['nom_classe'];
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
            'isStudent' => $role === 'eleve_ecole',
            'days' => [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'],
        ]);
    }
}
