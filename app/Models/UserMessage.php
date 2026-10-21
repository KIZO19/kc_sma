<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class UserMessage
{
    private static bool $tableEnsured = false;

    public static function notifyParentForStudent(
        int $studentId,
        string $type,
        int $referenceId,
        string $title,
        string $body
    ): void {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            "SELECT u.id
             FROM eleves e
             INNER JOIN utilisateurs u ON u.reference_id = e.parent_id
                AND u.role = 'parent_ecole' AND u.ecole_id = e.ecole_id
             WHERE e.id = :eleve_id AND u.statut = 'Actif'"
        );
        $stmt->execute([':eleve_id' => $studentId]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $userId) {
            self::create((int) $userId, $type, $referenceId, $title, $body);
        }
    }

    public static function notifyCommunique(
        int $schoolId,
        int $communiqueId,
        string $title,
        string $body,
        array $roles,
        array $sectionIds
    ): void {
        self::ensureTable();
        if ($schoolId <= 0 || $communiqueId <= 0 || $roles === []) {
            return;
        }

        $placeholders = [];
        $params = [':ecole_id' => $schoolId];
        foreach (array_values($roles) as $index => $role) {
            $key = ':role_' . $index;
            $placeholders[] = $key;
            $params[$key] = $role;
        }
        $stmt = Database::getConnection()->prepare(
            'SELECT id, role, reference_id, section_id
             FROM utilisateurs
             WHERE ecole_id = :ecole_id AND statut = \'Actif\'
               AND role IN (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($params);
        $selectedSections = array_fill_keys(array_map('intval', $sectionIds), true);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
            if ($selectedSections !== [] && !self::userHasSelectedSection($schoolId, $user, $selectedSections)) {
                continue;
            }

            self::create(
                (int) $user['id'],
                'communique',
                $communiqueId,
                $title,
                $body
            );
        }
    }

    public static function getForUser(int $userId, int $limit = 20): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT id, type, title, body, is_read, created_at
             FROM messages_utilisateurs
             WHERE utilisateur_id = :utilisateur_id
             ORDER BY created_at DESC, id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':utilisateur_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnreadForUser(int $userId, int $limit = 5): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT id, type, title, body, created_at
             FROM messages_utilisateurs
             WHERE utilisateur_id = :utilisateur_id AND is_read = 0
             ORDER BY created_at DESC, id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':utilisateur_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(20, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countUnreadForUser(int $userId): int
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) FROM messages_utilisateurs
             WHERE utilisateur_id = :utilisateur_id AND is_read = 0'
        );
        $stmt->execute([':utilisateur_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public static function markRead(int $messageId, int $userId): bool
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'UPDATE messages_utilisateurs SET is_read = 1
             WHERE id = :id AND utilisateur_id = :utilisateur_id'
        );
        $stmt->execute([':id' => $messageId, ':utilisateur_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    private static function create(int $userId, string $type, int $referenceId, string $title, string $body): void
    {
        if ($userId <= 0 || $referenceId <= 0) {
            return;
        }

        $stmt = Database::getConnection()->prepare(
            'INSERT INTO messages_utilisateurs
                (utilisateur_id, type, reference_id, title, body)
             VALUES (:utilisateur_id, :type, :reference_id, :title, :body)
             ON DUPLICATE KEY UPDATE id = id'
        );
        $stmt->execute([
            ':utilisateur_id' => $userId,
            ':type' => $type,
            ':reference_id' => $referenceId,
            ':title' => $title,
            ':body' => $body,
        ]);
    }

    private static function userHasSelectedSection(int $schoolId, array $user, array $selectedSections): bool
    {
        $role = (string) $user['role'];
        if ($role === 'parent_ecole' || $role === 'eleve_ecole') {
            $parentOrStudentId = (int) ($user['reference_id'] ?? 0);
            if ($parentOrStudentId <= 0) {
                return false;
            }
            $studentCondition = $role === 'parent_ecole' ? 'e.parent_id = :reference_id' : 'e.id = :reference_id';
            $stmt = Database::getConnection()->prepare(
                'SELECT 1 FROM eleves e
                 INNER JOIN inscriptions i ON i.eleve_id = e.id
                 INNER JOIN classes c ON c.id = i.classe_id
                 WHERE ' . $studentCondition . ' AND c.ecole_id = :ecole_id
                   AND c.section_id IN (' . implode(',', array_map('intval', array_keys($selectedSections))) . ')
                 LIMIT 1'
            );
            $stmt->execute([':reference_id' => $parentOrStudentId, ':ecole_id' => $schoolId]);

            return (bool) $stmt->fetchColumn();
        }

        $userSectionId = (int) ($user['section_id'] ?? 0);
        if ($userSectionId > 0) {
            return isset($selectedSections[$userSectionId]);
        }

        return in_array($role, [
            'ecole_admin',
            'comptable_école',
            'sec_école',
            'promoteur_école',
        ], true);
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS messages_utilisateurs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                utilisateur_id INT NOT NULL,
                type VARCHAR(40) NOT NULL,
                reference_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                body TEXT NOT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_message_user_reference (utilisateur_id, type, reference_id),
                KEY idx_message_user_unread (utilisateur_id, is_read, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
