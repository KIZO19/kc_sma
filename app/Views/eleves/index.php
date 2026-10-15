<?php require __DIR__ . '/../partials/app_header.php'; ?>
<style>
  .student-kpi .card-body { padding: 1rem 1.25rem; }
  .student-kpi strong { font-size: 1.35rem; }
  .student-kpi-icon { display: inline-grid; width: 2.75rem; height: 2.75rem; place-items: center; border-radius: .85rem; font-size: 1.25rem; }
  .student-grid { min-width: 1050px; }
  .student-grid thead th { white-space: nowrap; background: var(--surface-muted); color: var(--text-muted); font-size: .76rem; text-transform: uppercase; letter-spacing: .035em; }
  .student-grid th[data-sort-type] { cursor: pointer; user-select: none; }
  .student-grid th[aria-sort="ascending"]::after { content: " ▲"; }
  .student-grid th[aria-sort="descending"]::after { content: " ▼"; }
  .student-grid .student-name { color: var(--text); font-weight: 600; text-decoration: none; }
  .student-grid .student-name:hover { color: var(--sidebar-active); }
</style>
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
          <?php
          $e = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
          $sectionOptions = [];
          $optionOptions = [];
          foreach ($classes as $class) {
              if (!empty($class['section_id'])) {
                  $sectionOptions[(int) $class['section_id']] = (string) ($class['nom_section'] ?? 'Section');
              }
              if (!empty($class['option_id'])) {
                  $optionOptions[(int) $class['option_id']] = (string) ($class['nom_option'] ?? 'Option');
              }
          }
          asort($sectionOptions, SORT_NATURAL | SORT_FLAG_CASE);
          asort($optionOptions, SORT_NATURAL | SORT_FLAG_CASE);
          $totalStudents = count($students);
          $activeStudents = count(array_filter($students, static fn (array $student): bool => ($student['statut_eleve'] ?? '') === 'actif'));
          $pendingStudents = count(array_filter($students, static fn (array $student): bool => ($student['statut_eleve'] ?? '') === 'inactif'));
          ?>
          <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-4">
              <div class="card student-kpi h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="student-kpi-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></span>
                <div><div class="text-muted small">Résultats filtrés</div><strong id="studentTotal"><?= $totalStudents ?></strong></div>
              </div></div>
            </div>
            <div class="col-sm-6 col-xl-4">
              <div class="card student-kpi h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="student-kpi-icon bg-success-subtle text-success"><i class="bi bi-person-check"></i></span>
                <div><div class="text-muted small">Actifs</div><strong id="activeTotal"><?= $activeStudents ?></strong></div>
              </div></div>
            </div>
            <div class="col-sm-6 col-xl-4">
              <div class="card student-kpi h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="student-kpi-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></span>
                <div><div class="text-muted small">En attente</div><strong id="pendingTotal"><?= $pendingStudents ?></strong></div>
              </div></div>
            </div>
          </div>
          <div class="card card-outline card-primary">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <h3 class="card-title mb-1">Répertoire des élèves</h3>
                <div class="small text-muted">Recherchez, filtrez et triez les informations d’inscription.</div>
              </div>
              <button id="exportStudents" class="btn btn-sm btn-outline-primary" type="button">
                <i class="bi bi-download me-1"></i>Exporter la sélection (CSV)
              </button>
            </div>
            <div class="card-body">
              <div class="row g-2 mb-3">
                <div class="col-12 col-lg-4">
                  <label class="form-label small mb-1" for="studentSearch">Recherche générale</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input id="studentSearch" type="search" class="form-control" placeholder="Nom, matricule, parent, téléphone…" aria-label="Recherche élève">
                  </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                  <label class="form-label small mb-1" for="sectionFilter">Section</label>
                  <select id="sectionFilter" class="form-select">
                    <option value="">Toutes les sections</option>
                    <?php foreach ($sectionOptions as $id => $name): ?>
                      <option value="<?= (int) $id ?>"><?= $e($name) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                  <label class="form-label small mb-1" for="optionFilter">Option</label>
                  <select id="optionFilter" class="form-select">
                    <option value="">Toutes les options</option>
                    <?php foreach ($optionOptions as $id => $name): ?>
                      <option value="<?= (int) $id ?>"><?= $e($name) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                  <label class="form-label small mb-1" for="classFilter">Classe</label>
                  <select id="classFilter" class="form-select">
                    <option value="">Toutes les classes</option>
                    <?php foreach ($classes as $class): ?>
                      <option value="<?= (int) $class['id'] ?>" <?= ((int) ($selectedClasseId ?? 0) === (int) $class['id']) ? 'selected' : '' ?>>
                        <?= $e($class['nom_classe'] ?? '') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                  <label class="form-label small mb-1" for="genderFilter">Genre</label>
                  <select id="genderFilter" class="form-select">
                    <option value="">Tous les genres</option>
                    <option value="masculin">Masculin</option>
                    <option value="féminin">Féminin</option>
                    <option value="autre">Autre</option>
                  </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                  <label class="form-label small mb-1" for="statusFilter">Statut</label>
                  <select id="statusFilter" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="actif">Actif</option>
                    <option value="inactif">En attente</option>
                  </select>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                  <label class="form-label small mb-1" for="pageSize">Lignes par page</label>
                  <select id="pageSize" class="form-select">
                    <option value="10" selected>10 lignes</option>
                    <option value="25">25 lignes</option>
                    <option value="50">50 lignes</option>
                    <option value="100">100 lignes</option>
                  </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2 d-flex align-items-end">
                  <button id="resetFilters" class="btn btn-light border w-100" type="button">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
                  </button>
                </div>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <span class="small text-muted" id="studentCount" aria-live="polite"></span>
                <span class="small text-muted">Astuce : cliquez sur un en-tête pour trier.</span>
              </div>
              <div class="table-responsive">
                <table id="studentsDataGrid" class="table table-hover align-middle student-grid mb-0">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col" data-sort-type="string">Matricule</th>
                      <th scope="col" data-sort-type="string">Élève</th>
                      <th scope="col" data-sort-type="string">Genre</th>
                      <th scope="col" data-sort-type="date">Naissance</th>
                      <th scope="col" data-sort-type="string">Section</th>
                      <th scope="col" data-sort-type="string">Option</th>
                      <th scope="col" data-sort-type="string">Classe</th>
                      <th scope="col" data-sort-type="string">Parent / responsable</th>
                      <th scope="col" data-sort-type="string">Téléphone parent</th>
                      <th scope="col" data-sort-type="string">Statut</th>
                      <th scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($students as $student): ?>
                      <?php
                      $status = (string) ($student['statut_eleve'] ?? '');
                      $gender = (string) ($student['genre'] ?? '');
                      $fullName = trim(implode(' ', array_filter([
                          $student['nom'] ?? '',
                          $student['postnom'] ?? '',
                          $student['prenom'] ?? '',
                      ])));
                      ?>
                      <tr
                        data-status="<?= $e(strtolower($status)) ?>"
                        data-gender="<?= $e(strtolower($gender)) ?>"
                        data-section="<?= (int) ($student['section_id'] ?? 0) ?>"
                        data-option="<?= (int) ($student['option_id'] ?? 0) ?>"
                        data-class="<?= (int) ($student['classe_id'] ?? 0) ?>">
                        <td class="row-number"></td>
                        <td data-sort-value="<?= $e($student['matricule'] ?? '') ?>"><?= $e($student['matricule'] ?? '—') ?></td>
                        <td data-sort-value="<?= $e($fullName) ?>">
                          <a class="student-name" href="<?= BASE_URL ?>/eleves/show?id=<?= (int) $student['id'] ?>"><?= $e($fullName ?: '—') ?></a>
                          <div class="small text-muted">ID <?= (int) $student['id'] ?></div>
                        </td>
                        <td><?= $e($gender ?: '—') ?></td>
                        <td data-sort-value="<?= $e($student['date_naissance'] ?? '') ?>"><?= $e(formatDate($student['date_naissance'] ?? null)) ?></td>
                        <td data-sort-value="<?= $e($student['nom_section'] ?? '') ?>"><?= $e($student['nom_section'] ?? '—') ?></td>
                        <td data-sort-value="<?= $e($student['nom_option'] ?? '') ?>"><?= $e($student['nom_option'] ?? '—') ?></td>
                        <td data-sort-value="<?= $e($student['nom_classe'] ?? '') ?>"><?= $e($student['nom_classe'] ?? '—') ?></td>
                        <td>
                          <?php if (!empty($student['parent_id']) && !empty($student['parent_nom_responsable'])): ?>
                            <a href="<?= BASE_URL ?>/parents/show?id=<?= (int) $student['parent_id'] ?>"><?= $e($student['parent_nom_responsable']) ?></a>
                          <?php else: ?>
                            —
                          <?php endif; ?>
                        </td>
                        <td data-sort-value="<?= $e($student['parent_telephone'] ?? '') ?>"><?= $e($student['parent_telephone'] ?? '—') ?></td>
                        <td>
                          <?php if ($status === 'actif'): ?>
                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis">Actif</span>
                          <?php elseif ($status === 'inactif'): ?>
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">En attente</span>
                          <?php else: ?>
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis"><?= $e($status ?: 'Inconnu') ?></span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm" role="group" aria-label="Actions pour <?= $e($fullName) ?>">
                            <a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) $student['id'] ?>" class="btn btn-outline-primary" title="Consulter la fiche" aria-label="Consulter la fiche"><i class="bi bi-eye"></i></a>
                            <?php if (in_array($role, ['super_admin', 'ecole_admin', 'comptable_école'], true)): ?>
                              <a href="<?= BASE_URL ?>/paiements?eleve_id=<?= (int) $student['id'] ?>" class="btn btn-outline-success" title="Consulter les paiements" aria-label="Consulter les paiements"><i class="bi bi-wallet2"></i></a>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3">
                <span class="small text-muted" id="pageInfo"></span>
                <div class="btn-group btn-group-sm" role="group" aria-label="Pagination">
                  <button id="prevPage" class="btn btn-outline-secondary" type="button"><i class="bi bi-chevron-left me-1"></i>Précédent</button>
                  <button id="nextPage" class="btn btn-outline-secondary" type="button">Suivant<i class="bi bi-chevron-right ms-1"></i></button>
                </div>
              </div>
            </div>
          </div>
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
        const headerCells = Array.from(table.querySelectorAll('thead th[data-sort-type]'));
        const sectionFilter = document.getElementById('sectionFilter');
        const optionFilter = document.getElementById('optionFilter');
        const classFilter = document.getElementById('classFilter');
        const genderFilter = document.getElementById('genderFilter');
        const resetFiltersButton = document.getElementById('resetFilters');
        const exportButton = document.getElementById('exportStudents');
        const studentTotal = document.getElementById('studentTotal');
        const activeTotal = document.getElementById('activeTotal');
        const pendingTotal = document.getElementById('pendingTotal');
        let filteredRows = originalRows.slice();
        let currentPage = 1;
        let pageSize = parseInt(pageSizeSelect.value, 10);
        let sortColumn = null;
        let sortAscending = true;

        function createNoDataRow() {
          const row = document.createElement('tr');
          row.className = 'no-data-row';
          const cell = document.createElement('td');
          cell.colSpan = 12;
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
          const value = cell.dataset.sortValue || text;
          if (type === 'date') {
            return Date.parse(value) || 0;
          }
          if (type === 'number') {
            return parseFloat(value.replace(/\s+/g, '').replace(',', '.')) || 0;
          }
          return value.toLocaleLowerCase('fr');
        }

        function sortRows(columnIndex, type, toggleDirection = true) {
          if (sortColumn === columnIndex && toggleDirection) {
            sortAscending = !sortAscending;
          } else if (sortColumn !== columnIndex) {
            sortAscending = true;
          }
          sortColumn = columnIndex;
          filteredRows.sort(function (a, b) {
            const aValue = getCellValue(a, columnIndex, type);
            const bValue = getCellValue(b, columnIndex, type);
            if (aValue < bValue) return sortAscending ? -1 : 1;
            if (aValue > bValue) return sortAscending ? 1 : -1;
            return 0;
          });
          headerCells.forEach(function (header) {
            header.removeAttribute('aria-sort');
          });
          const activeHeader = headerCells.find(function (header) {
            return parseInt(header.dataset.columnIndex || '-1', 10) === columnIndex;
          });
          if (activeHeader) {
            activeHeader.setAttribute('aria-sort', sortAscending ? 'ascending' : 'descending');
          }
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
            const numberCell = row.querySelector('.row-number');
            if (numberCell) {
              numberCell.textContent = start + rowIndex + 1;
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
          prevPageButton.disabled = currentPage <= 1;
          nextPageButton.disabled = currentPage >= totalPages;
          const firstResult = filteredRows.length === 0 ? 0 : start + 1;
          const lastResult = Math.min(end, filteredRows.length);
          pageInfo.textContent = `${firstResult}–${lastResult} sur ${filteredRows.length} résultat(s) · Page ${currentPage} / ${totalPages}`;
        }

        function applyFilters() {
          const query = (searchInput?.value || '').trim().toLocaleLowerCase('fr');
          const status = (statusFilter?.value || '').toLocaleLowerCase('fr');
          const section = sectionFilter?.value || '';
          const option = optionFilter?.value || '';
          const selectedClass = classFilter?.value || '';
          const gender = genderFilter?.value || '';
          filteredRows = originalRows.filter(function (row) {
            if (row.classList.contains('no-data-row')) return false;
            const textMatch = !query || row.textContent.toLocaleLowerCase('fr').includes(query);
            const statusMatch = !status || (row.dataset.status || '').toLowerCase() === status;
            const sectionMatch = !section || row.dataset.section === section;
            const optionMatch = !option || row.dataset.option === option;
            const classMatch = !selectedClass || row.dataset.class === selectedClass;
            const genderMatch = !gender || (row.dataset.gender || '').toLocaleLowerCase('fr') === gender;
            return textMatch && statusMatch && sectionMatch && optionMatch && classMatch && genderMatch;
          });
          if (studentTotal) studentTotal.textContent = filteredRows.length;
          if (activeTotal) activeTotal.textContent = filteredRows.filter(row => row.dataset.status === 'actif').length;
          if (pendingTotal) pendingTotal.textContent = filteredRows.filter(row => row.dataset.status === 'inactif').length;
          if (sortColumn !== null) {
            const sortedHeader = headerCells.find(h => parseInt(h.dataset.columnIndex || '-1', 10) === sortColumn);
            if (sortedHeader) {
              sortRows(sortColumn, sortedHeader.dataset.sortType, false);
              return;
            }
          }
          currentPage = 1;
          renderTable();
          if (studentCount) studentCount.textContent = `${filteredRows.length} élève(s) correspondent aux filtres`;
        }

        headerCells.forEach(function (header, index) {
          header.dataset.columnIndex = String(index + 1);
          header.addEventListener('click', function () {
            sortRows(index + 1, header.dataset.sortType);
          });
        });

        if (searchInput) {
          searchInput.addEventListener('input', applyFilters);
        }
        if (statusFilter) {
          statusFilter.addEventListener('change', applyFilters);
        }
        [sectionFilter, optionFilter, classFilter, genderFilter].forEach(function (filter) {
          if (filter) filter.addEventListener('change', applyFilters);
        });
        if (resetFiltersButton) {
          resetFiltersButton.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            [statusFilter, sectionFilter, optionFilter, classFilter, genderFilter].forEach(function (filter) {
              if (filter) filter.value = '';
            });
            applyFilters();
          });
        }
        if (exportButton) {
          exportButton.addEventListener('click', function () {
            const headers = Array.from(table.tHead.rows[0].cells).slice(1, -1).map(cell => cell.textContent.trim());
            const rows = filteredRows.map(function (row) {
              return Array.from(row.cells).slice(1, -1).map(function (cell) {
                let value = (cell.dataset.sortValue || cell.textContent).trim().replace(/\s+/g, ' ');
                if (/^[=+\-@\t\r]/.test(value)) value = "'" + value;
                return '"' + value.replace(/"/g, '""') + '"';
              });
            });
            const csv = '\uFEFF' + [headers, ...rows].map(row => row.join(';')).join('\r\n');
            const link = document.createElement('a');
            link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
            link.download = 'eleves-filtrees.csv';
            link.click();
            setTimeout(function () {
              URL.revokeObjectURL(link.href);
            }, 0);
          });
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
            applyFilters();
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