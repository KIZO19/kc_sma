<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class DisciplineAudition
{
    public static function create(array $data): int
    {
        $values = self::validate($data);
        $db = Database::getConnection();
        $incidentStmt = $db->prepare(
            'SELECT d.id
             FROM discipline_eleves d
             INNER JOIN eleves e ON e.id = d.eleve_id
             WHERE d.id = :incident_id
               AND e.id = :student_id
               AND (
                 e.ecole_id = :school_id
                 OR EXISTS (
                   SELECT 1 FROM inscriptions i
                   INNER JOIN classes c ON c.id = i.classe_id
                   WHERE i.eleve_id = e.id AND c.ecole_id = :enrollment_school_id
                 )
               )
             LIMIT 1'
        );
        $incidentStmt->execute([
            ':incident_id' => $values['discipline_id'],
            ':student_id' => $values['student_id'],
            ':school_id' => $values['school_id'],
            ':enrollment_school_id' => $values['school_id'],
        ]);
        if (!$incidentStmt->fetchColumn()) {
            throw new \DomainException('Cet incident ne relève pas de l’élève ou de l’école sélectionnée.');
        }

        $insert = $db->prepare(
            'INSERT INTO discipline_auditions (
                discipline_id, school_id, student_id, created_by, hearing_at, location,
                alleged_facts, student_statement, observations, decision,
                parent_meeting_at, parent_meeting_location, parent_notice
             ) VALUES (
                :discipline_id, :school_id, :student_id, :created_by, :hearing_at, :location,
                :alleged_facts, :student_statement, :observations, :decision,
                :parent_meeting_at, :parent_meeting_location, :parent_notice
             )'
        );
        $insert->execute([
            ':discipline_id' => $values['discipline_id'],
            ':school_id' => $values['school_id'],
            ':student_id' => $values['student_id'],
            ':created_by' => $values['created_by'],
            ':hearing_at' => $values['hearing_at'],
            ':location' => $values['location'],
            ':alleged_facts' => $values['alleged_facts'],
            ':student_statement' => $values['student_statement'],
            ':observations' => $values['observations'],
            ':decision' => $values['decision'],
            ':parent_meeting_at' => $values['parent_meeting_at'],
            ':parent_meeting_location' => $values['parent_meeting_location'],
            ':parent_notice' => $values['parent_notice'],
        ]);

        return (int) $db->lastInsertId();
    }

    public static function getForDisciplineEntries(array $disciplineIds, int $schoolId): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $disciplineIds),
            static fn (int $id): bool => $id > 0
        )));
        if ($ids === [] || $schoolId <= 0) {
            return [];
        }

        $placeholders = [];
        $params = [':school_id' => $schoolId];
        foreach ($ids as $index => $id) {
            $key = ':discipline_' . $index;
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT a.*, u.nom_complet AS author_name
             FROM discipline_auditions a
             LEFT JOIN utilisateurs u ON u.id = a.created_by
             WHERE a.school_id = :school_id
               AND a.discipline_id IN (' . implode(', ', $placeholders) . ')
             ORDER BY a.hearing_at DESC, a.id DESC'
        );
        $stmt->execute($params);

        $byIncident = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $audition) {
            $byIncident[(int) $audition['discipline_id']][] = $audition;
        }

        return $byIncident;
    }

    public static function findByIdAndSchool(int $id, int $schoolId, ?int $sectionId = null): ?array
    {
        if ($id <= 0 || $schoolId <= 0) {
            return null;
        }

        $sectionSql = '';
        $params = [':id' => $id, ':school_id' => $schoolId];
        if ($sectionId !== null) {
            $sectionSql = ' AND EXISTS (
                SELECT 1 FROM inscriptions scope_i
                INNER JOIN classes scope_c ON scope_c.id = scope_i.classe_id
                WHERE scope_i.eleve_id = e.id
                  AND scope_c.ecole_id = :section_school
                  AND scope_c.section_id = :section_id
            )';
            $params[':section_school'] = $schoolId;
            $params[':section_id'] = $sectionId;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT a.*, d.faute, d.sanction, d.retrait_points, d.date_evenement,
                    p.nom_periode, an.annee,
                    e.nom, e.postnom, e.prenom, e.matricule,
                    pa.nom_responsable AS parent_name, pa.telephone AS parent_phone, pa.email AS parent_email,
                    ec.nom_etablissement AS school_name,
                    u.nom_complet AS author_name,
                    c.nom_classe, o.nom_option
             FROM discipline_auditions a
             INNER JOIN discipline_eleves d ON d.id = a.discipline_id
             INNER JOIN eleves e ON e.id = a.student_id
             LEFT JOIN periodes p ON p.id = d.periode_id
             LEFT JOIN annees_scolaires an ON an.id = p.annee_scolaire_id
             LEFT JOIN parents pa ON pa.id = e.parent_id AND pa.ecole_id = a.school_id
             LEFT JOIN ecoles ec ON ec.id = a.school_id
             LEFT JOIN utilisateurs u ON u.id = a.created_by
             LEFT JOIN inscriptions i ON i.id = (
                 SELECT latest_i.id FROM inscriptions latest_i
                 INNER JOIN classes latest_c ON latest_c.id = latest_i.classe_id
                 WHERE latest_i.eleve_id = e.id AND latest_c.ecole_id = a.school_id
                 ORDER BY latest_i.date_inscription DESC, latest_i.id DESC
                 LIMIT 1
             )
             LEFT JOIN classes c ON c.id = i.classe_id
             LEFT JOIN options o ON o.id = c.option_id
             WHERE a.id = :id AND a.school_id = :school_id' . $sectionSql . '
             LIMIT 1'
        );
        $stmt->execute($params);
        $audition = $stmt->fetch(PDO::FETCH_ASSOC);

        return $audition ?: null;
    }

    public static function recordDelivery(
        int $id,
        string $channel,
        string $status,
        string $details,
        ?array $recipientUserIds = null
    ): void {
        $columns = [
            'email' => 'email',
            'sms' => 'sms',
            'report' => 'report',
        ];
        if (!isset($columns[$channel])) {
            throw new \InvalidArgumentException('Canal de transmission invalide.');
        }

        $allowedStatuses = $channel === 'report'
            ? ['sent', 'partial', 'failed', 'no_recipients']
            : ['sent', 'failed', 'skipped'];
        if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
            throw new \InvalidArgumentException('État de transmission invalide.');
        }

        $column = $columns[$channel];
        $sql = 'UPDATE discipline_auditions SET '
            . $column . '_status = :status, '
            . $column . '_details = :details';
        $params = [
            ':status' => $status,
            ':details' => mb_substr($details, 0, 4000, 'UTF-8'),
            ':id' => $id,
        ];
        if ($channel === 'report' && in_array($status, ['sent', 'partial'], true)) {
            $sql .= ', report_sent_at = NOW()';
        }
        if ($channel === 'report' && $recipientUserIds !== null) {
            $ids = array_values(array_unique(array_filter(
                array_map('intval', $recipientUserIds),
                static fn (int $userId): bool => $userId > 0
            )));
            $sql .= ', recipient_user_ids = :recipient_user_ids';
            $params[':recipient_user_ids'] = json_encode($ids, JSON_THROW_ON_ERROR);
        }
        $sql .= ' WHERE id = :id';
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
    }

    public static function getReportRecipientIds(int $id): array
    {
        if ($id <= 0) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT recipient_user_ids FROM discipline_auditions WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $value = $stmt->fetchColumn();
        if (!is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $decoded),
            static fn (int $userId): bool => $userId > 0
        )));
    }

    private static function validate(array $data): array
    {
        $dateFields = ['hearing_at', 'parent_meeting_at'];
        $textFields = [
            'location',
            'parent_meeting_location',
            'alleged_facts',
            'student_statement',
            'observations',
            'decision',
            'parent_notice',
        ];
        $values = [];
        foreach ($dateFields as $field) {
            $value = $data[$field] ?? null;
            if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
                throw new \InvalidArgumentException('Saisissez une date et une heure valides pour l’audition et la convocation.');
            }
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
            if (!$parsed || $parsed->format('Y-m-d\TH:i') !== $value) {
                throw new \InvalidArgumentException('Une date de l’audition ou de convocation est invalide.');
            }
            $values[$field] = $parsed->format('Y-m-d H:i:s');
        }

        foreach ($textFields as $field) {
            $value = $data[$field] ?? '';
            if (!is_scalar($value)) {
                throw new \InvalidArgumentException('Les champs du procès-verbal sont invalides.');
            }
            $value = trim((string) $value);
            $maxLength = in_array($field, ['location', 'parent_meeting_location'], true) ? 255 : 20000;
            if (mb_strlen($value, 'UTF-8') > $maxLength) {
                throw new \InvalidArgumentException('Un champ du procès-verbal dépasse la longueur maximale.');
            }
            if (in_array($field, ['alleged_facts', 'student_statement'], true) && $value === '') {
                throw new \InvalidArgumentException('Les faits reprochés et la déclaration de l’élève sont obligatoires.');
            }
            $values[$field] = $value !== '' ? $value : null;
        }

        foreach (['discipline_id', 'school_id', 'student_id', 'created_by'] as $field) {
            $value = filter_var($data[$field] ?? null, FILTER_VALIDATE_INT);
            if ($value === false || $value <= 0) {
                throw new \InvalidArgumentException('La référence au dossier disciplinaire est invalide.');
            }
            $values[$field] = $value;
        }

        return $values;
    }
}
