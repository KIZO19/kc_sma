<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

class CashOutflow
{
    private static ?bool $approvalWorkflowAvailable = null;

    private const APPROVER_ROLES = [
        'ecole_admin',
        'promoteur_école',
        'préfet_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
        'comptable_école',
        'sec_école',
        'enseignant_école',
        'agent_école',
    ];

    public static function getApprovalRoleOptions(int $schoolId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT DISTINCT role FROM utilisateurs
             WHERE ecole_id = :ecole_id AND statut = \'Actif\'
             ORDER BY role'
        );
        $stmt->execute([':ecole_id' => $schoolId]);
        $activeRoles = array_fill_keys($stmt->fetchAll(PDO::FETCH_COLUMN), true);
        $options = [];
        foreach (self::APPROVER_ROLES as $role) {
            if (isset($activeRoles[$role])) {
                $options[$role] = User::getRoleLabel($role);
            }
        }

        return $options;
    }

    public static function getConfiguredApprovalRoles(int $schoolId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT role FROM ecole_sortie_approbation_roles
             WHERE ecole_id = :ecole_id ORDER BY role'
        );
        $stmt->execute([':ecole_id' => $schoolId]);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function isApprovalWorkflowAvailable(): bool
    {
        if (self::$approvalWorkflowAvailable !== null) {
            return self::$approvalWorkflowAvailable;
        }

        $db = Database::getConnection();
        $rolesTable = $db->query("SHOW TABLES LIKE 'ecole_sortie_approbation_roles'")->fetchColumn();
        $outflowsTable = $db->query("SHOW TABLES LIKE 'sorties_caisse'")->fetchColumn();
        self::$approvalWorkflowAvailable = $rolesTable !== false && $outflowsTable !== false;

        return self::$approvalWorkflowAvailable;
    }

    public static function saveApprovalRoles(int $schoolId, array $roles, int $userId): void
    {
        if ($schoolId <= 0 || $userId <= 0) {
            throw new \InvalidArgumentException('École ou administrateur invalide.');
        }

        foreach ($roles as $role) {
            if (!\is_scalar($role)) {
                throw new \InvalidArgumentException('La liste des rôles approbateurs est invalide.');
            }
        }
        $roles = array_values(array_unique(array_filter(
            array_map(static fn ($role): string => trim((string) $role), $roles),
            static fn (string $role): bool => $role !== ''
        )));
        $options = self::getApprovalRoleOptions($schoolId);
        foreach ($roles as $role) {
            if (!\in_array($role, self::APPROVER_ROLES, true) || !\array_key_exists($role, $options)) {
                throw new \InvalidArgumentException('Un rôle sélectionné ne correspond pas à un compte actif de cette école.');
            }
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $delete = $db->prepare('DELETE FROM ecole_sortie_approbation_roles WHERE ecole_id = :ecole_id');
            $delete->execute([':ecole_id' => $schoolId]);
            $insert = $db->prepare(
                'INSERT INTO ecole_sortie_approbation_roles (ecole_id, role, configured_by_user_id)
                 VALUES (:ecole_id, :role, :user_id)'
            );
            foreach ($roles as $role) {
                $insert->execute([
                    ':ecole_id' => $schoolId,
                    ':role' => $role,
                    ':user_id' => $userId,
                ]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function isUserDesignatedApprover(array $user): bool
    {
        if (!self::isApprovalWorkflowAvailable()) {
            return false;
        }
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $userId = (int) ($user['id'] ?? 0);
        $role = (string) ($user['role'] ?? '');
        if ($schoolId <= 0 || $userId <= 0 || !\in_array($role, self::APPROVER_ROLES, true)) {
            return false;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT 1
             FROM utilisateurs u
             INNER JOIN ecole_sortie_approbation_roles r
                ON r.ecole_id = u.ecole_id AND r.role = u.role
             WHERE u.id = :user_id AND u.ecole_id = :ecole_id
               AND u.statut = \'Actif\' LIMIT 1'
        );
        $stmt->execute([':user_id' => $userId, ':ecole_id' => $schoolId]);

        return (bool) $stmt->fetchColumn();
    }

    public static function getAccounts(int $schoolId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT cb.id, cb.nom_compte, cb.type_compte, cb.solde_actuel,
                    COALESCE(cbd.devise, \'USD\') AS devise
             FROM caisses_banques cb
             LEFT JOIN caisses_banques_devises cbd ON cbd.caisse_banque_id = cb.id
             WHERE cb.ecole_id = :ecole_id
             ORDER BY cb.nom_compte'
        );
        $stmt->execute([':ecole_id' => $schoolId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getOutflows(int $schoolId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT s.*, cb.nom_compte, cb.type_compte, creator.nom_complet AS demandeur_nom,
                    decider.nom_complet AS decideur_nom,
                    a.nom AS agent_nom, a.postnom AS agent_postnom, a.prenom AS agent_prenom
             FROM sorties_caisse s
             INNER JOIN caisses_banques cb ON cb.id = s.caisse_banque_id
             INNER JOIN utilisateurs creator ON creator.id = s.created_by_user_id
             LEFT JOIN utilisateurs decider ON decider.id = s.decided_by_user_id
             LEFT JOIN paies_agents p ON p.id = s.paie_agent_id
             LEFT JOIN agents a ON a.id = p.agent_id
             WHERE s.ecole_id = :ecole_id
             ORDER BY (s.statut = \'En attente\') DESC, s.created_at DESC, s.id DESC'
        );
        $stmt->execute([':ecole_id' => $schoolId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function requestExpense(
        int $schoolId,
        int $accountId,
        float $amount,
        string $reason,
        int $userId
    ): void {
        $reason = trim($reason);
        $amount = round($amount, 2);
        if ($amount <= 0 || $amount > 9999999999.99 || $reason === '' || mb_strlen($reason, 'UTF-8') > 255) {
            throw new \InvalidArgumentException('Saisissez un motif et un montant positif valide.');
        }

        self::assertConfiguredApprovers($schoolId);
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $account = self::lockAccount($db, $schoolId, $accountId);
            if ((float) $account['solde_actuel'] < $amount) {
                throw new \InvalidArgumentException('Le solde actuel de la caisse ou banque est insuffisant.');
            }
            $stmt = $db->prepare(
                'INSERT INTO sorties_caisse
                    (ecole_id, caisse_banque_id, montant, devise, motif, created_by_user_id)
                 VALUES (:ecole_id, :account_id, :amount, :currency, :reason, :user_id)'
            );
            $stmt->execute([
                ':ecole_id' => $schoolId,
                ':account_id' => $accountId,
                ':amount' => number_format($amount, 2, '.', ''),
                ':currency' => $account['devise'],
                ':reason' => $reason,
                ':user_id' => $userId,
            ]);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function requestPayrollPayment(
        int $schoolId,
        int $slipId,
        int $accountId,
        int $userId
    ): void {
        self::assertConfiguredApprovers($schoolId);
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $slipStmt = $db->prepare(
                'SELECT agent_id, devise, net_a_payer, statut_paiement
                 FROM paies_agents WHERE id = :id AND ecole_id = :ecole_id FOR UPDATE'
            );
            $slipStmt->execute([':id' => $slipId, ':ecole_id' => $schoolId]);
            $slip = $slipStmt->fetch(PDO::FETCH_ASSOC);
            if (!$slip) {
                throw new \InvalidArgumentException('Cette fiche de paie est introuvable dans votre école.');
            }
            if ($slip['statut_paiement'] !== 'En attente') {
                throw new \InvalidArgumentException('Cette fiche de paie a déjà été payée.');
            }
            $duplicate = $db->prepare(
                'SELECT 1 FROM sorties_caisse
                 WHERE paie_agent_id = :slip_id AND statut = \'En attente\' LIMIT 1 FOR UPDATE'
            );
            $duplicate->execute([':slip_id' => $slipId]);
            if ($duplicate->fetchColumn()) {
                throw new \InvalidArgumentException('Une demande de paiement existe déjà pour cette fiche de paie.');
            }

            $account = self::lockAccount($db, $schoolId, $accountId);
            if ($account['devise'] !== $slip['devise']) {
                throw new \InvalidArgumentException('La devise du compte ne correspond pas à celle de la fiche de paie.');
            }
            if ((float) $account['solde_actuel'] < (float) $slip['net_a_payer']) {
                throw new \InvalidArgumentException('Le solde actuel de la caisse ou banque est insuffisant.');
            }

            $stmt = $db->prepare(
                'INSERT INTO sorties_caisse
                    (ecole_id, caisse_banque_id, paie_agent_id, montant, devise, motif, created_by_user_id)
                 VALUES (:ecole_id, :account_id, :slip_id, :amount, :currency, :reason, :user_id)'
            );
            $stmt->execute([
                ':ecole_id' => $schoolId,
                ':account_id' => $accountId,
                ':slip_id' => $slipId,
                ':amount' => number_format((float) $slip['net_a_payer'], 2, '.', ''),
                ':currency' => $slip['devise'],
                ':reason' => 'Paie mensuelle de l’agent #' . (int) $slip['agent_id'],
                ':user_id' => $userId,
            ]);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function decide(int $schoolId, int $outflowId, array $user, bool $approve, string $comment): void
    {
        $userId = (int) ($user['id'] ?? 0);
        if ($schoolId <= 0 || $schoolId !== (int) ($user['ecole_id'] ?? 0) || $outflowId <= 0 || $userId <= 0) {
            throw new \InvalidArgumentException('École, utilisateur ou demande invalide.');
        }
        $comment = trim($comment);
        if (mb_strlen($comment, 'UTF-8') > 255) {
            throw new \InvalidArgumentException('Le commentaire ne peut pas dépasser 255 caractères.');
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            if (!self::isUserDesignatedApprover($user)) {
                throw new \InvalidArgumentException('Votre rôle n’est pas désigné pour approuver les sorties de cette école.');
            }
            $stmt = $db->prepare(
                'SELECT id, caisse_banque_id, paie_agent_id, montant, devise, statut, created_by_user_id
                 FROM sorties_caisse WHERE id = :id AND ecole_id = :ecole_id FOR UPDATE'
            );
            $stmt->execute([':id' => $outflowId, ':ecole_id' => $schoolId]);
            $outflow = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$outflow) {
                throw new \InvalidArgumentException('Cette demande de sortie est introuvable dans votre école.');
            }
            if ($outflow['statut'] !== 'En attente') {
                throw new \InvalidArgumentException('Cette demande de sortie a déjà été traitée.');
            }
            if ((int) $outflow['created_by_user_id'] === $userId) {
                throw new \InvalidArgumentException('Vous ne pouvez pas approuver une sortie que vous avez vous-même demandée.');
            }

            if ($approve) {
                $account = self::lockAccount($db, $schoolId, (int) $outflow['caisse_banque_id']);
                if ($account['devise'] !== $outflow['devise']) {
                    throw new \InvalidArgumentException('La devise de la caisse ou banque a changé depuis la demande.');
                }
                $amount = (float) $outflow['montant'];
                if ((float) $account['solde_actuel'] < $amount) {
                    throw new \InvalidArgumentException('Le solde est devenu insuffisant. La sortie n’a pas été débitée.');
                }

                $debit = $db->prepare(
                    'UPDATE caisses_banques
                     SET solde_actuel = solde_actuel - :amount
                     WHERE id = :id AND ecole_id = :ecole_id AND solde_actuel >= :guard_amount'
                );
                $debit->execute([
                    ':amount' => number_format($amount, 2, '.', ''),
                    ':id' => (int) $outflow['caisse_banque_id'],
                    ':ecole_id' => $schoolId,
                    ':guard_amount' => number_format($amount, 2, '.', ''),
                ]);
                if ($debit->rowCount() !== 1) {
                    throw new RuntimeException('Le solde a changé pendant la validation. Rechargez la page et réessayez.');
                }

                if ($outflow['paie_agent_id'] !== null) {
                    $paySlip = $db->prepare(
                        'UPDATE paies_agents
                         SET statut_paiement = \'Payé\', caisse_banque_id = :account_id,
                             paid_by_user_id = :user_id, date_paiement_effectif = CURRENT_TIMESTAMP
                         WHERE id = :slip_id AND ecole_id = :ecole_id AND statut_paiement = \'En attente\''
                    );
                    $paySlip->execute([
                        ':account_id' => (int) $outflow['caisse_banque_id'],
                        ':user_id' => $userId,
                        ':slip_id' => (int) $outflow['paie_agent_id'],
                        ':ecole_id' => $schoolId,
                    ]);
                    if ($paySlip->rowCount() !== 1) {
                        throw new RuntimeException('La fiche de paie a changé pendant la validation. La sortie n’a pas été enregistrée.');
                    }
                }
            }

            $update = $db->prepare(
                'UPDATE sorties_caisse
                 SET statut = :status, decided_by_user_id = :user_id,
                     decided_at = CURRENT_TIMESTAMP, decision_comment = :comment
                 WHERE id = :id AND ecole_id = :ecole_id AND statut = \'En attente\''
            );
            $update->execute([
                ':status' => $approve ? 'Approuvée' : 'Refusée',
                ':user_id' => $userId,
                ':comment' => $comment !== '' ? $comment : null,
                ':id' => $outflowId,
                ':ecole_id' => $schoolId,
            ]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('La demande a été traitée par un autre utilisateur. Rechargez la page.');
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function hasPendingForAccount(int $accountId): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT 1 FROM sorties_caisse
             WHERE caisse_banque_id = :account_id AND statut = \'En attente\' LIMIT 1'
        );
        $stmt->execute([':account_id' => $accountId]);

        return (bool) $stmt->fetchColumn();
    }

    private static function assertConfiguredApprovers(int $schoolId): void
    {
        if (self::getConfiguredApprovalRoles($schoolId) === []) {
            throw new \InvalidArgumentException('L’administrateur de l’école doit d’abord désigner au moins un rôle approbateur dans les paramètres de l’école.');
        }
    }

    private static function lockAccount(PDO $db, int $schoolId, int $accountId): array
    {
        $stmt = $db->prepare(
            'SELECT cb.id, cb.solde_actuel, COALESCE(cbd.devise, \'USD\') AS devise
             FROM caisses_banques cb
             LEFT JOIN caisses_banques_devises cbd ON cbd.caisse_banque_id = cb.id
             WHERE cb.id = :id AND cb.ecole_id = :ecole_id FOR UPDATE'
        );
        $stmt->execute([':id' => $accountId, ':ecole_id' => $schoolId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$account) {
            throw new \InvalidArgumentException('Cette caisse ou banque n’appartient pas à votre école.');
        }

        return $account;
    }
}
