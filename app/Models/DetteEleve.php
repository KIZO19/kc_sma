<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class DetteEleve
{
    public static function getLatestEnrollmentForEleve(int $eleveId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT i.classe_id, i.annee_scolaire_id, c.ecole_id, c.option_id, c.section_id
             FROM inscriptions i
             INNER JOIN classes c ON c.id = i.classe_id
             WHERE i.eleve_id = :eleve
             ORDER BY i.date_inscription DESC, i.id DESC
             LIMIT 1'
        );
        $stmt->execute([':eleve' => $eleveId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function isFeeApplicableToEleve(int $eleveId, array $fee): bool
    {
        return self::isFeeApplicableToEnrollment($fee, self::getLatestEnrollmentForEleve($eleveId));
    }

    public static function isFeeApplicableToEnrollment(array $fee, ?array $enrollment): bool
    {
        if (
            $enrollment
            && !empty($fee['annee_scolaire_id'])
            && !empty($enrollment['annee_scolaire_id'])
            && (int) $fee['annee_scolaire_id'] !== (int) $enrollment['annee_scolaire_id']
        ) {
            return false;
        }

        $scope = (string) ($fee['scope'] ?? 'class');
        $scopeId = isset($fee['scope_id']) && $fee['scope_id'] !== '' ? (int) $fee['scope_id'] : null;
        $classId = (int) ($enrollment['classe_id'] ?? 0);

        if ($scope === 'school') {
            return true;
        }
        if (!$enrollment) {
            return false;
        }

        if ($scope === 'class') {
            $feeClassId = (int) ($fee['classe_id'] ?? 0);
            return ($feeClassId > 0 && $feeClassId === $classId)
                || ($scopeId !== null && $scopeId === $classId);
        }
        if ($scope === 'option') {
            return $scopeId !== null && $scopeId === (int) ($enrollment['option_id'] ?? 0);
        }
        if ($scope === 'section') {
            return $scopeId !== null && $scopeId === (int) ($enrollment['section_id'] ?? 0);
        }

        return false;
    }

    public static function create(int $eleveId, int $fraisId, int $anneeScolaireId, float $montant, string $devise): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO dettes_eleves (eleve_id, frais_id, annee_scolaire_id, montant_initial, montant_restant, devise)
             VALUES (:eleve_id, :frais_id, :annee_scolaire_id, :montant_initial, :montant_restant, :devise)'
        );

        return (bool) $stmt->execute([
            ':eleve_id' => $eleveId,
            ':frais_id' => $fraisId,
            ':annee_scolaire_id' => $anneeScolaireId,
            ':montant_initial' => $montant,
            ':montant_restant' => $montant,
            ':devise' => $devise,
        ]);
    }

    public static function getOutstandingByEleve(int $eleveId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT d.*, fs.type_frais, fs.montant_total, fs.devise, fs.scope, fs.scope_id,
                    c.nom_classe,
                    ao.nom_option AS option_scope_name,
                    ss.nom_section AS section_scope_name,
                    s.annee AS annee_scolaire,
                    CASE
                      WHEN fs.scope = 'class' THEN COALESCE(c.nom_classe, 'N/A')
                      WHEN fs.scope = 'option' THEN COALESCE(ao.nom_option, 'N/A')
                      WHEN fs.scope = 'section' THEN COALESCE(ss.nom_section, 'N/A')
                      WHEN fs.scope = 'school' THEN 'École entière'
                      ELSE 'N/A'
                    END AS scope_label
             FROM dettes_eleves d
             INNER JOIN frais_scolaires fs ON fs.id = d.frais_id
             LEFT JOIN classes c ON c.id = fs.classe_id
             LEFT JOIN options ao ON (fs.scope = 'option' AND ao.id = fs.scope_id)
             LEFT JOIN sections ss ON (fs.scope = 'section' AND ss.id = fs.scope_id)
             LEFT JOIN annees_scolaires s ON s.id = d.annee_scolaire_id
             WHERE d.eleve_id = :eleve_id AND d.montant_restant > 0
             ORDER BY d.date_creation DESC"
        );
        $stmt->execute([':eleve_id' => $eleveId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalOutstandingByEleve(int $eleveId): float
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT SUM(montant_restant) AS total_outstanding FROM dettes_eleves WHERE eleve_id = :eleve_id');
        $stmt->execute([':eleve_id' => $eleveId]);
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public static function findByEleve(int $eleveId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM dettes_eleves WHERE eleve_id = :eleve_id ORDER BY id DESC');
        $stmt->execute([':eleve_id' => $eleveId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalOutstandingGroupedByDevise(int $eleveId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT COALESCE(devise, "USD") AS devise, SUM(montant_restant) AS total FROM dettes_eleves WHERE eleve_id = :eleve_id GROUP BY COALESCE(devise, "USD")');
        $stmt->execute([':eleve_id' => $eleveId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $r) {
            $dev = strtoupper(trim($r['devise'] ?? 'USD')) ?: 'USD';
            $result[$dev] = (float) ($r['total'] ?? 0);
        }
        return $result;
    }

    public static function computeOutstandingFromApplicableFees(int $eleveId): array
    {
        $result = [];
        foreach (self::getOutstandingFeeDetails($eleveId) as $fee) {
            $currency = $fee['devise'];
            $result[$currency] = ($result[$currency] ?? 0.0) + $fee['montant_restant'];
        }
        return $result;
    }

    public static function getOutstandingFeeDetails(int $eleveId): array
    {
        $db = Database::getConnection();
        $enrollment = self::getLatestEnrollmentForEleve($eleveId);
        $yearId = (int) ($enrollment['annee_scolaire_id'] ?? 0);
        $ecoleId = (int) ($enrollment['ecole_id'] ?? 0);
        if ($ecoleId <= 0) {
            try {
                $eleve = \App\Models\Eleve::findById($eleveId);
                if ($eleve && !empty($eleve['ecole_id'])) {
                    $ecoleId = (int) $eleve['ecole_id'];
                }
            } catch (\Throwable $e) {
            }
        }

        if ($yearId <= 0 && $ecoleId > 0) {
            try {
                $activeYear = \App\Models\AnneeScolaire::getActiveBySchool($ecoleId);
                if ($activeYear) {
                    $yearId = (int) ($activeYear['id'] ?? 0);
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            $allFees = $ecoleId > 0 ? \App\Models\FraisScolaire::getAllBySchool($ecoleId) : \App\Models\FraisScolaire::getAll();
        } catch (\Throwable $e) {
            $allFees = [];
        }

        $feesById = [];
        foreach ($allFees as $fee) {
            $feesById[(int) ($fee['id'] ?? 0)] = $fee;
        }

        $details = [];
        $debtsByFee = [];
        $debtStmt = $db->prepare(
            'SELECT frais_id, COALESCE(devise, \'USD\') AS devise, SUM(montant_restant) AS montant_restant
             FROM dettes_eleves
             WHERE eleve_id = :eleve
             GROUP BY frais_id, COALESCE(devise, \'USD\')'
        );
        $debtStmt->execute([':eleve' => $eleveId]);
        foreach ($debtStmt->fetchAll(PDO::FETCH_ASSOC) as $debt) {
            $feeId = (int) $debt['frais_id'];
            $devise = strtoupper(trim((string) ($debt['devise'] ?? 'USD'))) ?: 'USD';
            $remaining = max(0.0, (float) ($debt['montant_restant'] ?? 0));
            $debtsByFee[$feeId] = true;
            if ($remaining > 0) {
                $details[] = [
                    'frais_id' => $feeId,
                    'type_frais' => (string) ($feesById[$feeId]['type_frais'] ?? 'Frais scolaire'),
                    'montant_total' => (float) ($feesById[$feeId]['montant_total'] ?? 0),
                    'montant_restant' => $remaining,
                    'devise' => $devise,
                ];
            }
        }

        foreach ($allFees as $f) {
            $feeYearId = isset($f['annee_scolaire_id']) ? (int) $f['annee_scolaire_id'] : 0;
            if ($yearId > 0 && $feeYearId > 0 && $feeYearId !== $yearId) {
                continue;
            }
            if (!self::isFeeApplicableToEnrollment($f, $enrollment)) {
                continue;
            }

            $feeId = (int) ($f['id'] ?? 0);
            if (isset($debtsByFee[$feeId])) {
                continue;
            }
            $feeAmount = (float) ($f['montant_total'] ?? 0);
            $devise = strtoupper(trim($f['devise'] ?? 'USD')) ?: 'USD';

            $paidAccounting = 0.0;
            $paidLegacy = 0.0;
            try {
                $pstmt = $db->prepare('SELECT SUM(ece.montant) AS paid FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id WHERE ce.eleve_id = :eleve AND ece.frais_id = :frais AND ece.type_mouvement = :type');
                $pstmt->execute([':eleve' => $eleveId, ':frais' => $feeId, ':type' => 'CREDIT']);
                $paidAccounting = (float) ($pstmt->fetchColumn() ?: 0);
            } catch (\Throwable $e) {
            }
            try {
                $pstmt2 = $db->prepare('SELECT SUM(pe.montant_paye) AS paid FROM paiements_eleves pe WHERE pe.eleve_id = :eleve AND pe.frais_id = :frais');
                $pstmt2->execute([':eleve' => $eleveId, ':frais' => $feeId]);
                $paidLegacy = (float) ($pstmt2->fetchColumn() ?: 0);
            } catch (\Throwable $e) {
            }

            $remaining = max(0.0, $feeAmount - max($paidAccounting, $paidLegacy));
            if ($remaining <= 0) {
                continue;
            }

            $details[] = [
                'frais_id' => $feeId,
                'type_frais' => (string) ($f['type_frais'] ?? 'Frais scolaire'),
                'montant_total' => $feeAmount,
                'montant_restant' => $remaining,
                'devise' => $devise,
            ];
        }

        return $details;
    }

    public static function findByEleveAndFrais(int $eleveId, int $fraisId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM dettes_eleves WHERE eleve_id = :eleve_id AND frais_id = :frais_id LIMIT 1');
        $stmt->execute([':eleve_id' => $eleveId, ':frais_id' => $fraisId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function reduceRemaining(int $detteId, float $montant): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE dettes_eleves SET montant_restant = GREATEST(0, montant_restant - :montant) WHERE id = :id');
        return (bool) $stmt->execute([':montant' => $montant, ':id' => $detteId]);
    }
}
