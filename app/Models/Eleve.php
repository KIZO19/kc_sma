<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Eleve
{
    public static function getChildrenForParent(int $parentId, int $schoolId): array
    {
        if ($parentId <= 0 || $schoolId <= 0) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT e.id, e.nom, e.postnom, e.prenom, e.matricule,
                    (SELECT c.nom_classe
                     FROM inscriptions i
                     INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = :class_school
                     WHERE i.eleve_id = e.id
                     ORDER BY i.date_inscription DESC, i.id DESC
                     LIMIT 1) AS nom_classe,
                    (SELECT o.nom_option
                     FROM inscriptions i
                     INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = :option_school
                     LEFT JOIN options o ON o.id = c.option_id
                     WHERE i.eleve_id = e.id
                     ORDER BY i.date_inscription DESC, i.id DESC
                     LIMIT 1) AS nom_option
             FROM eleves e
             WHERE e.parent_id = :parent_id AND e.ecole_id = :school_id
             ORDER BY e.nom, e.postnom, e.prenom'
        );
        $stmt->execute([
            ':class_school' => $schoolId,
            ':option_school' => $schoolId,
            ':parent_id' => $parentId,
            ':school_id' => $schoolId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPaymentsForParent(int $parentId, int $schoolId): array
    {
        $paymentsByChild = [];
        foreach (self::getChildrenForParent($parentId, $schoolId) as $child) {
            $childId = (int) $child['id'];
            $payments = array_values(array_filter(
                self::getAccountingEntries($childId),
                static fn (array $entry): bool => strtoupper((string) ($entry['type_mouvement'] ?? '')) === 'CREDIT'
                    && empty($entry['annulee_at'])
                    && strpos((string) ($entry['reference_recu'] ?? ''), 'REG-FRAIS-') !== 0
            ));
            $totalByCurrency = [];
            foreach ($payments as $payment) {
                $currency = strtoupper(trim((string) ($payment['frais_devise'] ?? 'USD'))) ?: 'USD';
                $totalByCurrency[$currency] = ($totalByCurrency[$currency] ?? 0.0) + (float) ($payment['montant'] ?? 0);
            }
            ksort($totalByCurrency);
            $paymentsByChild[$childId] = [
                'payments' => $payments,
                'total_by_currency' => $totalByCurrency,
            ];
        }

        return $paymentsByChild;
    }

    public static function getOutstandingByChild(array $children): array
    {
        $outstandingByChild = [];
        foreach ($children as $child) {
            $childId = (int) ($child['id'] ?? 0);
            if ($childId <= 0) {
                continue;
            }

            $outstanding = array_filter(
                DetteEleve::getTotalOutstandingGroupedByDevise($childId),
                static fn ($amount): bool => (float) $amount > 0
            );
            if ($outstanding === []) {
                $outstanding = DetteEleve::computeOutstandingFromApplicableFees($childId);
            }
            ksort($outstanding);
            $outstandingByChild[$childId] = $outstanding;
        }

        return $outstandingByChild;
    }

    public static function findByMatricule(string $matricule, ?int $excludeId = null): ?array
    {
        $normalized = trim($matricule);
        if ($normalized === '') {
            return null;
        }

        $db = Database::getConnection();
        $sql = 'SELECT * FROM eleves WHERE LOWER(TRIM(matricule)) = LOWER(TRIM(:matricule))';
        $params = [':matricule' => $normalized];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function isMatriculeTaken(string $matricule, ?int $excludeId = null): bool
    {
        return self::findByMatricule($matricule, $excludeId) !== null;
    }

    public static function isValidMatriculeFormat(string $matricule): bool
    {
        return preg_match('/^[A-Z]{4}\d{5}$/', trim($matricule)) === 1;
    }

    public static function create(array $data): ?array
    {
        Ecole::assertSubscriptionCapacity((int) ($data['ecole_id'] ?? 0), 'students');
        $matricule = isset($data['matricule']) ? strtoupper(trim((string) $data['matricule'])) : '';
        if ($matricule !== '' && !self::isValidMatriculeFormat($matricule)) {
            throw new \InvalidArgumentException('Le matricule doit contenir 4 lettres majuscules suivies de 5 chiffres.');
        }
        if ($matricule !== '' && self::isMatriculeTaken($matricule)) {
            throw new \InvalidArgumentException('Le matricule est déjà utilisé.');
        }

        $db = Database::getConnection();
        $fields = ['matricule', 'nom', 'postnom', 'prenom', 'genre', 'lieu_naissance', 'nationalite', 'adresse', 'date_naissance', 'parent_id', 'nom_pere', 'nom_mere', 'province_origine', 'territoire', 'secteur', 'groupement', 'village', 'num_permanent', 'photo', 'statut_eleve'];
        $placeholders = [':matricule', ':nom', ':postnom', ':prenom', ':genre', ':lieu_naissance', ':nationalite', ':adresse', ':date_naissance', ':parent_id', ':nom_pere', ':nom_mere', ':province_origine', ':territoire', ':secteur', ':groupement', ':village', ':num_permanent', ':photo', ':statut_eleve'];
        if (array_key_exists('ecole_id', $data)) {
            $fields[] = 'ecole_id';
            $placeholders[] = ':ecole_id';
        }

        $stmt = $db->prepare('INSERT INTO eleves (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')');

        $params = [
            ':matricule' => $matricule !== '' ? $matricule : null,
            ':nom' => $data['nom'],
            ':postnom' => $data['postnom'],
            ':prenom' => $data['prenom'] ?? null,
            ':genre' => $data['genre'],
            ':lieu_naissance' => $data['lieu_naissance'] ?? null,
            ':nationalite' => $data['nationalite'] ?? 'CONGOLAISE',
            ':adresse' => $data['adresse'] ?? null,
            ':date_naissance' => $data['date_naissance'],
            ':parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
            ':nom_pere' => $data['nom_pere'] ?? null,
            ':nom_mere' => $data['nom_mere'] ?? null,
            ':province_origine' => $data['province_origine'] ?? null,
            ':territoire' => $data['territoire'] ?? null,
            ':secteur' => $data['secteur'] ?? null,
            ':groupement' => $data['groupement'] ?? null,
            ':village' => $data['village'] ?? null,
            ':num_permanent' => $data['num_permanent'] ?? null,
            ':photo' => $data['photo'] ?? null,
            ':statut_eleve' => $data['statut_eleve'] ?? 'inactif',
        ];

        if (array_key_exists('ecole_id', $data)) {
            $params[':ecole_id'] = !empty($data['ecole_id']) ? $data['ecole_id'] : null;
        }

        try {
            $stmt->execute($params);
        } catch (\PDOException $e) {
            if (stripos($e->getMessage(), 'Unknown column') !== false && stripos($e->getMessage(), 'ecole_id') !== false) {
                unset($params[':ecole_id']);
                $fields = array_filter($fields, fn($field) => $field !== 'ecole_id');
                $placeholders = array_filter($placeholders, fn($placeholder) => $placeholder !== ':ecole_id');
                $stmt = $db->prepare('INSERT INTO eleves (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')');
                $stmt->execute($params);
            } else {
                throw $e;
            }
        }

        $created = self::findById((int) $db->lastInsertId());
        if ($created) {
            try {
                self::createUserAccount((int) $created['id']);
            } catch (\Throwable $e) {
                error_log('Eleve::create user account error: ' . $e->getMessage());
            }
        }

        return $created;
    }

    public static function getPending(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.*, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . "WHERE e.statut_eleve = 'inactif' ORDER BY e.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPendingBySchool(int $ecoleId, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        $sql =
            'SELECT e.*, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . 'WHERE e.statut_eleve = :statut AND (e.ecole_id = :ecole_id OR p.ecole_id = :ecole_id OR EXISTS ('
            . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id))';
        $params = [':statut' => 'inactif', ':ecole_id' => $ecoleId];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions section_i '
                . 'INNER JOIN classes section_c ON section_c.id = section_i.classe_id '
                . 'WHERE section_i.eleve_id = e.id AND section_c.ecole_id = :section_school '
                . 'AND section_c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' ORDER BY e.id DESC');
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEnrolledBySchool(int $ecoleId, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        $sql =
            'SELECT e.*, p.nom_responsable AS parent_nom_responsable, '
            . 'p.telephone AS parent_telephone, p.email AS parent_email, '
            . 'c.id AS classe_id, c.nom_classe, c.section_id, s.nom_section, '
            . 'c.option_id, o.nom_option, i.annee_scolaire_id, a.annee AS annee_scolaire, i.date_inscription '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . 'LEFT JOIN inscriptions i ON i.id = ('
            . 'SELECT i2.id FROM inscriptions i2 '
            . 'INNER JOIN classes enrollment_class ON enrollment_class.id = i2.classe_id '
            . 'WHERE i2.eleve_id = e.id AND enrollment_class.ecole_id = :inscription_school '
            . 'ORDER BY i2.date_inscription DESC, i2.id DESC LIMIT 1) '
            . 'LEFT JOIN classes c ON c.id = i.classe_id '
            . 'LEFT JOIN sections s ON s.id = c.section_id '
            . 'LEFT JOIN options o ON o.id = c.option_id '
            . 'LEFT JOIN annees_scolaires a ON a.id = i.annee_scolaire_id '
            . 'WHERE e.statut_eleve = :statut AND (e.ecole_id = :ecole_id OR p.ecole_id = :ecole_id OR EXISTS ('
            . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id))';
        $params = [
            ':inscription_school' => $ecoleId,
            ':statut' => 'actif',
            ':ecole_id' => $ecoleId,
        ];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions section_i '
                . 'INNER JOIN classes section_c ON section_c.id = section_i.classe_id '
                . 'WHERE section_i.eleve_id = e.id AND section_c.ecole_id = :section_school '
                . 'AND section_c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC');
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.*, p.nom_responsable AS parent_nom_responsable, '
            . 'p.telephone AS parent_telephone, c.id AS classe_id, c.nom_classe, '
            . 'c.section_id, s.nom_section, c.option_id, o.nom_option, '
            . 'i.annee_scolaire_id, a.annee AS annee_scolaire, i.date_inscription '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . 'LEFT JOIN inscriptions i ON i.id = ('
            . 'SELECT i2.id FROM inscriptions i2 WHERE i2.eleve_id = e.id '
            . 'ORDER BY i2.date_inscription DESC, i2.id DESC LIMIT 1) '
            . 'LEFT JOIN classes c ON c.id = i.classe_id '
            . 'LEFT JOIN sections s ON s.id = c.section_id '
            . 'LEFT JOIN options o ON o.id = c.option_id '
            . 'LEFT JOIN annees_scolaires a ON a.id = i.annee_scolaire_id '
            . 'ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllBySchool(int $ecoleId, ?int $classeId = null, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        $sql =
            'SELECT DISTINCT e.*, p.nom_responsable AS parent_nom_responsable, '
            . 'p.telephone AS parent_telephone, c.id AS classe_id, c.nom_classe, '
            . 'c.section_id, s.nom_section, c.option_id, o.nom_option, '
            . 'i.annee_scolaire_id, a.annee AS annee_scolaire, i.date_inscription '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . 'LEFT JOIN inscriptions i ON i.id = ('
            . 'SELECT i2.id FROM inscriptions i2 WHERE i2.eleve_id = e.id '
            . 'AND i2.classe_id IN (SELECT c2.id FROM classes c2 WHERE c2.ecole_id = :inscription_school'
            . ($sectionId !== null ? ' AND c2.section_id = :inscription_section' : '') . ') '
            . 'ORDER BY i2.date_inscription DESC, i2.id DESC LIMIT 1) '
            . 'LEFT JOIN classes c ON c.id = i.classe_id '
            . 'LEFT JOIN sections s ON s.id = c.section_id '
            . 'LEFT JOIN options o ON o.id = c.option_id '
            . 'LEFT JOIN annees_scolaires a ON a.id = i.annee_scolaire_id '
            . 'WHERE (e.ecole_id = :ecole_id OR p.ecole_id = :ecole_id OR EXISTS ('
            . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id))';
        $params = [':ecole_id' => $ecoleId, ':inscription_school' => $ecoleId];

        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions section_i '
                . 'INNER JOIN classes section_c ON section_c.id = section_i.classe_id '
                . 'WHERE section_i.eleve_id = e.id AND section_c.ecole_id = :section_school '
                . 'AND section_c.section_id = :section_id)';
            $params[':inscription_section'] = $sectionId;
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }

        if ($classeId !== null && $classeId > 0) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions ic INNER JOIN classes cc ON ic.classe_id = cc.id '
                . 'WHERE ic.eleve_id = e.id AND ic.classe_id = :classe_id AND cc.ecole_id = :classe_ecole_id)';
            $params[':classe_id'] = $classeId;
            $params[':classe_ecole_id'] = $ecoleId;
        }

        $sql .= ' ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAccount(int $eleveId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM comptes_eleves WHERE eleve_id = :eleve ORDER BY annee_scolaire_id DESC LIMIT 1');
        $stmt->execute([':eleve' => $eleveId]);
        $compte = $stmt->fetch(PDO::FETCH_ASSOC);
        return $compte ?: null;
    }

    public static function getAccountForSchool(int $eleveId, int $ecoleId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ce.* '
            . 'FROM comptes_eleves ce '
            . 'INNER JOIN eleves e ON ce.eleve_id = e.id '
            . 'WHERE ce.eleve_id = :eleve AND (e.ecole_id = :ecole_id OR EXISTS ('
            . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id)) '
            . 'ORDER BY ce.annee_scolaire_id DESC LIMIT 1'
        );
        $stmt->execute([':eleve' => $eleveId, ':ecole_id' => $ecoleId]);
        $compte = $stmt->fetch(PDO::FETCH_ASSOC);
        return $compte ?: null;
    }

    public static function getEnrollmentHistoryForSchool(int $eleveId, int $ecoleId, ?int $sectionId = null): array
    {
        if ($eleveId <= 0 || $ecoleId <= 0) {
            return [];
        }

        $db = Database::getConnection();
        $sql = 'SELECT a.id AS annee_scolaire_id, a.annee, a.est_active,
                       i.id AS inscription_id, i.date_inscription,
                       c.id AS classe_id, c.nom_classe, c.section_id,
                       sec.nom_section, c.option_id, opt.nom_option,
                       CONCAT(c.nom_classe, CASE WHEN opt.nom_option IS NOT NULL AND opt.nom_option <> \'\' THEN CONCAT(\' · \', opt.nom_option) ELSE \'\' END) AS classe_label
                FROM inscriptions i
                INNER JOIN annees_scolaires a ON a.id = i.annee_scolaire_id AND a.ecole_id = :year_school
                INNER JOIN eleves e ON e.id = i.eleve_id
                INNER JOIN classes c ON c.id = i.classe_id AND c.ecole_id = :class_school
                LEFT JOIN sections sec ON sec.id = c.section_id
                LEFT JOIN options opt ON opt.id = c.option_id
                WHERE i.eleve_id = :eleve_id
                  AND e.ecole_id = :student_school
                  AND i.id = (
                      SELECT latest.id FROM inscriptions latest
                      WHERE latest.eleve_id = i.eleve_id AND latest.annee_scolaire_id = i.annee_scolaire_id
                      ORDER BY latest.date_inscription DESC, latest.id DESC LIMIT 1
                  )';
        $params = [
            ':year_school' => $ecoleId,
            ':class_school' => $ecoleId,
            ':eleve_id' => $eleveId,
            ':student_school' => $ecoleId,
        ];
        if ($sectionId !== null) {
            $sql .= ' AND c.section_id = :section_id';
            $params[':section_id'] = $sectionId;
        }
        $sql .= ' ORDER BY a.annee DESC, a.id DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAccountingEntries(int $eleveId, ?int $yearId = null): array
    {
        $db = Database::getConnection();
        $sql =
            'SELECT ece.*, fs.devise AS frais_devise, fs.type_frais AS frais_type_frais FROM ecritures_comptables_eleves ece '
            . 'INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id '
            . 'LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id '
            . 'WHERE ce.eleve_id = :eleve '
            . 'AND (UPPER(ece.type_mouvement) <> \'CREDIT\' '
            . 'OR NULLIF(TRIM(ece.reference_recu), \'\') IS NOT NULL)';
        $params = [':eleve' => $eleveId];
        if ($yearId !== null) {
            $sql .= ' AND ce.annee_scolaire_id = :year_id';
            $params[':year_id'] = $yearId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $seenKeys = [];
        $dedupedRecords = [];
        foreach ($records as $record) {
            $key = implode('|', [
                trim((string) ($record['date_operation'] ?? '')),
                trim((string) ($record['frais_id'] ?? '')),
                trim((string) ($record['montant'] ?? '')),
                trim((string) ($record['libelle'] ?? '')),
                trim((string) ($record['type_mouvement'] ?? '')),
                trim((string) ($record['reference_recu'] ?? '')),
            ]);

            if (isset($seenKeys[$key])) {
                continue;
            }

            $seenKeys[$key] = true;
            $dedupedRecords[] = $record;
        }

        $records = $dedupedRecords;

        usort($records, function ($a, $b) {
            $ta = strtotime($a['date_operation'] ?? '1970-01-01 00:00:00');
            $tb = strtotime($b['date_operation'] ?? '1970-01-01 00:00:00');
            return $tb <=> $ta;
        });

        return $records;
    }

    public static function getNotes(int $eleveId, ?int $yearId = null): array
    {
        $db = Database::getConnection();
        $sql =
            'SELECT n.*, ev.attribution_cours_id, ev.periode_id, ev.type_evaluation,
                    ev.date_evaluation, ev.ponderation_max, co.nom_cours, p.nom_periode
             FROM notes n
             LEFT JOIN evaluations ev ON n.evaluation_id = ev.id
             LEFT JOIN attribution_cours ac ON ac.id = ev.attribution_cours_id
             LEFT JOIN cours co ON co.id = ac.cours_id
             LEFT JOIN periodes p ON p.id = ev.periode_id '
            . 'WHERE n.eleve_id = :eleve';
        $params = [':eleve' => $eleveId];
        if ($yearId !== null) {
            $sql .= ' AND ac.annee_scolaire_id = :year_id';
            $params[':year_id'] = $yearId;
        }
        $stmt = $db->prepare($sql . ' ORDER BY ev.date_evaluation DESC');
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDiscipline(int $eleveId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT d.*, p.nom_periode, a.annee
             FROM discipline_eleves d
             LEFT JOIN periodes p ON p.id = d.periode_id
             LEFT JOIN annees_scolaires a ON a.id = p.annee_scolaire_id
             WHERE d.eleve_id = :eleve
             ORDER BY d.date_evenement DESC, d.id DESC'
        );
        $stmt->execute([':eleve' => $eleveId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDisciplinePeriodsForSchool(int $schoolId): array
    {
        if ($schoolId <= 0) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT p.id, p.nom_periode, p.cloturee, a.annee
             FROM periodes p
             INNER JOIN annees_scolaires a ON a.id = p.annee_scolaire_id
             WHERE a.ecole_id = :school_id
             ORDER BY a.est_active DESC, a.annee DESC, p.id ASC'
        );
        $stmt->execute([':school_id' => $schoolId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createDiscipline(int $studentId, int $schoolId, array $data): int
    {
        $values = self::validateDisciplineData($data);
        $db = Database::getConnection();
        self::assertDisciplineStudent($db, $studentId, $schoolId);
        self::assertDisciplinePeriod($db, $values['periode_id'], $schoolId);

        $stmt = $db->prepare(
            'INSERT INTO discipline_eleves (eleve_id, periode_id, faute, sanction, retrait_points, date_evenement)
             VALUES (:student_id, :period_id, :fault, :sanction, :points, :event_date)'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':period_id' => $values['periode_id'],
            ':fault' => $values['faute'],
            ':sanction' => $values['sanction'],
            ':points' => $values['retrait_points'],
            ':event_date' => $values['date_evenement'],
        ]);

        return (int) $db->lastInsertId();
    }

    public static function updateDiscipline(int $entryId, int $studentId, int $schoolId, array $data): void
    {
        $values = self::validateDisciplineData($data);
        $db = Database::getConnection();
        self::assertDisciplineStudent($db, $studentId, $schoolId);
        self::assertDisciplinePeriod($db, $values['periode_id'], $schoolId);
        self::assertDisciplineEntry($db, $entryId, $studentId);

        $stmt = $db->prepare(
            'UPDATE discipline_eleves
             SET periode_id = :period_id, faute = :fault, sanction = :sanction,
                 retrait_points = :points, date_evenement = :event_date
             WHERE id = :entry_id AND eleve_id = :student_id'
        );
        $stmt->execute([
            ':period_id' => $values['periode_id'],
            ':fault' => $values['faute'],
            ':sanction' => $values['sanction'],
            ':points' => $values['retrait_points'],
            ':event_date' => $values['date_evenement'],
            ':entry_id' => $entryId,
            ':student_id' => $studentId,
        ]);
    }

    public static function deleteDiscipline(int $entryId, int $studentId, int $schoolId): void
    {
        $db = Database::getConnection();
        self::assertDisciplineStudent($db, $studentId, $schoolId);
        self::assertDisciplineEntry($db, $entryId, $studentId);

        $stmt = $db->prepare(
            'DELETE FROM discipline_eleves WHERE id = :entry_id AND eleve_id = :student_id'
        );
        $stmt->execute([':entry_id' => $entryId, ':student_id' => $studentId]);
    }

    private static function validateDisciplineData(array $data): array
    {
        $faultInput = $data['faute'] ?? '';
        $sanctionInput = $data['sanction'] ?? '';
        $dateInput = $data['date_evenement'] ?? '';
        if (!is_scalar($faultInput) || !is_scalar($sanctionInput) || !is_scalar($dateInput)) {
            throw new \InvalidArgumentException('Les champs du dossier disciplinaire sont invalides.');
        }

        $fault = trim((string) $faultInput);
        $sanction = trim((string) $sanctionInput);
        $date = trim((string) $dateInput);
        $periodId = filter_var($data['periode_id'] ?? null, FILTER_VALIDATE_INT);
        $points = filter_var($data['retrait_points'] ?? null, FILTER_VALIDATE_INT);
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        if ($fault === '' || mb_strlen($fault, 'UTF-8') > 255) {
            throw new \InvalidArgumentException('Le fait consigné est obligatoire et limité à 255 caractères.');
        }
        if (mb_strlen($sanction, 'UTF-8') > 255) {
            throw new \InvalidArgumentException('La sanction est limitée à 255 caractères.');
        }
        if ($periodId === false || $periodId <= 0) {
            throw new \InvalidArgumentException('Sélectionnez une période scolaire valide.');
        }
        if ($points === false || $points < 0 || $points > 2147483647) {
            throw new \InvalidArgumentException('Le retrait de points doit être un entier positif ou nul compatible avec le dossier.');
        }
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            throw new \InvalidArgumentException('Saisissez une date d’incident valide.');
        }

        return [
            'periode_id' => $periodId,
            'faute' => $fault,
            'sanction' => $sanction !== '' ? $sanction : null,
            'retrait_points' => $points,
            'date_evenement' => $date,
        ];
    }

    private static function assertDisciplineStudent(PDO $db, int $studentId, int $schoolId): void
    {
        if ($studentId <= 0 || $schoolId <= 0) {
            throw new \InvalidArgumentException('Élève ou école invalide.');
        }

        $stmt = $db->prepare(
            'SELECT e.id
             FROM eleves e
             WHERE e.id = :student_id
               AND (
                 e.ecole_id = :school_id
                 OR EXISTS (
                   SELECT 1
                   FROM inscriptions i
                   INNER JOIN classes c ON c.id = i.classe_id
                   WHERE i.eleve_id = e.id AND c.ecole_id = :enrollment_school_id
                 )
               )
             LIMIT 1'
        );
        $stmt->execute([
            ':student_id' => $studentId,
            ':school_id' => $schoolId,
            ':enrollment_school_id' => $schoolId,
        ]);
        if (!$stmt->fetchColumn()) {
            throw new \DomainException('Cet élève ne relève pas de votre école.');
        }
    }

    private static function assertDisciplinePeriod(PDO $db, int $periodId, int $schoolId): void
    {
        $stmt = $db->prepare(
            'SELECT p.id
             FROM periodes p
             INNER JOIN annees_scolaires a ON a.id = p.annee_scolaire_id
             WHERE p.id = :period_id AND a.ecole_id = :school_id
             LIMIT 1'
        );
        $stmt->execute([':period_id' => $periodId, ':school_id' => $schoolId]);
        if (!$stmt->fetchColumn()) {
            throw new \DomainException('La période sélectionnée ne relève pas de votre école.');
        }
    }

    private static function assertDisciplineEntry(PDO $db, int $entryId, int $studentId): void
    {
        $stmt = $db->prepare(
            'SELECT id FROM discipline_eleves WHERE id = :entry_id AND eleve_id = :student_id LIMIT 1'
        );
        $stmt->execute([':entry_id' => $entryId, ':student_id' => $studentId]);
        if (!$stmt->fetchColumn()) {
            throw new \DomainException('Le dossier disciplinaire demandé est introuvable.');
        }
    }

    public static function update(int $id, array $data): bool
    {
        $matricule = array_key_exists('matricule', $data) ? strtoupper(trim((string) ($data['matricule'] ?? ''))) : null;
        if ($matricule !== null && $matricule !== '' && !self::isValidMatriculeFormat($matricule)) {
            throw new \InvalidArgumentException('Le matricule doit contenir 4 lettres majuscules suivies de 5 chiffres.');
        }
        if ($matricule !== null && $matricule !== '' && self::isMatriculeTaken($matricule, $id)) {
            throw new \InvalidArgumentException('Le matricule est déjà utilisé.');
        }
        if ($matricule === '') {
            $matricule = null;
        }

        $db = Database::getConnection();
        $fields = [
            'matricule = :matricule',
            'nom = :nom',
            'postnom = :postnom',
            'prenom = :prenom',
            'genre = :genre',
            'lieu_naissance = :lieu_naissance',
            'nationalite = :nationalite',
            'adresse = :adresse',
            'date_naissance = :date_naissance',
            'parent_id = :parent_id',
            'nom_pere = :nom_pere',
            'nom_mere = :nom_mere',
            'province_origine = :province_origine',
            'territoire = :territoire',
            'secteur = :secteur',
            'groupement = :groupement',
            'village = :village',
            'num_permanent = :num_permanent',
            'photo = :photo',
        ];
        if (array_key_exists('ecole_id', $data)) {
            $fields[] = 'ecole_id = :ecole_id';
        }

        $sql = 'UPDATE eleves SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);

        $params = [
            ':matricule' => $matricule,
            ':nom' => $data['nom'],
            ':postnom' => $data['postnom'],
            ':prenom' => $data['prenom'] ?? null,
            ':genre' => $data['genre'],
            ':lieu_naissance' => $data['lieu_naissance'] ?? null,
            ':nationalite' => $data['nationalite'] ?? 'CONGOLAISE',
            ':adresse' => $data['adresse'] ?? null,
            ':date_naissance' => $data['date_naissance'],
            ':parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
            ':nom_pere' => $data['nom_pere'] ?? null,
            ':nom_mere' => $data['nom_mere'] ?? null,
            ':province_origine' => $data['province_origine'] ?? null,
            ':territoire' => $data['territoire'] ?? null,
            ':secteur' => $data['secteur'] ?? null,
            ':groupement' => $data['groupement'] ?? null,
            ':village' => $data['village'] ?? null,
            ':num_permanent' => $data['num_permanent'] ?? null,
            ':photo' => $data['photo'] ?? null,
            ':id' => $id,
        ];
        if (array_key_exists('ecole_id', $data)) {
            $params[':ecole_id'] = !empty($data['ecole_id']) ? $data['ecole_id'] : null;
        }

        try {
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            if (stripos($e->getMessage(), 'Unknown column') !== false && stripos($e->getMessage(), 'ecole_id') !== false) {
                unset($params[':ecole_id']);
                $fields = array_filter($fields, fn($field) => stripos($field, 'ecole_id') === false);
                $sql = 'UPDATE eleves SET ' . implode(', ', $fields) . ' WHERE id = :id';
                $stmt = $db->prepare($sql);
                return $stmt->execute($params);
            }

            throw $e;
        }
    }

    public static function updateMatricule(int $id, string $matricule): bool
    {
        $normalized = strtoupper(trim($matricule));
        if ($normalized === '') {
            return false;
        }
        if (!self::isValidMatriculeFormat($normalized)) {
            throw new \InvalidArgumentException('Le matricule doit contenir 4 lettres majuscules suivies de 5 chiffres.');
        }
        if (self::isMatriculeTaken($normalized, $id)) {
            throw new \InvalidArgumentException('Le matricule est déjà utilisé.');
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE eleves SET matricule = :matricule WHERE id = :id');
        return $stmt->execute([':matricule' => $normalized, ':id' => $id]);
    }

    public static function countPending(): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) AS total FROM eleves WHERE statut_eleve = 'inactif'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    public static function approve(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE eleves SET statut_eleve = 'actif' WHERE id = :id AND statut_eleve <> 'actif'");
        return $stmt->execute([':id' => $id]) && $stmt->rowCount() === 1;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM eleves WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

        return $eleve ?: null;
    }

    public static function findByIdAndSchool(int $id, int $ecoleId, ?int $sectionId = null): ?array
    {
        $db = Database::getConnection();
        $sql =
            'SELECT e.* FROM eleves e '
            . 'WHERE e.id = :id AND (e.ecole_id = :ecole_id OR EXISTS ('
            . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id))';
        $params = [':id' => $id, ':ecole_id' => $ecoleId];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions section_i '
                . 'INNER JOIN classes section_c ON section_c.id = section_i.classe_id '
                . 'WHERE section_i.eleve_id = e.id AND section_c.ecole_id = :section_school '
                . 'AND section_c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

        return $eleve ?: null;
    }

    public static function createUserAccount(int $eleveId): array
    {
        $eleve = self::findById($eleveId);
        if (!$eleve) {
            throw new \RuntimeException('Eleve not found');
        }
        // Determine associated school (ecole_id)
        $ecoleId = $eleve['ecole_id'] ?? null;

        // try to get from latest inscription -> classe -> ecole
        if (empty($ecoleId)) {
            $db = \App\Core\Database::getConnection();
            $stmt = $db->prepare('SELECT c.ecole_id FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id WHERE i.eleve_id = :id ORDER BY i.date_inscription DESC LIMIT 1');
            $stmt->execute([':id' => $eleve['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['ecole_id'])) {
                $ecoleId = (int) $row['ecole_id'];
            }
        }

        // fallback to parent's ecole
        if (empty($ecoleId) && !empty($eleve['parent_id'])) {
            $db = \App\Core\Database::getConnection();
            $stmt = $db->prepare('SELECT ecole_id FROM parents WHERE id = :pid LIMIT 1');
            $stmt->execute([':pid' => $eleve['parent_id']]);
            $prow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($prow && !empty($prow['ecole_id'])) {
                $ecoleId = (int) $prow['ecole_id'];
            }
        }

        if (empty($ecoleId)) {
            throw new \RuntimeException('Cannot determine ecole_id for eleve ' . $eleve['id']);
        }

        $identifiant = $eleve['matricule'] ?: 'eleve' . $eleve['id'] . '@school.local';
        $password = bin2hex(random_bytes(4));

        \App\Models\User::findOrCreateForReference([
            'role' => 'eleve_ecole',
            'reference_id' => $eleve['id'],
            'ecole_id' => $ecoleId,
            'identifiant' => $identifiant,
            'mot_de_passe' => $password,
            'nom_complet' => trim(($eleve['prenom'] ?? '') . ' ' . ($eleve['nom'] ?? '')),
        ]);

        return \App\Models\User::syncReferenceIdentifier(
            'eleve_ecole',
            (int) $eleve['id'],
            (int) $ecoleId,
            $identifiant
        );
    }
}
