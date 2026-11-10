<?php

namespace App\Models;

use App\Core\Database;
use App\Models\AnneeScolaire;
use App\Models\DetteEleve;
use App\Models\FraisScolaire;
use PDO;

class Exoneration
{
    public static function create(array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO exonerations (ecole_id, eleve_id, frais_id, montant, devise, motif, statut, demandeur_id)
             VALUES (:ecole_id, :eleve_id, :frais_id, :montant, :devise, :motif, :statut, :demandeur_id)'
        );

        return (bool) $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':eleve_id' => $data['eleve_id'],
            ':frais_id' => $data['frais_id'],
            ':montant' => $data['montant'],
            ':devise' => $data['devise'],
            ':motif' => $data['motif'],
            ':statut' => 'En_attente',
            ':demandeur_id' => $data['demandeur_id'],
        ]);
    }

    public static function getAllBySchool(int $ecoleId, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        $sql =
                'SELECT x.*, e.nom, e.postnom, e.prenom, e.matricule,
                    f.type_frais, f.devise AS frais_devise,
                    u.nom_complet AS demandeur_nom,
                    v.nom_complet AS validateur_nom
             FROM exonerations x
             INNER JOIN eleves e ON e.id = x.eleve_id
             INNER JOIN frais_scolaires f ON f.id = x.frais_id
             LEFT JOIN utilisateurs u ON u.id = x.demandeur_id
             LEFT JOIN utilisateurs v ON v.id = x.validateur_id
             WHERE x.ecole_id = :ecole_id';
        $params = [':ecole_id' => $ecoleId];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
                . 'WHERE i.eleve_id = x.eleve_id AND c.ecole_id = :section_school AND c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' ORDER BY x.created_at DESC, x.id DESC');
        $stmt->execute($params);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $financialByStudent = self::getFinancialSummaryBySchool($ecoleId, $sectionId);
        foreach ($requests as &$request) {
            $request['financial_summary'] = $financialByStudent[(int) $request['eleve_id']] ?? [];
        }
        unset($request);
        return $requests;
    }

    public static function getFinancialSummaryBySchool(int $ecoleId, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT fee_debts.eleve_id, fee_debts.devise,
                    SUM(fee_debts.montant_initial) AS montant_initial,
                    SUM(fee_debts.montant_paye) AS montant_paye,
                    SUM(fee_debts.montant_exonere) AS montant_exonere,
                    SUM(GREATEST(0, fee_debts.montant_initial - fee_debts.montant_paye - fee_debts.montant_exonere)) AS montant_restant
             FROM (
                 SELECT d.eleve_id, d.frais_id, UPPER(COALESCE(d.devise, \'USD\')) AS devise,
                        MAX(d.montant_initial) AS montant_initial,
                        GREATEST(
                            COALESCE((SELECT SUM(ece.montant) FROM ecritures_comptables_eleves ece
                                      INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                                      WHERE ce.eleve_id = d.eleve_id AND ece.frais_id = d.frais_id
                                        AND ece.type_mouvement = \'CREDIT\' AND ece.annulee_at IS NULL), 0),
                            COALESCE((SELECT SUM(pe.montant_paye) FROM paiements_eleves pe
                                      WHERE pe.eleve_id = d.eleve_id AND pe.frais_id = d.frais_id), 0)
                        ) AS montant_paye,
                        COALESCE((SELECT SUM(x.montant) FROM exonerations x
                                  WHERE x.eleve_id = d.eleve_id AND x.frais_id = d.frais_id
                                    AND x.statut = \'Approuvee\'
                                    AND UPPER(x.devise) = UPPER(COALESCE(d.devise, \'USD\'))), 0) AS montant_exonere
                 FROM dettes_eleves d
                 INNER JOIN eleves e ON e.id = d.eleve_id AND e.ecole_id = :school
                 INNER JOIN annees_scolaires a ON a.id = d.annee_scolaire_id AND a.ecole_id = e.ecole_id AND a.est_active = 1
                 GROUP BY d.eleve_id, d.frais_id, UPPER(COALESCE(d.devise, \'USD\'))
             ) fee_debts
             WHERE (:scope_none = 1 OR EXISTS (
                 SELECT 1 FROM inscriptions si
                 INNER JOIN classes sc ON sc.id = si.classe_id
                 WHERE si.eleve_id = fee_debts.eleve_id
                   AND sc.ecole_id = :scope_school AND sc.section_id = :scope_section
             ))
             GROUP BY fee_debts.eleve_id, fee_debts.devise'
        );
        $stmt->execute([
            ':school' => $ecoleId,
            ':scope_none' => $sectionId === null ? 1 : 0,
            ':scope_school' => $ecoleId,
            ':scope_section' => $sectionId ?? 0,
        ]);

        $summary = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $studentId = (int) $row['eleve_id'];
            $currency = strtoupper((string) $row['devise']);
            $summary[$studentId][$currency] = [
                'initial' => (float) $row['montant_initial'],
                'paid' => (float) $row['montant_paye'],
                'exonerated' => (float) $row['montant_exonere'],
                'remaining' => (float) $row['montant_restant'],
            ];
        }
        return $summary;
    }

    public static function getAvailableFeesForStudent(int $eleveId, int $ecoleId): array
    {
        $enrollment = DetteEleve::getLatestEnrollmentForEleve($eleveId);
        $activeYear = AnneeScolaire::getActiveBySchool($ecoleId);
        if (
            !$enrollment
            || !$activeYear
            || (int) ($enrollment['ecole_id'] ?? 0) !== $ecoleId
            || (int) ($enrollment['annee_scolaire_id'] ?? 0) !== (int) ($activeYear['id'] ?? 0)
        ) {
            return [];
        }

        $availableFees = [];
        foreach (FraisScolaire::getAllBySchool($ecoleId) as $fee) {
            if (
                (int) ($fee['annee_scolaire_id'] ?? 0) !== (int) $activeYear['id']
                || !DetteEleve::isFeeApplicableToEnrollment($fee, $enrollment)
            ) {
                continue;
            }

            $fee['montant_restant'] = self::getRemainingAmount(
                $eleveId,
                (int) $fee['id'],
                (float) $fee['montant_total'],
                (string) ($fee['devise'] ?? 'USD')
            );
            if ($fee['montant_restant'] > 0.005) {
                $availableFees[] = $fee;
            }
        }
        return $availableFees;
    }

    private static function getRemainingAmount(int $eleveId, int $fraisId, float $feeTotal, string $devise): float
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT GREATEST(0,
                COALESCE((SELECT MAX(d.montant_initial) FROM dettes_eleves d
                          WHERE d.eleve_id = :debt_student AND d.frais_id = :debt_fee), :fee_total)
                - GREATEST(
                    COALESCE((SELECT SUM(ece.montant) FROM ecritures_comptables_eleves ece
                              INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                              WHERE ce.eleve_id = :ledger_student AND ece.frais_id = :ledger_fee
                                AND ece.type_mouvement = \'CREDIT\' AND ece.annulee_at IS NULL), 0),
                    COALESCE((SELECT SUM(pe.montant_paye) FROM paiements_eleves pe
                              WHERE pe.eleve_id = :legacy_student AND pe.frais_id = :legacy_fee), 0)
                )
                - COALESCE((SELECT SUM(x.montant) FROM exonerations x
                            WHERE x.eleve_id = :exemption_student AND x.frais_id = :exemption_fee
                              AND x.statut = \'Approuvee\' AND UPPER(x.devise) = :currency), 0)
            ) AS montant_restant'
        );
        $stmt->execute([
            ':debt_student' => $eleveId,
            ':debt_fee' => $fraisId,
            ':fee_total' => $feeTotal,
            ':ledger_student' => $eleveId,
            ':ledger_fee' => $fraisId,
            ':legacy_student' => $eleveId,
            ':legacy_fee' => $fraisId,
            ':exemption_student' => $eleveId,
            ':exemption_fee' => $fraisId,
            ':currency' => strtoupper(trim($devise)),
        ]);
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public static function decide(int $id, int $ecoleId, int $validateurId, string $status, string $commentaire, ?int $sectionId = null): bool
    {
        if (!in_array($status, ['Approuvee', 'Refusee'], true) || $ecoleId <= 0 || $validateurId <= 0) {
            return false;
        }

        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $sql = 'SELECT * FROM exonerations
                 WHERE id = :id AND ecole_id = :ecole_id AND statut = :statut';
            $params = [':id' => $id, ':ecole_id' => $ecoleId, ':statut' => 'En_attente'];
            if ($sectionId !== null) {
                $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
                    . 'WHERE i.eleve_id = exonerations.eleve_id AND c.ecole_id = :section_school AND c.section_id = :section_id)';
                $params[':section_school'] = $ecoleId;
                $params[':section_id'] = $sectionId;
            }
            $stmt = $db->prepare($sql . ' LIMIT 1 FOR UPDATE');
            $stmt->execute($params);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$request || (int) $request['demandeur_id'] === $validateurId) {
                $db->rollBack();
                return false;
            }

            if ($status === 'Approuvee') {
                $studentLock = $db->prepare('SELECT id FROM eleves WHERE id = :id AND ecole_id = :ecole_id FOR UPDATE');
                $studentLock->execute([':id' => (int) $request['eleve_id'], ':ecole_id' => $ecoleId]);
                if (!$studentLock->fetchColumn()) {
                    $db->rollBack();
                    return false;
                }

                $fee = FraisScolaire::findByIdAndSchool((int) $request['frais_id'], $ecoleId);
                $availableFee = null;
                foreach (self::getAvailableFeesForStudent((int) $request['eleve_id'], $ecoleId) as $candidate) {
                    if ((int) $candidate['id'] === (int) $request['frais_id']) {
                        $availableFee = $candidate;
                        break;
                    }
                }
                if (
                    !$fee
                    || !$availableFee
                    || strtoupper((string) $request['devise']) !== strtoupper((string) ($fee['devise'] ?? 'USD'))
                    || (float) $request['montant'] > (float) $availableFee['montant_restant'] + 0.005
                ) {
                    $db->rollBack();
                    return false;
                }

                $enrollment = DetteEleve::getLatestEnrollmentForEleve((int) $request['eleve_id']);
                $debtStmt = $db->prepare(
                    'SELECT id FROM dettes_eleves
                     WHERE eleve_id = :eleve_id AND frais_id = :frais_id AND annee_scolaire_id = :year_id
                     ORDER BY id ASC LIMIT 1 FOR UPDATE'
                );
                $debtStmt->execute([
                    ':eleve_id' => $request['eleve_id'],
                    ':frais_id' => $request['frais_id'],
                    ':year_id' => (int) ($enrollment['annee_scolaire_id'] ?? 0),
                ]);
                $debtId = (int) ($debtStmt->fetchColumn() ?: 0);
                $newRemaining = max(0, (float) $availableFee['montant_restant'] - (float) $request['montant']);
                if ($debtId > 0) {
                    $reduce = $db->prepare(
                        'UPDATE dettes_eleves SET montant_restant = :remaining WHERE id = :id'
                    );
                    $reduce->execute([':remaining' => $newRemaining, ':id' => $debtId]);
                } else {
                    $insert = $db->prepare(
                        'INSERT INTO dettes_eleves
                            (eleve_id, frais_id, annee_scolaire_id, montant_initial, montant_restant, devise)
                         VALUES (:eleve_id, :frais_id, :year_id, :initial, :remaining, :devise)'
                    );
                    $insert->execute([
                        ':eleve_id' => $request['eleve_id'],
                        ':frais_id' => $request['frais_id'],
                        ':year_id' => (int) ($enrollment['annee_scolaire_id'] ?? 0),
                        ':initial' => (float) $availableFee['montant_total'],
                        ':remaining' => $newRemaining,
                        ':devise' => $fee['devise'] ?? 'USD',
                    ]);
                }
            }

            $update = $db->prepare(
                'UPDATE exonerations
                 SET statut = :statut, commentaire = :commentaire, validateur_id = :validateur_id, decided_at = NOW()
                 WHERE id = :id'
            );
            $update->execute([
                ':statut' => $status,
                ':commentaire' => $commentaire !== '' ? $commentaire : null,
                ':validateur_id' => $validateurId,
                ':id' => $id,
            ]);

            $db->commit();
            return $update->rowCount() > 0;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Exoneration::decide failed: ' . $e->getMessage());
            return false;
        }
    }
}
