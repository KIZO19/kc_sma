<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php $canManage = $canManage ?? false; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Classes</h1>
              <p class="text-muted">Consultez et gérez les classes, leurs sections et leurs options.</p>
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
              <?php if ($canManage): ?>
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
                        <?php if ($canManage ?? false): ?><th class="text-end">Actions</th><?php endif; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($classes as $index => $classe): ?>
                        <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= htmlspecialchars($classe['nom_classe']) ?></td>
                          <td><?= htmlspecialchars($classe['nom_section'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($classe['nom_option'] ?? '-') ?></td>
                          <?php if ($canManage ?? false): ?>
                            <td class="text-end text-nowrap">
                              <a href="<?= BASE_URL ?>/classes/edit?id=<?= (int) $classe['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1" aria-hidden="true"></i>Modifier
                              </a>
                              <form method="post" action="<?= BASE_URL ?>/classes/destroy" class="d-inline" onsubmit="return confirm('Supprimer cette classe ? La suppression est refusée si elle contient des inscriptions, des frais ou des cours.');">
                                <input type="hidden" name="id" value="<?= (int) $classe['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                  <i class="bi bi-trash me-1" aria-hidden="true"></i>Supprimer
                                </button>
                              </form>
                            </td>
                          <?php endif; ?>
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
