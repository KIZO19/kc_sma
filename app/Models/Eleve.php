<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Eleve
{
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

    public static function create(array $data): ?array
    {
        $matricule = isset($data['matricule']) ? trim((string) $data['matricule']) : '';
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

        return self::findById((int) $db->lastInsertId());
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

    public static function getPendingBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.*, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . 'WHERE e.statut_eleve = :statut AND (e.ecole_id = :ecole_id OR p.ecole_id = :ecole_id OR EXISTS ('
            . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id)) '
            . 'ORDER BY e.id DESC'
        );
        $stmt->execute([':statut' => 'inactif', ':ecole_id' => $ecoleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.*, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . 'ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT DISTINCT e.*, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON e.parent_id = p.id '
            . 'WHERE e.ecole_id = :ecole_id OR EXISTS ('
            . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id) '
            . 'ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
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

    public static function getAccountingEntries(int $eleveId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ece.* FROM ecritures_comptables_eleves ece '
            . 'INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id '
            . 'WHERE ce.eleve_id = :eleve ORDER BY ece.date_operation DESC'
        );
        $stmt->execute([':eleve' => $eleveId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getNotes(int $eleveId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT n.*, ev.attribution_cours_id, ev.periode_id, ev.type_evaluation, ev.date_evaluation, ev.ponderation_max '
            . 'FROM notes n LEFT JOIN evaluations ev ON n.evaluation_id = ev.id '
            . 'WHERE n.eleve_id = :eleve ORDER BY ev.date_evaluation DESC'
        );
        $stmt->execute([':eleve' => $eleveId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDiscipline(int $eleveId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM discipline_eleves WHERE eleve_id = :eleve ORDER BY date_evenement DESC');
        $stmt->execute([':eleve' => $eleveId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function update(int $id, array $data): bool
    {
        $matricule = array_key_exists('matricule', $data) ? trim((string) ($data['matricule'] ?? '')) : null;
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
        $normalized = trim($matricule);
        if ($normalized === '') {
            return false;
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
        $stmt = $db->prepare('UPDATE eleves SET statut_eleve = :statut WHERE id = :id');
        return $stmt->execute([':statut' => 'actif', ':id' => $id]);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM eleves WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

        return $eleve ?: null;
    }

    public static function findByIdAndSchool(int $id, int $ecoleId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.* FROM eleves e '
            . 'WHERE e.id = :id AND (e.ecole_id = :ecole_id OR EXISTS ('
            . 'SELECT 1 FROM inscriptions i INNER JOIN classes c ON i.classe_id = c.id '
            . 'WHERE i.eleve_id = e.id AND c.ecole_id = :ecole_id)) '
            . 'LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':ecole_id' => $ecoleId]);
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

        return \App\Models\User::findOrCreateForReference([
            'role' => 'eleve_ecole',
            'reference_id' => $eleve['id'],
            'ecole_id' => $ecoleId,
            'identifiant' => $identifiant,
            'mot_de_passe' => $password,
            'nom_complet' => trim(($eleve['prenom'] ?? '') . ' ' . ($eleve['nom'] ?? '')),
        ]);
    }
}
