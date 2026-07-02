<!DOCTYPE html>
<html lang="fr">
<?php
$title = $title ?? APP_NAME;
$errors = $errors ?? [];
$old = $old ?? [];
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Créer un compte - <?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" crossorigin="anonymous">
  <style>
    body { background: #f4f7fb; }
    .login-box { width: 420px; margin: 4% auto; }
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
      <p class="login-box-msg">Créez votre compte</p>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php elseif (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form action="<?= BASE_URL ?>/register" method="post">
        <div class="input-group mb-3">
          <input type="text" name="nom_complet" class="form-control" placeholder="Nom complet" value="<?= htmlspecialchars($old['nom_complet'] ?? '') ?>" required>
          <div class="input-group-append"><div class="input-group-text"><span class="bi bi-person"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <select name="role" class="form-select" required>
            <?php foreach (App\Models\User::getEligibleRoles() as $value => $label): ?>
              <option value="<?= $value ?>" <?= ($old['role'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="input-group-append"><div class="input-group-text"><span class="bi bi-person-badge"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="text" name="identifiant" class="form-control" placeholder="Email ou téléphone" value="<?= htmlspecialchars($old['identifiant'] ?? '') ?>" required>
          <div class="input-group-append"><div class="input-group-text"><span class="bi bi-envelope"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="mot_de_passe" class="form-control" placeholder="Mot de passe" required>
          <div class="input-group-append"><div class="input-group-text"><span class="bi bi-lock"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="mot_de_passe_confirm" class="form-control" placeholder="Confirmez le mot de passe" required>
          <div class="input-group-append"><div class="input-group-text"><span class="bi bi-lock"></span></div></div>
        </div>
        <div class="row">
          <div class="col-8">
            <a href="<?= BASE_URL ?>/login" class="text-center">J’ai déjà un compte</a>
          </div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Créer</button>
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
