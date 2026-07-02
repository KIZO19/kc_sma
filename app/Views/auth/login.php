<!DOCTYPE html>
<html lang="fr">
<?php
$title = $title ?? APP_NAME;
$error = $error ?? '';
$identifiant = $identifiant ?? '';
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion - <?= htmlspecialchars($title) ?></title>
  <link rel="icon" href="<?= BASE_URL ?>/assets/favicon.ico" type="image/x-icon">
  <link rel="alternate icon" href="<?= BASE_URL ?>/assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" crossorigin="anonymous">
  <style>
    body { background: #f4f7fb; }
    .login-box { width: 420px; margin: 6% auto; }
    .card { border-radius: 1rem; }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="<?= BASE_URL ?>" class="h1 d-flex align-items-center justify-content-center">
        <img src="<?= BASE_URL ?>/assets/kc-logo.svg" alt="KC_SMA" width="36" height="36" class="me-2">
        <span><b>KC</b>_SMA</span>
      </a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Connectez-vous pour accéder au tableau de bord</p>
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form action="<?= BASE_URL ?>/login" method="post">
        <div class="input-group mb-3">
          <input type="text" name="identifiant" class="form-control" placeholder="Email ou téléphone" value="<?= htmlspecialchars($identifiant) ?>" required>
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
</body>
</html>
