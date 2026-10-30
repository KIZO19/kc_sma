<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PlatformContact
{
    private static bool $tableEnsured = false;

    public static function send(array $sender, string $subject, string $body): int
    {
        self::ensureTable();
        UserMessage::ensureTable();
        $senderId = (int) ($sender['id'] ?? 0);
        if ($senderId <= 0) {
            throw new \DomainException('Votre session a expiré. Reconnectez-vous puis réessayez.');
        }

        $db = Database::getConnection();
        $administrators = $db->query(
            "SELECT id FROM utilisateurs WHERE role = 'super_admin' AND statut = 'Actif' ORDER BY id"
        )->fetchAll(PDO::FETCH_COLUMN);
        if ($administrators === []) {
            throw new \DomainException('Aucun administrateur KC_SMA actif ne peut recevoir votre message.');
        }

        $schoolId = (int) ($sender['ecole_id'] ?? 0);
        $senderName = trim((string) ($sender['nom_complet'] ?? 'Utilisateur'));
        $senderRole = User::getRoleLabel((string) ($sender['role'] ?? ''));
        $school = $schoolId > 0 ? Ecole::findById($schoolId) : null;
        $senderDetails = $senderName . ' — ' . $senderRole;
        if (!empty($school['nom_etablissement'])) {
            $senderDetails .= ' — ' . (string) $school['nom_etablissement'];
        }

        $db->beginTransaction();
        try {
            $insert = $db->prepare(
                'INSERT INTO kc_sma_contact_messages
                    (sender_user_id, school_id, sender_name, sender_role, subject, body)
                 VALUES (:sender_user_id, :school_id, :sender_name, :sender_role, :subject, :body)'
            );
            $insert->execute([
                ':sender_user_id' => $senderId,
                ':school_id' => $schoolId > 0 ? $schoolId : null,
                ':sender_name' => $senderName,
                ':sender_role' => $senderRole,
                ':subject' => $subject,
                ':body' => $body,
            ]);
            $contactMessageId = (int) $db->lastInsertId();
            if ($contactMessageId <= 0) {
                throw new \RuntimeException('Impossible d’enregistrer le message pour KC_SMA.');
            }

            UserMessage::notifyPlatformAdministrators(
                array_map('intval', $administrators),
                $contactMessageId,
                $senderDetails,
                $subject,
                $body
            );
            $db->commit();

            return count($administrators);
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS kc_sma_contact_messages (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                sender_user_id INT NOT NULL,
                school_id INT NULL,
                sender_name VARCHAR(160) NOT NULL,
                sender_role VARCHAR(100) NOT NULL,
                subject VARCHAR(160) NOT NULL,
                body TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_kc_sma_contact_sender (sender_user_id, created_at),
                KEY idx_kc_sma_contact_school (school_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
