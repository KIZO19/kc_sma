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

      <section class="content py-4">
        <div class="container-fluid">
          <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
              <div class="card card-outline shadow-sm">
                <div class="card-body text-center py-5">
                  <span class="badge rounded-pill bg-danger mb-3 px-4 py-2">Erreur <?= htmlspecialchars($statusCode ?? 500) ?></span>
                  <h1 class="display-1 mb-3"><?= htmlspecialchars($statusCode ?? 500) ?></h1>
                  <h2 class="mb-3"><?= htmlspecialchars($statusLabel ?? 'Erreur') ?></h2>
                  <p class="lead text-secondary mb-4"><?= htmlspecialchars($message ?? 'Une erreur est survenue.') ?></p>
                  <a href="<?= htmlspecialchars($buttonUrl ?? BASE_URL . '/dashboard') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-left-circle me-2"></i><?= htmlspecialchars($buttonText ?? 'Retour') ?>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
