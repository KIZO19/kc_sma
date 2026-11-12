<?php
$pendingCount = count($pendingStudents);
$enrolledCount = count($enrolledStudents);
$totalStudentsCount = $pendingCount + $enrolledCount;
$canManageAccountAccess = $canManageAccountAccess ?? false;
$accountAccessCsrf = $accountAccessCsrf ?? '';
$pageStyles = <<<'CSS'
<style>
  .inscriptions-page .enrolled-grid-card { overflow: hidden; border: 1px solid var(--border, #dee2e6); border-radius: .85rem; background: var(--surface, #fff); box-shadow: 0 .35rem 1.2rem rgba(31, 41, 55, .06); }
  .inscriptions-page .enrolled-grid-card > .card-header { padding: 1rem 1.25rem; background: var(--surface, #fff); border-bottom: 1px solid var(--border, #dee2e6); }
  .inscriptions-page .enrolled-grid-card > .card-body { padding: 1.1rem 1.25rem; }
  .inscriptions-page .enrolled-grid-title { display: flex; align-items: center; gap: .65rem; margin: 0; font-size: 1rem; font-weight: 650; }
  .inscriptions-page .enrolled-grid-title i { color: #3c8dbc; }
  .inscriptions-page .enrolled-grid-toolbar,
  .inscriptions-page .enrolled-grid-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem 1rem; }
  .inscriptions-page .enrolled-grid-toolbar { margin-bottom: .85rem; }
  .inscriptions-page .enrolled-grid-table { max-width: 100%; overflow-x: auto; overflow-y: hidden; border: 1px solid var(--border, #dee2e6); border-radius: .65rem; -webkit-overflow-scrolling: touch; }
  .inscriptions-page table.enrolled-students-table { min-width: 900px; margin: 0 !important; border-collapse: separate; border-spacing: 0; }
  .inscriptions-page .enrolled-students-table thead th { padding: .85rem .75rem; border-bottom: 1px solid var(--border, #dee2e6); background: var(--surface-muted, #f4f6f9); color: var(--text-muted, #6c757d); font-size: .72rem; font-weight: 700; letter-spacing: .045em; text-transform: uppercase; white-space: nowrap; }
  .inscriptions-page .enrolled-students-table tbody td { padding: .8rem .75rem; border-bottom: 1px solid var(--border, #dee2e6); vertical-align: middle; white-space: nowrap; }
  .inscriptions-page .enrolled-students-table tbody tr:last-child td { border-bottom: 0; }
  .inscriptions-page .enrolled-students-table tbody tr:hover { background: rgba(60, 141, 188, .045); }
  .inscriptions-page .enrolled-students-table .student-name { color: var(--text, #212529); font-weight: 600; }
  .inscriptions-page .enrolled-students-table .student-reference { color: var(--text-muted, #6c757d); font-size: .82rem; }
  .inscriptions-page .enrolled-students-table .student-account-actions { min-width: 190px; }
  .inscriptions-page .enrolled-students-table .student-account-actions form { display: inline-block; margin: .15rem; }
  .inscriptions-page .enrolled-grid-toolbar .dt-buttons { display: flex; flex-wrap: wrap; gap: .4rem; }
  .inscriptions-page .enrolled-grid-toolbar .dt-button { margin: 0; padding: .45rem .75rem; border: 1px solid #c9ddea; border-radius: .45rem; background: var(--surface, #fff); color: #367fa9; font-size: .8125rem; }
  .inscriptions-page .enrolled-grid-toolbar .dt-button:hover { border-color: #3c8dbc; background: rgba(60, 141, 188, .08); color: #2c698e; }
  .inscriptions-page .enrolled-grid-toolbar .dataTables_filter label,
  .inscriptions-page .enrolled-grid-footer .dataTables_length label { display: inline-flex; align-items: center; gap: .5rem; margin: 0; color: var(--text-muted, #6c757d); font-size: .875rem; }
  .inscriptions-page .enrolled-grid-toolbar .dataTables_filter input,
  .inscriptions-page .enrolled-grid-footer .dataTables_length select { min-height: 2.25rem; padding: .35rem .65rem; border: 1px solid var(--border, #dee2e6); border-radius: .4rem; background: var(--surface, #fff); color: var(--text, #212529); }
  .inscriptions-page .enrolled-grid-toolbar .dataTables_filter input { min-width: min(17rem, 50vw); margin-left: 0 !important; }
  .inscriptions-page .enrolled-grid-footer { margin-top: .9rem; color: var(--text-muted, #6c757d); font-size: .8125rem; }
  .inscriptions-page .enrolled-grid-footer .paginate_button { margin: 0 .1rem; padding: .35rem .65rem !important; border: 1px solid transparent !important; border-radius: .4rem; }
  .inscriptions-page .enrolled-grid-footer .paginate_button.current { border-color: #c9ddea !important; background: #eaf3f8 !important; color: #2c698e !important; }
  .inscriptions-page .enrolled-student-filters { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; margin-bottom: .9rem; }
  .inscriptions-page .enrolled-student-filters label { display: grid; gap: .3rem; color: var(--text-muted, #6c757d); font-size: .78rem; font-weight: 600; }
  .inscriptions-page .enrolled-student-filters select { min-width: 0; min-height: 2.4rem; padding: .4rem .65rem; border: 1px solid var(--border, #dee2e6); border-radius: .45rem; background: var(--surface, #fff); color: var(--text, #212529); }
  .inscriptions-page .enrolled-grid-toolbar .dataTables_processing { background: var(--surface, #fff); color: var(--text, #212529); }
  [data-theme="dark"] .inscriptions-page .enrolled-grid-card { border-color: rgba(255,255,255,.12); }
  [data-theme="dark"] .inscriptions-page .enrolled-grid-table { border-color: rgba(255,255,255,.12); }
  [data-theme="dark"] .inscriptions-page .enrolled-students-table thead th { background: var(--surface-muted, #172232); }
  [data-theme="dark"] .inscriptions-page .enrolled-students-table tbody tr:hover { background: rgba(96, 165, 250, .08); }
  [data-theme="dark"] .inscriptions-page .enrolled-grid-toolbar .dt-button { border-color: rgba(255,255,255,.16); }
  [data-theme="dark"] .inscriptions-page .enrolled-grid-footer .paginate_button.current { border-color: #31547e !important; background: #172a43 !important; color: #bfdbfe !important; }
  @media (max-width: 767.98px) {
    .inscriptions-page .enrolled-grid-card > .card-body { padding: .75rem; }
    .inscriptions-page .enrolled-grid-toolbar,
    .inscriptions-page .enrolled-grid-footer { align-items: stretch; }
    .inscriptions-page .enrolled-grid-toolbar > *,
    .inscriptions-page .enrolled-grid-footer > * { width: 100%; }
    .inscriptions-page .enrolled-grid-toolbar .dt-buttons { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .inscriptions-page .enrolled-grid-toolbar .dt-button { text-align: center; }
    .inscriptions-page .enrolled-grid-toolbar .dataTables_filter label { display: grid; }
    .inscriptions-page .enrolled-grid-toolbar .dataTables_filter input { width: 100%; min-width: 0; }
    .inscriptions-page .enrolled-student-filters { grid-template-columns: 1fr; }
    .inscriptions-page .enrolled-grid-footer .dataTables_paginate { align-self: center; }
  }
  @media (min-width: 768px) and (max-width: 991.98px) {
    .inscriptions-page .enrolled-student-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  .inscriptions-page .content-header h1 { font-weight: 700; letter-spacing: -.03em; }
  .inscriptions-page .registration-summary { border: 0; border-radius: .85rem; box-shadow: 0 .25rem 1rem rgba(31, 41, 55, .06); }
  .inscriptions-page .registration-summary .inner { padding: 1.15rem 1.25rem; }
  .inscriptions-page .registration-summary h3 { font-size: 1.85rem; font-weight: 700; }
  .inscriptions-page .registration-summary p { margin-bottom: 0; opacity: .9; }
  .inscriptions-page .registration-summary .icon { top: .65rem; right: 1rem; font-size: 2.5rem; opacity: .2; }
  .inscriptions-page .registration-card { border: 0; border-radius: .85rem; box-shadow: 0 .25rem 1rem rgba(31, 41, 55, .06); }
  .inscriptions-page .registration-card > .card-header { padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid rgba(0, 0, 0, .07); }
  .inscriptions-page .registration-grid-title { display: flex; align-items: center; gap: .65rem; margin: 0; font-size: 1rem; font-weight: 650; }
  .inscriptions-page .registration-grid-title i { color: #2563eb; }
  .inscriptions-page .registration-grid .table { margin-bottom: 0; }
  .inscriptions-page .registration-grid .table thead th { padding: .85rem .75rem; color: #64748b; background: #f8fafc; border-bottom-width: 1px; font-size: .73rem; font-weight: 700; letter-spacing: .045em; text-transform: uppercase; }
  .inscriptions-page .registration-grid .table tbody td { padding: .8rem .75rem; vertical-align: middle; }
  .inscriptions-page .student-name { color: #1e293b; font-weight: 600; }
  .inscriptions-page .student-reference { color: #64748b; font-size: .82rem; }
  .inscriptions-page .registration-actions { display: flex; align-items: center; gap: .4rem; white-space: nowrap; }
  .inscriptions-page .registration-actions form { margin: 0; }
  .inscriptions-page .registration-grid .app-datagrid-toolbar { border: 0; border-bottom: 1px solid rgba(0, 0, 0, .07); }
  .inscriptions-page .registration-export { white-space: nowrap; }
  [data-theme="dark"] .inscriptions-page .registration-card > .card-header { background: var(--surface, #1e293b); border-color: rgba(255, 255, 255, .12); }
  [data-theme="dark"] .inscriptions-page .registration-grid .table thead th { color: #cbd5e1; background: #172232; }
  [data-theme="dark"] .inscriptions-page .student-name { color: #e2e8f0; }
  @media print {
    .inscriptions-page .content-header .btn, .inscriptions-page .registration-export,
    .inscriptions-page .app-datagrid-toolbar, .inscriptions-page .registration-actions { display: none !important; }
    .inscriptions-page .registration-card { box-shadow: none; }
  }
</style>
CSS;
$pageStyles = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">'
    . '<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">'
    . $pageStyles;
$pageScripts = <<<'SCRIPT'
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js" crossorigin="anonymous"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('#enrolledStudentsGrid');
    if (!table || !window.jQuery || !jQuery.fn.DataTable) return;

    const headerCells = Array.from(table.querySelectorAll('thead th'));
    const columnIndex = function (label) {
      return headerCells.findIndex(function (header) {
        return header.textContent.trim() === label;
      });
    };
    const actionsColumn = headerCells.findIndex(function (header) {
      return header.classList.contains('student-account-actions-column');
    });
    const detailColumns = headerCells.reduce(function (columns, header, index) {
      if (header.hasAttribute('data-enrolled-detail')) columns.push(index);
      return columns;
    }, []);
    const dateColumn = columnIndex('Date de naissance');
    const exportColumns = [];
    for (let index = 1; index < headerCells.length; index += 1) {
      if (index !== actionsColumn) exportColumns.push(index);
    }
    const exportOptions = {
      columns: exportColumns,
      modifier: { search: 'applied', order: 'applied', page: 'all' }
    };
    const columnDefs = [
      { targets: [0], orderable: false, searchable: false, className: 'text-muted' },
      { targets: [dateColumn], type: 'date' }
    ];
    if (detailColumns.length) {
      columnDefs.push({ targets: detailColumns, visible: false });
    }
    const nonSortableColumns = headerCells.reduce(function (columns, header, index) {
      if (header.dataset.gridSort === 'off') columns.push(index);
      return columns;
    }, []);
    if (nonSortableColumns.length) {
      columnDefs.push({ targets: nonSortableColumns, orderable: false });
    }
    if (actionsColumn >= 0) {
      columnDefs.push({ targets: [actionsColumn], orderable: false, searchable: false });
    }
    const dataTable = jQuery(table).DataTable({
      dom: '<"enrolled-grid-toolbar"Bf>t<"enrolled-grid-footer"lip>',
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i>Excel',
          title: 'Élèves déjà inscrits',
          filename: 'eleves-inscrits',
          exportOptions: exportOptions
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>PDF',
          title: 'Élèves déjà inscrits',
          filename: 'eleves-inscrits',
          orientation: 'landscape',
          pageSize: 'A4',
          exportOptions: exportOptions
        }
      ],
      autoWidth: false,
      pageLength: 10,
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Tous']],
      order: [[1, 'asc']],
      columnDefs: columnDefs,
      language: {
        search: 'Rechercher',
        searchPlaceholder: 'Nom, matricule, parent, coordonnées…',
        lengthMenu: '_MENU_ lignes',
        info: '_START_–_END_ sur _TOTAL_ élèves',
        infoEmpty: 'Aucun élève disponible',
        emptyTable: 'Aucun élève inscrit pour le moment.',
        zeroRecords: 'Aucun élève ne correspond aux filtres.',
        paginate: { first: 'Premier', previous: 'Précédent', next: 'Suivant', last: 'Dernier' },
        buttons: { excel: 'Excel', pdf: 'PDF' }
      },
      initComplete: function () {
        const api = this.api();
        [
          { column: columnIndex('Genre'), id: 'enrolledGenderFilter' },
          { column: columnIndex('Section'), id: 'enrolledSectionFilter' },
          { column: columnIndex('Classe'), id: 'enrolledClassFilter' },
          { column: columnIndex('Option'), id: 'enrolledOptionFilter' }
        ].forEach(function (filter) {
          const select = document.getElementById(filter.id);
          if (!select) return;

          const values = api.column(filter.column).data().unique().toArray()
            .map(function (value) { return String(value).trim(); })
            .filter(Boolean)
            .sort(new Intl.Collator('fr', { numeric: true, sensitivity: 'base' }).compare);
          values.forEach(function (value) {
            select.add(new Option(value, value));
          });
          select.addEventListener('change', function () {
            const escaped = jQuery.fn.dataTable.util.escapeRegex(this.value);
            api.column(filter.column).search(this.value ? '^' + escaped + '$' : '', true, false).draw();
          });
        });
      }
    });

    const resetFilters = document.getElementById('resetEnrolledStudentFilters');
    if (resetFilters) {
      resetFilters.addEventListener('click', function () {
        ['enrolledGenderFilter', 'enrolledSectionFilter', 'enrolledClassFilter', 'enrolledOptionFilter'].forEach(function (id) {
          const select = document.getElementById(id);
          if (select) select.value = '';
        });
        dataTable.search('');
        dataTable.columns().search('');
        dataTable.draw();
      });
    }

    const detailsToggle = document.getElementById('toggleEnrolledStudentDetails');
    if (detailsToggle && detailColumns.length) {
      detailsToggle.addEventListener('click', function () {
        const showDetails = this.getAttribute('aria-pressed') !== 'true';
        dataTable.columns(detailColumns).visible(showDetails);
        this.setAttribute('aria-pressed', String(showDetails));
        this.innerHTML = showDetails
          ? '<i class="bi bi-arrows-collapse me-1" aria-hidden="true"></i>Masquer les détails'
          : '<i class="bi bi-arrows-expand me-1" aria-hidden="true"></i>Afficher les détails';
      });
    }
  });
</script>
SCRIPT;
require __DIR__ . '/../partials/app_header.php';
?>
<div class="inscriptions-page">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Inscriptions</h1>
              <p class="text-muted mb-0">Suivez les dossiers à valider et consultez les élèves déjà inscrits.</p>
            </div>
            <div class="col-sm-6 d-flex align-items-center justify-content-sm-end mt-3 mt-sm-0">
              <ol class="breadcrumb mb-0 me-sm-3">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Inscriptions</li>
              </ol>
              <?php if ($canSubmit): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectionSelectModal">
                  <i class="bi bi-plus-lg me-2"></i>Nouvelle inscription
                </button>
              <?php endif; ?>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="small-box bg-primary registration-summary">
                <div class="inner">
                  <h3><?= $totalStudentsCount ?></h3>
                  <p>Élèves suivis</p>
                </div>
                <div class="icon"><i class="bi bi-people"></i></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="small-box bg-warning registration-summary">
                <div class="inner">
                  <h3><?= $pendingCount ?></h3>
                  <p>Dossiers en attente</p>
                </div>
                <div class="icon"><i class="bi bi-hourglass-split"></i></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="small-box bg-success registration-summary">
                <div class="inner">
                  <h3><?= $enrolledCount ?></h3>
                  <p>Élèves déjà inscrits</p>
                </div>
                <div class="icon"><i class="bi bi-person-check"></i></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['inscriptions_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= htmlspecialchars($_SESSION['inscriptions_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['inscriptions_success']); ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['inscriptions_errors'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <ul class="mb-0">
                <?php foreach ($_SESSION['inscriptions_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['inscriptions_errors']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['account_access_notification'])): ?>
            <?php $accountAccessNotification = $_SESSION['account_access_notification']; unset($_SESSION['account_access_notification']); ?>
            <?php
              $accountAccessStatus = $accountAccessNotification['status'] ?? '';
              $accountAccessAlertClass = $accountAccessStatus === 'success' ? 'alert-success' : 'alert-warning';
            ?>
            <div class="alert <?= $accountAccessAlertClass ?> alert-dismissible" role="status">
              <?= htmlspecialchars($accountAccessNotification['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
          <?php endif; ?>
          <?php if (!empty($_SESSION['inscriptions_notification'])): ?>
            <?php $registrationNotification = $_SESSION['inscriptions_notification']; unset($_SESSION['inscriptions_notification']); ?>
            <?php
              $registrationStatus = $registrationNotification['status'] ?? '';
              $registrationAlertClass = $registrationStatus === 'success' ? 'alert-success' : ($registrationStatus === 'partial' ? 'alert-warning' : 'alert-danger');
              $registrationTitle = $registrationStatus === 'success'
                  ? 'Message de confirmation créé'
                  : ($registrationStatus === 'partial' ? 'Message de confirmation partiel' : 'Message au parent non créé');
            ?>
            <div class="alert <?= $registrationAlertClass ?> alert-dismissible">
              <strong><?= htmlspecialchars($registrationTitle, ENT_QUOTES, 'UTF-8') ?></strong><br>
              <?= htmlspecialchars($registrationNotification['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
              <?php if (!empty($registrationNotification['details'])): ?>
                <div class="small mt-1"><?= htmlspecialchars($registrationNotification['details'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-xl-12">
              <div class="card registration-card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                  <div>
                    <h3 class="registration-grid-title"><i class="bi bi-card-checklist"></i>Dossiers d’inscription</h3>
                    <small class="text-muted">Espace de gestion · <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></small>
                  </div>
                </div>
                <div class="card-body">
                  <p class="text-muted mb-4">Recherchez, triez et parcourez les dossiers. La validation finale est assurée par le secrétaire.</p>

                  <?php if (empty($pendingStudents)): ?>
                    <div class="alert alert-light border d-flex align-items-center gap-2 mb-0">
                      <i class="bi bi-check-circle text-success"></i>
                      <span>Aucun dossier en attente de validation.</span>
                    </div>
                  <?php else: ?>
                    <section class="registration-grid mb-5" aria-labelledby="pendingRegistrationsTitle">
                      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h4 class="registration-grid-title" id="pendingRegistrationsTitle">
                          <i class="bi bi-hourglass-split text-warning"></i>
                          À valider <span class="badge rounded-pill text-bg-warning"><?= $pendingCount ?></span>
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline-secondary registration-export" data-export-table="pendingRegistrationsGrid" data-export-name="inscriptions-en-attente">
                          <i class="bi bi-download me-1"></i>Exporter CSV
                        </button>
                      </div>
                      <div class="table-responsive">
                        <table id="pendingRegistrationsGrid" class="table table-hover align-middle">
                        <thead class="table-light">
                          <tr>
                            <th data-grid-sort="off" style="width: 60px;">N°</th>
                            <th>Matricule</th>
                            <th>Nom complet</th>
                            <th data-grid-sort-type="date">Date de naissance</th>
                            <th>Parent / responsable</th>
                            <th style="width: 130px;">Statut</th>
                            <?php if ($canEdit || $canApprove): ?>
                              <th class="registration-actions" data-grid-sort="off" style="width: 180px;">Actions</th>
                            <?php endif; ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($pendingStudents as $index => $student): ?>
                            <?php $studentFullName = trim(implode(' ', array_filter([
                                trim((string) ($student['nom'] ?? '')),
                                trim((string) ($student['postnom'] ?? '')),
                                trim((string) ($student['prenom'] ?? '')),
                            ], static fn ($part) => $part !== ''))); ?>
                            <tr>
                              <td class="text-muted"><?= $index + 1 ?></td>
                              <td><span class="student-reference"><?= htmlspecialchars($student['matricule'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                              <td class="student-name"><?= htmlspecialchars($studentFullName !== '' ? $studentFullName : '-', ENT_QUOTES, 'UTF-8') ?></td>
                              <td><?= htmlspecialchars(formatDate($student['date_naissance'] ?? null)) ?></td>
                              <td><?= htmlspecialchars($student['parent_nom_responsable'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                              <td><span class="badge rounded-pill text-bg-warning"><i class="bi bi-clock me-1"></i>En attente</span></td>
                              <?php if ($canEdit || $canApprove): ?>
                                <td class="registration-actions">
                                  <?php if ($canEdit): ?>
                                    <a href="<?= BASE_URL ?>/inscriptions/edit?id=<?= (int) $student['id'] ?>" class="btn btn-sm btn-outline-primary" aria-label="Modifier <?= htmlspecialchars(trim(($student['prenom'] ?? '') . ' ' . ($student['nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                                      <i class="bi bi-pencil-square me-1"></i>Modifier
                                    </a>
                                  <?php endif; ?>
                                  <?php if ($canApprove): ?>
                                    <form method="post" action="<?= BASE_URL ?>/inscriptions/approve" class="d-inline">
                                      <input type="hidden" name="eleve_id" value="<?= (int) $student['id'] ?>">
                                      <button type="submit" class="btn btn-sm btn-success" aria-label="Valider <?= htmlspecialchars(trim(($student['prenom'] ?? '') . ' ' . ($student['nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-check2 me-1"></i>Valider
                                      </button>
                                    </form>
                                  <?php endif; ?>
                                </td>
                              <?php endif; ?>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    </section>
                  <?php endif; ?>

                  <section class="enrolled-grid-card" aria-labelledby="enrolledStudentsTitle">
                    <div class="card-header">
                      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                          <h4 class="enrolled-grid-title" id="enrolledStudentsTitle">
                            <i class="bi bi-person-check"></i>
                            Élèves déjà inscrits <span class="badge rounded-pill text-bg-primary"><?= $enrolledCount ?></span>
                          </h4>
                          <small class="text-muted">Filtrez les élèves, affichez les informations complémentaires au besoin et exportez les résultats. Les liens d’accès sont envoyés au téléphone du parent/tuteur.</small>
                          <?php if ($canManageAccountAccess): ?>
                            <div class="small text-muted mt-2">
                              <i class="bi bi-shield-lock me-1" aria-hidden="true"></i>
                              Le secrétariat peut créer l’accès ou renvoyer un lien sécurisé pour que le parent ou l’élève choisisse un nouveau mot de passe. Le lien expire après 24 heures ; celui de l’élève exige son matricule.
                            </div>
                          <?php endif; ?>
                        </div>
                        <?php if (!empty($enrolledStudents)): ?>
                          <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleEnrolledStudentDetails" aria-pressed="false">
                              <i class="bi bi-arrows-expand me-1" aria-hidden="true"></i>Afficher les détails
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="resetEnrolledStudentFilters">
                              <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Réinitialiser les filtres
                            </button>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="card-body">
                    <?php if (empty($enrolledStudents)): ?>
                      <div class="alert alert-light border d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-info-circle text-muted"></i>
                        <span>Aucun élève inscrit pour le moment.</span>
                      </div>
                    <?php else: ?>
                      <div class="enrolled-student-filters" role="group" aria-label="Filtres des élèves inscrits">
                        <label for="enrolledGenderFilter">
                          Genre
                          <select id="enrolledGenderFilter">
                            <option value="">Tous les genres</option>
                          </select>
                        </label>
                        <label for="enrolledSectionFilter">
                          Section
                          <select id="enrolledSectionFilter">
                            <option value="">Toutes les sections</option>
                          </select>
                        </label>
                        <label for="enrolledClassFilter">
                          Classe
                          <select id="enrolledClassFilter">
                            <option value="">Toutes les classes</option>
                          </select>
                        </label>
                        <label for="enrolledOptionFilter">
                          Option
                          <select id="enrolledOptionFilter">
                            <option value="">Toutes les options</option>
                          </select>
                        </label>
                      </div>
                        <div class="enrolled-grid-table table-responsive" role="region" aria-label="Liste des élèves inscrits, défilement horizontal" tabindex="0">
                        <table id="enrolledStudentsGrid" class="table table-hover align-middle enrolled-students-table" data-datagrid="off">
                          <thead class="table-light">
                            <tr>
                              <th>N°</th>
                              <th>Matricule</th>
                              <th>Nom</th>
                              <?php if ($canSubmit): ?>
                                <th data-grid-sort="off">Année / réinscription</th>
                              <?php endif; ?>
                              <th data-enrolled-detail>Postnom</th>
                              <th data-enrolled-detail>Prénom</th>
                              <th>Genre</th>
                              <th>Date de naissance</th>
                              <th>Section</th>
                              <th>Classe</th>
                              <th>Option</th>
                              <th>Parent / responsable</th>
                              <th>Téléphone du parent</th>
                              <th data-enrolled-detail>Email du parent</th>
                              <th data-enrolled-detail>Nom du père</th>
                              <th data-enrolled-detail>Nom de la mère</th>
                              <th data-enrolled-detail>Province d’origine</th>
                              <th data-enrolled-detail>Territoire</th>
                              <th data-enrolled-detail>Numéro permanent</th>
                              <th data-enrolled-detail>Lieu de naissance</th>
                              <th data-enrolled-detail>Adresse</th>
                              <th data-enrolled-detail>Photo</th>
                              <?php if ($canManageAccountAccess): ?>
                                <th class="student-account-actions-column" data-grid-sort="off">Accès comptes</th>
                              <?php endif; ?>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($enrolledStudents as $index => $student): ?>
                              <?php
                                $studentGender = ($student['genre'] ?? '') === 'M'
                                    ? 'Masculin'
                                    : ((($student['genre'] ?? '') === 'F') ? 'Féminin' : '-');
                                $studentFullName = trim(implode(' ', array_filter([
                                    $student['nom'] ?? '',
                                    $student['postnom'] ?? '',
                                    $student['prenom'] ?? '',
                                ], static function ($part) {
                                    return trim((string) $part) !== '';
                                })));
                                $studentClass = trim((string) ($student['nom_classe'] ?? ''));
                                $studentOption = trim((string) ($student['nom_option'] ?? ''));
                              ?>
                              <tr>
                                <td class="text-muted"><?= $index + 1 ?></td>
                                <td><span class="student-reference"><?= htmlspecialchars($student['matricule'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>
                                  <a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) ($student['id'] ?? 0) ?>">
                                    <span class="student-name"><?= htmlspecialchars($studentFullName !== '' ? $studentFullName : '-', ENT_QUOTES, 'UTF-8') ?></span>
                                  </a>
                                </td>
                                <?php if ($canSubmit): ?>
                                  <td class="text-nowrap">
                                    <div class="small text-muted mb-1">
                                      Année : <?= htmlspecialchars($student['annee_scolaire'] ?? 'Non précisée', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <a class="btn btn-sm btn-outline-secondary mb-1" href="<?= BASE_URL ?>/inscriptions/parcours?id=<?= (int) ($student['id'] ?? 0) ?>">
                                      <i class="bi bi-journal-text me-1" aria-hidden="true"></i>Parcours
                                    </a>
                                    <?php if (!empty($student['annee_scolaire_id']) && (int) $student['annee_scolaire_id'] !== (int) ($activeYearId ?? 0) && (int) ($activeYearId ?? 0) > 0): ?>
                                      <a class="btn btn-sm btn-success mb-1" href="<?= BASE_URL ?>/inscriptions/reenroll?id=<?= (int) ($student['id'] ?? 0) ?>" aria-label="Confirmer la réinscription de <?= htmlspecialchars($studentFullName !== '' ? $studentFullName : 'cet élève', ENT_QUOTES, 'UTF-8') ?>" title="Confirmer la réinscription">
                                        <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Réinscrire
                                      </a>
                                    <?php endif; ?>
                                  </td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($student['postnom'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['prenom'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($studentGender, ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-order="<?= htmlspecialchars((string) ($student['date_naissance'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(formatDate($student['date_naissance'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['nom_section'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($studentClass !== '' ? $studentClass : '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($studentOption !== '' ? $studentOption : '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['parent_nom_responsable'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['parent_telephone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['parent_email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['nom_pere'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['nom_mere'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['province_origine'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['territoire'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['num_permanent'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['lieu_naissance'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['adresse'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= !empty($student['photo']) ? 'Oui' : 'Non' ?></td>
                                <?php if ($canManageAccountAccess): ?>
                                  <td class="student-account-actions">
                                    <form method="post" action="<?= BASE_URL ?>/inscriptions/sendAccountAccess">
                                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($accountAccessCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                      <input type="hidden" name="eleve_id" value="<?= (int) ($student['id'] ?? 0) ?>">
                                      <input type="hidden" name="account_type" value="student">
                                      <button type="submit" class="btn btn-sm btn-outline-primary" title="Créer l’accès élève ou renvoyer son lien de mot de passe">
                                        <i class="bi bi-mortarboard me-1" aria-hidden="true"></i>Mot de passe élève
                                      </button>
                                    </form>
                                    <form method="post" action="<?= BASE_URL ?>/inscriptions/sendAccountAccess">
                                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($accountAccessCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                      <input type="hidden" name="eleve_id" value="<?= (int) ($student['id'] ?? 0) ?>">
                                      <input type="hidden" name="account_type" value="parent">
                                      <button type="submit" class="btn btn-sm btn-outline-success" title="Créer l’accès parent ou renvoyer son lien de mot de passe">
                                        <i class="bi bi-people me-1" aria-hidden="true"></i>Mot de passe parent
                                      </button>
                                    </form>
                                  </td>
                                <?php endif; ?>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                    </div>
                  </section>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <?php if (!empty($sections)): ?>
        <div class="modal fade" id="sectionSelectModal" tabindex="-1" aria-labelledby="sectionSelectModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="sectionSelectModalLabel">Choisir une section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <p>Sélectionnez la section dans laquelle vous souhaitez créer une inscription.</p>
                <div class="list-group">
                  <?php foreach ($sections as $section): ?>
                    <?php $isSecondary = mb_strtolower($section['nom_section'], 'UTF-8') === 'secondaire'; ?>
                    <a href="<?= $isSecondary ? 'javascript:void(0)' : BASE_URL . '/inscriptions/create?section_id=' . (int) $section['id'] ?>"
                       class="list-group-item list-group-item-action<?= $isSecondary ? ' secondary-section-item' : '' ?>"
                       data-section-id="<?= (int) $section['id'] ?>"
                       data-section-name="<?= htmlspecialchars($section['nom_section'], ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($section['nom_section']) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="modal-footer">
                <a href="<?= BASE_URL ?>/inscriptions/create" class="btn btn-outline-secondary">Créer sans section</a>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Annuler</button>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($options)): ?>
        <div class="modal fade" id="optionSelectModal" tabindex="-1" aria-labelledby="optionSelectModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="optionSelectModalLabel">Choisir une option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <p>Sélectionnez l’option dans laquelle l’élève doit être inscrit en secondaire.</p>
                <div class="list-group" id="optionListGroup">
                  <?php foreach ($options as $option): ?>
                    <a href="#" class="list-group-item list-group-item-action option-choice-item" data-option-id="<?= (int) $option['id'] ?>">
                      <?= htmlspecialchars($option['nom_option']) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <script>
        document.addEventListener('DOMContentLoaded', function () {
          document.querySelectorAll('.registration-export').forEach(function (button) {
            button.addEventListener('click', function () {
              const table = document.getElementById(button.dataset.exportTable);
              if (!table) {
                return;
              }

              const rows = Array.from(table.rows).map(function (row) {
                return Array.from(row.cells).filter(function (cell) {
                  return !cell.classList.contains('registration-actions');
                }).map(function (cell) {
                  const value = cell.innerText.trim().replace(/\s+/g, ' ');
                  const safeValue = /^[=+\-@]/.test(value) ? "'" + value : value;
                  return '"' + safeValue.replace(/"/g, '""') + '"';
                }).join(';');
              });
              const csv = '\uFEFF' + rows.join('\r\n');
              const link = document.createElement('a');
              link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
              link.download = (button.dataset.exportName || 'inscriptions') + '.csv';
              link.click();
              URL.revokeObjectURL(link.href);
            });
          });

          const optionModalElement = document.getElementById('optionSelectModal');
          const optionItems = document.querySelectorAll('.option-choice-item');
          let selectedSecondarySectionId = null;

          const sectionLinks = document.querySelectorAll('.secondary-section-item');
          sectionLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
              event.preventDefault();
              selectedSecondarySectionId = this.dataset.sectionId;
              if (optionModalElement) {
                const modal = new bootstrap.Modal(optionModalElement);
                modal.show();
                return;
              }
              window.location.href = '<?= BASE_URL ?>/inscriptions/create?section_id=' + encodeURIComponent(selectedSecondarySectionId);
            });
          });

          optionItems.forEach(function (link) {
            link.addEventListener('click', function (event) {
              event.preventDefault();
              if (!selectedSecondarySectionId) {
                return;
              }
              const optionId = this.dataset.optionId;
              window.location.href = '<?= BASE_URL ?>/inscriptions/create?section_id=' + encodeURIComponent(selectedSecondarySectionId) + '&option_id=' + encodeURIComponent(optionId);
            });
          });
        });
      </script>
      </div>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
