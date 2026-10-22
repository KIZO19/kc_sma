<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PresenceAgent
{
    public static function getTeachersForDate(int $schoolId, ?int $sectionId, string $date): array
    {
        if ($schoolId <= 0) {
            return [];
        }

        SchoolTimetable::ensureTables();
        $params = [':ecole_id' => $schoolId, ':date_presence' => $date];
        $sectionFilter = '';
        if ($sectionId !== null) {
            $sectionFilter = ' AND c.section_id = :section_id';
            $params[':section_id'] = $sectionId;
        }

        $stmt = Database::getConnection()->prepare(
                        'SELECT DISTINCT ag.id, ag.nom, ag.postnom, ag.prenom,
                                        p.statut AS statut_presence, p.justification,
                                        j.jour_id AS jour_hebdomadaire, js.nom_jour AS jour_hebdomadaire_nom
             FROM agents ag
             INNER JOIN roles_administration ra ON ra.id = ag.role_id
                         INNER JOIN annees_scolaires an ON an.ecole_id = ag.ecole_id AND an.est_active = 1
             LEFT JOIN presences_agents p ON p.agent_id = ag.id AND p.date_presence = :date_presence
                         LEFT JOIN jours_libres_enseignants j
                             ON j.ecole_id = ag.ecole_id AND j.annee_scolaire_id = an.id AND j.agent_id = ag.id
                         LEFT JOIN jours_semaine js ON js.id = j.jour_id
             WHERE ag.ecole_id = :ecole_id
               AND ra.titre_role IN (\'Enseignant Titulaire\', \'Enseignant\')
               AND EXISTS (
                   SELECT 1
                   FROM attribution_cours ac
                   INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
                                     WHERE ac.agent_id = ag.id AND ac.ecole_id = ag.ecole_id
                                         AND ac.annee_scolaire_id = an.id' . $sectionFilter . '
               )
             ORDER BY ag.nom ASC, ag.postnom ASC, ag.prenom ASC'
        );
        $stmt->execute($params);

                $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $selectedDay = (int) (new \DateTimeImmutable($date))->format('N');
                foreach ($teachers as &$teacher) {
                        $teacher['est_jour_hebdomadaire'] = (int) ($teacher['jour_hebdomadaire'] ?? 0) === $selectedDay;
                }
                unset($teacher);

                return $teachers;
    }

    public static function getSummary(int $schoolId, ?int $sectionId, string $date): array
    {
        $teachers = self::getTeachersForDate($schoolId, $sectionId, $date);
        $summary = ['total' => count($teachers), 'present' => 0, 'absent' => 0, 'recorded' => 0, 'pending' => 0, 'weekly_off' => 0];

        foreach ($teachers as $teacher) {
            if (!empty($teacher['est_jour_hebdomadaire'])) {
                $summary['weekly_off']++;
            }
            $status = (string) ($teacher['statut_presence'] ?? '');
            if ($status === '') {
                if (empty($teacher['est_jour_hebdomadaire'])) {
                    $summary['pending']++;
                }
                continue;
            }

            $summary['recorded']++;
            if ($status === 'Présent') {
                $summary['present']++;
            } elseif ($status === 'Absent') {
                $summary['absent']++;
            }
        }

        return $summary;
    }

    public static function saveForDate(int $schoolId, ?int $sectionId, string $date, array $statuses, array $justifications): int
    {
        $teachers = self::getTeachersForDate($schoolId, $sectionId, $date);
        $allowedTeachers = array_fill_keys(array_map(static fn (array $teacher): int => (int) $teacher['id'], $teachers), true);
        $allowedStatuses = ['Présent', 'Absent', 'Journée Pédagogique', 'Congé'];
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO presences_agents (agent_id, date_presence, statut, justification)
             VALUES (:agent_id, :date_presence, :statut, :justification)
             ON DUPLICATE KEY UPDATE statut = :updated_statut, justification = :updated_justification'
        );
        $saved = 0;

        $db->beginTransaction();
        try {
            foreach ($statuses as $agentId => $status) {
                $agentId = (int) $agentId;
                $status = (string) $status;
                if (!isset($allowedTeachers[$agentId]) || !in_array($status, $allowedStatuses, true)) {
                    continue;
                }

                $justification = trim((string) ($justifications[$agentId] ?? ''));
                $stmt->execute([
                    ':agent_id' => $agentId,
                    ':date_presence' => $date,
                    ':statut' => $status,
                    ':justification' => $justification !== '' ? $justification : null,
                    ':updated_statut' => $status,
                    ':updated_justification' => $justification !== '' ? $justification : null,
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