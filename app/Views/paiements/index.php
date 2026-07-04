<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Paiements reçus</h1>
        <p class="text-muted">Liste des paiements enregistrés</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Paiements</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row mb-3">
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="paymentSearch" type="search" class="form-control" placeholder="Rechercher dans les paiements..." aria-label="Recherche paiements">
        </div>
      </div>
      <div class="col-md-4">
        <select id="eleveFilter" class="form-select">
          <option value="">Tous les élèves</option>
          <?php foreach (($students ?? []) as $student): ?>
            <option value="<?= (int) $student['id'] ?>" <?= (!empty($eleveId) && (int) $eleveId === (int) $student['id']) ? 'selected' : '' ?>><?= htmlspecialchars(trim(($student['prenom'] ?? '') . ' ' . ($student['nom'] ?? '') . ' ' . ($student['postnom'] ?? ''))) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <select id="fraisFilter" class="form-select">
          <option value="">Tous les frais</option>
          <?php foreach (($fees ?? []) as $fee): ?>
            <option value="<?= (int) $fee['id'] ?>" <?= (!empty($fraisId) && (int) $fraisId === (int) $fee['id']) ? 'selected' : '' ?>><?= htmlspecialchars(trim(($fee['type_frais'] ?? '') . ' - ' . ($fee['nom_classe'] ?? '') . ' ' . ($fee['scope_label'] ?? ''))) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6 text-end">
          <?php if (in_array($role, ['super_admin','comptable_école'], true)): ?>
            <a href="<?= BASE_URL ?>/paiements/create<?= !empty($eleveId) ? '?eleve_id=' . (int) $eleveId : '' ?>" class="btn btn-success">Enregistrer paiement</a>
          <?php endif; ?>
          <div class="btn-group ms-2" role="group">
            <button id="resetFilters" type="button" class="btn btn-outline-secondary">Réinitialiser filtres</button>
            <a href="<?= BASE_URL ?>/paiements/export?format=csv<?= (!empty($eleveId) ? '&eleve_id=' . (int) $eleveId : '') . (!empty($fraisId) ? '&frais_id=' . (int) $fraisId : '') ?>" class="btn btn-outline-secondary export-filtered" data-format="csv">Export CSV</a>
            <a href="<?= BASE_URL ?>/paiements/export?format=excel<?= (!empty($eleveId) ? '&eleve_id=' . (int) $eleveId : '') . (!empty($fraisId) ? '&frais_id=' . (int) $fraisId : '') ?>" class="btn btn-outline-secondary export-filtered" data-format="excel">Export Excel</a>
            <a href="<?= BASE_URL ?>/paiements/export?format=pdf<?= (!empty($eleveId) ? '&eleve_id=' . (int) $eleveId : '') . (!empty($fraisId) ? '&frais_id=' . (int) $fraisId : '') ?>" class="btn btn-outline-secondary export-filtered" data-format="pdf">Export PDF</a>
          </div>
      </div>
    </div>
    <?php if (!empty($eleveFilter)): ?>
      <div class="alert alert-info py-2">
        Affichage des paiements pour l'élève <strong><?= htmlspecialchars(($eleveFilter['prenom'] ?? '') . ' ' . ($eleveFilter['nom'] ?? '') . ' ' . ($eleveFilter['postnom'] ?? '')) ?></strong>.
      </div>
    <?php endif; ?>
    <div class="row">
      <div class="col-12">
        <div class="card card-outline card-secondary">
          <div class="card-body table-responsive p-0">
            <table id="paymentsDataGrid" class="table table-striped table-hover table-bordered mb-0">
              <thead class="table-dark">
                <tr>
                  <th style="width: 50px;">#</th>
                  <th data-sort-key="reference_recu" class="sortable">Réf reçu</th>
                  <th data-sort-key="eleve" class="sortable">Élève</th>
                  <th data-sort-key="libelle" class="sortable">Motif / Frais</th>
                  <th data-sort-key="date_operation" class="sortable">Date opération</th>
                  <th data-sort-key="montant" class="sortable">Montant</th>
                  <th data-sort-key="frais_devise" class="sortable">Devise</th>
                  <th data-sort-key="nom_compte" class="sortable">Caisse / Compte</th>
                  <th data-sort-key="agent_nom" class="sortable">Agent</th>
                  <th style="width: 110px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($payments)): ?>
                  <tr><td colspan="10" class="text-center py-4">Aucun paiement enregistré.</td></tr>
                <?php else: ?>
                  <?php foreach ($payments as $i => $p): ?>
                    <tr>
                      <td><?= $i + 1 ?></td>
                      <td data-key="reference_recu"><?= htmlspecialchars($p['reference_recu'] ?? '-') ?></td>
                      <td data-key="eleve"><?= htmlspecialchars(trim(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['postnom'] ?? ''))) ?></td>
                      <td data-key="libelle"><?= htmlspecialchars($p['libelle'] ?? '-') ?></td>
                      <td data-key="date_operation"><?= htmlspecialchars($p['date_operation'] ?? '') ?></td>
                      <td data-key="montant"><?= htmlspecialchars($p['montant_affiche'] ?? number_format((float) ($p['montant'] ?? 0), 2)) ?></td>
                      <td data-key="frais_devise"><?= htmlspecialchars($p['frais_devise'] ?? ($p['transaction_devise'] ?? 'USD')) ?></td>
                      <td data-key="nom_compte"><?= htmlspecialchars($p['nom_compte'] ?? '') ?></td>
                      <td data-key="agent_nom"><?= htmlspecialchars($p['agent_nom'] ?? '') ?></td>
                      <td>
                        <a href="<?= BASE_URL ?>/paiements/receipt?id=<?= urlencode($p['id']) ?>" class="btn btn-sm btn-outline-primary">Reçu</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center py-2">
      <div>
        <button id="prevPage" class="btn btn-sm btn-outline-secondary me-2" type="button">Précédent</button>
        <button id="nextPage" class="btn btn-sm btn-outline-secondary" type="button">Suivant</button>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span id="pageInfo">Page 1 / 1</span>
        <select id="pageSize" class="form-select form-select-sm" style="width: auto;">
          <option value="5">5 / page</option>
          <option value="10" selected>10 / page</option>
          <option value="20">20 / page</option>
          <option value="50">50 / page</option>
        </select>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const initialEleveId = <?= json_encode($eleveId ?? null) ?>;
        const initialFraisId = <?= json_encode($fraisId ?? null) ?>;
        const searchInput = document.getElementById('paymentSearch');
        const eleveFilterSelect = document.getElementById('eleveFilter');
        const fraisFilterSelect = document.getElementById('fraisFilter');
        const table = document.getElementById('paymentsDataGrid');
        const prevPageButton = document.getElementById('prevPage');
        const nextPageButton = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        const pageSizeSelect = document.getElementById('pageSize');
        if (!table || !prevPageButton || !nextPageButton || !pageInfo || !pageSizeSelect) return;

        const tbody = table.tBodies[0];
        let originalRows = Array.from(tbody.querySelectorAll('tr'));
        let filteredRows = originalRows.slice();
        let currentPage = 1;
        let pageSize = parseInt(pageSizeSelect.value, 10);
        let currentSort = { key: null, direction: 1 };

        const sortableHeaders = Array.from(table.querySelectorAll('th.sortable'));
        sortableHeaders.forEach(function (header) {
          header.style.cursor = 'pointer';
          header.addEventListener('click', function () {
            const sortKey = header.dataset.sortKey;
            if (!sortKey) return;
            if (currentSort.key === sortKey) {
              currentSort.direction *= -1;
            } else {
              currentSort.key = sortKey;
              currentSort.direction = 1;
            }
            sortableHeaders.forEach(h => h.classList.remove('sorted-asc', 'sorted-desc'));
            header.classList.add(currentSort.direction === 1 ? 'sorted-asc' : 'sorted-desc');
            applySearch();
          });
        });

        function createNoDataRow() {
          const row = document.createElement('tr');
          row.className = 'no-data-row';
          const cell = document.createElement('td');
          cell.colSpan = 10;
          cell.className = 'text-center py-4';
          cell.textContent = 'Aucune donnée correspondante trouvée.';
          row.appendChild(cell);
          return row;
        }

        const noDataRow = createNoDataRow();

        function updateTableRows(rowsToDisplay) {
          tbody.innerHTML = '';
          if (rowsToDisplay.length === 0) {
            tbody.appendChild(noDataRow);
            return;
          }
          rowsToDisplay.forEach(function (row) { tbody.appendChild(row); });
        }

        function renderTable() {
          const start = (currentPage - 1) * pageSize;
          const end = start + pageSize;
          const pageRows = filteredRows.slice(start, end);
          updateTableRows(pageRows);
          const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
          pageInfo.textContent = `Page ${currentPage} / ${totalPages}`;
          prevPageButton.disabled = currentPage <= 1;
          nextPageButton.disabled = currentPage >= totalPages;
        }

        function sortRows(rows) {
          if (!currentSort.key) return rows;
          const key = currentSort.key;
          const direction = currentSort.direction;
          return rows.slice().sort(function (a, b) {
            const aText = a.querySelector(`td[data-key="${key}"]`)?.textContent?.trim().toLowerCase() || '';
            const bText = b.querySelector(`td[data-key="${key}"]`)?.textContent?.trim().toLowerCase() || '';
            const aNum = parseFloat(aText.replace(/[^0-9.-]+/g, ''));
            const bNum = parseFloat(bText.replace(/[^0-9.-]+/g, ''));
            if (!isNaN(aNum) && !isNaN(bNum)) {
              return (aNum - bNum) * direction;
            }
            if (aText < bText) return -1 * direction;
            if (aText > bText) return 1 * direction;
            return 0;
          });
        }

        function applySearch() {
          const filter = (searchInput && searchInput.value) ? searchInput.value.toLowerCase() : '';
          filteredRows = originalRows.filter(function (row) {
            if (row.classList.contains('no-data-row')) return false;
            return row.textContent.toLowerCase().includes(filter);
          });
          filteredRows = sortRows(filteredRows);
          currentPage = 1;
          renderTable();
        }

        const exportLinks = Array.from(document.querySelectorAll('.export-filtered'));

        function updateExportLinks() {
          const params = new URLSearchParams();
          if (eleveFilterSelect && eleveFilterSelect.value) {
            params.set('eleve_id', eleveFilterSelect.value);
          } else if (initialEleveId) {
            params.set('eleve_id', initialEleveId);
          }
          if (fraisFilterSelect && fraisFilterSelect.value) {
            params.set('frais_id', fraisFilterSelect.value);
          } else if (initialFraisId) {
            params.set('frais_id', initialFraisId);
          }
          exportLinks.forEach(function (link) {
            const format = link.dataset.format;
            link.href = '<?= BASE_URL ?>/paiements/export?format=' + encodeURIComponent(format) + (params.toString() ? '&' + params.toString() : '');
          });
        }

        const resetFiltersButton = document.getElementById('resetFilters');

        if (searchInput) searchInput.addEventListener('input', function () { currentPage = 1; applySearch(); });
        if (eleveFilterSelect) eleveFilterSelect.addEventListener('change', function () { currentPage = 1; updateExportLinks(); fetchPaymentsAndUpdate(); });
        if (fraisFilterSelect) fraisFilterSelect.addEventListener('change', function () { currentPage = 1; updateExportLinks(); fetchPaymentsAndUpdate(); });
        if (resetFiltersButton) {
          resetFiltersButton.addEventListener('click', function () {
            if (eleveFilterSelect) eleveFilterSelect.value = '';
            if (fraisFilterSelect) fraisFilterSelect.value = '';
            if (searchInput) searchInput.value = '';
            updateExportLinks();
            currentPage = 1;
            fetchPaymentsAndUpdate();
            applySearch();
          });
        }
        prevPageButton.addEventListener('click', function () { if (currentPage > 1) { currentPage -= 1; renderTable(); } });
        nextPageButton.addEventListener('click', function () { const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize)); if (currentPage < totalPages) { currentPage += 1; renderTable(); } });
        pageSizeSelect.addEventListener('change', function () { pageSize = parseInt(this.value, 10); currentPage = 1; renderTable(); });

        updateExportLinks();
        renderTable();

        // Polling: refresh payments every 5 seconds
        let lastDataHash = null;
        function buildPaymentsListUrl() {
          const url = new URL('<?= BASE_URL ?>/paiements/listJson', window.location.origin);
          if (eleveFilterSelect && eleveFilterSelect.value) {
            url.searchParams.set('eleve_id', eleveFilterSelect.value);
          } else if (initialEleveId) {
            url.searchParams.set('eleve_id', initialEleveId);
          }
          if (fraisFilterSelect && fraisFilterSelect.value) {
            url.searchParams.set('frais_id', fraisFilterSelect.value);
          } else if (initialFraisId) {
            url.searchParams.set('frais_id', initialFraisId);
          }
          return url.toString();
        }

        async function fetchPaymentsAndUpdate() {
          try {
            const res = await fetch(buildPaymentsListUrl());
            if (!res.ok) return;
            const json = await res.json();
            const payments = json.payments || [];
            // build rows from payments
            const newRows = payments.map((p, idx) => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${idx+1}</td>
                <td data-key="reference_recu">${(p.reference_recu||'-')}</td>
                <td data-key="eleve">${((p.prenom||'') + ' ' + (p.nom||'') + ' ' + (p.postnom||'')).trim()}</td>
                <td data-key="libelle">${p.libelle || '-'}</td>
                <td data-key="date_operation">${p.date_operation || ''}</td>
                <td data-key="montant">${p.montant_affiche || Number(p.montant||0).toFixed(2)}</td>
                <td data-key="frais_devise">${p.frais_devise || (p.transaction_devise || 'USD')}</td>
                <td data-key="nom_compte">${p.nom_compte || ''}</td>
                <td data-key="agent_nom">${p.agent_nom || ''}</td>
                <td><a href="<?= BASE_URL ?>/paiements/receipt?id=${encodeURIComponent(p.id)}" class="btn btn-sm btn-outline-primary">Reçu</a></td>
              `;
              return tr;
            });

            // compute a simple hash to detect changes
            const hash = payments.map(p => (p.id+'|'+p.date_operation+'|'+p.montant)).join('\n');
            if (hash !== lastDataHash) {
              lastDataHash = hash;
              // update originalRows and filteredRows
              originalRows.length = 0;
              newRows.forEach(r => originalRows.push(r));
              applySearch();
            }
          } catch (e) {
            // ignore errors
          }
        }

        // start polling
        fetchPaymentsAndUpdate();
        setInterval(fetchPaymentsAndUpdate, 5000);
      });
    </script>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>