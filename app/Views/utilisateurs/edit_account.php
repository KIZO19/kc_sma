<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$account = $account ?? [];
$csrfToken = $csrfToken ?? '';
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Modifier les informations du compte</h1>
    <p class="text-muted mb-0">Le rôle et l’établissement de ce compte ne sont pas modifiables ici.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['utilisateurs_errors'])): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($_SESSION['utilisateurs_errors'] as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
      <?php unset($_SESSION['utilisateurs_errors']); ?>
    <?php endif; ?>
    <div class="row justify-content-center">
      <div class="col-12 col-lg-7 col-xl-6">
        <div class="card card-outline card-primary">
          <div class="card-header"><h2 class="h6 mb-0"><?= htmlspecialchars($account['nom_complet'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2></div>
          <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/utilisateurs/updateAccountInfo" autocomplete="off">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
              <div class="mb-3">
                <label for="accountFullName" class="form-label">Nom complet</label>
                <input id="accountFullName" class="form-control" name="nom_complet" maxlength="150" value="<?= htmlspecialchars($account['nom_complet'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
              <div class="mb-3">
                <label for="accountIdentifier" class="form-label">Identifiant de connexion</label>
                <input id="accountIdentifier" class="form-control" name="identifiant" maxlength="100" value="<?= htmlspecialchars($account['identifiant'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
              <hr>
              <p class="small text-muted">Laissez les champs de mot de passe vides pour conserver le mot de passe actuel.</p>
              <div class="mb-3">
                <label for="accountPassword" class="form-label">Nouveau mot de passe</label>
                <input id="accountPassword" class="form-control" type="password" name="mot_de_passe" minlength="8" autocomplete="new-password">
              </div>
              <div class="mb-3">
                <label for="accountPasswordConfirm" class="form-label">Confirmer le nouveau mot de passe</label>
                <input id="accountPasswordConfirm" class="form-control" type="password" name="mot_de_passe_confirm" minlength="8" autocomplete="new-password">
              </div>
              <div class="d-flex flex-wrap gap-2 justify-content-between">
                <a href="<?= BASE_URL ?>/utilisateurs" class="btn btn-outline-secondary">Retour aux comptes</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Enregistrer les modifications</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>