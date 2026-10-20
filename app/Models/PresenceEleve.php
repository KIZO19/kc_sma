<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PresenceEleve
{
    public static function getAvailableClasses(?int $schoolId, ?int $teacherAgentId = null): array
    {
        if ($teacherAgentId !== null && $teacherAgentId <= 0) {
            return [];
        }

        $sql = 'SELECT DISTINCT c.id, c.ecole_id, c.nom_classe, e.nom_etablissement
                FROM classes c
                INNER JOIN ecoles e ON e.id = c.ecole_id
                WHERE EXISTS (
                    SELECT 1 FROM annees_scolaires a
                    WHERE a.ecole_id = c.ecole_id AND a.est_active = 1
                )';
        $params = [];

        if ($schoolId !== null) {
            $sql .= ' AND c.ecole_id = :ecole_id';
            $params[':ecole_id'] = $schoolId;
        }

        if ($teacherAgentId !== null) {
            $sql .= ' AND EXISTS (
                SELECT 1
                FROM attribution_cours ac
                INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id AND a.est_active = 1
                INNER JOIN agents ag ON ag.id = ac.agent_id AND ag.ecole_id = ac.ecole_id
                WHERE ac.classe_id = c.id
                  AND ac.ecole_id = c.ecole_id
                  AND ac.agent_id = :agent_id
            )';
            $params[':agent_id'] = $teacherAgentId;
        }

        $sql .= ' ORDER BY e.nom_etablissement ASC, c.nom_classe ASC';
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStudentsForClass(int $schoolId, int $classId, string $date): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT e.id, e.matricule, e.nom, e.postnom, e.prenom, p.statut AS statut_presence
             FROM inscriptions i
             INNER JOIN eleves e ON e.id = i.eleve_id
             INNER JOIN classes c ON c.id = i.classe_id
             INNER JOIN annees_scolaires a ON a.id = i.annee_scolaire_id
                 AND a.ecole_id = c.ecole_id AND a.est_active = 1
             LEFT JOIN presences_eleves p ON p.eleve_id = e.id AND p.date_jour = :date_jour
             WHERE c.id = :classe_id AND c.ecole_id = :ecole_id
             ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC'
        );
        $stmt->execute([
            ':date_jour' => $date,
            ':classe_id' => $classId,
            ':ecole_id' => $schoolId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function saveForDate(array $studentIds, string $date, array $statuses): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO presences_eleves (eleve_id, date_jour, statut)
             VALUES (:eleve_id, :date_jour, :statut)
             ON DUPLICATE KEY UPDATE statut = :updated_statut'
        );
        $allowedStatuses = ['Présent', 'Absent Justifié', 'Absent Non Justifié', 'Retard'];
        $saved = 0;

        $db->beginTransaction();
        try {
            foreach ($studentIds as $studentId) {
                $studentId = (int) $studentId;
                $status = (string) ($statuses[$studentId] ?? '');
                if (!in_array($status, $allowedStatuses, true)) {
                    continue;
                }

                $stmt->execute([
                    ':eleve_id' => $studentId,
                    ':date_jour' => $date,
                    ':statut' => $status,
                    ':updated_statut' => $status,
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
