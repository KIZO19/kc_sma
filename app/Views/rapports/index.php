<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Rapports</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
          <li class="breadcrumb-item active">Rapports</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (($role ?? '') !== 'super_admin' && (empty($schoolCalendar['annee_scolaire_debut']) || empty($schoolCalendar['annee_scolaire_fin']))): ?>
      <div class="alert alert-warning" role="status">
        Les dates officielles de l’année scolaire ne sont pas encore configurées.
        <a href="<?= BASE_URL ?>/ecoles/settings">Définir le calendrier dans les paramètres de l’école</a>.
      </div>
    <?php endif; ?>
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h2 class="card-title fw-semibold">Période du rapport</h2>
      </div>
      <form method="get" action="<?= BASE_URL ?>/rapports">
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
              <label class="form-label" for="report-period">Période</label>
              <select class="form-select" id="report-period" name="period">
                <option value="month" <?= ($period['type'] ?? '') === 'month' ? 'selected' : '' ?>>Mois</option>
                <option value="quarter" <?= ($period['type'] ?? '') === 'quarter' ? 'selected' : '' ?>>Trimestre</option>
                <option value="year" <?= ($period['type'] ?? '') === 'year' ? 'selected' : '' ?>>Année civile</option>
                <?php if (!empty($schoolCalendar['annee_scolaire_debut']) && !empty($schoolCalendar['annee_scolaire_fin'])): ?>
                  <option value="school_year" <?= ($period['type'] ?? '') === 'school_year' ? 'selected' : '' ?>>Année scolaire officielle</option>
                <?php endif; ?>
                <option value="custom" <?= ($period['type'] ?? '') === 'custom' ? 'selected' : '' ?>>Période personnalisée</option>
              </select>
            </div>
            <div class="col-6 col-md-2 period-year-field">
              <label class="form-label" for="report-year">Année</label>
              <input class="form-control" id="report-year" name="year" type="number" min="2000" max="<?= (int) date('Y') ?>" value="<?= htmlspecialchars(substr((string) ($period['start'] ?? date('Y-m-d')), 0, 4), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-6 col-md-2 period-month-field">
              <label class="form-label" for="report-month">Mois</label>
              <select class="form-select" id="report-month" name="month">
                <?php foreach (['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'] as $monthNumber => $monthName): ?>
                  <option value="<?= $monthNumber + 1 ?>" <?= (int) substr((string) ($period['start'] ?? date('Y-m-d')), 5, 2) === $monthNumber + 1 ? 'selected' : '' ?>><?= htmlspecialchars($monthName, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-2 period-quarter-field">
              <label class="form-label" for="report-quarter">Trimestre</label>
              <select class="form-select" id="report-quarter" name="quarter">
                <?php for ($quarter = 1; $quarter <= 4; $quarter++): ?>
                  <option value="<?= $quarter ?>" <?= (int) ceil((int) substr((string) ($period['start'] ?? date('Y-m-d')), 5, 2) / 3) === $quarter ? 'selected' : '' ?>>Trimestre <?= $quarter ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-6 col-md-2 d-grid">
              <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Générer</button>
            </div>
            <div class="col-12 col-md-6 period-custom-field">
              <label class="form-label" for="report-start">Du</label>
              <input class="form-control" id="report-start" name="start_date" type="date" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars((string) ($period['start'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-md-6 period-custom-field">
              <label class="form-label" for="report-end">Au</label>
              <input class="form-control" id="report-end" name="end_date" type="date" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars((string) ($period['end'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
          <?php if (($period['type'] ?? '') === 'school_year' && !empty($schoolCalendar['annee_scolaire_debut'])): ?>
            <p class="form-text mb-0 mt-2">Calendrier officiel configuré : <?= htmlspecialchars(formatDate((string) $schoolCalendar['annee_scolaire_debut']), ENT_QUOTES, 'UTF-8') ?> – <?= htmlspecialchars(formatDate((string) $schoolCalendar['annee_scolaire_fin']), ENT_QUOTES, 'UTF-8') ?>.</p>
          <?php endif; ?>
        </div>
      </form>
    </div>
    <?php if (!empty($periodError)): ?>
      <div class="alert alert-warning" role="alert"><?= htmlspecialchars($periodError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <div class="alert alert-info py-2" role="status">
      <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>Rapport généré pour : <strong><?= htmlspecialchars((string) ($period['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>

    <div class="row">
      <?php foreach ($reportCategories as $category): ?>
        <div class="col-12 col-xl-6">
          <div class="card card-outline card-primary h-100">
            <div class="card-header">
              <h2 class="card-title fw-semibold">
                <i class="bi <?= htmlspecialchars($category['icon'], ENT_QUOTES, 'UTF-8') ?> me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') ?>
              </h2>
            </div>
            <div class="card-body">
              <p class="text-muted"><?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?></p>
              <?php if ($category['title'] === 'Finances'): ?>
                <?php if (!empty($reportData['finance']['payments'])): ?>
                  <h3 class="h6 mt-3">Paiements encaissés</h3>
                  <div class="table-responsive">
                    <table class="table table-sm">
                      <thead><tr><th>Devise</th><th>Nombre</th><th>Total</th></tr></thead>
                      <tbody>
                        <?php foreach ($reportData['finance']['payments'] as $row): ?>
                          <tr>
                            <td><?= htmlspecialchars((string) $row['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format((int) $row['nombre'], 0, ',', ' ') ?></td>
                            <td><?= number_format((float) $row['total'], 2, ',', ' ') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <p class="small text-muted">Aucun paiement encaissé pendant cette période.</p>
                <?php endif; ?>
                <?php if (in_array('/paies-agents', array_column($category['reports'], 'path'), true)): ?>
                  <h3 class="h6 mt-3">Salaires payés</h3>
                  <?php if (!empty($reportData['finance']['payroll'])): ?>
                    <div class="table-responsive">
                      <table class="table table-sm">
                        <thead><tr><th>Devise</th><th>Nombre</th><th>Total net</th></tr></thead>
                        <tbody>
                          <?php foreach ($reportData['finance']['payroll'] as $row): ?>
                            <tr>
                              <td><?= htmlspecialchars((string) $row['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                              <td><?= number_format((int) $row['nombre'], 0, ',', ' ') ?></td>
                              <td><?= number_format((float) $row['total'], 2, ',', ' ') ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <p class="small text-muted">Aucun salaire payé pendant cette période.</p>
                  <?php endif; ?>
                <?php endif; ?>
              <?php elseif ($category['title'] === 'Pédagogie'): ?>
                <?php if (in_array('/notes', array_column($category['reports'], 'path'), true)): ?>
                  <div class="row g-2 mt-2">
                    <div class="col-4"><div class="border rounded p-2 h-100"><span class="d-block small text-muted">Évaluations</span><strong><?= number_format($reportData['pedagogy']['evaluations'], 0, ',', ' ') ?></strong></div></div>
                    <div class="col-4"><div class="border rounded p-2 h-100"><span class="d-block small text-muted">Notes saisies</span><strong><?= number_format($reportData['pedagogy']['scores'], 0, ',', ' ') ?></strong></div></div>
                    <div class="col-4"><div class="border rounded p-2 h-100"><span class="d-block small text-muted">Moyenne</span><strong><?= $reportData['pedagogy']['average'] === null ? '—' : number_format((float) $reportData['pedagogy']['average'], 2, ',', ' ') ?></strong></div></div>
                  </div>
                <?php endif; ?>
                <?php if (in_array('/presences', array_column($category['reports'], 'path'), true)): ?>
                  <h3 class="h6 mt-3">Présences par statut</h3>
                  <?php if (!empty($reportData['pedagogy']['attendance'])): ?>
                    <ul class="list-group list-group-flush">
                      <?php foreach ($reportData['pedagogy']['attendance'] as $row): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between"><span><?= htmlspecialchars((string) $row['statut'], ENT_QUOTES, 'UTF-8') ?></span><strong><?= number_format((int) $row['nombre'], 0, ',', ' ') ?></strong></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <p class="small text-muted">Aucune présence enregistrée pendant cette période.</p>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if (in_array('/bulletins', array_column($category['reports'], 'path'), true)): ?>
                  <p class="small mt-3 mb-0">Bulletins produits : <strong><?= number_format($reportData['pedagogy']['bulletins'], 0, ',', ' ') ?></strong></p>
                <?php endif; ?>
              <?php elseif ($category['title'] === 'Administration'): ?>
                <div class="row g-2 mt-2">
                  <?php if (in_array('/inscriptions', array_column($category['reports'], 'path'), true)): ?>
                    <div class="col-6"><div class="border rounded p-2 h-100"><span class="d-block small text-muted">Inscriptions</span><strong><?= number_format($reportData['administration']['registrations'], 0, ',', ' ') ?></strong></div></div>
                  <?php endif; ?>
                  <?php if (in_array('/utilisateurs', array_column($category['reports'], 'path'), true)): ?>
                    <div class="col-6"><div class="border rounded p-2 h-100"><span class="d-block small text-muted">Comptes créés</span><strong><?= number_format($reportData['administration']['users'], 0, ',', ' ') ?></strong></div></div>
                  <?php endif; ?>
                  <?php if (in_array('/evenements', array_column($category['reports'], 'path'), true)): ?>
                    <div class="col-6"><div class="border rounded p-2 h-100"><span class="d-block small text-muted">Événements</span><strong><?= number_format($reportData['administration']['events'], 0, ',', ' ') ?></strong></div></div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <div class="list-group list-group-flush">
                <?php foreach ($category['reports'] as $report): ?>
                  <a class="list-group-item list-group-item-action px-0" href="<?= BASE_URL . htmlspecialchars($report['path'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="d-flex justify-content-between align-items-center gap-3">
                      <span>
                        <span class="d-block fw-semibold"><?= htmlspecialchars($report['title'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="small text-muted"><?= htmlspecialchars($report['description'], ENT_QUOTES, 'UTF-8') ?></span>
                      </span>
                      <i class="bi bi-arrow-right text-primary" aria-hidden="true"></i>
                    </span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script>
  (() => {
    const periodSelect = document.getElementById('report-period');
    const updatePeriodFields = () => {
      const period = periodSelect.value;
      document.querySelectorAll('.period-year-field').forEach((field) => {
        field.hidden = !['month', 'quarter', 'year'].includes(period);
      });
      document.querySelectorAll('.period-month-field').forEach((field) => {
        field.hidden = period !== 'month';
      });
      document.querySelectorAll('.period-quarter-field').forEach((field) => {
        field.hidden = period !== 'quarter';
      });
      document.querySelectorAll('.period-custom-field').forEach((field) => {
        field.hidden = period !== 'custom';
      });
    };
    periodSelect.addEventListener('change', updatePeriodFields);
    updatePeriodFields();
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
