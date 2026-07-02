<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Nouvelle inscription</h1>
              <p class="text-muted">Créez un dossier d'élève en attente de validation par le secrétaire.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inscriptions">Dossiers d’inscription</a></li>
                <li class="breadcrumb-item active">Nouvelle inscription</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['inscriptions_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= htmlspecialchars($_SESSION['inscriptions_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['inscriptions_success']); ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['inscriptions_errors'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <ul class="mb-0">
                <?php foreach ($_SESSION['inscriptions_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['inscriptions_errors']); ?>
          <?php endif; ?>

          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="card card-outline card-success">
                <div class="card-header">
                  <h3 class="card-title">Formulaire d’inscription</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/inscriptions/submit" autocomplete="off">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Postnom</label>
                        <input type="text" name="postnom" class="form-control" required>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Genre</label>
                        <select name="genre" class="form-select" required>
                          <option value="">Sélectionnez</option>
                          <option value="M">Masculin</option>
                          <option value="F">Féminin</option>
                        </select>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control" required>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Matricule (optionnel)</label>
                        <input type="text" name="matricule" class="form-control" placeholder="Généré automatiquement si vide">
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Parent ID</label>
                      <input type="number" name="parent_id" class="form-control" placeholder="ID du parent, si connu">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Lieu de naissance</label>
                      <input type="text" name="lieu_naissance" class="form-control">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Adresse</label>
                      <textarea name="adresse" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="<?= BASE_URL ?>/inscriptions" class="btn btn-secondary">Retour aux dossiers</a>
                      <button type="submit" class="btn btn-success">Enregistrer</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
