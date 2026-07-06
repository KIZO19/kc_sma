<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Élèves</h1>
              <p class="text-muted">Liste des élèves enregistrés dans votre école.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Élèves</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (empty($students)): ?>
            <div class="alert alert-info">Aucun élève disponible pour le périmètre de cette école.</div>
          <?php else: ?>
            <div class="card card-outline card-primary">
              <div class="card-header">
                <div class="row w-100 gy-2">
                  <div class="col-md-12 col-sm-12">
                    <h3 class="card-title">Élèves inscrits</h3>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="row align-items-center g-3 mb-3">
                  <div class="col-md-5 col-sm-12">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-search"></i></span>
                      <input id="studentSearch" type="search" class="form-control" placeholder="Rechercher un élève..." aria-label="Recherche élève">
                    </div>
                  </div>
                  <div class="col-md-3 col-sm-12">
                    <select id="statusFilter" class="form-select">
                      <option value="">Tous les statuts</option>
                      <option value="actif">Actif</option>
                      <option value="inactif">En attente</option>
                    </select>
                  </div>
                  <div class="col-md-2 col-sm-12">
                    <select id="pageSize" class="form-select">
                      <option value="5">5 / page</option>
                      <option value="10" selected>10 / page</option>
                      <option value="20">20 / page</option>
                      <option value="50">50 / page</option>
                    </select>
                  </div>
                  <div class="col-md-2 col-sm-12 text-md-end">
                    <span class="text-muted" id="studentCount"><?= count($students) ?> élèves</span>
                  </div>
                </div>
                <div class="table-responsive">
                  <table id="studentsDataGrid" class="table table-sm table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                      <tr>
                        <th data-sort="number" style="width: 50px;">#</th>
                        <th data-sort="string">Matricule</th>
                        <th data-sort="string">Nom</th>
                        <th data-sort="string">Postnom</th>
                        <th data-sort="string">Prénom</th>
                        <th data-sort="string">Date de naissance</th>
                        <th data-sort="string">Parent</th>
                        <th data-sort="string">Statut</th>
                        <th style="width:180px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students as $index => $student): ?>
                        <tr data-status="<?= htmlspecialchars($student['statut_eleve'] ?? '') ?>">
                          <td><?= $index + 1 ?></td>
                          <td><?= htmlspecialchars($student['matricule'] ?? 'N/A') ?></td>
                          <td><?= htmlspecialchars($student['nom'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($student['postnom'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($student['prenom'] ?? '-') ?></td>
                          <td><?= htmlspecialchars(formatDate($student['date_naissance'] ?? null)) ?></td>
                          <td><?= htmlspecialchars($student['parent_nom_responsable'] ?? '-') ?></td>
                          <td>
                            <?php if (($student['statut_eleve'] ?? '') === 'actif'): ?>
                              <span class="badge bg-success">Actif</span>
                            <?php elseif (($student['statut_eleve'] ?? '') === 'inactif'): ?>
                              <span class="badge bg-warning">En attente</span>
                            <?php else: ?>
                              <span class="badge bg-secondary"><?= htmlspecialchars($student['statut_eleve'] ?? 'N/A') ?></span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) $student['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                              <?php if (in_array($role, ['super_admin', 'ecole_admin', 'comptable_école'], true)): ?>
                                <a href="<?= BASE_URL ?>/paiements?eleve_id=<?= (int) $student['id'] ?>" class="btn btn-sm btn-outline-success">Paiements</a>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                  <div>
                    <button id="prevPage" class="btn btn-sm btn-outline-secondary me-2" type="button">Précédent</button>
                    <button id="nextPage" class="btn btn-sm btn-outline-secondary" type="button">Suivant</button>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <span id="pageInfo">Page 1 / 1</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('studentSearch');
        const statusFilter = document.getElementById('statusFilter');
        const table = document.getElementById('studentsDataGrid');
        const prevPageButton = document.getElementById('prevPage');
        const nextPageButton = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        const pageSizeSelect = document.getElementById('pageSize');
        const studentCount = document.getElementById('studentCount');
        if (!table || !prevPageButton || !nextPageButton || !pageInfo || !pageSizeSelect) return;

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
          cell.colSpan = 9;
          cell.className = 'text-center py-4';
          cell.textContent = 'Aucun élève trouvé pour ces critères.';
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
          const start = (currentPage - 1) * pageSize;
          rowsToDisplay.forEach(function (row, rowIndex) {
            const firstCell = row.cells[0];
            if (firstCell) {
              firstCell.textContent = start + rowIndex + 1;
            }
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

        function applyFilters() {
          const query = (searchInput?.value || '').toLowerCase();
          const status = (statusFilter?.value || '').toLowerCase();
          filteredRows = originalRows.filter(function (row) {
            if (row.classList.contains('no-data-row')) return false;
            const textMatch = !query || row.textContent.toLowerCase().includes(query);
            const statusMatch = !status || (row.dataset.status || '').toLowerCase() === status;
            return textMatch && statusMatch;
          });
          if (sortColumn !== null) {
            const sortedHeader = headerCells.find(h => parseInt(h.dataset.sort, 10) === sortColumn);
            if (sortedHeader) {
              sortRows(sortColumn, sortedHeader.dataset.type);
              return;
            }
          }
          currentPage = 1;
          renderTable();
          studentCount.textContent = `${filteredRows.length} élève(s)`;
        }

        headerCells.forEach(function (header, index) {
          header.style.cursor = 'pointer';
          header.addEventListener('click', function () {
            sortRows(index, header.dataset.type);
          });
        });

        if (searchInput) {
          searchInput.addEventListener('input', applyFilters);
        }
        if (statusFilter) {
          statusFilter.addEventListener('change', applyFilters);
        }

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

        renderTable();
      });
    </script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>