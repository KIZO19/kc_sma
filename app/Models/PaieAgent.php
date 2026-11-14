<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PaieAgent
{
    public static function getMissingModuleTables(): array
    {
        $requiredTables = [
            'agents_paie_preferences',
            'caisses_banques_devises',
            'paies_agents',
            'paies_agents_lignes',
            'ecole_sortie_approbation_roles',
            'sorties_caisse',
        ];
        $placeholders = [];
        $params = [];
        foreach ($requiredTables as $index => $table) {
            $placeholder = ':table_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $table;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT table_name FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name IN (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($params);
        $existingTables = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        return array_values(array_diff($requiredTables, $existingTables));
    }

    public static function getAgentsForSchool(int $schoolId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT a.id, a.nom, a.postnom, a.prenom, a.salaire_de_base,
                    r.titre_role AS fonction, p.devise_salaire
             FROM agents a
             LEFT JOIN roles_administration r ON r.id = a.role_id
             LEFT JOIN agents_paie_preferences p ON p.agent_id = a.id
             WHERE a.ecole_id = :ecole_id
             ORDER BY a.nom, a.postnom, a.prenom'
        );
        $stmt->execute([':ecole_id' => $schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCurrencies(): array
    {
        $stmt = Database::getConnection()->query(
            'SELECT code, libelle FROM devises WHERE actif = 1 ORDER BY code'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCashAccounts(int $schoolId): array
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

    public static function getSlipsForPeriod(int $schoolId, int $month, int $year): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT p.*, a.nom, a.postnom, a.prenom, r.titre_role AS fonction,
                    cb.nom_compte, u.nom_complet AS payeur_nom, sc.statut AS sortie_statut
             FROM paies_agents p
             INNER JOIN agents a ON a.id = p.agent_id
             LEFT JOIN roles_administration r ON r.id = a.role_id
             LEFT JOIN caisses_banques cb ON cb.id = p.caisse_banque_id
             LEFT JOIN utilisateurs u ON u.id = p.paid_by_user_id
             LEFT JOIN sorties_caisse sc
                ON sc.paie_agent_id = p.id
               AND sc.id = (SELECT MAX(s2.id) FROM sorties_caisse s2 WHERE s2.paie_agent_id = p.id)
             WHERE p.ecole_id = :ecole_id AND p.mois = :mois AND p.annee = :annee
             ORDER BY a.nom, a.postnom, a.prenom'
        );
        $stmt->execute([
            ':ecole_id' => $schoolId,
            ':mois' => $month,
            ':annee' => $year,
        ]);
        $slips = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$slips) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($slips as $index => $slip) {
            $placeholder = ':paie_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = (int) $slip['id'];
        }
        $lineStmt = $db->prepare(
            'SELECT paie_agent_id, type_ligne, libelle, montant
             FROM paies_agents_lignes
             WHERE paie_agent_id IN (' . implode(',', $placeholders) . ')
             ORDER BY id'
        );
        $lineStmt->execute($params);
        $linesBySlip = [];
        foreach ($lineStmt->fetchAll(PDO::FETCH_ASSOC) as $line) {
            $linesBySlip[(int) $line['paie_agent_id']][] = $line;
        }
        foreach ($slips as &$slip) {
            $slip['lignes'] = $linesBySlip[(int) $slip['id']] ?? [];
        }
        unset($slip);

        return $slips;
    }

    public static function updateSalary(
        int $schoolId,
        int $agentId,
        float $baseSalary,
        string $currency,
        int $userId
    ): void {
        self::validateCurrency($currency);
        if ($baseSalary < 0 || $baseSalary > 9999999999.99) {
            throw new \InvalidArgumentException('Le salaire de base doit être compris entre 0 et 9 999 999 999,99.');
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $agentStmt = $db->prepare(
                'SELECT id FROM agents WHERE id = :agent_id AND ecole_id = :ecole_id FOR UPDATE'
            );
            $agentStmt->execute([':agent_id' => $agentId, ':ecole_id' => $schoolId]);
            if (!$agentStmt->fetchColumn()) {
                throw new \InvalidArgumentException('Cet agent n’appartient pas à votre école.');
            }

            $salaryStmt = $db->prepare(
                'UPDATE agents SET salaire_de_base = :salaire WHERE id = :agent_id AND ecole_id = :ecole_id'
            );
            $salaryStmt->execute([
                ':salaire' => number_format($baseSalary, 2, '.', ''),
                ':agent_id' => $agentId,
                ':ecole_id' => $schoolId,
            ]);
            $preferenceStmt = $db->prepare(
                'INSERT INTO agents_paie_preferences (agent_id, devise_salaire, updated_by_user_id)
                 VALUES (:agent_id, :devise, :user_id)
                 ON DUPLICATE KEY UPDATE devise_salaire = VALUES(devise_salaire),
                    updated_by_user_id = VALUES(updated_by_user_id)'
            );
            $preferenceStmt->execute([
                ':agent_id' => $agentId,
                ':devise' => $currency,
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

    public static function updateAccountCurrency(
        int $schoolId,
        int $accountId,
        string $currency,
        int $userId,
        bool $balanceAlreadyInCurrency
    ): void {
        self::validateCurrency($currency);
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $accountStmt = $db->prepare(
                'SELECT cb.solde_actuel, COALESCE(cbd.devise, \'USD\') AS devise_actuelle
                 FROM caisses_banques cb
                 LEFT JOIN caisses_banques_devises cbd ON cbd.caisse_banque_id = cb.id
                 WHERE cb.id = :id AND cb.ecole_id = :ecole_id
                 FOR UPDATE'
            );
            $accountStmt->execute([':id' => $accountId, ':ecole_id' => $schoolId]);
            $account = $accountStmt->fetch(PDO::FETCH_ASSOC);
            if (!$account) {
                throw new \InvalidArgumentException('Cette caisse ou banque n’appartient pas à votre école.');
            }
            if ((string) $account['devise_actuelle'] !== $currency) {
                if (CashOutflow::hasPendingForAccount($accountId)) {
                    throw new \InvalidArgumentException('La devise d’un compte ayant des sorties en attente ne peut pas être changée.');
                }
                $historyStmt = $db->prepare(
                    'SELECT COUNT(*) FROM paies_agents WHERE caisse_banque_id = :id'
                );
                $historyStmt->execute([':id' => $accountId]);
                if ((int) $historyStmt->fetchColumn() > 0) {
                    throw new \InvalidArgumentException('La devise d’un compte ayant un historique de paie ne peut pas être changée.');
                }
                if ((float) $account['solde_actuel'] !== 0.0 && !$balanceAlreadyInCurrency) {
                    throw new \InvalidArgumentException('Confirmez que le solde actuel est déjà dans la devise choisie. Aucun montant ne sera converti.');
                }
            }

            $stmt = $db->prepare(
                'INSERT INTO caisses_banques_devises (caisse_banque_id, devise, updated_by_user_id)
                 VALUES (:account_id, :devise, :user_id)
                 ON DUPLICATE KEY UPDATE devise = VALUES(devise),
                    updated_by_user_id = VALUES(updated_by_user_id)'
            );
            $stmt->execute([
                ':account_id' => $accountId,
                ':devise' => $currency,
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

    public static function createSlip(
        int $schoolId,
        int $agentId,
        int $month,
        int $year,
        array $primes,
        array $retenues,
        int $userId
    ): void {
        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            throw new \InvalidArgumentException('La période de paie est invalide.');
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $agentStmt = $db->prepare(
                'SELECT a.salaire_de_base, p.devise_salaire
                 FROM agents a
                 LEFT JOIN agents_paie_preferences p ON p.agent_id = a.id
                 WHERE a.id = :agent_id AND a.ecole_id = :ecole_id
                 FOR UPDATE'
            );
            $agentStmt->execute([':agent_id' => $agentId, ':ecole_id' => $schoolId]);
            $agent = $agentStmt->fetch(PDO::FETCH_ASSOC);
            if (!$agent) {
                throw new \InvalidArgumentException('Cet agent n’appartient pas à votre école.');
            }
            $currency = strtoupper(trim((string) ($agent['devise_salaire'] ?? '')));
            if ($currency === '') {
                throw new \InvalidArgumentException('Définissez d’abord le salaire de base et la devise de cet agent.');
            }
            self::validateCurrency($currency);

            $existingStmt = $db->prepare(
                'SELECT COUNT(*) FROM paies_agents
                 WHERE agent_id = :agent_id AND mois = :mois AND annee = :annee'
            );
            $existingStmt->execute([
                ':agent_id' => $agentId,
                ':mois' => $month,
                ':annee' => $year,
            ]);
            if ((int) $existingStmt->fetchColumn() > 0) {
                throw new \InvalidArgumentException('Une fiche de paie existe déjà pour cet agent et cette période.');
            }

            $baseSalary = (float) $agent['salaire_de_base'];
            $primeLines = self::validateLines($primes);
            $deductionLines = self::validateLines($retenues);
            $totalPrimes = array_sum(array_column($primeLines, 'montant'));
            $totalDeductions = array_sum(array_column($deductionLines, 'montant'));
            $netPayable = round($baseSalary + $totalPrimes - $totalDeductions, 2);
            if ($netPayable <= 0) {
                throw new \InvalidArgumentException('Le net à payer doit être supérieur à zéro.');
            }

            $stmt = $db->prepare(
                'INSERT INTO paies_agents (
                    agent_id, ecole_id, mois, annee, devise, salaire_base_historique,
                    total_primes, total_retenues, net_a_payer, created_by_user_id
                 ) VALUES (
                    :agent_id, :ecole_id, :mois, :annee, :devise, :salaire_base,
                    :total_primes, :total_retenues, :net_a_payer, :user_id
                 )'
            );
            $stmt->execute([
                ':agent_id' => $agentId,
                ':ecole_id' => $schoolId,
                ':mois' => $month,
                ':annee' => $year,
                ':devise' => $currency,
                ':salaire_base' => number_format($baseSalary, 2, '.', ''),
                ':total_primes' => number_format($totalPrimes, 2, '.', ''),
                ':total_retenues' => number_format($totalDeductions, 2, '.', ''),
                ':net_a_payer' => number_format($netPayable, 2, '.', ''),
                ':user_id' => $userId,
            ]);
            $slipId = (int) $db->lastInsertId();
            self::saveLines($db, $slipId, $primeLines, $deductionLines);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function updateSlip(int $schoolId, int $slipId, array $primes, array $retenues): void
    {
        $primeLines = self::validateLines($primes);
        $deductionLines = self::validateLines($retenues);
        $totalPrimes = array_sum(array_column($primeLines, 'montant'));
        $totalDeductions = array_sum(array_column($deductionLines, 'montant'));

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                'SELECT salaire_base_historique, statut_paiement
                 FROM paies_agents
                 WHERE id = :id AND ecole_id = :ecole_id
                 FOR UPDATE'
            );
            $stmt->execute([':id' => $slipId, ':ecole_id' => $schoolId]);
            $slip = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$slip) {
                throw new \InvalidArgumentException('Cette fiche de paie est introuvable dans votre école.');
            }
            if ($slip['statut_paiement'] !== 'En attente') {
                throw new \InvalidArgumentException('Une fiche déjà payée ne peut plus être modifiée.');
            }

            $netPayable = round((float) $slip['salaire_base_historique'] + $totalPrimes - $totalDeductions, 2);
            if ($netPayable <= 0) {
                throw new \InvalidArgumentException('Le net à payer doit être supérieur à zéro.');
            }

            $updateStmt = $db->prepare(
                'UPDATE paies_agents
                 SET total_primes = :total_primes, total_retenues = :total_retenues, net_a_payer = :net_a_payer
                 WHERE id = :id AND ecole_id = :ecole_id AND statut_paiement = \'En attente\''
            );
            $updateStmt->execute([
                ':total_primes' => number_format($totalPrimes, 2, '.', ''),
                ':total_retenues' => number_format($totalDeductions, 2, '.', ''),
                ':net_a_payer' => number_format($netPayable, 2, '.', ''),
                ':id' => $slipId,
                ':ecole_id' => $schoolId,
            ]);
            $db->prepare('DELETE FROM paies_agents_lignes WHERE paie_agent_id = :id')
                ->execute([':id' => $slipId]);
            self::saveLines($db, $slipId, $primeLines, $deductionLines);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function paySlip(int $schoolId, int $slipId, int $accountId, int $userId): void
    {
        CashOutflow::requestPayrollPayment($schoolId, $slipId, $accountId, $userId);
    }

    private static function validateCurrency(string $currency): void
    {
        $currency = strtoupper(trim($currency));
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) FROM devises WHERE code = :code AND actif = 1'
        );
        $stmt->execute([':code' => $currency]);
        if ($currency === '' || (int) $stmt->fetchColumn() !== 1) {
            throw new \InvalidArgumentException('Sélectionnez une devise active.');
        }
    }

    private static function validateLines(array $lines): array
    {
        $validLines = [];
        foreach ($lines as $line) {
            if (!\is_array($line)) {
                throw new \InvalidArgumentException('Une prime ou retenue est invalide.');
            }
            $amountValue = $line['montant'] ?? '';
            $labelValue = $line['libelle'] ?? '';
            if (!\is_scalar($amountValue) || !\is_scalar($labelValue)) {
                throw new \InvalidArgumentException('Une prime ou retenue contient une valeur invalide.');
            }
            $amountText = trim((string) $amountValue);
            if ($amountText === '') {
                continue;
            }
            if (!is_numeric($amountText)) {
                throw new \InvalidArgumentException('Les montants des primes et retenues doivent être numériques.');
            }
            $amount = round((float) $amountText, 2);
            $label = trim((string) $labelValue);
            if ($amount <= 0 || $amount > 9999999999.99 || $label === '' || mb_strlen($label, 'UTF-8') > 150) {
                throw new \InvalidArgumentException('Chaque prime ou retenue doit avoir un libellé et un montant positif valide.');
            }
            $validLines[] = ['libelle' => $label, 'montant' => $amount];
        }

        return $validLines;
    }

    private static function saveLines(PDO $db, int $slipId, array $primes, array $retenues): void
    {
        $stmt = $db->prepare(
            'INSERT INTO paies_agents_lignes (paie_agent_id, type_ligne, libelle, montant)
             VALUES (:paie_id, :type, :libelle, :montant)'
        );
        foreach (['prime' => $primes, 'retenue' => $retenues] as $type => $lines) {
            foreach ($lines as $line) {
                $stmt->execute([
                    ':paie_id' => $slipId,
                    ':type' => $type,
                    ':libelle' => $line['libelle'],
                    ':montant' => number_format($line['montant'], 2, '.', ''),
                ]);
            }
        }
    }
}
