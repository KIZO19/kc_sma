<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Classes</h1>
              <p class="text-muted">Gérez les classes de l’école. Les classes maternelle et primaire peuvent être créées par certains rôles.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Classes</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['classes_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= htmlspecialchars($_SESSION['classes_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['classes_success']); ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['classes_errors'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <ul class="mb-0">
                <?php foreach ($_SESSION['classes_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['classes_errors']); ?>
          <?php endif; ?>

          <div class="card card-outline card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title">Liste des classes</h3>
              <?php if ($canCreate): ?>
                <a href="<?= BASE_URL ?>/classes/create" class="btn btn-success">Nouvelle classe</a>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <?php if (empty($classes)): ?>
                <div class="alert alert-info">Aucune classe définie pour cette école.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-hover table-bordered">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>Nom de la classe</th>
                        <th>Section</th>
                        <th>Option</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($classes as $index => $classe): ?>
                        <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= htmlspecialchars($classe['nom_classe']) ?></td>
                          <td><?= htmlspecialchars($classe['nom_section'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($classe['nom_option'] ?? '-') ?></td>
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
