<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php $old = $_SESSION['frais_old'] ?? []; unset($_SESSION['frais_old']); ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Modifier le frais</h1>
        <p class="text-muted"><?= htmlspecialchars($fee['type_frais'] ?? '') ?></p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/frais">Frais</a></li>
          <li class="breadcrumb-item active">Modifier</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['frais_errors'])): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($_SESSION['frais_errors'] as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php unset($_SESSION['frais_errors']); ?>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-8">
        <form method="post" action="<?= BASE_URL ?>/frais/update">
          <input type="hidden" name="id" value="<?= (int) $fee['id'] ?>">

          <div class="mb-3">
            <label class="form-label">Type de frais</label>
            <input class="form-control" name="type_frais" value="<?= htmlspecialchars($old['type_frais'] ?? $fee['type_frais'] ?? '') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Montant</label>
            <input type="number" step="0.01" class="form-control" name="montant_total" value="<?= htmlspecialchars($old['montant_total'] ?? $fee['montant_total'] ?? '') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Encodage (code unique)</label>
            <input class="form-control" name="encodage" value="<?= htmlspecialchars($old['encodage'] ?? $fee['encodage'] ?? '') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Année scolaire</label>
            <select name="annee_scolaire_id" class="form-select">
              <?php foreach ($years as $y): ?>
                <option value="<?= (int) $y['id'] ?>" <?= ((int) ($old['annee_scolaire_id'] ?? $fee['annee_scolaire_id'] ?? 0) === (int) $y['id']) ? 'selected' : '' ?>><?= htmlspecialchars($y['annee']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Devise</label>
              <select name="devise" class="form-select">
                <?php foreach ($currencies as $key => $label): ?>
                  <option value="<?= htmlspecialchars($key) ?>" <?= (($old['devise'] ?? $fee['devise'] ?? '') === $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Portée</label>
              <select name="scope" class="form-select" id="scopeSelect">
                <option value="class" <?= (($old['scope'] ?? $fee['scope'] ?? '') === 'class') ? 'selected' : '' ?>>Classe</option>
                <option value="option" <?= (($old['scope'] ?? $fee['scope'] ?? '') === 'option') ? 'selected' : '' ?>>Option</option>
                <option value="section" <?= (($old['scope'] ?? $fee['scope'] ?? '') === 'section') ? 'selected' : '' ?>>Section</option>
                <option value="school" <?= (($old['scope'] ?? $fee['scope'] ?? '') === 'school') ? 'selected' : '' ?>>École entière</option>
              </select>
            </div>
          </div>

          <div class="mt-3" id="scopeSelectors">
            <input type="hidden" name="scope_id" id="scopeIdHidden" value="<?= (int) ($old['scope_id'] ?? $fee['scope_id'] ?? $fee['classe_id'] ?? 0) ?>">
            <input type="hidden" name="ecole_id" id="ecoleIdHidden" value="<?= (int) ($fee['ecole_id'] ?? $user['ecole_id'] ?? 0) ?>">

            <div class="mb-3 d-none" id="classSelectWrapper">
              <label class="form-label">Classe</label>
              <select id="classSelect" class="form-select">
                <option value="">-- Sélectionner une classe --</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?= (int) $c['id'] ?>" <?= ((int) ($old['scope_id'] ?? $fee['scope_id'] ?? $fee['classe_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nom_classe']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3 d-none" id="optionSelectWrapper">
              <label class="form-label">Option</label>
              <select id="optionSelect" class="form-select">
                <option value="">-- Sélectionner une option --</option>
                <?php foreach ($options as $o): ?>
                  <option value="<?= (int) $o['id'] ?>" <?= ((int) ($old['scope_id'] ?? $fee['scope_id'] ?? 0) === (int) $o['id']) ? 'selected' : '' ?>><?= htmlspecialchars($o['nom_option']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3 d-none" id="sectionSelectWrapper">
              <label class="form-label">Section</label>
              <select id="sectionSelect" class="form-select">
                <option value="">-- Sélectionner une section --</option>
                <?php foreach ($sections as $s): ?>
                  <option value="<?= (int) $s['id'] ?>" <?= ((int) ($old['scope_id'] ?? $fee['scope_id'] ?? 0) === (int) $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nom_section']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Enregistrer</button>
            <a href="<?= BASE_URL ?>/frais" class="btn btn-secondary">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const scopeSelect = document.getElementById('scopeSelect');
    const classWrapper = document.getElementById('classSelectWrapper');
    const optionWrapper = document.getElementById('optionSelectWrapper');
    const sectionWrapper = document.getElementById('sectionSelectWrapper');
    const classSelect = document.getElementById('classSelect');
    const optionSelect = document.getElementById('optionSelect');
    const sectionSelect = document.getElementById('sectionSelect');
    const scopeIdHidden = document.getElementById('scopeIdHidden');
    const form = document.querySelector('form[action="<?= BASE_URL ?>/frais/update"]');

    if (!scopeSelect || !scopeIdHidden || !form) return;

    function showForScope(scope) {
      classWrapper.classList.add('d-none');
      optionWrapper.classList.add('d-none');
      sectionWrapper.classList.add('d-none');
      if (scope === 'class') {
        classWrapper.classList.remove('d-none');
      } else if (scope === 'option') {
        optionWrapper.classList.remove('d-none');
      } else if (scope === 'section') {
        sectionWrapper.classList.remove('d-none');
      }
    }

    // initialize
    showForScope(scopeSelect.value || 'class');

    // map existing hidden value to visible selects
    const existing = scopeIdHidden.value ? String(scopeIdHidden.value) : '';
    if (existing) {
      // try to select in each if present
      if (classSelect) {
        const opt = classSelect.querySelector('option[value="' + existing + '"]');
        if (opt) opt.selected = true;
      }
      if (optionSelect) {
        const opt2 = optionSelect.querySelector('option[value="' + existing + '"]');
        if (opt2) opt2.selected = true;
      }
      if (sectionSelect) {
        const opt3 = sectionSelect.querySelector('option[value="' + existing + '"]');
        if (opt3) opt3.selected = true;
      }
    }

    scopeSelect.addEventListener('change', function () {
      showForScope(scopeSelect.value);
    });

    form.addEventListener('submit', function (e) {
      const scope = scopeSelect.value;
      let val = '';
      if (scope === 'class' && classSelect) val = classSelect.value || '';
      if (scope === 'option' && optionSelect) val = optionSelect.value || '';
      if (scope === 'section' && sectionSelect) val = sectionSelect.value || '';
      scopeIdHidden.value = val ? parseInt(val, 10) : 0;
    });
  });
</script>