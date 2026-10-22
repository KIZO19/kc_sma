<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$dayNames = $days ?? [];
$formatTime = static fn (int $minutes): string => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
$selectedClass = null;
foreach (($classes ?? []) as $class) {
  if ((int) $class['id'] === (int) ($classId ?? 0)) {
    $selectedClass = $class;
    break;
  }
}
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Gestion des horaires</h1>
    <p class="text-muted mb-0"><?= htmlspecialchars((string) ($sectionName ?? 'Section'), ENT_QUOTES, 'UTF-8') ?> · Chaque heure dure <?= (int) ($hourDuration ?? 50) ?> minutes.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <div class="card card-outline card-info">
      <div class="card-header"><h2 class="card-title">Paramètres horaires de la section</h2></div>
      <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/horaires/save-duration" class="row g-3 align-items-end">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
          <div class="col-12 col-md-5">
            <label class="form-label" for="hourDuration">Durée d’une heure de cours (minutes)</label>
            <input class="form-control" id="hourDuration" name="duree_heure_minutes" type="number" min="20" max="120" value="<?= (int) ($hourDuration ?? 50) ?>" required>
            <div class="form-text">Valeur propre à cette section. Autorisé : 20 à 120 minutes.</div>
          </div>
          <div class="col-auto"><button class="btn btn-info" type="submit">Enregistrer la durée</button></div>
        </form>
      </div>
    </div>
    <?php if (empty($year)): ?>
      <div class="alert alert-warning">Aucune année scolaire active n’est configurée pour cette école.</div>
    <?php else: ?>
      <div class="alert alert-info">
        Année scolaire active : <strong><?= htmlspecialchars((string) $year['annee'], ENT_QUOTES, 'UTF-8') ?></strong>.
        Première heure à 7 h 30. La récréation dure 15 minutes après l’heure qui atteint ou dépasse 10 h, sans interrompre un cours.
      </div>

      <div class="card card-outline card-primary">
        <div class="card-header"><h2 class="card-title"><?= !empty($editingCoursePlan) ? 'Modifier le cours et son programme' : 'Créer un cours et définir son volume' ?></h2></div>
        <div class="card-body">
          <form method="post" action="<?= BASE_URL ?>/horaires/save-course-plan" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="programme_id" value="<?= (int) ($editingCoursePlan['id'] ?? 0) ?>">
            <div class="col-12 col-md-4">
              <label class="form-label" for="courseName">Nom du cours</label>
              <input class="form-control" id="courseName" name="nom_cours" maxlength="100" value="<?= htmlspecialchars((string) ($editingCoursePlan['nom_cours'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-12 col-md-2">
              <label class="form-label" for="courseCode">Code du cours</label>
              <input class="form-control" id="courseCode" name="code_cours" maxlength="15" value="<?= htmlspecialchars((string) ($editingCoursePlan['code_cours'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label" for="courseNature">Type de cours</label>
              <select class="form-select" id="courseNature" name="nature_cours" required>
                <option value="commun" <?= ($editingCoursePlan['nature_cours'] ?? 'commun') === 'commun' ? 'selected' : '' ?>>Tronc commun (plusieurs classes possibles)</option>
                <option value="option" <?= ($editingCoursePlan['nature_cours'] ?? '') === 'option' ? 'selected' : '' ?>>Cours d’option (une seule classe)</option>
              </select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label" for="courseHours">Heures par semaine et par classe</label>
              <input class="form-control" id="courseHours" name="volume_horaire_hebdo" type="number" min="1" max="35" value="<?= (int) ($editingCoursePlan['volume_horaire_hebdo'] ?? 2) ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="courseClasses">Classes concernées</label>
              <select class="form-select" id="courseClasses" name="classe_ids[]" size="<?= ($editingCoursePlan['nature_cours'] ?? 'commun') === 'option' ? 1 : 5 ?>" <?= ($editingCoursePlan['nature_cours'] ?? 'commun') === 'option' ? '' : 'multiple' ?> required>
                <?php foreach (($classes ?? []) as $class): ?>
                  <?php $isCourseClass = false; foreach (($editingCoursePlan['classes'] ?? []) as $courseClass) { if ((int) $courseClass['id'] === (int) $class['id']) { $isCourseClass = true; break; } } ?>
                  <option value="<?= (int) $class['id'] ?>" <?= $isCourseClass ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $class['nom_classe'], ENT_QUOTES, 'UTF-8') ?><?= !empty($class['nom_option']) ? ' · ' . htmlspecialchars((string) $class['nom_option'], ENT_QUOTES, 'UTF-8') : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Un cours de tronc commun peut être rattaché à plusieurs classes. Un cours d’option doit être rattaché à une seule classe ayant une option.</div>
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><?= !empty($editingCoursePlan) ? 'Enregistrer les modifications' : 'Créer le cours' ?></button>
              <?php if (!empty($editingCoursePlan)): ?><a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/horaires">Annuler</a><?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <div class="card card-outline card-info">
        <div class="card-header"><h2 class="card-title">Cours de la section et attribution des enseignants</h2></div>
        <div class="card-body p-0">
          <?php if (empty($coursePlans)): ?>
            <div class="p-4 text-muted">Aucun cours n’est encore configuré pour cette section.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Cours</th><th>Type</th><th>Classes</th><th>Volume / classe</th><th>Charge hebdomadaire de l’enseignant</th><th>Enseignant commun</th><th>Action</th></tr></thead>
                <tbody>
                  <?php foreach ($coursePlans as $plan): ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars((string) $plan['nom_cours'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="d-block small text-muted"><?= htmlspecialchars((string) $plan['code_cours'], ENT_QUOTES, 'UTF-8') ?></span>
                      </td>
                      <td><?= $plan['nature_cours'] === 'commun' ? 'Tronc commun' : 'Cours d’option' ?></td>
                      <td>
                        <?php foreach ($plan['classes'] as $courseClass): ?>
                          <span class="badge text-bg-light border"><?= htmlspecialchars((string) $courseClass['nom_classe'], ENT_QUOTES, 'UTF-8') ?><?= !empty($courseClass['nom_option']) ? ' · ' . htmlspecialchars((string) $courseClass['nom_option'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                        <?php endforeach; ?>
                      </td>
                      <td><?= (int) $plan['volume_horaire_hebdo'] ?> heures</td>
                      <td>
                        <?php if (!empty($plan['agent_id'])): ?>
                          <?= (int) $plan['teacher_weekly_load'] ?> heures / semaine
                        <?php else: ?>
                          <span class="text-muted">Non calculée — aucun enseignant attribué</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($plan['agent_id'])): ?>
                          <?= htmlspecialchars(\App\Core\PersonName::format($plan), ENT_QUOTES, 'UTF-8') ?>
                          <span class="d-block small text-muted">Indice <?= (int) $plan['teacher_index'] ?></span>
                        <?php else: ?>
                          <span class="text-muted">À attribuer</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <form method="post" action="<?= BASE_URL ?>/horaires/assign-course-teacher" class="d-flex gap-2 mb-2">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="programme_id" value="<?= (int) $plan['id'] ?>">
                          <select class="form-select form-select-sm" name="agent_id" aria-label="Enseignant pour <?= htmlspecialchars((string) $plan['nom_cours'], ENT_QUOTES, 'UTF-8') ?>" required>
                            <option value="">Choisir un enseignant</option>
                            <?php foreach (($teachers ?? []) as $teacher): ?>
                              <option value="<?= (int) $teacher['id'] ?>" <?= (int) ($plan['agent_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(\App\Core\PersonName::format($teacher), ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <button class="btn btn-sm btn-outline-primary text-nowrap" type="submit"><?= empty($plan['agent_id']) ? 'Attribuer' : 'Changer' ?></button>
                        </form>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/horaires?edit_plan=<?= (int) $plan['id'] ?>">Modifier</a>
                        <form method="post" action="<?= BASE_URL ?>/horaires/delete-course-plan" class="d-inline" onsubmit="return confirm('Supprimer ce cours de cette section ? Les heures planifiées seront retirées. La suppression sera refusée si des évaluations ont déjà été saisies.');">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="programme_id" value="<?= (int) $plan['id'] ?>">
                          <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h2 class="card-title">Planning hebdomadaire</h2>
          <div class="d-flex flex-wrap gap-2 align-items-center">
          <a class="btn btn-sm btn-outline-danger" href="<?= BASE_URL ?>/horaires/export-pdf">
            <i class="bi bi-file-earmark-pdf me-1"></i>Exporter l’horaire global en PDF
          </a>
          <form method="get" action="<?= BASE_URL ?>/horaires" class="d-flex gap-2 align-items-center">
            <label for="timetableClass" class="form-label mb-0">Classe</label>
            <select class="form-select form-select-sm" id="timetableClass" name="classe_id" onchange="this.form.submit()">
              <option value="0" <?= !empty($isGlobalSchedule) ? 'selected' : '' ?>>Toutes les classes</option>
              <?php foreach (($classes ?? []) as $class): ?>
                <option value="<?= (int) $class['id'] ?>" <?= (int) $classId === (int) $class['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string) $class['nom_classe'], ENT_QUOTES, 'UTF-8') ?><?= !empty($class['nom_option']) ? ' · ' . htmlspecialchars((string) $class['nom_option'], ENT_QUOTES, 'UTF-8') : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
          </div>
        </div>
        <div class="card-body p-0">
          <?php if (empty($classes)): ?>
            <div class="p-4 text-muted">Aucune classe n’est disponible dans votre section.</div>
          <?php elseif (!empty($isGlobalSchedule)): ?>
            <div class="alert alert-info rounded-0 mb-0">
              Vue globale en lecture seule : les classes sont en colonnes et les jours/heures en lignes. Les créneaux en rouge signalent un conflit d’enseignant. Pour modifier le planning, sélectionnez une classe.
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle text-center mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Jour</th>
                    <th>Heure</th>
                    <?php foreach (($classes ?? []) as $class): ?>
                      <th style="min-width: 120px">
                        <?= htmlspecialchars((string) $class['nom_classe'], ENT_QUOTES, 'UTF-8') ?>
                        <?= !empty($class['nom_option']) ? '<br>' . htmlspecialchars((string) $class['nom_option'], ENT_QUOTES, 'UTF-8') : '' ?>
                      </th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($dayNames as $dayId => $day): ?>
                    <?php foreach (($dailySchedule['timeline'] ?? []) as $timelineItem): ?>
                      <?php if ($timelineItem['type'] === 'recess'): ?>
                        <tr class="table-warning">
                          <th><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></th>
                          <th><?= $formatTime((int) $timelineItem['start']) ?>–<?= $formatTime((int) $timelineItem['end']) ?></th>
                          <td colspan="<?= max(1, count($classes)) ?>">Récréation</td>
                        </tr>
                        <?php continue; ?>
                      <?php endif; ?>
                      <?php $period = (int) $timelineItem['hour']; ?>
                      <tr>
                        <th><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></th>
                        <th class="text-nowrap">
                          <?= $period ?><br><small class="text-muted"><?= $formatTime((int) $timelineItem['start']) ?>–<?= $formatTime((int) $timelineItem['end']) ?></small>
                        </th>
                        <?php foreach (($classes ?? []) as $class): ?>
                          <?php $slot = $schedule[$dayId][$period] ?? []; $slotForClass = null; ?>
                          <?php foreach ($slot as $candidate) { if ((int) $candidate['classe_id'] === (int) $class['id']) { $slotForClass = $candidate; break; } } ?>
                          <td class="text-start" style="min-width: 120px">
                            <?php if ($slotForClass): ?>
                              <div class="fw-semibold"><?= htmlspecialchars((string) $slotForClass['nom_cours'], ENT_QUOTES, 'UTF-8') ?></div>
                              <span class="badge text-bg-primary">I<?= (int) $slotForClass['indice'] ?></span>
                              <?php if (!empty($slotForClass['teacher_conflict'])): ?>
                                <span class="badge text-bg-danger">Conflit</span>
                              <?php endif; ?>
                            <?php else: ?>
                              <span class="text-muted">—</span>
                            <?php endif; ?>
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php elseif (!$selectedClass): ?>
            <div class="p-4 text-muted">Aucune classe n’est disponible dans votre section.</div>
          <?php elseif (!$classAssignments): ?>
            <div class="p-4 text-muted">Aucun cours n’est affecté à cette classe. Créez d’abord ses affectations ci-dessus.</div>
          <?php else: ?>
            <?php if (!$plannableAssignments): ?>
              <div class="alert alert-success rounded-0 mb-0">
                Toutes les heures hebdomadaires sont planifiées. Les créneaux existants restent affichés et peuvent être retirés.
              </div>
            <?php endif; ?>
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle text-center mb-0">
                <thead class="table-light">
                  <tr><th>Heure</th><?php foreach ($dayNames as $day): ?><th><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                  <?php foreach (($dailySchedule['timeline'] ?? []) as $timelineItem): ?>
                    <?php if ($timelineItem['type'] === 'recess'): ?>
                      <tr class="table-warning"><th colspan="7">Récréation · <?= $formatTime((int) $timelineItem['start']) ?>–<?= $formatTime((int) $timelineItem['end']) ?></th></tr>
                      <?php continue; ?>
                    <?php endif; ?>
                    <?php $period = (int) $timelineItem['hour']; ?>
                    <tr>
                      <th class="text-nowrap">
                        <?= $period ?><br><small class="text-muted"><?= $formatTime((int) $timelineItem['start']) ?>–<?= $formatTime((int) $timelineItem['end']) ?></small>
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
                              <select class="form-select form-select-sm" name="attribution_id" aria-label="Cours et enseignant pour <?= htmlspecialchars($day . ' heure ' . $period, ENT_QUOTES, 'UTF-8') ?>" required>
                                <option value="">
                                  <?= $plannableAssignments ? 'Cours · indice · heures restantes' : 'Aucune heure restante à planifier' ?>
                                </option>
                                <?php foreach ($plannableAssignments as $assignment): ?>
                                  <option
                                    value="<?= (int) $assignment['id'] ?>"
                                    data-assignment-id="<?= (int) $assignment['id'] ?>"
                                    data-course-name="<?= htmlspecialchars((string) $assignment['nom_cours'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-teacher-index="<?= (int) $assignment['indice'] ?>"
                                    data-remaining="<?= max(0, (int) $assignment['volume_horaire_hebdo'] - (int) $assignment['planned_hours']) ?>"
                                  ><?= htmlspecialchars((string) $assignment['nom_cours'], ENT_QUOTES, 'UTF-8') ?> · I<?= (int) $assignment['indice'] ?> · <?= max(0, (int) $assignment['volume_horaire_hebdo'] - (int) $assignment['planned_hours']) ?> h restantes</option>
                                <?php endforeach; ?>
                              </select>
                              <button class="btn btn-sm btn-outline-primary" type="submit" <?= !$plannableAssignments ? 'disabled' : '' ?>>Planifier</button>
                            </form>
                          <?php endif; ?>
                        </td>
                      <?php endforeach; ?>
                    </tr>
                  <?php endforeach; ?>
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
                        <?= (int) $workload['volume_total'] ?> heures<?= $hasWeeklyDayOff ? ' · temps plein' : '' ?>
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
                <thead><tr><th>Classe</th><th>Cours</th><th>Enseignant</th><th>Indice</th><th>Volume / semaine</th><th>Actions</th></tr></thead>
                <tbody>
                  <?php foreach ($assignments as $assignment): ?>
                    <tr>
                      <td><?= htmlspecialchars((string) $assignment['nom_classe'], ENT_QUOTES, 'UTF-8') ?><?= !empty($assignment['nom_option']) ? ' · ' . htmlspecialchars((string) $assignment['nom_option'], ENT_QUOTES, 'UTF-8') : '' ?></td>
                      <td><?= htmlspecialchars((string) $assignment['nom_cours'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars(\App\Core\PersonName::format($assignment), ENT_QUOTES, 'UTF-8') ?></td>
                      <td><span class="badge text-bg-primary"><?= (int) $assignment['indice'] ?></span></td>
                      <td><?= (int) $assignment['volume_horaire_hebdo'] ?> heures</td>
                      <td class="text-nowrap">
                        <?php if (!empty($assignment['programme_id'])): ?>
                          <span class="text-muted">Géré depuis le programme du cours</span>
                        <?php else: ?>
                          <form method="post" action="<?= BASE_URL ?>/horaires/delete-assignment" class="d-inline" onsubmit="return confirm('Supprimer cette affectation et ses créneaux ? Les affectations ayant déjà des évaluations ne peuvent pas être supprimées.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="assignment_id" value="<?= (int) $assignment['id'] ?>">
                            <input type="hidden" name="classe_id" value="<?= (int) $assignment['classe_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
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
    <?php endif; ?>
  </div>
</section>
<script>
  (() => {
    const nature = document.getElementById('courseNature');
    const classes = document.getElementById('courseClasses');
    if (!nature || !classes) return;

    const syncClassSelection = () => {
      const isOptionCourse = nature.value === 'option';
      if (isOptionCourse && classes.selectedOptions.length > 1) {
        Array.from(classes.options).forEach((option) => {
          if (option !== classes.selectedOptions[0]) option.selected = false;
        });
      }
      classes.multiple = !isOptionCourse;
      classes.size = isOptionCourse ? 1 : 5;
    };

    nature.addEventListener('change', syncClassSelection);
    syncClassSelection();
  })();

  (() => {
    const updateCsrfTokens = (token) => {
      if (!token) return;
      document.querySelectorAll('input[name="csrf_token"]').forEach((input) => {
        input.value = token;
      });
    };

    const updateAssignmentOptions = (assignments) => {
      const assignmentById = new Map(assignments.map((assignment) => [String(assignment.id), assignment]));
      document.querySelectorAll('select[name="attribution_id"] option[data-assignment-id]').forEach((option) => {
        const assignment = assignmentById.get(option.dataset.assignmentId);
        if (!assignment || assignment.remaining <= 0) {
          option.remove();
          return;
        }
        option.dataset.remaining = String(assignment.remaining);
        option.textContent = `${assignment.nom_cours} · I${assignment.indice} · ${assignment.remaining} h restantes`;
      });

      document.querySelectorAll('select[name="attribution_id"]').forEach((select) => {
        if (select.options.length <= 1) {
          select.options[0].textContent = 'Aucune heure restante à planifier';
          const button = select.form?.querySelector('button[type="submit"]');
          if (button) button.disabled = true;
        }
      });
    };

    const showScheduledSlot = (cell, slot, deleteAction, csrfToken) => {
      const course = document.createElement('div');
      course.className = 'fw-semibold';
      course.textContent = slot.nom_cours;

      const index = document.createElement('span');
      index.className = 'badge text-bg-primary';
      index.textContent = `Indice ${slot.indice}`;

      const form = document.createElement('form');
      form.method = 'post';
      form.action = deleteAction;
      form.className = 'mt-1';

      [
        ['csrf_token', csrfToken],
        ['slot_id', slot.id],
        ['classe_id', slot.classe_id],
      ].forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = String(value);
        form.append(input);
      });

      const button = document.createElement('button');
      button.className = 'btn btn-sm btn-outline-danger';
      button.type = 'submit';
      button.setAttribute('aria-label', 'Supprimer le créneau');
      button.textContent = 'Retirer';
      form.append(button);
      cell.replaceChildren(course, index, form);
    };

    document.addEventListener('submit', async (event) => {
      const form = event.target.closest('form[action$="/horaires/save-slot"]');
      if (!form) return;
      event.preventDefault();

      const cell = form.closest('.timetable-cell');
      const button = form.querySelector('button[type="submit"]');
      const originalButtonText = button.textContent;
      button.disabled = true;
      button.textContent = 'Enregistrement…';
      form.querySelector('.js-schedule-error')?.remove();

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
        });
        const result = await response.json();
        updateCsrfTokens(result.csrfToken);
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Impossible d’enregistrer ce créneau.');
        }

        const deleteUrl = new URL(form.action, window.location.href);
        deleteUrl.pathname = deleteUrl.pathname.replace(/\/save-slot$/, '/delete-slot');
        updateAssignmentOptions(result.assignments);
        showScheduledSlot(cell, result.slot, deleteUrl.toString(), result.csrfToken);
      } catch (error) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger py-1 px-2 mt-2 mb-0 js-schedule-error';
        alert.setAttribute('role', 'alert');
        alert.textContent = error instanceof Error ? error.message : 'Une erreur est survenue lors de la planification.';
        form.append(alert);
        button.disabled = false;
        button.textContent = originalButtonText;
      }
    });
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
