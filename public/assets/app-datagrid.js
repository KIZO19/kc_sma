(() => {
  const managedTableIds = new Set([
    'ecolesTable',
    'studentsDataGrid',
    'feesDataGrid',
    'paymentsDataGrid',
    'recouvrementsTable',
    'allUsersTable',
    'inactiveUsersTable',
    'unassignedUsersTable'
  ]);
  const managedTableClasses = ['student-grid', 'recouvrements-grid'];
  const collator = new Intl.Collator('fr', { numeric: true, sensitivity: 'base' });

  const normalize = (value) => String(value ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase('fr');

  const parseNumber = (value) => {
    let normalized = String(value ?? '')
      .replace(/\s/g, '')
      .replace(/[\u00a0\u202f]/g, '');
    normalized = normalized.includes(',')
      ? normalized.replace(/\./g, '').replace(',', '.')
      : normalized.replace(/,/g, '');
    normalized = normalized
      .replace(/[^\d.-]/g, '');
    const parsed = Number(normalized);
    return Number.isFinite(parsed) ? parsed : null;
  };

  const initTable = (table, index) => {
    if (
      managedTableIds.has(table.id)
      || managedTableClasses.some((className) => table.classList.contains(className))
      || table.dataset.datagrid === 'off'
      || table.dataset.datagridReady === 'true'
      || !table.tHead
      || !table.tBodies.length
      || table.closest('.dataTables_wrapper')
      || table.closest('.rec-fee-detail')
    ) return;

    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);
    if (!rows.length || rows.some((row) => row.cells.length === 1 && row.cells[0].hasAttribute('colspan'))) return;

    const headers = Array.from(table.tHead.rows[0]?.cells ?? []);
    if (!headers.length || headers.some((header) => header.colSpan > 1)) return;

    table.dataset.datagridReady = 'true';
    table.classList.add('app-data-grid');
    const wrapper = table.closest('.table-responsive') || table;
    const toolbar = document.createElement('div');
    toolbar.className = 'app-datagrid-toolbar';
    toolbar.setAttribute('role', 'search');
    toolbar.setAttribute('aria-label', 'Outils du tableau');

    const searchWrap = document.createElement('div');
    searchWrap.className = 'input-group input-group-sm app-grid-search';
    const searchIcon = document.createElement('span');
    searchIcon.className = 'input-group-text';
    searchIcon.innerHTML = '<i class="bi bi-search" aria-hidden="true"></i>';
    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'form-control';
    search.placeholder = 'Rechercher dans le tableau…';
    search.setAttribute('aria-label', 'Rechercher dans ce tableau');
    searchWrap.append(searchIcon, search);

    const sizeLabel = document.createElement('label');
    sizeLabel.className = 'visually-hidden';
    sizeLabel.htmlFor = `app-grid-size-${index}`;
    sizeLabel.textContent = 'Lignes par page';
    const size = document.createElement('select');
    size.id = `app-grid-size-${index}`;
    size.className = 'form-select form-select-sm app-grid-size';
    size.setAttribute('aria-label', 'Lignes par page');
    [['10', '10 / page'], ['25', '25 / page'], ['50', '50 / page'], ['100', '100 / page'], ['all', 'Tout']]
      .forEach(([value, label]) => size.add(new Option(label, value)));
    size.value = '25';

    const info = document.createElement('span');
    info.className = 'app-grid-info';
    info.setAttribute('aria-live', 'polite');

    const pagination = document.createElement('div');
    pagination.className = 'btn-group btn-group-sm app-grid-pagination';
    pagination.setAttribute('role', 'group');
    pagination.setAttribute('aria-label', 'Pagination du tableau');
    const previous = document.createElement('button');
    previous.className = 'btn btn-outline-secondary';
    previous.type = 'button';
    previous.title = 'Page précédente';
    previous.setAttribute('aria-label', 'Page précédente');
    previous.innerHTML = '<i class="bi bi-chevron-left" aria-hidden="true"></i>';
    const next = document.createElement('button');
    next.className = 'btn btn-outline-secondary';
    next.type = 'button';
    next.title = 'Page suivante';
    next.setAttribute('aria-label', 'Page suivante');
    next.innerHTML = '<i class="bi bi-chevron-right" aria-hidden="true"></i>';
    pagination.append(previous, next);
    toolbar.append(searchWrap, sizeLabel, size, info, pagination);
    wrapper.parentNode.insertBefore(toolbar, wrapper);

    const originalPositions = new Map(rows.map((row, rowIndex) => [row, rowIndex]));
    let page = 1;
    let sortColumn = -1;
    let sortDirection = 1;

    headers.forEach((header, columnIndex) => {
      const label = header.textContent.trim();
      if (!label || /^(actions?|décision|options?)$/i.test(label) || header.dataset.gridSort === 'off') return;
      header.dataset.gridSort = 'on';
      header.setAttribute('aria-sort', 'none');
      const icon = document.createElement('i');
      icon.className = 'bi bi-arrow-down-up app-grid-sort-icon';
      icon.setAttribute('aria-hidden', 'true');
      header.appendChild(icon);
      header.addEventListener('click', () => {
        if (sortColumn === columnIndex) {
          sortDirection *= -1;
        } else {
          sortColumn = columnIndex;
          sortDirection = 1;
        }
        headers.forEach((item, itemIndex) => {
          if (item.dataset.gridSort !== 'on') return;
          item.setAttribute('aria-sort', itemIndex === sortColumn
            ? (sortDirection === 1 ? 'ascending' : 'descending')
            : 'none');
        });
        page = 1;
        render();
      });
    });

    const getCellText = (row, columnIndex) => row.cells[columnIndex]?.textContent?.trim() ?? '';
    const render = () => {
      const query = normalize(search.value.trim());
      const filtered = rows.filter((row) => normalize(row.textContent).includes(query));
      if (sortColumn >= 0) {
        filtered.sort((left, right) => {
          const leftText = getCellText(left, sortColumn);
          const rightText = getCellText(right, sortColumn);
          const type = headers[sortColumn]?.dataset.gridSortType || '';
          if (type === 'number' || (!type && parseNumber(leftText) !== null && parseNumber(rightText) !== null)) {
            const difference = (parseNumber(leftText) ?? 0) - (parseNumber(rightText) ?? 0);
            if (difference) return difference * sortDirection;
          } else if (type === 'date') {
            const difference = Date.parse(leftText) - Date.parse(rightText);
            if (Number.isFinite(difference) && difference) return difference * sortDirection;
          }
          return collator.compare(leftText, rightText) * sortDirection
            || originalPositions.get(left) - originalPositions.get(right);
        });
      } else {
        filtered.sort((left, right) => originalPositions.get(left) - originalPositions.get(right));
      }

      const pageSize = size.value === 'all' ? Math.max(1, filtered.length) : Number(size.value) || 25;
      const pageCount = Math.max(1, Math.ceil(filtered.length / pageSize));
      page = Math.min(page, pageCount);
      const start = filtered.length ? (page - 1) * pageSize : 0;
      const visibleRows = new Set(filtered.slice(start, start + pageSize));
      const filteredSet = new Set(filtered);
      filtered.forEach((row) => tbody.appendChild(row));
      rows.forEach((row) => {
        if (!filteredSet.has(row)) tbody.appendChild(row);
        row.hidden = !visibleRows.has(row);
      });

      info.textContent = filtered.length
        ? `${start + 1}–${Math.min(start + pageSize, filtered.length)} sur ${filtered.length}`
        : 'Aucun résultat';
      previous.disabled = page <= 1;
      next.disabled = page >= pageCount;
    };

    search.addEventListener('input', () => { page = 1; render(); });
    size.addEventListener('change', () => { page = 1; render(); });
    previous.addEventListener('click', () => { page -= 1; render(); });
    next.addEventListener('click', () => { page += 1; render(); });
    render();
  };

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table.table').forEach(initTable);
  });
})();
