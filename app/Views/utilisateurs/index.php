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
    const tableIds = ['#inactiveUsersTable', '#unassignedUsersTable'];
    tableIds.forEach(function (selector) {
      const table = document.querySelector(selector);
      if (!table) return;
      $(selector).DataTable({
        dom: 'Bfrtip',
        buttons: [
          { extend: 'excelHtml5', text: 'Excel' },
          { extend: 'pdfHtml5', text: 'PDF' },
          { extend: 'print', text: 'Imprimer' }
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
        <h1>Validation des comptes</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Validation des comptes</li>
        </ol>
      </div>
    </div>
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
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Comptes en attente</h3>
      </div>
      <div class="card-body">
        <?php if (empty($inactiveUsers)): ?>
          <div class="alert alert-info">Aucun compte en attente de validation.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nom</th>
                  <th>Identifiant</th>
                  <th>Rôle</th>
                  <th>Date de création</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($inactiveUsers as $inactive): ?>
                  <tr>
                    <td><?= (int) $inactive['id'] ?></td>
                    <td><?= htmlspecialchars($inactive['nom_complet']) ?></td>
                    <td><?= htmlspecialchars($inactive['identifiant']) ?></td>
                    <td><?= htmlspecialchars(\App\Models\User::getRoleLabel($inactive['role'])) ?></td>
                    <td><?= htmlspecialchars($inactive['created_at']) ?></td>
                    <td>
                      <form method="post" action="<?= BASE_URL ?>/utilisateurs/validate" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= (int) $inactive['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">Valider</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h3 class="card-title">Lier les comptes personnels à une école</h3>
      </div>
      <div class="card-body">
        <?php if (empty($unassignedUsers)): ?>
          <div class="alert alert-info">Aucun agent, parent ou enseignant non lié à une école.</div>
        <?php elseif (empty($schools)): ?>
          <div class="alert alert-warning">Aucune école disponible pour le lien.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nom</th>
                  <th>Identifiant</th>
                  <th>Rôle</th>
                  <th>Associer à l'école</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($unassignedUsers as $unassigned): ?>
                  <tr>
                    <td><?= (int) $unassigned['id'] ?></td>
                    <td><?= htmlspecialchars($unassigned['nom_complet']) ?></td>
                    <td><?= htmlspecialchars($unassigned['identifiant']) ?></td>
                    <td><?= htmlspecialchars(\App\Models\User::getRoleLabel($unassigned['role'])) ?></td>
                    <td>
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
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>
