<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Nouvelle option</h1>
              <p class="text-muted">Créez une nouvelle option de cours.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/options">Options</a></li>
                <li class="breadcrumb-item active">Créer</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['options_errors'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <ul class="mb-0">
                <?php foreach ($_SESSION['options_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['options_errors']); ?>
          <?php endif; ?>

          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title">Nouvelle option</h3>
            </div>
            <form action="<?= BASE_URL ?>/options/submit" method="post">
              <div class="card-body">
                <div class="form-group">
                  <label for="nom_option">Nom de l’option</label>
                  <input type="text" name="nom_option" id="nom_option" class="form-control" value="<?= htmlspecialchars($oldInput['nom_option'] ?? '') ?>" placeholder="Ex: Mathématiques approfondies">
                </div>
              </div>
              <div class="card-footer d-flex justify-content-between">
                <a href="<?= BASE_URL ?>/options" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary">Créer l’option</button>
              </div>
            </form>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
