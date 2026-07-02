<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php $canCreate = $canCreate ?? false; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Options</h1>
              <p class="text-muted">Gérez les options disponibles pour les classes du secondaire.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Options</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['options_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= htmlspecialchars($_SESSION['options_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['options_success']); ?>
          <?php endif; ?>

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
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title">Liste des options</h3>
              <?php if ($canCreate): ?>
                <a href="<?= BASE_URL ?>/options/create" class="btn btn-success">Nouvelle option</a>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <?php if (empty($options)): ?>
                <div class="alert alert-info">Aucune option définie.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>Option</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($options as $index => $option): ?>
                        <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= htmlspecialchars($option['nom_option']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
