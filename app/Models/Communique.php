<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Communique
{
    private static bool $tableEnsured = false;

    public static function record(array $data): int
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO communiques
                (ecole_id, auteur_user_id, titre, message, destinataires_total,
                 destinataires_livres, destinataires_echec, statut, destinataires_roles, destinataires_sections)
             VALUES
                (:ecole_id, :auteur_user_id, :titre, :message, :destinataires_total,
                 :destinataires_livres, :destinataires_echec, :statut, :destinataires_roles, :destinataires_sections)'
        );
        $stmt->execute([
            ':ecole_id' => (int) $data['ecole_id'],
            ':auteur_user_id' => (int) $data['auteur_user_id'],
            ':titre' => (string) $data['titre'],
            ':message' => (string) $data['message'],
            ':destinataires_total' => (int) ($data['destinataires_total'] ?? 0),
            ':destinataires_livres' => (int) ($data['destinataires_livres'] ?? 0),
            ':destinataires_echec' => (int) ($data['destinataires_echec'] ?? 0),
            ':statut' => (string) ($data['statut'] ?? 'failed'),
            ':destinataires_roles' => json_encode($data['destinataires_roles'] ?? [], JSON_UNESCAPED_UNICODE),
            ':destinataires_sections' => json_encode($data['destinataires_sections'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        return (int) Database::getConnection()->lastInsertId();
    }

    public static function latestBySchool(int $ecoleId, int $limit = 20, ?int $sectionId = null): array
    {
        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT c.*, u.nom_complet AS auteur_nom
             FROM communiques c
             LEFT JOIN utilisateurs u ON u.id = c.auteur_user_id
             WHERE c.ecole_id = :ecole_id
             ORDER BY c.created_at DESC, c.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':ecole_id', $ecoleId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $sectionId === null ? max(1, min(100, $limit)) : 500, PDO::PARAM_INT);
        $stmt->execute();

        $communiques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($sectionId !== null) {
            $communiques = array_values(array_filter(
                $communiques,
                static function (array $communique) use ($sectionId): bool {
                    $rawSections = $communique['destinataires_sections'] ?? null;
                    if ($rawSections === null || trim((string) $rawSections) === '') {
                        return true;
                    }
                    $sections = json_decode((string) $rawSections, true);

                    return is_array($sections)
                        && $sections !== []
                        && in_array($sectionId, array_map('intval', $sections), true);
                }
            ));
        }

        return array_slice($communiques, 0, max(1, min(100, $limit)));
    }

    public static function latestForAudience(int $ecoleId, string $role, ?int $sectionId, int $limit = 30): array
    {
        if ($ecoleId <= 0 || $role === '') {
            return [];
        }

        self::ensureTable();
        $stmt = Database::getConnection()->prepare(
            'SELECT c.*, u.nom_complet AS auteur_nom
             FROM communiques c
             LEFT JOIN utilisateurs u ON u.id = c.auteur_user_id
             WHERE c.ecole_id = :ecole_id AND c.statut IN (\'success\', \'partial\')
             ORDER BY c.created_at DESC, c.id DESC
             LIMIT 500'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
        $matching = [];
        $roleAliases = in_array($role, ['eleve_ecole', 'eleve_école'], true)
            ? ['eleve_ecole', 'eleve_école']
            : [$role];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $communique) {
            $roles = json_decode((string) ($communique['destinataires_roles'] ?? ''), true);
            if (!is_array($roles) || array_intersect($roleAliases, $roles) === []) {
                continue;
            }

            $sections = json_decode((string) ($communique['destinataires_sections'] ?? ''), true);
            if ($sectionId !== null) {
                if (is_array($sections) && $sections !== []
                    && !in_array($sectionId, array_map('intval', $sections), true)) {
                    continue;
                }
            } elseif (is_array($sections) && $sections !== []) {
                continue;
            }

            if ($sectionId === null && trim((string) ($communique['destinataires_sections'] ?? '')) !== ''
                && !is_array($sections)) {
                continue;
            }

            $matching[] = $communique;
            if (count($matching) >= max(1, min(100, $limit))) {
                break;
            }
        }

        return $matching;
    }

    private static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        Database::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS communiques (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ecole_id INT NOT NULL,
                auteur_user_id INT NOT NULL,
                titre VARCHAR(160) NOT NULL,
                message TEXT NOT NULL,
                destinataires_total INT NOT NULL DEFAULT 0,
                destinataires_livres INT NOT NULL DEFAULT 0,
                destinataires_echec INT NOT NULL DEFAULT 0,
                statut VARCHAR(20) NOT NULL DEFAULT \'failed\',
                destinataires_roles TEXT DEFAULT NULL,
                destinataires_sections TEXT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_communiques_ecole_created (ecole_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $columns = [
            'destinataires_roles' => 'TEXT DEFAULT NULL',
            'destinataires_sections' => 'TEXT DEFAULT NULL',
        ];
        foreach ($columns as $columnName => $definition) {
            $column = Database::getConnection()->query(
                "SHOW COLUMNS FROM communiques LIKE '" . $columnName . "'"
            )->fetch();
            if (!$column) {
                Database::getConnection()->exec(
                    'ALTER TABLE communiques ADD COLUMN ' . $columnName . ' ' . $definition
                );
            }
        }
        self::$tableEnsured = true;
    }
}
