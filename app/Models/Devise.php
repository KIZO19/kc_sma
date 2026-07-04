<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Devise
{
    private static function ensureTableExists(): void
    {
        $db = Database::getConnection();
        $db->exec(
            'CREATE TABLE IF NOT EXISTS devises (
                id int(11) NOT NULL AUTO_INCREMENT,
                code varchar(5) NOT NULL,
                libelle varchar(100) NOT NULL,
                taux decimal(14,6) NOT NULL DEFAULT 1.000000,
                actif tinyint(1) NOT NULL DEFAULT 1,
                date_creation timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (id),
                UNIQUE KEY code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::ensureDefaultRates();
    }

    private static function ensureDefaultRates(): void
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT COUNT(*) AS cnt FROM devises');
            $count = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
            if ($count === 0) {
                $insert = $db->prepare(
                    'INSERT INTO devises (code, libelle, taux, actif) VALUES
                        (:usd_code, :usd_label, :usd_rate, 1),
                        (:eur_code, :eur_label, :eur_rate, 1),
                        (:cdf_code, :cdf_label, :cdf_rate, 1),
                        (:xaf_code, :xaf_label, :xaf_rate, 1),
                        (:xof_code, :xof_label, :xof_rate, 1)'
                );
                $insert->execute([
                    ':usd_code' => 'USD',
                    ':usd_label' => 'Dollar américain',
                    ':usd_rate' => 1.000000,
                    ':eur_code' => 'EUR',
                    ':eur_label' => 'Euro',
                    ':eur_rate' => 1.100000,
                    ':cdf_code' => 'CDF',
                    ':cdf_label' => 'Franc congolais',
                    ':cdf_rate' => 0.000500,
                    ':xaf_code' => 'XAF',
                    ':xaf_label' => 'Franc CFA BEAC',
                    ':xaf_rate' => 0.001700,
                    ':xof_code' => 'XOF',
                    ':xof_label' => 'Franc CFA BCEAO',
                    ':xof_rate' => 0.001700,
                ]);
            }
        } catch (\Throwable $e) {
            // ignore failures during default initialization
        }
    }

    public static function getAll(): array
    {
        self::ensureTableExists();
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM devises ORDER BY code ASC');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        self::ensureTableExists();
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM devises WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        self::ensureTableExists();
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM devises WHERE code = :code LIMIT 1');
        $stmt->execute([':code' => $code]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): bool
    {
        self::ensureTableExists();
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO devises (code, libelle, taux, actif) VALUES (:code, :libelle, :taux, :actif)'
        );

        return (bool) $stmt->execute([
            ':code' => strtoupper(trim($data['code'] ?? '')),
            ':libelle' => trim($data['libelle'] ?? ''),
            ':taux' => (float) ($data['taux'] ?? 0),
            ':actif' => isset($data['actif']) ? (int) $data['actif'] : 1,
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        self::ensureTableExists();
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE devises SET code = :code, libelle = :libelle, taux = :taux, actif = :actif WHERE id = :id'
        );

        return (bool) $stmt->execute([
            ':code' => strtoupper(trim($data['code'] ?? '')),
            ':libelle' => trim($data['libelle'] ?? ''),
            ':taux' => (float) ($data['taux'] ?? 0),
            ':actif' => isset($data['actif']) ? (int) $data['actif'] : 1,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        self::ensureTableExists();
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM devises WHERE id = :id');

        return (bool) $stmt->execute([':id' => $id]);
    }
}
