<?php
require __DIR__ . '/../partials/app_header.php';
$e = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$selectedCourse = null;
foreach ($courses as $course) {
    if ((int) $course['attribution_id'] === $assignmentId) {
        $selectedCourse = $course;
        break;
    }
}
?>
<style>
  .teacher-notes .notes-panel { border: 1px solid var(--border); border-radius: .75rem; background: var(--card); }
  .teacher-notes .form-label,
  .teacher-notes .notes-panel h2 { color: var(--text); }
  .teacher-notes .form-select,
  .teacher-notes .form-control { color: var(--text); background-color: var(--surface); border-color: var(--border); }
  .teacher-notes .notes-table thead th { background: var(--surface-muted); color: var(--text-muted); font-size: .75rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
  .teacher-notes .notes-table td { color: var(--text); vertical-align: middle; }
  .teacher-notes .student-score { max-width: 8rem; }
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h1>Notes de mes cours</h1>
        <p class="text-muted mb-0">Saisissez et consultez les notes des élèves de vos cours attribués.</p>
      </div>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Notes</li>
      </ol>
    </div>
  </div>
</section>
<section class="content teacher-notes">
  <div class="container-fluid">
    <?php if ($success !== ''): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2" aria-hidden="true"></i><?= $e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i><?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>

    <?php if ($courses === []): ?>
      <div class="notes-panel p-4 text-center">
        <i class="bi bi-journal-x fs-2 text-muted d-block mb-2" aria-hidden="true"></i>
        <h2 class="h5">Aucun cours attribué</h2>
        <p class="text-muted mb-0">Aucun cours actif ne vous est attribué pour cette année scolaire. Contactez la direction.</p>
      </div>
    <?php else: ?>
      <div class="card notes-panel mb-3">
        <div class="card-body">
          <form method="get" action="<?= BASE_URL ?>/notes" class="row g-3 align-items-end">
            <div class="col-12 col-lg-6">
              <label for="courseAssignment" class="form-label">Cours et classe</label>
              <select class="form-select" id="courseAssignment" name="attribution_id" required>
                <?php foreach ($courses as $course): ?>
                  <option value="<?= (int) $course['attribution_id'] ?>" <?= (int) $course['attribution_id'] === $assignmentId ? 'selected' : '' ?>>
                    <?= $e($course['nom_cours'] . ' · ' . $course['nom_classe']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if ($periods !== []): ?>
              <div class="col-12 col-sm-8 col-lg-4">
                <label for="periodId" class="form-label">Période</label>
                <select class="form-select" id="periodId" name="period_id" required>
                  <?php foreach ($periods as $period): ?>
                    <option value="<?= (int) $period['id'] ?>" <?= (int) $period['id'] === $periodId ? 'selected' : '' ?>>
                      <?= $e($period['nom_periode']) ?><?= (int) $period['cloturee'] === 1 ? ' · Clôturée' : '' ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>
            <div class="col-12 col-sm-4 col-lg-2">
              <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Afficher</button>
            </div>
          </form>
        </div>
      </div>

      <?php if ($selectedCourse && $periods === []): ?>
        <div class="alert alert-info">
          Aucun trimestre ou période n’est configuré pour l’année scolaire <?= $e($selectedCourse['annee']) ?>. La direction doit créer les périodes avant la saisie des notes.
        </div>
      <?php elseif ($periods !== []): ?>
        <div class="row g-3">
          <div class="col-12 col-xl-5">
            <div class="card notes-panel h-100">
              <div class="card-header bg-transparent">
                <h2 class="h5 mb-0">Nouvelle évaluation</h2>
              </div>
              <div class="card-body">
                <?php if (!empty($periods[array_search($periodId, array_column($periods, 'id'))]['cloturee'])): ?>
                  <p class="text-muted mb-0">Cette période est clôturée. La création et la modification de notes sont désactivées.</p>
                <?php else: ?>
                  <form method="post" action="<?= BASE_URL ?>/notes">
                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="action" value="create_evaluation">
                    <input type="hidden" name="attribution_id" value="<?= $assignmentId ?>">
                    <input type="hidden" name="period_id" value="<?= $periodId ?>">
                    <div class="mb-3">
                      <label for="evaluationType" class="form-label">Type</label>
                      <select class="form-select" id="evaluationType" name="type_evaluation" data-evaluation-max="<?= (int) ($selectedCourse['max_evaluation'] ?? 10) ?>" data-exam-max="<?= (int) ($selectedCourse['max_examen'] ?? 40) ?>" required>
                        <?php foreach (['Interrogation', 'Devoir', 'Exposé', 'Examen'] as $type): ?>
                          <option value="<?= $e($type) ?>"><?= $e($type) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <label for="evaluationDate" class="form-label">Date</label>
                        <input class="form-control" id="evaluationDate" type="date" name="date_evaluation" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                      </div>
                      <div class="col-sm-6">
                        <label for="evaluationMaximum" class="form-label">Note maximale</label>
                        <input class="form-control" id="evaluationMaximum" type="number" name="ponderation_max" min="1" max="<?= (int) ($selectedCourse['max_evaluation'] ?? 10) ?>" step="1" value="<?= (int) ($selectedCourse['max_evaluation'] ?? 10) ?>" required>
                      </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Créer l’évaluation</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-12 col-xl-7">
            <div class="card notes-panel h-100">
              <div class="card-header bg-transparent">
                <h2 class="h5 mb-0">Évaluations de la période</h2>
              </div>
              <div class="card-body p-0">
                <?php if ($evaluations === []): ?>
                  <p class="text-muted p-3 mb-0">Aucune évaluation n’a encore été créée pour ce cours.</p>
                <?php else: ?>
                  <div class="list-group list-group-flush">
                    <?php foreach ($evaluations as $evaluation): ?>
                      <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 <?= $selectedEvaluation && (int) $selectedEvaluation['id'] === (int) $evaluation['id'] ? 'active' : '' ?>" href="<?= BASE_URL ?>/notes?attribution_id=<?= $assignmentId ?>&period_id=<?= $periodId ?>&evaluation_id=<?= (int) $evaluation['id'] ?>">
                        <span>
                          <strong class="d-block"><?= $e($evaluation['type_evaluation']) ?></strong>
                          <small><?= $e(date('d/m/Y', strtotime($evaluation['date_evaluation']))) ?> · /<?= $e($evaluation['ponderation_max']) ?></small>
                        </span>
                        <span class="badge text-bg-secondary"><?= (int) $evaluation['notes_saisies'] ?> note(s)</span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <?php if ($selectedEvaluation): ?>
          <form method="post" action="<?= BASE_URL ?>/notes" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <input type="hidden" name="action" value="update_scores">
            <input type="hidden" name="attribution_id" value="<?= $assignmentId ?>">
            <input type="hidden" name="period_id" value="<?= $periodId ?>">
            <input type="hidden" name="evaluation_id" value="<?= (int) $selectedEvaluation['id'] ?>">
            <div class="card notes-panel">
              <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h2 class="h5 mb-1">Saisie des notes</h2>
                  <span class="small text-muted"><?= $e($selectedEvaluation['type_evaluation']) ?> · maximum <?= $e($selectedEvaluation['ponderation_max']) ?></span>
                </div>
                <?php if ((int) $selectedEvaluation['cloturee'] === 0): ?>
                  <button class="btn btn-primary" type="submit"><i class="bi bi-save me-2" aria-hidden="true"></i>Enregistrer les notes</button>
                <?php endif; ?>
              </div>
              <div class="card-body p-0">
                <?php if ($students === []): ?>
                  <p class="text-muted p-3 mb-0">Aucun élève inscrit dans cette classe pour l’année scolaire active.</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-hover notes-table align-middle mb-0">
                      <thead><tr><th class="ps-3">Matricule</th><th>Élève</th><th class="pe-3">Note / <?= $e($selectedEvaluation['ponderation_max']) ?></th></tr></thead>
                      <tbody>
                        <?php foreach ($students as $student): ?>
                          <?php $studentName = trim(implode(' ', array_filter([$student['nom'] ?? '', $student['postnom'] ?? '', $student['prenom'] ?? '']))); ?>
                          <tr>
                            <td class="ps-3 font-monospace"><?= $e($student['matricule'] ?: '—') ?></td>
                            <td class="fw-semibold"><?= $e($studentName) ?></td>
                            <td class="pe-3">
                              <?php if ((int) $selectedEvaluation['cloturee'] === 0): ?>
                                <input class="form-control form-control-sm student-score" type="number" name="scores[<?= (int) $student['id'] ?>]" min="0" max="<?= $e($selectedEvaluation['ponderation_max']) ?>" step="0.1" value="<?= $e($student['note_obtenue']) ?>" aria-label="Note de <?= $e($studentName) ?>">
                              <?php else: ?>
                                <?= $student['note_obtenue'] !== null ? $e($student['note_obtenue']) : '—' ?>
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
          </form>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const evaluationType = document.getElementById('evaluationType');
        const maximumInput = document.getElementById('evaluationMaximum');
        if (!evaluationType || !maximumInput) return;

        function updateMaximum() {
          const maximum = evaluationType.value === 'Examen'
            ? Number(evaluationType.dataset.examMax)
            : Number(evaluationType.dataset.evaluationMax);
          maximumInput.max = String(maximum);
          if (Number(maximumInput.value) > maximum) maximumInput.value = String(maximum);
        }

        evaluationType.addEventListener('change', updateMaximum);
      });
    </script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
