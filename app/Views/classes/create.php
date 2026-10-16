<?php
$isEditing = $isEditing ?? false;
$classe = $classe ?? [];
require __DIR__ . '/../partials/app_header.php';
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><?= $isEditing ? 'Modifier la classe' : 'Nouvelle classe' ?></h1>
              <p class="text-muted"><?= $isEditing ? 'Mettez à jour les informations de la classe.' : 'Créez une nouvelle classe et associez-la à une section et, si nécessaire, à une option.' ?></p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/classes">Classes</a></li>
                <li class="breadcrumb-item active"><?= $isEditing ? 'Modifier' : 'Nouvelle classe' ?></li>
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
                  <h3 class="card-title"><?= $isEditing ? 'Modifier une classe' : 'Créer une classe' ?></h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/classes/<?= $isEditing ? 'update' : 'submit' ?>" autocomplete="off">
                    <?php if ($isEditing): ?>
                      <input type="hidden" name="id" value="<?= (int) $classe['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                      <label for="nom_classe" class="form-label">Nom de la classe</label>
                      <input type="text" id="nom_classe" name="nom_classe" maxlength="100" class="form-control" required value="<?= htmlspecialchars($oldInput['nom_classe'] ?? $classe['nom_classe'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label for="section_id" class="form-label">Section</label>
                      <select id="section_id" name="section_id" class="form-select" required>
                        <option value="">Sélectionnez une section</option>
                        <?php foreach ($sections as $section): ?>
                          <option value="<?= (int) $section['id'] ?>" <?= ((int) ($oldInput['section_id'] ?? $classe['section_id'] ?? 0) === (int) $section['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($section['nom_section']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="option_id" class="form-label">Option (facultative)</label>
                      <select id="option_id" name="option_id" class="form-select">
                        <option value="">Aucune option</option>
                        <?php foreach ($options as $option): ?>
                          <option value="<?= (int) $option['id'] ?>" data-section-id="<?= (int) ($option['section_id'] ?? 0) ?>" <?= ((int) ($oldInput['option_id'] ?? $classe['option_id'] ?? 0) === (int) $option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['nom_option']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="<?= BASE_URL ?>/classes" class="btn btn-secondary">Retour</a>
                      <button type="submit" class="btn btn-success"><?= $isEditing ? 'Enregistrer les modifications' : 'Créer' ?></button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
<script>
  (() => {
    const sectionSelect = document.getElementById('section_id');
    const optionSelect = document.getElementById('option_id');
    if (!sectionSelect || !optionSelect) return;

    const filterOptions = () => {
      const sectionId = sectionSelect.value;
      let selectedOptionIsValid = false;
      Array.from(optionSelect.options).forEach((option, index) => {
        if (index === 0) return;
        const visible = option.dataset.sectionId === sectionId;
        option.hidden = !visible;
        option.disabled = !visible;
        if (visible && option.selected) selectedOptionIsValid = true;
      });
      if (!selectedOptionIsValid) optionSelect.value = '';
    };

    sectionSelect.addEventListener('change', filterOptions);
    filterOptions();
  })();
</script>
