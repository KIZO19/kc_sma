<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class TeacherAssignment
{
    public static function getClasses(int $schoolId, int $agentId): array
    {
        if ($schoolId <= 0 || $agentId <= 0) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT DISTINCT c.id, c.ecole_id, c.nom_classe
             FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             INNER JOIN agents ag ON ag.id = ac.agent_id AND ag.ecole_id = ac.ecole_id
             INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.ecole_id = ac.ecole_id AND a.est_active = 1
             WHERE ac.ecole_id = :ecole_id AND ac.agent_id = :agent_id
             ORDER BY c.nom_classe ASC'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':agent_id' => $agentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStudents(int $schoolId, int $agentId, ?int $classId = null): array
    {
        if ($schoolId <= 0 || $agentId <= 0) {
            return [];
        }

        $sql = 'SELECT DISTINCT e.id, e.matricule, e.nom, e.postnom, e.prenom,
                   e.statut_eleve, e.genre, e.date_naissance,
                   c.id AS classe_id, c.nom_classe, s.nom_section, o.nom_option,
                   a.annee AS annee_scolaire, i.date_inscription,
                   p.id AS parent_id, p.nom_responsable AS parent_nom_responsable,
                   p.telephone AS parent_telephone
                FROM attribution_cours ac
                INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
                INNER JOIN agents ag ON ag.id = ac.agent_id AND ag.ecole_id = ac.ecole_id
                INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.ecole_id = ac.ecole_id AND a.est_active = 1
                INNER JOIN inscriptions i ON i.classe_id = c.id AND i.annee_scolaire_id = a.id
                INNER JOIN eleves e ON e.id = i.eleve_id
            LEFT JOIN sections s ON s.id = c.section_id
            LEFT JOIN options o ON o.id = c.option_id
            LEFT JOIN parents p ON p.id = e.parent_id
                WHERE ac.ecole_id = :ecole_id AND ac.agent_id = :agent_id';
        $params = [':ecole_id' => $schoolId, ':agent_id' => $agentId];

        if ($classId !== null) {
            $sql .= ' AND c.id = :classe_id';
            $params[':classe_id'] = $classId;
        }

        $sql .= ' ORDER BY c.nom_classe ASC, e.nom ASC, e.postnom ASC, e.prenom ASC';
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function teacherCanAccessStudent(int $schoolId, int $agentId, int $studentId): bool
    {
        if ($studentId <= 0) {
            return false;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT 1
             FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             INNER JOIN agents ag ON ag.id = ac.agent_id AND ag.ecole_id = ac.ecole_id
             INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.ecole_id = ac.ecole_id AND a.est_active = 1
             INNER JOIN inscriptions i ON i.classe_id = c.id AND i.annee_scolaire_id = a.id
             WHERE ac.ecole_id = :ecole_id AND ac.agent_id = :agent_id AND i.eleve_id = :eleve_id
             LIMIT 1'
        );
        $stmt->execute([
            ':ecole_id' => $schoolId,
            ':agent_id' => $agentId,
            ':eleve_id' => $studentId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public static function getCourses(int $schoolId, int $agentId): array
    {
        if ($schoolId <= 0 || $agentId <= 0) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT ac.id AS attribution_id, ac.classe_id, ac.cours_id, ac.annee_scolaire_id,
                    ac.max_evaluation, ac.max_examen, c.nom_classe, co.nom_cours, a.annee
             FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             INNER JOIN cours co ON co.id = ac.cours_id AND co.ecole_id = ac.ecole_id
             INNER JOIN agents ag ON ag.id = ac.agent_id AND ag.ecole_id = ac.ecole_id
             INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.ecole_id = ac.ecole_id AND a.est_active = 1
             WHERE ac.ecole_id = :ecole_id AND ac.agent_id = :agent_id
             ORDER BY c.nom_classe ASC, co.nom_cours ASC'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':agent_id' => $agentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPeriods(int $assignmentId, int $agentId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT p.id, p.nom_periode, p.cloturee
             FROM attribution_cours ac
             INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.est_active = 1
             INNER JOIN periodes p ON p.annee_scolaire_id = a.id
             WHERE ac.id = :attribution_id AND ac.agent_id = :agent_id
             ORDER BY p.id ASC'
        );
        $stmt->execute([':attribution_id' => $assignmentId, ':agent_id' => $agentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEvaluations(int $assignmentId, int $agentId, int $periodId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT ev.id, ev.periode_id, ev.type_evaluation, ev.date_evaluation, ev.ponderation_max, p.nom_periode,
                    COUNT(n.id) AS notes_saisies
             FROM evaluations ev
             INNER JOIN attribution_cours ac ON ac.id = ev.attribution_cours_id
             INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.est_active = 1
             INNER JOIN periodes p ON p.id = ev.periode_id
             LEFT JOIN notes n ON n.evaluation_id = ev.id
             WHERE ac.id = :attribution_id AND ac.agent_id = :agent_id AND ev.periode_id = :period_id
             GROUP BY ev.id, ev.periode_id, ev.type_evaluation, ev.date_evaluation, ev.ponderation_max, p.nom_periode
             ORDER BY ev.date_evaluation DESC, ev.id DESC'
        );
        $stmt->execute([':attribution_id' => $assignmentId, ':agent_id' => $agentId, ':period_id' => $periodId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findEvaluation(int $evaluationId, int $agentId): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT ev.*, ac.classe_id, ac.annee_scolaire_id, p.nom_periode, p.cloturee, c.nom_classe, co.nom_cours
             FROM evaluations ev
             INNER JOIN attribution_cours ac ON ac.id = ev.attribution_cours_id AND ac.agent_id = :agent_id
             INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.est_active = 1
             INNER JOIN periodes p ON p.id = ev.periode_id AND p.annee_scolaire_id = a.id
             INNER JOIN classes c ON c.id = ac.classe_id
             INNER JOIN cours co ON co.id = ac.cours_id
             WHERE ev.id = :evaluation_id LIMIT 1'
        );
        $stmt->execute([':agent_id' => $agentId, ':evaluation_id' => $evaluationId]);
        $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

        return $evaluation ?: null;
    }

    public static function getStudentsForEvaluation(array $evaluation): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT e.id, e.matricule, e.nom, e.postnom, e.prenom, n.note_obtenue
             FROM inscriptions i
             INNER JOIN eleves e ON e.id = i.eleve_id
             LEFT JOIN notes n ON n.eleve_id = e.id AND n.evaluation_id = :evaluation_id
             WHERE i.classe_id = :classe_id AND i.annee_scolaire_id = :annee_scolaire_id
             ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC'
        );
        $stmt->execute([
            ':evaluation_id' => (int) $evaluation['id'],
            ':classe_id' => (int) $evaluation['classe_id'],
            ':annee_scolaire_id' => (int) $evaluation['annee_scolaire_id'],
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createEvaluation(int $assignmentId, int $agentId, int $periodId, string $type, string $date, int $maximum): int
    {
        $allowedTypes = ['Interrogation', 'Devoir', 'Exposé', 'Examen'];
        if (!in_array($type, $allowedTypes, true) || $maximum <= 0 || $maximum > 100) {
            throw new \InvalidArgumentException('Les paramètres de l’évaluation sont invalides.');
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ac.id
             FROM attribution_cours ac
             INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.est_active = 1
             INNER JOIN periodes p ON p.annee_scolaire_id = a.id AND p.id = :periode_id AND p.cloturee = 0
             WHERE ac.id = :attribution_id AND ac.agent_id = :agent_id LIMIT 1'
        );
        $stmt->execute([
            ':periode_id' => $periodId,
            ':attribution_id' => $assignmentId,
            ':agent_id' => $agentId,
        ]);
        if (!$stmt->fetchColumn()) {
            throw new \RuntimeException('Ce cours ou cette période n’est pas autorisé.');
        }

        $insert = $db->prepare(
            'INSERT INTO evaluations (attribution_cours_id, periode_id, type_evaluation, date_evaluation, ponderation_max)
             VALUES (:attribution_id, :periode_id, :type, :date, :maximum)'
        );
        $insert->execute([
            ':attribution_id' => $assignmentId,
            ':periode_id' => $periodId,
            ':type' => $type,
            ':date' => $date,
            ':maximum' => $maximum,
        ]);

        return (int) $db->lastInsertId();
    }

    public static function saveScores(int $evaluationId, int $agentId, array $scores): int
    {
        $evaluation = self::findEvaluation($evaluationId, $agentId);
        if (!$evaluation || (int) $evaluation['cloturee'] === 1) {
            throw new \RuntimeException('Cette évaluation n’est pas accessible ou sa période est clôturée.');
        }

        $students = self::getStudentsForEvaluation($evaluation);
        $allowedIds = array_fill_keys(array_map(static fn (array $student): int => (int) $student['id'], $students), true);
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO notes (evaluation_id, eleve_id, note_obtenue)
             VALUES (:evaluation_id, :eleve_id, :score)
             ON DUPLICATE KEY UPDATE note_obtenue = :updated_score'
        );
        $saved = 0;

        $db->beginTransaction();
        try {
            foreach ($scores as $studentId => $scoreValue) {
                $studentId = (int) $studentId;
                if (!isset($allowedIds[$studentId]) || $scoreValue === '' || !is_numeric($scoreValue)) {
                    continue;
                }
                $score = (float) $scoreValue;
                if ($score < 0 || $score > (float) $evaluation['ponderation_max']) {
                    throw new \InvalidArgumentException('Chaque note doit être comprise entre 0 et le maximum de l’évaluation.');
                }
                $stmt->execute([
                    ':evaluation_id' => $evaluationId,
                    ':eleve_id' => $studentId,
                    ':score' => $score,
                    ':updated_score' => $score,
                ]);
                $saved++;
            }
            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }

        return $saved;
    }
}
