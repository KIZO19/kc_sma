<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Option
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT o.id, o.nom_option, o.section_id, o.ecole_id, s.nom_section,
                    (SELECT COUNT(*) FROM classes c WHERE c.option_id = o.id) AS class_count,
                    (SELECT COUNT(*) FROM frais_scolaires f WHERE f.scope = \'option\' AND f.scope_id = o.id) AS fee_count
             FROM options o
             LEFT JOIN sections s ON s.id = o.section_id
             ORDER BY o.nom_option ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, nom_option, section_id, ecole_id FROM options WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $option = $stmt->fetch(PDO::FETCH_ASSOC);

        return $option ?: null;
    }

    public static function getAllForSchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT o.id, o.nom_option, o.section_id, s.nom_section,
                    (SELECT COUNT(*) FROM classes c WHERE c.option_id = o.id) AS class_count,
                    (SELECT COUNT(*) FROM frais_scolaires f WHERE f.scope = \'option\' AND f.scope_id = o.id) AS fee_count
             FROM options o
             LEFT JOIN sections s ON s.id = o.section_id
             WHERE o.ecole_id = :ecole_id
             ORDER BY o.nom_option ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllForSchoolAndSection(int $ecoleId, int $sectionId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT id, nom_option, section_id
             FROM options
             WHERE ecole_id = :ecole_id AND section_id = :section_id
             ORDER BY nom_option ASC'
        );
        $stmt->execute([':ecole_id' => $ecoleId, ':section_id' => $sectionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByIdForSchool(int $id, int $ecoleId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT id, nom_option, section_id, ecole_id
             FROM options
             WHERE id = :id AND ecole_id = :ecole_id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':ecole_id' => $ecoleId]);
        $option = $stmt->fetch(PDO::FETCH_ASSOC);

        return $option ?: null;
    }

    public static function isLinkedToSchool(int $optionId, int $ecoleId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT 1 FROM options WHERE id = :id AND ecole_id = :ecole_id LIMIT 1');
        $stmt->execute([':id' => $optionId, ':ecole_id' => $ecoleId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function nameExists(int $ecoleId, int $sectionId, string $name, ?int $excludeId = null): bool
    {
        $db = Database::getConnection();
        $sql = 'SELECT 1 FROM options WHERE ecole_id = :ecole_id AND section_id = :section_id AND nom_option = :nom_option';
        $params = [
            ':ecole_id' => $ecoleId,
            ':section_id' => $sectionId,
            ':nom_option' => $name,
        ];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public static function getUsage(int $id): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT
               (SELECT COUNT(*) FROM classes WHERE option_id = :class_option_id) AS classes,
               (SELECT COUNT(*) FROM frais_scolaires WHERE scope = 'option' AND scope_id = :fee_option_id) AS frais"
        );
        $stmt->execute([':class_option_id' => $id, ':fee_option_id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['classes' => 0, 'frais' => 0];
    }

    public static function create(array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO options (ecole_id, nom_option, section_id)
             VALUES (:ecole_id, :nom_option, :section_id)'
        );

        return $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':nom_option' => $data['nom_option'],
            ':section_id' => $data['section_id'],
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE options
             SET nom_option = :nom_option, section_id = :section_id
             WHERE id = :id AND ecole_id = :ecole_id'
        );
        return $stmt->execute([
            ':nom_option' => $data['nom_option'],
            ':section_id' => $data['section_id'],
            ':id' => $id,
            ':ecole_id' => $data['ecole_id'],
        ]);
    }

    public static function delete(int $id, int $ecoleId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM options WHERE id = :id AND ecole_id = :ecole_id');

        return $stmt->execute([':id' => $id, ':ecole_id' => $ecoleId]);
    }
}
