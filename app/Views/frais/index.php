<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$fees = $fees ?? [];
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Frais scolaires</h1>
              <p class="text-muted">Liste des frais existants et création de nouveaux frais pour l’école.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Frais</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['frais_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= htmlspecialchars($_SESSION['frais_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['frais_success']); ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['frais_errors'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <ul class="mb-0">
                <?php foreach ($_SESSION['frais_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['frais_errors']); ?>
          <?php endif; ?>

          <div class="row mb-3">
            <div class="col-md-6 mb-2">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input id="feeSearch" type="search" class="form-control" placeholder="Rechercher dans les frais..." aria-label="Recherche des frais">
              </div>
            </div>
            <div class="col-md-6 text-end mb-2">
              <a href="<?= BASE_URL ?>/frais/create" class="btn btn-success"><i class="bi bi-plus-lg me-2"></i>Nouveau frais</a>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="card card-outline card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h3 class="card-title">Frais enregistrés</h3>
                  <span class="badge bg-info">Total : <?= count($fees) ?></span>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table id="feesDataGrid" class="table table-striped table-hover table-bordered mb-0">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 50px;">#</th>
                          <th data-sort="1" data-type="string" class="cursor-pointer">Type de frais</th>
                          <th data-sort="2" data-type="number" class="cursor-pointer">Montant</th>
                          <th data-sort="3" data-type="string" class="cursor-pointer">Classe</th>
                          <th data-sort="4" data-type="string" class="cursor-pointer">Année scolaire</th>
                          <th data-sort="5" data-type="string" class="cursor-pointer">Devise</th>
                          <th data-sort="6" data-type="string" class="cursor-pointer">Portée</th>
                          <th style="width: 170px;">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($fees)): ?>
                          <tr>
                            <td colspan="8" class="text-center py-4">Aucun frais n'est encore défini pour cette école.</td>
                          </tr>
                        <?php else: ?>
                          <?php foreach ($fees as $index => $fee): ?>
                            <tr>
                              <td><?= $index + 1 ?></td>
                              <td><?= htmlspecialchars($fee['type_frais']) ?></td>
                              <td>
                                <?= number_format((float) $fee['montant_total'], 2, ',', ' ') ?>
                                <?= ' ' . htmlspecialchars($fee['devise'] ?? ($schoolCurrency ?? 'USD')) ?>
                              </td>
                              <td><?= htmlspecialchars($fee['nom_classe'] ?? 'N/A') ?></td>
                              <td><?= htmlspecialchars($fee['annee_scolaire'] ?? '-') ?></td>
                              <td><?= htmlspecialchars($fee['devise'] ?? ($schoolCurrency ?? 'USD')) ?></td>
                              <td><?= htmlspecialchars($fee['scope_label'] ?? '-') ?></td>
                              <td>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1">Détail</a>
                                <a href="#" class="btn btn-sm btn-outline-secondary">Modifier</a>
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
          </div>
          <div class="row">
            <div class="col-12">
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
            </div>
          </div>
          <script>
            document.addEventListener('DOMContentLoaded', function () {
              const searchInput = document.getElementById('feeSearch');
              const table = document.getElementById('feesDataGrid');
              const prevPageButton = document.getElementById('prevPage');
              const nextPageButton = document.getElementById('nextPage');
              const pageInfo = document.getElementById('pageInfo');
              const pageSizeSelect = document.getElementById('pageSize');
              if (!searchInput || !table || !prevPageButton || !nextPageButton || !pageInfo || !pageSizeSelect) return;

              const tbody = table.tBodies[0];
              const originalRows = Array.from(tbody.querySelectorAll('tr'));
              const headerCells = Array.from(table.querySelectorAll('thead th[data-sort]'));
              let filteredRows = originalRows.slice();
              let currentPage = 1;
              let pageSize = parseInt(pageSizeSelect.value, 10);
              let sortColumn = null;
              let sortAscending = true;

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

              function getCellValue(row, index, type) {
                const cell = row.cells[index];
                if (!cell) return '';
                const text = cell.textContent.trim();
                if (type === 'number') {
                  return parseFloat(text.replace(/\s+/g, '').replace(',', '.')) || 0;
                }
                return text.toLowerCase();
              }

              function sortRows(columnIndex, type) {
                sortAscending = sortColumn === columnIndex ? !sortAscending : true;
                sortColumn = columnIndex;
                filteredRows.sort(function (a, b) {
                  const aValue = getCellValue(a, columnIndex, type);
                  const bValue = getCellValue(b, columnIndex, type);
                  if (aValue < bValue) return sortAscending ? -1 : 1;
                  if (aValue > bValue) return sortAscending ? 1 : -1;
                  return 0;
                });
                currentPage = 1;
                renderTable();
              }

              function updateTableRows(rowsToDisplay) {
                tbody.innerHTML = '';
                if (rowsToDisplay.length === 0) {
                  tbody.appendChild(noDataRow);
                  return;
                }
                rowsToDisplay.forEach(function (row) {
                  tbody.appendChild(row);
                });
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
                const filter = searchInput.value.toLowerCase();
                filteredRows = originalRows.filter(function (row) {
                  if (row.classList.contains('no-data-row')) {
                    return false;
                  }
                  return row.textContent.toLowerCase().includes(filter);
                });
                if (sortColumn !== null) {
                  const header = headerCells.find(h => parseInt(h.dataset.sort, 10) === sortColumn);
                  if (header) {
                    sortRows(sortColumn, header.dataset.type);
                    return;
                  }
                }
                currentPage = 1;
                renderTable();
              }

              searchInput.addEventListener('input', function () {
                applySearch();
              });

              prevPageButton.addEventListener('click', function () {
                if (currentPage > 1) {
                  currentPage -= 1;
                  renderTable();
                }
              });

              nextPageButton.addEventListener('click', function () {
                const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
                if (currentPage < totalPages) {
                  currentPage += 1;
                  renderTable();
                }
              });

              pageSizeSelect.addEventListener('change', function () {
                pageSize = parseInt(this.value, 10);
                currentPage = 1;
                renderTable();
              });

              headerCells.forEach(function (header) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function () {
                  sortRows(parseInt(header.dataset.sort, 10), header.dataset.type);
                });
              });

              renderTable();
            });
          </script>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
