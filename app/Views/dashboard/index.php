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
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box bg-primary text-white">
                <div class="inner">
                  <h3>150</h3>
                  <p>New Orders</p>
                </div>
                <div class="icon"><i class="bi bi-cart4"></i></div>
                <a href="#" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
            <div class="col-lg-3 col-6">
              <div class="small-box bg-success text-white">
                <div class="inner">
                  <h3>53<sup style="font-size: 0.6rem">%</sup></h3>
                  <p>Bounce Rate</p>
                </div>
                <div class="icon"><i class="bi bi-graph-up-arrow"></i></div>
                <a href="#" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
            <div class="col-lg-3 col-6">
              <div class="small-box bg-warning text-white">
                <div class="inner">
                  <h3>44</h3>
                  <p>User Registrations</p>
                </div>
                <div class="icon"><i class="bi bi-person-plus"></i></div>
                <a href="#" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
            <div class="col-lg-3 col-6">
              <div class="small-box bg-danger text-white">
                <div class="inner">
                  <h3>65</h3>
                  <p>Unique Visitors</p>
                </div>
                <div class="icon"><i class="bi bi-people-fill"></i></div>
                <a href="#" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="card card-primary card-outline">
                <div class="card-header">
                  <h3 class="card-title">Sales Value</h3>
                  <div class="card-tools">
                      <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i class="bi bi-dash"></i></button>
                  </div>
                </div>
                <div class="card-body">
                  <canvas id="dashboardChart" height="140"></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card card-primary card-outline">
                <div class="card-header">
                  <h3 class="card-title">Sales Value</h3>
                  <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i class="bi bi-dash"></i></button>
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="position-relative overflow-hidden" style="height: 100%; min-height: 320px; background:#007bff; border-bottom-left-radius:0.9rem; border-bottom-right-radius:0.9rem;">
                    <div class="position-absolute top-50 start-50 translate-middle text-white text-center">
                      <i class="bi bi-globe2" style="font-size:3rem;"></i>
                      <p class="mt-3 mb-0">Carte du monde</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <?php foreach ($modules as $module): ?>
              <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card card-outline shadow-sm h-100">
                  <div class="card-body text-center">
                    <div class="mb-3">
                      <i class="bi <?= htmlspecialchars($module['icon']) ?> text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1"><?= htmlspecialchars($module['name']) ?></h5>
                    <p class="text-muted small">Accédez à <?= htmlspecialchars($module['name']) ?>.</p>
                    <a href="<?= BASE_URL . $module['path'] ?>" class="btn btn-sm btn-outline-primary">Ouvrir</a>
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
            label: 'Sales Value',
            data: <?= json_encode($dashboardData['chart']['values'] ?? []) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.15)',
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
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
