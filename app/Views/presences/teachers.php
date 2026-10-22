<?php
require __DIR__ . '/../partials/app_header.php';
$e = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$presentCount = count(array_filter($teachers, static fn (array $teacher): bool => ($teacher['statut_presence'] ?? '') === 'Présent'));
$absentCount = count(array_filter($teachers, static fn (array $teacher): bool => ($teacher['statut_presence'] ?? '') === 'Absent'));
$weeklyDayOffCount = count(array_filter($teachers, static fn (array $teacher): bool => !empty($teacher['est_jour_hebdomadaire'])));
$pendingCount = count(array_filter($teachers, static fn (array $teacher): bool => empty($teacher['statut_presence']) && empty($teacher['est_jour_hebdomadaire'])));
?>
<style>
  .staff-attendance .attendance-summary,
  .staff-attendance .attendance-panel { border: 1px solid var(--border); border-radius: .75rem; background: var(--card); }
  .staff-attendance .attendance-count { color: var(--text); font-size: 1.5rem; font-weight: 700; }
  .staff-attendance .attendance-panel h2,
  .staff-attendance .form-label { color: var(--text); }
  .staff-attendance .form-control,
  .staff-attendance .form-select { color: var(--text); background-color: var(--surface); border-color: var(--border); }
  .staff-attendance .attendance-table thead th { background: var(--surface-muted); color: var(--text-muted); font-size: .75rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
  .staff-attendance .attendance-table tbody td { vertical-align: middle; color: var(--text); background-color: var(--card); }
  @media (max-width: 575.98px) { .staff-attendance .attendance-table { min-width: 760px; } }
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h1>Présences des enseignants</h1>
        <p class="text-muted mb-0">Suivez les présences de l’équipe enseignante de votre périmètre.</p>
      </div>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Présences des enseignants</li>
      </ol>
    </div>
  </div>
</section>
<section class="content staff-attendance">
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
    <?php if ($weeklyDayOffCount > 0): ?>
      <div class="alert alert-info" role="status">
        <i class="bi bi-calendar-check me-2" aria-hidden="true"></i>
        <?= $weeklyDayOffCount ?> enseignant(s) ont leur journée hebdomadaire le <?= $e(date('d/m/Y', strtotime($date))) ?>. Ils sont signalés dans le registre et exclus des pointages en attente.
      </div>
    <?php endif; ?>

    <div class="card attendance-panel mb-3">
      <div class="card-body">
        <form method="get" action="<?= BASE_URL ?>/presences" class="row g-3 align-items-end">
          <input type="hidden" name="mode" value="enseignants">
          <div class="col-12 col-sm-8 col-lg-4">
            <label for="attendanceDate" class="form-label">Date</label>
            <input class="form-control" type="date" id="attendanceDate" name="date" value="<?= $e($date) ?>" max="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-12 col-sm-4 col-lg-3">
            <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Afficher</button>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <?php foreach ([['Enseignants concernés', count($teachers), 'primary'], ['Présents', $presentCount, 'success'], ['Absents', $absentCount, 'danger'], ['À pointer', $pendingCount, 'warning']] as [$label, $value, $tone]): ?>
        <div class="col-6 col-xl-3">
          <div class="attendance-summary p-3 h-100">
            <div class="small text-muted"><?= $e($label) ?></div>
            <div class="attendance-count text-<?= $e($tone) ?>"><?= (int) $value ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <form method="post" action="<?= BASE_URL ?>/presences?mode=enseignants">
      <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
      <input type="hidden" name="mode" value="enseignants">
      <input type="hidden" name="date_jour" value="<?= $e($date) ?>">
      <div class="card attendance-panel">
        <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h2 class="h5 mb-1">Registre journalier</h2>
            <span class="small text-muted"><?= $e(date('d/m/Y', strtotime($date))) ?></span>
          </div>
          <button class="btn btn-primary" type="submit" <?= $teachers === [] ? 'disabled' : '' ?>>
            <i class="bi bi-check2-square me-2" aria-hidden="true"></i>Enregistrer les présences
          </button>
        </div>
        <div class="card-body p-0">
          <?php if ($teachers === []): ?>
            <div class="text-center text-muted py-5">Aucun enseignant avec un cours attribué dans ce périmètre pour l’année scolaire active.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover attendance-table mb-0">
                <thead>
                  <tr><th class="ps-3">N°</th><th>Enseignant</th><th>Statut</th><th class="pe-3">Justification</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($teachers as $index => $teacher): ?>
                    <?php $fullName = \App\Core\PersonName::format($teacher); ?>
                    <tr>
                      <td class="ps-3 text-muted"><?= $index + 1 ?></td>
                      <td class="fw-semibold">
                        <?= $e($fullName ?: '—') ?>
                        <?php if (!empty($teacher['est_jour_hebdomadaire'])): ?>
                          <span class="badge text-bg-info ms-2">Journée hebdomadaire · <?= $e($teacher['jour_hebdomadaire_nom'] ?? '') ?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <select class="form-select form-select-sm" name="attendance[<?= (int) $teacher['id'] ?>]" aria-label="Présence de <?= $e($fullName) ?>">
                          <option value="" <?= empty($teacher['statut_presence']) ? 'selected' : '' ?>>À pointer</option>
                          <?php foreach (['Présent', 'Absent', 'Journée Pédagogique', 'Congé'] as $status): ?>
                            <option value="<?= $e($status) ?>" <?= ($teacher['statut_presence'] ?? '') === $status ? 'selected' : '' ?>><?= $e($status) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </td>
                      <td class="pe-3"><input class="form-control form-control-sm" name="justification[<?= (int) $teacher['id'] ?>]" value="<?= $e($teacher['justification'] ?? '') ?>" maxlength="2000" aria-label="Justification de <?= $e($fullName) ?>"></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>