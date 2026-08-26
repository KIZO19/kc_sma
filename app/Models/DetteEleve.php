<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class DetteEleve
{
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
        $db = Database::getConnection();
        // determine student's latest class
        $stmt = $db->prepare('SELECT i.classe_id FROM inscriptions i WHERE i.eleve_id = :eleve ORDER BY i.date_inscription DESC LIMIT 1');
        $stmt->execute([':eleve' => $eleveId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $classeId = (int) ($row['classe_id'] ?? 0);

        $classRec = null;
        $optionId = 0;
        $sectionId = 0;
        $ecoleId = 0;
        try {
            if ($classeId > 0) {
                $classRec = \App\Models\Classe::findById($classeId);
                $optionId = (int) ($classRec['option_id'] ?? 0);
                $sectionId = (int) ($classRec['section_id'] ?? 0);
                $ecoleId = (int) ($classRec['ecole_id'] ?? 0);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // fallback: try eleve record for ecole
        try {
            $eleve = \App\Models\Eleve::findById($eleveId);
            if ($eleve && !empty($eleve['ecole_id'])) {
                $ecoleId = (int) $eleve['ecole_id'];
            }
        } catch (\Throwable $e) {
        }

        // fetch fees for school (if ecoleId 0, get all fees)
        try {
            $allFees = $ecoleId > 0 ? \App\Models\FraisScolaire::getAllBySchool($ecoleId) : \App\Models\FraisScolaire::getAll();
        } catch (\Throwable $e) {
            $allFees = [];
        }

        $result = [];
        foreach ($allFees as $f) {
            $apply = false;
            $scope = $f['scope'] ?? 'class';
            $scopeId = isset($f['scope_id']) ? (int) $f['scope_id'] : null;
            $feeClasseId = isset($f['classe_id']) ? (int) $f['classe_id'] : 0;

            if ($scope === 'school') {
                $apply = true;
            } elseif ($scope === 'class') {
                if ($feeClasseId > 0 && $classeId > 0 && $feeClasseId === $classeId) $apply = true;
                if ($scopeId !== null && $classeId > 0 && $scopeId === $classeId) $apply = true;
            } elseif ($scope === 'option') {
                if ($scopeId !== null && $optionId > 0 && $scopeId === $optionId) $apply = true;
            } elseif ($scope === 'section') {
                if ($scopeId !== null && $sectionId > 0 && $scopeId === $sectionId) $apply = true;
            }

            if (!$apply) continue;

            $feeId = (int) ($f['id'] ?? 0);
            $feeAmount = (float) ($f['montant_total'] ?? 0);
            $devise = strtoupper(trim($f['devise'] ?? 'USD')) ?: 'USD';

            // sum payments for this fee and student
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

            // A payment may exist in both the accounting and legacy tables.
            // Use the larger total instead of adding both copies.
            $remaining = max(0.0, $feeAmount - max($paidAccounting, $paidLegacy));
            if ($remaining <= 0) continue;
            if (!isset($result[$devise])) $result[$devise] = 0.0;
            $result[$devise] += $remaining;
        }

        return $result;
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
