<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$dayNames = $days ?? [];
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
$selectedClass = null;
foreach (($classes ?? []) as $class) {
  if ((int) $class['id'] === (int) ($classId ?? 0)) {
    $selectedClass = $class;
    break;
  }
}
$editingAssignment = $editingAssignment ?? null;
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Gestion des horaires</h1>
    <p class="text-muted mb-0">Section secondaire · Les périodes durent 50 minutes. La récréation est prévue à 10 h pendant 15 minutes.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (empty($year)): ?>
      <div class="alert alert-warning">Aucune année scolaire active n’est configurée pour cette école.</div>
    <?php else: ?>
      <div class="alert alert-info">
        Année scolaire active : <strong><?= htmlspecialchars((string) $year['annee'], ENT_QUOTES, 'UTF-8') ?></strong>.
        Première période à 7 h 30; trois périodes précèdent la récréation de 10 h.
      </div>

      <div class="card card-outline card-primary">
        <div class="card-header"><h2 class="card-title">Affectations des cours</h2></div>
        <div class="card-body">
          <form method="post" action="<?= BASE_URL ?>/horaires/save-assignment" class="row g-3 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="assignment_id" value="<?= (int) ($editingAssignment['id'] ?? 0) ?>">
            <div class="col-12 col-md-3">
              <label class="form-label" for="assignmentClass">Classe</label>
              <select class="form-select" id="assignmentClass" name="classe_id" required>
                <option value="">Choisir une classe</option>
                <?php foreach (($classes ?? []) as $class): ?>
                  <option value="<?= (int) $class['id'] ?>" <?= (int) ($editingAssignment['classe_id'] ?? $classId) === (int) $class['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $class['nom_classe'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label" for="assignmentCourse">Cours</label>
              <select class="form-select" id="assignmentCourse" name="cours_id" required>
                <option value="">Choisir un cours</option>
                <?php foreach (($courses ?? []) as $course): ?>
                  <option value="<?= (int) $course['id'] ?>" <?= (int) ($editingAssignment['cours_id'] ?? 0) === (int) $course['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $course['nom_cours'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label" for="assignmentTeacher">Enseignant</label>
              <select class="form-select" id="assignmentTeacher" name="agent_id" required>
                <option value="">Choisir un enseignant</option>
                <?php foreach (($teachers ?? []) as $teacher): ?>
                  <option value="<?= (int) $teacher['id'] ?>" <?= (int) ($editingAssignment['agent_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(\App\Core\PersonName::format($teacher), ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-1">
              <label class="form-label" for="assignmentVolume">Périodes / sem.</label>
              <input class="form-control" id="assignmentVolume" name="volume_horaire_hebdo" type="number" min="1" max="42" value="<?= (int) ($editingAssignment['volume_horaire_hebdo'] ?? 2) ?>" required>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
              <button class="btn btn-primary flex-grow-1" type="submit"><?= $editingAssignment ? 'Modifier' : 'Affecter' ?></button>
              <?php if ($editingAssignment): ?><a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/horaires?classe_id=<?= (int) $classId ?>">Annuler</a><?php endif; ?>
            </div>
          </form>
          <p class="form-text mb-0 mt-2">Le volume indiqué correspond au nombre de périodes hebdomadaires de 50 minutes.</p>
        </div>
      </div>

      <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h2 class="card-title">Planning hebdomadaire</h2>
          <form method="get" action="<?= BASE_URL ?>/horaires" class="d-flex gap-2 align-items-center">
            <label for="timetableClass" class="form-label mb-0">Classe</label>
            <select class="form-select form-select-sm" id="timetableClass" name="classe_id" onchange="this.form.submit()">
              <?php foreach (($classes ?? []) as $class): ?>
                <option value="<?= (int) $class['id'] ?>" <?= (int) $classId === (int) $class['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string) $class['nom_classe'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        <div class="card-body p-0">
          <?php if (!$selectedClass): ?>
            <div class="p-4 text-muted">Aucune classe n’est disponible dans votre section secondaire.</div>
          <?php elseif (!$classAssignments): ?>
            <div class="p-4 text-muted">Aucun cours n’est affecté à cette classe. Créez d’abord ses affectations ci-dessus.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle text-center mb-0">
                <thead class="table-light">
                  <tr><th>Période</th><?php foreach ($dayNames as $day): ?><th><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                  <?php for ($period = 1; $period <= 7; $period++): ?>
                    <?php [$startMinute, $endMinute] = $periodStart($period); ?>
                    <?php if ($period === 4): ?>
                      <tr class="table-warning"><th colspan="7">Récréation · 10:00–10:15</th></tr>
                    <?php endif; ?>
                    <tr>
                      <th class="text-nowrap">
                        <?= $period ?><br><small class="text-muted"><?= $formatTime($startMinute) ?>–<?= $formatTime($endMinute) ?></small>
                      </th>
                      <?php foreach ($dayNames as $dayId => $day): ?>
                        <?php $slot = $schedule[$dayId][$period] ?? null; ?>
                        <td class="timetable-cell">
                          <?php if ($slot): ?>
                            <div class="fw-semibold"><?= htmlspecialchars((string) $slot['nom_cours'], ENT_QUOTES, 'UTF-8') ?></div>
                            <span class="badge text-bg-primary">Indice <?= (int) $slot['indice'] ?></span>
                            <form method="post" action="<?= BASE_URL ?>/horaires/delete-slot" class="mt-1">
                              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                              <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                              <input type="hidden" name="classe_id" value="<?= (int) $classId ?>">
                              <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Supprimer le créneau">Retirer</button>
                            </form>
                          <?php else: ?>
                            <form method="post" action="<?= BASE_URL ?>/horaires/save-slot" class="d-grid gap-1">
                              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                              <input type="hidden" name="classe_id" value="<?= (int) $classId ?>">
                              <input type="hidden" name="jour_id" value="<?= (int) $dayId ?>">
                              <input type="hidden" name="periode" value="<?= $period ?>">
                              <select class="form-select form-select-sm" name="attribution_id" aria-label="Cours et enseignant pour <?= htmlspecialchars($day . ' période ' . $period, ENT_QUOTES, 'UTF-8') ?>" required>
                                <option value="">Cours · indice</option>
                                <?php foreach ($classAssignments as $assignment): ?>
                                  <option value="<?= (int) $assignment['id'] ?>">
                                    <?= htmlspecialchars((string) $assignment['nom_cours'], ENT_QUOTES, 'UTF-8') ?> · I<?= (int) $assignment['indice'] ?>
                                  </option>
                                <?php endforeach; ?>
                              </select>
                              <button class="btn btn-sm btn-outline-primary" type="submit">Planifier</button>
                            </form>
                          <?php endif; ?>
                        </td>
                      <?php endforeach; ?>
                    </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card card-outline card-info">
        <div class="card-header"><h2 class="card-title">Charge des enseignants et journée hebdomadaire</h2></div>
        <div class="card-body p-0">
          <?php if (empty($teacherWorkloads)): ?>
            <div class="p-4 text-muted">Aucune affectation d’enseignant dans cette section.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Indice</th><th>Enseignant</th><th>Charge hebdomadaire</th><th>Journée libre</th></tr></thead>
                <tbody>
                  <?php foreach ($teacherWorkloads as $workload): ?>
                    <?php $hasWeeklyDayOff = (int) $workload['volume_total'] > 22; ?>
                    <tr>
                      <td><span class="badge text-bg-primary"><?= (int) $workload['indice'] ?></span></td>
                      <td><?= htmlspecialchars(\App\Core\PersonName::format($workload), ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="<?= $hasWeeklyDayOff ? 'fw-semibold text-success' : '' ?>">
                        <?= (int) $workload['volume_total'] ?> périodes<?= $hasWeeklyDayOff ? ' · temps plein' : '' ?>
                      </td>
                      <td>
                        <?php if (!$hasWeeklyDayOff): ?>
                          <span class="text-muted">Non requise</span>
                        <?php else: ?>
                          <form method="post" action="<?= BASE_URL ?>/horaires/save-day-off" class="d-flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="agent_id" value="<?= (int) $workload['agent_id'] ?>">
                            <select class="form-select form-select-sm" name="jour_id" aria-label="Journée hebdomadaire de l’enseignant" required>
                              <option value="">Choisir le jour libre</option>
                              <?php foreach ($dayNames as $dayId => $day): ?>
                                <option value="<?= (int) $dayId ?>" <?= (int) ($workload['jour_libre'] ?? 0) === (int) $dayId ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-outline-primary text-nowrap" type="submit">Enregistrer</button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h2 class="card-title">Affectations enregistrées</h2></div>
        <div class="card-body p-0">
          <?php if (empty($assignments)): ?>
            <div class="p-4 text-muted">Aucune affectation n’est enregistrée.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <thead><tr><th>Classe</th><th>Cours</th><th>Enseignant</th><th>Indice</th><th>Volume / semaine</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($assignments as $assignment): ?>
                    <tr>
                      <td><?= htmlspecialchars((string) $assignment['nom_classe'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars((string) $assignment['nom_cours'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars(\App\Core\PersonName::format($assignment), ENT_QUOTES, 'UTF-8') ?></td>
                      <td><span class="badge text-bg-primary"><?= (int) $assignment['indice'] ?></span></td>
                      <td><?= (int) $assignment['volume_horaire_hebdo'] ?> périodes</td>
                      <td><a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/horaires?classe_id=<?= (int) $assignment['classe_id'] ?>&amp;edit_id=<?= (int) $assignment['id'] ?>">Modifier</a></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
