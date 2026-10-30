<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SchoolMessage
{
    private const PARENT_ROLES = ['parent_ecole', 'parent_école'];
    private const STAFF_ROLES = [
        'ecole_admin',
        'promoteur_école',
        'préfet_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
        'comptable_école',
        'sec_école',
    ];

    private static bool $tablesEnsured = false;

    public static function getChildrenForParent(int $parentId, int $schoolId): array
    {
        if ($parentId <= 0 || $schoolId <= 0) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT id, nom, postnom, prenom, matricule
             FROM eleves
             WHERE parent_id = :parent_id AND ecole_id = :ecole_id
             ORDER BY nom, postnom, prenom'
        );
        $stmt->execute([':parent_id' => $parentId, ':ecole_id' => $schoolId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getParentContacts(int $parentId, int $schoolId): array
    {
        if ($parentId <= 0 || $schoolId <= 0) {
            return [];
        }

        self::ensureTables();
        $recipientIds = self::getParentRecipientUserIds($schoolId);
        if ($recipientIds === []) {
            return [];
        }

        $roles = self::rolePlaceholders('staff_role_', self::STAFF_ROLES, $params);
        $params[':parent_id'] = $parentId;
        $params[':ecole_id'] = $schoolId;
        $recipientPlaceholders = [];
        foreach (array_values($recipientIds) as $index => $recipientId) {
            $key = ':recipient_' . $index;
            $recipientPlaceholders[] = $key;
            $params[$key] = $recipientId;
        }
        $stmt = Database::getConnection()->prepare(
            'SELECT DISTINCT u.id, u.nom_complet, u.role
             FROM utilisateurs u
             INNER JOIN eleves e ON e.ecole_id = u.ecole_id
             WHERE e.parent_id = :parent_id AND u.ecole_id = :ecole_id
               AND u.statut = \'Actif\' AND u.role IN (' . $roles . ')
               AND u.id IN (' . implode(', ', $recipientPlaceholders) . ')
             ORDER BY u.nom_complet'
        );
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getParentRecipientOptions(int $schoolId): array
    {
        if ($schoolId <= 0) {
            return [];
        }

        self::ensureTables();
        $roles = self::rolePlaceholders('staff_role_', self::STAFF_ROLES, $params);
        $params[':ecole_id'] = $schoolId;
        $stmt = Database::getConnection()->prepare(
            'SELECT id, nom_complet, role FROM utilisateurs
             WHERE ecole_id = :ecole_id AND statut = \'Actif\'
               AND role IN (' . $roles . ')
             ORDER BY role ASC, nom_complet ASC'
        );
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getParentRecipientUserIds(int $schoolId): array
    {
        if ($schoolId <= 0) {
            return [];
        }

        self::ensureTables();
        $db = Database::getConnection();
        $configured = $db->prepare(
            'SELECT 1 FROM ecole_school_message_recipient_settings WHERE ecole_id = :ecole_id'
        );
        $configured->execute([':ecole_id' => $schoolId]);
        if (!$configured->fetchColumn()) {
            return array_map(
                static fn (array $recipient): int => (int) $recipient['id'],
                self::getParentRecipientOptions($schoolId)
            );
        }

        $stmt = $db->prepare(
            'SELECT r.utilisateur_id
             FROM ecole_school_message_recipients r
             INNER JOIN utilisateurs u ON u.id = r.utilisateur_id AND u.ecole_id = r.ecole_id
             WHERE r.ecole_id = :ecole_id AND u.statut = \'Actif\'
             ORDER BY r.utilisateur_id ASC'
        );
        $stmt->execute([':ecole_id' => $schoolId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function saveParentRecipientUserIds(int $schoolId, array $userIds, int $updatedBy): bool
    {
        if ($schoolId <= 0 || $updatedBy <= 0) {
            throw new \DomainException('École ou administrateur invalide.');
        }

        $userIds = array_values(array_unique(array_filter(
            array_map('intval', $userIds),
            static fn (int $id): bool => $id > 0
        )));
        if ($userIds === []) {
            throw new \DomainException('Sélectionnez au moins une autorité scolaire.');
        }

        $optionsById = [];
        foreach (self::getParentRecipientOptions($schoolId) as $option) {
            $optionsById[(int) $option['id']] = true;
        }
        foreach ($userIds as $userId) {
            if (!isset($optionsById[$userId])) {
                throw new \DomainException('Une des autorités sélectionnées n’est pas un compte actif autorisé de cette école.');
            }
        }

        self::ensureTables();
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $setting = $db->prepare(
                'INSERT INTO ecole_school_message_recipient_settings (ecole_id, updated_by_user_id)
                 VALUES (:ecole_id, :updated_by)
                 ON DUPLICATE KEY UPDATE updated_by_user_id = VALUES(updated_by_user_id),
                    updated_at = CURRENT_TIMESTAMP'
            );
            $setting->execute([':ecole_id' => $schoolId, ':updated_by' => $updatedBy]);
            $delete = $db->prepare(
                'DELETE FROM ecole_school_message_recipients WHERE ecole_id = :ecole_id'
            );
            $delete->execute([':ecole_id' => $schoolId]);
            $insert = $db->prepare(
                'INSERT INTO ecole_school_message_recipients (ecole_id, utilisateur_id)
                 VALUES (:ecole_id, :utilisateur_id)'
            );
            foreach ($userIds as $userId) {
                $insert->execute([':ecole_id' => $schoolId, ':utilisateur_id' => $userId]);
            }
            $db->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    public static function getStaffParentContacts(int $schoolId, ?int $sectionId = null): array
    {
        if ($schoolId <= 0) {
            return [];
        }

        self::ensureTables();
        $roles = self::rolePlaceholders('parent_role_', self::PARENT_ROLES, $params);
        $params[':ecole_id'] = $schoolId;
        $sectionSql = '';
        if ($sectionId !== null) {
            $sectionSql = ' AND EXISTS (
                SELECT 1 FROM inscriptions i
                INNER JOIN classes c ON c.id = i.classe_id
                WHERE i.eleve_id = e.id AND c.ecole_id = :section_school
                  AND c.section_id = :section_id
            )';
            $params[':section_school'] = $schoolId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = Database::getConnection()->prepare(
            'SELECT DISTINCT u.id AS parent_user_id, u.nom_complet AS parent_nom,
                    e.id AS eleve_id, e.nom, e.postnom, e.prenom, e.matricule
             FROM eleves e
             INNER JOIN utilisateurs u ON u.reference_id = e.parent_id
               AND u.ecole_id = e.ecole_id AND u.statut = \'Actif\'
             WHERE e.ecole_id = :ecole_id AND u.role IN (' . $roles . ')' . $sectionSql . '
             ORDER BY u.nom_complet, e.nom, e.postnom, e.prenom'
        );
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getThreadsForUser(int $userId, int $schoolId): array
    {
        if ($userId <= 0 || $schoolId <= 0) {
            return [];
        }

        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT t.id, t.student_id, t.subject, t.updated_at,
                    CASE WHEN t.parent_user_id = :current_user THEN staff.nom_complet ELSE parent.nom_complet END AS other_name,
                    CONCAT_WS(\' \', e.nom, e.postnom, e.prenom) AS student_name,
                    (SELECT m.body FROM school_messages m WHERE m.thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS last_body,
                    (SELECT m.created_at FROM school_messages m WHERE m.thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS last_message_at,
                    (SELECT COUNT(*) FROM school_messages unread
                     WHERE unread.thread_id = t.id AND unread.recipient_user_id = :unread_user
                       AND unread.read_at IS NULL) AS unread_count
             FROM school_message_threads t
             INNER JOIN utilisateurs parent ON parent.id = t.parent_user_id
             INNER JOIN utilisateurs staff ON staff.id = t.staff_user_id
             INNER JOIN eleves e ON e.id = t.student_id
             WHERE t.school_id = :school_id
               AND (t.parent_user_id = :parent_user OR t.staff_user_id = :staff_user)
             ORDER BY t.updated_at DESC, t.id DESC'
        );
        $stmt->execute([
            ':current_user' => $userId,
            ':unread_user' => $userId,
            ':parent_user' => $userId,
            ':staff_user' => $userId,
            ':school_id' => $schoolId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getThread(int $threadId, int $userId, int $schoolId): ?array
    {
        if ($threadId <= 0 || $userId <= 0 || $schoolId <= 0) {
            return null;
        }

        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT t.*, parent.nom_complet AS parent_name, staff.nom_complet AS staff_name,
                    CONCAT_WS(\' \', e.nom, e.postnom, e.prenom) AS student_name
             FROM school_message_threads t
             INNER JOIN utilisateurs parent ON parent.id = t.parent_user_id
             INNER JOIN utilisateurs staff ON staff.id = t.staff_user_id
             INNER JOIN eleves e ON e.id = t.student_id
             WHERE t.id = :id AND t.school_id = :school_id
               AND (t.parent_user_id = :parent_user OR t.staff_user_id = :staff_user)
             LIMIT 1'
        );
        $stmt->execute([
            ':id' => $threadId,
            ':school_id' => $schoolId,
            ':parent_user' => $userId,
            ':staff_user' => $userId,
        ]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$thread) {
            return null;
        }

        $markRead = Database::getConnection()->prepare(
            'UPDATE school_messages SET read_at = NOW()
             WHERE thread_id = :thread_id AND recipient_user_id = :user_id AND read_at IS NULL'
        );
        $markRead->execute([':thread_id' => $threadId, ':user_id' => $userId]);

        $messagesStmt = Database::getConnection()->prepare(
            'SELECT m.id, m.sender_user_id, m.body, m.created_at, u.nom_complet AS sender_name
             FROM school_messages m
             INNER JOIN utilisateurs u ON u.id = m.sender_user_id
             WHERE m.thread_id = :thread_id
             ORDER BY m.id ASC'
        );
        $messagesStmt->execute([':thread_id' => $threadId]);
        $thread['messages'] = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

        return $thread;
    }

    public static function getMessagesAfter(int $threadId, int $userId, int $schoolId, int $afterId): ?array
    {
        if ($threadId <= 0 || $userId <= 0 || $schoolId <= 0 || $afterId < 0) {
            return null;
        }

        self::ensureTables();
        $db = Database::getConnection();
        $access = $db->prepare(
            'SELECT parent_user_id, staff_user_id FROM school_message_threads
             WHERE id = :id AND school_id = :school_id
               AND (parent_user_id = :parent_user OR staff_user_id = :staff_user)
             LIMIT 1'
        );
        $access->execute([
            ':id' => $threadId,
            ':school_id' => $schoolId,
            ':parent_user' => $userId,
            ':staff_user' => $userId,
        ]);
        $thread = $access->fetch(PDO::FETCH_ASSOC);
        if (!$thread) {
            return null;
        }

        $messagesStmt = $db->prepare(
            'SELECT m.id, m.sender_user_id, m.body, m.created_at, u.nom_complet AS sender_name
             FROM school_messages m
             INNER JOIN utilisateurs u ON u.id = m.sender_user_id
             WHERE m.thread_id = :thread_id AND m.id > :after_id
             ORDER BY m.id ASC LIMIT 100'
        );
        $messagesStmt->execute([':thread_id' => $threadId, ':after_id' => $afterId]);
        $messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);
        if ($messages) {
            $lastId = (int) $messages[count($messages) - 1]['id'];
            $markRead = $db->prepare(
                'UPDATE school_messages SET read_at = NOW()
                 WHERE thread_id = :thread_id AND recipient_user_id = :user_id
                   AND id > :after_id AND id <= :last_id AND read_at IS NULL'
            );
            $markRead->execute([
                ':thread_id' => $threadId,
                ':user_id' => $userId,
                ':after_id' => $afterId,
                ':last_id' => $lastId,
            ]);
        }

        $typingStmt = $db->prepare(
            'SELECT u.nom_complet
             FROM school_message_typing typing
             INNER JOIN utilisateurs u ON u.id = typing.user_id
             WHERE typing.thread_id = :thread_id AND typing.user_id <> :user_id
               AND typing.typing_until > NOW()
             LIMIT 1'
        );
        $typingStmt->execute([':thread_id' => $threadId, ':user_id' => $userId]);

        return [
            'messages' => $messages,
            'typingName' => $typingStmt->fetchColumn() ?: null,
        ];
    }

    public static function setTyping(int $threadId, int $userId, int $schoolId, bool $isTyping): bool
    {
        if ($threadId <= 0 || $userId <= 0 || $schoolId <= 0) {
            return false;
        }

        self::ensureTables();
        $db = Database::getConnection();
        $access = $db->prepare(
            'SELECT 1 FROM school_message_threads
             WHERE id = :id AND school_id = :school_id
               AND (parent_user_id = :parent_user OR staff_user_id = :staff_user)
             LIMIT 1'
        );
        $access->execute([
            ':id' => $threadId,
            ':school_id' => $schoolId,
            ':parent_user' => $userId,
            ':staff_user' => $userId,
        ]);
        if (!$access->fetchColumn()) {
            return false;
        }

        if ($isTyping) {
            $stmt = $db->prepare(
                'INSERT INTO school_message_typing (thread_id, user_id, typing_until)
                 VALUES (:thread_id, :user_id, DATE_ADD(NOW(), INTERVAL 4 SECOND))
                 ON DUPLICATE KEY UPDATE typing_until = DATE_ADD(NOW(), INTERVAL 4 SECOND)'
            );
        } else {
            $stmt = $db->prepare(
                'DELETE FROM school_message_typing WHERE thread_id = :thread_id AND user_id = :user_id'
            );
        }
        $stmt->execute([':thread_id' => $threadId, ':user_id' => $userId]);

        return true;
    }

    public static function createThread(
        array $sender,
        int $recipientUserId,
        int $studentId,
        string $subject,
        string $body,
        ?int $sectionId = null
    ): int {
        self::ensureTables();
        $senderId = (int) ($sender['id'] ?? 0);
        $schoolId = (int) ($sender['ecole_id'] ?? 0);
        $senderRole = (string) ($sender['role'] ?? '');
        $student = self::findStudent($studentId, $schoolId, $sectionId);
        if ($senderId <= 0 || $schoolId <= 0 || !$student || $recipientUserId <= 0) {
            throw new \InvalidArgumentException('Le destinataire ou l’élève sélectionné est invalide pour cette école.');
        }

        $parentUserId = 0;
        $staffUserId = 0;
        if (in_array($senderRole, self::PARENT_ROLES, true)) {
            if ((int) ($student['parent_id'] ?? 0) !== (int) ($sender['reference_id'] ?? 0)
                || !self::userHasRoleInSchool($recipientUserId, self::STAFF_ROLES, $schoolId)
                || !in_array($recipientUserId, self::getParentRecipientUserIds($schoolId), true)) {
                throw new \InvalidArgumentException('Vous ne pouvez écrire qu’à un responsable de l’école de votre enfant.');
            }
            $parentUserId = $senderId;
            $staffUserId = $recipientUserId;
        } elseif (in_array($senderRole, self::STAFF_ROLES, true)) {
            if (!self::userHasRoleInSchool($recipientUserId, self::PARENT_ROLES, $schoolId)
                || !self::parentOwnsStudent($recipientUserId, $studentId, $schoolId)) {
                throw new \InvalidArgumentException('Le parent choisi n’est pas associé à cet élève dans votre école.');
            }
            $parentUserId = $recipientUserId;
            $staffUserId = $senderId;
        } else {
            throw new \InvalidArgumentException('Votre rôle ne permet pas de démarrer une conversation école-parent.');
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $threadStmt = $db->prepare(
                'INSERT INTO school_message_threads
                    (school_id, student_id, parent_user_id, staff_user_id, subject)
                 VALUES (:school_id, :student_id, :parent_user_id, :staff_user_id, :subject)
                 ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), updated_at = NOW()'
            );
            $threadStmt->execute([
                ':school_id' => $schoolId,
                ':student_id' => $studentId,
                ':parent_user_id' => $parentUserId,
                ':staff_user_id' => $staffUserId,
                ':subject' => $subject,
            ]);
            $threadId = (int) $db->lastInsertId();
            if ($threadId <= 0) {
                throw new \RuntimeException('Impossible de créer la conversation.');
            }
            self::insertMessage($db, $threadId, $senderId, $senderId === $parentUserId ? $staffUserId : $parentUserId, $body);
            $db->commit();

            return $threadId;
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    public static function reply(int $threadId, array $sender, string $body): bool
    {
        self::ensureTables();
        $senderId = (int) ($sender['id'] ?? 0);
        $stmt = Database::getConnection()->prepare(
            'SELECT parent_user_id, staff_user_id, school_id
             FROM school_message_threads WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $threadId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$thread || !in_array($senderId, [(int) $thread['parent_user_id'], (int) $thread['staff_user_id']], true)
            || (int) ($sender['ecole_id'] ?? 0) !== (int) $thread['school_id']) {
            return false;
        }

        $recipientId = $senderId === (int) $thread['parent_user_id']
            ? (int) $thread['staff_user_id']
            : (int) $thread['parent_user_id'];
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            self::insertMessage($db, $threadId, $senderId, $recipientId, $body);
            $update = $db->prepare('UPDATE school_message_threads SET updated_at = NOW() WHERE id = :id');
            $update->execute([':id' => $threadId]);
            $db->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    public static function getUnreadForUser(int $userId, int $schoolId, int $limit = 10): array
    {
        if ($userId <= 0 || $schoolId <= 0) {
            return [];
        }
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT m.id, t.id AS thread_id, t.subject, m.body, m.created_at,
                    sender.nom_complet AS sender_name
             FROM school_messages m
             INNER JOIN school_message_threads t ON t.id = m.thread_id
             INNER JOIN utilisateurs sender ON sender.id = m.sender_user_id
             WHERE m.recipient_user_id = :user_id AND t.school_id = :school_id AND m.read_at IS NULL
             ORDER BY m.id DESC LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':school_id', $schoolId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(20, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countUnreadForUser(int $userId, int $schoolId): int
    {
        if ($userId <= 0 || $schoolId <= 0) {
            return 0;
        }
        self::ensureTables();
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) FROM school_messages m
             INNER JOIN school_message_threads t ON t.id = m.thread_id
             WHERE m.recipient_user_id = :user_id AND t.school_id = :school_id AND m.read_at IS NULL'
        );
        $stmt->execute([':user_id' => $userId, ':school_id' => $schoolId]);

        return (int) $stmt->fetchColumn();
    }

    public static function isStaffRole(string $role): bool
    {
        return in_array($role, self::STAFF_ROLES, true);
    }

    private static function findStudent(int $studentId, int $schoolId, ?int $sectionId): ?array
    {
        $sql = 'SELECT id, parent_id, ecole_id FROM eleves WHERE id = :id AND ecole_id = :ecole_id';
        $params = [':id' => $studentId, ':ecole_id' => $schoolId];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (
                SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id
                WHERE i.eleve_id = eleves.id AND c.ecole_id = :section_school AND c.section_id = :section_id
            )';
            $params[':section_school'] = $schoolId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = Database::getConnection()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        return $student ?: null;
    }

    private static function parentOwnsStudent(int $parentUserId, int $studentId, int $schoolId): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT 1 FROM utilisateurs u
             INNER JOIN eleves e ON e.parent_id = u.reference_id AND e.ecole_id = u.ecole_id
             WHERE u.id = :user_id AND u.ecole_id = :ecole_id AND u.role IN (\'parent_ecole\', \'parent_école\')
               AND u.statut = \'Actif\' AND e.id = :student_id LIMIT 1'
        );
        $stmt->execute([':user_id' => $parentUserId, ':ecole_id' => $schoolId, ':student_id' => $studentId]);

        return (bool) $stmt->fetchColumn();
    }

    private static function userHasRoleInSchool(int $userId, array $roles, int $schoolId): bool
    {
        $placeholders = self::rolePlaceholders('role_', $roles, $params);
        $params[':user_id'] = $userId;
        $params[':ecole_id'] = $schoolId;
        $stmt = Database::getConnection()->prepare(
            'SELECT 1 FROM utilisateurs WHERE id = :user_id AND ecole_id = :ecole_id
             AND statut = \'Actif\' AND role IN (' . $placeholders . ') LIMIT 1'
        );
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    private static function rolePlaceholders(string $prefix, array $roles, ?array &$params): string
    {
        $params = [];
        $placeholders = [];
        foreach (array_values($roles) as $index => $role) {
            $key = ':' . $prefix . $index;
            $placeholders[] = $key;
            $params[$key] = $role;
        }

        return implode(', ', $placeholders);
    }

    private static function insertMessage(PDO $db, int $threadId, int $senderId, int $recipientId, string $body): void
    {
        $stmt = $db->prepare(
            'INSERT INTO school_messages (thread_id, sender_user_id, recipient_user_id, body)
             VALUES (:thread_id, :sender_user_id, :recipient_user_id, :body)'
        );
        $stmt->execute([
            ':thread_id' => $threadId,
            ':sender_user_id' => $senderId,
            ':recipient_user_id' => $recipientId,
            ':body' => $body,
        ]);
    }

    private static function ensureTables(): void
    {
        if (self::$tablesEnsured) {
            return;
        }

        $db = Database::getConnection();
        $db->exec(
            'CREATE TABLE IF NOT EXISTS school_message_threads (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                school_id INT NOT NULL,
                student_id INT NOT NULL,
                parent_user_id INT NOT NULL,
                staff_user_id INT NOT NULL,
                subject VARCHAR(160) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_school_message_thread (school_id, student_id, parent_user_id, staff_user_id),
                KEY idx_school_message_parent (parent_user_id, updated_at),
                KEY idx_school_message_staff (staff_user_id, updated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS school_messages (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                thread_id BIGINT UNSIGNED NOT NULL,
                sender_user_id INT NOT NULL,
                recipient_user_id INT NOT NULL,
                body TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                read_at TIMESTAMP NULL DEFAULT NULL,
                KEY idx_school_messages_recipient_unread (recipient_user_id, read_at, id),
                KEY idx_school_messages_thread (thread_id, id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS school_message_typing (
                thread_id BIGINT UNSIGNED NOT NULL,
                user_id INT NOT NULL,
                typing_until DATETIME NOT NULL,
                PRIMARY KEY (thread_id, user_id),
                KEY idx_school_message_typing_expiry (typing_until)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS ecole_school_message_recipient_settings (
                ecole_id INT NOT NULL PRIMARY KEY,
                updated_by_user_id INT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_school_message_recipient_settings_updated_by (updated_by_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $db->exec(
            'CREATE TABLE IF NOT EXISTS ecole_school_message_recipients (
                ecole_id INT NOT NULL,
                utilisateur_id INT NOT NULL,
                PRIMARY KEY (ecole_id, utilisateur_id),
                KEY idx_school_message_recipients_user (utilisateur_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tablesEnsured = true;
    }
}
