<?php
require __DIR__ . '/../app/Config/config.php';

$raw = file_get_contents(__DIR__ . '/../ELELVES.csv');
$encoding = mb_detect_encoding($raw, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true) ?: 'Windows-1252';
$csv = mb_convert_encoding($raw, 'UTF-8', $encoding);
$lines = preg_split('/\r\n|\n|\r/', $csv);
$header = str_getcsv((string) array_shift($lines), ';');
$rows = [];
foreach ($lines as $line) {
    if (trim($line) === '') {
        continue;
    }
    $values = str_getcsv($line, ';');
    if (count($values) === count($header)) {
        $rows[] = array_combine($header, $values);
    }
}

$ids = [];
$combos = [];
$missingNames = [];
$missingBirthdays = 0;
$invalidBirthdays = [];
$missingGenders = 0;
$invalidGenders = [];
$badRows = [];
foreach ($rows as $row) {
    $id = trim((string) ($row['PupId'] ?? ''));
    if ($id !== '') {
        $ids[] = $id;
    }
    $combo = implode(' | ', [trim((string) ($row['Section'] ?? '')), trim((string) ($row['Class'] ?? '')), trim((string) ($row['Option'] ?? ''))]);
    $combos[$combo] = ($combos[$combo] ?? 0) + 1;
    if (trim((string) ($row['Name'] ?? '')) === '' || trim((string) ($row['Post Name'] ?? '')) === '') {
        $missingNames[] = [$id, $row['Name'] ?? '', $row['Post Name'] ?? '', $row['PreName'] ?? '', $row['SearchName'] ?? ''];
    }
    $birthday = trim((string) ($row['BirthDay'] ?? ''));
    if ($birthday === '') {
        $missingBirthdays++;
    } elseif (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $birthday)) {
        $invalidBirthdays[$birthday] = ($invalidBirthdays[$birthday] ?? 0) + 1;
    }
    $gender = trim((string) ($row['Gender'] ?? ''));
    if ($gender === '') {
        $missingGenders++;
    } elseif (!in_array($gender, ['Féminin', 'Masculin'], true)) {
        $invalidGenders[$gender] = ($invalidGenders[$gender] ?? 0) + 1;
    }
    if (trim((string) ($row['Name'] ?? '')) === '' || trim((string) ($row['Post Name'] ?? '')) === '') {
        $badRows[] = $row;
    }
}

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$existing = $pdo->query('SELECT id, num_permanent, matricule, nom, postnom, prenom, ecole_id FROM eleves')->fetchAll(PDO::FETCH_ASSOC);
$existingPermanent = [];
$existingMatricules = [];
foreach ($existing as $student) {
    $permanent = trim((string) ($student['num_permanent'] ?? ''));
    if ($permanent !== '') {
        $existingPermanent[$permanent] = $student;
    }
    $matricule = strtoupper(trim((string) ($student['matricule'] ?? '')));
    if ($matricule !== '') {
        $existingMatricules[$matricule] = $student;
    }
}
$permanentMatches = [];
$matriculeMatches = [];
foreach ($rows as $row) {
    $id = trim((string) ($row['PupId'] ?? ''));
    if ($id !== '' && isset($existingPermanent[$id])) {
        $permanentMatches[$id] = $existingPermanent[$id];
    }
    if ($id !== '' && isset($existingMatricules[strtoupper($id)])) {
        $matriculeMatches[$id] = $existingMatricules[strtoupper($id)];
    }
}

$classes = $pdo->query('SELECT c.id, c.nom_classe, s.nom_section, o.nom_option FROM classes c LEFT JOIN sections s ON s.id = c.section_id LEFT JOIN options o ON o.id = c.option_id WHERE c.ecole_id = 4')->fetchAll(PDO::FETCH_ASSOC);
$schoolFourStudents = $pdo->query('SELECT id, matricule, num_permanent, nom, postnom, prenom FROM eleves WHERE ecole_id = 4')->fetchAll(PDO::FETCH_ASSOC);
$options = $pdo->query('SELECT id, nom_option FROM options')->fetchAll(PDO::FETCH_ASSOC);
$sections = $pdo->query('SELECT id, nom_section FROM sections')->fetchAll(PDO::FETCH_ASSOC);

echo 'ENCODING=' . $encoding . PHP_EOL;
echo 'ROWS=' . count($rows) . ' SOURCE_DUPLICATE_IDS=' . (count($ids) - count(array_unique($ids))) . PHP_EOL;
echo 'EXISTING_PERMANENT_MATCHES=' . json_encode(array_keys($permanentMatches), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'EXISTING_MATRICULE_MATCHES=' . json_encode(array_keys($matriculeMatches), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'CLASSES=' . json_encode($classes, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'SECTIONS=' . json_encode($sections, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'OPTIONS=' . json_encode($options, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'CSV_CLASS_COMBINATIONS=' . json_encode($combos, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'MISSING_NAMES=' . json_encode($missingNames, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'BAD_ROWS=' . json_encode($badRows, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'SCHOOL4_STUDENTS=' . json_encode($schoolFourStudents, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'MISSING_BIRTHDAYS=' . $missingBirthdays . ' INVALID_BIRTHDAYS=' . json_encode($invalidBirthdays, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'MISSING_GENDERS=' . $missingGenders . ' INVALID_GENDERS=' . json_encode($invalidGenders, JSON_UNESCAPED_UNICODE) . PHP_EOL;
