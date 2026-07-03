<?php
// Usage: php scripts/create_accounts_bulk.php --type=agents|parents|eleves|all [--ecole=1]
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

$opts = getopt('', ['type::', 'ecole::']);
$type = $opts['type'] ?? 'all';
$ecole = isset($opts['ecole']) ? (int)$opts['ecole'] : null;

$created = [];

try {
    if ($type === 'agents' || $type === 'all') {
        $agents = App\Models\Agent::getAll($ecole);
        foreach ($agents as $a) {
            try {
                $user = App\Models\Agent::createUserAccount((int)$a['id']);
                $created[] = ['role' => 'agent', 'reference' => $a['id'], 'identifiant' => $user['identifiant'] ?? '(?)'];
            } catch (Throwable $e) {
                $created[] = ['role' => 'agent', 'reference' => $a['id'], 'skipped' => $e->getMessage()];
            }
        }
    }

    if ($type === 'parents' || $type === 'all') {
        $db = App\Core\Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM parents' . ($ecole ? ' WHERE ecole_id = :ecole' : ''));
        if ($ecole) $stmt->execute([':ecole' => $ecole]); else $stmt->execute();
        $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($parents as $p) {
            try {
                $user = App\Models\ParentModel::createUserAccount((int)$p['id']);
                $created[] = ['role' => 'parent', 'reference' => $p['id'], 'identifiant' => $user['identifiant'] ?? '(?)'];
            } catch (Throwable $e) {
                $created[] = ['role' => 'parent', 'reference' => $p['id'], 'skipped' => $e->getMessage()];
            }
        }
    }

    if ($type === 'eleves' || $type === 'all') {
        $db = App\Core\Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM eleves' . ($ecole ? ' WHERE ecole_id = :ecole' : ''));
        if ($ecole) $stmt->execute([':ecole' => $ecole]); else $stmt->execute();
        $eleves = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($eleves as $e) {
            try {
                $user = App\Models\Eleve::createUserAccount((int)$e['id']);
                $created[] = ['role' => 'eleve', 'reference' => $e['id'], 'identifiant' => $user['identifiant'] ?? '(?)'];
            } catch (Throwable $e) {
                $created[] = ['role' => 'eleve', 'reference' => $e['id'], 'skipped' => $e->getMessage()];
            }
        }
    }

    echo "Created/verified accounts:\n";
    foreach ($created as $c) {
        echo json_encode($c, JSON_UNESCAPED_UNICODE) . "\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
