<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class UserMessage
{
    private static bool $tableEnsured = false;
    private static bool $schoolActionReportsTableEnsured = false;

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

    public static function notifySchoolAdministratorsOfSubscriptionDecision(
        array $request,
        string $status,
        string $note
    ): void {
        if (
            ($request['type_demande'] ?? '') !== 'renouvellement'
            || !in_array($status, ['Approuvee', 'Refusee'], true)
        ) {
            return;
        }

        self::ensureTable();
        $schoolId = (int) ($request['ecole_id'] ?? 0);
        $requestId = (int) ($request['id'] ?? 0);
        if ($schoolId <= 0 || $requestId <= 0) {
            throw new \RuntimeException('Subscription renewal request details are invalid.');
        }

        $stmt = Database::getConnection()->prepare(
            "SELECT id FROM utilisateurs
             WHERE ecole_id = :ecole_id AND role = 'ecole_admin' AND statut = 'Actif'"
        );
        $stmt->execute([':ecole_id' => $schoolId]);
        $administratorIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        if ($administratorIds === []) {
            throw new \RuntimeException('No active school administrator is available for the subscription decision notification.');
        }

        $approved = $status === 'Approuvee';
        $title = $approved
            ? 'Demande de renouvellement approuvée'
            : 'Demande de renouvellement refusée';
        $body = 'Votre demande de renouvellement pour '
            . (string) ($request['nom_etablissement'] ?? 'votre école')
            . ' a été ' . ($approved ? 'approuvée' : 'refusée')
            . '. Tarif : ' . (string) ($request['nom_plan'] ?? 'non précisé') . '.';
        if ($note !== '') {
            $body .= "\nRéponse de KC_SMA : " . $note;
        }

        foreach ($administratorIds as $administratorId) {
            self::create(
                $administratorId,
                $approved ? 'subscription_request_approved' : 'subscription_request_rejected',
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
        self::ensureSchoolActionReportsTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT m.id, m.type, m.title, m.body, m.is_read, m.created_at
             FROM messages_utilisateurs m
             INNER JOIN utilisateurs recipient ON recipient.id = m.utilisateur_id
             LEFT JOIN rapports_actions_ecole report ON report.id = m.reference_id
                AND m.type = \'school_action_report\'
             WHERE m.utilisateur_id = :utilisateur_id'
                . self::schoolActionReportVisibilitySql() . '
             ORDER BY m.created_at DESC, m.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':utilisateur_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function notifyUsers(array $userIds, int $referenceId, string $title, string $body): int
    {
        if ($referenceId <= 0 || trim($title) === '' || trim($body) === '') {
            throw new \InvalidArgumentException('La notification interne est incomplète.');
        }

        self::ensureTable();
        $sent = 0;
        $uniqueUserIds = array_values(array_unique(array_filter(
            array_map('intval', $userIds),
            static fn (int $userId): bool => $userId > 0
        )));
        foreach ($uniqueUserIds as $userId) {
            if (self::create($userId, 'discipline_audition', $referenceId, $title, $body)) {
                $sent++;
            }
        }

        return $sent;
    }

    public static function notifySchoolActionReport(
        int $schoolId,
        int $actorUserId,
        string $actionType,
        string $title,
        string $body
    ): int {
        if (
            $schoolId <= 0
            || $actorUserId <= 0
            || trim($actionType) === ''
            || trim($title) === ''
            || trim($body) === ''
        ) {
            throw new \InvalidArgumentException('Le rapport d’action scolaire est incomplet.');
        }

        self::ensureTable();
        self::ensureSchoolActionReportsTable();
        $db = Database::getConnection();

        $actorStmt = $db->prepare(
            'SELECT nom_complet, role FROM utilisateurs
             WHERE id = :user_id AND ecole_id = :school_id LIMIT 1'
        );
        $actorStmt->execute([':user_id' => $actorUserId, ':school_id' => $schoolId]);
        $actor = $actorStmt->fetch(PDO::FETCH_ASSOC);
        $actorLabel = $actor
            ? trim((string) $actor['nom_complet']) . ' (' . User::getRoleLabel((string) $actor['role']) . ')'
            : 'Utilisateur #' . $actorUserId;
        $reportBody = "Action effectuée par : " . $actorLabel . "\n\n" . trim($body);

        $reportStmt = $db->prepare(
            'INSERT INTO rapports_actions_ecole (ecole_id, acteur_user_id, type_action, title, body)
             VALUES (:school_id, :actor_user_id, :action_type, :title, :body)'
        );
        $reportStmt->execute([
            ':school_id' => $schoolId,
            ':actor_user_id' => $actorUserId,
            ':action_type' => trim($actionType),
            ':title' => trim($title),
            ':body' => $reportBody,
        ]);
        $reportId = (int) $db->lastInsertId();
        if ($reportId <= 0) {
            throw new \RuntimeException('Le rapport d’action scolaire n’a pas pu être identifié.');
        }

        $recipientsStmt = $db->prepare(
            "SELECT id FROM utilisateurs
             WHERE ecole_id = :school_id
               AND statut = 'Actif'
               AND role IN ('promoteur_école', 'promoteur_ecole', 'ecole_admin')
               AND id <> :actor_user_id
             ORDER BY role, id"
        );
        $recipientsStmt->execute([
            ':school_id' => $schoolId,
            ':actor_user_id' => $actorUserId,
        ]);

        $recipientIds = array_map('intval', $recipientsStmt->fetchAll(PDO::FETCH_COLUMN));
        foreach ($recipientIds as $recipientId) {
            self::create($recipientId, 'school_action_report', $reportId, $title, $reportBody, false);
        }

        return count($recipientIds);
    }

    public static function getUnreadForUser(int $userId, int $limit = 5): array
    {
        self::ensureTable();
        self::ensureSchoolActionReportsTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT m.id, m.type, m.title, m.body, m.created_at
             FROM messages_utilisateurs m
             INNER JOIN utilisateurs recipient ON recipient.id = m.utilisateur_id
             LEFT JOIN rapports_actions_ecole report ON report.id = m.reference_id
                AND m.type = \'school_action_report\'
             WHERE m.utilisateur_id = :utilisateur_id
               AND m.is_read = 0' . self::schoolActionReportVisibilitySql() . '
             ORDER BY m.created_at DESC, m.id DESC
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
        self::ensureSchoolActionReportsTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*)
             FROM messages_utilisateurs m
             INNER JOIN utilisateurs recipient ON recipient.id = m.utilisateur_id
             LEFT JOIN rapports_actions_ecole report ON report.id = m.reference_id
                AND m.type = \'school_action_report\'
             WHERE m.utilisateur_id = :utilisateur_id
               AND m.is_read = 0' . self::schoolActionReportVisibilitySql()
        );
        $stmt->execute([':utilisateur_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public static function markRead(int $messageId, int $userId): bool
    {
        self::ensureTable();
        self::ensureSchoolActionReportsTable();
        $stmt = Database::getConnection()->prepare(
            'UPDATE messages_utilisateurs m
             INNER JOIN utilisateurs recipient ON recipient.id = m.utilisateur_id
             LEFT JOIN rapports_actions_ecole report ON report.id = m.reference_id
                AND m.type = \'school_action_report\'
             SET m.is_read = 1
             WHERE m.id = :id AND m.utilisateur_id = :utilisateur_id'
                . self::schoolActionReportVisibilitySql()
        );
        $stmt->execute([':id' => $messageId, ':utilisateur_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    public static function findForUser(int $messageId, int $userId): ?array
    {
        self::ensureTable();
        self::ensureSchoolActionReportsTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT m.id, m.type, m.reference_id, m.title, m.body, m.created_at
             FROM messages_utilisateurs m
             INNER JOIN utilisateurs recipient ON recipient.id = m.utilisateur_id
             LEFT JOIN rapports_actions_ecole report ON report.id = m.reference_id
                AND m.type = \'school_action_report\'
             WHERE m.id = :id AND m.utilisateur_id = :utilisateur_id'
                . self::schoolActionReportVisibilitySql() . '
             LIMIT 1'
        );
        $stmt->execute([':id' => $messageId, ':utilisateur_id' => $userId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        return $message ?: null;
    }

    private static function create(
        int $userId,
        string $type,
        int $referenceId,
        string $title,
        string $body,
        bool $sendPush = true
    ): bool
    {
        if ($userId <= 0 || $referenceId <= 0) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
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
        if ($stmt->rowCount() > 0 && $sendPush) {
            PushNotification::sendToUser(
                $userId,
                $title,
                $body,
                BASE_URL . '/messages',
                'kc-sma-message-' . (int) $db->lastInsertId()
            );
        }
        if ($stmt->rowCount() > 0) {
            return true;
        }

        return false;
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

    private static function ensureSchoolActionReportsTable(): void
    {
        if (self::$schoolActionReportsTableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS rapports_actions_ecole (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                acteur_user_id INT NOT NULL,
                type_action VARCHAR(60) NOT NULL,
                title VARCHAR(200) NOT NULL,
                body TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_rapports_actions_ecole_date (ecole_id, created_at),
                KEY idx_rapports_actions_ecole_actor (acteur_user_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$schoolActionReportsTableEnsured = true;
    }

    private static function schoolActionReportVisibilitySql(): string
    {
        return " AND (
            m.type <> 'school_action_report'
            OR (
                report.id IS NOT NULL
                AND report.ecole_id = recipient.ecole_id
                AND recipient.role IN ('promoteur_école', 'promoteur_ecole', 'ecole_admin')
            )
        )";
    }
}
