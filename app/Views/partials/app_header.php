<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  if (!function_exists('formatDate')) {
      function formatDate(?string $date): string
      {
          if (empty($date)) {
              return '-';
          }

          try {
              $dt = new \DateTime($date);
              return $dt->format('d/m/Y');
          } catch (\Exception $e) {
              return trim((string) $date) ?: '-';
          }
      }
  }
  $title = $title ?? APP_NAME; ?>
  <?php
  if (!empty($user['id']) && empty($_SESSION['push_notifications_csrf'])) {
      $_SESSION['push_notifications_csrf'] = bin2hex(random_bytes(32));
  }
  if (AI_CHAT_ENABLED && !empty($user['id']) && empty($_SESSION['ai_chat_csrf'])) {
      $_SESSION['ai_chat_csrf'] = bin2hex(random_bytes(32));
  }
  $faviconUrl = BASE_URL . '/assets/kc-logo.svg';
  $faviconMime = 'image/svg+xml';
  $schoolForHeader = null;
  if (!empty($user['ecole_id'])) {
      $schoolForHeader = \App\Models\Ecole::findById((int) $user['ecole_id']);
  }
  if (!empty($schoolForHeader['logo_url'])) {
      $faviconUrl = $schoolForHeader['logo_url'];
      if (strpos($faviconUrl, 'http') !== 0) {
          $faviconUrl = BASE_URL . '/' . ltrim($faviconUrl, '/');
      }
      if (preg_match('/\.png$/i', $faviconUrl)) {
          $faviconMime = 'image/png';
      } elseif (preg_match('/\.svg$/i', $faviconUrl)) {
          $faviconMime = 'image/svg+xml';
      }
  }
  ?>
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="theme-color" content="#3c8dbc">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(APP_NAME) ?>">
  <link rel="manifest" href="<?= BASE_URL ?>/manifest.php">
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icons/kc-sma-192.png">
  <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>" type="<?= htmlspecialchars($faviconMime) ?>">
  <link rel="shortcut icon" href="<?= htmlspecialchars($faviconUrl) ?>" type="<?= htmlspecialchars($faviconMime) ?>">
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($faviconUrl) ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/app-datagrid.css">
  <?php if (AI_CHAT_ENABLED && !empty($user['id'])): ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/ai-chat.css">
  <?php endif; ?>
  <?= $pageStyles ?? '' ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/responsive.css">
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
      --sidebar: #3c8dbc;
      --sidebar-surface: #367fa9;
      --sidebar-text: #ffffff;
      --sidebar-text-active: #ffffff;
      --sidebar-active: #3c8dbc;
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
      --sidebar: #3c8dbc;
      --sidebar-surface: #367fa9;
      --sidebar-text: #ffffff;
      --sidebar-text-active: #ffffff;
      --sidebar-active: #3c8dbc;
      --border: rgba(0,0,0,.08);
    }

    body {
      background: var(--bg);
      color: var(--text);
    }
    .live-notification-toasts {
      position: fixed;
      top: 4.5rem;
      right: 1rem;
      z-index: 2000;
      display: grid;
      gap: .5rem;
      width: min(24rem, calc(100vw - 2rem));
      pointer-events: none;
    }
    .live-notification-toast {
      pointer-events: auto;
      box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.2);
    }
    .app-header {
      background: var(--navbar);
      position: sticky;
      top: 0;
      z-index: 1050;
      width: 100%;
    }
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
    .app-sidebar .school-context-card {
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: .6rem;
    }
    .app-sidebar .sidebar {
      padding-top: 0.5rem;
    }
    .app-sidebar .nav-header {
      padding: .85rem .65rem .45rem;
      color: rgba(255,255,255,.72);
      font-size: .64rem;
      font-weight: 800;
      letter-spacing: .1em;
    }
    .app-sidebar .nav-sidebar .nav-link {
      margin: .12rem 0;
      padding: .68rem .75rem;
      border-radius: .65rem;
      color: var(--sidebar-text);
      transition: background-color .15s ease, color .15s ease, transform .15s ease;
    }
    .app-sidebar .nav-sidebar .nav-link:hover {
      color: var(--sidebar-text-active);
      background: rgba(0,0,0,.12);
      color: #fff;
    }
    .app-sidebar .nav-sidebar .nav-link.active {
      color: var(--sidebar-text-active);
      background: linear-gradient(100deg, var(--sidebar-active), #367fa9);
      box-shadow: 0 .35rem .8rem rgba(0,0,0,.15);
    }
    .app-sidebar .nav-treeview {
      margin: .2rem 0 .45rem .25rem;
      padding-left: .45rem !important;
      border-left: 1px solid rgba(255,255,255,.28);
    }
    .app-sidebar .nav-treeview .nav-link {
      padding-top: .55rem;
      padding-bottom: .55rem;
      font-size: .9rem;
    }
    .app-sidebar .sidebar-role-label {
      color: #6c757d;
      font-size: .68rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
    }
    .app-sidebar .user-panel {
      background: rgba(255,255,255,.1);
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
      background: rgba(0,0,0,.12);
      color: #fff;
    }
    .app-sidebar .nav-sidebar .nav-link.active {
      background-color: var(--sidebar-active);
      color: #fff;
    }
    .app-sidebar .nav-sidebar .nav-treeview {
      display: none;
    }
    .app-sidebar .nav-sidebar .menu-open > .nav-treeview {
      display: block;
    }
    .app-sidebar .nav-header {
      color: rgba(255,255,255,.72);
      margin-top: 1rem;
    }
    .brand-link { font-weight: 700; }
    .small-box { border-radius: 0.75rem; }
    .card { border-radius: 0.9rem; background: var(--card); border-color: var(--border); }
    .content-wrapper { min-height: calc(100vh - 56px); background: var(--surface-muted); }
    .breadcrumb .breadcrumb-item a { color: var(--text); }
    .breadcrumb .breadcrumb-item.active { color: var(--text-muted); }
    .sidebar-backdrop { display: none; }
  </style>
  <style>
    /* Responsive tweaks */
    @media (max-width: 767.98px) {
      .app-wrapper { display: block !important; min-height: 100vh; }
      .brand-text { display: none; }
      /* mobile: sidebar hidden by default, slides in as overlay */
      body.sidebar-mini .app-sidebar { display: block !important; width: min(84vw, 320px) !important; min-width: min(84vw, 320px) !important; max-width: min(84vw, 320px) !important; margin-left: 0 !important; visibility: visible !important; transform: translateX(-100%); position: fixed; left: 0; top: 0; bottom: 0; z-index: 1050; transition: transform .25s ease; }
      body.sidebar-open .app-sidebar { margin-left: 0 !important; transform: translateX(0); }
      .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1040; }
      body.sidebar-open .sidebar-backdrop { display: block; }
      .app-sidebar .sidebar-menu .nav-header {
        display: block !important;
        visibility: visible !important;
      }
      .app-sidebar .sidebar-menu > .nav-item > .nav-link {
        display: flex;
        align-items: center;
        width: 100% !important;
        min-height: 44px;
      }
      .app-sidebar .sidebar-menu .nav-link p {
        display: inline-block !important;
        width: auto !important;
        margin: 0 !important;
        overflow: visible !important;
        white-space: normal !important;
        visibility: visible !important;
        animation: none !important;
      }
      .app-sidebar .sidebar-menu .nav-arrow,
      .app-sidebar .sidebar-menu .nav-badge {
        display: inline-block !important;
        visibility: visible !important;
      }
      .app-sidebar .sidebar-menu .nav-item.menu-open > .nav-treeview {
        display: block !important;
      }
      .content-wrapper { min-height: calc(100vh - 56px); padding: 0.75rem; }
      .app-header .nav-link { padding: .35rem .5rem; }
      .app-header .rounded-circle { width: 34px !important; height: 34px !important; }
    }
    @media (min-width: 768px) {
      .app-sidebar { width: 220px; }
    }
  </style>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/theme.css?v=blue-sidebar-20261105">
