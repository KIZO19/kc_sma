<?php
require __DIR__ . '/../partials/app_header.php';
$e = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$recordedCount = count(array_filter($students, static fn (array $student): bool => !empty($student['statut_presence'])));
?>
<style>
  .attendance-page .attendance-summary,
  .attendance-page .attendance-panel { border: 1px solid var(--border); border-radius: .75rem; background: var(--card); }
  .attendance-page .attendance-count { color: var(--text); font-size: 1.5rem; font-weight: 700; }
  .attendance-page .form-label,
  .attendance-page .attendance-panel h2 { color: var(--text); }
  .attendance-page .form-select,
  .attendance-page .form-control { color: var(--text); background-color: var(--surface); border-color: var(--border); }
  .attendance-page .attendance-table thead th { position: sticky; top: 0; z-index: 1; background: var(--surface-muted); color: var(--text-muted); font-size: .75rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
  .attendance-page .attendance-table tbody td { vertical-align: middle; color: var(--text); background-color: var(--card); }
  .attendance-page .attendance-table tbody tr:hover > * { --bs-table-bg: var(--surface-muted); }
  @media (max-width: 575.98px) {
    .attendance-page .attendance-table { min-width: 620px; }
  }
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h1>Présences des élèves</h1>
        <p class="text-muted mb-0"><?= $isTeacher ? 'Saisissez les présences des classes qui vous sont attribuées.' : 'Consultez et enregistrez les présences par classe.' ?></p>
      </div>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Présences</li>
      </ol>
    </div>
  </div>
</section>
<section class="content attendance-page">
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

    <div class="card attendance-panel mb-3">
      <div class="card-body">
        <form method="get" action="<?= BASE_URL ?>/presences" class="row g-3 align-items-end">
          <div class="col-12 col-lg-6">
            <label for="classId" class="form-label">Classe</label>
            <select class="form-select" id="classId" name="class_id" required <?= $classes === [] ? 'disabled' : '' ?>>
              <?php foreach ($classes as $class): ?>
                <?php $classLabel = $class['nom_classe'] . (!empty($class['nom_etablissement']) && !$isTeacher && count($classes) > 1 ? ' · ' . $class['nom_etablissement'] : ''); ?>
                <option value="<?= (int) $class['id'] ?>" <?= (int) $class['id'] === $selectedClassId ? 'selected' : '' ?>><?= $e($classLabel) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <label for="attendanceDate" class="form-label">Date</label>
            <input class="form-control" type="date" id="attendanceDate" name="date" value="<?= $e($date) ?>" max="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-12 col-sm-6 col-lg-3">
            <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Afficher</button>
          </div>
        </form>
      </div>
    </div>

    <?php if ($selectedClass === null): ?>
      <div class="attendance-panel p-4 text-center">
        <i class="bi bi-easel fs-2 text-muted d-block mb-2" aria-hidden="true"></i>
        <h2 class="h5">Aucune classe disponible</h2>
        <p class="text-muted mb-0"><?= $isTeacher ? 'Aucune classe active ne vous est attribuée. Contactez l’administration de votre école.' : 'Aucune classe active n’est disponible dans votre école.' ?></p>
      </div>
    <?php else: ?>
      <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
          <div class="attendance-summary p-3 h-100">
            <div class="small text-muted">Classe</div>
            <div class="fw-semibold text-truncate" title="<?= $e($selectedClass['nom_classe']) ?>"><?= $e($selectedClass['nom_classe']) ?></div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="attendance-summary p-3 h-100">
            <div class="small text-muted">Élèves inscrits</div>
            <div class="attendance-count"><?= count($students) ?></div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="attendance-summary p-3 h-100">
            <div class="small text-muted">Présences déjà saisies</div>
            <div class="attendance-count"><?= $recordedCount ?></div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="attendance-summary p-3 h-100">
            <div class="small text-muted">Date sélectionnée</div>
            <div class="fw-semibold"><?= $e(date('d/m/Y', strtotime($date))) ?></div>
          </div>
        </div>
      </div>

      <form method="post" action="<?= BASE_URL ?>/presences">
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <input type="hidden" name="class_id" value="<?= $selectedClassId ?>">
        <input type="hidden" name="date_jour" value="<?= $e($date) ?>">
        <div class="card attendance-panel">
          <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h2 class="h5 mb-1">Registre de présence</h2>
              <span class="small text-muted"><?= $e($selectedClass['nom_classe']) ?> · <?= $e(date('d/m/Y', strtotime($date))) ?></span>
            </div>
            <button class="btn btn-primary" type="submit" <?= $students === [] ? 'disabled' : '' ?>>
              <i class="bi bi-check2-square me-2" aria-hidden="true"></i>Enregistrer les présences
            </button>
          </div>
          <div class="card-body p-0">
            <?php if ($students === []): ?>
              <div class="text-center text-muted py-5">Aucun élève inscrit dans cette classe pour l’année scolaire active.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover attendance-table mb-0">
                  <thead>
                    <tr>
                      <th class="ps-3">N°</th>
                      <th>Matricule</th>
                      <th>Élève</th>
                      <th class="pe-3">Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($students as $index => $student): ?>
                      <?php $fullName = \App\Core\PersonName::format($student); ?>
                      <tr>
                        <td class="ps-3 text-muted"><?= $index + 1 ?></td>
                        <td class="font-monospace"><?= $e($student['matricule'] ?: '—') ?></td>
                        <td class="fw-semibold"><?= $e($fullName ?: '—') ?></td>
                        <td class="pe-3">
                          <select class="form-select form-select-sm" name="attendance[<?= (int) $student['id'] ?>]" aria-label="Présence de <?= $e($fullName) ?>">
                            <option value="" <?= empty($student['statut_presence']) ? 'selected' : '' ?>>À marquer</option>
                            <?php foreach (['Présent', 'Absent Justifié', 'Absent Non Justifié', 'Retard'] as $status): ?>
                              <option value="<?= $e($status) ?>" <?= ($student['statut_presence'] ?? '') === $status ? 'selected' : '' ?>><?= $e($status) ?></option>
                            <?php endforeach; ?>
                          </select>
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
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
