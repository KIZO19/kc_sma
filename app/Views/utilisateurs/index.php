<?php
$chartLabels = json_encode(array_column($schoolPopulations ?? [], 'nom_etablissement'));
$chartData = json_encode(array_map(function ($row) {
    return (int) $row['total_personnels'];
}, $schoolPopulations ?? []));

$pageStyles = <<<STYLE
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<style>
  .users-page .chart-card { min-height: 360px; }
  .users-page .users-grid-card { overflow: hidden; border: 0; box-shadow: 0 2px 12px rgba(31, 41, 55, .08); }
  .users-page .users-grid-card > .card-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; padding: 1rem 1.25rem; }
  .users-page .users-grid-card .card-title { font-weight: 600; }
  .users-page .users-grid-card .user-count { border-radius: 999px; font-size: .75rem; }
  .users-page .users-grid-card > .card-body { padding: 0; }
  .users-page .users-table-shell { overflow-x: auto; }
  .users-page .dataTables_wrapper { padding: 1rem 1.25rem; }
  .users-page .users-grid-toolbar,
  .users-page .users-grid-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem 1rem; }
  .users-page .users-grid-toolbar { margin-bottom: 1rem; }
  .users-page .users-grid-table { overflow: hidden; border: 1px solid var(--border, rgba(0, 0, 0, .08)); border-radius: .65rem; }
  .users-page table.users-data-table { min-width: 1050px; margin: 0 !important; border-collapse: separate; border-spacing: 0; }
  .users-page .users-data-table thead th { padding: .85rem .75rem; border-bottom: 1px solid var(--border, rgba(0, 0, 0, .08)); background: var(--surface-muted, #f8f9fb); color: var(--text-muted, #6c757d); font-size: .72rem; font-weight: 700; letter-spacing: .045em; text-transform: uppercase; white-space: nowrap; }
  .users-page .users-data-table tbody td { padding: .8rem .75rem; border-bottom: 1px solid var(--border, rgba(0, 0, 0, .06)); vertical-align: middle; }
  .users-page .users-data-table tbody tr:last-child td { border-bottom: 0; }
  .users-page .users-data-table tbody tr:hover { background: rgba(13, 110, 253, .035); }
  .users-page .users-data-table th:nth-child(1),
  .users-page .users-data-table td:nth-child(1) { width: 4.5rem; color: var(--text-muted, #6c757d); font-variant-numeric: tabular-nums; }
  .users-page .users-data-table th:nth-child(6),
  .users-page .users-data-table td:nth-child(6) { width: 5rem; text-align: center; }
  .users-page .users-data-table td:nth-child(3) { min-width: 12rem; overflow-wrap: anywhere; }
  .users-page .users-data-table th:last-child,
  .users-page .users-data-table td:last-child { min-width: 24rem; }
  .users-page .users-data-table .img-thumbnail { width: 2.75rem; height: 2.75rem; padding: .15rem; border-radius: .65rem; object-fit: cover; }
  .users-page .user-access-form { min-width: 22rem; }
  .users-page .dt-buttons { display: flex; flex-wrap: wrap; gap: .4rem; }
  .users-page .dt-buttons .dt-button { margin: 0; padding: .4rem .7rem; border: 1px solid #d5deea; border-radius: .4rem; background: var(--surface, #fff); color: var(--text, #1f2937); font-size: .8125rem; }
  .users-page .dt-buttons .dt-button:hover { border-color: #0d6efd; background: rgba(13, 110, 253, .06); color: #0d6efd; }
  .users-page .dataTables_filter label,
  .users-page .dataTables_length label { display: inline-flex; align-items: center; gap: .5rem; margin: 0; color: var(--text-muted, #6c757d); font-size: .875rem; }
  .users-page .dataTables_filter input,
  .users-page .dataTables_length select { min-height: 2.25rem; padding: .35rem .65rem; border: 1px solid #d5deea; border-radius: .4rem; background: var(--surface, #fff); color: var(--text, #1f2937); }
  .users-page .dataTables_filter input { min-width: min(18rem, 55vw); margin-left: 0 !important; }
  .users-page .users-grid-footer { margin-top: 1rem; color: var(--text-muted, #6c757d); font-size: .8125rem; }
  .users-page .dataTables_paginate .paginate_button { margin: 0 .1rem; padding: .35rem .65rem !important; border: 1px solid transparent !important; border-radius: .4rem; }
  .users-page .dataTables_paginate .paginate_button.current { border-color: #cfe2ff !important; background: #eaf2ff !important; color: #0d6efd !important; }
  [data-theme="dark"] .users-page .users-data-table thead th { background: var(--surface-muted, #172232); }
  [data-theme="dark"] .users-page .users-grid-table { border-color: rgba(255, 255, 255, .12); }
  [data-theme="dark"] .users-page .users-data-table tbody tr:hover { background: rgba(96, 165, 250, .08); }
  [data-theme="dark"] .users-page .dt-buttons .dt-button { border-color: rgba(255, 255, 255, .16); }
  @media (max-width: 767.98px) {
    .users-page .dataTables_wrapper { padding: .75rem; }
    .users-page .users-grid-toolbar,
    .users-page .users-grid-footer { align-items: stretch; }
    .users-page .users-grid-toolbar > *,
    .users-page .users-grid-footer > * { width: 100%; }
    .users-page .dt-buttons { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .users-page .dt-buttons .dt-button { padding-inline: .35rem; text-align: center; }
    .users-page .dataTables_filter label { display: flex; flex-wrap: wrap; }
    .users-page .dataTables_filter input { width: 100%; min-width: 0; }
    .users-page .dataTables_paginate { align-self: center; }
  }
</style>
STYLE;

$pageScripts = <<<SCRIPT
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.user-access-form').forEach(function (form) {
      const roleSelect = form.querySelector('[name="role"]');
      const sectionSelect = form.querySelector('[name="section_id"]');
      const sectionField = form.querySelector('.user-section-field');
      if (!roleSelect || !sectionSelect || !sectionField) return;

      const updateSectionOptions = function () {
        const role = roleSelect.value;
        const secondaryRoles = ['préfet_école', 'DE_école', 'DD_école'];
        const primaryRoles = ['DP_école', 'DA_école'];
        const allowedNames = secondaryRoles.includes(role)
          ? ['secondaire']
          : (primaryRoles.includes(role) ? ['primaire', 'maternelle'] : []);
        const scoped = allowedNames.length > 0;
        sectionField.hidden = !scoped;
        sectionSelect.disabled = !scoped;
        sectionSelect.required = scoped;

        let selectedIsAllowed = false;
        Array.from(sectionSelect.options).forEach(function (option) {
          const allowed = option.value !== '' && allowedNames.includes(option.dataset.sectionName || '');
          option.hidden = option.value !== '' && scoped && !allowed;
          if (option.selected && allowed) selectedIsAllowed = true;
        });

        if (scoped && !selectedIsAllowed) {
          const preferredName = secondaryRoles.includes(role) ? 'secondaire' : 'primaire';
          const preferredOption = Array.from(sectionSelect.options).find(function (option) {
            return option.dataset.sectionName === preferredName && !option.hidden;
          });
          const firstAllowedOption = Array.from(sectionSelect.options).find(function (option) {
            return option.value !== '' && !option.hidden;
          });
          sectionSelect.value = (preferredOption || firstAllowedOption || {}).value || '';
        }
      };

      roleSelect.addEventListener('change', updateSectionOptions);
      updateSectionOptions();
    });

    const tableIds = ['#allUsersTable', '#inactiveUsersTable', '#unassignedUsersTable'];
    tableIds.forEach(function (selector) {
      const table = document.querySelector(selector);
      if (!table || !window.jQuery || !jQuery.fn.DataTable) return;
      const isAccountTable = selector === '#allUsersTable';
      window.jQuery(selector).DataTable({
        dom: '<"users-grid-toolbar"Bf><"users-grid-table"t><"users-grid-footer"lip>',
        buttons: [
          { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i>Excel', className: 'users-export-button', title: 'Utilisateurs KC_SMA' },
          { extend: 'pdfHtml5', text: '<i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>PDF', className: 'users-export-button', title: 'Utilisateurs KC_SMA' },
          { extend: 'print', text: '<i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimer', className: 'users-export-button', title: 'Utilisateurs KC_SMA' }
        ],
        autoWidth: false,
        scrollX: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        order: [[1, 'asc']],
        columnDefs: isAccountTable
          ? [
            { targets: [5, 8], orderable: false, searchable: false },
            { targets: [0], type: 'num' }
          ]
          : [{ targets: [0], type: 'num' }],
        language: {
          search: 'Rechercher',
          searchPlaceholder: 'Nom, identifiant, rôle…',
          lengthMenu: '_MENU_ lignes',
          info: '_START_–_END_ sur _TOTAL_ utilisateurs',
          infoEmpty: 'Aucun utilisateur disponible',
          emptyTable: 'Aucun utilisateur disponible.',
          zeroRecords: 'Aucun utilisateur ne correspond à la recherche.',
          paginate: { first: 'Premier', previous: 'Précédent', next: 'Suivant', last: 'Dernier' },
          buttons: { excel: 'Excel', pdf: 'PDF', print: 'Imprimer' }
        }
      });
    });

    const ctx = document.getElementById('schoolPopulationChart');
    if (ctx) {
      const labels = $chartLabels;
      const data = $chartData;
      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [{
            data: data,
            backgroundColor: [
              '#0d6efd','#198754','#ffc107','#dc3545','#6610f2','#0dcaf0','#fd7e14','#6f42c1','#20c997','#e83e8c'
            ],
            borderColor: '#ffffff',
            borderWidth: 2
          }]
        },
        options: {
          plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: function(context) { return context.label + ': ' + context.parsed + ' personnes'; } } }
          }
        }
      });
    }
  });
</script>
SCRIPT;
?>

<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header users-page">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['utilisateurs_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['utilisateurs_success']) ?></div>
      <?php unset($_SESSION['utilisateurs_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['utilisateurs_errors'])): ?>
      <div class="alert alert-danger">
        <?php foreach ((array) $_SESSION['utilisateurs_errors'] as $error): ?>
          <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
      </div>
      <?php unset($_SESSION['utilisateurs_errors']); ?>
    <?php endif; ?>
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><?= ($role ?? '') === 'super_admin' ? 'Gestion des utilisateurs de la plateforme' : 'Utilisateurs de votre école' ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Utilisateurs</li>
        </ol>
      </div>
    </div>
    <?php if (($role ?? '') === 'super_admin'): ?>
      <div class="row mb-4">
        <div class="col-lg-8">
          <div class="card chart-card">
            <div class="card-header">
              <h3 class="card-title">Répartition des effectifs par école</h3>
            </div>
            <div class="card-body">
              <?php if (empty($schoolPopulations)): ?>
                <div class="alert alert-info">Aucune donnée d’école disponible pour le graphique.</div>
              <?php else: ?>
                <canvas id="schoolPopulationChart" style="width:100%;height:360px;"></canvas>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card chart-card">
            <div class="card-header">
              <h3 class="card-title">École la plus fournie</h3>
            </div>
            <div class="card-body">
              <?php if (!empty($schoolPopulations)): ?>
                <h4><?= htmlspecialchars($schoolPopulations[0]['nom_etablissement']) ?></h4>
                <p class="mb-1">Total de personnes : <strong><?= (int) $schoolPopulations[0]['total_personnels'] ?></strong></p>
                <p class="text-muted">Cette école a le plus de personnels liés.</p>
              <?php else: ?>
                <div class="alert alert-info">Pas de données disponibles.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="row mb-4">
        <div class="col-lg-12">
          <div class="card chart-card">
            <div class="card-header">
              <h3 class="card-title">Statistiques de votre école</h3>
            </div>
            <div class="card-body">
              <p class="mb-2">Vous visualisez les comptes et les statuts associés à votre établissement.</p>
              <div class="alert alert-info mb-0">Les données détaillées par école restent réservées au super administrateur.</div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="content users-page">
  <div class="container-fluid">
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3><?= (int) ($summaryStats['total'] ?? 0) ?></h3>
            <p>Total utilisateurs</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-success">
          <div class="inner">
            <h3><?= (int) ($summaryStats['actifs'] ?? 0) ?></h3>
            <p>Comptes actifs</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3><?= (int) ($summaryStats['inactifs'] ?? 0) ?></h3>
            <p>En attente</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-info">
          <div class="inner">
            <h3><?= (int) ($summaryStats['non_lies'] ?? 0) ?></h3>
            <p>Comptes personnels non liés</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4 users-grid-card">
      <div class="card-header">
        <h3 class="card-title mb-0"><?= ($role ?? '') === 'super_admin' ? 'Comptes de la plateforme' : 'Comptes de votre école' ?></h3>
        <span class="badge bg-light text-secondary user-count"><?= count($allUsers ?? []) ?> compte<?= count($allUsers ?? []) === 1 ? '' : 's' ?></span>
      </div>
      <div class="card-body">
        <?php if (empty($allUsers)): ?>
          <div class="alert alert-info">Aucun utilisateur disponible.</div>
        <?php else: ?>
          <div class="users-table-shell">
            <table id="allUsersTable" class="table table-striped table-hover align-middle users-data-table" aria-label="Liste des comptes utilisateurs">
              <thead>
                <tr>
                  <th scope="col" data-grid-sort-type="number">ID</th>
                  <th scope="col">Nom</th>
                  <th scope="col">Identifiant</th>
                  <th scope="col">Rôle</th>
                  <th scope="col">École</th>
                  <th scope="col" data-grid-sort="off">Logo</th>
                  <th scope="col">Statut</th>
                  <th scope="col">Type</th>
                  <th scope="col" data-grid-sort="off">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allUsers as $account): ?>
                  <tr>
                    <td><?= (int) $account['id'] ?></td>
                    <td><?= htmlspecialchars($account['nom_complet'] ?? '') ?></td>
                    <td><?= htmlspecialchars($account['identifiant'] ?? '') ?></td>
                    <td><?= htmlspecialchars(\App\Models\User::getRoleLabel($account['role'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($schoolNamesById[(int) ($account['ecole_id'] ?? 0)] ?? 'Non lié') ?></td>
                    <td>
                      <?php $schoolLogo = $schoolDetailsById[(int) ($account['ecole_id'] ?? 0)]['logo_url'] ?? ''; ?>
                      <?php if (!empty($schoolLogo)): ?>
                        <?php $logoSrc = $schoolLogo; if (strpos($logoSrc, 'http') !== 0) { $logoSrc = BASE_URL . '/' . ltrim($logoSrc, '/'); } ?>
                        <img src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo école" class="img-thumbnail">
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (($account['statut'] ?? '') === 'Actif'): ?>
                        <span class="badge bg-success">Actif</span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark">Inactif</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (in_array($account['role'] ?? '', ['agent_ecole', 'parent_ecole', 'enseignant_école'], true)): ?>
                        <span class="badge bg-info text-dark">Personnel</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Système</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (($role ?? '') === 'super_admin'): ?>
                        <a href="<?= BASE_URL ?>/utilisateurs/editAccount?id=<?= (int) $account['id'] ?>" class="btn btn-sm btn-outline-secondary mb-2">Modifier les informations</a>
                        <form method="post" action="<?= BASE_URL ?>/utilisateurs/update-access" class="d-flex flex-wrap gap-2 align-items-center user-access-form">
                          <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($accountCsrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                          <select name="role" class="form-select form-select-sm" required>
                            <?php foreach (($availableRoles ?? []) as $roleKey => $roleName): ?>
                              <option value="<?= htmlspecialchars($roleKey) ?>" <?= (($account['role'] ?? '') === $roleKey) ? 'selected' : '' ?>><?= htmlspecialchars($roleName) ?></option>
                            <?php endforeach; ?>
                          </select>
                          <select name="ecole_id" class="form-select form-select-sm">
                            <option value="">Aucune école</option>
                            <?php foreach (($schools ?? []) as $school): ?>
                              <option value="<?= (int) $school['id'] ?>" <?= ((int) ($account['ecole_id'] ?? 0) === (int) $school['id']) ? 'selected' : '' ?>><?= htmlspecialchars($school['nom_etablissement']) ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="user-section-field" hidden>
                            <select name="section_id" class="form-select form-select-sm" aria-label="Section affectée à ce compte" disabled>
                              <option value="">Choisir une section</option>
                              <?php foreach (($sections ?? []) as $section): ?>
                                <option
                                  value="<?= (int) $section['id'] ?>"
                                  data-section-name="<?= htmlspecialchars(mb_strtolower(trim((string) $section['nom_section']), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>"
                                  <?= (int) ($account['section_id'] ?? 0) === (int) $section['id'] ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($section['nom_section'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <select name="statut" class="form-select form-select-sm" required>
                            <option value="Actif" <?= (($account['statut'] ?? '') === 'Actif') ? 'selected' : '' ?>>Actif</option>
                            <option value="Inactif" <?= (($account['statut'] ?? '') === 'Inactif') ? 'selected' : '' ?>>Inactif</option>
                          </select>
                          <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                        </form>
                      <?php elseif ((in_array($account['role'] ?? '', ['agent_ecole', 'parent_ecole', 'enseignant_école'], true) && (empty($account['ecole_id']) || (int) $account['ecole_id'] === 0)) && (($role ?? '') === 'super_admin')): ?>
                        <form method="post" action="<?= BASE_URL ?>/utilisateurs/link" class="d-flex gap-2 align-items-center">
                          <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                          <select name="ecole_id" class="form-select form-select-sm" required>
                            <option value="">Choisir une école</option>
                            <?php foreach (($schools ?? []) as $school): ?>
                              <option value="<?= (int) $school['id'] ?>"><?= htmlspecialchars($school['nom_etablissement']) ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="btn btn-sm btn-primary">Lier</button>
                        </form>
                      <?php elseif (
                          ($role ?? '') === 'ecole_admin'
                          && (int) ($account['ecole_id'] ?? 0) === (int) ($user['ecole_id'] ?? 0)
                          && (int) ($account['id'] ?? 0) !== (int) ($user['id'] ?? 0)
                          && !in_array($account['role'] ?? '', ['ecole_admin', 'promoteur_école'], true)
                      ): ?>
                        <a href="<?= BASE_URL ?>/utilisateurs/editAccount?id=<?= (int) $account['id'] ?>" class="btn btn-sm btn-outline-secondary mb-2">Modifier les informations</a>
                        <form method="post" action="<?= BASE_URL ?>/utilisateurs/update-access" class="d-flex flex-wrap gap-2 align-items-center user-access-form">
                          <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($accountCsrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                          <select name="role" class="form-select form-select-sm" required>
                            <?php foreach (($manageableSchoolRoles ?? []) as $roleKey => $roleName): ?>
                              <option value="<?= htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8') ?>" <?= (($account['role'] ?? '') === $roleKey) ? 'selected' : '' ?>><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="user-section-field" hidden>
                            <select name="section_id" class="form-select form-select-sm" aria-label="Section affectée à ce compte" disabled>
                              <option value="">Choisir une section</option>
                              <?php foreach (($sections ?? []) as $section): ?>
                                <option
                                  value="<?= (int) $section['id'] ?>"
                                  data-section-name="<?= htmlspecialchars(mb_strtolower(trim((string) $section['nom_section']), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>"
                                  <?= (int) ($account['section_id'] ?? 0) === (int) $section['id'] ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($section['nom_section'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <select name="statut" class="form-select form-select-sm" required>
                            <option value="Actif" <?= (($account['statut'] ?? '') === 'Actif') ? 'selected' : '' ?>>Actif</option>
                            <option value="Inactif" <?= (($account['statut'] ?? '') === 'Inactif') ? 'selected' : '' ?>>Inactif</option>
                          </select>
                          <button type="submit" class="btn btn-sm btn-primary">Modifier les accès</button>
                        </form>
                      <?php elseif (($role ?? '') === 'ecole_admin' && (int) ($account['ecole_id'] ?? 0) === (int) ($user['ecole_id'] ?? 0)): ?>
                        <a href="<?= BASE_URL ?>/utilisateurs/editAccount?id=<?= (int) $account['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier les informations</a>
                      <?php else: ?>
                        <span class="text-muted">Lecture seule</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Comptes en attente</h3>
          </div>
          <div class="card-body">
            <?php if (empty($inactiveUsers)): ?>
              <div class="alert alert-info">Aucun compte en attente de validation.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table id="inactiveUsersTable" class="table table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Nom</th>
                      <th>Rôle</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($inactiveUsers as $inactive): ?>
                      <tr>
                        <td><?= (int) $inactive['id'] ?></td>
                        <td><?= htmlspecialchars($inactive['nom_complet']) ?></td>
                        <td><?= htmlspecialchars(\App\Models\User::getRoleLabel($inactive['role'])) ?></td>
                        <td>
                          <?php
                            $isPendingUnassignedAgent = ($inactive['role'] ?? '') === 'agent_ecole'
                              && (empty($inactive['ecole_id']) || (int) $inactive['ecole_id'] === 0);
                            $hasSchoolAssignment = (int) ($inactive['ecole_id'] ?? 0) > 0;
                            $requiresSchoolAssignment = in_array(
                              (string) ($inactive['role'] ?? ''),
                              \App\Models\User::getSchoolScopedRoles(),
                              true
                            );
                            $canValidateAccount = (
                              ($role ?? '') === 'super_admin'
                              && (!$requiresSchoolAssignment || $hasSchoolAssignment)
                            )
                              || (
                                in_array(($role ?? ''), ['ecole_admin', 'promoteur_école'], true)
                                && (int) ($user['ecole_id'] ?? 0) > 0
                                && (
                                  (int) ($inactive['ecole_id'] ?? 0) === (int) ($user['ecole_id'] ?? 0)
                                  || $isPendingUnassignedAgent
                                )
                              );
                          ?>
                          <?php if ($canValidateAccount): ?>
                            <form method="post" action="<?= BASE_URL ?>/utilisateurs/validate" style="display:inline">
                              <input type="hidden" name="user_id" value="<?= (int) $inactive['id'] ?>">
                              <button type="submit" class="btn btn-sm btn-success">
                                <?= $isPendingUnassignedAgent && ($role ?? '') !== 'super_admin' ? 'Affecter et valider' : 'Valider' ?>
                              </button>
                            </form>
                          <?php else: ?>
                            <span class="text-muted"><?= $isPendingUnassignedAgent && ($role ?? '') === 'super_admin' ? 'À lier à une école avant validation' : 'Lecture seule' ?></span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (($role ?? '') === 'super_admin'): ?>
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Comptes personnels à lier</h3>
          </div>
          <div class="card-body">
            <?php if (empty($unassignedUsers)): ?>
              <div class="alert alert-info">Aucun agent, parent ou enseignant non lié à une école.</div>
            <?php elseif (empty($schools)): ?>
              <div class="alert alert-warning">Aucune école disponible pour le lien.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table id="unassignedUsersTable" class="table table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Nom</th>
                      <th>Rôle</th>
                      <th>Associer</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($unassignedUsers as $unassigned): ?>
                      <tr>
                        <td><?= (int) $unassigned['id'] ?></td>
                        <td><?= htmlspecialchars($unassigned['nom_complet']) ?></td>
                        <td><?= htmlspecialchars(\App\Models\User::getRoleLabel($unassigned['role'])) ?></td>
                        <td>
                          <?php if (($role ?? '') === 'super_admin'): ?>
                            <form method="post" action="<?= BASE_URL ?>/utilisateurs/link" class="d-flex gap-2 align-items-center">
                              <input type="hidden" name="user_id" value="<?= (int) $unassigned['id'] ?>">
                              <select name="ecole_id" class="form-select form-select-sm" required>
                                <option value="">Choisir une école</option>
                                <?php foreach ($schools as $school): ?>
                                  <option value="<?= (int) $school['id'] ?>"><?= htmlspecialchars($school['nom_etablissement']) ?></option>
                                <?php endforeach; ?>
                              </select>
                              <button type="submit" class="btn btn-sm btn-primary">Lier</button>
                            </form>
                          <?php else: ?>
                            <span class="text-muted">Lecture seule</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>
