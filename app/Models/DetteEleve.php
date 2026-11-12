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
            return $feeClassId > 0
                ? $feeClassId === $classId
                : ($scopeId !== null && $scopeId === $classId);
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

    public static function getFeeStatusForStudentYear(int $eleveId, int $ecoleId, int $yearId): array
    {
        if ($eleveId <= 0 || $ecoleId <= 0 || $yearId <= 0) {
            return [];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT fs.id AS frais_id, fs.type_frais, fs.devise, fs.scope,
                    CASE
                      WHEN COALESCE(fs.scope, 'class') = 'class' THEN COALESCE(fee_class.nom_classe, 'Classe')
                      WHEN fs.scope = 'option' THEN COALESCE(fee_option.nom_option, 'Option')
                      WHEN fs.scope = 'section' THEN COALESCE(fee_section.nom_section, 'Section')
                      WHEN fs.scope = 'school' THEN 'École entière'
                      ELSE 'N/A'
                    END AS scope_label,
                    COALESCE(d.montant_initial, fs.montant_total) AS montant_initial,
                    COALESCE(p.montant_paye, 0) AS montant_paye,
                    COALESCE(x.montant_exonere, 0) AS montant_exonere,
                    GREATEST(
                      0,
                      COALESCE(d.montant_initial, fs.montant_total)
                        - COALESCE(p.montant_paye, 0)
                        - COALESCE(x.montant_exonere, 0)
                    ) AS montant_restant
             FROM inscriptions i
             INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = :class_school_id
             INNER JOIN frais_scolaires fs
               ON fs.annee_scolaire_id = i.annee_scolaire_id
              AND fs.ecole_id = c.ecole_id
              AND (
                    COALESCE(fs.scope, 'class') = 'school'
                    OR (COALESCE(fs.scope, 'class') = 'class'
                        AND (fs.classe_id = c.id OR fs.scope_id = c.id))
                    OR (fs.scope = 'section' AND fs.scope_id = c.section_id)
                    OR (fs.scope = 'option' AND fs.scope_id = c.option_id)
              )
             LEFT JOIN classes fee_class
               ON fee_class.id = COALESCE(fs.classe_id, CASE WHEN COALESCE(fs.scope, 'class') = 'class' THEN fs.scope_id END)
             LEFT JOIN options fee_option ON fs.scope = 'option' AND fee_option.id = fs.scope_id
             LEFT JOIN sections fee_section ON fs.scope = 'section' AND fee_section.id = fs.scope_id
             LEFT JOIN (
                 SELECT frais_id, MAX(montant_initial) AS montant_initial
                 FROM dettes_eleves
                 WHERE eleve_id = :debt_student_id AND annee_scolaire_id = :debt_year_id
                 GROUP BY frais_id
             ) d ON d.frais_id = fs.id
             LEFT JOIN (
                 SELECT ece.frais_id, SUM(ece.montant) AS montant_paye
                 FROM ecritures_comptables_eleves ece
                 INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                 WHERE ce.eleve_id = :payment_student_id
                   AND ce.annee_scolaire_id = :payment_year_id
                   AND ece.type_mouvement = 'CREDIT'
                   AND ece.annulee_at IS NULL
                   AND NULLIF(TRIM(ece.reference_recu), '') IS NOT NULL
                   AND ece.reference_recu NOT LIKE 'REG-FRAIS-%'
                 GROUP BY ece.frais_id
             ) p ON p.frais_id = fs.id
             LEFT JOIN (
                 SELECT x.frais_id, SUM(x.montant) AS montant_exonere
                 FROM exonerations x
                 INNER JOIN frais_scolaires xfs
                   ON xfs.id = x.frais_id AND UPPER(x.devise) = UPPER(xfs.devise)
                 WHERE x.eleve_id = :exoneration_student_id AND x.statut = 'Approuvee'
                 GROUP BY x.frais_id
             ) x ON x.frais_id = fs.id
             WHERE i.eleve_id = :student_id
               AND i.annee_scolaire_id = :year_id
               AND i.id = (
                   SELECT latest.id FROM inscriptions latest
                   WHERE latest.eleve_id = i.eleve_id AND latest.annee_scolaire_id = i.annee_scolaire_id
                   ORDER BY latest.date_inscription DESC, latest.id DESC LIMIT 1
               )
             ORDER BY fs.type_frais ASC"
        );
        $stmt->execute([
            ':class_school_id' => $ecoleId,
            ':debt_student_id' => $eleveId,
            ':debt_year_id' => $yearId,
            ':payment_student_id' => $eleveId,
            ':payment_year_id' => $yearId,
            ':exoneration_student_id' => $eleveId,
            ':student_id' => $eleveId,
            ':year_id' => $yearId,
        ]);
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
            $currency = strtoupper(trim((string) ($fee['devise'] ?? 'USD'))) ?: 'USD';
            $remaining = max(0.0, (float) ($fee['montant_restant'] ?? 0));
            if ($remaining <= 0) {
                continue;
            }

            $result[$currency] = round(($result[$currency] ?? 0.0) + $remaining, 2);
        }

        ksort($result);
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
            try {
                $pstmt = $db->prepare(
                    'SELECT SUM(ece.montant) AS paid
                     FROM ecritures_comptables_eleves ece
                     INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id
                     WHERE ce.eleve_id = :eleve AND ece.frais_id = :frais
                       AND ece.type_mouvement = :type AND ece.annulee_at IS NULL
                       AND NULLIF(TRIM(ece.reference_recu), \'\') IS NOT NULL
                       AND ece.reference_recu NOT LIKE \'REG-FRAIS-%\''
                );
                $pstmt->execute([':eleve' => $eleveId, ':frais' => $feeId, ':type' => 'CREDIT']);
                $paidAccounting = (float) ($pstmt->fetchColumn() ?: 0);
            } catch (\Throwable $e) {
            }

            $approvedExoneration = 0.0;
            try {
                $exonerationStmt = $db->prepare(
                    'SELECT COALESCE(SUM(montant), 0) FROM exonerations
                     WHERE eleve_id = :eleve AND frais_id = :frais AND statut = :statut
                       AND UPPER(devise) = :devise'
                );
                $exonerationStmt->execute([
                    ':eleve' => $eleveId,
                    ':frais' => $feeId,
                    ':statut' => 'Approuvee',
                    ':devise' => $devise,
                ]);
                $approvedExoneration = (float) ($exonerationStmt->fetchColumn() ?: 0);
            } catch (\Throwable $e) {
            }

            $remaining = max(0.0, $feeAmount - $paidAccounting - $approvedExoneration);
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
