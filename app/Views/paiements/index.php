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
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="paymentSearch" type="search" class="form-control" placeholder="Rechercher dans les paiements..." aria-label="Recherche paiements">
        </div>
      </div>
      <div class="col-md-6 text-end">
          <?php if (in_array($role, ['super_admin','comptable_école'], true)): ?>
            <a href="<?= BASE_URL ?>/paiements/create" class="btn btn-success">Enregistrer paiement</a>
          <?php endif; ?>
          <div class="btn-group ms-2" role="group">
            <a href="<?= BASE_URL ?>/paiements/export?format=csv" class="btn btn-outline-secondary">Export CSV</a>
            <a href="<?= BASE_URL ?>/paiements/export?format=excel" class="btn btn-outline-secondary">Export Excel</a>
            <a href="<?= BASE_URL ?>/paiements/export?format=pdf" class="btn btn-outline-secondary">Export PDF</a>
          </div>
      </div>
    </div>

    <div class="card card-outline card-primary">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="paymentsDataGrid" class="table table-sm table-striped mb-0">
            <thead class="table-light">
              <tr><th>#</th><th>Réf reçu</th><th>Élève</th><th>Date</th><th>Montant</th><th>Caisse</th><th>Agent</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (empty($payments)): ?>
                <tr><td colspan="8" class="text-center py-4">Aucun paiement enregistré.</td></tr>
              <?php else: ?>
                <?php foreach ($payments as $i => $p): ?>
                  <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($p['reference_recu'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['postnom'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($p['date_operation'] ?? '') ?></td>
                    <td><?= number_format((float) ($p['montant'] ?? 0), 2) ?></td>
                    <td><?= htmlspecialchars($p['nom_compte'] ?? $p['nom_compte']) ?></td>
                    <td><?= htmlspecialchars($p['agent_nom'] ?? '') ?></td>
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
        const searchInput = document.getElementById('paymentSearch');
        const table = document.getElementById('paymentsDataGrid');
        const prevPageButton = document.getElementById('prevPage');
        const nextPageButton = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        const pageSizeSelect = document.getElementById('pageSize');
        if (!table || !prevPageButton || !nextPageButton || !pageInfo || !pageSizeSelect) return;

        const tbody = table.tBodies[0];
        const originalRows = Array.from(tbody.querySelectorAll('tr'));
        let filteredRows = originalRows.slice();
        let currentPage = 1;
        let pageSize = parseInt(pageSizeSelect.value, 10);

        function createNoDataRow() {
          const row = document.createElement('tr');
          row.className = 'no-data-row';
          const cell = document.createElement('td');
          cell.colSpan = 8;
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

        function applySearch() {
          const filter = (searchInput && searchInput.value) ? searchInput.value.toLowerCase() : '';
          filteredRows = originalRows.filter(function (row) {
            if (row.classList.contains('no-data-row')) return false;
            return row.textContent.toLowerCase().includes(filter);
          });
          currentPage = 1;
          renderTable();
        }

        if (searchInput) searchInput.addEventListener('input', applySearch);
        prevPageButton.addEventListener('click', function () { if (currentPage > 1) { currentPage -= 1; renderTable(); } });
        nextPageButton.addEventListener('click', function () { const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize)); if (currentPage < totalPages) { currentPage += 1; renderTable(); } });
        pageSizeSelect.addEventListener('change', function () { pageSize = parseInt(this.value, 10); currentPage = 1; renderTable(); });

        renderTable();

        // Polling: refresh payments every 5 seconds
        let lastDataHash = null;
        async function fetchPaymentsAndUpdate() {
          try {
            const res = await fetch('<?= BASE_URL ?>/paiements/listJson');
            if (!res.ok) return;
            const json = await res.json();
            const payments = json.payments || [];
            // build rows from payments
            const newRows = payments.map((p, idx) => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${idx+1}</td>
                <td>${(p.reference_recu||'-')}</td>
                <td>${((p.prenom||'') + ' ' + (p.nom||'') + ' ' + (p.postnom||'')).trim()}</td>
                <td>${p.date_operation || ''}</td>
                <td>${Number(p.montant||0).toFixed(2)}</td>
                <td>${p.nom_compte || ''}</td>
                <td>${p.agent_nom || ''}</td>
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