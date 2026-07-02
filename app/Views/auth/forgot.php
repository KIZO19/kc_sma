<!DOCTYPE html>
<html lang="fr">
<?php
$title = $title ?? APP_NAME;
$message = $message ?? '';
$identifiant = $identifiant ?? '';
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mot de passe oublié - <?= htmlspecialchars($title) ?></title>
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
      <a href="<?= BASE_URL ?>" class="h1"><b>KC</b>_SMA</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Mot de passe oublié</p>
      <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form action="<?= BASE_URL ?>/forgot-password" method="post">
        <div class="input-group mb-3">
          <input type="text" name="identifiant" class="form-control" placeholder="Email ou téléphone" value="<?= htmlspecialchars($identifiant ?? '') ?>" required>
          <div class="input-group-append"><div class="input-group-text"><span class="bi bi-person"></span></div></div>
        </div>
        <div class="row">
          <div class="col-8">
            <a href="<?= BASE_URL ?>/login" class="text-center">Se connecter</a>
          </div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Envoyer</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>/assets/adminlte.min.js"></script>
</body>
</html>
