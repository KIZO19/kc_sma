<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$currencyOptions = $currencyOptions ?? [];
$school = $school ?? [];
$devises = $devises ?? [];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Devises</h1>
        <p class="text-muted">Gestion des devises et des taux de change pour la facturation.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Devises</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['devises_success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['devises_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['devises_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['devises_errors'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
          <?php foreach ($_SESSION['devises_errors'] as $message): ?>
            <li><?= htmlspecialchars($message) ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['devises_errors']); ?>
    <?php endif; ?>

    <div class="row">
      <div class="col-lg-4">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title">Devise principale de l'école</h3>
          </div>
          <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/devises/store">
              <input type="hidden" name="form_type" value="default_currency">
              <div class="mb-3">
                <label class="form-label">Devise principale</label>
                <select name="devise_principale" class="form-select" required>
                  <?php foreach ($currencyOptions as $code => $label): ?>
                    <option value="<?= htmlspecialchars($code) ?>" <?= (($school['devise_principale'] ?? '') === $code) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($code . ' — ' . $label) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
          </div>
        </div>

        <div class="card card-outline card-secondary mt-3">
          <div class="card-header">
            <h3 class="card-title">Ajouter / modifier un taux</h3>
          </div>
          <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/devises/store" id="deviseForm">
              <input type="hidden" name="form_type" value="rate">
              <input type="hidden" name="id" id="deviseId" value="0">

              <div class="mb-3">
                <label class="form-label">Code de la devise</label>
                <input type="text" name="code" id="deviseCode" class="form-control" maxlength="5" required placeholder="USD">
              </div>

              <div class="mb-3">
                <label class="form-label">Libellé</label>
                <input type="text" name="libelle" id="deviseLabel" class="form-control" required placeholder="Dollar américain">
              </div>

              <div class="mb-3">
                <label class="form-label">Taux de change vers USD</label>
                <input type="number" name="taux" id="deviseRate" class="form-control" step="0.000001" min="0" required placeholder="1.000000">
                <div class="form-text">Le taux est utilisé pour convertir vers la devise de référence USD.</div>
              </div>

              <div class="mb-3 form-check">
                <input type="checkbox" name="actif" id="deviseActif" class="form-check-input" value="1" checked>
                <label class="form-check-label" for="deviseActif">Actif</label>
              </div>

              <button type="submit" class="btn btn-success">Enregistrer le taux</button>
              <button type="button" class="btn btn-outline-secondary" id="resetDeviseForm">Nouveau taux</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title">Taux de change enregistrés</h3>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-sm table-striped">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Libellé</th>
                  <th>Taux (USD)</th>
                  <th>Actif</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($devises)): ?>
                  <tr><td colspan="5" class="text-center py-4">Aucun taux de change défini.</td></tr>
                <?php else: ?>
                  <?php foreach ($devises as $devise): ?>
                    <tr>
                      <td><?= htmlspecialchars($devise['code']) ?></td>
                      <td><?= htmlspecialchars($devise['libelle']) ?></td>
                      <td><?= number_format((float) $devise['taux'], 6) ?></td>
                      <td><?= $devise['actif'] ? 'Oui' : 'Non' ?></td>
                      <td>
                        <button type="button" class="btn btn-sm btn-outline-primary edit-rate-btn" data-id="<?= (int) $devise['id'] ?>" data-code="<?= htmlspecialchars($devise['code']) ?>" data-libelle="<?= htmlspecialchars($devise['libelle']) ?>" data-taux="<?= htmlspecialchars($devise['taux']) ?>" data-actif="<?= (int) $devise['actif'] ?>">
                          Modifier
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-rate-btn');
    const deviseId = document.getElementById('deviseId');
    const deviseCode = document.getElementById('deviseCode');
    const deviseLabel = document.getElementById('deviseLabel');
    const deviseRate = document.getElementById('deviseRate');
    const deviseActif = document.getElementById('deviseActif');
    const resetButton = document.getElementById('resetDeviseForm');

    editButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        deviseId.value = this.dataset.id || '0';
        deviseCode.value = this.dataset.code || '';
        deviseLabel.value = this.dataset.libelle || '';
        deviseRate.value = this.dataset.taux || '1.000000';
        deviseActif.checked = this.dataset.actif === '1';
        deviseCode.focus();
      });
    });

    if (resetButton) {
      resetButton.addEventListener('click', function () {
        deviseId.value = '0';
        deviseCode.value = '';
        deviseLabel.value = '';
        deviseRate.value = '1.000000';
        deviseActif.checked = true;
      });
    }
  });
</script>
