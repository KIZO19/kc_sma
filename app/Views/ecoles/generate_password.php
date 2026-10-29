<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="mb-0">Générer un mot de passe temporaire</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Générer mot de passe</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="status">
              <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
              <?php if (!empty($generatedPassword)): ?>
                <div class="mt-2">Mot de passe temporaire : <code><?= htmlspecialchars($generatedPassword, ENT_QUOTES, 'UTF-8') ?></code></div>
                <div class="small mt-1">Copiez-le maintenant et transmettez-le au parent par un canal sûr. Il ne sera plus affiché après avoir quitté cette page.</div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-6 mb-3">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Compte élève</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/ecoles/generatePassword">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="account_type" value="student">
                    <div class="mb-3">
                      <label class="form-label">Matricule de l'élève</label>
                      <input type="text" name="matricule" class="form-control" value="<?= htmlspecialchars($matricule ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Générer pour l’élève</button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-lg-6 mb-3">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Compte parent</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/ecoles/generatePassword">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="account_type" value="parent">
                    <div class="mb-3">
                      <label class="form-label" for="parent-identifier">Identifiant de connexion du parent</label>
                      <input type="text" id="parent-identifier" name="parent_identifier" class="form-control" value="<?= htmlspecialchars($parentIdentifier ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Générer pour le parent</button>
                  </form>
                  <p class="form-text mt-3 mb-0">Cette opération remplace le mot de passe actuel. Elle est limitée aux comptes parents actifs de votre établissement.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>