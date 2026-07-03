<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$classes = $classes ?? [];
$years = $years ?? [];
$oldInput = $oldInput ?? [];
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Nouveau frais</h1>
              <p class="text-muted">Créez un nouveau frais scolaire pour une classe et une année.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/frais">Frais</a></li>
                <li class="breadcrumb-item active">Nouveau frais</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['frais_errors'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <ul class="mb-0">
                <?php foreach ($_SESSION['frais_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['frais_errors']); ?>
          <?php endif; ?>

          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="card card-outline card-success">
                <div class="card-header">
                  <h3 class="card-title">Création d’un frais</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/frais/submit" autocomplete="off">
                    <div class="mb-3">
                      <label class="form-label">Type de frais</label>
                      <input type="text" name="type_frais" class="form-control" required value="<?= htmlspecialchars($oldInput['type_frais'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Montant total</label>
                      <input type="number" name="montant_total" class="form-control" required step="0.01" min="0" value="<?= htmlspecialchars($oldInput['montant_total'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Classe</label>
                      <select name="classe_id" class="form-select" required>
                        <option value="">Sélectionnez une classe</option>
                        <?php foreach ($classes as $classe): ?>
                          <option value="<?= (int) $classe['id'] ?>" <?= ((int) ($oldInput['classe_id'] ?? 0) === (int) $classe['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($classe['nom_classe']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Année scolaire</label>
                      <select name="annee_scolaire_id" class="form-select" required>
                        <option value="">Sélectionnez une année scolaire</option>
                        <?php foreach ($years as $year): ?>
                          <option value="<?= (int) $year['id'] ?>" <?= ((int) ($oldInput['annee_scolaire_id'] ?? 0) === (int) $year['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($year['annee']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="<?= BASE_URL ?>/frais" class="btn btn-secondary">Retour à la liste</a>
                      <button type="submit" class="btn btn-success">Créer le frais</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
