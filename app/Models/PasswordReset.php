<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PasswordReset
{
    private const TOKEN_LIFETIME_MINUTES = 30;

    public static function findAccountByRecoveryIdentifier(string $identifier): ?array
    {
        $db = Database::getConnection();
        $sql = "SELECT u.id, u.nom_complet, u.role, u.reference_id, u.ecole_id, u.identifiant,
                    CASE
                        WHEN u.role = 'parent_ecole' THEN p.email
                        WHEN u.role = 'eleve_ecole' THEN student_parent.email
                        WHEN u.role IN ('agent_ecole', 'enseignant_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'comptable_école', 'sec_école') THEN a.email
                        WHEN u.role IN ('ecole_admin', 'promoteur_école') THEN s.email_officiel
                        ELSE NULL
                    END AS recovery_email
                FROM utilisateurs u
                LEFT JOIN parents p ON u.role = 'parent_ecole' AND u.reference_id = p.id
                LEFT JOIN agents a ON u.role IN ('agent_ecole', 'enseignant_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'comptable_école', 'sec_école') AND u.reference_id = a.id
                LEFT JOIN eleves e ON u.role = 'eleve_ecole' AND u.reference_id = e.id
                LEFT JOIN parents student_parent ON e.parent_id = student_parent.id
                LEFT JOIN ecoles s ON u.role IN ('ecole_admin', 'promoteur_école') AND u.ecole_id = s.id
                WHERE LOWER(u.identifiant) = LOWER(:identifier_user)
                    OR LOWER(p.email) = LOWER(:identifier_parent_email)
                    OR p.telephone = :identifier_parent_phone
                    OR LOWER(a.email) = LOWER(:identifier_agent_email)
                    OR a.telephone = :identifier_agent_phone
                    OR LOWER(s.email_officiel) = LOWER(:identifier_school_email)
                    OR s.telephone_contact = :identifier_school_phone
                ORDER BY (LOWER(u.identifiant) = LOWER(:identifier_order)) DESC
                LIMIT 2";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':identifier_user' => $identifier,
            ':identifier_parent_email' => $identifier,
            ':identifier_parent_phone' => $identifier,
            ':identifier_agent_email' => $identifier,
            ':identifier_agent_phone' => $identifier,
            ':identifier_school_email' => $identifier,
            ':identifier_school_phone' => $identifier,
            ':identifier_order' => $identifier,
        ]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($accounts) !== 1) {
            return null;
        }

        $account = $accounts[0];
        if (empty($account['recovery_email']) && filter_var($account['identifiant'], FILTER_VALIDATE_EMAIL)) {
            $account['recovery_email'] = $account['identifiant'];
        }

        return $account;
    }

    public static function recordRequest(string $remoteAddress, ?int $userId): bool
    {
        $db = Database::getConnection();
        $db->exec("DELETE FROM password_reset_requests WHERE requested_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");

        $ipLimit = $db->prepare(
            'SELECT COUNT(*) FROM password_reset_requests '
            . 'WHERE remote_address = :remote_address AND requested_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
        );
        $ipLimit->execute([':remote_address' => $remoteAddress]);
        if ((int) $ipLimit->fetchColumn() >= 5) {
            return false;
        }

        if ($userId !== null) {
            $accountLimit = $db->prepare(
                'SELECT COUNT(*) FROM password_reset_requests '
                . 'WHERE user_id = :user_id AND requested_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)'
            );
            $accountLimit->execute([':user_id' => $userId]);
            if ((int) $accountLimit->fetchColumn() > 0) {
                return false;
            }
        }

        $insert = $db->prepare(
            'INSERT INTO password_reset_requests (user_id, remote_address) VALUES (:user_id, :remote_address)'
        );
        $insert->execute([
            ':user_id' => $userId,
            ':remote_address' => substr($remoteAddress, 0, 45),
        ]);

        return true;
    }

    public static function createToken(int $userId, int $lifetimeMinutes = self::TOKEN_LIFETIME_MINUTES): string
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Le compte utilisateur est invalide.');
        }
        $lifetimeMinutes = max(1, min(1440, $lifetimeMinutes));
        $db = Database::getConnection();
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $db->beginTransaction();
        try {
            $db->exec('DELETE FROM password_reset_tokens WHERE expires_at <= NOW()');
            $delete = $db->prepare('DELETE FROM password_reset_tokens WHERE user_id = :user_id');
            $delete->execute([':user_id' => $userId]);

            $insert = $db->prepare(
                'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) '
                . 'VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL ' . $lifetimeMinutes . ' MINUTE))'
            );
            $insert->execute([':user_id' => $userId, ':token_hash' => $tokenHash]);
            $db->commit();
        } catch (\PDOException $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        return $token;
    }

    public static function findAccountForToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT u.id, u.nom_complet, u.role, u.reference_id, u.ecole_id, u.identifiant, e.matricule '
            . 'FROM password_reset_tokens t '
            . 'INNER JOIN utilisateurs u ON u.id = t.user_id '
            . "LEFT JOIN eleves e ON u.role IN ('eleve_ecole', 'eleve_école') AND e.id = u.reference_id "
            . 'WHERE t.token_hash = :token_hash AND t.used_at IS NULL AND t.expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([':token_hash' => hash('sha256', $token)]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        return $account ?: null;
    }

    public static function isTokenValid(string $token): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT 1 FROM password_reset_tokens '
            . 'WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([':token_hash' => hash('sha256', $token)]);
        return (bool) $stmt->fetchColumn();
    }

    public static function resetPassword(string $token, string $passwordHash, ?string $matricule = null): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                'SELECT u.id AS user_id, u.role, e.matricule '
                . 'FROM password_reset_tokens t '
                . 'INNER JOIN utilisateurs u ON u.id = t.user_id '
                . "LEFT JOIN eleves e ON u.role IN ('eleve_ecole', 'eleve_école') AND e.id = u.reference_id "
                . 'WHERE t.token_hash = :token_hash AND t.used_at IS NULL AND t.expires_at > NOW() '
                . 'LIMIT 1 FOR UPDATE'
            );
            $stmt->execute([':token_hash' => hash('sha256', $token)]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$account) {
                $db->rollBack();
                return false;
            }

            if (
                in_array($account['role'], ['eleve_ecole', 'eleve_école'], true)
                && (
                    $matricule === null
                    || trim($matricule) === ''
                    || !hash_equals((string) ($account['matricule'] ?? ''), trim($matricule))
                )
            ) {
                $db->rollBack();
                return false;
            }

            $userId = (int) $account['user_id'];
            $updateUser = $db->prepare(
                'UPDATE utilisateurs
                 SET mot_de_passe = :password_hash, must_change_password = 0
                 WHERE id = :user_id'
            );
            $updateUser->execute([':password_hash' => $passwordHash, ':user_id' => $userId]);
            if ($updateUser->rowCount() !== 1) {
                $db->rollBack();
                return false;
            }

            $deleteTokens = $db->prepare('DELETE FROM password_reset_tokens WHERE user_id = :user_id');
            $deleteTokens->execute([':user_id' => $userId]);
            $db->commit();

            return true;
        } catch (\PDOException $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    public static function revokeToken(string $token): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return;
        }

        $stmt = Database::getConnection()->prepare(
            'DELETE FROM password_reset_tokens WHERE token_hash = :token_hash'
        );
        $stmt->execute([':token_hash' => hash('sha256', $token)]);
    }
}
