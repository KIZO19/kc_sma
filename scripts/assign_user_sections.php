<?php
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ]);

    $pdo->exec("UPDATE utilisateurs SET section_id = 3 WHERE role IN ('préfet_école','DE_école','DD_école')");
    $pdo->exec("UPDATE utilisateurs SET section_id = 2 WHERE role IN ('DP_école','DA_école')");

    $stmt = $pdo->query("SELECT id, role, section_id, identifiant FROM utilisateurs WHERE role IN ('préfet_école','DE_école','DD_école','DP_école','DA_école') ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['id'] . '\t' . $row['role'] . '\t' . $row['section_id'] . '\t' . $row['identifiant'] . "\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
