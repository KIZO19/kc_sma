<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Parents</h1>
        <p class="text-muted mb-0">Consultez les profils des parents de votre école.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Parents</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">Liste des parents</h3>
        <div class="card-tools">
          <span class="badge bg-primary"><?= count($parents ?? []) ?></span>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($parents)): ?>
          <div class="alert alert-info mb-0">Aucun parent n’est disponible dans votre périmètre.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
              <thead>
                <tr>
                  <th>Nom du parent</th>
                  <th>Téléphone</th>
                  <th>Email</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($parents as $parent): ?>
                  <tr>
                    <td><?= htmlspecialchars($parent['nom_responsable'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($parent['telephone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($parent['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/parents/show?id=<?= (int) $parent['id'] ?>">
                        <i class="bi bi-person-vcard me-1"></i> Profil
                      </a>
                    </td>
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
