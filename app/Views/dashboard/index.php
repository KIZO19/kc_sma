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
                  <h3 class="card-title text-white">Aperçu rapide</h3>
                </div>
                <div class="card-body">
                  <div class="mb-4">
                    <h5 class="mb-1">Vue synthétique</h5>
                    <p class="text-muted">Suivi des commandes, présences et performances en temps réel.</p>
                  </div>
                  <div class="progress-group mb-3">
                    <span class="progress-text">Taux de présence</span>
                    <span class="float-right"><b>92%</b></span>
                    <div class="progress progress-sm">
                      <div class="progress-bar bg-success" style="width:92%"></div>
                    </div>
                  </div>
                  <div class="progress-group mb-3">
                    <span class="progress-text">Satisfaction</span>
                    <span class="float-right"><b>87%</b></span>
                    <div class="progress progress-sm">
                      <div class="progress-bar bg-warning" style="width:87%"></div>
                    </div>
                  </div>
                  <div class="progress-group">
                    <span class="progress-text">Paiements traités</span>
                    <span class="float-right"><b>74%</b></span>
                    <div class="progress progress-sm">
                      <div class="progress-bar bg-primary" style="width:74%"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

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
      new Chart(chartCtx, {
        type: 'line',
        data: {
          labels: <?= json_encode($dashboardData['chart']['labels'] ?? []) ?>,
          datasets: [{
            label: <?= json_encode($dashboardData['chart']['label'] ?? 'Indicateur scolaire') ?>,
            data: <?= json_encode($dashboardData['chart']['values'] ?? []) ?>,
            borderColor: <?= json_encode($dashboardData['chart']['borderColor'] ?? '#0d6efd') ?>,
            backgroundColor: <?= json_encode($dashboardData['chart']['backgroundColor'] ?? 'rgba(13, 110, 253, 0.15)') ?>,
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
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
