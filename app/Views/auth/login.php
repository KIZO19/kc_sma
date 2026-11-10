<!DOCTYPE html>
<html lang="fr">
<?php
$title = $title ?? APP_NAME;
$error = $error ?? '';
$identifiant = $identifiant ?? '';
$portal = in_array($portal ?? 'parent', ['parent', 'eleve', 'personnel'], true) ? $portal : 'parent';
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#3c8dbc">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="KC_SMA">
  <link rel="manifest" href="<?= BASE_URL ?>/manifest.php">
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icons/kc-sma-192.png">
  <title>Connexion - <?= htmlspecialchars($title) ?></title>
  <link rel="icon" href="<?= BASE_URL ?>/assets/favicon.ico" type="image/x-icon">
  <link rel="alternate icon" href="<?= BASE_URL ?>/assets/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/responsive.css">
  <style>
    body { background: #f4f7fb; }
    .login-box { width: 420px; margin: 6% auto; }
    .card { border-radius: 1rem; }
    .login-logo { width: 110px; height: 110px; object-fit: cover; border-radius: 50%; }
    .portal-choice { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .5rem; margin-bottom: 1.25rem; }
    .portal-choice button { padding: .65rem .35rem; border-radius: .65rem; }
    .portal-choice button.active { color: #fff; background: #0d6efd; border-color: #0d6efd; }
    @media (max-width: 480px) { .login-box { width: min(100% - 1.5rem, 420px); } }
  </style>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/theme.css?v=blue-sidebar-20261105">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="<?= BASE_URL ?>" class="d-inline-flex align-items-center justify-content-center">
        <img src="<?= BASE_URL ?>/assets/kc-logo.jpg" alt="KC_SMA" class="login-logo">
      </a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Choisissez votre portail de connexion</p>
      <div class="portal-choice" role="group" aria-label="Choisir le portail">
        <button type="button" class="btn btn-outline-primary<?= $portal === 'parent' ? ' active' : '' ?>" data-portal="parent" aria-pressed="<?= $portal === 'parent' ? 'true' : 'false' ?>">
          <i class="bi bi-people me-1" aria-hidden="true"></i> Parent
        </button>
        <button type="button" class="btn btn-outline-primary<?= $portal === 'eleve' ? ' active' : '' ?>" data-portal="eleve" aria-pressed="<?= $portal === 'eleve' ? 'true' : 'false' ?>">
          <i class="bi bi-backpack me-1" aria-hidden="true"></i> Élève
        </button>
        <button type="button" class="btn btn-outline-secondary<?= $portal === 'personnel' ? ' active' : '' ?>" data-portal="personnel" aria-pressed="<?= $portal === 'personnel' ? 'true' : 'false' ?>">
          <i class="bi bi-person-badge me-1" aria-hidden="true"></i> Personnel
        </button>
      </div>
      <p id="portal-description" class="small text-muted text-center mb-3">
        <?= $portal === 'parent'
          ? 'Accédez au portail parent avec le numéro de téléphone renseigné lors de l’inscription.'
          : ($portal === 'eleve'
            ? 'Accédez au portail élève avec le matricule attribué lors de l’inscription.'
            : 'Connexion réservée au personnel et aux administrateurs de l’école.') ?>
      </p>
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form action="<?= BASE_URL ?>/login" method="post">
        <input type="hidden" name="portal" id="loginPortal" value="<?= htmlspecialchars($portal, ENT_QUOTES, 'UTF-8') ?>">
        <div class="input-group mb-3">
          <input type="text" name="identifiant" id="loginIdentifier" class="form-control" placeholder="<?= $portal === 'parent' ? 'Numéro de téléphone du parent' : ($portal === 'eleve' ? 'Matricule de l’élève' : 'Email ou identifiant') ?>" value="<?= htmlspecialchars($identifiant) ?>" autocomplete="username" required>
          <div class="input-group-append"><div class="input-group-text"><span class="bi bi-person"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="mot_de_passe" id="passwordField" class="form-control" placeholder="Mot de passe" required>
          <button type="button" class="btn btn-outline-secondary border-start-0" id="togglePassword" aria-label="Afficher ou masquer le mot de passe">
            <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
          </button>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="showPasswordCheckbox">
          <label class="form-check-label" for="showPasswordCheckbox">Afficher le mot de passe</label>
        </div>
        <div class="row">
          <div class="col-8"></div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Connexion</button>
          </div>
        </div>
      </form>
      <p class="mt-3 mb-1">
        <a href="<?= BASE_URL ?>/forgot-password">Mot de passe oublié ?</a>
      </p>
      <p class="mb-0">
        <a href="<?= BASE_URL ?>/register" class="text-center">Créer un compte</a>
      </p>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>/assets/adminlte.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const portalField = document.getElementById('loginPortal');
    const identifierField = document.getElementById('loginIdentifier');
    const portalDescription = document.getElementById('portal-description');
    const portalButtons = document.querySelectorAll('[data-portal]');
    const portalSettings = {
      parent: {
        placeholder: 'Numéro de téléphone du parent',
        description: 'Accédez au portail parent avec le numéro de téléphone renseigné lors de l’inscription.'
      },
      eleve: {
        placeholder: 'Matricule de l’élève',
        description: 'Accédez au portail élève avec le matricule attribué lors de l’inscription.'
      },
      personnel: {
        placeholder: 'Email ou identifiant',
        description: 'Connexion réservée au personnel et aux administrateurs de l’école.'
      }
    };

    portalButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        const selectedPortal = button.dataset.portal;
        const settings = portalSettings[selectedPortal];
        if (!settings) return;
        portalField.value = selectedPortal;
        identifierField.placeholder = settings.placeholder;
        portalDescription.textContent = settings.description;
        portalButtons.forEach(function (item) {
          const isActive = item === button;
          item.classList.toggle('active', isActive);
          item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
      });
    });

    const passwordField = document.getElementById('passwordField');
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');
    const showPasswordCheckbox = document.getElementById('showPasswordCheckbox');

    function setPasswordVisibility(show) {
      passwordField.type = show ? 'text' : 'password';
      togglePasswordIcon.classList.toggle('bi-eye', show);
      togglePasswordIcon.classList.toggle('bi-eye-slash', !show);
      showPasswordCheckbox.checked = show;
    }

    togglePassword.addEventListener('click', function () {
      setPasswordVisibility(passwordField.type === 'password');
    });

    showPasswordCheckbox.addEventListener('change', function () {
      setPasswordVisibility(showPasswordCheckbox.checked);
    });
  });
</script>
<script src="<?= BASE_URL ?>/assets/pwa-install.js?v=blue-white-20261105" defer></script>
</body>
</html>
