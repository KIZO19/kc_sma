<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Agent
{
    public static function getAll(int $ecoleId = null): array
    {
        $db = Database::getConnection();
        if ($ecoleId) {
            $stmt = $db->prepare('SELECT * FROM agents WHERE ecole_id = :ecole_id');
            $stmt->execute([':ecole_id' => $ecoleId]);
        } else {
            $stmt = $db->prepare('SELECT * FROM agents');
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM agents WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);

        return $agent ?: null;
    }

    public static function createUserAccount(int $agentId): array
    {
        $agent = self::findById($agentId);
        if (!$agent) {
            throw new \RuntimeException('Agent not found');
        }
        $ecoleId = $agent['ecole_id'] ?? null;
        if (empty($ecoleId)) {
            throw new \RuntimeException('Agent ' . $agent['id'] . ' has no ecole_id');
        }

        $identifiant = $agent['email'] ?: $agent['telephone'] ?: 'agent' . $agent['id'] . '@local';
        $password = bin2hex(random_bytes(4));

        return \App\Models\User::findOrCreateForReference([
            'role' => 'enseignant_école',
            'reference_id' => $agent['id'],
            'ecole_id' => $ecoleId,
            'identifiant' => $identifiant,
            'mot_de_passe' => $password,
            'nom_complet' => trim(($agent['nom'] ?? '') . ' ' . ($agent['postnom'] ?? '') . ' ' . ($agent['prenom'] ?? '')),
        ]);
    }
}
