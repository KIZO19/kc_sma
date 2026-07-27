<?php
$chartLabels = json_encode(array_column($schoolPopulations ?? [], 'nom_etablissement'));
$chartData = json_encode(array_map(function ($row) {
    return (int) $row['total_personnels'];
}, $schoolPopulations ?? []));

$pageStyles = <<<STYLE
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<style>
  .chart-card { min-height: 360px; }
  .dataTables_wrapper .dt-buttons { margin-bottom: 1rem; }
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
    const stripHtml = function (value) {
      if (value === null || value === undefined) return '';
      return String(value)
        .replace(/<br\s*\/?>/gi, ' ')
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    };

    const exportTitles = {
      '#allUsersTable': 'Liste des utilisateurs',
      '#inactiveUsersTable': 'Comptes en attente',
      '#unassignedUsersTable': 'Comptes personnels à lier'
    };
    const currentSchoolLogoUrl = <?= json_encode($currentSchoolLogoUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const tableIds = ['#allUsersTable', '#inactiveUsersTable', '#unassignedUsersTable'];
    tableIds.forEach(function (selector) {
      const table = document.querySelector(selector);
      if (!table) return;
      $(selector).DataTable({
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            text: 'Excel',
            title: exportTitles[selector] || 'Export',
            messageTop: 'KC_SMA - Export généré le ' + new Date().toLocaleDateString('fr-FR'),
            exportOptions: {
              columns: ':visible',
              format: {
                body: function (data) {
                  return stripHtml(data);
                }
              }
            },
            customize: function (xlsx) {
              const sheet = xlsx.xl.worksheets['sheet1.xml'];
              $('row:first c', sheet).attr('s', '51');
            }
          },
          {
            extend: 'pdfHtml5',
            text: 'PDF',
            title: exportTitles[selector] || 'Export',
            messageTop: 'KC_SMA - Export généré le ' + new Date().toLocaleDateString('fr-FR'),
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: {
              columns: ':visible',
              format: {
                body: function (data) {
                  return stripHtml(data);
                }
              }
            },
            customize: function (doc) {
              doc.defaultStyle = { fontSize: 8, font: 'Helvetica' };
              doc.styles.title = { fontSize: 14, bold: true, color: '#0d6efd' };
              doc.styles.tableHeader = { fillColor: '#0d6efd', color: 'white', bold: true };

              const title = exportTitles[selector] || 'Export';
              const generatedDate = new Date().toLocaleDateString('fr-FR');

              const headerColumns = [];
              if (currentSchoolLogoUrl) {
                headerColumns.push({ image: currentSchoolLogoUrl, fit: [48, 48], alignment: 'left', width: 70, margin: [0, 0, 8, 0] });
              }
              headerColumns.push({
                stack: [
                  { text: 'KC_SMA', style: 'title' },
                  { text: 'Système de gestion scolaire', fontSize: 8, color: '#6c757d', margin: [0, 2, 0, 0] }
                ],
                width: '*'
              });
              headerColumns.push({ text: 'Rapport généré le ' + generatedDate, alignment: 'right', color: '#6c757d', width: 170 });

              doc.content = [
                {
                  columns: headerColumns,
                  margin: [0, 0, 0, 8]
                },
                {
                  text: title,
                  style: 'title',
                  margin: [0, 0, 0, 8]
                },
                {
                  text: 'Système de gestion scolaire - Vue centralisée des utilisateurs',
                  fontSize: 9,
                  color: '#495057',
                  margin: [0, 0, 0, 10]
                }
              ];

              const tableNode = doc.content.find(function (item) {
                return item && item.table;
              });
              if (!tableNode) {
                const existingContent = doc.content.slice(3);
                doc.content = doc.content.slice(0, 3).concat(existingContent);
              }

              const tableIndex = doc.content.findIndex(function (item) {
                return item && item.table;
              });
              if (tableIndex !== -1) {
                const tableItem = doc.content[tableIndex];
                tableItem.table.widths = Array(tableItem.table.body[0].length).fill('*');
                tableItem.table.body.forEach(function (row) {
                  row.forEach(function (cell) {
                    cell.margin = [0, 2, 0, 2];
                  });
                });
              }

              doc.content.push(
                {
                  canvas: [{
                    type: 'line',
                    x1: 0,
                    y1: 5,
                    x2: 515,
                    y2: 5,
                    lineWidth: 0.5,
                    lineColor: '#0d6efd'
                  }],
                  margin: [0, 10, 0, 0]
                },
                {
                  text: 'Document généré par KC_SMA • ' + generatedDate,
                  alignment: 'center',
                  fontSize: 7,
                  color: '#6c757d',
                  margin: [0, 4, 0, 0]
                }
              );
            }
          },
          {
            extend: 'print',
            text: 'Imprimer',
            title: exportTitles[selector] || 'Export',
            messageTop: 'KC_SMA - Export généré le ' + new Date().toLocaleDateString('fr-FR'),
            exportOptions: {
              columns: ':visible',
              format: {
                body: function (data) {
                  return stripHtml(data);
                }
              }
            }
          }
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        language: {
          search: 'Filtrer :',
          lengthMenu: 'Afficher _MENU_ enregistrements',
          info: 'Affichage de _START_ à _END_ sur _TOTAL_ enregistrements',
          infoEmpty: 'Aucun enregistrement disponible',
          zeroRecords: 'Aucun résultat trouvé',
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

<section class="content-header">
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
        <h1>Vue centralisée des utilisateurs</h1>
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

<section class="content">
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

    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Tous les comptes utilisateurs</h3>
      </div>
      <div class="card-body">
        <?php if (empty($allUsers)): ?>
          <div class="alert alert-info">Aucun utilisateur disponible.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table id="allUsersTable" class="table table-striped table-hover align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nom</th>
                  <th>Identifiant</th>
                  <th>Rôle</th>
                  <th>École</th>
                  <th>Logo</th>
                  <th></th>Statut</th>
                  <th>Type</th>
                  <th>Action</th>
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
                        <img src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo école" class="img-thumbnail" style="width:48px;height:48px;object-fit:cover;">
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
                      <?php if ((($account['statut'] ?? '') !== 'Actif') && (($role ?? '') === 'super_admin')): ?>
                        <form method="post" action="<?= BASE_URL ?>/utilisateurs/validate" style="display:inline">
                          <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-success">Valider</button>
                        </form>
                      <?php elseif ((in_array($account['role'] ?? '', ['agent_ecole', 'parent_ecole', 'enseignant_école'], true) && (empty($account['ecole_id']) || (int) $account['ecole_id'] === 0)) && (($role ?? '') === 'super_admin')): ?>
                        <form method="post" action="<?= BASE_URL ?>/utilisateurs/link" class="d-flex gap-2 align-items-center">
                          <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                          <select name="ecole_id" class="form-select form-select-sm" required>
                            <option value="">Choisir une école</option>
                            <?php foreach ($schools as $school): ?>
                              <option value="<?= (int) $school['id'] ?>"><?= htmlspecialchars($school['nom_etablissement']) ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="btn btn-sm btn-primary">Lier</button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted">Aucune action</span>
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
                          <?php if (($role ?? '') === 'super_admin'): ?>
                            <form method="post" action="<?= BASE_URL ?>/utilisateurs/validate" style="display:inline">
                              <input type="hidden" name="user_id" value="<?= (int) $inactive['id'] ?>">
                              <button type="submit" class="btn btn-sm btn-success">Valider</button>
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
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>
