<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$filters = $filters ?? [];
$classes = $classes ?? [];
$sections = $sections ?? [];
$fees = $fees ?? [];
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
    'section_id' => $filters['section_id'] ?? '',
     'classe' => $filters['classe'] ?? '',
    'frais_id' => $filters['frais_id'] ?? '',
     'devise' => $filters['devise'] ?? '',
     'montant_min' => $filters['montant_min'] ?? '',
     'montant_max' => $filters['montant_max'] ?? '',
     'colonnes' => implode(',', $columns),
   ], static fn ($value) => $value !== '' && $value !== null);
   return BASE_URL . '/recouvrements/export?' . http_build_query($query);
};
?>
<style>
  .recouvrements-grid-wrap { border-top: 1px solid #dee2e6; }
  .recouvrements-grid { min-width: 1040px; }
  .recouvrements-grid thead th { padding: .8rem .9rem; color: #52606d; font-size: .72rem; letter-spacing: 0; text-transform: uppercase; white-space: nowrap; }
  .recouvrements-grid tbody td { padding: .85rem .9rem; }
  .recouvrements-grid tbody tr:hover > td { background-color: #f7fafc; }
  .recouvrements-grid .grid-sortable { cursor: pointer; user-select: none; }
  .recouvrements-grid .grid-sortable:hover { color: #0d6efd; }
  .rec-student-name { color: #243447; font-weight: 600; }
  .rec-student-meta { color: #6c757d; font-size: .78rem; }
  .rec-money-stack { white-space: nowrap; font-variant-numeric: tabular-nums; }
  .rec-money-stack > div + div { margin-top: .18rem; }
  .rec-status { min-width: 82px; }
  .rec-fee-detail { background: #f7f9fb; }
  .rec-fee-detail > td { padding: .8rem 1rem 1rem !important; }
  .rec-fee-table { min-width: 620px; }
  .rec-fee-table th { color: #66727f; font-size: .7rem; text-transform: uppercase; }
  .rec-grid-toolbar { background: #fff; }
  @media (max-width: 767.98px) {
    .rec-grid-toolbar > * { width: 100%; }
    .rec-grid-toolbar .input-group { max-width: none !important; }
    .rec-grid-paging { justify-content: space-between; }
  }
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1 class="mb-1">Recouvrements</h1>
        <p class="text-muted mb-0">Toutes les dettes, avec les montants payés et les soldes restants.</p>
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
          <div class="inner"><h3><?= (int) ($summary['students'] ?? 0) ?></h3><p>Élèves concernés</p></div>
          <div class="icon"><i class="bi bi-people"></i></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="small-box bg-warning mb-0 h-100">
          <div class="inner"><h3><?= (int) ($summary['records'] ?? 0) ?></h3><p>Dettes enregistrées</p></div>
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
        <h3 class="card-title mb-0">Toutes les dettes et les paiements</h3>
        <form method="get" action="<?= BASE_URL ?>/recouvrements" class="row g-2 align-items-end w-100">
          <div class="col-12 col-md-2"><label class="form-label small mb-1">Recherche</label><input name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" type="search" class="form-control" placeholder="Élève, matricule, frais"></div>
          <div class="col-6 col-md-2"><label class="form-label small mb-1">Classe</label><select name="classe" class="form-select"><option value="">Toutes les classes</option><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>" <?= (int) ($filters['classe'] ?? 0) === (int) $class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['nom_classe'] . (!empty($class['nom_option']) ? ' · ' . $class['nom_option'] : '')) ?></option><?php endforeach; ?></select></div>
          <div class="col-6 col-md-2"><label class="form-label small mb-1">Section</label><select name="section_id" class="form-select"><option value="">Toutes les sections</option><?php foreach ($sections as $section): ?><option value="<?= (int) $section['id'] ?>" <?= (int) ($filters['section_id'] ?? 0) === (int) $section['id'] ? 'selected' : '' ?>><?= htmlspecialchars($section['nom_section']) ?></option><?php endforeach; ?></select></div>
          <div class="col-6 col-md-2"><label class="form-label small mb-1">Frais</label><select name="frais_id" class="form-select"><option value="">Tous les frais</option><?php foreach ($fees as $fee): ?><option value="<?= (int) $fee['id'] ?>" <?= (int) ($filters['frais_id'] ?? 0) === (int) $fee['id'] ? 'selected' : '' ?>><?= htmlspecialchars($fee['type_frais']) ?> · <?= htmlspecialchars(strtoupper($fee['devise'] ?? 'USD')) ?></option><?php endforeach; ?></select></div>
          <div class="col-6 col-md-1"><label class="form-label small mb-1">Devise</label><input name="devise" value="<?= htmlspecialchars($filters['devise'] ?? '') ?>" class="form-control" placeholder="USD"></div>
          <div class="col-6 col-md-1"><label class="form-label small mb-1">Dette min.</label><input name="montant_min" value="<?= htmlspecialchars((string) ($filters['montant_min'] ?? '')) ?>" type="number" min="0" step="0.01" class="form-control"></div>
          <div class="col-6 col-md-1"><label class="form-label small mb-1">Dette max.</label><input name="montant_max" value="<?= htmlspecialchars((string) ($filters['montant_max'] ?? '')) ?>" type="number" min="0" step="0.01" class="form-control"></div>
          <div class="col-6 col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-fill" type="submit" title="Filtrer"><i class="bi bi-funnel"></i></button>
            <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/recouvrements" title="Réinitialiser les filtres"><i class="bi bi-arrow-counterclockwise"></i></a>
          </div>
        </form>
        <details class="w-100"><summary class="btn btn-sm btn-outline-secondary">Personnaliser la mise en page</summary>
          <div class="border rounded p-2 mt-2"><div class="row g-2">
            <?php foreach ($exportColumns as $key => $label): ?><div class="col-6 col-md-3"><label class="form-check"><input class="form-check-input layout-column" type="checkbox" value="<?= $key ?>" checked><span class="form-check-label"><?= htmlspecialchars($label) ?></span></label></div><?php endforeach; ?>
          </div><small class="text-muted">Les colonnes sélectionnées seront utilisées pour les exports.</small></div>
        </details>
        <div class="rec-grid-toolbar d-flex flex-wrap align-items-center gap-2 w-100 pt-2 border-top">
          <div class="input-group" style="max-width: 360px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="recouvrementsGridSearch" type="search" class="form-control" placeholder="Rechercher dans la liste..." aria-label="Rechercher dans la liste">
          </div>
          <label class="visually-hidden" for="recouvrementsStatusFilter">Filtrer par statut</label>
          <select id="recouvrementsStatusFilter" class="form-select form-select-sm" style="width: auto; min-width: 150px;">
            <option value="">Tous les statuts</option>
            <option value="impaye">Impayé</option>
            <option value="partiel">Paiement partiel</option>
            <option value="regle">Réglé</option>
          </select>
          <span class="small text-muted"><?= number_format(count($debts), 0, ',', ' ') ?> élève(s)</span>
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
            Aucune dette enregistrée pour ces critères.
          </div>
        <?php else: ?>
          <div class="table-responsive recouvrements-grid-wrap">
            <table class="table table-hover align-middle mb-0 recouvrements-grid" id="recouvrementsTable">
              <thead class="table-light">
                <tr>
                  <th class="grid-sortable" data-sort-key="section">Section <i class="bi bi-arrow-down-up small"></i></th>
                  <th class="grid-sortable" data-sort-key="classe">Classe <i class="bi bi-arrow-down-up small"></i></th>
                  <th class="grid-sortable" data-sort-key="eleve">Élève <i class="bi bi-arrow-down-up small"></i></th>
                  <th class="text-center grid-sortable" data-sort-key="fees">Frais <i class="bi bi-arrow-down-up small"></i></th>
                  <th class="text-end">Montant initial</th>
                  <th class="text-end">Déjà payé</th>
                  <th class="text-end">Reste à payer</th>
                  <th class="text-center grid-sortable" data-sort-key="status">Statut <i class="bi bi-arrow-down-up small"></i></th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($debts as $debt): ?>
                  <?php
                    $initialAmounts = $debt['initial_by_currency'] ?? [];
                    $paidAmounts = $debt['paid_by_currency'] ?? [];
                    $remainingAmounts = $debt['remaining_by_currency'] ?? [];
                    $hasRemaining = (bool) array_filter($remainingAmounts, static fn ($amount) => (float) $amount > 0.005);
                    $hasPaid = (bool) array_filter($paidAmounts, static fn ($amount) => (float) $amount > 0.005);
                    $status = !$hasRemaining ? 'regle' : ($hasPaid ? 'partiel' : 'impaye');
                    $name = trim(($debt['nom'] ?? '') . ' ' . ($debt['postnom'] ?? '') . ' ' . ($debt['prenom'] ?? ''));
                    $studentSection = trim((string) ($debt['nom_section'] ?? 'Section non définie')) ?: 'Section non définie';
                    $studentClass = trim((string) ($debt['nom_classe'] ?? 'Classe non définie')) ?: 'Classe non définie';
                    $classLabel = $studentClass . (!empty($debt['nom_option']) ? ' · ' . $debt['nom_option'] : '');
                    $detailsId = 'rec-fees-' . (int) $debt['eleve_id'];
                    $formatAmounts = static function (array $amounts) use ($formatAmount): string {
                      $values = [];
                      foreach ($amounts as $currency => $amount) {
                        $values[] = '<div>' . $formatAmount($amount) . ' <span class="text-muted">' . htmlspecialchars((string) $currency, ENT_QUOTES, 'UTF-8') . '</span></div>';
                      }
                      return $values ? '<div class="rec-money-stack">' . implode('', $values) . '</div>' : '<span class="text-muted">—</span>';
                    };
                  ?>
                  <tr class="recouvrements-grid-row" data-section="<?= htmlspecialchars(strtolower($studentSection), ENT_QUOTES, 'UTF-8') ?>" data-section-label="<?= htmlspecialchars($studentSection, ENT_QUOTES, 'UTF-8') ?>" data-class="<?= htmlspecialchars(strtolower($classLabel), ENT_QUOTES, 'UTF-8') ?>" data-eleve="<?= htmlspecialchars(strtolower($name), ENT_QUOTES, 'UTF-8') ?>" data-fees="<?= count($debt['frais_details'] ?? []) ?>" data-status="<?= $status ?>" data-search="<?= htmlspecialchars(strtolower($studentSection . ' ' . $classLabel . ' ' . $name . ' ' . ($debt['matricule'] ?? '') . ' ' . ($debt['frais_liste'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" data-detail-id="<?= $detailsId ?>">
                    <td><?= htmlspecialchars($studentSection) ?></td>
                    <td><span class="fw-semibold"><?= htmlspecialchars($classLabel) ?></span></td>
                    <td>
                      <div class="rec-student-name"><?= htmlspecialchars($name) ?></div>
                      <div class="rec-student-meta"><?= htmlspecialchars($debt['matricule'] ?? 'Sans matricule') ?></div>
                    </td>
                    <td class="text-center"><span class="badge text-bg-light border"><?= count($debt['frais_details'] ?? []) ?></span></td>
                    <td class="text-end"><?= $formatAmounts($initialAmounts) ?></td>
                    <td class="text-end text-success fw-semibold"><?= $formatAmounts($paidAmounts) ?></td>
                    <td class="text-end fw-semibold <?= $hasRemaining ? 'text-danger' : 'text-muted' ?>"><?= $formatAmounts($remainingAmounts) ?></td>
                    <td class="text-center">
                      <?php if ($status === 'regle'): ?><span class="badge rounded-pill text-bg-success rec-status">Réglé</span>
                      <?php elseif ($status === 'partiel'): ?><span class="badge rounded-pill text-bg-warning rec-status">Partiel</span>
                      <?php else: ?><span class="badge rounded-pill text-bg-danger rec-status">Impayé</span><?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                      <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/paiements?eleve_id=<?= (int) $debt['eleve_id'] ?>" title="Voir les paiements" aria-label="Voir les paiements de <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-wallet2"></i></a>
                      <button class="btn btn-sm btn-outline-secondary" type="button" data-detail-toggle aria-controls="<?= $detailsId ?>" aria-expanded="false" title="Détail des frais" aria-label="Détail des frais de <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-list-ul"></i></button>
                    </td>
                  </tr>
                  <tr id="<?= $detailsId ?>" class="rec-fee-detail" hidden>
                    <td colspan="9">
                      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div><strong>Détail des frais</strong><span class="text-muted small ms-2"><?= htmlspecialchars($name) ?></span></div>
                        <details class="text-start">
                          <summary class="btn btn-sm btn-outline-warning"><i class="bi bi-file-earmark-check me-1"></i>Demander une dérogation</summary>
                          <form method="post" action="<?= BASE_URL ?>/derogations/request" class="border rounded bg-white shadow-sm p-3 mt-2" style="min-width: 280px; position: absolute; right: 1rem; z-index: 10;">
                            <input type="hidden" name="eleve_id" value="<?= (int) $debt['eleve_id'] ?>">
                            <label class="form-label small mb-1">Protéger l’élève jusqu’au</label>
                            <input class="form-control form-control-sm mb-2" name="date_fin" type="date" min="<?= date('Y-m-d') ?>" required>
                            <label class="form-label small mb-1">Motif</label>
                            <textarea class="form-control form-control-sm mb-2" name="motif" rows="2" maxlength="500" required></textarea>
                            <button class="btn btn-sm btn-warning w-100" type="submit">Demander au promoteur</button>
                          </form>
                        </details>
                      </div>
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white mb-0 rec-fee-table">
                          <thead><tr><th>Frais</th><th class="text-end">Montant prévu</th><th class="text-end">Déjà payé</th><th class="text-end">Reste dû</th></tr></thead>
                          <tbody>
                            <?php foreach (($debt['frais_details'] ?? []) as $feeName => $feeAmounts): ?>
                              <tr>
                                <td><?= htmlspecialchars($feeName) ?></td>
                                <td class="text-end"><?= $formatAmounts($feeAmounts['initial_by_currency'] ?? []) ?></td>
                                <td class="text-end text-success"><?= $formatAmounts($feeAmounts['paid_by_currency'] ?? []) ?></td>
                                <td class="text-end fw-semibold"><?= $formatAmounts($feeAmounts['remaining_by_currency'] ?? []) ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div id="recouvrementEmptySearch" class="p-4 text-center text-muted d-none">Aucun élève ne correspond à votre recherche.</div>
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
    const statusFilter = document.getElementById('recouvrementsStatusFilter');
    const emptySearchMessage = document.getElementById('recouvrementEmptySearch');
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
    const getSortValue = (row, key) => row.dataset[key] || '';

    const renderGrid = () => {
      const query = gridSearch.value.trim().toLowerCase();
      const selectedStatus = statusFilter?.value || '';
      const pageSize = Number(pageSizeSelect.value) || 25;
      let visibleRows = rows.filter((row) => (row.dataset.search || '').includes(query)
        && (!selectedStatus || row.dataset.status === selectedStatus));
      if (sortKey) {
        visibleRows.sort((left, right) => {
          const leftValue = getSortValue(left, sortKey);
          const rightValue = getSortValue(right, sortKey);
          if (sortKey === 'fees') return (Number(leftValue) - Number(rightValue)) * sortDirection;
          const valueCompare = leftValue.localeCompare(rightValue, 'fr', { sensitivity: 'base' }) * sortDirection;
          if (valueCompare) return valueCompare;
          return getSortValue(left, 'class').localeCompare(getSortValue(right, 'class'), 'fr', { sensitivity: 'base' })
            || getSortValue(left, 'eleve').localeCompare(getSortValue(right, 'eleve'), 'fr', { sensitivity: 'base' });
        });
      } else {
        visibleRows.sort((left, right) => getSortValue(left, 'section').localeCompare(getSortValue(right, 'section'), 'fr', { sensitivity: 'base' })
          || getSortValue(left, 'class').localeCompare(getSortValue(right, 'class'), 'fr', { sensitivity: 'base' })
          || getSortValue(left, 'eleve').localeCompare(getSortValue(right, 'eleve'), 'fr', { sensitivity: 'base' }));
      }

      const total = visibleRows.length;
      const totalPages = Math.max(1, Math.ceil(total / pageSize));
      page = Math.min(page, totalPages);
      const start = (page - 1) * pageSize;
      const pageRows = visibleRows.slice(start, start + pageSize);
      if (table) table.classList.toggle('d-none', total === 0);
      if (emptySearchMessage) emptySearchMessage.classList.toggle('d-none', total > 0);
      tbody.replaceChildren();
      pageRows.forEach((row) => {
        tbody.appendChild(row);
        const detailRow = document.getElementById(row.dataset.detailId);
        if (detailRow && !detailRow.hidden) tbody.appendChild(detailRow);
      });
      if (gridInfo) gridInfo.textContent = total ? `${start + 1}-${Math.min(start + pageSize, total)} sur ${total}` : 'Aucun résultat';
      if (previousButton) previousButton.disabled = page <= 1;
      if (nextButton) nextButton.disabled = page >= totalPages;
    };

    gridSearch.addEventListener('input', () => { page = 1; renderGrid(); });
    statusFilter?.addEventListener('change', () => { page = 1; renderGrid(); });
    pageSizeSelect.addEventListener('change', () => { page = 1; renderGrid(); });
    previousButton?.addEventListener('click', () => { page -= 1; renderGrid(); });
    nextButton?.addEventListener('click', () => { page += 1; renderGrid(); });
    tbody.addEventListener('click', (event) => {
      const button = event.target.closest('[data-detail-toggle]');
      if (!button) return;
      const detailRow = document.getElementById(button.getAttribute('aria-controls'));
      if (!detailRow) return;
      detailRow.hidden = !detailRow.hidden;
      button.setAttribute('aria-expanded', String(!detailRow.hidden));
      button.classList.toggle('btn-outline-secondary', detailRow.hidden);
      button.classList.toggle('btn-secondary', !detailRow.hidden);
    });
    headers.forEach((header) => header.addEventListener('click', () => {
      const nextKey = header.dataset.sortKey || '';
      sortDirection = sortKey === nextKey ? sortDirection * -1 : 1;
      sortKey = nextKey;
      headers.forEach((item) => item.classList.remove('text-primary'));
      header.classList.add('text-primary');
      headers.forEach((item) => item.setAttribute('aria-sort', 'none'));
      header.setAttribute('aria-sort', sortDirection === 1 ? 'ascending' : 'descending');
      renderGrid();
    }));
    renderGrid();
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>