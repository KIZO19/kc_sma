<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Communique;
use App\Models\Derogation;
use App\Models\Eleve;
use App\Models\SchoolTimetable;
use App\Models\User;

class EspaceEleveController extends Controller
{
    public function discipline(): void
    {
        [$user, $student, $class] = $this->studentContext();

        $this->renderRecords($user, $student, $class, 'discipline', Eleve::getDiscipline((int) $student['id']));
    }

    public function derogations(): void
    {
        [$user, $student, $class] = $this->studentContext();

        $this->renderRecords(
            $user,
            $student,
            $class,
            'derogations',
            Derogation::getForStudent((int) $student['id'], (int) $user['ecole_id'])
        );
    }

    public function cours(): void
    {
        [$user, $student, $class] = $this->studentContext();
        $courses = [];
        if ($class) {
            $stmt = Database::getConnection()->prepare(
                'SELECT co.nom_cours, ac.volume_horaire_hebdo,
                        a.nom AS enseignant_nom, a.postnom AS enseignant_postnom, a.prenom AS enseignant_prenom
                 FROM attribution_cours ac
                 INNER JOIN cours co ON co.id = ac.cours_id AND co.ecole_id = ac.ecole_id
                 LEFT JOIN agents a ON a.id = ac.agent_id AND a.ecole_id = ac.ecole_id
                 WHERE ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
                   AND ac.classe_id = :classe_id
                 ORDER BY co.nom_cours'
            );
            $stmt->execute([
                ':ecole_id' => (int) $user['ecole_id'],
                ':annee_scolaire_id' => (int) $class['annee_scolaire_id'],
                ':classe_id' => (int) $class['classe_id'],
            ]);
            $courses = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $this->renderRecords($user, $student, $class, 'cours', $courses);
    }

    public function evaluations(): void
    {
        [$user, $student, $class] = $this->studentContext();
        $evaluations = [];
        if ($class) {
            $stmt = Database::getConnection()->prepare(
                'SELECT ev.type_evaluation, ev.date_evaluation, ev.ponderation_max,
                        p.nom_periode, co.nom_cours, n.note_obtenue
                 FROM evaluations ev
                 INNER JOIN attribution_cours ac ON ac.id = ev.attribution_cours_id
                 INNER JOIN cours co ON co.id = ac.cours_id AND co.ecole_id = ac.ecole_id
                 LEFT JOIN periodes p ON p.id = ev.periode_id
                 LEFT JOIN notes n ON n.evaluation_id = ev.id AND n.eleve_id = :eleve_id
                 WHERE ac.ecole_id = :ecole_id AND ac.classe_id = :classe_id
                   AND ac.annee_scolaire_id = :annee_scolaire_id
                 ORDER BY ev.date_evaluation DESC, co.nom_cours'
            );
            $stmt->execute([
                ':eleve_id' => (int) $student['id'],
                ':ecole_id' => (int) $user['ecole_id'],
                ':classe_id' => (int) $class['classe_id'],
                ':annee_scolaire_id' => (int) $class['annee_scolaire_id'],
            ]);
            $evaluations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $this->renderRecords($user, $student, $class, 'evaluations', $evaluations);
    }

    public function communiques(): void
    {
        [$user, $student, $class] = $this->studentContext();
        $sectionId = $class ? (int) ($class['section_id'] ?? 0) : null;

        $this->renderRecords(
            $user,
            $student,
            $class,
            'communiques',
            Communique::latestForAudience(
                (int) $user['ecole_id'],
                (string) ($user['role'] ?? 'eleve_ecole'),
                $sectionId
            )
        );
    }

    private function studentContext(): array
    {
        Auth::requireAuth();
        Auth::requireRoles(['eleve_ecole', 'eleve_école']);

        $user = Auth::refresh() ?: Auth::user();
        $studentId = (int) ($user['reference_id'] ?? 0);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $student = $studentId > 0 && $schoolId > 0
            ? Eleve::findByIdAndSchool($studentId, $schoolId)
            : null;
        if (!$student) {
            $this->redirect('/error/accessDenied');
        }

        $class = SchoolTimetable::getStudentClass($schoolId, $studentId);
        return [$user, $student, $class];
    }

    private function renderRecords(array $user, array $student, ?array $class, string $kind, array $records): void
    {
        $role = (string) ($user['role'] ?? 'eleve_ecole');
        $titles = [
            'discipline' => 'Mon dossier disciplinaire',
            'derogations' => 'Mes dérogations',
            'cours' => 'Mes cours',
            'evaluations' => 'Mes évaluations',
            'communiques' => 'Mes communiqués',
        ];

        $this->view('espace_eleve/records', [
            'title' => APP_NAME . ' - ' . $titles[$kind],
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'student' => $student,
            'studentClass' => $class,
            'recordType' => $kind,
            'records' => $records,
        ]);
    }
}
