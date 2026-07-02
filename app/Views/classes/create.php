<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Nouvelle classe</h1>
              <p class="text-muted">Créez une nouvelle classe et associez-la à une section et le cas échéant à une option.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/classes">Classes</a></li>
                <li class="breadcrumb-item active">Nouvelle classe</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
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

          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="card card-outline card-success">
                <div class="card-header">
                  <h3 class="card-title">Créer une classe</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/classes/submit" autocomplete="off">
                    <div class="mb-3">
                      <label class="form-label">Nom de la classe</label>
                      <input type="text" name="nom_classe" class="form-control" required value="<?= htmlspecialchars($oldInput['nom_classe'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Section</label>
                      <select name="section_id" class="form-select" required>
                        <option value="">Sélectionnez une section</option>
                        <?php foreach ($sections as $section): ?>
                          <option value="<?= (int) $section['id'] ?>" <?= ((int) ($oldInput['section_id'] ?? 0) === (int) $section['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($section['nom_section']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Option (facultative)</label>
                      <select name="option_id" class="form-select">
                        <option value="">Aucune option</option>
                        <?php foreach ($options as $option): ?>
                          <option value="<?= (int) $option['id'] ?>" <?= ((int) ($oldInput['option_id'] ?? 0) === (int) $option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['nom_option']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="<?= BASE_URL ?>/classes" class="btn btn-secondary">Retour</a>
                      <button type="submit" class="btn btn-success">Créer</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
