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
    ): int {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            "SELECT u.id
             FROM eleves e
             INNER JOIN utilisateurs u ON u.reference_id = e.parent_id
                AND u.role IN ('parent_ecole', 'parent_école') AND u.ecole_id = e.ecole_id
             WHERE e.id = :eleve_id AND u.statut = 'Actif'"
        );
        $stmt->execute([':eleve_id' => $studentId]);
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($userIds as $userId) {
            self::create((int) $userId, $type, $referenceId, $title, $body);
        }

        return count($userIds);
    }

    public static function notifyDerogationRecipients(
        int $schoolId,
        int $derogationId,
        string $studentName,
        string $requesterName,
        string $endDate
    ): int {
        if ($schoolId <= 0 || $derogationId <= 0) {
            return 0;
        }

        self::ensureTable();
        $userIds = Derogation::getRecipientUserIds($schoolId);
        $title = 'Nouvelle demande de dérogation';
        $body = $requesterName . ' a demandé une dérogation pour ' . $studentName
            . ', jusqu’au ' . date('d/m/Y', strtotime($endDate)) . '.';
        foreach ($userIds as $userId) {
            self::create((int) $userId, 'derogation_demande', $derogationId, $title, $body);
        }

        return count($userIds);
    }

    public static function notifyPlatformAdministrators(
        array $administratorIds,
        int $contactMessageId,
        string $senderName,
        string $subject,
        string $body
    ): void {
        self::ensureTable();
        $title = 'Nouveau message reçu par KC_SMA';
        $messageBody = 'De : ' . $senderName . "\nObjet : " . $subject . "\n\n" . $body;
        foreach ($administratorIds as $administratorId) {
            self::create((int) $administratorId, 'contact_kc_sma', $contactMessageId, $title, $messageBody);
        }
    }

    public static function notifyAdministratorsOfSubscriptionRequest(
        int $requestId,
        int $schoolId,
        int $planId,
        int $requesterId
    ): void {
        self::ensureTable();
        $db = Database::getConnection();
        $request = $db->prepare(
            'SELECT e.nom_etablissement, u.nom_complet AS requester_name,
                    p.nom_plan, p.prix, p.duree_mois
             FROM demandes_abonnement_ecoles r
             INNER JOIN ecoles e ON e.id = r.ecole_id
             INNER JOIN utilisateurs u ON u.id = r.demandeur_id
             INNER JOIN plans_abonnement p ON p.id = r.plan_id
             WHERE r.id = :request_id AND r.ecole_id = :school_id
               AND r.plan_id = :plan_id AND r.demandeur_id = :requester_id
               AND r.type_demande = \'renouvellement\'
             LIMIT 1'
        );
        $request->execute([
            ':request_id' => $requestId,
            ':school_id' => $schoolId,
            ':plan_id' => $planId,
            ':requester_id' => $requesterId,
        ]);
        $details = $request->fetch(PDO::FETCH_ASSOC);
        if (!$details) {
            throw new \RuntimeException('Subscription renewal request details could not be loaded.');
        }

        $administratorIds = $db->query(
            "SELECT id FROM utilisateurs WHERE role = 'super_admin' AND statut = 'Actif'"
        )->fetchAll(PDO::FETCH_COLUMN);
        if ($administratorIds === []) {
            throw new \RuntimeException('No active KC_SMA administrator is available for subscription notifications.');
        }

        $title = 'Nouvelle demande de renouvellement d’abonnement';
        $body = (string) $details['requester_name'] . ' demande le renouvellement de l’abonnement de '
            . (string) $details['nom_etablissement'] . ' avec le tarif '
            . (string) $details['nom_plan'] . ' ('
            . number_format((float) $details['prix'], 2, ',', ' ') . ' USD / '
            . (int) $details['duree_mois'] . ' mois).';
        foreach ($administratorIds as $administratorId) {
            self::create(
                (int) $administratorId,
                'subscription_renewal_request',
                $requestId,
                $title,
                $body
            );
        }
    }

    public static function notifyAccountantsOfExpiredDerogations(int $schoolId): void
    {
        if ($schoolId <= 0) {
            return;
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            "INSERT IGNORE INTO messages_utilisateurs
                (utilisateur_id, type, reference_id, title, body)
             SELECT u.id, 'derogation_expiree', d.id, 'Dérogation arrivée à échéance',
                    CONCAT(
                        'La dérogation accordée à ',
                        TRIM(CONCAT_WS(' ', e.nom, e.postnom, e.prenom)),
                        ' a pris fin le ',
                        DATE_FORMAT(d.date_fin, '%d/%m/%Y'),
                        '.'
                    )
             FROM derogations d
             INNER JOIN eleves e ON e.id = d.eleve_id
             INNER JOIN utilisateurs u ON u.ecole_id = d.ecole_id
                AND u.role = 'comptable_école' AND u.statut = 'Actif'
             WHERE d.ecole_id = :ecole_id
               AND d.statut = 'Approuvee' AND d.date_fin <= CURDATE()"
        );
        $stmt->execute([':ecole_id' => $schoolId]);
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

    public static function findForUser(int $messageId, int $userId): ?array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT id, type, reference_id, title, body, created_at FROM messages_utilisateurs '
            . 'WHERE id = :id AND utilisateur_id = :utilisateur_id LIMIT 1'
        );
        $stmt->execute([':id' => $messageId, ':utilisateur_id' => $userId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        return $message ?: null;
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
        if (in_array($role, ['parent_ecole', 'parent_école', 'eleve_ecole', 'eleve_école'], true)) {
            $parentOrStudentId = (int) ($user['reference_id'] ?? 0);
            if ($parentOrStudentId <= 0) {
                return false;
            }
            $studentCondition = in_array($role, ['parent_ecole', 'parent_école'], true)
                ? 'e.parent_id = :reference_id'
                : 'e.id = :reference_id';
            $stmt = Database::getConnection()->prepare(
                'SELECT 1 FROM eleves e
                 INNER JOIN inscriptions i ON i.eleve_id = e.id
                 INNER JOIN annees_scolaires a ON a.id = i.annee_scolaire_id AND a.est_active = 1
                 INNER JOIN classes c ON c.id = i.classe_id
                 WHERE ' . $studentCondition . ' AND c.ecole_id = :ecole_id
                   AND c.section_id IN (' . implode(',', array_map('intval', array_keys($selectedSections))) . ')
                 LIMIT 1'
            );
            $stmt->execute([':reference_id' => $parentOrStudentId, ':ecole_id' => $schoolId]);

            return (bool) $stmt->fetchColumn();
        }

        $userSectionId = (int) ($user['section_id'] ?? 0);
        if ($userSectionId <= 0) {
            $userSectionId = User::getDefaultSectionIdForRole($role) ?? 0;
        }
        if ($userSectionId > 0) {
            return isset($selectedSections[$userSectionId]);
        }

        if ($role === 'enseignant_école') {
            $agentId = (int) ($user['reference_id'] ?? 0);
            if ($agentId <= 0) {
                return false;
            }
            $stmt = Database::getConnection()->prepare(
                'SELECT 1
                 FROM attribution_cours ac
                 INNER JOIN classes c ON c.id = ac.classe_id AND c.ecole_id = ac.ecole_id
                 INNER JOIN annees_scolaires a ON a.id = ac.annee_scolaire_id
                   AND a.ecole_id = ac.ecole_id AND a.est_active = 1
                 WHERE ac.ecole_id = :ecole_id AND ac.agent_id = :agent_id
                   AND c.section_id IN (' . implode(',', array_map('intval', array_keys($selectedSections))) . ')
                 LIMIT 1'
            );
            $stmt->execute([':ecole_id' => $schoolId, ':agent_id' => $agentId]);

            return (bool) $stmt->fetchColumn();
        }

        return true;
    }

    public static function ensureTable(): void
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
