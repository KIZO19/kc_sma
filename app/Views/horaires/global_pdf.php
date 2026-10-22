<?php
$dayNames = $days ?? [];
$schedule = $schedule ?? [];
$groupedSlots = [];
foreach ($schedule as $slot) {
    $groupedSlots[(int) $slot['jour_id']][(int) $slot['periode']][(int) $slot['classe_id']] = $slot;
}
$classes = $classes ?? [];
$teacherWorkloads = $teacherWorkloads ?? [];
$formatTime = static fn (int $minutes): string => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?= $escape($title ?? 'Horaire global des classes') ?></title>
  <style>
    @page { size: A3 landscape; margin: 10mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: <?= count($classes) > 24 ? 5 : (count($classes) > 14 ? 6 : 8) ?>px; color: #20252b; }
    h1 { font-size: 16px; margin: 0 0 3px; }
    .meta { margin: 0 0 7px; color: #46515c; }
    table { border-collapse: collapse; table-layout: fixed; width: 100%; }
    th, td { border: 1px solid #aeb8c2; padding: 2px; vertical-align: top; overflow-wrap: anywhere; }
    thead th { background: #e9f0f7; text-align: center; }
    .day { width: 55px; text-align: center; }
    .time { width: 46px; text-align: center; white-space: nowrap; }
    .lesson { page-break-inside: avoid; }
    .teacher { color: #394957; }
    .conflict { background: #fde2e2; border-color: #c62828; }
    .conflict-label { color: #a31515; font-weight: bold; }
    .recess th { background: #fff2cc; text-align: center; }
    .empty { color: #87919b; text-align: center; }
    .days-off { margin-top: 7px; page-break-inside: avoid; }
    .days-off h2 { font-size: 10px; margin: 0 0 3px; }
    .days-off-list { display: flex; flex-wrap: wrap; gap: 2px 8px; }
    .days-off-item { white-space: nowrap; }
    .print { margin-bottom: 12px; }
    @media print {
      .print { display: none; }
      html, body { width: 100%; height: 100%; }
      table { page-break-inside: avoid; }
      tr { page-break-inside: avoid; }
    }
  </style>
</head>
<body>
  <?php if (!empty($printFallback)): ?>
    <div class="print"><button onclick="window.print()">Imprimer / Enregistrer en PDF</button></div>
  <?php endif; ?>
  <h1><?= $escape($schoolName ?? '') ?> — <?= $escape($title ?? 'Horaire global des classes') ?></h1>
  <p class="meta">
    Section : <strong><?= $escape($sectionName ?? '') ?></strong> ·
    Année scolaire : <strong><?= $escape($schoolYear ?? '') ?></strong> ·
    Durée d’une heure : <strong><?= (int) ($hourDuration ?? 50) ?> minutes</strong> ·
    Généré le <?= $escape($generatedAt ?? '') ?>
  </p>
  <table>
    <thead>
      <tr>
        <th class="day">Jour</th>
        <th class="time">Heure</th>
        <?php foreach ($classes as $class): ?>
          <th><?= $escape($class['nom_classe']) ?><?= !empty($class['nom_option']) ? '<br>' . $escape($class['nom_option']) : '' ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($dayNames as $dayId => $day): ?>
        <?php foreach (($dailySchedule['timeline'] ?? []) as $timelineItem): ?>
          <?php if (($timelineItem['type'] ?? '') === 'recess'): ?>
            <tr class="recess">
              <th><?= $escape($day) ?></th>
              <th><?= $formatTime((int) $timelineItem['start']) ?>–<?= $formatTime((int) $timelineItem['end']) ?></th>
              <td colspan="<?= max(1, count($classes)) ?>">Récréation</td>
            </tr>
            <?php continue; ?>
          <?php endif; ?>
          <?php $period = (int) $timelineItem['hour']; ?>
          <tr>
            <th class="day"><?= $escape($day) ?></th>
            <th class="time">
              <?= $period ?><br>
              <?= $formatTime((int) $timelineItem['start']) ?>–<?= $formatTime((int) $timelineItem['end']) ?>
            </th>
            <?php foreach ($classes as $class): ?>
              <?php $slot = $groupedSlots[$dayId][$period][(int) $class['id']] ?? null; ?>
              <td class="<?= $slot && !empty($slot['teacher_conflict']) ? 'conflict' : '' ?>">
                <?php if ($slot): ?>
                  <div class="lesson">
                    <strong><?= $escape($slot['nom_cours']) ?></strong><br>
                    <span class="teacher">I<?= (int) $slot['indice'] ?></span>
                    <?php if (!empty($slot['teacher_conflict'])): ?><br><span class="conflict-label">CONFLIT</span><?php endif; ?>
                  </div>
                <?php else: ?>
                  <span class="empty">—</span>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
  <section class="days-off">
    <h2>Journées hebdomadaires des enseignants</h2>
    <?php if (empty($teacherWorkloads)): ?>
      <div>Aucun enseignant n’est affecté dans cette section.</div>
    <?php else: ?>
      <div class="days-off-list">
        <?php foreach ($teacherWorkloads as $teacher): ?>
          <span class="days-off-item">
            <strong>I<?= (int) $teacher['indice'] ?></strong>
            <?= $escape(trim(
                (string) ($teacher['nom'] ?? '') . ' '
                . (string) ($teacher['postnom'] ?? '') . ' '
                . (string) ($teacher['prenom'] ?? '')
            )) ?> :
            <?= $escape($dayNames[(int) ($teacher['jour_libre'] ?? 0)] ?? 'Non définie') ?>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</body>
</html>
