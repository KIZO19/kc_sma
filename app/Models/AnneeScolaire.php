<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class AnneeScolaire
{
    public static function setInitialForSchool(int $ecoleId, string $annee): void
    {
        $annee = self::validateYear($annee);
        if ($ecoleId <= 0) {
            throw new \InvalidArgumentException('École invalide.');
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $school = $db->prepare('SELECT id FROM ecoles WHERE id = :id FOR UPDATE');
            $school->execute([':id' => $ecoleId]);
            if (!$school->fetchColumn()) {
                throw new \DomainException('École introuvable.');
            }

            if (self::getActiveBySchool($ecoleId) !== null) {
                throw new \DomainException('L’année scolaire d’exercice est déjà définie. Soumettez une demande de modification.');
            }

            self::activateForSchool($db, $ecoleId, $annee);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public static function validateYear(string $annee): string
    {
        $annee = trim($annee);
        if (
            !preg_match('/^(\d{4})-(\d{4})$/', $annee, $matches)
            || (int) $matches[2] !== (int) $matches[1] + 1
        ) {
            throw new \InvalidArgumentException('Saisissez une année scolaire valide au format AAAA-AAAA, par exemple 2026-2027.');
        }

        return $annee;
    }

    public static function activateForSchool(PDO $db, int $ecoleId, string $annee): void
    {
        if (!$db->inTransaction()) {
            throw new \LogicException('La modification de l’année active doit être effectuée dans une transaction.');
        }
        if ($ecoleId <= 0) {
            throw new \InvalidArgumentException('École invalide.');
        }

        $annee = self::validateYear($annee);
        $deactivate = $db->prepare(
            'UPDATE annees_scolaires SET est_active = 0 WHERE ecole_id = :ecole_id'
        );
        $deactivate->execute([':ecole_id' => $ecoleId]);

        $find = $db->prepare(
            'SELECT id FROM annees_scolaires WHERE ecole_id = :ecole_id AND annee = :annee ORDER BY id ASC LIMIT 1 FOR UPDATE'
        );
        $find->execute([':ecole_id' => $ecoleId, ':annee' => $annee]);
        $yearId = $find->fetchColumn();

        if ($yearId !== false) {
            $activate = $db->prepare('UPDATE annees_scolaires SET est_active = 1 WHERE id = :id');
            $activate->execute([':id' => (int) $yearId]);
            return;
        }

        $insert = $db->prepare(
            'INSERT INTO annees_scolaires (ecole_id, annee, est_active) VALUES (:ecole_id, :annee, 1)'
        );
        $insert->execute([':ecole_id' => $ecoleId, ':annee' => $annee]);
    }

    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM annees_scolaires WHERE ecole_id = :ecole_id ORDER BY annee DESC');
        $stmt->execute([':ecole_id' => $ecoleId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getActiveBySchool(int $ecoleId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM annees_scolaires WHERE ecole_id = :ecole_id AND est_active = 1 LIMIT 1');
        $stmt->execute([':ecole_id' => $ecoleId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM annees_scolaires WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
