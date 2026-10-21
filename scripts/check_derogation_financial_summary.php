<?php
require_once dirname(__DIR__) . '/app/Config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});
$requests = App\Models\Derogation::getAllBySchool(4);
$summary = App\Models\Exoneration::getFinancialSummaryBySchool(4);
echo 'DEROGATION_REQUESTS=' . count($requests) . PHP_EOL;
echo 'STUDENTS_WITH_FINANCIAL_SUMMARY=' . count($summary) . PHP_EOL;
if ($requests) {
    $row = $requests[0];
    echo 'REQUEST_HAS_FINANCIAL_SUMMARY=' . (isset($row['financial_summary']) ? 'yes' : 'no') . PHP_EOL;
}
