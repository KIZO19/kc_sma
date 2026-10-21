<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$periodStart = static function (int $period): array {
  $minutes = 7 * 60 + 30;
  for ($current = 1; $current < $period; $current++) {
    $minutes += 50;
    if ($current === 3) {
      $minutes += 15;
    }
  }
  return [$minutes, $minutes + 50];
};
$formatTime = static fn (int $minutes): string => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
$slotsByDay = [];
foreach (($schedule ?? []) as $slot) {
  $slotsByDay[(int) $slot['jour_id']][(int) $slot['periode']] = $slot;
}
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Mon emploi du temps</h1>
    <p class="text-muted mb-0">
      <?= !empty($isStudent) && !empty($className) ? 'Classe : ' . htmlspecialchars($className, ENT_QUOTES, 'UTF-8') . ' · ' : '' ?>
      Année scolaire <?= htmlspecialchars((string) ($year['annee'] ?? 'indisponible'), ENT_QUOTES, 'UTF-8') ?>
    </p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (empty($year)): ?>
      <div class="alert alert-warning">Aucune année scolaire active n’est disponible.</div>
    <?php elseif (!empty($isStudent) && empty($className)): ?>
      <div class="alert alert-info">Aucune classe active n’est associée à votre inscription.</div>
    <?php elseif (empty($schedule)): ?>
      <div class="alert alert-info">Aucun horaire n’a encore été publié pour votre compte.</div>
    <?php else: ?>
      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle text-center mb-0">
              <thead class="table-light">
                <tr><th>Période</th><?php foreach ($days as $day): ?><th><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?></tr>
              </thead>
              <tbody>
                <?php for ($period = 1; $period <= 7; $period++): ?>
                  <?php [$startMinute, $endMinute] = $periodStart($period); ?>
                  <?php if ($period === 4): ?><tr class="table-warning"><th colspan="7">Récréation · 10:00–10:15</th></tr><?php endif; ?>
                  <tr>
                    <th class="text-nowrap"><?= $period ?><br><small class="text-muted"><?= $formatTime($startMinute) ?>–<?= $formatTime($endMinute) ?></small></th>
                    <?php foreach ($days as $dayId => $day): ?>
                      <?php $slot = $slotsByDay[$dayId][$period] ?? null; ?>
                      <td>
                        <?php if ($slot): ?>
                          <strong><?= htmlspecialchars((string) $slot['nom_cours'], ENT_QUOTES, 'UTF-8') ?></strong>
                          <?php if (!empty($isStudent)): ?>
                            <span class="d-block small text-muted">Enseignant indice <?= (int) $slot['indice'] ?></span>
                          <?php else: ?>
                            <span class="d-block small text-muted"><?= htmlspecialchars((string) $slot['nom_classe'], ENT_QUOTES, 'UTF-8') ?> · Indice <?= (int) $slot['indice'] ?></span>
                          <?php endif; ?>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
