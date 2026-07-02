<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Flux d'activités</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Home</a></li>
          <li class="breadcrumb-item active">Activités</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              <?php if (empty($activities)): ?>
                <li class="list-group-item">Aucune activité récente.</li>
              <?php else: ?>
                <?php foreach ($activities as $act): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                      <strong><?= htmlspecialchars($act['type']) ?></strong>
                      <div><?= htmlspecialchars($act['title']) ?></div>
                    </div>
                    <div class="text-muted small"><?= htmlspecialchars($act['created_at']) ?></div>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>