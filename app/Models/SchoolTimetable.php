<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SchoolTimetable
{
    private static bool $tablesEnsured = false;

    public static function ensureTables(): void
    {
        if (self::$tablesEnsured) {
            return;
        }

        $db = Database::getConnection();
        $db->exec(
            'CREATE TABLE IF NOT EXISTS indices_enseignants (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                annee_scolaire_id INT NOT NULL,
                agent_id INT NOT NULL,
                indice INT NOT NULL,
                UNIQUE KEY uq_teacher_index_agent (ecole_id, annee_scolaire_id, agent_id),
                UNIQUE KEY uq_teacher_index_number (ecole_id, annee_scolaire_id, indice)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS jours_libres_enseignants (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                annee_scolaire_id INT NOT NULL,
                agent_id INT NOT NULL,
                jour_id TINYINT NOT NULL,
                UNIQUE KEY uq_teacher_weekly_day_off (ecole_id, annee_scolaire_id, agent_id),
                KEY idx_teacher_day_off (ecole_id, annee_scolaire_id, jour_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS horaires_cours (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                annee_scolaire_id INT NOT NULL,
                attribution_cours_id INT NOT NULL,
                classe_id INT NOT NULL,
                cours_id INT NOT NULL,
                agent_id INT NOT NULL,
                jour_id TINYINT NOT NULL,
                periode TINYINT NOT NULL,
                UNIQUE KEY uq_timetable_class_period (ecole_id, annee_scolaire_id, classe_id, jour_id, periode),
                UNIQUE KEY uq_timetable_teacher_period (ecole_id, annee_scolaire_id, agent_id, jour_id, periode),
                KEY idx_timetable_class (ecole_id, annee_scolaire_id, classe_id, jour_id),
                KEY idx_timetable_teacher (ecole_id, annee_scolaire_id, agent_id, jour_id),
                KEY idx_timetable_assignment (attribution_cours_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tablesEnsured = true;
    }

    public static function getAssignments(int $schoolId, int $yearId, int $sectionId, ?int $classId = null): array
    {
        if ($schoolId <= 0 || $yearId <= 0 || $sectionId <= 0) {
            return [];
        }

        self::ensureTables();
        $sql =
            'SELECT ac.id, ac.classe_id, ac.cours_id, ac.agent_id, ac.volume_horaire_hebdo,
                    c.nom_classe, co.nom_cours, a.nom, a.postnom, a.prenom, r.titre_role,
                    ti.indice
             FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             INNER JOIN cours co ON co.id = ac.cours_id AND co.ecole_id = ac.ecole_id
             INNER JOIN agents a ON a.id = ac.agent_id AND a.ecole_id = ac.ecole_id
             INNER JOIN roles_administration r ON r.id = a.role_id
             LEFT JOIN indices_enseignants ti
               ON ti.ecole_id = ac.ecole_id AND ti.annee_scolaire_id = ac.annee_scolaire_id
              AND ti.agent_id = ac.agent_id
             WHERE ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
               AND c.section_id = :section_id';
        $params = [
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ];
        if ($classId !== null) {
            $sql .= ' AND c.id = :classe_id';
            $params[':classe_id'] = $classId;
        }
        $sql .= ' ORDER BY c.nom_classe, co.nom_cours, a.nom, a.postnom';
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($assignments as &$assignment) {
            $assignment['indice'] = self::ensureTeacherIndex(
                $schoolId,
                $yearId,
                (int) $assignment['agent_id']
            );
        }
        unset($assignment);

        return $assignments;
    }

    public static function getAssignmentResources(int $schoolId, int $sectionId): array
    {
        $db = Database::getConnection();
        $classes = $db->prepare(
            'SELECT id, nom_classe FROM classes
             WHERE ecole_id = :ecole_id AND section_id = :section_id
             ORDER BY nom_classe'
        );
        $classes->execute([':ecole_id' => $schoolId, ':section_id' => $sectionId]);

        $courses = $db->prepare(
            'SELECT id, nom_cours, code_cours FROM cours
             WHERE ecole_id = :ecole_id ORDER BY nom_cours'
        );
        $courses->execute([':ecole_id' => $schoolId]);

        $teachers = $db->prepare(
            "SELECT ag.id, ag.nom, ag.postnom, ag.prenom, r.titre_role
             FROM agents ag
             INNER JOIN roles_administration r ON r.id = ag.role_id
             WHERE ag.ecole_id = :ecole_id AND ag.est_enseignant_secondaire = 1
               AND LOWER(r.titre_role) LIKE '%enseignant%'
             ORDER BY ag.nom, ag.postnom, ag.prenom"
        );
        $teachers->execute([':ecole_id' => $schoolId]);

        return [
            'classes' => $classes->fetchAll(PDO::FETCH_ASSOC),
            'courses' => $courses->fetchAll(PDO::FETCH_ASSOC),
            'teachers' => $teachers->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public static function saveAssignment(
        int $schoolId,
        int $yearId,
        int $sectionId,
        int $classId,
        int $courseId,
        int $teacherId,
        int $weeklyPeriods,
        ?int $assignmentId = null
    ): void {
        if (
            $schoolId <= 0 || $yearId <= 0 || $sectionId <= 0 || $classId <= 0
            || $courseId <= 0 || $teacherId <= 0 || $weeklyPeriods <= 0 || $weeklyPeriods > 35
        ) {
            throw new \InvalidArgumentException('Les informations sont invalides. Une affectation ne peut pas dépasser 35 périodes hebdomadaires.');
        }

        self::ensureTables();
        $db = Database::getConnection();
        $check = $db->prepare(
            "SELECT c.id
             FROM classes c
             INNER JOIN cours co ON co.ecole_id = c.ecole_id AND co.id = :cours_id
             INNER JOIN agents ag ON ag.ecole_id = c.ecole_id AND ag.id = :agent_id
             INNER JOIN roles_administration r ON r.id = ag.role_id
             INNER JOIN annees_scolaires an ON an.ecole_id = c.ecole_id
                AND an.id = :annee_scolaire_id AND an.est_active = 1
             WHERE c.id = :classe_id AND c.ecole_id = :ecole_id
               AND c.section_id = :section_id AND ag.est_enseignant_secondaire = 1
               AND LOWER(r.titre_role) LIKE '%enseignant%'
             LIMIT 1"
        );
        $check->execute([
            ':cours_id' => $courseId,
            ':agent_id' => $teacherId,
            ':annee_scolaire_id' => $yearId,
            ':classe_id' => $classId,
            ':ecole_id' => $schoolId,
            ':section_id' => $sectionId,
        ]);
        if (!$check->fetchColumn()) {
            throw new \InvalidArgumentException('La classe, le cours ou l’enseignant ne relève pas de votre section secondaire.');
        }

        $duplicate = $db->prepare(
            'SELECT id FROM attribution_cours
             WHERE ecole_id = :ecole_id AND classe_id = :classe_id
               AND cours_id = :cours_id AND annee_scolaire_id = :annee_scolaire_id
               AND (id <> :excluded_id OR :exclude_current = 0) LIMIT 1'
        );
        $duplicate->execute([
            ':ecole_id' => $schoolId,
            ':classe_id' => $classId,
            ':cours_id' => $courseId,
            ':annee_scolaire_id' => $yearId,
            ':excluded_id' => $assignmentId ?? 0,
            ':exclude_current' => $assignmentId === null ? 0 : 1,
        ]);
        if ($duplicate->fetchColumn()) {
            throw new \InvalidArgumentException('Ce cours est déjà affecté à cette classe pour l’année scolaire active.');
        }

        $previousTeacherId = null;
        $previousVolume = 0;
        if ($assignmentId !== null) {
            $current = $db->prepare(
                'SELECT ac.id, ac.agent_id FROM attribution_cours ac
                 INNER JOIN classes c ON c.id = ac.classe_id
                 WHERE ac.id = :id AND ac.ecole_id = :ecole_id
                   AND ac.annee_scolaire_id = :annee_scolaire_id AND c.section_id = :section_id'
            );
            $current->execute([
                ':id' => $assignmentId,
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':section_id' => $sectionId,
            ]);
            $currentAssignment = $current->fetch(PDO::FETCH_ASSOC);
            if (!$currentAssignment) {
                throw new \InvalidArgumentException('Cette affectation est hors de votre périmètre.');
            }
            $previousTeacherId = (int) $currentAssignment['agent_id'];
            $oldVolume = $db->prepare('SELECT volume_horaire_hebdo FROM attribution_cours WHERE id = :id');
            $oldVolume->execute([':id' => $assignmentId]);
            $previousVolume = (int) $oldVolume->fetchColumn();

            $scheduled = $db->prepare(
                'SELECT COUNT(*) FROM horaires_cours WHERE attribution_cours_id = :id'
            );
            $scheduled->execute([':id' => $assignmentId]);
            if ((int) $scheduled->fetchColumn() > $weeklyPeriods) {
                throw new \InvalidArgumentException('Le volume hebdomadaire ne peut être inférieur aux périodes déjà planifiées.');
            }
            $conflict = $db->prepare(
                'SELECT 1 FROM horaires_cours current_slot
                 WHERE current_slot.attribution_cours_id = :assignment_id
                   AND EXISTS (
                     SELECT 1 FROM horaires_cours other_slot
                     WHERE other_slot.ecole_id = current_slot.ecole_id
                       AND other_slot.annee_scolaire_id = current_slot.annee_scolaire_id
                       AND other_slot.jour_id = current_slot.jour_id
                       AND other_slot.periode = current_slot.periode
                       AND other_slot.id <> current_slot.id
                       AND (other_slot.classe_id = :classe_id OR other_slot.agent_id = :agent_id)
                   )
                 LIMIT 1'
            );
            $conflict->execute([
                ':assignment_id' => $assignmentId,
                ':classe_id' => $classId,
                ':agent_id' => $teacherId,
            ]);
            if ($conflict->fetchColumn()) {
                throw new \InvalidArgumentException('Le changement d’enseignant ou de classe crée un conflit avec un créneau déjà planifié.');
            }
        }

        $targetWorkload = self::getWeeklyWorkload($schoolId, $yearId, $teacherId);
        if ($previousTeacherId === $teacherId) {
            $targetWorkload -= $previousVolume;
        }
        if ($targetWorkload + $weeklyPeriods > 35) {
            throw new \InvalidArgumentException('La charge totale de cet enseignant dépasserait 35 périodes, le maximum planifiable avec une journée libre.');
        }

        if ($assignmentId !== null) {
            $update = $db->prepare(
                'UPDATE attribution_cours
                 SET classe_id = :classe_id, cours_id = :cours_id, agent_id = :agent_id,
                     volume_horaire_hebdo = :volume
                 WHERE id = :id AND ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id'
            );
            $update->execute([
                ':classe_id' => $classId,
                ':cours_id' => $courseId,
                ':agent_id' => $teacherId,
                ':volume' => $weeklyPeriods,
                ':id' => $assignmentId,
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
            ]);
            $sync = $db->prepare('UPDATE horaires_cours SET classe_id = :classe_id, cours_id = :cours_id, agent_id = :agent_id WHERE attribution_cours_id = :id');
            $sync->execute([
                ':classe_id' => $classId,
                ':cours_id' => $courseId,
                ':agent_id' => $teacherId,
                ':id' => $assignmentId,
            ]);
        } else {
            $insert = $db->prepare(
                'INSERT INTO attribution_cours
                    (ecole_id, classe_id, cours_id, agent_id, annee_scolaire_id, volume_horaire_hebdo)
                 VALUES (:ecole_id, :classe_id, :cours_id, :agent_id, :annee_scolaire_id, :volume)'
            );
            $insert->execute([
                ':ecole_id' => $schoolId,
                ':classe_id' => $classId,
                ':cours_id' => $courseId,
                ':agent_id' => $teacherId,
                ':annee_scolaire_id' => $yearId,
                ':volume' => $weeklyPeriods,
            ]);
        }

        if ($previousTeacherId !== null && $previousTeacherId !== $teacherId) {
            self::clearDayOffIfNotRequired($schoolId, $yearId, $previousTeacherId);
        }
        self::clearDayOffIfNotRequired($schoolId, $yearId, $teacherId);
        self::ensureTeacherIndex($schoolId, $yearId, $teacherId);
    }

    public static function getScheduleForClass(int $schoolId, int $yearId, int $classId): array
    {
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT h.id, h.jour_id, h.periode, co.nom_cours, c.nom_classe,
                    ti.indice, a.nom, a.postnom, a.prenom
             FROM horaires_cours h
             INNER JOIN attribution_cours ac ON ac.id = h.attribution_cours_id
             INNER JOIN classes c ON c.id = h.classe_id
             INNER JOIN cours co ON co.id = h.cours_id
             INNER JOIN agents a ON a.id = h.agent_id
             INNER JOIN indices_enseignants ti
               ON ti.ecole_id = h.ecole_id AND ti.annee_scolaire_id = h.annee_scolaire_id
              AND ti.agent_id = h.agent_id
             WHERE h.ecole_id = :ecole_id AND h.annee_scolaire_id = :annee_scolaire_id
               AND h.classe_id = :classe_id
             ORDER BY h.jour_id, h.periode'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':classe_id' => $classId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getScheduleForTeacher(int $schoolId, int $yearId, int $agentId): array
    {
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT h.jour_id, h.periode, co.nom_cours, c.nom_classe, ti.indice
             FROM horaires_cours h
             INNER JOIN classes c ON c.id = h.classe_id
             INNER JOIN cours co ON co.id = h.cours_id
             INNER JOIN indices_enseignants ti
               ON ti.ecole_id = h.ecole_id AND ti.annee_scolaire_id = h.annee_scolaire_id
              AND ti.agent_id = h.agent_id
             WHERE h.ecole_id = :ecole_id AND h.annee_scolaire_id = :annee_scolaire_id
               AND h.agent_id = :agent_id
             ORDER BY h.jour_id, h.periode'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTeacherDayOff(int $schoolId, int $yearId, int $agentId): ?int
    {
        return self::getDayOff($schoolId, $yearId, $agentId);
    }

    public static function getStudentClass(int $schoolId, int $studentId): ?array
    {
        if ($schoolId <= 0 || $studentId <= 0) {
            return null;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT c.id AS classe_id, c.nom_classe, an.id AS annee_scolaire_id, an.annee
             FROM inscriptions i
             INNER JOIN annees_scolaires an ON an.id = i.annee_scolaire_id
               AND an.ecole_id = :ecole_id AND an.est_active = 1
             INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = an.ecole_id
             WHERE i.eleve_id = :eleve_id
             ORDER BY i.id DESC LIMIT 1'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':eleve_id' => $studentId]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        return $class ?: null;
    }

    public static function saveSlot(int $schoolId, int $yearId, int $sectionId, int $assignmentId, int $dayId, int $period): void
    {
        if ($dayId < 1 || $dayId > 6 || $period < 1 || $period > 7) {
            throw new \InvalidArgumentException('Le jour ou la période sélectionnée est invalide.');
        }

        self::ensureTables();
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ac.id, ac.classe_id, ac.cours_id, ac.agent_id, ac.volume_horaire_hebdo
             FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             INNER JOIN annees_scolaires an ON an.id = ac.annee_scolaire_id
                AND an.ecole_id = ac.ecole_id AND an.est_active = 1
             WHERE ac.id = :id AND ac.ecole_id = :ecole_id
               AND ac.annee_scolaire_id = :annee_scolaire_id AND c.section_id = :section_id'
        );
        $stmt->execute([
            ':id' => $assignmentId,
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$assignment) {
            throw new \InvalidArgumentException('Cette affectation est introuvable ou hors de votre section.');
        }

        $dayOff = self::getDayOff($schoolId, $yearId, (int) $assignment['agent_id']);
        if ($dayOff !== null && $dayOff === $dayId) {
            throw new \InvalidArgumentException('Cet enseignant ne peut pas être planifié pendant sa journée hebdomadaire.');
        }
        $workload = self::getWeeklyWorkload($schoolId, $yearId, (int) $assignment['agent_id']);
        if ($workload > 22 && $dayOff === null) {
            throw new \InvalidArgumentException('Cet enseignant dépasse 22 périodes par semaine. Configurez d’abord sa journée hebdomadaire.');
        }

        $existing = $db->prepare(
            'SELECT id, attribution_cours_id FROM horaires_cours
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id
               AND classe_id = :classe_id AND jour_id = :jour_id AND periode = :periode'
        );
        $existing->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':classe_id' => (int) $assignment['classe_id'],
            ':jour_id' => $dayId,
            ':periode' => $period,
        ]);
        $existingSlot = $existing->fetch(PDO::FETCH_ASSOC);
        $alreadySameAssignment = $existingSlot
            && (int) $existingSlot['attribution_cours_id'] === $assignmentId;
        $scheduledCount = $db->prepare(
            'SELECT COUNT(*) FROM horaires_cours
             WHERE attribution_cours_id = :assignment_id'
        );
        $scheduledCount->execute([':assignment_id' => $assignmentId]);
        if (!$alreadySameAssignment && (int) $scheduledCount->fetchColumn() >= (int) $assignment['volume_horaire_hebdo']) {
            throw new \InvalidArgumentException('Le volume hebdomadaire de cette affectation est déjà entièrement planifié.');
        }

        $teacherConflict = $db->prepare(
            'SELECT 1 FROM horaires_cours
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id
               AND agent_id = :agent_id AND jour_id = :jour_id AND periode = :periode
               AND classe_id <> :classe_id LIMIT 1'
        );
        $teacherConflict->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':agent_id' => (int) $assignment['agent_id'],
            ':jour_id' => $dayId,
            ':periode' => $period,
            ':classe_id' => (int) $assignment['classe_id'],
        ]);
        if ($teacherConflict->fetchColumn()) {
            throw new \InvalidArgumentException('Cet enseignant est déjà planifié à cette période dans une autre classe.');
        }

        $save = $db->prepare(
            'INSERT INTO horaires_cours
                (ecole_id, annee_scolaire_id, attribution_cours_id, classe_id, cours_id, agent_id, jour_id, periode)
             VALUES (:ecole_id, :annee_scolaire_id, :attribution_id, :classe_id, :cours_id, :agent_id, :jour_id, :periode)
             ON DUPLICATE KEY UPDATE
                attribution_cours_id = VALUES(attribution_cours_id),
                cours_id = VALUES(cours_id), agent_id = VALUES(agent_id)'
        );
        try {
            $save->execute([
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':attribution_id' => $assignmentId,
                ':classe_id' => (int) $assignment['classe_id'],
                ':cours_id' => (int) $assignment['cours_id'],
                ':agent_id' => (int) $assignment['agent_id'],
                ':jour_id' => $dayId,
                ':periode' => $period,
            ]);
        } catch (\PDOException $exception) {
            throw new \InvalidArgumentException('La classe ou l’enseignant est déjà occupé à cette période.');
        }

        self::ensureTeacherIndex($schoolId, $yearId, (int) $assignment['agent_id']);
    }

    public static function deleteSlot(int $schoolId, int $yearId, int $sectionId, int $slotId): bool
    {
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'DELETE h FROM horaires_cours h
             INNER JOIN classes c ON c.id = h.classe_id
             WHERE h.id = :id AND h.ecole_id = :ecole_id
               AND h.annee_scolaire_id = :annee_scolaire_id AND c.section_id = :section_id'
        );
        $stmt->execute([
            ':id' => $slotId,
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function getTeacherWorkloads(int $schoolId, int $yearId, int $sectionId): array
    {
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            "SELECT DISTINCT ac.agent_id, a.nom, a.postnom, a.prenom,
                    (SELECT SUM(all_ac.volume_horaire_hebdo)
                     FROM attribution_cours all_ac
                     WHERE all_ac.ecole_id = ac.ecole_id
                       AND all_ac.annee_scolaire_id = ac.annee_scolaire_id
                       AND all_ac.agent_id = ac.agent_id) AS volume_total, ti.indice,
                    j.jour_id AS jour_libre
             FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             INNER JOIN agents a ON a.id = ac.agent_id
             LEFT JOIN indices_enseignants ti
               ON ti.ecole_id = ac.ecole_id AND ti.annee_scolaire_id = ac.annee_scolaire_id
              AND ti.agent_id = ac.agent_id
             LEFT JOIN jours_libres_enseignants j
               ON j.ecole_id = ac.ecole_id AND j.annee_scolaire_id = ac.annee_scolaire_id
              AND j.agent_id = ac.agent_id
             WHERE ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
               AND c.section_id = :section_id
             ORDER BY volume_total DESC, a.nom, a.postnom"
        );
        $stmt->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':section_id' => $sectionId]);
        $workloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($workloads as &$workload) {
            $workload['indice'] = self::ensureTeacherIndex($schoolId, $yearId, (int) $workload['agent_id']);
        }
        unset($workload);

        return $workloads;
    }

    public static function setWeeklyDayOff(int $schoolId, int $yearId, int $sectionId, int $agentId, ?int $dayId): void
    {
        self::ensureTables();
        if ($dayId !== null && ($dayId < 1 || $dayId > 6)) {
            throw new \InvalidArgumentException('Jour libre invalide.');
        }
        if ($dayId === null) {
            if (self::getWeeklyWorkload($schoolId, $yearId, $agentId) > 22) {
                throw new \InvalidArgumentException('Cet enseignant a droit à une journée libre, car sa charge dépasse 22 périodes.');
            }
            $delete = Database::getConnection()->prepare(
                'DELETE FROM jours_libres_enseignants
                 WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id AND agent_id = :agent_id'
            );
            $delete->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);
            return;
        }
        if (self::getWeeklyWorkload($schoolId, $yearId, $agentId) <= 22) {
            throw new \InvalidArgumentException('La journée hebdomadaire est réservée aux enseignants dépassant 22 périodes.');
        }

        $teacher = Database::getConnection()->prepare(
            "SELECT 1 FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id
             WHERE ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
               AND ac.agent_id = :agent_id AND c.section_id = :section_id LIMIT 1"
        );
        $teacher->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':agent_id' => $agentId,
            ':section_id' => $sectionId,
        ]);
        if (!$teacher->fetchColumn()) {
            throw new \InvalidArgumentException('Cet enseignant est hors de votre section.');
        }

        $scheduled = Database::getConnection()->prepare(
            'SELECT 1 FROM horaires_cours
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id
               AND agent_id = :agent_id AND jour_id = :jour_id LIMIT 1'
        );
        $scheduled->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':agent_id' => $agentId,
            ':jour_id' => $dayId,
        ]);
        if ($scheduled->fetchColumn()) {
            throw new \InvalidArgumentException('Retirez ou déplacez d’abord les cours prévus ce jour-là.');
        }

        $save = Database::getConnection()->prepare(
            'INSERT INTO jours_libres_enseignants
                (ecole_id, annee_scolaire_id, agent_id, jour_id)
             VALUES (:ecole_id, :annee_scolaire_id, :agent_id, :jour_id)
             ON DUPLICATE KEY UPDATE jour_id = VALUES(jour_id)'
        );
        $save->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':agent_id' => $agentId,
            ':jour_id' => $dayId,
        ]);
        self::ensureTeacherIndex($schoolId, $yearId, $agentId);
    }

    private static function getDayOff(int $schoolId, int $yearId, int $agentId): ?int
    {
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT jour_id FROM jours_libres_enseignants
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id AND agent_id = :agent_id'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);
        $day = $stmt->fetchColumn();

        return $day === false ? null : (int) $day;
    }

    private static function getWeeklyWorkload(int $schoolId, int $yearId, int $agentId): int
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COALESCE(SUM(volume_horaire_hebdo), 0)
             FROM attribution_cours
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id AND agent_id = :agent_id'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);

        return (int) $stmt->fetchColumn();
    }

    private static function clearDayOffIfNotRequired(int $schoolId, int $yearId, int $agentId): void
    {
        if (self::getWeeklyWorkload($schoolId, $yearId, $agentId) > 22) {
            return;
        }

        $stmt = Database::getConnection()->prepare(
            'DELETE FROM jours_libres_enseignants
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id AND agent_id = :agent_id'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);
    }

    private static function ensureTeacherIndex(int $schoolId, int $yearId, int $agentId): int
    {
        self::ensureTables();
        $db = Database::getConnection();
        $find = $db->prepare(
            'SELECT indice FROM indices_enseignants
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id AND agent_id = :agent_id'
        );
        $find->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);
        $existing = $find->fetchColumn();
        if ($existing !== false) {
            return (int) $existing;
        }

        $next = $db->prepare(
            'SELECT COALESCE(MAX(indice), 0) + 1 FROM indices_enseignants
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id'
        );
        $next->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId]);
        $index = (int) $next->fetchColumn();
        $insert = $db->prepare(
            'INSERT IGNORE INTO indices_enseignants
                (ecole_id, annee_scolaire_id, agent_id, indice)
             VALUES (:ecole_id, :annee_scolaire_id, :agent_id, :indice)'
        );
        $insert->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':agent_id' => $agentId,
            ':indice' => $index,
        ]);
        $find->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);

        return (int) $find->fetchColumn();
    }
}
