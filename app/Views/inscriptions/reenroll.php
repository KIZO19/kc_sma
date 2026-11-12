<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$previousClassName = trim((string) ($previousEnrollment['nom_classe'] ?? ''));
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Réinscription annuelle</h1>
    <p class="text-muted mb-0">Confirmez l’élève pour l’année <?= $escape($activeYear['annee'] ?? '') ?> sans recréer ni modifier son identité.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['inscriptions_errors'])): ?>
      <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
          <?php foreach ($_SESSION['inscriptions_errors'] as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php unset($_SESSION['inscriptions_errors']); ?>
    <?php endif; ?>
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="card card-outline card-success">
          <div class="card-header">
            <h2 class="h5 mb-0"><?= $escape(\App\Core\PersonName::format($student)) ?></h2>
            <div class="small text-muted">
              Matricule : <?= $escape($student['matricule'] ?? '—') ?>
              · Parent : <?= $escape($parent['nom_responsable'] ?? 'Non renseigné') ?>
              · Téléphone : <?= $escape($parent['telephone'] ?? '—') ?>
            </div>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              Les noms, l’adresse, le matricule et les coordonnées du parent resteront inchangés. Seule l’inscription à l’année active sera ajoutée.
            </div>
            <form method="post" action="<?= BASE_URL ?>/inscriptions/submitReenrollment" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
              <input type="hidden" name="eleve_id" value="<?= (int) $student['id'] ?>">
              <input type="hidden" name="annee_scolaire_id" value="<?= (int) $activeYear['id'] ?>">

              <div class="mb-3">
                <label class="form-label">Année scolaire</label>
                <input class="form-control" value="<?= $escape($activeYear['annee'] ?? '') ?>" readonly>
              </div>

              <fieldset class="mb-4">
                <legend class="form-label">Décision de progression</legend>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="progression" id="progression-repeat" value="redoublement" required checked>
                  <label class="form-check-label" for="progression-repeat">L’élève reprend la même classe : <?= $escape($previousClassName ?: 'classe précédente') ?></label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="progression" id="progression-pass" value="passage" required>
                  <label class="form-check-label" for="progression-pass">L’élève passe dans une autre classe et/ou option</label>
                </div>
              </fieldset>

              <div id="target-class-fields" class="border rounded p-3 mb-4" hidden>
                <h3 class="h6">Nouvelle affectation</h3>
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label" for="reenroll-section">Section</label>
                    <select id="reenroll-section" name="section_id" class="form-select" disabled>
                      <option value="">Sélectionnez une section</option>
                      <?php foreach ($sections as $section): ?>
                        <option value="<?= (int) $section['id'] ?>" data-section-name="<?= $escape(mb_strtolower(trim((string) $section['nom_section']), 'UTF-8')) ?>"><?= $escape($section['nom_section']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3" id="reenroll-option-wrap" hidden>
                    <label class="form-label" for="reenroll-option">Option</label>
                    <select id="reenroll-option" name="option_id" class="form-select" disabled>
                      <option value="">Sélectionnez une option</option>
                      <?php foreach ($options as $option): ?>
                        <option value="<?= (int) $option['id'] ?>" data-section-id="<?= (int) ($option['section_id'] ?? 0) ?>"><?= $escape($option['nom_option']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label" for="reenroll-class">Classe</label>
                    <select id="reenroll-class" name="classe_id" class="form-select" disabled>
                      <option value="">Sélectionnez une classe</option>
                      <?php foreach ($classes as $class): ?>
                        <option value="<?= (int) $class['id'] ?>" data-section-id="<?= (int) ($class['section_id'] ?? 0) ?>" data-option-id="<?= (int) ($class['option_id'] ?? 0) ?>"><?= $escape($class['nom_classe']) ?><?= !empty($class['nom_option']) ? ' · ' . $escape($class['nom_option']) : '' ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <fieldset class="mb-4">
                <legend class="h6">Bulletins scolaires <span class="text-muted fw-normal">(facultatif)</span></legend>
                <p class="small text-muted">L’école actuelle est proposée par défaut comme provenance. Vous pouvez la modifier pour chaque bulletin antérieur.</p>
                <div class="mb-3">
                  <label class="form-label" for="reenroll-bulletins">Images des bulletins <span class="text-muted fw-normal">(jusqu’à 5 fichiers, 5 Mo chacun)</span></label>
                  <input id="reenroll-bulletins" type="file" name="bulletins[]" accept="image/jpeg,image/png,image/webp" class="form-control academic-bulletin-input" data-default-origin="<?= $escape($schoolName ?? '') ?>" multiple>
                  <div class="form-text">Sélectionnez plusieurs images à la fois. Elles seront optimisées avant l’envoi, puis renseignez l’école, l’année et la mention de chacune.</div>
                  <div class="small text-muted mt-2" data-bulletin-compression-status role="status" aria-live="polite"></div>
                </div>
                <div class="academic-bulletin-metadata" aria-live="polite"></div>
              </fieldset>

              <div class="d-flex flex-wrap justify-content-between gap-2">
                <a href="<?= BASE_URL ?>/inscriptions" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-success">Confirmer la réinscription</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const repeat = document.getElementById('progression-repeat');
  const passage = document.getElementById('progression-pass');
  const fields = document.getElementById('target-class-fields');
  const section = document.getElementById('reenroll-section');
  const option = document.getElementById('reenroll-option');
  const optionWrap = document.getElementById('reenroll-option-wrap');
  const classSelect = document.getElementById('reenroll-class');
  const classes = Array.from(classSelect.options).slice(1).map(function (item) {
    return { value: item.value, label: item.textContent, sectionId: item.dataset.sectionId, optionId: item.dataset.optionId };
  });
  const options = Array.from(option.options).slice(1).map(function (item) {
    return { value: item.value, label: item.textContent, sectionId: item.dataset.sectionId };
  });

  function refreshClassOptions() {
    const sectionId = section.value;
    const isSecondary = section.selectedOptions[0]?.dataset.sectionName === 'secondaire';
    const optionId = option.value;
    const previousValue = classSelect.value;
    classSelect.innerHTML = '<option value="">Sélectionnez une classe</option>';
    const matches = classes.filter(function (item) {
      return item.sectionId === sectionId && (!isSecondary || item.optionId === optionId) && (isSecondary || item.optionId === '0');
    });
    matches.forEach(function (item) {
      const optionElement = document.createElement('option');
      optionElement.value = item.value;
      optionElement.textContent = item.label;
      classSelect.appendChild(optionElement);
    });
    classSelect.value = matches.some(function (item) { return item.value === previousValue; }) ? previousValue : '';
  }

  function refreshOptions() {
    const isSecondary = section.selectedOptions[0]?.dataset.sectionName === 'secondaire';
    const previousValue = option.value;
    optionWrap.hidden = !isSecondary;
    option.disabled = !isSecondary;
    option.required = isSecondary && passage.checked;
    option.innerHTML = '<option value="">Sélectionnez une option</option>';
    options.filter(function (item) { return isSecondary && item.sectionId === section.value; }).forEach(function (item) {
      const optionElement = document.createElement('option');
      optionElement.value = item.value;
      optionElement.textContent = item.label;
      option.appendChild(optionElement);
    });
    option.value = options.some(function (item) { return item.value === previousValue && item.sectionId === section.value; }) ? previousValue : '';
    refreshClassOptions();
  }

  function toggleProgressionFields() {
    const needsNewClass = passage.checked;
    fields.hidden = !needsNewClass;
    section.disabled = !needsNewClass;
    section.required = needsNewClass;
    classSelect.disabled = !needsNewClass;
    classSelect.required = needsNewClass;
    if (needsNewClass) refreshOptions();
  }

  repeat.addEventListener('change', toggleProgressionFields);
  passage.addEventListener('change', toggleProgressionFields);
  section.addEventListener('change', refreshOptions);
  option.addEventListener('change', refreshClassOptions);
  document.querySelector('form').addEventListener('submit', function (event) {
    const files = document.getElementById('reenroll-bulletins').files;
    if (files.length > 5 || Array.from(files).some(function (file) {
      return !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024;
    })) {
      event.preventDefault();
      window.alert('Joignez au maximum 5 images JPEG, PNG ou WebP de 5 Mo maximum chacune.');
    }
  });
  toggleProgressionFields();
});
</script>
<script src="<?= BASE_URL ?>/assets/academic-bulletins.js" defer></script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
