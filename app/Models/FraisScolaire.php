<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class FraisScolaire
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM frais_scolaires ORDER BY annee_scolaire_id DESC, type_frais ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllBySchool(int $ecoleId, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare(
                "SELECT f.*, 
                        c.nom_classe, c.section_id AS class_section_id, co.nom_option AS class_option_name,
                        ao.nom_option AS option_scope_name,
                        ss.nom_section AS section_scope_name,
                        s.annee AS annee_scolaire,
                        CASE 
                          WHEN f.scope = 'class' THEN COALESCE(CONCAT(c.nom_classe, CASE WHEN co.nom_option IS NOT NULL AND co.nom_option <> '' THEN CONCAT(' · ', co.nom_option) ELSE '' END), 'N/A')
                          WHEN f.scope = 'option' THEN COALESCE(ao.nom_option, 'N/A')
                          WHEN f.scope = 'section' THEN COALESCE(ss.nom_section, 'N/A')
                          WHEN f.scope = 'school' THEN 'Toutes les options'
                          ELSE 'N/A' END AS scope_label
                 FROM frais_scolaires f
                 LEFT JOIN classes c ON c.id = f.classe_id
                 LEFT JOIN options co ON co.id = c.option_id
                 LEFT JOIN options ao ON (f.scope = 'option' AND ao.id = f.scope_id)
                 LEFT JOIN sections ss ON (f.scope = 'section' AND ss.id = f.scope_id)
                 LEFT JOIN annees_scolaires s ON s.id = f.annee_scolaire_id
                 WHERE (
                     f.ecole_id = :fee_school
                     OR c.ecole_id = :class_school
                     OR (f.scope = 'option' AND EXISTS (
                         SELECT 1 FROM classes sc WHERE sc.option_id = f.scope_id AND sc.ecole_id = :option_school
                     ))
                     OR (f.scope = 'section' AND EXISTS (
                         SELECT 1 FROM classes ss2 WHERE ss2.section_id = f.scope_id AND ss2.ecole_id = :section_school
                     ))
                 )
                 ORDER BY s.annee DESC, c.nom_classe ASC, f.type_frais ASC"
            );
            $stmt->execute([
                ':fee_school' => $ecoleId,
                ':class_school' => $ecoleId,
                ':option_school' => $ecoleId,
                ':section_school' => $ecoleId,
            ]);
            $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $sectionId === null ? $fees : self::filterFeesBySection($fees, $ecoleId, $sectionId);
        } catch (\PDOException $e) {
            // Fallback for older schema without 'scope' column: return basic fees for school's classes
            error_log('FraisScolaire::getAllBySchool fallback due to DB error: ' . $e->getMessage());
            $stmt = $db->prepare(
                "SELECT f.*, c.nom_classe, c.section_id AS class_section_id, co.nom_option AS class_option_name,
                        s.annee AS annee_scolaire,
                        CASE WHEN co.nom_option IS NOT NULL AND co.nom_option <> ''
                          THEN CONCAT(c.nom_classe, ' · ', co.nom_option)
                          ELSE COALESCE(c.nom_classe, 'N/A') END AS scope_label
                 FROM frais_scolaires f
                 LEFT JOIN classes c ON c.id = f.classe_id
                 LEFT JOIN options co ON co.id = c.option_id
                 LEFT JOIN annees_scolaires s ON s.id = f.annee_scolaire_id
                 WHERE c.ecole_id = :class_school OR f.ecole_id = :fee_school
                 ORDER BY s.annee DESC, c.nom_classe ASC, f.type_frais ASC"
            );
            $stmt->execute([':class_school' => $ecoleId, ':fee_school' => $ecoleId]);
            $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $sectionId === null ? $fees : self::filterFeesBySection($fees, $ecoleId, $sectionId);
        }
    }

    private static function filterFeesBySection(array $fees, int $ecoleId, int $sectionId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT DISTINCT option_id FROM classes WHERE ecole_id = :ecole_id AND section_id = :section_id AND option_id IS NOT NULL'
        );
        $stmt->execute([':ecole_id' => $ecoleId, ':section_id' => $sectionId]);
        $optionIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        return array_values(array_filter($fees, static function (array $fee) use ($sectionId, $optionIds): bool {
            $scope = (string) ($fee['scope'] ?? '');
            $scopeId = (int) ($fee['scope_id'] ?? 0);
            if ($scope === 'school' || ($scope === '' && empty($fee['classe_id']))) {
                return true;
            }
            if ($scope === 'section') {
                return $scopeId === $sectionId;
            }
            if ($scope === 'class') {
                return (int) ($fee['class_section_id'] ?? 0) === $sectionId;
            }
            if ($scope === 'option') {
                return in_array($scopeId, $optionIds, true);
            }

            return (int) ($fee['class_section_id'] ?? 0) === $sectionId;
        }));
    }

    public static function create(array $data)
    {
        $db = Database::getConnection();
        // support optional ecole_id and encodage fields (newer schema)
        if (array_key_exists('ecole_id', $data)) {
            $stmt = $db->prepare(
                'INSERT INTO frais_scolaires (classe_id, type_frais, montant_total, annee_scolaire_id, devise, scope, scope_id, ecole_id, encodage)'
                . ' VALUES (:classe_id, :type_frais, :montant_total, :annee_scolaire_id, :devise, :scope, :scope_id, :ecole_id, :encodage)'
            );
        } else {
            $stmt = $db->prepare(
                'INSERT INTO frais_scolaires (classe_id, type_frais, montant_total, annee_scolaire_id, devise, scope, scope_id, encodage)'
                . ' VALUES (:classe_id, :type_frais, :montant_total, :annee_scolaire_id, :devise, :scope, :scope_id, :encodage)'
            );
        }

        $params = [
            ':classe_id' => $data['classe_id'] ?? null,
            ':type_frais' => $data['type_frais'],
            ':montant_total' => $data['montant_total'],
            ':annee_scolaire_id' => $data['annee_scolaire_id'],
            ':devise' => $data['devise'],
            ':scope' => $data['scope'] ?? 'class',
            ':scope_id' => $data['scope_id'] ?? null,
            ':encodage' => $data['encodage'] ?? null,
        ];
        if (array_key_exists('ecole_id', $data)) {
            $params[':ecole_id'] = $data['ecole_id'] ?? null;
        }

        $ok = $stmt->execute($params);

        if ($ok) {
            return (int) $db->lastInsertId();
        }

        return false;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT f.*, c.nom_classe, co.nom_option AS class_option_name,
                    s.annee AS annee_scolaire
             FROM frais_scolaires f
             LEFT JOIN classes c ON c.id = f.classe_id
             LEFT JOIN options co ON co.id = c.option_id
             LEFT JOIN annees_scolaires s ON s.id = f.annee_scolaire_id
             WHERE f.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByIdAndSchool(int $id, int $ecoleId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT f.*, c.nom_classe, class_option.nom_option AS class_option_name,
                    o.nom_option AS option_scope_name,
                    sec.nom_section AS section_scope_name, s.annee AS annee_scolaire,
                    CASE
                      WHEN f.scope = 'class' THEN COALESCE(CONCAT(c.nom_classe, CASE WHEN class_option.nom_option IS NOT NULL AND class_option.nom_option <> '' THEN CONCAT(' · ', class_option.nom_option) ELSE '' END), 'N/A')
                      WHEN f.scope = 'option' THEN COALESCE(o.nom_option, 'N/A')
                      WHEN f.scope = 'section' THEN COALESCE(sec.nom_section, 'N/A')
                      WHEN f.scope = 'school' THEN 'École entière'
                      ELSE 'N/A'
                    END AS scope_label "
            . 'FROM frais_scolaires f '
            . 'LEFT JOIN classes c ON c.id = f.classe_id '
            . 'LEFT JOIN options class_option ON class_option.id = c.option_id '
            . 'LEFT JOIN options o ON f.scope = \'option\' AND o.id = f.scope_id '
            . 'LEFT JOIN sections sec ON f.scope = \'section\' AND sec.id = f.scope_id '
            . 'LEFT JOIN annees_scolaires s ON s.id = f.annee_scolaire_id '
            . 'WHERE f.id = :id AND ('
            . 'f.ecole_id = :fee_school OR c.ecole_id = :class_school OR '
            . 'EXISTS (SELECT 1 FROM classes sc WHERE sc.ecole_id = :option_school AND sc.option_id = f.scope_id AND f.scope = \'option\') OR '
            . 'EXISTS (SELECT 1 FROM classes ss WHERE ss.ecole_id = :section_school AND ss.section_id = f.scope_id AND f.scope = \'section\')) '
            . 'LIMIT 1'
        );
        $stmt->execute([
            ':id' => $id,
            ':fee_school' => $ecoleId,
            ':class_school' => $ecoleId,
            ':option_school' => $ecoleId,
            ':section_school' => $ecoleId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getStudentsPaymentStatus(int $feeId, int $ecoleId, int $yearId, string $currency): array
    {
        if ($feeId <= 0 || $ecoleId <= 0 || $yearId <= 0) {
            return [];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT e.id AS eleve_id, e.matricule, e.nom, e.postnom, e.prenom,
                    c.nom_classe, o.nom_option,
                    COALESCE(d.montant_initial, fs.montant_total) AS montant_initial,
                    COALESCE(p.montant_paye, 0) AS montant_paye,
                    COALESCE(x.montant_exonere, 0) AS montant_exonere,
                    GREATEST(
                      0,
                      COALESCE(d.montant_initial, fs.montant_total)
                        - COALESCE(p.montant_paye, 0)
                        - COALESCE(x.montant_exonere, 0)
                    ) AS montant_restant
             FROM frais_scolaires fs
             INNER JOIN inscriptions i
               ON i.annee_scolaire_id = fs.annee_scolaire_id
              AND i.id = (
                  SELECT latest.id
                  FROM inscriptions latest
                  WHERE latest.eleve_id = i.eleve_id
                    AND latest.annee_scolaire_id = i.annee_scolaire_id
                  ORDER BY latest.date_inscription DESC, latest.id DESC
                  LIMIT 1
              )
             INNER JOIN eleves e ON e.id = i.eleve_id
             INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = e.ecole_id
             LEFT JOIN options o ON o.id = c.option_id
             LEFT JOIN (
                 SELECT eleve_id, frais_id, annee_scolaire_id, MAX(montant_initial) AS montant_initial
                 FROM dettes_eleves
                 WHERE frais_id = :debt_fee_id AND annee_scolaire_id = :debt_year_id
                 GROUP BY eleve_id, frais_id, annee_scolaire_id
             ) d ON d.eleve_id = e.id AND d.frais_id = fs.id
                AND d.annee_scolaire_id = fs.annee_scolaire_id
             LEFT JOIN (
                 SELECT ce.eleve_id, ece.frais_id, SUM(ece.montant) AS montant_paye
                 FROM ecritures_comptables_eleves ece
                 INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                 WHERE ece.frais_id = :payment_fee_id
                   AND ece.type_mouvement = 'CREDIT'
                   AND ece.annulee_at IS NULL
                   AND NULLIF(TRIM(ece.reference_recu), '') IS NOT NULL
                   AND ece.reference_recu NOT LIKE 'REG-FRAIS-%'
                 GROUP BY ce.eleve_id, ece.frais_id
             ) p ON p.eleve_id = e.id AND p.frais_id = fs.id
             LEFT JOIN (
                 SELECT eleve_id, frais_id, SUM(montant) AS montant_exonere
                 FROM exonerations
                 WHERE frais_id = :exoneration_fee_id
                   AND statut = 'Approuvee'
                   AND UPPER(devise) = UPPER(:exoneration_currency)
                 GROUP BY eleve_id, frais_id
             ) x ON x.eleve_id = e.id AND x.frais_id = fs.id
             WHERE fs.id = :fee_id
               AND fs.annee_scolaire_id = :year_id
               AND e.ecole_id = :student_school_id
               AND (
                    COALESCE(fs.scope, 'class') = 'school'
                    OR (COALESCE(fs.scope, 'class') = 'class'
                        AND (fs.classe_id = c.id OR fs.scope_id = c.id))
                    OR (fs.scope = 'section' AND fs.scope_id = c.section_id)
                    OR (fs.scope = 'option' AND fs.scope_id = c.option_id)
               )
             ORDER BY c.nom_classe ASC, e.nom ASC, e.postnom ASC, e.prenom ASC"
        );
        $stmt->execute([
            ':debt_fee_id' => $feeId,
            ':debt_year_id' => $yearId,
            ':payment_fee_id' => $feeId,
            ':exoneration_fee_id' => $feeId,
            ':exoneration_currency' => $currency,
            ':fee_id' => $feeId,
            ':year_id' => $yearId,
            ':student_school_id' => $ecoleId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function existsEncodage(string $encodage, int $ecoleId, ?int $excludeId = null): bool
    {
        $db = Database::getConnection();
        // Try the full-scoped check first (joins to classes/options/sections). If DB schema is older
        // or some tables/columns are missing, fall back to a simpler check to avoid blocking inserts.
        $params = [':encodage' => $encodage, ':ecole_id' => $ecoleId];
        try {
            $sql = 'SELECT f.id FROM frais_scolaires f LEFT JOIN classes c ON c.id = f.classe_id WHERE f.encodage = :encodage AND (f.ecole_id = :fee_school OR c.ecole_id = :class_school OR EXISTS (SELECT 1 FROM classes sc WHERE sc.option_id = f.scope_id AND sc.ecole_id = :option_school AND f.scope = \'option\') OR EXISTS (SELECT 1 FROM classes ss WHERE ss.section_id = f.scope_id AND ss.ecole_id = :section_school AND f.scope = \'section\'))';
            if ($excludeId !== null) {
                $sql .= ' AND f.id != :excludeId';
                $params[':excludeId'] = $excludeId;
            }
            unset($params[':ecole_id']);
            $params[':fee_school'] = $ecoleId;
            $params[':class_school'] = $ecoleId;
            $params[':option_school'] = $ecoleId;
            $params[':section_school'] = $ecoleId;
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (bool) $row;
        } catch (\Throwable $e) {
            // log and fall back to simpler check (by encodage and ecole_id)
            error_log('FraisScolaire::existsEncodage fallback due to error: ' . $e->getMessage());
            $fallbackSql = 'SELECT f.id FROM frais_scolaires f LEFT JOIN classes c ON c.id = f.classe_id WHERE f.encodage = :encodage AND (f.ecole_id = :ecole_id OR c.ecole_id = :ecole_id)';
            if ($excludeId !== null) {
                $fallbackSql .= ' AND id != :excludeId';
                $params[':excludeId'] = $excludeId;
            }
            $stmt = $db->prepare($fallbackSql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (bool) $row;
        }
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE frais_scolaires SET classe_id = :classe_id, type_frais = :type_frais, montant_total = :montant_total, annee_scolaire_id = :annee_scolaire_id, devise = :devise, scope = :scope, scope_id = :scope_id, ecole_id = :ecole_id, encodage = :encodage '
            . 'WHERE id = :id AND (ecole_id = :owner_school OR '
            . 'EXISTS (SELECT 1 FROM classes c WHERE c.id = frais_scolaires.classe_id AND c.ecole_id = :class_school) OR '
            . 'EXISTS (SELECT 1 FROM classes sc WHERE sc.option_id = frais_scolaires.scope_id AND sc.ecole_id = :option_school AND frais_scolaires.scope = \'option\') OR '
            . 'EXISTS (SELECT 1 FROM classes ss WHERE ss.section_id = frais_scolaires.scope_id AND ss.ecole_id = :section_school AND frais_scolaires.scope = \'section\'))'
        );

        $params = [
            ':classe_id' => $data['classe_id'] ?? null,
            ':type_frais' => $data['type_frais'],
            ':montant_total' => $data['montant_total'],
            ':annee_scolaire_id' => $data['annee_scolaire_id'],
            ':devise' => $data['devise'],
            ':scope' => $data['scope'] ?? 'class',
            ':scope_id' => $data['scope_id'] ?? null,
            ':ecole_id' => $data['ecole_id'] ?? null,
            ':encodage' => $data['encodage'] ?? null,
            ':id' => $id,
            ':owner_school' => (int) ($data['ecole_id'] ?? 0),
            ':class_school' => (int) ($data['ecole_id'] ?? 0),
            ':option_school' => (int) ($data['ecole_id'] ?? 0),
            ':section_school' => (int) ($data['ecole_id'] ?? 0),
        ];

        return (bool) $stmt->execute($params);
    }

    public static function deleteByIdAndSchool(int $id, int $ecoleId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'DELETE FROM frais_scolaires '
            . 'WHERE id = :id AND ('
            . 'ecole_id = :fee_school OR '
            . 'EXISTS (SELECT 1 FROM classes c WHERE c.id = frais_scolaires.classe_id AND c.ecole_id = :class_school) OR '
            . 'EXISTS (SELECT 1 FROM classes sc WHERE sc.option_id = frais_scolaires.scope_id AND sc.ecole_id = :option_school AND frais_scolaires.scope = \'option\') OR '
            . 'EXISTS (SELECT 1 FROM classes ss WHERE ss.section_id = frais_scolaires.scope_id AND ss.ecole_id = :section_school AND frais_scolaires.scope = \'section\'))'
        );
        $stmt->execute([
            ':id' => $id,
            ':fee_school' => $ecoleId,
            ':class_school' => $ecoleId,
            ':option_school' => $ecoleId,
            ':section_school' => $ecoleId,
        ]);

        return $stmt->rowCount() === 1;
    }
}
