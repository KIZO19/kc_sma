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
                 destinataires_livres, destinataires_echec, statut)
             VALUES
                (:ecole_id, :auteur_user_id, :titre, :message, :destinataires_total,
                 :destinataires_livres, :destinataires_echec, :statut)'
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
        ]);

        return (int) Database::getConnection()->lastInsertId();
    }

    public static function latestBySchool(int $ecoleId, int $limit = 20): array
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
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_communiques_ecole_created (ecole_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tableEnsured = true;
    }
}
