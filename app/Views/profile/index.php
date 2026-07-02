<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="mb-0">Mon profil</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($success)): ?>
            <div class="alert alert-success">Profil mis à jour avec succès.</div>
          <?php endif; ?>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Informations personnelles</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/profile/update" enctype="multipart/form-data">
                    <div class="mb-3">
                      <label class="form-label">Photo de profil (avatar)</label>
                      <input type="file" name="avatar" accept="image/png,image/jpeg" class="form-control">
                      <div class="form-text">Formats autorisés: PNG, JPEG. Taille max: 250 KB.</div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nom complet</label>
                      <input type="text" name="nom_complet" class="form-control" value="<?= htmlspecialchars($user['nom_complet'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Identifiant</label>
                      <input type="text" name="identifiant" class="form-control" value="<?= htmlspecialchars($user['identifiant'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Rôle</label>
                      <input type="text" class="form-control" value="<?= htmlspecialchars($roleLabel) ?>" disabled>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nouveau mot de passe</label>
                      <input type="password" name="mot_de_passe" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Confirmer le mot de passe</label>
                      <input type="password" name="mot_de_passe_confirm" class="form-control" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>