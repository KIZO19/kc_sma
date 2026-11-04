<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/Config/config.php';
require dirname(__DIR__) . '/app/Core/Database.php';
require dirname(__DIR__) . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

try {
    $stmt = \App\Core\Database::getConnection()->query(
        "SELECT id, ecole_id, role, section_id, reference_id
         FROM utilisateurs
         WHERE statut = 'Actif' AND ecole_id > 0
         ORDER BY id ASC"
    );
    $count = 0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
        \App\Models\EvenementNotification::deliverDueForUser($user);
        $count++;
    }

    echo 'Processed event notifications for ' . $count . " active school accounts.\n";
} catch (\Throwable $exception) {
    fwrite(STDERR, 'Event push dispatch failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
