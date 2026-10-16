<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Modifier l'option</h1>
        <p class="text-muted">Modifiez le nom de l'option.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/options">Options</a></li>
          <li class="breadcrumb-item active">Modifier</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
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
      <div class="card-header">
        <h3 class="card-title">Modifier l'option</h3>
      </div>
      <form action="<?= BASE_URL ?>/options/update" method="post">
        <input type="hidden" name="id" value="<?= (int) ($oldInput['id'] ?? $option['id'] ?? 0) ?>">
        <div class="card-body">
            <?php if (!empty($usage['classes']) || !empty($usage['frais'])): ?>
              <div class="alert alert-info">
                Cette option est utilisée par <?= (int) ($usage['classes'] ?? 0) ?> classe(s) et <?= (int) ($usage['frais'] ?? 0) ?> frais.
                Son nom peut être modifié; la section ne peut pas changer et la suppression est bloquée tant qu’elle est utilisée.
              </div>
            <?php endif; ?>
            <div class="mb-3">
              <label for="nom_option" class="form-label">Nom de l’option</label>
              <input type="text" name="nom_option" id="nom_option" class="form-control" maxlength="100" value="<?= htmlspecialchars($oldInput['nom_option'] ?? $option['nom_option'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
              <label for="section_id" class="form-label">Section</label>
              <select name="section_id" id="section_id" class="form-select" required <?= !empty($usage['classes']) ? 'disabled' : '' ?>>
                <?php foreach ($sections as $section): ?>
                  <option value="<?= (int) $section['id'] ?>" <?= (int) ($oldInput['section_id'] ?? $option['section_id'] ?? 0) === (int) $section['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($section['nom_section']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($usage['classes'])): ?>
                <input type="hidden" name="section_id" value="<?= (int) $option['section_id'] ?>">
              <?php endif; ?>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-between">
            <a href="<?= BASE_URL ?>/options" class="btn btn-secondary">Retour</a>
            <div>
              <?php if (empty($usage['classes']) && empty($usage['frais'])): ?>
                <button type="submit" form="deleteForm" class="btn btn-danger me-2" onclick="return confirm('Confirmer la suppression de cette option ?')">Supprimer</button>
              <?php endif; ?>
              <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
          </div>
        </form>

        <?php if (empty($usage['classes']) && empty($usage['frais'])): ?>
          <form id="deleteForm" action="<?= BASE_URL ?>/options/destroy" method="post" class="d-none">
            <input type="hidden" name="id" value="<?= (int) ($option['id'] ?? 0) ?>">
          </form>
        <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>
