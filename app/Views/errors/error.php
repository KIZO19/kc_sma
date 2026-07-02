<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><?= htmlspecialchars($statusLabel ?? 'Erreur') ?></h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($statusLabel ?? 'Erreur') ?></li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="card card-outline">
            <div class="card-body text-center">
              <h1 class="display-1"><?= htmlspecialchars($statusCode ?? 500) ?></h1>
              <h2><?= htmlspecialchars($statusLabel ?? 'Erreur') ?></h2>
              <p class="lead"><?= htmlspecialchars($message ?? 'Une erreur est survenue.') ?></p>
              <a href="<?= htmlspecialchars($buttonUrl ?? BASE_URL . '/dashboard') ?>" class="btn btn-primary"><?= htmlspecialchars($buttonText ?? 'Retour') ?></a>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
