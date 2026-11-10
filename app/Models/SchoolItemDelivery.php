<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SchoolItemDelivery
{
    private static bool $tableEnsured = false;

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS livraisons_objets_scolaires (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                annee_scolaire_id INT NOT NULL,
                eleve_id INT NOT NULL,
                frais_id INT NOT NULL,
                livre_par_user_id INT NULL,
                date_livraison DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_objet_livraison_eleve_frais (ecole_id, annee_scolaire_id, eleve_id, frais_id),
                KEY idx_objet_livraison_date (ecole_id, annee_scolaire_id, date_livraison)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }

    public static function getPurchasedItems(int $schoolId, int $yearId): array
    {
        if ($schoolId <= 0 || $yearId <= 0) {
            return [];
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT d.eleve_id, d.frais_id, d.annee_scolaire_id,
                    MAX(d.montant_initial) AS montant_initial,
                    COALESCE((
                        SELECT SUM(ece.montant)
                        FROM ecritures_comptables_eleves ece
                        INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
                        WHERE ce.eleve_id = d.eleve_id
                          AND ce.annee_scolaire_id = d.annee_scolaire_id
                          AND ece.frais_id = d.frais_id
                          AND ece.type_mouvement = \'CREDIT\'
                          AND ece.annulee_at IS NULL
                          AND COALESCE(ece.reference_recu, \'\') NOT LIKE \'REG-FRAIS-%\'
                    ), 0) AS total_ledger_paye,
                    COALESCE((
                        SELECT SUM(pe.montant_paye)
                        FROM paiements_eleves pe
                        WHERE pe.eleve_id = d.eleve_id AND pe.frais_id = d.frais_id
                    ), 0) AS total_legacy_paye,
                    e.matricule, e.nom, e.postnom, e.prenom,
                    c.nom_classe, o.nom_option, sec.nom_section,
                    f.type_frais, f.devise,
                    l.date_livraison, l.livre_par_user_id,
                    u.nom_complet AS livre_par_nom
             FROM dettes_eleves d
             INNER JOIN frais_scolaires f ON f.id = d.frais_id
               AND f.ecole_id = :fee_school AND f.scope = \'school\'
               AND f.annee_scolaire_id = :fee_year
               AND f.encodage LIKE \'ART-SCOL-%\'
             INNER JOIN eleves e ON e.id = d.eleve_id AND e.ecole_id = :student_school
             INNER JOIN inscriptions i ON i.id = (
                 SELECT latest.id FROM inscriptions latest
                 WHERE latest.eleve_id = d.eleve_id
                   AND latest.annee_scolaire_id = d.annee_scolaire_id
                 ORDER BY latest.date_inscription DESC, latest.id DESC LIMIT 1
             )
             INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = :class_school
             LEFT JOIN options o ON o.id = c.option_id
             LEFT JOIN sections sec ON sec.id = c.section_id
             LEFT JOIN livraisons_objets_scolaires l
               ON l.ecole_id = :delivery_school
              AND l.annee_scolaire_id = d.annee_scolaire_id
              AND l.eleve_id = d.eleve_id AND l.frais_id = d.frais_id
             LEFT JOIN utilisateurs u ON u.id = l.livre_par_user_id
             WHERE d.annee_scolaire_id = :debt_year
             GROUP BY d.eleve_id, d.frais_id, d.annee_scolaire_id,
                      e.matricule, e.nom, e.postnom, e.prenom,
                      c.nom_classe, o.nom_option, sec.nom_section, f.type_frais, f.devise,
                      l.date_livraison, l.livre_par_user_id, u.nom_complet
             HAVING GREATEST(total_ledger_paye, total_legacy_paye) >= MAX(d.montant_initial) - 0.005
             ORDER BY l.date_livraison IS NULL DESC, c.nom_classe ASC,
                      e.nom ASC, e.postnom ASC, e.prenom ASC, f.type_frais ASC'
        );
        $stmt->execute([
            ':fee_school' => $schoolId,
            ':fee_year' => $yearId,
            ':student_school' => $schoolId,
            ':class_school' => $schoolId,
            ':delivery_school' => $schoolId,
            ':debt_year' => $yearId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function setDelivered(
        int $schoolId,
        int $yearId,
        int $studentId,
        int $feeId,
        int $userId,
        bool $delivered
    ): void {
        $purchasedItems = self::getPurchasedItems($schoolId, $yearId);
        $isPurchased = false;
        foreach ($purchasedItems as $item) {
            if ((int) $item['eleve_id'] === $studentId && (int) $item['frais_id'] === $feeId) {
                $isPurchased = true;
                break;
            }
        }
        if (!$isPurchased) {
            throw new \InvalidArgumentException('Cet objet n’est pas entièrement payé par cet élève.');
        }

        $db = Database::getConnection();
        if (!$delivered) {
            $stmt = $db->prepare(
                'DELETE FROM livraisons_objets_scolaires
                 WHERE ecole_id = :school_id AND annee_scolaire_id = :year_id
                   AND eleve_id = :student_id AND frais_id = :fee_id'
            );
            $stmt->execute([
                ':school_id' => $schoolId,
                ':year_id' => $yearId,
                ':student_id' => $studentId,
                ':fee_id' => $feeId,
            ]);
            return;
        }

        $stmt = $db->prepare(
            'INSERT INTO livraisons_objets_scolaires
                (ecole_id, annee_scolaire_id, eleve_id, frais_id, livre_par_user_id, date_livraison)
             VALUES (:school_id, :year_id, :student_id, :fee_id, :user_id, NOW())
             ON DUPLICATE KEY UPDATE livre_par_user_id = VALUES(livre_par_user_id), date_livraison = VALUES(date_livraison)'
        );
        $stmt->execute([
            ':school_id' => $schoolId,
            ':year_id' => $yearId,
            ':student_id' => $studentId,
            ':fee_id' => $feeId,
            ':user_id' => $userId > 0 ? $userId : null,
        ]);
    }
}