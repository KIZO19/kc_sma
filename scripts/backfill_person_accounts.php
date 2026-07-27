<?php
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getConnection();

$created = 0;

$agents = $db->query('SELECT id, ecole_id, nom, postnom, prenom, email, telephone FROM agents ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($agents as $agent) {
    $exists = $db->prepare('SELECT id FROM utilisateurs WHERE role = :role AND reference_id = :id LIMIT 1');
    $exists->execute([':role' => 'enseignant_école', ':id' => (int) $agent['id']]);
    if ($exists->fetch()) {
        continue;
    }

    $identifiant = $agent['email'] ?: $agent['telephone'] ?: 'agent' . $agent['id'] . '@local';
    $nom = trim(($agent['nom'] ?? '') . ' ' . ($agent['postnom'] ?? '') . ' ' . ($agent['prenom'] ?? ''));
    $stmt = $db->prepare('INSERT INTO utilisateurs (nom_complet, identifiant, mot_de_passe, role, ecole_id, reference_id, statut, created_at) VALUES (:nom, :identifiant, :pass, :role, :ecole, :ref, :statut, NOW())');
    $stmt->execute([
        ':nom' => $nom,
        ':identifiant' => $identifiant,
        ':pass' => password_hash('Test1234!', PASSWORD_DEFAULT),
        ':role' => 'enseignant_école',
        ':ecole' => $agent['ecole_id'] ?? null,
        ':ref' => (int) $agent['id'],
        ':statut' => 'Actif',
    ]);
    $created++;
}

$parents = $db->query('SELECT id, ecole_id, nom_responsable, email, telephone FROM parents ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($parents as $parent) {
    $exists = $db->prepare('SELECT id FROM utilisateurs WHERE role = :role AND reference_id = :id LIMIT 1');
    $exists->execute([':role' => 'parent_ecole', ':id' => (int) $parent['id']]);
    if ($exists->fetch()) {
        continue;
    }

    $identifiant = $parent['email'] ?: $parent['telephone'] ?: 'parent' . $parent['id'] . '@local';
    $stmt = $db->prepare('INSERT INTO utilisateurs (nom_complet, identifiant, mot_de_passe, role, ecole_id, reference_id, statut, created_at) VALUES (:nom, :identifiant, :pass, :role, :ecole, :ref, :statut, NOW())');
    $stmt->execute([
        ':nom' => $parent['nom_responsable'] ?? $identifiant,
        ':identifiant' => $identifiant,
        ':pass' => password_hash('Test1234!', PASSWORD_DEFAULT),
        ':role' => 'parent_ecole',
        ':ecole' => $parent['ecole_id'] ?? null,
        ':ref' => (int) $parent['id'],
        ':statut' => 'Actif',
    ]);
    $created++;
}

$eleves = $db->query('SELECT id, ecole_id, parent_id, matricule, nom, prenom FROM eleves ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($eleves as $eleve) {
    $exists = $db->prepare('SELECT id FROM utilisateurs WHERE role = :role AND reference_id = :id LIMIT 1');
    $exists->execute([':role' => 'eleve_ecole', ':id' => (int) $eleve['id']]);
    if ($exists->fetch()) {
        continue;
    }

    $ecoleId = $eleve['ecole_id'] ?? null;
    if (empty($ecoleId) && !empty($eleve['parent_id'])) {
        $parent = $db->prepare('SELECT ecole_id FROM parents WHERE id = :pid LIMIT 1');
        $parent->execute([':pid' => (int) $eleve['parent_id']]);
        $p = $parent->fetch(PDO::FETCH_ASSOC);
        $ecoleId = $p['ecole_id'] ?? null;
    }

    $identifiant = $eleve['matricule'] ?: 'eleve' . $eleve['id'] . '@school.local';
    $nom = trim(($eleve['prenom'] ?? '') . ' ' . ($eleve['nom'] ?? ''));
    $stmt = $db->prepare('INSERT INTO utilisateurs (nom_complet, identifiant, mot_de_passe, role, ecole_id, reference_id, statut, created_at) VALUES (:nom, :identifiant, :pass, :role, :ecole, :ref, :statut, NOW())');
    $stmt->execute([
        ':nom' => $nom,
        ':identifiant' => $identifiant,
        ':pass' => password_hash('Test1234!', PASSWORD_DEFAULT),
        ':role' => 'eleve_ecole',
        ':ecole' => $ecoleId,
        ':ref' => (int) $eleve['id'],
        ':statut' => 'Actif',
    ]);
    $created++;
}

echo "Accounts created: {$created}\n";
