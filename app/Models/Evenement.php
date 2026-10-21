<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Evenement
{
    private static bool $authorColumnReady = false;

    public static function ensureAuthorColumn(): void
    {
        if (self::$authorColumnReady) {
            return;
        }

        $db = Database::getConnection();
        $authorColumn = $db->query("SHOW COLUMNS FROM evenements LIKE 'auteur_user_id'")->fetch();
        if (!$authorColumn) {
            $db->exec(
                'ALTER TABLE evenements
                 ADD COLUMN auteur_user_id INT DEFAULT NULL AFTER organisateur_id,
                 ADD INDEX idx_evenements_auteur_user (auteur_user_id)'
            );
        }

        $organizerColumn = $db->query("SHOW COLUMNS FROM evenements LIKE 'organisateur_id'")->fetch();
        if ($organizerColumn && $organizerColumn['Null'] === 'NO') {
            $db->exec('ALTER TABLE evenements MODIFY organisateur_id INT(11) DEFAULT NULL');
        }

        self::$authorColumnReady = true;
    }

    public static function getCategories(): array
    {
        $stmt = Database::getConnection()->query(
            'SELECT id, nom_categorie FROM categories_evenements ORDER BY nom_categorie'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function categoryExists(int $categoryId): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id FROM categories_evenements WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $categoryId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function getAllBySchool(int $schoolId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT e.*, c.nom_categorie, u.nom_complet AS auteur_nom
             FROM evenements e
             LEFT JOIN categories_evenements c ON c.id = e.categorie_id
             LEFT JOIN utilisateurs u ON u.id = e.auteur_user_id
             WHERE e.ecole_id = :ecole_id
             ORDER BY e.date_debut ASC, e.id DESC'
        );
        $stmt->execute([':ecole_id' => $schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): void
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO evenements (
                ecole_id, titre, description, date_debut, date_fin, lieu,
                categorie_id, organisateur_id, annee_scolaire_id, auteur_user_id
             ) VALUES (
                :ecole_id, :titre, :description, :date_debut, :date_fin, :lieu,
                :categorie_id, NULL, :annee_scolaire_id, :auteur_user_id
             )'
        );
        $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':titre' => $data['titre'],
            ':description' => $data['description'] !== '' ? $data['description'] : null,
            ':date_debut' => $data['date_debut'],
            ':date_fin' => $data['date_fin'],
            ':lieu' => $data['lieu'] !== '' ? $data['lieu'] : null,
            ':categorie_id' => $data['categorie_id'],
            ':annee_scolaire_id' => $data['annee_scolaire_id'],
            ':auteur_user_id' => $data['auteur_user_id'],
        ]);
    }

    public static function updateOwned(int $id, int $schoolId, int $authorId, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE evenements
             SET titre = :titre, description = :description, date_debut = :date_debut,
                 date_fin = :date_fin, lieu = :lieu, categorie_id = :categorie_id
             WHERE id = :id AND ecole_id = :ecole_id AND auteur_user_id = :auteur_user_id'
        );
        $stmt->execute([
            ':titre' => $data['titre'],
            ':description' => $data['description'] !== '' ? $data['description'] : null,
            ':date_debut' => $data['date_debut'],
            ':date_fin' => $data['date_fin'],
            ':lieu' => $data['lieu'] !== '' ? $data['lieu'] : null,
            ':categorie_id' => $data['categorie_id'],
            ':id' => $id,
            ':ecole_id' => $schoolId,
            ':auteur_user_id' => $authorId,
        ]);
        if ($stmt->rowCount() > 0) {
            return true;
        }

        $check = $db->prepare(
            'SELECT id FROM evenements
             WHERE id = :id AND ecole_id = :ecole_id AND auteur_user_id = :auteur_user_id
             LIMIT 1'
        );
        $check->execute([
            ':id' => $id,
            ':ecole_id' => $schoolId,
            ':auteur_user_id' => $authorId,
        ]);
        return (bool) $check->fetchColumn();
    }

    public static function deleteOwned(int $id, int $schoolId, int $authorId): bool
    {
        $stmt = Database::getConnection()->prepare(
            'DELETE FROM evenements
             WHERE id = :id AND ecole_id = :ecole_id AND auteur_user_id = :auteur_user_id'
        );
        $stmt->execute([
            ':id' => $id,
            ':ecole_id' => $schoolId,
            ':auteur_user_id' => $authorId,
        ]);
        return $stmt->rowCount() > 0;
    }
}
