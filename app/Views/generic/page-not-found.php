<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><?= htmlspecialchars($headline ?? 'Page introuvable') ?></h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Introuvable</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="card card-outline">
            <div class="card-body text-center">
              <h2><?= htmlspecialchars($headline ?? 'Page introuvable') ?></h2>
              <p><?= htmlspecialchars($description ?? 'La page demandée n’existe pas ou n’est pas disponible.') ?></p>
              <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary">Retour au dashboard</a>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
