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
            'CREATE TABLE IF NOT EXISTS parametres_horaires (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                section_id INT NOT NULL,
                duree_heure_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 50,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_timetable_duration_section (ecole_id, section_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
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
                KEY idx_timetable_class (ecole_id, annee_scolaire_id, classe_id, jour_id),
                KEY idx_timetable_teacher_period (ecole_id, annee_scolaire_id, agent_id, jour_id),
                KEY idx_timetable_assignment (attribution_cours_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS programmes_cours (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                annee_scolaire_id INT NOT NULL,
                section_id INT NOT NULL,
                cours_id INT NOT NULL,
                nature_cours ENUM(\'commun\', \'option\') NOT NULL,
                volume_horaire_hebdo TINYINT UNSIGNED NOT NULL,
                agent_id INT NULL,
                UNIQUE KEY uq_course_program_section (ecole_id, annee_scolaire_id, section_id, cours_id),
                KEY idx_course_program_teacher (ecole_id, annee_scolaire_id, agent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS programmes_cours_classes (
                programme_id INT NOT NULL,
                classe_id INT NOT NULL,
                PRIMARY KEY (programme_id, classe_id),
                KEY idx_course_program_class (classe_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $teacherPeriodUnique = $db->query(
            "SHOW INDEX FROM horaires_cours WHERE Key_name = 'uq_timetable_teacher_period'"
        )->fetch();
        if ($teacherPeriodUnique) {
            $db->exec('ALTER TABLE horaires_cours DROP INDEX uq_timetable_teacher_period');
        }
        $teacherPeriodIndex = $db->query(
            "SHOW INDEX FROM horaires_cours WHERE Key_name = 'idx_timetable_teacher_period'"
        )->fetch();
        if (!$teacherPeriodIndex) {
            $db->exec(
                'ALTER TABLE horaires_cours ADD INDEX idx_timetable_teacher_period
                 (ecole_id, annee_scolaire_id, agent_id, jour_id)'
            );
        }
        self::$tablesEnsured = true;
    }

    public static function getHourDuration(int $schoolId, int $sectionId): int
    {
        if ($schoolId <= 0 || $sectionId <= 0) {
            return 50;
        }

        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT duree_heure_minutes FROM parametres_horaires
             WHERE ecole_id = :ecole_id AND section_id = :section_id LIMIT 1'
        );
        $stmt->execute([':ecole_id' => $schoolId, ':section_id' => $sectionId]);
        $duration = $stmt->fetchColumn();

        return $duration === false ? 50 : (int) $duration;
    }

    public static function setHourDuration(int $schoolId, int $sectionId, int $durationMinutes): void
    {
        if ($schoolId <= 0 || $sectionId <= 0 || $durationMinutes < 20 || $durationMinutes > 120) {
            throw new \InvalidArgumentException('La durée d’une heure doit être comprise entre 20 et 120 minutes.');
        }

        self::ensureTables();
        $slots = Database::getConnection()->prepare(
            'SELECT h.agent_id, h.jour_id, h.periode, c.section_id
             FROM horaires_cours h
             INNER JOIN classes c ON c.id = h.classe_id AND c.ecole_id = h.ecole_id
             INNER JOIN annees_scolaires an ON an.id = h.annee_scolaire_id
               AND an.ecole_id = h.ecole_id AND an.est_active = 1
             WHERE h.ecole_id = :ecole_id'
        );
        $slots->execute([':ecole_id' => $schoolId]);
        $slotsByTeacherDay = [];
        foreach ($slots->fetchAll(PDO::FETCH_ASSOC) as $slot) {
            $slotsByTeacherDay[(int) $slot['agent_id']][(int) $slot['jour_id']][] = $slot;
        }
        foreach ($slotsByTeacherDay as $teacherDays) {
            foreach ($teacherDays as $teacherSlots) {
                foreach ($teacherSlots as $index => $slot) {
                    $section = (int) $slot['section_id'];
                    $duration = $section === $sectionId
                        ? $durationMinutes
                        : self::getHourDuration($schoolId, $section);
                    $time = self::getDailySchedule($duration)['hours'][(int) $slot['periode']];
                    for ($otherIndex = $index + 1; $otherIndex < count($teacherSlots); $otherIndex++) {
                        $otherSlot = $teacherSlots[$otherIndex];
                        $otherSection = (int) $otherSlot['section_id'];
                        $otherDuration = $otherSection === $sectionId
                            ? $durationMinutes
                            : self::getHourDuration($schoolId, $otherSection);
                        $otherTime = self::getDailySchedule($otherDuration)['hours'][(int) $otherSlot['periode']];
                        if ((int) $time['start'] < (int) $otherTime['end']
                            && (int) $time['end'] > (int) $otherTime['start']) {
                            throw new \InvalidArgumentException(
                                'Cette durée créerait un chevauchement d’horaires pour un enseignant affecté à plusieurs sections.'
                            );
                        }
                    }
                }
            }
        }

        $stmt = Database::getConnection()->prepare(
            'INSERT INTO parametres_horaires (ecole_id, section_id, duree_heure_minutes)
             VALUES (:ecole_id, :section_id, :duree)
             ON DUPLICATE KEY UPDATE duree_heure_minutes = VALUES(duree_heure_minutes)'
        );
        $stmt->execute([
            ':ecole_id' => $schoolId,
            ':section_id' => $sectionId,
            ':duree' => $durationMinutes,
        ]);
    }

    public static function getDailyHours(): int
    {
        return 7;
    }

    public static function getDailySchedule(int $durationMinutes): array
    {
        if ($durationMinutes < 1) {
            throw new \InvalidArgumentException('La durée d’une heure doit être positive.');
        }

        $currentMinute = 7 * 60 + 30;
        $recessAfterHour = null;
        $hours = [];
        $timeline = [];
        for ($hour = 1; $hour <= self::getDailyHours(); $hour++) {
            $startMinute = $currentMinute;
            $endMinute = $startMinute + $durationMinutes;
            $hours[$hour] = ['start' => $startMinute, 'end' => $endMinute];
            $timeline[] = [
                'type' => 'hour',
                'hour' => $hour,
                'start' => $startMinute,
                'end' => $endMinute,
            ];
            $currentMinute = $endMinute;
            if ($recessAfterHour === null && $currentMinute >= 10 * 60) {
                $recessAfterHour = $hour;
                $timeline[] = [
                    'type' => 'recess',
                    'start' => $currentMinute,
                    'end' => $currentMinute + 15,
                ];
                $currentMinute += 15;
            }
        }

        return [
            'hours' => $hours,
            'timeline' => $timeline,
            'recess_after_hour' => $recessAfterHour,
            'recess_start' => $recessAfterHour === null ? null : $hours[$recessAfterHour]['end'],
            'recess_end' => $recessAfterHour === null ? null : $hours[$recessAfterHour]['end'] + 15,
        ];
    }

    public static function isSecondarySection(int $sectionId): bool
    {
        $section = Section::findById($sectionId);

        return mb_strtolower(trim((string) ($section['nom_section'] ?? '')), 'UTF-8') === 'secondaire';
    }

    public static function getAssignments(int $schoolId, int $yearId, int $sectionId, ?int $classId = null): array
    {
        if ($schoolId <= 0 || $yearId <= 0 || $sectionId <= 0) {
            return [];
        }

        self::ensureTables();
        $sql =
            'SELECT ac.id, ac.classe_id, ac.cours_id, ac.agent_id, ac.volume_horaire_hebdo,
                    (SELECT COUNT(*) FROM horaires_cours h
                     WHERE h.attribution_cours_id = ac.id) AS planned_hours,
                    c.nom_classe, o.nom_option, co.nom_cours, a.nom, a.postnom, a.prenom, r.titre_role,
                    ti.indice, pc.id AS programme_id
             FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             LEFT JOIN options o ON o.id = c.option_id
             INNER JOIN cours co ON co.id = ac.cours_id AND co.ecole_id = ac.ecole_id
             INNER JOIN agents a ON a.id = ac.agent_id AND a.ecole_id = ac.ecole_id
             INNER JOIN roles_administration r ON r.id = a.role_id
             LEFT JOIN indices_enseignants ti
               ON ti.ecole_id = ac.ecole_id AND ti.annee_scolaire_id = ac.annee_scolaire_id
              AND ti.agent_id = ac.agent_id
             LEFT JOIN programmes_cours pc
               ON pc.ecole_id = ac.ecole_id AND pc.annee_scolaire_id = ac.annee_scolaire_id
              AND pc.section_id = c.section_id AND pc.cours_id = ac.cours_id
             LEFT JOIN programmes_cours_classes pcc
               ON pcc.programme_id = pc.id AND pcc.classe_id = ac.classe_id
             WHERE ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
               AND c.section_id = :section_id
               AND (pc.id IS NULL OR pcc.classe_id IS NOT NULL)';
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
            'SELECT c.id, c.nom_classe, o.nom_option, c.option_id
             FROM classes c
             LEFT JOIN options o ON o.id = c.option_id AND o.section_id = c.section_id
             WHERE c.ecole_id = :ecole_id AND c.section_id = :section_id
             ORDER BY c.nom_classe, o.nom_option'
        );
        $classes->execute([':ecole_id' => $schoolId, ':section_id' => $sectionId]);

        $teachers = $db->prepare(
            "SELECT ag.id, ag.nom, ag.postnom, ag.prenom, r.titre_role
             FROM agents ag
             INNER JOIN roles_administration r ON r.id = ag.role_id
             WHERE ag.ecole_id = :ecole_id
               AND LOWER(r.titre_role) LIKE '%enseignant%'
               AND ((:is_secondary_for_teachers = 1 AND ag.est_enseignant_secondaire = 1)
                    OR (:is_not_secondary_for_teachers = 0 AND ag.est_enseignant_secondaire = 0))
             ORDER BY ag.nom, ag.postnom, ag.prenom"
        );
        $teachers->execute([
            ':ecole_id' => $schoolId,
            ':is_secondary_for_teachers' => self::isSecondarySection($sectionId) ? 1 : 0,
            ':is_not_secondary_for_teachers' => self::isSecondarySection($sectionId) ? 1 : 0,
        ]);

        return [
            'classes' => $classes->fetchAll(PDO::FETCH_ASSOC),
            'teachers' => $teachers->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public static function getCoursePlans(int $schoolId, int $yearId, int $sectionId): array
    {
        self::ensureTables();
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT pc.id, pc.cours_id, pc.nature_cours, pc.volume_horaire_hebdo, pc.agent_id,
                    co.nom_cours, co.code_cours, a.nom, a.postnom, a.prenom,
                    COALESCE((
                        SELECT SUM(ac.volume_horaire_hebdo)
                        FROM attribution_cours ac
                        WHERE ac.ecole_id = pc.ecole_id
                          AND ac.annee_scolaire_id = pc.annee_scolaire_id
                          AND ac.agent_id = pc.agent_id
                    ), 0) AS teacher_weekly_load
             FROM programmes_cours pc
             INNER JOIN cours co ON co.id = pc.cours_id AND co.ecole_id = pc.ecole_id
             LEFT JOIN agents a ON a.id = pc.agent_id AND a.ecole_id = pc.ecole_id
             WHERE pc.ecole_id = :ecole_id AND pc.annee_scolaire_id = :annee_scolaire_id
               AND pc.section_id = :section_id
             ORDER BY co.nom_cours'
        );
        $stmt->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$plans) {
            return [];
        }

        $planIds = array_map(static fn (array $plan): int => (int) $plan['id'], $plans);
        $placeholders = implode(',', array_fill(0, count($planIds), '?'));
        $classes = $db->prepare(
            "SELECT pcc.programme_id, c.id, c.nom_classe, o.nom_option
             FROM programmes_cours_classes pcc
             INNER JOIN classes c ON c.id = pcc.classe_id
             LEFT JOIN options o ON o.id = c.option_id
             WHERE pcc.programme_id IN ({$placeholders})
             ORDER BY c.nom_classe, o.nom_option"
        );
        $classes->execute($planIds);
        $classesByPlan = [];
        foreach ($classes->fetchAll(PDO::FETCH_ASSOC) as $class) {
            $classesByPlan[(int) $class['programme_id']][] = $class;
        }

        foreach ($plans as &$plan) {
            $plan['classes'] = $classesByPlan[(int) $plan['id']] ?? [];
            $plan['class_count'] = count($plan['classes']);
            $plan['teacher_index'] = !empty($plan['agent_id'])
                ? self::ensureTeacherIndex($schoolId, $yearId, (int) $plan['agent_id'])
                : null;
        }
        unset($plan);

        return $plans;
    }

    public static function createCoursePlan(
        int $schoolId,
        int $yearId,
        int $sectionId,
        string $courseName,
        string $courseCode,
        string $courseType,
        array $classIds,
        int $weeklyHours
    ): void {
        $courseName = trim($courseName);
        $courseCode = strtoupper(trim($courseCode));
        $classIds = array_values(array_unique(array_filter(
            array_map('intval', $classIds),
            static fn (int $classId): bool => $classId > 0
        )));
        if (
            $schoolId <= 0 || $yearId <= 0 || $sectionId <= 0
            || $courseName === '' || mb_strlen($courseName, 'UTF-8') > 100
            || $courseCode === '' || strlen($courseCode) > 15
            || !in_array($courseType, ['commun', 'option'], true)
            || !$classIds || $weeklyHours < 1 || $weeklyHours > 35
        ) {
            throw new \InvalidArgumentException('Vérifiez le cours, les classes sélectionnées et le volume hebdomadaire (1 à 35 heures par classe).');
        }
        if ($courseType === 'option' && count($classIds) !== 1) {
            throw new \InvalidArgumentException('Un cours d’option doit être rattaché à une seule classe.');
        }

        self::ensureTables();
        $db = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($classIds), '?'));
        $classQuery = $db->prepare(
            "SELECT c.id, o.id AS section_option_id FROM classes c
             LEFT JOIN options o ON o.id = c.option_id AND o.section_id = c.section_id
            WHERE c.ecole_id = ? AND c.section_id = ? AND c.id IN ({$placeholders})"
        );
        $classQuery->execute(array_merge([$schoolId, $sectionId], $classIds));
        $classes = $classQuery->fetchAll(PDO::FETCH_ASSOC);
        if (count($classes) !== count($classIds)) {
            throw new \InvalidArgumentException('Une ou plusieurs classes sont hors de votre section.');
        }
        if ($courseType === 'option' && empty($classes[0]['section_option_id'])) {
            throw new \InvalidArgumentException('Un cours d’option doit être rattaché à une classe qui possède une option.');
        }

        $db->beginTransaction();
        try {
            $existingCourse = $db->prepare(
                'SELECT id, nom_cours FROM cours
                 WHERE ecole_id = :ecole_id AND code_cours = :code_cours LIMIT 1'
            );
            $existingCourse->execute([':ecole_id' => $schoolId, ':code_cours' => $courseCode]);
            $course = $existingCourse->fetch(PDO::FETCH_ASSOC);
            if ($course && mb_strtolower(trim((string) $course['nom_cours']), 'UTF-8') !== mb_strtolower($courseName, 'UTF-8')) {
                throw new \InvalidArgumentException('Ce code de cours est déjà utilisé par une autre matière.');
            }
            if ($course) {
                $courseId = (int) $course['id'];
            } else {
                $insertCourse = $db->prepare(
                    'INSERT INTO cours (ecole_id, nom_cours, code_cours)
                     VALUES (:ecole_id, :nom_cours, :code_cours)'
                );
                $insertCourse->execute([
                    ':ecole_id' => $schoolId,
                    ':nom_cours' => $courseName,
                    ':code_cours' => $courseCode,
                ]);
                $courseId = (int) $db->lastInsertId();
            }

            $existingClassPlaceholders = implode(',', array_fill(0, count($classIds), '?'));
            $unselectedAssignments = $db->prepare(
                'SELECT c.id FROM attribution_cours ac
                 INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
                 WHERE ac.ecole_id = ? AND ac.annee_scolaire_id = ? AND ac.cours_id = ?
                   AND c.section_id = ?
                   AND c.id NOT IN (' . $existingClassPlaceholders . ')
                 LIMIT 1'
            );
            $unselectedAssignments->execute(array_merge(
                [$schoolId, $yearId, $courseId, $sectionId],
                $classIds
            ));
            if ($unselectedAssignments->fetchColumn()) {
                throw new \InvalidArgumentException('Ce cours est déjà affecté à une autre classe. Incluez cette classe dans la sélection ou utilisez un autre code.');
            }

            $existingPlan = $db->prepare(
                'SELECT id FROM programmes_cours
                 WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id
                   AND section_id = :section_id AND cours_id = :cours_id LIMIT 1'
            );
            $existingPlan->execute([
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':section_id' => $sectionId,
                ':cours_id' => $courseId,
            ]);
            if ($existingPlan->fetchColumn()) {
                throw new \InvalidArgumentException('Ce cours possède déjà une configuration pour cette année scolaire et cette section.');
            }

            $insertPlan = $db->prepare(
                'INSERT INTO programmes_cours
                    (ecole_id, annee_scolaire_id, section_id, cours_id, nature_cours, volume_horaire_hebdo)
                 VALUES (:ecole_id, :annee_scolaire_id, :section_id, :cours_id, :nature_cours, :volume)'
            );
            $insertPlan->execute([
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':section_id' => $sectionId,
                ':cours_id' => $courseId,
                ':nature_cours' => $courseType,
                ':volume' => $weeklyHours,
            ]);
            $planId = (int) $db->lastInsertId();
            $insertClass = $db->prepare(
                'INSERT INTO programmes_cours_classes (programme_id, classe_id)
                 VALUES (:programme_id, :classe_id)'
            );
            foreach ($classIds as $classId) {
                $insertClass->execute([':programme_id' => $planId, ':classe_id' => $classId]);
            }
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    public static function updateCoursePlan(
        int $schoolId,
        int $yearId,
        int $sectionId,
        int $planId,
        string $courseName,
        string $courseCode,
        string $courseType,
        array $classIds,
        int $weeklyHours
    ): void {
        $courseName = trim($courseName);
        $courseCode = strtoupper(trim($courseCode));
        $classIds = array_values(array_unique(array_filter(
            array_map('intval', $classIds),
            static fn (int $classId): bool => $classId > 0
        )));
        if (
            $schoolId <= 0 || $yearId <= 0 || $sectionId <= 0 || $planId <= 0
            || $courseName === '' || mb_strlen($courseName, 'UTF-8') > 100
            || $courseCode === '' || strlen($courseCode) > 15
            || !in_array($courseType, ['commun', 'option'], true)
            || !$classIds || $weeklyHours < 1 || $weeklyHours > 35
        ) {
            throw new \InvalidArgumentException('Vérifiez le cours, les classes sélectionnées et le volume hebdomadaire (1 à 35 heures par classe).');
        }
        if ($courseType === 'option' && count($classIds) !== 1) {
            throw new \InvalidArgumentException('Un cours d’option doit être rattaché à une seule classe.');
        }

        self::ensureTables();
        $db = Database::getConnection();
        $planQuery = $db->prepare(
            'SELECT pc.*, co.nom_cours, co.code_cours
             FROM programmes_cours pc
             INNER JOIN cours co ON co.id = pc.cours_id AND co.ecole_id = pc.ecole_id
             WHERE pc.id = :id AND pc.ecole_id = :ecole_id
               AND pc.annee_scolaire_id = :annee_scolaire_id AND pc.section_id = :section_id
             LIMIT 1'
        );
        $planQuery->execute([
            ':id' => $planId,
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);
        $plan = $planQuery->fetch(PDO::FETCH_ASSOC);
        if (!$plan) {
            throw new \InvalidArgumentException('Ce cours est introuvable dans votre section.');
        }

        $placeholders = implode(',', array_fill(0, count($classIds), '?'));
        $classQuery = $db->prepare(
            "SELECT c.id, o.id AS section_option_id FROM classes c
             LEFT JOIN options o ON o.id = c.option_id AND o.section_id = c.section_id
             WHERE c.ecole_id = ? AND c.section_id = ? AND c.id IN ({$placeholders})"
        );
        $classQuery->execute(array_merge([$schoolId, $sectionId], $classIds));
        $classes = $classQuery->fetchAll(PDO::FETCH_ASSOC);
        if (count($classes) !== count($classIds)) {
            throw new \InvalidArgumentException('Une ou plusieurs classes sont hors de votre section.');
        }
        if ($courseType === 'option' && empty($classes[0]['section_option_id'])) {
            throw new \InvalidArgumentException('Un cours d’option doit être rattaché à une classe qui possède une option.');
        }

        $codeConflict = $db->prepare(
            'SELECT id FROM cours
             WHERE ecole_id = :ecole_id AND code_cours = :code_cours AND id <> :cours_id LIMIT 1'
        );
        $codeConflict->execute([
            ':ecole_id' => $schoolId,
            ':code_cours' => $courseCode,
            ':cours_id' => (int) $plan['cours_id'],
        ]);
        if ($codeConflict->fetchColumn()) {
            throw new \InvalidArgumentException('Ce code de cours est déjà utilisé par une autre matière.');
        }

        $existingClassesQuery = $db->prepare(
            'SELECT pcc.classe_id, ac.id AS attribution_id, ac.agent_id,
                    ac.volume_horaire_hebdo,
                    (SELECT COUNT(*) FROM horaires_cours h WHERE h.attribution_cours_id = ac.id) AS planned_hours,
                    (SELECT COUNT(*) FROM evaluations ev WHERE ev.attribution_cours_id = ac.id) AS evaluations
             FROM programmes_cours_classes pcc
             LEFT JOIN attribution_cours ac
               ON ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
              AND ac.cours_id = :cours_id AND ac.classe_id = pcc.classe_id
             WHERE pcc.programme_id = :programme_id'
        );
        $existingClassesQuery->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':cours_id' => (int) $plan['cours_id'],
            ':programme_id' => $planId,
        ]);
        $existingRows = $existingClassesQuery->fetchAll(PDO::FETCH_ASSOC);
        $existingClassIds = array_map(static fn (array $row): int => (int) $row['classe_id'], $existingRows);
        $removedClassIds = array_values(array_diff($existingClassIds, $classIds));
        $teacherId = (int) ($plan['agent_id'] ?? 0);
        $oldTeacherHours = 0;

        foreach ($existingRows as $row) {
            if ($row['attribution_id'] === null) {
                continue;
            }
            $rowTeacherId = (int) $row['agent_id'];
            if ($rowTeacherId === $teacherId) {
                $oldTeacherHours += (int) $row['volume_horaire_hebdo'];
            }
            if (in_array((int) $row['classe_id'], $removedClassIds, true) && (int) $row['evaluations'] > 0) {
                throw new \InvalidArgumentException('Une classe ne peut pas être retirée du cours après la saisie d’évaluations.');
            }
            if ((int) $row['planned_hours'] > $weeklyHours) {
                throw new \InvalidArgumentException('Le nouveau volume ne peut être inférieur aux heures déjà planifiées.');
            }
        }

        if ($teacherId > 0) {
            $newTeacherHours = $weeklyHours * count($classIds);
            $otherTeacherHours = self::getWeeklyWorkload($schoolId, $yearId, $teacherId) - $oldTeacherHours;
            if ($otherTeacherHours + $newTeacherHours > 35) {
                throw new \InvalidArgumentException('La charge totale de cet enseignant dépasserait 35 heures par semaine.');
            }
        }

        $db->beginTransaction();
        try {
            $updateCourse = $db->prepare(
                'UPDATE cours SET nom_cours = :nom_cours, code_cours = :code_cours
                 WHERE id = :id AND ecole_id = :ecole_id'
            );
            $updateCourse->execute([
                ':nom_cours' => $courseName,
                ':code_cours' => $courseCode,
                ':id' => (int) $plan['cours_id'],
                ':ecole_id' => $schoolId,
            ]);
            $updatePlan = $db->prepare(
                'UPDATE programmes_cours
                 SET nature_cours = :nature_cours, volume_horaire_hebdo = :volume
                 WHERE id = :id AND ecole_id = :ecole_id
                   AND annee_scolaire_id = :annee_scolaire_id'
            );
            $updatePlan->execute([
                ':nature_cours' => $courseType,
                ':volume' => $weeklyHours,
                ':id' => $planId,
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
            ]);

            if ($removedClassIds) {
                $removedPlaceholders = implode(',', array_fill(0, count($removedClassIds), '?'));
                $deleteSlots = $db->prepare(
                    'DELETE FROM horaires_cours
                     WHERE ecole_id = ? AND annee_scolaire_id = ? AND cours_id = ?
                       AND classe_id IN (' . $removedPlaceholders . ')'
                );
                $deleteSlots->execute(array_merge([$schoolId, $yearId, (int) $plan['cours_id']], $removedClassIds));
                $deleteAssignments = $db->prepare(
                    'DELETE FROM attribution_cours
                     WHERE ecole_id = ? AND annee_scolaire_id = ? AND cours_id = ?
                       AND classe_id IN (' . $removedPlaceholders . ')'
                );
                $deleteAssignments->execute(array_merge([$schoolId, $yearId, (int) $plan['cours_id']], $removedClassIds));
                $deletePlanClasses = $db->prepare(
                    'DELETE FROM programmes_cours_classes
                     WHERE programme_id = ? AND classe_id IN (' . $removedPlaceholders . ')'
                );
                $deletePlanClasses->execute(array_merge([$planId], $removedClassIds));
            }

            $upsertClass = $db->prepare(
                'INSERT INTO programmes_cours_classes (programme_id, classe_id)
                 VALUES (:programme_id, :classe_id)
                 ON DUPLICATE KEY UPDATE classe_id = VALUES(classe_id)'
            );
            foreach ($classIds as $classId) {
                $upsertClass->execute([':programme_id' => $planId, ':classe_id' => $classId]);
            }

            if ($teacherId > 0) {
                $upsertAssignment = $db->prepare(
                    'INSERT INTO attribution_cours
                        (ecole_id, classe_id, cours_id, agent_id, annee_scolaire_id, volume_horaire_hebdo)
                     VALUES (:ecole_id, :classe_id, :cours_id, :agent_id, :annee_scolaire_id, :volume)
                     ON DUPLICATE KEY UPDATE volume_horaire_hebdo = VALUES(volume_horaire_hebdo)'
                );
                foreach ($classIds as $classId) {
                    $upsertAssignment->execute([
                        ':ecole_id' => $schoolId,
                        ':classe_id' => $classId,
                        ':cours_id' => (int) $plan['cours_id'],
                        ':agent_id' => $teacherId,
                        ':annee_scolaire_id' => $yearId,
                        ':volume' => $weeklyHours,
                    ]);
                }
            }
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        if ($teacherId > 0) {
            self::clearDayOffIfNotRequired($schoolId, $yearId, $teacherId);
        }
    }

    public static function deleteCoursePlan(int $schoolId, int $yearId, int $sectionId, int $planId): void
    {
        if ($schoolId <= 0 || $yearId <= 0 || $sectionId <= 0 || $planId <= 0) {
            throw new \InvalidArgumentException('Le cours sélectionné est invalide.');
        }
        self::ensureTables();
        $db = Database::getConnection();
        $planQuery = $db->prepare(
            'SELECT cours_id, agent_id FROM programmes_cours
             WHERE id = :id AND ecole_id = :ecole_id
               AND annee_scolaire_id = :annee_scolaire_id AND section_id = :section_id LIMIT 1'
        );
        $planQuery->execute([
            ':id' => $planId,
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);
        $plan = $planQuery->fetch(PDO::FETCH_ASSOC);
        if (!$plan) {
            throw new \InvalidArgumentException('Ce cours est introuvable dans votre section.');
        }
        $courseId = (int) $plan['cours_id'];

        $evaluationQuery = $db->prepare(
            'SELECT 1 FROM attribution_cours ac
             INNER JOIN evaluations ev ON ev.attribution_cours_id = ac.id
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             WHERE ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
               AND ac.cours_id = :cours_id AND c.section_id = :section_id LIMIT 1'
        );
        $evaluationQuery->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':cours_id' => $courseId,
            ':section_id' => $sectionId,
        ]);
        if ($evaluationQuery->fetchColumn()) {
            throw new \InvalidArgumentException('Ce cours possède déjà des évaluations et ne peut pas être supprimé.');
        }

        $db->beginTransaction();
        try {
            $deleteSlots = $db->prepare(
                'DELETE h FROM horaires_cours h
                 INNER JOIN attribution_cours ac ON ac.id = h.attribution_cours_id
                 INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
                 WHERE ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
                   AND ac.cours_id = :cours_id AND c.section_id = :section_id'
            );
            $deleteSlots->execute([
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':cours_id' => $courseId,
                ':section_id' => $sectionId,
            ]);
            $deleteAssignments = $db->prepare(
                'DELETE ac FROM attribution_cours ac
                 INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
                 WHERE ac.ecole_id = :ecole_id AND ac.annee_scolaire_id = :annee_scolaire_id
                   AND ac.cours_id = :cours_id AND c.section_id = :section_id'
            );
            $deleteAssignments->execute([
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':cours_id' => $courseId,
                ':section_id' => $sectionId,
            ]);
            $deleteMappings = $db->prepare('DELETE FROM programmes_cours_classes WHERE programme_id = :id');
            $deleteMappings->execute([':id' => $planId]);
            $deletePlan = $db->prepare(
                'DELETE FROM programmes_cours
                 WHERE id = :id AND ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id'
            );
            $deletePlan->execute([
                ':id' => $planId,
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
            ]);

            $remainingUsage = $db->prepare(
                'SELECT
                   (SELECT COUNT(*) FROM attribution_cours WHERE ecole_id = :assignment_ecole_id AND cours_id = :assignment_cours_id)
                   + (SELECT COUNT(*) FROM programmes_cours WHERE ecole_id = :programme_ecole_id AND cours_id = :programme_cours_id)'
            );
            $remainingUsage->execute([
                ':assignment_ecole_id' => $schoolId,
                ':assignment_cours_id' => $courseId,
                ':programme_ecole_id' => $schoolId,
                ':programme_cours_id' => $courseId,
            ]);
            if ((int) $remainingUsage->fetchColumn() === 0) {
                $deleteCourse = $db->prepare('DELETE FROM cours WHERE id = :id AND ecole_id = :ecole_id');
                $deleteCourse->execute([':id' => $courseId, ':ecole_id' => $schoolId]);
            }
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        $teacherId = (int) ($plan['agent_id'] ?? 0);
        if ($teacherId > 0) {
            self::clearDayOffIfNotRequired($schoolId, $yearId, $teacherId);
        }
    }

    public static function assignCourseTeacher(
        int $schoolId,
        int $yearId,
        int $sectionId,
        int $planId,
        int $teacherId
    ): void {
        if ($schoolId <= 0 || $yearId <= 0 || $sectionId <= 0 || $planId <= 0 || $teacherId <= 0) {
            throw new \InvalidArgumentException('Le cours et l’enseignant sélectionnés sont invalides.');
        }
        self::ensureTables();
        $db = Database::getConnection();
        $planQuery = $db->prepare(
            'SELECT pc.*, co.nom_cours
             FROM programmes_cours pc
             INNER JOIN cours co ON co.id = pc.cours_id AND co.ecole_id = pc.ecole_id
             WHERE pc.id = :id AND pc.ecole_id = :ecole_id
               AND pc.annee_scolaire_id = :annee_scolaire_id AND pc.section_id = :section_id
             LIMIT 1'
        );
        $planQuery->execute([
            ':id' => $planId,
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);
        $plan = $planQuery->fetch(PDO::FETCH_ASSOC);
        if (!$plan) {
            throw new \InvalidArgumentException('Ce cours est introuvable dans votre section.');
        }

        $teacherQuery = $db->prepare(
            "SELECT ag.id FROM agents ag
             INNER JOIN roles_administration r ON r.id = ag.role_id
             WHERE ag.id = :agent_id AND ag.ecole_id = :ecole_id
               AND LOWER(r.titre_role) LIKE '%enseignant%'
               AND ((:is_secondary = 1 AND ag.est_enseignant_secondaire = 1)
                    OR (:is_not_secondary = 0 AND ag.est_enseignant_secondaire = 0))
             LIMIT 1"
        );
        $isSecondary = self::isSecondarySection($sectionId) ? 1 : 0;
        $teacherQuery->execute([
            ':agent_id' => $teacherId,
            ':ecole_id' => $schoolId,
            ':is_secondary' => $isSecondary,
            ':is_not_secondary' => $isSecondary,
        ]);
        if (!$teacherQuery->fetchColumn()) {
            throw new \InvalidArgumentException('L’enseignant sélectionné n’appartient pas à votre section.');
        }

        $classQuery = $db->prepare(
            'SELECT pcc.classe_id
             FROM programmes_cours_classes pcc
             INNER JOIN classes c ON c.id = pcc.classe_id AND c.ecole_id = :ecole_id
               AND c.section_id = :section_id
             WHERE pcc.programme_id = :programme_id'
        );
        $classQuery->execute([
            ':ecole_id' => $schoolId,
            ':section_id' => $sectionId,
            ':programme_id' => $planId,
        ]);
        $classIds = array_map('intval', $classQuery->fetchAll(PDO::FETCH_COLUMN));
        if (!$classIds) {
            throw new \InvalidArgumentException('Ce cours n’est associé à aucune classe.');
        }
        $totalPlanHours = (int) $plan['volume_horaire_hebdo'] * count($classIds);

        $existingRows = $db->prepare(
            'SELECT ac.id, ac.agent_id, ac.classe_id, ac.volume_horaire_hebdo
             FROM attribution_cours ac
             WHERE ac.ecole_id = ? AND ac.annee_scolaire_id = ?
               AND ac.cours_id = ? AND ac.classe_id IN (' . implode(',', array_fill(0, count($classIds), '?')) . ')'
        );
        $existingRows->execute(array_merge([$schoolId, $yearId, (int) $plan['cours_id']], $classIds));
        $oldHoursForTeacher = 0;
        $previousTeachers = [];
        foreach ($existingRows->fetchAll(PDO::FETCH_ASSOC) as $existing) {
            $previousTeachers[(int) $existing['agent_id']] = true;
            if ((int) $existing['agent_id'] === $teacherId) {
                $oldHoursForTeacher += (int) $existing['volume_horaire_hebdo'];
            } else {
                $hasActivity = $db->prepare(
                    'SELECT
                       (SELECT COUNT(*) FROM horaires_cours WHERE attribution_cours_id = :slot_assignment) +
                       (SELECT COUNT(*) FROM evaluations WHERE attribution_cours_id = :evaluation_assignment)'
                );
                $hasActivity->execute([
                    ':slot_assignment' => (int) $existing['id'],
                    ':evaluation_assignment' => (int) $existing['id'],
                ]);
                if ((int) $hasActivity->fetchColumn() > 0) {
                    throw new \InvalidArgumentException('Retirez les créneaux et évaluations existants avant de changer l’enseignant de ce cours.');
                }
            }
        }

        $teacherWorkload = self::getWeeklyWorkload($schoolId, $yearId, $teacherId) - $oldHoursForTeacher;
        if ($teacherWorkload + $totalPlanHours > 35) {
            throw new \InvalidArgumentException('La charge totale de cet enseignant dépasserait 35 heures par semaine.');
        }

        $db->beginTransaction();
        try {
            $saveAssignment = $db->prepare(
                'INSERT INTO attribution_cours
                    (ecole_id, classe_id, cours_id, agent_id, annee_scolaire_id, volume_horaire_hebdo)
                 VALUES (:ecole_id, :classe_id, :cours_id, :agent_id, :annee_scolaire_id, :volume)
                 ON DUPLICATE KEY UPDATE agent_id = VALUES(agent_id),
                    volume_horaire_hebdo = VALUES(volume_horaire_hebdo)'
            );
            foreach ($classIds as $classId) {
                $saveAssignment->execute([
                    ':ecole_id' => $schoolId,
                    ':classe_id' => $classId,
                    ':cours_id' => (int) $plan['cours_id'],
                    ':agent_id' => $teacherId,
                    ':annee_scolaire_id' => $yearId,
                    ':volume' => (int) $plan['volume_horaire_hebdo'],
                ]);
            }
            $saveTeacher = $db->prepare(
                'UPDATE programmes_cours SET agent_id = :agent_id
                 WHERE id = :id AND ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id'
            );
            $saveTeacher->execute([
                ':agent_id' => $teacherId,
                ':id' => $planId,
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
            ]);
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        self::ensureTeacherIndex($schoolId, $yearId, $teacherId);
        foreach (array_keys($previousTeachers) as $previousTeacherId) {
            if ((int) $previousTeacherId !== $teacherId) {
                self::clearDayOffIfNotRequired($schoolId, $yearId, (int) $previousTeacherId);
            }
        }
        self::clearDayOffIfNotRequired($schoolId, $yearId, $teacherId);
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
            throw new \InvalidArgumentException('Les informations sont invalides. Une affectation ne peut pas dépasser 35 heures hebdomadaires.');
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
               AND c.section_id = :section_id
               AND LOWER(r.titre_role) LIKE '%enseignant%'
               AND ((:is_secondary_for_teacher = 1 AND ag.est_enseignant_secondaire = 1)
                    OR (:is_not_secondary_for_teacher = 0 AND ag.est_enseignant_secondaire = 0))
             LIMIT 1"
        );
        $check->execute([
            ':cours_id' => $courseId,
            ':agent_id' => $teacherId,
            ':annee_scolaire_id' => $yearId,
            ':classe_id' => $classId,
            ':ecole_id' => $schoolId,
            ':section_id' => $sectionId,
            ':is_secondary_for_teacher' => self::isSecondarySection($sectionId) ? 1 : 0,
            ':is_not_secondary_for_teacher' => self::isSecondarySection($sectionId) ? 1 : 0,
        ]);
        if (!$check->fetchColumn()) {
            throw new \InvalidArgumentException('La classe, le cours ou l’enseignant ne relève pas de votre section.');
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
                'SELECT ac.id, ac.agent_id, ac.classe_id FROM attribution_cours ac
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
                throw new \InvalidArgumentException('Le volume hebdomadaire ne peut être inférieur aux heures déjà planifiées.');
            }
            if (
                (int) $currentAssignment['agent_id'] !== $teacherId
                || (int) $currentAssignment['classe_id'] !== $classId
            ) {
                $hasSlots = $db->prepare(
                    'SELECT 1 FROM horaires_cours WHERE attribution_cours_id = :id LIMIT 1'
                );
                $hasSlots->execute([':id' => $assignmentId]);
                if ($hasSlots->fetchColumn()) {
                    throw new \InvalidArgumentException('Retirez les heures planifiées avant de changer la classe ou l’enseignant de cette affectation.');
                }
            }
        }

        $targetWorkload = self::getWeeklyWorkload($schoolId, $yearId, $teacherId);
        if ($previousTeacherId === $teacherId) {
            $targetWorkload -= $previousVolume;
        }
        if ($targetWorkload + $weeklyPeriods > 35) {
            throw new \InvalidArgumentException('La charge totale de cet enseignant dépasserait 35 heures, le maximum planifiable avec une journée libre.');
        }
        self::ensureTeacherIndex($schoolId, $yearId, $teacherId);

        $db->beginTransaction();
        try {
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
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        if ($previousTeacherId !== null && $previousTeacherId !== $teacherId) {
            self::clearDayOffIfNotRequired($schoolId, $yearId, $previousTeacherId);
        }
        self::clearDayOffIfNotRequired($schoolId, $yearId, $teacherId);
    }

    public static function getScheduleForClass(int $schoolId, int $yearId, int $classId): array
    {
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT h.id, h.attribution_cours_id, h.jour_id, h.periode, c.section_id, co.nom_cours, c.nom_classe, o.nom_option,
                    ti.indice, a.nom, a.postnom, a.prenom
             FROM horaires_cours h
             INNER JOIN attribution_cours ac ON ac.id = h.attribution_cours_id
             INNER JOIN classes c ON c.id = h.classe_id
             LEFT JOIN options o ON o.id = c.option_id
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

    public static function getScheduleForSection(int $schoolId, int $yearId, int $sectionId): array
    {
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT h.id, h.attribution_cours_id, h.agent_id, h.jour_id, h.periode, c.id AS classe_id,
                    c.section_id, c.nom_classe, o.nom_option, co.nom_cours,
                    a.nom, a.postnom, a.prenom, ti.indice,
                    CASE WHEN pc.nature_cours = \'commun\' AND pcc.classe_id IS NOT NULL
                         THEN pc.id ELSE NULL END AS programme_id
             FROM horaires_cours h
             INNER JOIN attribution_cours ac ON ac.id = h.attribution_cours_id
             INNER JOIN classes c ON c.id = h.classe_id AND c.ecole_id = h.ecole_id
             LEFT JOIN options o ON o.id = c.option_id
             INNER JOIN cours co ON co.id = h.cours_id AND co.ecole_id = h.ecole_id
             INNER JOIN agents a ON a.id = h.agent_id AND a.ecole_id = h.ecole_id
             INNER JOIN indices_enseignants ti
               ON ti.ecole_id = h.ecole_id AND ti.annee_scolaire_id = h.annee_scolaire_id
              AND ti.agent_id = h.agent_id
             LEFT JOIN programmes_cours pc
               ON pc.ecole_id = ac.ecole_id AND pc.annee_scolaire_id = ac.annee_scolaire_id
              AND pc.section_id = c.section_id AND pc.cours_id = ac.cours_id
             LEFT JOIN programmes_cours_classes pcc
               ON pcc.programme_id = pc.id AND pcc.classe_id = c.id
             WHERE h.ecole_id = :ecole_id AND h.annee_scolaire_id = :annee_scolaire_id
               AND c.section_id = :section_id
             ORDER BY h.jour_id, h.periode, c.nom_classe, o.nom_option'
        );
        $stmt->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);
        $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $slotsByTeacherTime = [];
        foreach ($slots as $index => $slot) {
            $key = (int) $slot['jour_id'] . ':' . (int) $slot['periode'] . ':' . (int) $slot['agent_id'];
            $slotsByTeacherTime[$key][] = $index;
        }

        foreach ($slotsByTeacherTime as $slotIndexes) {
            if (count($slotIndexes) < 2) {
                continue;
            }
            $programIds = array_unique(array_map(
                static fn (int $index): int => (int) ($slots[$index]['programme_id'] ?? 0),
                $slotIndexes
            ));
            if (count($programIds) === 1 && (int) array_values($programIds)[0] > 0) {
                continue;
            }
            foreach ($slotIndexes as $index) {
                $slots[$index]['teacher_conflict'] = true;
            }
        }

        return $slots;
    }

    public static function deleteAssignment(int $schoolId, int $yearId, int $sectionId, int $assignmentId): void
    {
        self::ensureTables();
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ac.agent_id
             FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id
             WHERE ac.id = :id AND ac.ecole_id = :ecole_id
               AND ac.annee_scolaire_id = :annee_scolaire_id
               AND c.section_id = :section_id LIMIT 1'
        );
        $stmt->execute([
            ':id' => $assignmentId,
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);
        $agentId = $stmt->fetchColumn();
        if ($agentId === false) {
            throw new \InvalidArgumentException('Cette affectation est introuvable ou hors de votre section.');
        }

        $managedByPlan = $db->prepare(
            'SELECT 1 FROM attribution_cours ac
             INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
             INNER JOIN programmes_cours pc
               ON pc.ecole_id = ac.ecole_id AND pc.annee_scolaire_id = ac.annee_scolaire_id
              AND pc.section_id = c.section_id AND pc.cours_id = ac.cours_id
             INNER JOIN programmes_cours_classes pcc
               ON pcc.programme_id = pc.id AND pcc.classe_id = ac.classe_id
             WHERE ac.id = :id LIMIT 1'
        );
        $managedByPlan->execute([':id' => $assignmentId]);
        if ($managedByPlan->fetchColumn()) {
            throw new \InvalidArgumentException('Cette affectation est gérée par le programme du cours. Modifiez l’enseignant depuis la liste des cours.');
        }

        $evaluations = $db->prepare(
            'SELECT 1 FROM evaluations WHERE attribution_cours_id = :id LIMIT 1'
        );
        $evaluations->execute([':id' => $assignmentId]);
        if ($evaluations->fetchColumn()) {
            throw new \InvalidArgumentException('Cette affectation possède déjà des évaluations et ne peut pas être supprimée.');
        }

        $db->beginTransaction();
        try {
            $deleteSlots = $db->prepare('DELETE FROM horaires_cours WHERE attribution_cours_id = :id');
            $deleteSlots->execute([':id' => $assignmentId]);
            $deleteAssignment = $db->prepare(
                'DELETE FROM attribution_cours
                 WHERE id = :id AND ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id'
            );
            $deleteAssignment->execute([
                ':id' => $assignmentId,
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
            ]);
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        self::clearDayOffIfNotRequired($schoolId, $yearId, (int) $agentId);
    }

    public static function getScheduleForTeacher(int $schoolId, int $yearId, int $agentId): array
    {
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT h.jour_id, h.periode, c.section_id, co.nom_cours, c.nom_classe, o.nom_option, ti.indice
             FROM horaires_cours h
             INNER JOIN classes c ON c.id = h.classe_id
             LEFT JOIN options o ON o.id = c.option_id
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
            'SELECT c.id AS classe_id, c.nom_classe, c.section_id, an.id AS annee_scolaire_id, an.annee
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
            throw new \InvalidArgumentException('Le jour ou l’heure sélectionnée est invalide.');
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

        $targetAssignments = [$assignment];
        $programId = 0;
        $program = $db->prepare(
            'SELECT pc.id
             FROM programmes_cours pc
             INNER JOIN programmes_cours_classes pcc ON pcc.programme_id = pc.id
             WHERE pc.ecole_id = :ecole_id AND pc.annee_scolaire_id = :annee_scolaire_id
               AND pc.section_id = :section_id AND pc.cours_id = :cours_id
               AND pc.nature_cours = \'commun\' AND pcc.classe_id = :classe_id
             LIMIT 1'
        );
        $program->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
            ':cours_id' => (int) $assignment['cours_id'],
            ':classe_id' => (int) $assignment['classe_id'],
        ]);
        $programId = (int) ($program->fetchColumn() ?: 0);
        if ($programId > 0) {
            $targetStmt = $db->prepare(
                'SELECT ac.id, ac.classe_id, ac.cours_id, ac.agent_id, ac.volume_horaire_hebdo
                 FROM programmes_cours_classes pcc
                 INNER JOIN classes c ON c.id = pcc.classe_id AND c.ecole_id = :ecole_id
                 INNER JOIN programmes_cours pc ON pc.id = pcc.programme_id
                 INNER JOIN attribution_cours ac
                   ON ac.ecole_id = pc.ecole_id AND ac.annee_scolaire_id = pc.annee_scolaire_id
                  AND ac.classe_id = c.id AND ac.cours_id = pc.cours_id
                 WHERE pc.id = :programme_id AND pc.ecole_id = :school_id
                   AND pc.annee_scolaire_id = :year_id AND pc.section_id = :section_id
                 ORDER BY ac.classe_id'
            );
            $targetStmt->execute([
                ':ecole_id' => $schoolId,
                ':programme_id' => $programId,
                ':school_id' => $schoolId,
                ':year_id' => $yearId,
                ':section_id' => $sectionId,
            ]);
            $targetAssignments = $targetStmt->fetchAll(PDO::FETCH_ASSOC);
            $classCount = $db->prepare(
                'SELECT COUNT(*) FROM programmes_cours_classes WHERE programme_id = :programme_id'
            );
            $classCount->execute([':programme_id' => $programId]);
            if (count($targetAssignments) !== (int) $classCount->fetchColumn()) {
                throw new \InvalidArgumentException('Une classe du tronc commun n’a pas d’affectation d’enseignant.');
            }
            foreach ($targetAssignments as $targetAssignment) {
                if ((int) $targetAssignment['agent_id'] !== (int) $assignment['agent_id']) {
                    throw new \InvalidArgumentException('Le tronc commun doit avoir le même enseignant dans toutes ses classes.');
                }
            }
        }

        $dayOff = self::getDayOff($schoolId, $yearId, (int) $assignment['agent_id']);
        if ($dayOff !== null && $dayOff === $dayId) {
            throw new \InvalidArgumentException('Cet enseignant ne peut pas être planifié pendant sa journée hebdomadaire.');
        }
        $workload = self::getWeeklyWorkload($schoolId, $yearId, (int) $assignment['agent_id']);
        if ($workload > 22 && $dayOff === null) {
            throw new \InvalidArgumentException('Cet enseignant dépasse 22 heures par semaine. Configurez d’abord sa journée hebdomadaire.');
        }

        $classIds = array_map(
            static fn (array $target): int => (int) $target['classe_id'],
            $targetAssignments
        );
        $existing = $db->prepare(
            'SELECT classe_id, attribution_cours_id FROM horaires_cours
             WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id
               AND jour_id = :jour_id AND periode = :periode
               AND classe_id IN (' . implode(',', $classIds) . ')'
        );
        $existing->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':jour_id' => $dayId,
            ':periode' => $period,
        ]);
        $existingByClass = [];
        foreach ($existing->fetchAll(PDO::FETCH_ASSOC) as $existingSlot) {
            $existingByClass[(int) $existingSlot['classe_id']] = (int) $existingSlot['attribution_cours_id'];
        }
        foreach ($targetAssignments as $targetAssignment) {
            $classId = (int) $targetAssignment['classe_id'];
            if (
                isset($existingByClass[$classId])
                && $existingByClass[$classId] !== (int) $targetAssignment['id']
            ) {
                throw new \InvalidArgumentException('Une classe concernée a déjà un autre cours pendant cette heure.');
            }
        }

        if ($programId > 0) {
            $scheduledCount = $db->prepare(
                'SELECT COUNT(DISTINCT CONCAT(h.jour_id, \':\', h.periode))
                 FROM horaires_cours h
                 INNER JOIN attribution_cours ac ON ac.id = h.attribution_cours_id
                 INNER JOIN programmes_cours_classes pcc
                   ON pcc.programme_id = :programme_id AND pcc.classe_id = h.classe_id
                 WHERE h.ecole_id = :ecole_id AND h.annee_scolaire_id = :annee_scolaire_id
                   AND ac.cours_id = :cours_id'
            );
            $scheduledCount->execute([
                ':programme_id' => $programId,
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':cours_id' => (int) $assignment['cours_id'],
            ]);
        } else {
            $scheduledCount = $db->prepare(
                'SELECT COUNT(*) FROM horaires_cours WHERE attribution_cours_id = :assignment_id'
            );
            $scheduledCount->execute([':assignment_id' => $assignmentId]);
        }
        $alreadyScheduled = count($existingByClass) === count($targetAssignments);
        if (
            !$alreadyScheduled
            && empty($existingByClass)
            && (int) $scheduledCount->fetchColumn() >= (int) $assignment['volume_horaire_hebdo']
        ) {
            throw new \InvalidArgumentException('Le volume horaire hebdomadaire de cette affectation est déjà entièrement planifié.');
        }

        $candidateSchedule = self::getDailySchedule(self::getHourDuration($schoolId, $sectionId));
        $candidateStart = (int) $candidateSchedule['hours'][$period]['start'];
        $candidateEnd = (int) $candidateSchedule['hours'][$period]['end'];
        $teacherConflict = $db->prepare(
            'SELECT h.attribution_cours_id, h.periode, c.section_id,
                    CASE WHEN pcc.classe_id IS NOT NULL THEN pc.id ELSE NULL END AS programme_id
             FROM horaires_cours h
             INNER JOIN classes c ON c.id = h.classe_id
             LEFT JOIN attribution_cours ac ON ac.id = h.attribution_cours_id
             LEFT JOIN programmes_cours pc
               ON pc.ecole_id = ac.ecole_id AND pc.annee_scolaire_id = ac.annee_scolaire_id
              AND pc.section_id = c.section_id AND pc.cours_id = ac.cours_id
             LEFT JOIN programmes_cours_classes pcc
               ON pcc.programme_id = pc.id AND pcc.classe_id = c.id
             WHERE h.ecole_id = :ecole_id AND h.annee_scolaire_id = :annee_scolaire_id
               AND h.agent_id = :agent_id AND h.jour_id = :jour_id'
        );
        $teacherConflict->execute([
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':agent_id' => (int) $assignment['agent_id'],
            ':jour_id' => $dayId,
        ]);
        foreach ($teacherConflict->fetchAll(PDO::FETCH_ASSOC) as $conflict) {
            if ($programId > 0 && (int) ($conflict['programme_id'] ?? 0) === $programId) {
                continue;
            }
            $otherSchedule = self::getDailySchedule(
                self::getHourDuration($schoolId, (int) $conflict['section_id'])
            );
            $otherTime = $otherSchedule['hours'][(int) $conflict['periode']];
            if ($candidateStart < (int) $otherTime['end'] && $candidateEnd > (int) $otherTime['start']) {
                throw new \InvalidArgumentException('Cet enseignant est déjà en cours à cette heure dans une autre classe.');
            }
        }

        $save = $db->prepare(
            'INSERT INTO horaires_cours
                (ecole_id, annee_scolaire_id, attribution_cours_id, classe_id, cours_id, agent_id, jour_id, periode)
             VALUES (:ecole_id, :annee_scolaire_id, :attribution_id, :classe_id, :cours_id, :agent_id, :jour_id, :periode)
             ON DUPLICATE KEY UPDATE
                attribution_cours_id = VALUES(attribution_cours_id),
                cours_id = VALUES(cours_id), agent_id = VALUES(agent_id)'
        );
        $db->beginTransaction();
        try {
            foreach ($targetAssignments as $targetAssignment) {
                $save->execute([
                    ':ecole_id' => $schoolId,
                    ':annee_scolaire_id' => $yearId,
                    ':attribution_id' => (int) $targetAssignment['id'],
                    ':classe_id' => (int) $targetAssignment['classe_id'],
                    ':cours_id' => (int) $targetAssignment['cours_id'],
                    ':agent_id' => (int) $targetAssignment['agent_id'],
                    ':jour_id' => $dayId,
                    ':periode' => $period,
                ]);
            }
            $db->commit();
        } catch (\PDOException $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new \InvalidArgumentException('La classe ou l’enseignant est déjà occupé à cette heure.');
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        self::ensureTeacherIndex($schoolId, $yearId, (int) $assignment['agent_id']);
    }

    public static function deleteSlot(int $schoolId, int $yearId, int $sectionId, int $slotId): bool
    {
        self::ensureTables();
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT h.jour_id, h.periode,
                    CASE WHEN pcc.classe_id IS NOT NULL THEN pc.id ELSE NULL END AS programme_id,
                    pc.nature_cours
             FROM horaires_cours h
             INNER JOIN classes c ON c.id = h.classe_id AND c.ecole_id = h.ecole_id
             LEFT JOIN attribution_cours ac ON ac.id = h.attribution_cours_id
             LEFT JOIN programmes_cours pc
               ON pc.ecole_id = ac.ecole_id AND pc.annee_scolaire_id = ac.annee_scolaire_id
              AND pc.section_id = c.section_id AND pc.cours_id = ac.cours_id
             LEFT JOIN programmes_cours_classes pcc
               ON pcc.programme_id = pc.id AND pcc.classe_id = c.id
             WHERE h.id = :id AND h.ecole_id = :ecole_id
               AND h.annee_scolaire_id = :annee_scolaire_id AND c.section_id = :section_id
             LIMIT 1'
        );
        $stmt->execute([
            ':id' => $slotId,
            ':ecole_id' => $schoolId,
            ':annee_scolaire_id' => $yearId,
            ':section_id' => $sectionId,
        ]);
        $slot = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$slot) {
            return false;
        }
        if ($slot['nature_cours'] === 'commun' && !empty($slot['programme_id'])) {
            $delete = $db->prepare(
                'DELETE h FROM horaires_cours h
                 INNER JOIN programmes_cours_classes pcc
                   ON pcc.programme_id = :programme_id AND pcc.classe_id = h.classe_id
                 INNER JOIN attribution_cours ac ON ac.id = h.attribution_cours_id
                 WHERE h.ecole_id = :ecole_id AND h.annee_scolaire_id = :annee_scolaire_id
                   AND h.jour_id = :jour_id AND h.periode = :periode
                   AND ac.cours_id = (SELECT cours_id FROM programmes_cours WHERE id = :program_id)'
            );
            $delete->execute([
                ':programme_id' => (int) $slot['programme_id'],
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':jour_id' => (int) $slot['jour_id'],
                ':periode' => (int) $slot['periode'],
                ':program_id' => (int) $slot['programme_id'],
            ]);
        } else {
            $delete = $db->prepare(
                'DELETE FROM horaires_cours
                 WHERE id = :id AND ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id'
            );
            $delete->execute([
                ':id' => $slotId,
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
            ]);
        }

        return $delete->rowCount() > 0;
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
                throw new \InvalidArgumentException('Cet enseignant a droit à une journée libre, car sa charge dépasse 22 heures.');
            }
            $delete = Database::getConnection()->prepare(
                'DELETE FROM jours_libres_enseignants
                 WHERE ecole_id = :ecole_id AND annee_scolaire_id = :annee_scolaire_id AND agent_id = :agent_id'
            );
            $delete->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);
            return;
        }
        if (self::getWeeklyWorkload($schoolId, $yearId, $agentId) <= 22) {
            throw new \InvalidArgumentException('La journée hebdomadaire est réservée aux enseignants dépassant 22 heures.');
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
        $insert = $db->prepare(
            'INSERT IGNORE INTO indices_enseignants
                (ecole_id, annee_scolaire_id, agent_id, indice)
             VALUES (:ecole_id, :annee_scolaire_id, :agent_id, :indice)'
        );
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $next->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId]);
            $index = (int) $next->fetchColumn();
            $insert->execute([
                ':ecole_id' => $schoolId,
                ':annee_scolaire_id' => $yearId,
                ':agent_id' => $agentId,
                ':indice' => $index,
            ]);
            $find->execute([':ecole_id' => $schoolId, ':annee_scolaire_id' => $yearId, ':agent_id' => $agentId]);
            $existing = $find->fetchColumn();
            if ($existing !== false) {
                return (int) $existing;
            }
        }

        throw new \RuntimeException('Impossible de générer un indice unique pour cet enseignant.');
    }
}
