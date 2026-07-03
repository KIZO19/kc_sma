<?php
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use PDO;

try {
    $dsn = DB_DSN;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);

    $password = 'Test1234!';
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $accounts = [
        ['role' => 'super_admin', 'identifiant' => 'super_admin@test.local', 'nom_complet' => 'Super Admin Test', 'ecole_id' => null],
        ['role' => 'ecole_admin', 'identifiant' => 'ecole_admin@test.local', 'nom_complet' => 'École Admin Test', 'ecole_id' => 1],
        ['role' => 'préfet_école', 'identifiant' => 'prefet_ecole@test.local', 'nom_complet' => 'Préfet École Test', 'ecole_id' => 1],
        ['role' => 'DE_école', 'identifiant' => 'DE_ecole@test.local', 'nom_complet' => 'Directeur des études Test', 'ecole_id' => 1],
        ['role' => 'DD_école', 'identifiant' => 'DD_ecole@test.local', 'nom_complet' => 'Directeur Département Test', 'ecole_id' => 1],
        ['role' => 'DP_école', 'identifiant' => 'DP_ecole@test.local', 'nom_complet' => 'Directeur Principal Test', 'ecole_id' => 1],
        ['role' => 'DA_école', 'identifiant' => 'DA_ecole@test.local', 'nom_complet' => 'Directeur Adjoint Test', 'ecole_id' => 1],
        ['role' => 'comptable_école', 'identifiant' => 'comptable_ecole@test.local', 'nom_complet' => 'Comptable École Test', 'ecole_id' => 1],
        ['role' => 'sec_école', 'identifiant' => 'sec_ecole@test.local', 'nom_complet' => 'Secrétaire École Test', 'ecole_id' => 1],
        ['role' => 'promoteur_école', 'identifiant' => 'promoteur_ecole@test.local', 'nom_complet' => 'Promoteur École Test', 'ecole_id' => 1],
        ['role' => 'enseignant_école', 'identifiant' => 'enseignant_ecole@test.local', 'nom_complet' => 'Enseignant École Test', 'ecole_id' => 1],
        ['role' => 'eleve_ecole', 'identifiant' => 'eleve_ecole@test.local', 'nom_complet' => 'Élève École Test', 'ecole_id' => 1],
        ['role' => 'parent_ecole', 'identifiant' => 'parent_ecole@test.local', 'nom_complet' => 'Parent École Test', 'ecole_id' => 1],
    ];

    $identifiants = array_column($accounts, 'identifiant');
    $placeholders = implode(', ', array_fill(0, count($identifiants), '?'));
    $deleteStmt = $pdo->prepare("DELETE FROM utilisateurs WHERE identifiant IN ($placeholders)");
    $deleteStmt->execute($identifiants);

    $insertStmt = $pdo->prepare(
        'INSERT INTO utilisateurs (nom_complet, identifiant, mot_de_passe, role, ecole_id, statut, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );

    foreach ($accounts as $account) {
        $insertStmt->execute([
            $account['nom_complet'],
            $account['identifiant'],
            $hashed,
            $account['role'],
            $account['ecole_id'],
            'Actif',
        ]);
    }

    $selectStmt = $pdo->prepare("SELECT id, role, identifiant FROM utilisateurs WHERE identifiant IN ($placeholders) ORDER BY id ASC");
    $selectStmt->execute($identifiants);
    $rows = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        echo $row['id'] . ' | ' . $row['role'] . ' | ' . $row['identifiant'] . "\n";
    }
    echo "PASSWORD_CLEAR: $password\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