</head>
<body class="layout-fixed sidebar-mini">
<?php
$modules = $modules ?? [];
$role = $role ?? '';
$roleLabel = $roleLabel ?? '';
$user = $user ?? ['nom_complet' => 'Utilisateur'];
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
$moduleMenuOpen = false;
if (!empty($modules) && is_array($modules)) {
    foreach ($modules as $module) {
        $moduleUrl = BASE_URL . $module['path'];
        if ($currentPath === $moduleUrl || strpos($currentPath, $moduleUrl . '/') === 0) {
            $moduleMenuOpen = true;
            break;
        }
    }
}

$brandName = 'AdminKC';
$brandLogo = BASE_URL . '/assets/kc-logo.svg';
$schoolContext = $school ?? $schoolForHeader ?? null;
if (!empty($user['ecole_id']) && (empty($schoolContext) || empty($schoolContext['id']))) {
    $schoolContext = \App\Models\Ecole::findById((int) $user['ecole_id']);
}
if (!empty($schoolContext['nom_etablissement'])) {
    $brandName = $schoolContext['nom_etablissement'];
}
if (!empty($schoolContext['logo_url'])) {
    $brandLogo = $schoolContext['logo_url'];
    if (strpos($brandLogo, 'http') !== 0) {
        $brandLogo = BASE_URL . '/' . ltrim($brandLogo, '/');
    }
}
?>
  <div class="app-wrapper">
  <div id="sidebarBackdrop" class="sidebar-backdrop" aria-hidden="true"></div>
  <nav class="app-header navbar navbar-expand bg-body"
       data-notifications-endpoint="<?= BASE_URL ?>/notifications/poll"
       data-notifications-base-url="<?= BASE_URL ?>"
       data-notifications-interval="<?= ($user['role'] ?? '') === 'comptable_école' ? '2000' : '5000' ?>"
       data-notifications-icon="<?= BASE_URL ?>/assets/icons/kc-sma-192.png"
       data-push-vapid-public-key="<?= htmlspecialchars(PUSH_VAPID_PUBLIC_KEY, ENT_QUOTES, 'UTF-8') ?>"
       data-push-csrf-token="<?= htmlspecialchars((string) ($_SESSION['push_notifications_csrf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
       data-can-compose-messages="<?= !empty($user['id']) && \App\Models\SchoolMessage::canComposeForRole((string) ($user['role'] ?? '')) ? '1' : '0' ?>">
      <div class="container-fluid">
        <ul class="navbar-nav">
          <li class="nav-item">
            <button class="nav-link border-0 bg-transparent" data-sidebar-toggle type="button" aria-label="Ouvrir ou fermer le menu" aria-expanded="false">
              <i class="bi bi-list" aria-hidden="true"></i>
            </button>
          </li>
          <li class="nav-item d-none d-md-block">
            <a href="<?= BASE_URL ?>/dashboard" class="nav-link">Dashboard</a>
          </li>
          <?php if (in_array(($user['role'] ?? ''), ['super_admin', 'ecole_admin'], true)): ?>
            <li class="nav-item d-none d-md-block">
              <a href="<?= BASE_URL ?>/ecoles" class="nav-link"><?= ($user['role'] ?? '') === 'super_admin' ? 'Gestion des écoles' : 'Mon école' ?></a>
            </li>
          <?php endif; ?>
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
            <form class="d-flex" role="search" id="globalSearchForm">
              <input class="form-control form-control-navbar me-2" type="search" name="q" id="globalSearchInput" placeholder="Recherche" aria-label="Recherche">
              <button class="btn btn-navbar" type="submit"><i class="bi bi-search"></i></button>
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
                  <input type="color" id="sidebarColorInput" value="#3c8dbc" title="Choisir la couleur de la sidebar">
                  <button class="btn btn-sm btn-link ms-2" id="sidebarColorReset">Réinitialiser</button>
                </div>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-label="Messages">
              <i class="bi bi-chat-text"></i>
              <?php
                $messageCount = (int) ($pendingDerogationMessagesCount ?? 0)
                  + (int) ($derogationDecisionMessagesCount ?? 0)
                  + (int) ($userMessagesCount ?? 0)
                  + (int) ($directMessagesCount ?? 0);
              ?>
              <span id="messagesNotificationBadge" class="navbar-badge badge text-bg-danger"<?= $messageCount > 0 ? '' : ' hidden' ?>><?= $messageCount ?></span>
            </a>
            <div id="messagesNotificationMenu" class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-2">
              <?php if (!empty($userMessages)): ?>
                <span class="dropdown-item-text fw-bold">Nouveaux messages</span>
                <div class="dropdown-divider"></div>
                <?php foreach ($userMessages as $userMessage): ?>
                  <a href="<?= BASE_URL ?>/messages/open?id=<?= (int) $userMessage['id'] ?>" class="dropdown-item text-wrap">
                    <i class="bi bi-envelope-fill me-2 text-primary"></i>
                    <strong><?= htmlspecialchars((string) $userMessage['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                  </a>
                <?php endforeach; ?>
              <?php endif; ?>
              <?php if (isset($pendingDerogationMessages)): ?>
                <?php if (!empty($userMessages)): ?><div class="dropdown-divider"></div><?php endif; ?>
                <span class="dropdown-item-text fw-bold">Demandes à valider</span>
                <div class="dropdown-divider"></div>
              <?php endif; ?>
              <?php if (!empty($pendingDerogationMessages)): ?>
                <?php foreach ($pendingDerogationMessages as $derogationMessage): ?>
                  <a href="<?= BASE_URL ?>/derogations" class="dropdown-item text-wrap">
                    <i class="bi bi-file-earmark-check me-2 text-warning"></i>
                    <strong><?= htmlspecialchars(\App\Core\PersonName::format($derogationMessage), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span class="d-block ms-4 small text-muted">
                      Demande de <?= htmlspecialchars((string) ($derogationMessage['demandeur_nom'] ?? 'Autorité scolaire'), ENT_QUOTES, 'UTF-8') ?>
                      · jusqu’au <?= htmlspecialchars(date('d/m/Y', strtotime((string) $derogationMessage['date_fin'])), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </a>
                <?php endforeach; ?>
              <?php elseif (isset($pendingDerogationMessages)): ?>
                <span class="dropdown-item-text text-muted small">Aucune demande de dérogation à valider.</span>
              <?php endif; ?>
              <?php if (!empty($derogationDecisionMessages)): ?>
                <?php if (isset($pendingDerogationMessages) || !empty($userMessages)): ?><div class="dropdown-divider"></div><?php endif; ?>
                <span class="dropdown-item-text fw-bold">Décisions sur vos demandes</span>
                <div class="dropdown-divider"></div>
                <?php foreach ($derogationDecisionMessages as $decisionMessage): ?>
                  <a href="<?= BASE_URL ?>/derogations/open-message?id=<?= (int) $decisionMessage['id'] ?>" class="dropdown-item text-wrap">
                    <i class="bi <?= $decisionMessage['statut'] === 'Approuvee' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-2"></i>
                    <?= htmlspecialchars(\App\Core\PersonName::format($decisionMessage), ENT_QUOTES, 'UTF-8') ?> :
                    <?= $decisionMessage['statut'] === 'Approuvee' ? 'demande approuvée' : 'demande refusée' ?>
                    <span class="d-block ms-4 small text-muted">
                      Par <?= htmlspecialchars((string) ($decisionMessage['validateur_nom'] ?? 'le comptable'), ENT_QUOTES, 'UTF-8') ?>
                      <?php if (!empty($decisionMessage['commentaire'])): ?> · <?= htmlspecialchars((string) $decisionMessage['commentaire'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                    </span>
                  </a>
                <?php endforeach; ?>
              <?php elseif (isset($derogationDecisionMessages)): ?>
                <?php if (isset($pendingDerogationMessages) || !empty($userMessages)): ?><div class="dropdown-divider"></div><?php endif; ?>
                <span class="dropdown-item-text text-muted small">Aucune nouvelle décision sur vos demandes.</span>
              <?php endif; ?>
              <?php if (empty($userMessages) && !isset($pendingDerogationMessages) && !isset($derogationDecisionMessages)): ?>
                <span class="dropdown-item-text text-muted small">Aucun nouveau message.</span>
              <?php endif; ?>
              <div class="dropdown-divider"></div>
              <span class="dropdown-item-text small text-muted">Les alertes système utilisent le son choisi dans les réglages de l’appareil ou du navigateur. Sur iPhone, installez KC_SMA sur l’écran d’accueil. Les notifications push nécessitent Internet et un serveur actif.</span>
              <button id="phoneNotificationToggle" class="dropdown-item small" type="button" aria-pressed="false">
                <i class="bi bi-bell me-2" aria-hidden="true"></i>Utiliser la sonnerie du système
              </button>
              <span id="phoneNotificationHelp" class="dropdown-item-text small text-muted"></span>
              <div class="dropdown-divider"></div>
              <button id="notificationSoundToggle" class="dropdown-item small" type="button" aria-pressed="true">
                <i class="bi bi-volume-up-fill me-2" aria-hidden="true"></i>Bip sonore de la page activé
              </button>
              <?php if (!empty($user['id'])): ?>
                <div class="dropdown-divider"></div>
                <?php if (\App\Models\SchoolMessage::canComposeForRole((string) ($user['role'] ?? ''))): ?>
                  <a href="<?= BASE_URL ?>/messages?compose=1#compose-message" class="dropdown-item text-center">
                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Commencer une conversation
                  </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/messages" class="dropdown-item text-center">Ouvrir les messages</a>
              <?php endif; ?>
              <?php if (isset($pendingDerogationMessages) || isset($derogationDecisionMessages)): ?>
                <div class="dropdown-divider"></div>
                <a href="<?= BASE_URL ?>/derogations" class="dropdown-item text-center">Ouvrir les dérogations</a>
              <?php endif; ?>
            </div>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-bell-fill"></i>
              <span id="eventsNotificationBadge" class="navbar-badge badge text-bg-warning"<?= !empty($eventNotificationsCount) ? '' : ' hidden' ?>><?= (int) ($eventNotificationsCount ?? 0) ?></span>
            </a>
            <div id="eventsNotificationMenu" class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-2">
              <span class="dropdown-item-text fw-bold">Notifications d’événements</span>
              <div class="dropdown-divider"></div>
              <?php if (!empty($eventNotifications)): ?>
                <?php foreach ($eventNotifications as $eventNotification): ?>
                  <a href="<?= BASE_URL ?>/evenements/open-notification?id=<?= (int) $eventNotification['id'] ?>" class="dropdown-item text-wrap <?= (int) $eventNotification['is_read'] === 1 ? 'text-muted' : '' ?>">
                    <i class="bi bi-calendar-event me-2 text-primary"></i><?= htmlspecialchars((string) $eventNotification['titre'], ENT_QUOTES, 'UTF-8') ?>
                    <span class="d-block ms-4 text-muted small">
                      <?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string) $eventNotification['date_debut'])), ENT_QUOTES, 'UTF-8') ?>
                      <?php if (!empty($eventNotification['lieu'])): ?> · <?= htmlspecialchars((string) $eventNotification['lieu'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                    </span>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="dropdown-item-text text-muted small">Aucune notification pour le moment.</span>
              <?php endif; ?>
              <div class="dropdown-divider"></div>
              <a href="<?= BASE_URL ?>/evenements" class="dropdown-item text-center">Voir les événements</a>
            </div>
          </li>
        </ul>
      </div>
    </nav>
    <div id="liveNotificationToasts" class="live-notification-toasts" aria-live="polite" aria-atomic="false"></div>

    <aside class="app-sidebar">
      <div class="school-context-card p-3 mb-3">
        <a href="<?= BASE_URL ?>/dashboard" class="brand-link d-flex align-items-center text-decoration-none p-0">
          <img src="<?= htmlspecialchars($brandLogo) ?>" alt="Logo <?= htmlspecialchars($brandName) ?>" class="me-2 rounded-circle" style="width:36px;height:36px;object-fit:cover;">
          <span class="brand-text h6 mb-0 fw-bold"><?= htmlspecialchars($brandName) ?></span>
        </a>
      </div>

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
            <span class="sidebar-role-label"><?= htmlspecialchars($roleLabel ?? '') ?></span>
          </div>
        </div>


        <div class="mb-3">
          <div class="input-group">
            <input id="sidebarSearchInput" class="form-control form-control-sidebar" type="search" placeholder="Recherche" aria-label="Search">
            <button id="sidebarSearchButton" class="btn btn-outline-light" type="button"><i class="bi bi-search"></i></button>
          </div>
          <div id="sidebarSearchEmptyState" class="text-muted small mt-2 d-none">Aucun élément trouvé.</div>
        </div>

        <nav class="mt-2">
          <ul id="sidebarNavMenu" class="nav nav-pills nav-sidebar sidebar-menu flex-column" data-lte-treeview="true" role="menu" data-accordion="true">
            <li class="nav-header"><?= in_array(($role ?? ''), ['préfet_école', 'DP_école'], true) ? 'ESPACE SCOLAIRE' : 'MENU' ?></li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/dashboard" class="nav-link <?= $currentPath === BASE_URL . '/dashboard' ? 'active' : '' ?>">
                <i class="nav-icon bi bi-speedometer2"></i>
                <p>Tableau de bord</p>
              </a>
            </li>

            <?php if (!empty($modules) && is_array($modules)): ?>
              <li class="nav-header"><?= ($role ?? '') === 'super_admin' ? 'GESTION DE LA PLATEFORME' : (in_array(($role ?? ''), ['préfet_école', 'DP_école'], true) ? 'PILOTAGE PÉDAGOGIQUE' : 'MODULES') ?></li>
              <li class="nav-item has-treeview <?= $moduleMenuOpen ? 'menu-open' : '' ?>">
                <a href="#" class="nav-link <?= $moduleMenuOpen ? 'active' : '' ?>" data-lte-toggle="treeview">
                  <i class="nav-icon bi bi-grid-1x2"></i>
                  <p><?= in_array(($role ?? ''), ['préfet_école', 'DP_école'], true) ? 'Accès rapides' : 'Modules' ?> <i class="bi bi-caret-down-fill float-end"></i></p>
                </a>
                <ul class="nav nav-treeview ps-3">
                  <?php foreach ($modules as $module): ?>
                    <li class="nav-item">
                      <?php $moduleUrl = BASE_URL . $module['path']; ?>
                      <a href="<?= $moduleUrl ?>" class="nav-link <?= ($currentPath === $moduleUrl || strpos($currentPath, $moduleUrl . '/') === 0) ? 'active' : '' ?>">
                        <i class="nav-icon <?= htmlspecialchars($module['icon']) ?>"></i>
                        <p><?= htmlspecialchars($module['name']) ?></p>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endif; ?>

          </ul>
        </nav>
      </div>
    </aside>

    <div class="content-wrapper">
      <?php if (!empty($planExpiryNotice)): ?>
        <div class="container-fluid pt-3">
          <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
            <strong>Votre abonnement arrive à expiration.</strong>
            Le plan « <?= htmlspecialchars($planExpiryNotice['plan_name']) ?> »
            <?php if ((int) $planExpiryNotice['days_remaining'] === 0): ?>
              expire aujourd’hui
            <?php else: ?>
              expire dans <?= (int) $planExpiryNotice['days_remaining'] ?> jour(s)
            <?php endif; ?>
            (le <?= htmlspecialchars(formatDate($planExpiryNotice['date_fin'])) ?>).
            Veuillez contacter <a href="<?= BASE_URL ?>/contact">KC_SMA</a> pour le renouveler.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>
        </div>
      <?php endif; ?>