<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ParcoursScolaire
{
    public static function createForInscription(
        int $inscriptionId,
        array $documents,
        int $schoolId,
        int $studentId,
        array &$movedFiles = []
    ): void {
        if ($inscriptionId <= 0 || $schoolId <= 0 || $studentId <= 0) {
            throw new \InvalidArgumentException('Dossier scolaire invalide.');
        }

        $db = Database::getConnection();
        $uploadDirectory = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'private'
            . DIRECTORY_SEPARATOR . 'bulletins'
            . DIRECTORY_SEPARATOR . $schoolId
            . DIRECTORY_SEPARATOR . $studentId;
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            throw new \RuntimeException('Impossible de préparer le stockage des bulletins.');
        }

        try {
            $insertPath = $db->prepare(
                'INSERT INTO parcours_scolaires (inscription_id, ecole_provenance, annee_document, mention) '
                . 'VALUES (:inscription_id, :school, :year, :mention)'
            );
            $insertBulletin = $db->prepare(
                'INSERT INTO bulletins_scolaires (parcours_scolaire_id, fichier_stockage, nom_original) '
                . 'VALUES (:path_id, :url, :original_name)'
            );
            foreach ($documents as $document) {
                $insertPath->execute([
                    ':inscription_id' => $inscriptionId,
                    ':school' => $document['school'],
                    ':year' => $document['year'],
                    ':mention' => $document['mention'],
                ]);
                $pathId = (int) $db->lastInsertId();
                $bulletin = $document['bulletin'];
                $extension = $bulletin['extension'];
                $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
                $targetPath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
                if (!move_uploaded_file($bulletin['tmp_name'], $targetPath)) {
                    throw new \RuntimeException('Impossible d’enregistrer un fichier de bulletin.');
                }
                $movedFiles[] = $targetPath;
                $insertBulletin->execute([
                    ':path_id' => $pathId,
                    ':url' => 'bulletins/' . $schoolId . '/' . $studentId . '/' . $fileName,
                    ':original_name' => $bulletin['name'],
                ]);
            }
        } catch (\Throwable $e) {
            self::removeFiles($movedFiles);
            throw $e;
        }
    }

    public static function getForStudent(int $studentId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT ps.id, ps.ecole_provenance, ps.annee_document, ps.mention, a.annee, i.annee_scolaire_id '
            . 'FROM parcours_scolaires ps INNER JOIN inscriptions i ON i.id = ps.inscription_id '
            . 'INNER JOIN annees_scolaires a ON a.id = i.annee_scolaire_id '
            . 'WHERE i.eleve_id = :student_id '
            . 'ORDER BY a.annee DESC, ps.id DESC'
        );
        $stmt->execute([':student_id' => $studentId]);
        $paths = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $bulletinStmt = $db->prepare(
            'SELECT id, nom_original FROM bulletins_scolaires WHERE parcours_scolaire_id = :path_id ORDER BY id'
        );
        foreach ($paths as &$path) {
            $bulletinStmt->execute([':path_id' => (int) $path['id']]);
            $path['bulletins'] = $bulletinStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($path);

        return $paths;
    }

    public static function findBulletinById(int $bulletinId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT bs.id, bs.fichier_stockage, bs.nom_original, e.id AS eleve_id '
            . 'FROM bulletins_scolaires bs INNER JOIN parcours_scolaires ps ON ps.id = bs.parcours_scolaire_id '
            . 'INNER JOIN inscriptions i ON i.id = ps.inscription_id '
            . 'INNER JOIN eleves e ON e.id = i.eleve_id WHERE bs.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $bulletinId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function removeFiles(array $files): void
    {
        foreach ($files as $file) {
            if (\is_string($file) && is_file($file)) {
                unlink($file);
            }
        }
    }
}
