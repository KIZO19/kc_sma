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
            <label class="form-label">Année scolaire</label>
            <select name="annee_scolaire_id" class="form-select">
              <?php foreach ($years as $y): ?>
                <option value="<?= (int) $y['id'] ?>" <?= ((int) ($old['annee_scolaire_id'] ?? $fee['annee_scolaire_id'] ?? 0) === (int) $y['id']) ? 'selected' : '' ?>><?= htmlspecialchars($y['annee']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Devise</label>
            <select name="devise" class="form-select">
              <?php foreach ($currencies as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>" <?= (($old['devise'] ?? $fee['devise'] ?? '') === $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Portée</label>
            <select name="scope" class="form-select" id="scopeSelect">
              <option value="class" <?= (($old['scope'] ?? $fee['scope'] ?? '') === 'class') ? 'selected' : '' ?>>Classe</option>
              <option value="option" <?= (($old['scope'] ?? $fee['scope'] ?? '') === 'option') ? 'selected' : '' ?>>Option</option>
              <option value="section" <?= (($old['scope'] ?? $fee['scope'] ?? '') === 'section') ? 'selected' : '' ?>>Section</option>
              <option value="school" <?= (($old['scope'] ?? $fee['scope'] ?? '') === 'school') ? 'selected' : '' ?>>École entière</option>
            </select>
          </div>

          <div class="mb-3" id="scopeIdWrapper">
            <label class="form-label">Sélection portée</label>
            <select name="scope_id" class="form-select">
              <option value="">-- Sélectionner --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= ((int) ($old['scope_id'] ?? $fee['scope_id'] ?? $fee['classe_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nom_classe']) ?></option>
              <?php endforeach; ?>
              <?php foreach ($options as $o): ?>
                <option value="<?= (int) $o['id'] ?>" <?= ((int) ($old['scope_id'] ?? $fee['scope_id'] ?? 0) === (int) $o['id']) ? 'selected' : '' ?>><?= htmlspecialchars($o['nom_option']) ?></option>
              <?php endforeach; ?>
              <?php foreach ($sections as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= ((int) ($old['scope_id'] ?? $fee['scope_id'] ?? 0) === (int) $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nom_section']) ?></option>
              <?php endforeach; ?>
            </select>
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