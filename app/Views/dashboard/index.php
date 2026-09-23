<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Dashboard</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
              </ol>
            </div>
          </div>
        </div>
      <!-- </section> -->

      <section class="content">
        <div class="container-fluid">
          <?php if (($role ?? '') === 'agent_ecole' && (($user['statut'] ?? 'Actif') === 'Inactif') && (empty($user['ecole_id']) || (int) $user['ecole_id'] === 0)): ?>
            <div class="alert alert-warning border-0 shadow-sm mb-4">
              <strong>Compte en attente :</strong> votre compte agent est bien créé mais n’a pas encore été affecté à une école. L’administrateur de l’école doit le valider pour l’activer. Il sera automatiquement supprimé après 6 jours s’il n’est pas approuvé.
            </div>
          <?php endif; ?>
          <div class="row">
            <?php foreach ($dashboardData['stats'] ?? [] as $stat): ?>
              <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="small-box <?= htmlspecialchars($stat['bg'] ?? 'bg-primary') ?> text-white">
                  <div class="inner">
                    <h3><?= htmlspecialchars($stat['value'] ?? '0') ?></h3>
                    <p><?= htmlspecialchars($stat['title'] ?? '') ?></p>
                  </div>
                  <div class="icon"><i class="bi <?= htmlspecialchars($stat['icon'] ?? 'bi-bar-chart-line') ?>"></i></div>
                  <a href="#" class="small-box-footer"><?= htmlspecialchars($stat['hint'] ?? 'Détails') ?> <i class="bi bi-arrow-right"></i></a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="card card-primary card-outline">
                <div class="card-header">
                  <h3 class="card-title"><?= htmlspecialchars($dashboardData['chart']['title'] ?? 'Performance scolaire') ?></h3>
                  <div class="card-tools">
                      <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i class="bi bi-dash"></i></button>
                  </div>
                </div>
                <div class="card-body">
                  <canvas id="dashboardChart" height="230"></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card card-outline shadow-sm h-100">
                <div class="card-header bg-info">
                  <h3 class="card-title text-white"><?= htmlspecialchars($dashboardData['overview']['title'] ?? 'Aperçu rapide') ?></h3>
                </div>
                <div class="card-body">
                  <div class="mb-4">
                    <h5 class="mb-1"><?= htmlspecialchars($dashboardData['overview']['title'] ?? 'Vue synthétique') ?></h5>
                    <p class="text-muted">Suivi des points clés qui influencent la gestion quotidienne de l’établissement.</p>
                  </div>

                  <?php foreach ($dashboardData['overview']['items'] ?? [] as $item): ?>
                    <div class="progress-group mb-3">
                      <span class="progress-text"><?= htmlspecialchars($item['label'] ?? '') ?></span>
                      <span class="float-right"><b><?= htmlspecialchars($item['value'] ?? '0') ?></b></span>
                      <div class="progress progress-sm">
                        <div class="progress-bar <?= htmlspecialchars($item['color'] ?? 'bg-primary') ?>" style="width: <?= isset($item['value']) && is_numeric(str_replace(['%', ',', ' '], '', (string) $item['value'])) ? min(100, max(10, (int) str_replace(['%', ',', ' '], '', (string) $item['value']))) : 75 ?>%"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <?php if (($role ?? '') === 'ecole_admin'): ?>
          <div class="row mt-3">
            <div class="col-12 mb-4">
              <div class="card border-primary shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                  <h5 class="card-title mb-0">Portail de l’école</h5>
                </div>
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <a href="<?= BASE_URL ?>/ecoles" class="btn btn-outline-primary w-100 h-100 p-3">
                        <i class="bi bi-gear fs-3 d-block mb-2"></i>
                        <strong>Paramètres de l’école</strong>
                      </a>
                    </div>
                    <div class="col-md-6">
                      <a href="<?= BASE_URL ?>/paiements/gestionAutorisations" class="btn btn-outline-success w-100 h-100 p-3">
                        <i class="bi bi-shield-check fs-3 d-block mb-2"></i>
                        <strong>Accès paiements</strong>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card mt-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <span>Dettes par frais, classe, option et section</span>
              <a href="<?= BASE_URL ?>/recouvrements" class="btn btn-sm btn-outline-primary">Voir les recouvrements</a>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Frais</th>
                      <th>Classe</th>
                      <th>Option</th>
                      <th>Section</th>
                      <th class="text-end">Dette restante</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($dashboardData['accounting']['debtBreakdown'] ?? [] as $breakdown): ?>
                    <tr>
                      <td><?= htmlspecialchars($breakdown['type_frais'] ?? 'Frais scolaire') ?></td>
                      <td><?= htmlspecialchars($breakdown['nom_classe'] ?? 'Classe non définie') ?></td>
                      <td><?= htmlspecialchars($breakdown['nom_option'] ?? 'Sans option') ?></td>
                      <td><?= htmlspecialchars($breakdown['nom_section'] ?? 'Section non définie') ?></td>
                      <td class="text-end fw-semibold text-danger">
                        <?= number_format((float) ($breakdown['dette_restante'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($breakdown['devise'] ?? '') ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($dashboardData['accounting']['debtBreakdown'])): ?>
                    <tr><td colspan="5" class="text-center text-muted">Aucune dette restante.</td></tr>
                  <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if (($role ?? '') === 'comptable_école'): ?>
          <div class="row mt-3">
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card text-white bg-danger h-100">
                <div class="card-body">
                  <h5 class="card-title">Total dette</h5>
                  <h3 class="card-text"><?= number_format($dashboardData['accounting']['totalOutstanding'] ?? 0, 2) ?></h3>
                  <p class="small">Dette restante calculée par frais</p>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card text-white bg-success h-100">
                <div class="card-body">
                  <h5 class="card-title">Paiements (30j)</h5>
                  <h3 class="card-text"><?= number_format($dashboardData['accounting']['payments30d'] ?? 0, 2) ?></h3>
                  <p class="small">Total des paiements reçus (30 derniers jours)</p>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-12 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <h5 class="card-title">Actions rapides</h5>
                  <a href="<?= BASE_URL ?>/paiements/create" class="btn btn-primary btn-sm mb-2">Enregistrer paiement</a>
                  <a href="<?= BASE_URL ?>/frais/create" class="btn btn-secondary btn-sm mb-2">Créer un frais</a>
                  <a href="<?= BASE_URL ?>/comptes_eleves" class="btn btn-info btn-sm mb-2">Voir comptes élèves</a>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="card">
                <div class="card-header bg-light">Paiements récents</div>
                <div class="card-body p-0">
                  <table class="table table-striped mb-0">
                    <thead>
                      <tr><th>Réf</th><th>Élève</th><th>Date</th><th>Montant</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardData['accounting']['recentPayments'] ?? [] as $p): ?>
                      <tr>
                        <td><?= htmlspecialchars($p['reference_recu'] ?? '') ?></td>
                        <td><?= htmlspecialchars(trim(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['postnom'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars($p['date_operation'] ?? '') ?></td>
                        <td><?= number_format((float) ($p['montant'] ?? 0), 2) ?></td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dashboardData['accounting']['recentPayments'])): ?>
                      <tr><td colspan="4" class="text-center text-muted">Aucun paiement récent.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card">
                <div class="card-header bg-light">Top débiteurs</div>
                <div class="card-body p-0">
                  <ul class="list-group list-group-flush">
                    <?php foreach ($dashboardData['accounting']['topDebtors'] ?? [] as $d): ?>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><?= htmlspecialchars(trim(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '') . ' ' . ($d['postnom'] ?? ''))) ?></div>
                        <span class="badge bg-danger"><?= number_format((float) ($d['debt'] ?? 0), 2) ?></span>
                      </li>
                    <?php endforeach; ?>
                    <?php if (empty($dashboardData['accounting']['topDebtors'])): ?>
                      <li class="list-group-item text-center text-muted">Aucun débiteur trouvé.</li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <div class="row">
            <?php foreach ($modules as $module): ?>
              <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card card-outline shadow-sm h-100 border-0">
                  <div class="card-body text-center">
                    <div class="mb-3">
                      <i class="bi <?= htmlspecialchars($module['icon']) ?> text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1"><?= htmlspecialchars($module['name']) ?></h5>
                    <p class="text-muted small">Accédez à <?= htmlspecialchars($module['name']) ?>.</p>
                    <a href="<?= BASE_URL . $module['path'] ?>" class="btn btn-sm btn-primary rounded-pill">Ouvrir</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
<?php $pageScripts = <<<'SCRIPT'
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const chartCtx = document.getElementById('dashboardChart');
    if (chartCtx) {
      const chartData = <?= json_encode($dashboardData['chart'] ?? []) ?>;
      const datasets = chartData.datasets && chartData.datasets.length
        ? chartData.datasets
        : [{
            label: chartData.label || 'Indicateur scolaire',
            data: chartData.values || [],
            borderColor: chartData.borderColor || '#0d6efd',
            backgroundColor: chartData.backgroundColor || 'rgba(13, 110, 253, 0.15)',
            tension: 0.3,
            fill: false,
          }];

      new Chart(chartCtx, {
        type: 'line',
        data: {
          labels: chartData.labels || [],
          datasets: datasets
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: chartData.datasets && chartData.datasets.length > 1 },
            tooltip: { mode: 'index', intersect: false }
          },
          scales: { y: { beginAtZero: true } }
        }
      });
    }

    const searchToggle = document.getElementById('navbarSearchToggle');
    const searchForm = document.getElementById('navbarSearchForm');
    const searchClose = document.getElementById('navbarSearchClose');

    if (searchToggle && searchForm) {
      searchToggle.addEventListener('click', function (event) {
        event.preventDefault();
        searchForm.classList.toggle('d-none');
        if (!searchForm.classList.contains('d-none')) {
          searchForm.querySelector('input[type="search"]')?.focus();
        }
      });
    }

    if (searchClose && searchForm) {
      searchClose.addEventListener('click', function () {
        searchForm.classList.add('d-none');
      });
    }
  });
</script>
SCRIPT;
?>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
