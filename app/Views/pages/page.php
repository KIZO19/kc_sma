<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><?= htmlspecialchars($pageTitle ?? 'Page') ?></h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle ?? 'Page') ?></li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="card card-outline">
            <div class="card-header">
              <h3 class="card-title"><?= htmlspecialchars($pageDescription ?? '') ?></h3>
            </div>
            <div class="card-body">
              <p><?= htmlspecialchars($pageContent ?? 'Cette page est en cours de construction.') ?></p>
              <?php if (!empty($pageNotes)): ?>
                <div class="alert alert-info mt-3">
                  <?= htmlspecialchars($pageNotes) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
