<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="icon" href="<?= BASE_URL ?>/assets/favicon.ico" type="image/x-icon">
  <link rel="alternate icon" href="<?= BASE_URL ?>/assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" crossorigin="anonymous">
  <style>
    :root {
      color-scheme: light;
      --bg: #f4f7fb;
      --surface: #ffffff;
      --surface-muted: #f8f9fb;
      --text: #1f2937;
      --text-muted: #6c757d;
      --card: #ffffff;
      --navbar: #ffffff;
      --navbar-text: #495057;
      --sidebar: #172230;
      --sidebar-surface: rgba(255,255,255,.05);
      --sidebar-text: #cfd8e3;
      --sidebar-text-active: #ffffff;
      --sidebar-active: #1f6feb;
      --border: rgba(0,0,0,.08);
    }

    [data-theme="dark"] {
      color-scheme: dark;
      --bg: #121827;
      --surface: #1e293b;
      --surface-muted: #172232;
      --text: #e2e8f0;
      --text-muted: #94a3b8;
      --card: #1f2a44;
      --navbar: #111827;
      --navbar-text: #e2e8f0;
      --sidebar: #0f172a;
      --sidebar-surface: rgba(255,255,255,.05);
      --sidebar-text: #cbd5e1;
      --sidebar-text-active: #ffffff;
      --sidebar-active: #3b82f6;
      --border: rgba(255,255,255,.12);
    }

    [data-theme="light"] {
      color-scheme: light;
      --bg: #f4f7fb;
      --surface: #ffffff;
      --surface-muted: #f8f9fb;
      --text: #1f2937;
      --text-muted: #6c757d;
      --card: #ffffff;
      --navbar: #ffffff;
      --navbar-text: #495057;
      --sidebar: #172230;
      --sidebar-surface: rgba(255,255,255,.05);
      --sidebar-text: #cfd8e3;
      --sidebar-text-active: #ffffff;
      --sidebar-active: #1f6feb;
      --border: rgba(0,0,0,.08);
    }

    body {
      background: var(--bg);
      color: var(--text);
    }
    .app-header { background: var(--navbar); }
    .app-header .navbar-nav .nav-link { color: var(--navbar-text); }
    .app-sidebar {
      background: var(--sidebar);
      min-height: 100vh;
      border-right: 1px solid var(--border);
    }
    .app-sidebar .brand-link {
      background: var(--sidebar-surface);
      color: var(--sidebar-text-active);
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .app-sidebar .sidebar {
      padding-top: 0.5rem;
    }
    .app-sidebar .user-panel {
      background: rgba(255,255,255,.04);
      border-radius: 0.75rem;
      margin: 0.75rem 0;
      padding: 0.75rem;
    }
    .app-sidebar .user-panel .info a { color: var(--sidebar-text-active); }
    .app-sidebar .user-panel .info span { color: var(--sidebar-text); }
    .app-sidebar .nav-sidebar .nav-link {
      color: var(--sidebar-text);
    }
    .app-sidebar .nav-sidebar .nav-link:hover {
      background: rgba(255,255,255,.08);
      color: var(--sidebar-text-active);
    }
    .app-sidebar .nav-sidebar .nav-link.active {
      background-color: var(--sidebar-active);
      color: var(--sidebar-text-active);
    }
    .app-sidebar .nav-header {
      color: #a2b1c4;
      margin-top: 1rem;
    }
    .brand-link { font-weight: 700; }
    .small-box { border-radius: 0.75rem; }
    .card { border-radius: 0.9rem; background: var(--card); border-color: var(--border); }
    .content-wrapper { min-height: calc(100vh - 56px); background: var(--surface-muted); }
    /* Layout shifts: reserve space for sidebar on wide screens */
    body:not(.sidebar-open) .content-wrapper { margin-left: 220px; transition: margin .25s ease; }
    body.sidebar-collapse .content-wrapper { margin-left: 64px; }
    @media (max-width: 767.98px) {
      body:not(.sidebar-open) .content-wrapper { margin-left: 0; }
    }
    .breadcrumb .breadcrumb-item a { color: var(--text); }
    .breadcrumb .breadcrumb-item.active { color: var(--text-muted); }
  </style>
  <style>
    /* Responsive tweaks */
    @media (max-width: 767.98px) {
      .brand-text { display: none; }
      /* mobile: sidebar hidden by default, slides in as overlay */
      .app-sidebar { width: 220px; transform: translateX(-100%); position: fixed; left: 0; top: 0; bottom: 0; z-index: 1050; transition: transform .25s ease; }
      .app-sidebar .nav-sidebar .nav-link p { display: none; }
      .app-sidebar .user-panel .info { display: none; }
      body.sidebar-open .app-sidebar { transform: translateX(0); }
      .sidebar-backdrop { display: none; }
      body.sidebar-open .sidebar-backdrop { display: block; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1040; }
      .content-wrapper { min-height: calc(100vh - 56px); padding: 0.75rem; }
      .app-header .nav-link { padding: .35rem .5rem; }
      .app-header .rounded-circle { width: 34px !important; height: 34px !important; }
    }
    @media (min-width: 768px) {
      .app-sidebar { width: 220px; }
    }
  </style>
    <!-- Backdrop for mobile sidebar -->
    <div id="sidebarBackdrop" class="sidebar-backdrop" style="display:none;"></div>
</head>
<body class="layout-fixed sidebar-mini">
<?php
$modules = $modules ?? [];
$role = $role ?? '';
$roleLabel = $roleLabel ?? '';
$user = $user ?? ['nom_complet' => 'Utilisateur'];
?>
  <div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
      <div class="container-fluid">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a>
          </li>
          <li class="nav-item d-none d-md-block">
            <a href="<?= BASE_URL ?>/dashboard" class="nav-link">Dashboard</a>
          </li>
          <li class="nav-item d-none d-md-block">
            <a href="<?= BASE_URL ?>/ecoles" class="nav-link">Écoles</a>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" href="#" role="button">
                      <?php
                      $avatarUrl = null;
                      // Prefer explicit avatar/photo from DB
                      if (!empty($user['avatar'] ?? '')) {
                        $avatarUrl = $user['avatar'];
                      } elseif (!empty($user['photo'] ?? '')) {
                        $avatarUrl = $user['photo'];
                      } else {
                        // Fallback: check public/uploads/avatars/{id}.{ext}
                        $projectRoot = dirname(__DIR__, 3);
                        $avatarDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
                        $uid = $user['id'] ?? null;
                        if ($uid) {
                          foreach (['png','jpg','jpeg','webp','gif'] as $ext) {
                            $path = $avatarDir . DIRECTORY_SEPARATOR . $uid . '.' . $ext;
                            if (file_exists($path)) {
                              $avatarUrl = BASE_URL . '/uploads/avatars/' . $uid . '.' . $ext;
                              break;
                            }
                          }
                        }
                      }

                      if (!empty($avatarUrl)):
                      ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="rounded-circle" style="width:42px;height:42px;object-fit:cover;">
                      <?php else: ?>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;"><i class="bi bi-person-fill"></i></div>
                      <?php endif; ?>
                      <span class="ms-2 d-none d-md-inline fw-semibold"><?php echo htmlspecialchars($user['nom_complet'] ?? 'Utilisateur'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile"><i class="bi bi-person-circle me-2"></i>Mon profil</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                    </ul>
                  </li>
          <li class="nav-item">
            <a class="nav-link" id="navbarSearchToggle" href="#" role="button"><i class="bi bi-search"></i></a>
          </li>
          <li class="nav-item navbar-search d-none" id="navbarSearchForm">
            <form class="d-flex" role="search">
              <input class="form-control form-control-navbar me-2" type="search" placeholder="Recherche" aria-label="Recherche">
              <button class="btn btn-navbar" type="button" id="navbarSearchClose"><i class="bi bi-x-lg"></i></button>
            </form>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button" id="themeModeToggle">
              <i class="bi bi-moon-stars" id="themeModeIcon"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeModeToggle">
              <li><button class="dropdown-item" type="button" data-theme-mode="light"><i class="bi bi-sun me-2"></i>Light</button></li>
              <li><button class="dropdown-item" type="button" data-theme-mode="dark"><i class="bi bi-moon-fill me-2"></i>Dark</button></li>
              <li><button class="dropdown-item" type="button" data-theme-mode="auto"><i class="bi bi-circle-half me-2"></i>Auto</button></li>
              <li><hr class="dropdown-divider"></li>
              <li class="px-3 py-2">
                <div class="d-flex align-items-center">
                  <label class="me-2 mb-0 small">Couleur sidebar</label>
                  <input type="color" id="sidebarColorInput" value="#172230" title="Choisir la couleur de la sidebar">
                  <button class="btn btn-sm btn-link ms-2" id="sidebarColorReset">Réinitialiser</button>
                </div>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-chat-text"></i>
              <span class="navbar-badge badge text-bg-danger">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <a href="#" class="dropdown-item">
                <div class="d-flex">
                  <div class="flex-shrink-0">
                    <img src="<?= BASE_URL ?>/assets/user1-128x128.jpg" alt="User Avatar" class="img-size-50 rounded-circle me-3">
                  </div>
                  <div class="flex-grow-1">
                    <h3 class="dropdown-item-title">Brad Diesel <span class="float-end fs-7 text-danger"><i class="bi bi-star-fill"></i></span></h3>
                    <p class="fs-7">Call me whenever you can...</p>
                    <p class="fs-7 text-secondary"><i class="bi bi-clock-fill me-1"></i> 4 Hours Ago</p>
                  </div>
                </div>
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">See All Messages</a>
            </div>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-bell-fill"></i>
              <span class="navbar-badge badge text-bg-warning">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-2">
              <span class="dropdown-item-text fw-bold">Notifications</span>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="bi bi-envelope-fill me-2"></i> Nouveau message reçu
                <span class="float-end text-muted small">2m</span>
              </a>
              <a href="#" class="dropdown-item">
                <i class="bi bi-person-check-fill me-2"></i> Compte confirmé
                <span class="float-end text-muted small">1h</span>
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item text-center">Voir toutes les notifications</a>
            </div>
          </li>
        </ul>
      </div>
    </nav>

    <aside class="app-sidebar">
      <a href="<?= BASE_URL ?>/dashboard" class="brand-link d-flex align-items-center p-3">
        <img src="<?= BASE_URL ?>/assets/favicon.ico" alt="Logo" class="me-2 rounded-circle" style="width:36px;height:36px;">
        <span class="brand-text h5 mb-0">AdminKC</span>
      </a>

      <div class="sidebar p-3">
        <div class="user-panel d-flex align-items-center mb-3">
          <div class="image me-2">
            <?php if (!empty($avatarUrl)): ?>
              <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="rounded-circle" style="width:42px;height:42px;object-fit:cover;">
            <?php else: ?>
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;"><i class="bi bi-person-fill"></i></div>
            <?php endif; ?>
          </div>
          <div class="info">
            <a href="<?= BASE_URL ?>/profile" class="d-block fw-bold small"><?= htmlspecialchars($user['nom_complet'] ?? 'Utilisateur') ?></a>
            <span class="small text-muted"><?= htmlspecialchars($roleLabel ?? '') ?></span>
          </div>
        </div>

        <div class="mb-3">
          <div class="input-group">
            <input class="form-control form-control-sidebar" type="search" placeholder="Recherche" aria-label="Search">
            <button class="btn btn-outline-light"><i class="bi bi-search"></i></button>
          </div>
        </div>

        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-lte-treeview="true" role="menu" data-accordion="false">
            <li class="nav-header">MENU</li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/dashboard" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '') === BASE_URL . '/dashboard' ? 'active' : '' ?>">
                <i class="nav-icon bi bi-speedometer2"></i>
                <p>Tableau de bord</p>
              </a>
            </li>

            <?php if (!empty($modules) && is_array($modules)): ?>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link" data-lte-toggle="treeview">
                  <i class="nav-icon bi bi-grid-1x2"></i>
                  <p>Modules <i class="bi bi-caret-down-fill float-end"></i></p>
                </a>
                <ul class="nav nav-treeview ps-3">
                  <?php foreach ($modules as $module): ?>
                    <li class="nav-item">
                      <a href="<?= BASE_URL . $module['path'] ?>" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '') === BASE_URL . $module['path'] ? 'active' : '' ?>">
                        <i class="nav-icon <?= htmlspecialchars($module['icon']) ?>"></i>
                        <p><?= htmlspecialchars($module['name']) ?></p>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endif; ?>

            <?php if (($role ?? '') === 'super_admin'): ?>
              <li class="nav-header">ADMINISTRATION</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/ecoles') !== false) ? 'active' : '' ?>" data-lte-toggle="treeview">
                  <i class="nav-icon bi bi-building"></i>
                  <p>Gestion des écoles <i class="bi bi-caret-down-fill float-end"></i></p>
                </a>
                <ul class="nav nav-treeview ps-3">
                  <li class="nav-item">
                    <a href="<?= BASE_URL ?>/ecoles" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/ecoles') !== false) ? 'active' : '' ?>">
                      <i class="nav-icon bi bi-list"></i>
                      <p>Liste des écoles</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= BASE_URL ?>/ecoles#create" class="nav-link">
                      <i class="nav-icon bi bi-plus-square"></i>
                      <p>Créer une école</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= BASE_URL ?>/ecoles/edit" class="nav-link">
                      <i class="nav-icon bi bi-pencil-square"></i>
                      <p>Modifier une école</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= BASE_URL ?>/ecoles/requests" class="nav-link">
                      <i class="nav-icon bi bi-hourglass-split"></i>
                      <p>Demandes en attente</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/comptes" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/comptes') !== false) ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-person-badge"></i>
                  <p>Gestion des comptes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/abonnements" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/abonnements') !== false) ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-card-checklist"></i>
                  <p>Abonnements</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/ecoles/generatePassword" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/ecoles/generatePassword') !== false) ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-key"></i>
                  <p>Générer mot de passe élève</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/activities" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/activities') !== false) ? 'active' : '' ?>">
                  <i class="nav-icon bi bi-activity"></i>
                  <p>Activités</p>
                </a>
              </li>
            <?php endif; ?>

            <?php if (in_array($role ?? '', ['ecole_admin', 'comptable_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole'], true)): ?>
              <li class="nav-header">ACTIONS RAPIDES</li>
              <?php if (($role ?? '') === 'ecole_admin'): ?>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/agents" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/agents') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-people-fill"></i>
                    <p>Équipe & agents</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/paiements" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/paiements') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-currency-dollar"></i>
                    <p>Paiements</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/ecoles/generatePassword" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/ecoles/generatePassword') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-key"></i>
                    <p>Générer mot de passe élève</p>
                  </a>
                </li>
              <?php elseif (($role ?? '') === 'comptable_école'): ?>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/paiements" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/paiements') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-wallet2"></i>
                    <p>Enregistrer un paiement</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/rapports" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/rapports') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                    <p>Voir les rapports</p>
                  </a>
                </li>
              <?php elseif (($role ?? '') === 'sec_école'): ?>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/inscriptions" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/inscriptions') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-person-plus"></i>
                    <p>Nouvelle inscription</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/parents" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/parents') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-people"></i>
                    <p>Gérer les parents</p>
                  </a>
                </li>
              <?php elseif (($role ?? '') === 'enseignant_école'): ?>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/notes" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/notes') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-pencil-square"></i>
                    <p>Entrer les notes</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/presences" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/presences') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-calendar-check"></i>
                    <p>Suivi des présences</p>
                  </a>
                </li>
              <?php elseif (($role ?? '') === 'eleve_ecole'): ?>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/mes-notes" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/mes-notes') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-file-earmark-text"></i>
                    <p>Consulter mes notes</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/mes-presences" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/mes-presences') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-check2-square"></i>
                    <p>Voir mes présences</p>
                  </a>
                </li>
              <?php elseif (($role ?? '') === 'parent_ecole'): ?>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/mes-enfants" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/mes-enfants') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-people"></i>
                    <p>Suivi des enfants</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= BASE_URL ?>/bulletins-enfant" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/bulletins-enfant') !== false) ? 'active' : '' ?>">
                    <i class="nav-icon bi bi-file-earmark-text"></i>
                    <p>Voir les bulletins</p>
                  </a>
                </li>
              <?php endif; ?>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </aside>

    <div class="content-wrapper">
