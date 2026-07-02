<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="KC_SMA est une solution professionnelle de gestion scolaire pour écoles, collèges et établissements d’enseignement.">
  <meta name="keywords" content="gestion scolaire, école, présences, bulletins, horaires, frais">
  <meta name="author" content="KC_SMA">
  <title><?= $title ?> | Dashboard</title>
  <link rel="icon" href="<?= BASE_URL ?>/assets/favicon.ico" type="image/x-icon">
  <link rel="alternate icon" href="<?= BASE_URL ?>/assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css" crossorigin="anonymous">
  <style>
    body { background: #f4f7fb; }
    .app-header { border-bottom: 1px solid rgba(0,0,0,0.08); }
    .app-sidebar { background: #343a40; }
    .app-sidebar .menu-title, .app-sidebar .nav-link { color: #cfd8dc; }
    .app-sidebar .nav-link.active, .app-sidebar .nav-link:hover { background: rgba(255,255,255,0.08); color: #fff; }
    .app-sidebar .brand-link { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .app-sidebar .brand-link .brand-text { color: #fff; font-size: 1.1rem; }
    .app-sidebar .user-panel { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .app-sidebar .user-panel .info a { color: #fff; }
    .info-box { border-radius: 1rem; }
    .card { border-radius: 1rem; }
    .content-wrapper { min-height: calc(100vh - 56px); }
    .breadcrumb-item + .breadcrumb-item::before { content: ">"; }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg">
<div class="app-wrapper">
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
            <i class="bi bi-list"></i>
          </a>
        </li>
        <li class="nav-item d-none d-md-block">
          <a href="#" class="nav-link"><i class="bi bi-grid-1x2 me-1"></i>Live preview</a>
        </li>
        <li class="nav-item d-none d-md-block">
          <a href="#" class="nav-link"><i class="bi bi-book me-1"></i>Documentation</a>
        </li>
      </ul>

      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="bi bi-search"></i>
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link" data-bs-toggle="dropdown" href="#">
            <i class="bi bi-chat-text"></i>
            <span class="badge rounded-pill bg-danger">3</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
            <a href="#" class="dropdown-item">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <img src="<?= BASE_URL ?>/assets/user1-128x128.jpg" alt="User Avatar" class="img-size-50 rounded-circle me-3" />
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1">Brad Diesel <span class="text-danger"><i class="bi bi-star-fill"></i></span></h6>
                  <p class="mb-1 small text-muted">Call me whenever you can...</p>
                  <p class="mb-0 small text-secondary"><i class="bi bi-clock-fill me-1"></i>4 Hours Ago</p>
                </div>
              </div>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link" data-bs-toggle="dropdown" href="#">
            <i class="bi bi-bell-fill"></i>
            <span class="badge rounded-pill bg-warning">15</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
            <span class="dropdown-header">15 Notifications</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item"><i class="bi bi-envelope me-2"></i>4 new messages<span class="float-end text-secondary small">3 mins</span></a>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button"><i class="bi bi-arrows-fullscreen"></i></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button"><i class="bi bi-layout-sidebar"></i></a>
        </li>
      </ul>
    </div>
  </nav>

  <aside class="app-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link d-flex align-items-center justify-content-center">
      <i class="bi bi-mortarboard-fill me-2"></i>
      <span class="brand-text">KC_SMA</span>
    </a>
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <i class="bi bi-buildings-fill text-white fs-2"></i>
        </div>
        <div class="info">
          <a href="#" class="d-block">KC_SMA Admin</a>
          <span class="d-block small text-muted">Gestion scolaire</span>
        </div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-header">TABLEAU DE BORD</li>
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon bi bi-speedometer2"></i>
              <p>Dashboard <i class="right bi bi-chevron-down"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="#" class="nav-link active"><i class="bi bi-circle"></i><p>Dashboard v1</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-circle"></i><p>Dashboard v2</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-circle"></i><p>Dashboard v3</p></a></li>
            </ul>
          </li>
          <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-palette"></i><p>Theme Generate</p></a></li>
          <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-grid-3x3-gap"></i><p>Widgets</p></a></li>
          <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-layout-sidebar"></i><p>Layout Options</p></a></li>
          <li class="nav-header">PAGES</li>
          <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-file-earmark-text"></i><p>Pages</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <main class="app-main">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
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
            <div class="small-box bg-primary">
              <div class="inner"><h3>150</h3><p>New Orders</p></div>
              <div class="icon"><i class="bi bi-cart-fill"></i></div>
              <a href="#" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner"><h3>53<sup style="font-size:20px">%</sup></h3><p>Bounce Rate</p></div>
              <div class="icon"><i class="bi bi-graph-up-arrow"></i></div>
              <a href="#" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner"><h3>44</h3><p>User Registrations</p></div>
              <div class="icon"><i class="bi bi-person-plus-fill"></i></div>
              <a href="#" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner"><h3>65</h3><p>Unique Visitors</p></div>
              <div class="icon"><i class="bi bi-pie-chart-fill"></i></div>
              <a href="#" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-header"><h3 class="card-title">Sales Value</h3></div>
              <div class="card-body">
                <div id="salesChart" style="min-height: 300px;"></div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card card-secondary card-outline">
              <div class="card-header"><h3 class="card-title">Sales Value</h3></div>
              <div class="card-body">
                <div id="worldMap" style="height: 300px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
</div>

<script src="<?= BASE_URL ?>/assets/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js" crossorigin="anonymous"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const options = {
      chart: { type: 'area', height: 300, toolbar: { show: false } },
      series: [{ name: 'Sales', data: [30, 45, 30, 45, 55, 40, 60, 50, 70, 65, 90] }],
      stroke: { curve: 'smooth', width: 3 },
      xaxis: { categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov'] },
      colors: ['#007bff'],
      grid: { borderColor: '#e9ecef', strokeDashArray: 4 },
      dataLabels: { enabled: false },
      tooltip: { theme: 'light' }
    };
    new ApexCharts(document.querySelector('#salesChart'), options).render();

    new jsVectorMap({
      selector: '#worldMap',
      map: 'world',
      zoomButtons: false,
      regionStyle: { initial: { fill: '#cfe2ff' } },
      markerStyle: { initial: { fill: '#0d6efd', stroke: '#fff' } },
    });
  });
</script>
</body>
</html>
