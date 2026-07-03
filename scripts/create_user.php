<?php
// Usage: php scripts/create_user.php --identifiant=comp1 --nom="Comptable Test" --password=Secret123 --ecole=1
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Router.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

// parse args
$opts = getopt('', ['identifiant:', 'nom::', 'password::', 'ecole::']);
$identifiant = $opts['identifiant'] ?? null;
$nom = $opts['nom'] ?? ($identifiant ? 'Comptable ' . $identifiant : 'Comptable Test');
$password = $opts['password'] ?? null;
$ecole_id = isset($opts['ecole']) ? (int)$opts['ecole'] : null;

if (!$identifiant) {
    echo "identifiant is required.\n";
    exit(1);
}
if (!$password) {
    // generate a random password
    $password = bin2hex(random_bytes(4));
    echo "Generated password: $password\n";
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    $user = App\Models\User::create([
        'nom_complet' => $nom,
        'identifiant' => $identifiant,
        'mot_de_passe' => $hashed,
        'role' => 'comptable_école',
        'statut' => 'Actif',
        'ecole_id' => $ecole_id,
    ]);

    if (!empty($user['id'])) {
        echo "Comptable créé avec ID: " . $user['id'] . "\n";
        echo "Identifiant: " . $identifiant . "\n";
        echo "Mot de passe (clair): " . $password . "\n";
    } else {
        echo "Échec de la création de l'utilisateur.\n";
    }
} catch (Throwable $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
