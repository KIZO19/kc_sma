<!DOCTYPE html>
<html lang="fr">
<?php
$title = $title ?? APP_NAME;
$csrfToken = $csrfToken ?? '';
$errors = $errors ?? [];
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#3c8dbc">
  <title>Changer le mot de passe - <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/responsive.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/theme.css?v=blue-sidebar-20261105">
  <style>
    body { background: #f4f7fb; }
    .login-box { width: min(420px, 100%); margin: 6% auto; }
    .card { border-radius: 1rem; }
  </style>
</head>
<body class="hold-transition login-page">
<main class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <strong>Changement obligatoire du mot de passe</strong>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Pour protéger votre compte, remplacez le mot de passe temporaire avant de continuer.</p>
      <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endforeach; ?>
      <form action="<?= BASE_URL ?>/auth/changeInitialPassword" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
          <label class="form-label" for="mot_de_passe">Nouveau mot de passe</label>
          <input id="mot_de_passe" type="password" name="mot_de_passe" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="mot_de_passe_confirm">Confirmer le nouveau mot de passe</label>
          <input id="mot_de_passe_confirm" type="password" name="mot_de_passe_confirm" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Enregistrer et continuer</button>
      </form>
      <div class="mt-3 text-center">
        <a href="<?= BASE_URL ?>/logout">Se déconnecter</a>
      </div>
    </div>
  </div>
</main>
</body>
</html>
