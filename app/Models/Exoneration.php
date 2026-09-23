<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Exoneration
{
    public static function create(array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO exonerations (ecole_id, eleve_id, frais_id, montant, devise, motif, statut, demandeur_id)
             VALUES (:ecole_id, :eleve_id, :frais_id, :montant, :devise, :motif, :statut, :demandeur_id)'
        );

        return (bool) $stmt->execute([
            ':ecole_id' => $data['ecole_id'],
            ':eleve_id' => $data['eleve_id'],
            ':frais_id' => $data['frais_id'],
            ':montant' => $data['montant'],
            ':devise' => $data['devise'],
            ':motif' => $data['motif'],
            ':statut' => 'En_attente',
            ':demandeur_id' => $data['demandeur_id'],
        ]);
    }

    public static function getAllBySchool(int $ecoleId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT x.*, CONCAT_WS(\' \', e.nom, e.postnom, e.prenom) AS eleve_nom,
                    e.matricule, f.type_frais,
                    u.nom_complet AS demandeur_nom,
                    v.nom_complet AS validateur_nom
             FROM exonerations x
             INNER JOIN eleves e ON e.id = x.eleve_id
             INNER JOIN frais_scolaires f ON f.id = x.frais_id
             LEFT JOIN utilisateurs u ON u.id = x.demandeur_id
             LEFT JOIN utilisateurs v ON v.id = x.validateur_id
             WHERE x.ecole_id = :ecole_id
             ORDER BY x.created_at DESC, x.id DESC'
        );
        $stmt->execute([':ecole_id' => $ecoleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function decide(int $id, int $validateurId, string $status, string $commentaire): bool
    {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare('SELECT * FROM exonerations WHERE id = :id AND statut = :statut LIMIT 1 FOR UPDATE');
            $stmt->execute([':id' => $id, ':statut' => 'En_attente']);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$request) {
                $db->rollBack();
                return false;
            }

            if ($status === 'Approuvee') {
                $debtStmt = $db->prepare(
                    'SELECT id, montant_restant FROM dettes_eleves
                     WHERE eleve_id = :eleve_id AND frais_id = :frais_id AND montant_restant > 0
                     ORDER BY date_creation ASC, id ASC LIMIT 1 FOR UPDATE'
                );
                $debtStmt->execute([
                    ':eleve_id' => $request['eleve_id'],
                    ':frais_id' => $request['frais_id'],
                ]);
                $debt = $debtStmt->fetch(PDO::FETCH_ASSOC);
                if (!$debt || (float) $request['montant'] > (float) $debt['montant_restant']) {
                    $db->rollBack();
                    return false;
                }

                $reduce = $db->prepare('UPDATE dettes_eleves SET montant_restant = GREATEST(0, montant_restant - :montant) WHERE id = :id');
                $reduce->execute([':montant' => $request['montant'], ':id' => $debt['id']]);
            }

            $update = $db->prepare(
                'UPDATE exonerations
                 SET statut = :statut, commentaire = :commentaire, validateur_id = :validateur_id, decided_at = NOW()
                 WHERE id = :id'
            );
            $update->execute([
                ':statut' => $status,
                ':commentaire' => $commentaire !== '' ? $commentaire : null,
                ':validateur_id' => $validateurId,
                ':id' => $id,
            ]);

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Exoneration::decide failed: ' . $e->getMessage());
            return false;
        }
    }
}
