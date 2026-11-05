<!DOCTYPE html>
<html lang="fr">
<?php
$title = $title ?? APP_NAME;
$csrfToken = $csrfToken ?? '';
$token = $token ?? '';
$errors = $errors ?? [];
$message = $message ?? null;
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="referrer" content="no-referrer">
  <title>Réinitialiser le mot de passe - <?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/responsive.css">
  <style>
    body { background: #f4f7fb; }
    .login-box { width: min(420px, 100%); margin: 6% auto; }
    .card { border-radius: 1rem; }
  </style>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/theme.css?v=blue-sidebar-20261105">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="<?= BASE_URL ?>" class="h1"><b>KC</b>_SMA</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Réinitialiser le mot de passe</p>
      <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
      <?php endforeach; ?>
      <?php if ($message !== null): ?>
        <div class="alert alert-success" role="status"><?= htmlspecialchars($message) ?></div>
        <a href="<?= BASE_URL ?>/login" class="btn btn-primary w-100">Se connecter</a>
      <?php elseif ($token !== ''): ?>
        <form action="<?= BASE_URL ?>/reset-password" method="post">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
          <div class="mb-3">
            <label class="form-label" for="mot_de_passe">Nouveau mot de passe</label>
            <input id="mot_de_passe" type="password" name="mot_de_passe" class="form-control" minlength="8" autocomplete="new-password" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="mot_de_passe_confirm">Confirmer le mot de passe</label>
            <input id="mot_de_passe_confirm" type="password" name="mot_de_passe_confirm" class="form-control" minlength="8" autocomplete="new-password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Enregistrer le nouveau mot de passe</button>
        </form>
      <?php endif; ?>
      <?php if ($message === null): ?>
        <div class="mt-3"><a href="<?= BASE_URL ?>/login">Retour à la connexion</a></div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
