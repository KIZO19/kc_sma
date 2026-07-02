<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><?= htmlspecialchars($headline ?? 'Élèves') ?></h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Élèves</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="card card-outline">
            <div class="card-header">
              <h3 class="card-title"><?= htmlspecialchars($description ?? '') ?></h3>
            </div>
            <div class="card-body">
              <p>Accédez ici aux informations complètes des élèves, modifiez leurs données et gérez leur parcours scolaire.</p>
              <div class="alert alert-info">Cette page est une page de remplacement générique. Ajoutez ici la liste des élèves et leurs actions.</div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
