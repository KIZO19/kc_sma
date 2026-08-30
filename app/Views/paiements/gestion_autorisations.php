<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Autorisations de paiement</h1>
        <p class="text-muted">Le comptable décide quels rôles peuvent enregistrer des paiements pour quels frais.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/paiements">Paiements</a></li>
          <li class="breadcrumb-item active">Autorisations</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['paiements_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['paiements_success']) ?></div>
      <?php unset($_SESSION['paiements_success']); ?>
    <?php endif; ?>

    <div class="card card-outline card-primary">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
              <tr>
                <th>Rôle</th>
                <?php foreach (($fees ?? []) as $fee): ?>
                  <th><?= htmlspecialchars((string) ($fee['type_frais'] ?? 'Frais')) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach (($roleList ?? []) as $roleKey => $roleLabel): ?>
                <tr>
                  <td><?= htmlspecialchars($roleLabel) ?></td>
                  <?php foreach (($fees ?? []) as $fee): ?>
                    <?php $feeId = (int) ($fee['id'] ?? 0); $allowed = !empty(($matrix[$roleKey] ?? [])) && in_array($feeId, (array) ($matrix[$roleKey] ?? []), true); ?>
                    <td class="text-center">
                      <form method="post" action="<?= BASE_URL ?>/paiements/gestionAutorisations">
                        <input type="hidden" name="role" value="<?= htmlspecialchars($roleKey) ?>">
                        <input type="hidden" name="frais_id" value="<?= (int) $feeId ?>">
                        <input type="hidden" name="enabled" value="0">
                        <div class="form-check form-switch d-inline-block">
                          <input class="form-check-input" type="checkbox" name="enabled" value="1" <?= $allowed ? 'checked' : '' ?> onchange="this.form.submit()">
                        </div>
                      </form>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
