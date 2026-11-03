<?php

namespace App\Models;

use App\Core\Database;
use App\Models\DetteEleve;
use PDO;

class ParentModel
{
    private static bool $childRequestsTableReady = false;

    public static function getUnassignedChildrenBySchool(int $ecoleId, ?int $sectionId = null): array
    {
        if ($ecoleId <= 0) {
            return [];
        }
        self::ensureChildRequestsTable();

        $classScope = ' AND c.ecole_id = :class_school';
        $optionClassScope = ' AND c.ecole_id = :option_school';
        $schoolScope = ' AND scope_c.ecole_id = :scope_school';
        $sectionScope = '';
        $params = [
            ':class_school' => $ecoleId,
            ':option_school' => $ecoleId,
            ':scope_school' => $ecoleId,
            ':student_school' => $ecoleId,
        ];
        if ($sectionId !== null) {
            $classScope .= ' AND c.section_id = :class_section';
            $optionClassScope .= ' AND c.section_id = :option_section';
            $schoolScope .= ' AND scope_c.section_id = :scope_section';
            $sectionScope = ' AND EXISTS ('
                . 'SELECT 1 FROM inscriptions section_i INNER JOIN classes section_c ON section_c.id = section_i.classe_id '
                . 'WHERE section_i.eleve_id = e.id AND section_c.ecole_id = :section_school '
                . 'AND section_c.section_id = :section_id)';
            $params[':class_section'] = $sectionId;
            $params[':option_section'] = $sectionId;
            $params[':scope_section'] = $sectionId;
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT e.id, e.nom, e.postnom, e.prenom, e.matricule, '
            . '(SELECT c.nom_classe FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
            . 'WHERE i.eleve_id = e.id' . $classScope . ' ORDER BY i.date_inscription DESC, i.id DESC LIMIT 1) AS nom_classe, '
            . '(SELECT o.nom_option FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
            . 'LEFT JOIN options o ON o.id = c.option_id '
            . 'WHERE i.eleve_id = e.id' . $optionClassScope . ' ORDER BY i.date_inscription DESC, i.id DESC LIMIT 1) AS nom_option '
            . 'FROM eleves e '
            . 'WHERE e.parent_id IS NULL '
            . 'AND NOT EXISTS (SELECT 1 FROM parent_child_requests pcr '
            . 'WHERE pcr.student_id = e.id AND pcr.status = \'pending\') '
            . 'AND (e.ecole_id = :student_school OR EXISTS ('
            . 'SELECT 1 FROM inscriptions scope_i INNER JOIN classes scope_c ON scope_c.id = scope_i.classe_id '
            . 'WHERE scope_i.eleve_id = e.id' . $schoolScope . '))'
            . $sectionScope
            . ' ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC'
        );
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createChildAssignmentRequest(
        int $parentId,
        int $eleveId,
        int $ecoleId,
        int $requestedBy,
        ?int $sectionId = null
    ): void
    {
        if ($parentId <= 0 || $eleveId <= 0 || $ecoleId <= 0 || $requestedBy <= 0) {
            throw new \DomainException('Le parent, l’élève ou l’école sélectionné est invalide.');
        }

        self::ensureChildRequestsTable();
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $parentStmt = $db->prepare('SELECT id FROM parents WHERE id = :parent_id AND ecole_id = :ecole_id LIMIT 1');
            $parentStmt->execute([':parent_id' => $parentId, ':ecole_id' => $ecoleId]);
            if (!$parentStmt->fetchColumn()) {
                throw new \DomainException('Ce parent n’appartient pas à votre école.');
            }

            $studentSql = 'SELECT e.parent_id FROM eleves e WHERE e.id = :eleve_id '
                . 'AND (e.ecole_id = :student_school OR EXISTS ('
                . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
                . 'WHERE i.eleve_id = e.id AND c.ecole_id = :class_school';
            $studentParams = [
                ':eleve_id' => $eleveId,
                ':student_school' => $ecoleId,
                ':class_school' => $ecoleId,
            ];
            if ($sectionId !== null) {
                $studentSql .= ' AND c.section_id = :class_section';
                $studentParams[':class_section'] = $sectionId;
            }
            $studentSql .= '))';
            if ($sectionId !== null) {
                $studentSql .= ' AND EXISTS ('
                    . 'SELECT 1 FROM inscriptions section_i INNER JOIN classes section_c ON section_c.id = section_i.classe_id '
                    . 'WHERE section_i.eleve_id = e.id AND section_c.ecole_id = :section_school '
                    . 'AND section_c.section_id = :section_id)';
                $studentParams[':section_school'] = $ecoleId;
                $studentParams[':section_id'] = $sectionId;
            }
            $studentStmt = $db->prepare($studentSql . ' LIMIT 1 FOR UPDATE');
            $studentStmt->execute($studentParams);
            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
            if (!$student) {
                throw new \DomainException('Cet élève n’appartient pas à votre école ou à votre section.');
            }
            if (!empty($student['parent_id'])) {
                throw new \DomainException('Cet élève est déjà rattaché à un parent.');
            }

            $pendingStmt = $db->prepare(
                'SELECT id FROM parent_child_requests WHERE student_id = :student_id AND status = \'pending\' LIMIT 1'
            );
            $pendingStmt->execute([':student_id' => $eleveId]);
            if ($pendingStmt->fetchColumn()) {
                throw new \DomainException('Une demande est déjà en attente pour cet élève.');
            }

            $requestStmt = $db->prepare(
                'INSERT INTO parent_child_requests (parent_id, student_id, school_id, requested_by, status) '
                . 'VALUES (:parent_id, :student_id, :school_id, :requested_by, \'pending\')'
            );
            $requestStmt->execute([
                ':parent_id' => $parentId,
                ':student_id' => $eleveId,
                ':school_id' => $ecoleId,
                ':requested_by' => $requestedBy,
            ]);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function getPendingChildRequestsForParent(int $parentId, int $ecoleId): array
    {
        if ($parentId <= 0 || $ecoleId <= 0) {
            return [];
        }
        self::ensureChildRequestsTable();

        $stmt = Database::getConnection()->prepare(
            'SELECT pcr.id, pcr.created_at, e.id AS student_id, e.nom, e.postnom, e.prenom, e.matricule, '
            . 'c.nom_classe, o.nom_option, u.nom_complet AS requested_by_name '
            . 'FROM parent_child_requests pcr '
            . 'INNER JOIN parents p ON p.id = pcr.parent_id AND p.ecole_id = pcr.school_id '
            . 'INNER JOIN eleves e ON e.id = pcr.student_id '
            . 'LEFT JOIN utilisateurs u ON u.id = pcr.requested_by '
            . 'LEFT JOIN inscriptions i ON i.id = ('
            . 'SELECT i2.id FROM inscriptions i2 INNER JOIN classes c2 ON c2.id = i2.classe_id '
            . 'WHERE i2.eleve_id = e.id AND c2.ecole_id = pcr.school_id '
            . 'ORDER BY i2.date_inscription DESC, i2.id DESC LIMIT 1) '
            . 'LEFT JOIN classes c ON c.id = i.classe_id '
            . 'LEFT JOIN options o ON o.id = c.option_id '
            . 'WHERE pcr.parent_id = :parent_id AND pcr.school_id = :school_id '
            . 'AND pcr.status = \'pending\' AND e.parent_id IS NULL '
            . 'ORDER BY pcr.created_at DESC, pcr.id DESC'
        );
        $stmt->execute([':parent_id' => $parentId, ':school_id' => $ecoleId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPendingChildRequestsForParentProfile(
        int $parentId,
        int $ecoleId,
        ?int $sectionId = null
    ): array
    {
        if ($parentId <= 0 || $ecoleId <= 0) {
            return [];
        }
        self::ensureChildRequestsTable();

        $sql =
            'SELECT pcr.id, pcr.created_at, pcr.status, e.id AS student_id, e.nom, e.postnom, e.prenom, '
            . 'e.matricule, u.nom_complet AS requested_by_name '
            . 'FROM parent_child_requests pcr '
            . 'INNER JOIN eleves e ON e.id = pcr.student_id '
            . 'LEFT JOIN utilisateurs u ON u.id = pcr.requested_by '
            . 'WHERE pcr.parent_id = :parent_id AND pcr.school_id = :school_id '
            . 'AND pcr.status = \'pending\' AND e.parent_id IS NULL';
        $params = [':parent_id' => $parentId, ':school_id' => $ecoleId];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
                . 'WHERE i.eleve_id = e.id AND c.ecole_id = :section_school AND c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = Database::getConnection()->prepare($sql . ' ORDER BY pcr.created_at DESC, pcr.id DESC');
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function respondToChildAssignmentRequest(
        int $requestId,
        int $parentId,
        int $ecoleId,
        string $decision
    ): void {
        if ($requestId <= 0 || $parentId <= 0 || $ecoleId <= 0 || !in_array($decision, ['accepted', 'rejected'], true)) {
            throw new \DomainException('La réponse à cette demande est invalide.');
        }
        self::ensureChildRequestsTable();

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $requestStmt = $db->prepare(
                'SELECT id, student_id FROM parent_child_requests '
                . 'WHERE id = :request_id AND parent_id = :parent_id AND school_id = :school_id '
                . 'AND status = \'pending\' LIMIT 1 FOR UPDATE'
            );
            $requestStmt->execute([
                ':request_id' => $requestId,
                ':parent_id' => $parentId,
                ':school_id' => $ecoleId,
            ]);
            $request = $requestStmt->fetch(PDO::FETCH_ASSOC);
            if (!$request) {
                throw new \DomainException('Cette demande n’est plus disponible.');
            }

            if ($decision === 'accepted') {
                $studentStmt = $db->prepare(
                    'UPDATE eleves SET parent_id = :parent_id '
                    . 'WHERE id = :student_id AND parent_id IS NULL '
                    . 'AND (ecole_id = :student_school OR EXISTS ('
                    . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
                    . 'WHERE i.eleve_id = eleves.id AND c.ecole_id = :class_school))'
                );
                $studentStmt->execute([
                    ':parent_id' => $parentId,
                    ':student_id' => (int) $request['student_id'],
                    ':student_school' => $ecoleId,
                    ':class_school' => $ecoleId,
                ]);
                if ($studentStmt->rowCount() !== 1) {
                    throw new \DomainException('Cet élève est déjà rattaché ou n’appartient plus à cette école.');
                }
            }

            $update = $db->prepare(
                'UPDATE parent_child_requests SET status = :status, responded_at = CURRENT_TIMESTAMP '
                . 'WHERE id = :request_id AND status = \'pending\''
            );
            $update->execute([':status' => $decision, ':request_id' => $requestId]);
            if ($update->rowCount() !== 1) {
                throw new \DomainException('Cette demande a déjà reçu une réponse.');
            }

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    private static function ensureChildRequestsTable(): void
    {
        if (self::$childRequestsTableReady) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS parent_child_requests (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                parent_id INT NOT NULL,
                student_id INT NOT NULL,
                school_id INT NOT NULL,
                requested_by INT NOT NULL,
                status ENUM(\'pending\', \'accepted\', \'rejected\') NOT NULL DEFAULT \'pending\',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                responded_at TIMESTAMP NULL DEFAULT NULL,
                KEY idx_parent_child_request_parent (parent_id, status, created_at),
                KEY idx_parent_child_request_student (student_id, status),
                KEY idx_parent_child_request_school (school_id, status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$childRequestsTableReady = true;
    }

    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, nom_responsable, telephone, email FROM parents ORDER BY nom_responsable ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllBySchool(int $ecoleId, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT id, nom_responsable, telephone, email FROM parents WHERE ecole_id = :ecole_id';
        $params = [':ecole_id' => $ecoleId];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM eleves e INNER JOIN inscriptions i ON i.eleve_id = e.id '
                . 'INNER JOIN classes c ON c.id = i.classe_id '
                . 'WHERE e.parent_id = parents.id AND c.ecole_id = :section_school AND c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' ORDER BY nom_responsable ASC');
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM parents WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        return $parent ?: null;
    }

    public static function findByIdAndSchool(int $id, int $ecoleId, ?int $sectionId = null): ?array
    {
        $db = Database::getConnection();
        $sql = 'SELECT * FROM parents WHERE id = :id AND ecole_id = :ecole_id';
        $params = [':id' => $id, ':ecole_id' => $ecoleId];
        if ($sectionId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM eleves e INNER JOIN inscriptions i ON i.eleve_id = e.id '
                . 'INNER JOIN classes c ON c.id = i.classe_id '
                . 'WHERE e.parent_id = parents.id AND c.ecole_id = :section_school AND c.section_id = :section_id)';
            $params[':section_school'] = $ecoleId;
            $params[':section_id'] = $sectionId;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        return $parent ?: null;
    }

    public static function hasContactConflict(int $id, int $ecoleId, string $telephone, ?string $email): bool
    {
        $db = Database::getConnection();
        $sql = 'SELECT id FROM parents
                WHERE id <> :id
                  AND (ecole_id = :ecole_id AND telephone = :telephone)';
        $params = [
            ':id' => $id,
            ':ecole_id' => $ecoleId,
            ':telephone' => $telephone,
        ];
        if ($email !== null) {
            $sql = 'SELECT id FROM parents
                    WHERE id <> :id
                      AND ((ecole_id = :ecole_id AND telephone = :telephone) OR email = :email)';
            $params[':email'] = $email;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public static function updateForSchool(int $id, int $ecoleId, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE parents
             SET nom_responsable = :nom_responsable, telephone = :telephone, email = :email
             WHERE id = :id AND ecole_id = :ecole_id'
        );

        $updated = $stmt->execute([
            ':nom_responsable' => $data['nom_responsable'],
            ':telephone' => $data['telephone'],
            ':email' => $data['email'] ?? null,
            ':id' => $id,
            ':ecole_id' => $ecoleId,
        ]);

        if ($updated) {
            $parent = self::findByIdAndSchool($id, $ecoleId);
            if ($parent) {
                self::syncInfobipContact($parent, 'update');
            }
        }

        return $updated;
    }

    public static function getChildren(int $parentId, ?int $ecoleId = null, ?int $sectionId = null): array
    {
        $db = Database::getConnection();
        $classScope = $ecoleId !== null ? ' AND c.ecole_id = :class_school' : '';
        $optionClassScope = $ecoleId !== null ? ' AND c.ecole_id = :option_class_school' : '';
        if ($sectionId !== null) {
            $classScope .= ' AND c.section_id = :class_section';
            $optionClassScope .= ' AND c.section_id = :option_class_section';
        }
        $sql =
            'SELECT e.id, e.matricule, e.nom, e.postnom, e.prenom, e.statut_eleve, '
            . '(SELECT c.nom_classe FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id' . $classScope . ' ORDER BY i.date_inscription DESC, i.id DESC LIMIT 1) AS nom_classe, '
            . '(SELECT o.nom_option FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'LEFT JOIN options o ON o.id = c.option_id '
            . 'WHERE i.eleve_id = e.id' . $optionClassScope . ' ORDER BY i.date_inscription DESC, i.id DESC LIMIT 1) AS nom_option '
            . 'FROM eleves e WHERE e.parent_id = :parent_id';
        $params = [':parent_id' => $parentId];
        if ($ecoleId !== null) {
            $params[':class_school'] = (int) $ecoleId;
            $params[':option_class_school'] = (int) $ecoleId;
        }
        if ($sectionId !== null) {
            $params[':class_section'] = $sectionId;
            $params[':option_class_section'] = $sectionId;
        }
        if ($ecoleId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM inscriptions i INNER JOIN classes c ON c.id = i.classe_id '
                . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id';
            $params[':ecole_id'] = $ecoleId;
            if ($sectionId !== null) {
                $sql .= ' AND c.section_id = :section_id';
                $params[':section_id'] = $sectionId;
            }
            $sql .= ')';
        }
        $stmt = $db->prepare($sql . ' ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC');
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getChildrenFinancialSummaries(array $children): array
    {
        $summaries = [];
        $db = Database::getConnection();

        foreach ($children as $child) {
            $eleveId = (int) ($child['id'] ?? 0);
            if ($eleveId <= 0) {
                continue;
            }

            $paid = [];
            $queries = [
                'SELECT COALESCE(fs.devise, "USD") AS devise, SUM(ece.montant) AS total '
                . 'FROM ecritures_comptables_eleves ece '
                . 'INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id '
                . 'LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id '
                . 'WHERE ce.eleve_id = :eleve AND ece.type_mouvement = "CREDIT" '
                . 'GROUP BY COALESCE(fs.devise, "USD")',
                'SELECT COALESCE(fs.devise, "USD") AS devise, SUM(pe.montant_paye) AS total '
                . 'FROM paiements_eleves pe LEFT JOIN frais_scolaires fs ON fs.id = pe.frais_id '
                . 'WHERE pe.eleve_id = :eleve GROUP BY COALESCE(fs.devise, "USD")',
            ];

            foreach ($queries as $query) {
                try {
                    $stmt = $db->prepare($query);
                    $stmt->execute([':eleve' => $eleveId]);
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $devise = strtoupper(trim($row['devise'] ?? 'USD')) ?: 'USD';
                        $paid[$devise] = max($paid[$devise] ?? 0.0, (float) ($row['total'] ?? 0));
                    }
                } catch (\Throwable $e) {
                    // Legacy payment tables may not exist in every installation.
                }
            }

            $debt = DetteEleve::getTotalOutstandingGroupedByDevise($eleveId);
            if (empty($debt)) {
                $debt = DetteEleve::computeOutstandingFromApplicableFees($eleveId);
            }
            ksort($paid);
            ksort($debt);
            $summaries[$eleveId] = ['paid' => $paid, 'debt' => $debt];
        }

        return $summaries;
    }

    public static function create(array $data): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO parents (ecole_id, nom_responsable, telephone, email, mot_de_passe) VALUES (:ecole_id, :nom_responsable, :telephone, :email, :mot_de_passe)'
        );

        $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':nom_responsable' => $data['nom_responsable'],
            ':telephone' => $data['telephone'],
            ':email' => $data['email'] ?? null,
            ':mot_de_passe' => $data['mot_de_passe'],
        ]);

        $created = self::findById((int) $db->lastInsertId());
        if ($created) {
            try {
                self::createUserAccount((int) $created['id']);
            } catch (\Throwable $e) {
                error_log('ParentModel::create user account error: ' . $e->getMessage());
            }

            self::syncInfobipContact($created, 'create');
        }

        return $created;
    }

    public static function createUserAccount(int $parentId): array
    {
        $parent = self::findById($parentId);
        if (!$parent) {
            throw new \RuntimeException('Parent not found');
        }
        $ecoleId = $parent['ecole_id'] ?? null;
        if (empty($ecoleId)) {
            throw new \RuntimeException('Parent ' . $parent['id'] . ' has no ecole_id');
        }

        $identifiant = $parent['email'] ?: $parent['telephone'] ?: 'parent' . $parent['id'] . '@local';
        $password = bin2hex(random_bytes(4));

        return \App\Models\User::findOrCreateForReference([
            'role' => 'parent_ecole',
            'reference_id' => $parent['id'],
            'ecole_id' => $ecoleId,
            'identifiant' => $identifiant,
            'mot_de_passe' => $password,
            'nom_complet' => $parent['nom_responsable'] ?? $identifiant,
        ]);
    }

    private static function syncInfobipContact(array $parent, string $operation): void
    {
        $result = \App\Services\InfobipPeopleSync::syncParent($parent);
        if (empty($result['synced']) && empty($result['skipped'])) {
            $reason = trim((string) ($result['reason'] ?? 'Unknown Infobip synchronization error.'));
            error_log('ParentModel::' . $operation . ' Infobip contact sync failed for parent '
                . (int) ($parent['id'] ?? 0) . ': ' . $reason);
        }
    }
}
