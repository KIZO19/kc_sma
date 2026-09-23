<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$filters = $filters ?? [];
$classes = $classes ?? [];
$debts = $debts ?? [];
$summary = $summary ?? [];
$feeColumns = $feeColumns ?? [];
$formatAmount = static fn ($amount) => number_format((float) $amount, 2, ',', ' ');
$currencies = array_unique(array_merge(array_keys($summary['initial'] ?? []), array_keys($summary['remaining'] ?? [])));
$exportColumns = [
  'classe' => 'Classe', 'eleve' => 'Élève', 'matricule' => 'Matricule',
  'frais' => 'Frais', 'initial' => 'Montant initial',
  'paye' => 'Montant déjà payé', 'restant' => 'Dette restante',
];
$exportQuery = static function (string $format, array $columns) use ($filters): string {
   $query = array_filter([
     'format' => $format,
     'q' => $filters['q'] ?? '',
     'classe' => $filters['classe'] ?? '',
     'devise' => $filters['devise'] ?? '',
     'montant_min' => $filters['montant_min'] ?? '',
     'montant_max' => $filters['montant_max'] ?? '',
     'colonnes' => implode(',', $columns),
   ], static fn ($value) => $value !== '' && $value !== null);
   return BASE_URL . '/recouvrements/export?' . http_build_query($query);
};
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1 class="mb-1">Recouvrements</h1>
        <p class="text-muted mb-0">Suivi des élèves ayant encore une dette à régler.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary" href="<?= BASE_URL ?>/paiements"><i class="bi bi-wallet2 me-1"></i>Enregistrer un paiement</a>
        <a id="exportPdf" class="btn btn-outline-danger" href="<?= $exportQuery('pdf', array_keys($exportColumns)) ?>"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <a id="exportExcel" class="btn btn-outline-success" href="<?= $exportQuery('excel', array_keys($exportColumns)) ?>"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel</a>
        <a id="exportCsv" class="btn btn-outline-secondary" href="<?= $exportQuery('csv', array_keys($exportColumns)) ?>"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-12 col-md-4">
        <div class="small-box bg-primary mb-0 h-100">
          <div class="inner"><h3><?= (int) ($summary['students'] ?? 0) ?></h3><p>Élèves à relancer</p></div>
          <div class="icon"><i class="bi bi-people"></i></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="small-box bg-warning mb-0 h-100">
          <div class="inner"><h3><?= (int) ($summary['records'] ?? 0) ?></h3><p>Dettes ouvertes</p></div>
          <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="small-box bg-danger mb-0 h-100">
          <div class="inner">
            <?php foreach ($currencies as $currency): ?>
              <h3 class="mb-0"><?= $formatAmount($summary['remaining'][$currency] ?? 0) ?> <small><?= htmlspecialchars($currency) ?></small></h3>
            <?php endforeach; ?>
            <?php if (!$currencies): ?><h3>0,00</h3><?php endif; ?>
            <p>Dette restante</p>
          </div>
          <div class="icon"><i class="bi bi-cash-stack"></i></div>
        </div>
      </div>
    </div>

    <div class="card card-outline card-primary">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0">Dossiers à recouvrer</h3>
        <form method="get" action="<?= BASE_URL ?>/recouvrements" class="row g-2 align-items-end w-100">
          <div class="col-12 col-md-3"><label class="form-label small mb-1">Recherche</label><input name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" type="search" class="form-control" placeholder="Élève, matricule, frais"></div>
          <div class="col-12 col-md-2"><label class="form-label small mb-1">Classe</label><select name="classe" class="form-select"><option value="">Toutes</option><?php foreach (($classes ?? []) as $class): ?><option value="<?= htmlspecialchars($class['nom_classe']) ?>" <?= ($filters['classe'] ?? '') === $class['nom_classe'] ? 'selected' : '' ?>><?= htmlspecialchars($class['nom_classe']) ?></option><?php endforeach; ?></select></div>
          <div class="col-6 col-md-2"><label class="form-label small mb-1">Devise</label><input name="devise" value="<?= htmlspecialchars($filters['devise'] ?? '') ?>" class="form-control" placeholder="USD"></div>
          <div class="col-6 col-md-2"><label class="form-label small mb-1">Dette min.</label><input name="montant_min" value="<?= htmlspecialchars((string) ($filters['montant_min'] ?? '')) ?>" type="number" min="0" step="0.01" class="form-control"></div>
          <div class="col-6 col-md-2"><label class="form-label small mb-1">Dette max.</label><input name="montant_max" value="<?= htmlspecialchars((string) ($filters['montant_max'] ?? '')) ?>" type="number" min="0" step="0.01" class="form-control"></div>
          <div class="col-6 col-md-1"><button class="btn btn-primary w-100" type="submit" title="Filtrer"><i class="bi bi-funnel"></i></button></div>
        </form>
        <details class="w-100"><summary class="btn btn-sm btn-outline-secondary">Personnaliser la mise en page</summary>
          <div class="border rounded p-2 mt-2"><div class="row g-2">
            <?php foreach ($exportColumns as $key => $label): ?><div class="col-6 col-md-3"><label class="form-check"><input class="form-check-input layout-column" type="checkbox" value="<?= $key ?>" checked><span class="form-check-label"><?= htmlspecialchars($label) ?></span></label></div><?php endforeach; ?>
          </div><small class="text-muted">Les colonnes sélectionnées seront utilisées pour les exports.</small></div>
        </details>
      </div>
      <div class="card-body p-0">
        <?php if (empty($debts)): ?>
          <div class="p-4 text-center text-muted">
            <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
            Aucun recouvrement ouvert pour le moment.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="recouvrementsTable">
              <thead class="table-light">
                <tr>
                  <th>Élève</th>
                  <?php foreach ($feeColumns as $feeColumn): ?>
                    <th class="text-end">Payé - <?= htmlspecialchars($feeColumn) ?></th>
                  <?php endforeach; ?>
                  <th class="text-end">Montant initial</th>
                  <th class="text-end">Déjà payé</th>
                  <th class="text-end">Dette restante</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php $currentClass = null; ?>
                <?php foreach ($debts as $debt): ?>
                  <?php
                    $initialTotal = array_sum($debt['initial_by_currency'] ?? []);
                    $remainingTotal = array_sum($debt['remaining_by_currency'] ?? []);
                    $paidTotal = array_sum($debt['paid_by_currency'] ?? []);
                    $progress = $initialTotal > 0 ? min(100, max(0, ($paidTotal / $initialTotal) * 100)) : 0;
                    $name = trim(($debt['nom'] ?? '') . ' ' . ($debt['postnom'] ?? '') . ' ' . ($debt['prenom'] ?? ''));
                    $studentClass = trim((string) ($debt['nom_classe'] ?? 'Classe non définie')) ?: 'Classe non définie';
                    $formatAmounts = static function (array $amounts) use ($formatAmount): string {
                      $values = [];
                      foreach ($amounts as $currency => $amount) $values[] = $formatAmount($amount) . ' ' . $currency;
                      return implode('<br>', $values);
                    };
                  ?>
                  <?php if ($currentClass !== $studentClass): ?>
                    <?php $currentClass = $studentClass; ?>
                    <tr class="table-primary">
                      <td colspan="<?= 5 + count($feeColumns) ?>" class="fw-semibold">
                        <i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($studentClass) ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                  <tr data-search="<?= htmlspecialchars(strtolower($studentClass . ' ' . $name . ' ' . ($debt['matricule'] ?? '') . ' ' . ($debt['frais_liste'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
                      <small class="text-muted"><?= htmlspecialchars($debt['matricule'] ?? 'Sans matricule') ?></small>
                    </td>
                    <?php foreach ($feeColumns as $feeColumn): ?>
                      <?php $feePaid = $debt['frais_details'][$feeColumn]['paid_by_currency'] ?? []; ?>
                      <td class="text-end">
                        <?= $feePaid ? $formatAmounts($feePaid) : '<span class="text-muted">-</span>' ?>
                      </td>
                    <?php endforeach; ?>
                    <td class="text-end"><?= $formatAmounts($debt['initial_by_currency'] ?? []) ?></td>
                    <td class="text-end">
                      <?= $formatAmounts($debt['paid_by_currency'] ?? []) ?>
                      <div class="progress mt-1" style="height: 5px;" title="<?= round($progress) ?> % payé">
                        <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                      </div>
                    </td>
                    <td class="text-end fw-semibold text-danger"><?= $formatAmounts($debt['remaining_by_currency'] ?? []) ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/paiements?eleve_id=<?= (int) $debt['eleve_id'] ?>" title="Voir les paiements">
                        <i class="bi bi-eye"></i><span class="visually-hidden">Voir les paiements</span>
                      </a>
                      <details class="d-inline-block text-start mt-1">
                        <summary class="btn btn-sm btn-outline-warning" title="Demander une dérogation">
                          <i class="bi bi-file-earmark-check"></i><span class="visually-hidden">Demander une dérogation</span>
                        </summary>
                        <form method="post" action="<?= BASE_URL ?>/derogations/request" class="border rounded bg-white shadow-sm p-3 mt-2" style="min-width: 280px; position: absolute; right: 1rem; z-index: 10;">
                          <input type="hidden" name="eleve_id" value="<?= (int) $debt['eleve_id'] ?>">
                          <label class="form-label small mb-1">Protéger l’élève jusqu’au</label>
                          <input class="form-control form-control-sm mb-2" name="date_fin" type="date" min="<?= date('Y-m-d') ?>" required>
                          <label class="form-label small mb-1">Motif</label>
                          <textarea class="form-control form-control-sm mb-2" name="motif" rows="2" maxlength="500" required></textarea>
                          <button class="btn btn-sm btn-warning w-100" type="submit">Demander au promoteur</button>
                        </form>
                      </details>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div id="recouvrementEmptySearch" class="p-4 text-center text-muted d-none">Aucun dossier ne correspond à votre recherche.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<div class="container-fluid text-end text-muted small mt-3 mb-2">
  Liste générée le <?= date('d/m/Y à H:i') ?>
</div>

<script>
  (() => {
    const columnChecks = [...document.querySelectorAll('.layout-column')];
    const exportLinks = {
      pdf: document.getElementById('exportPdf'),
      excel: document.getElementById('exportExcel'),
      csv: document.getElementById('exportCsv')
    };
    const baseUrls = Object.fromEntries(Object.entries(exportLinks).map(([format, link]) => [format, link ? link.href : '']));
    const updateExports = () => {
      const selected = columnChecks.filter((check) => check.checked).map((check) => check.value);
      const columns = selected.length ? selected : columnChecks.map((check) => check.value);
      Object.entries(exportLinks).forEach(([format, link]) => {
        if (!link) return;
        const url = new URL(baseUrls[format], window.location.origin);
        url.searchParams.set('colonnes', columns.join(','));
        link.href = url.toString();
      });
    };
    columnChecks.forEach((check) => check.addEventListener('change', updateExports));
    updateExports();
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>