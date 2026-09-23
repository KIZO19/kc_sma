<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$filters = $filters ?? [];
$classes = $classes ?? [];
$debts = $debts ?? [];
$summary = $summary ?? [];
$feeColumns = $feeColumns ?? [];
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
        <div class="d-flex flex-wrap align-items-center gap-2 w-100 pt-2 border-top">
          <div class="input-group" style="max-width: 360px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="recouvrementsGridSearch" type="search" class="form-control" placeholder="Rechercher dans la liste..." aria-label="Rechercher dans la liste">
          </div>
          <label class="small text-muted ms-md-auto" for="recouvrementsPageSize">Lignes</label>
          <select id="recouvrementsPageSize" class="form-select form-select-sm" style="width: auto;" aria-label="Nombre de lignes par page">
            <option value="10">10</option><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option>
          </select>
          <span id="recouvrementsGridInfo" class="small text-muted"></span>
          <button id="recouvrementsPrev" class="btn btn-sm btn-outline-secondary" type="button" title="Page précédente"><i class="bi bi-chevron-left"></i></button>
          <button id="recouvrementsNext" class="btn btn-sm btn-outline-secondary" type="button" title="Page suivante"><i class="bi bi-chevron-right"></i></button>
        </div>
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
                  <th class="grid-sortable" data-sort-key="eleve">Élève <i class="bi bi-arrow-down-up small"></i></th>
                  <?php foreach ($feeColumns as $feeColumn): ?>
                    <th class="text-end grid-sortable" data-sort-key="<?= htmlspecialchars(strtolower($feeColumn), ENT_QUOTES, 'UTF-8') ?>">Payé - <?= htmlspecialchars($feeColumn) ?> <i class="bi bi-arrow-down-up small"></i></th>
                  <?php endforeach; ?>
                  <th class="text-end grid-sortable" data-sort-key="initial">Montant initial <i class="bi bi-arrow-down-up small"></i></th>
                  <th class="text-end grid-sortable" data-sort-key="paye">Déjà payé <i class="bi bi-arrow-down-up small"></i></th>
                  <th class="text-end grid-sortable" data-sort-key="restant">Dette restante <i class="bi bi-arrow-down-up small"></i></th>
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
                  <tr class="recouvrements-grid-row" data-class="<?= htmlspecialchars($studentClass, ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars(strtolower($studentClass . ' ' . $name . ' ' . ($debt['matricule'] ?? '') . ' ' . ($debt['frais_liste'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                    <td>
                      <div class="fw-semibold" data-grid-value="eleve"><?= htmlspecialchars($name) ?></div>
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

    const table = document.getElementById('recouvrementsTable');
    const tbody = table?.querySelector('tbody');
    const gridSearch = document.getElementById('recouvrementsGridSearch');
    const pageSizeSelect = document.getElementById('recouvrementsPageSize');
    const gridInfo = document.getElementById('recouvrementsGridInfo');
    const previousButton = document.getElementById('recouvrementsPrev');
    const nextButton = document.getElementById('recouvrementsNext');
    if (!table || !tbody || !gridSearch || !pageSizeSelect) return;

    const rows = [...tbody.querySelectorAll('.recouvrements-grid-row')];
    const headers = [...table.querySelectorAll('thead th[data-sort-key]')];
    let page = 1;
    let sortKey = '';
    let sortDirection = 1;

    const numericValue = (value) => {
      const match = String(value || '').replace(/\s/g, '').match(/-?[\d]+(?:[,.][\d]+)?/);
      return match ? Number(match[0].replace(',', '.')) : 0;
    };
    const headerIndex = (key) => headers.findIndex((header) => header.dataset.sortKey === key);
    const getCellValue = (row, key) => {
      const index = headerIndex(key);
      return index >= 0 ? (row.children[index]?.textContent || '').trim() : '';
    };
    const isNumericSort = (key) => ['initial', 'paye', 'restant'].includes(key) || key !== 'eleve' && headerIndex(key) > 0;

    const renderGrid = () => {
      const query = gridSearch.value.trim().toLowerCase();
      const pageSize = Number(pageSizeSelect.value) || 25;
      let visibleRows = rows.filter((row) => (row.dataset.search || '').includes(query));
      if (sortKey) {
        visibleRows.sort((left, right) => {
          const leftValue = getCellValue(left, sortKey);
          const rightValue = getCellValue(right, sortKey);
          if (isNumericSort(sortKey)) return (numericValue(leftValue) - numericValue(rightValue)) * sortDirection;
          return leftValue.localeCompare(rightValue, 'fr', { sensitivity: 'base' }) * sortDirection;
        });
      }

      const total = visibleRows.length;
      const totalPages = Math.max(1, Math.ceil(total / pageSize));
      page = Math.min(page, totalPages);
      const start = (page - 1) * pageSize;
      const pageRows = visibleRows.slice(start, start + pageSize);
      tbody.replaceChildren();
      let currentClass = null;
      pageRows.forEach((row) => {
        const studentClass = row.dataset.class || 'Classe non définie';
        if (studentClass !== currentClass) {
          currentClass = studentClass;
          const classRow = document.createElement('tr');
          classRow.className = 'table-primary';
          const cell = document.createElement('td');
          cell.colSpan = table.tHead.rows[0].cells.length;
          cell.className = 'fw-semibold';
          cell.innerHTML = '<i class="bi bi-mortarboard me-1"></i>' + studentClass.replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char]);
          classRow.appendChild(cell);
          tbody.appendChild(classRow);
        }
        tbody.appendChild(row);
      });
      if (gridInfo) gridInfo.textContent = total ? `${start + 1}-${Math.min(start + pageSize, total)} sur ${total}` : 'Aucun résultat';
      if (previousButton) previousButton.disabled = page <= 1;
      if (nextButton) nextButton.disabled = page >= totalPages;
    };

    gridSearch.addEventListener('input', () => { page = 1; renderGrid(); });
    pageSizeSelect.addEventListener('change', () => { page = 1; renderGrid(); });
    previousButton?.addEventListener('click', () => { page -= 1; renderGrid(); });
    nextButton?.addEventListener('click', () => { page += 1; renderGrid(); });
    headers.forEach((header) => header.addEventListener('click', () => {
      const nextKey = header.dataset.sortKey || '';
      sortDirection = sortKey === nextKey ? sortDirection * -1 : 1;
      sortKey = nextKey;
      headers.forEach((item) => item.classList.remove('text-primary'));
      header.classList.add('text-primary');
      renderGrid();
    }));
    renderGrid();
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>