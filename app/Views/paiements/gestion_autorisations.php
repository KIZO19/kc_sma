<?php
$pageStyles = <<<'CSS'
<style>
  .payment-authorizations .permission-control {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    min-height: 2rem;
    cursor: pointer;
    white-space: nowrap;
  }
  .payment-authorizations .permission-switch {
    width: 2.75rem;
    height: 1.4rem;
    margin: 0;
    border: 2px solid #6c757d;
    border-radius: 2rem;
    background-color: #e9ecef;
    background-image: radial-gradient(circle, #fff 58%, transparent 62%);
    background-position: left center;
    background-size: 1.1rem 1.1rem;
    cursor: pointer;
    transition: background-position .15s ease-in-out;
  }
  .payment-authorizations .permission-switch:checked {
    border-color: #198754;
    background-color: #198754;
    background-position: right center;
  }
  .payment-authorizations .permission-switch:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .3);
  }
  .payment-authorizations .permission-status {
    padding: .2rem .4rem;
    border-radius: .25rem;
    font-size: .75rem;
    font-weight: 700;
  }
  .payment-authorizations .permission-status-enabled {
    color: #0f5132;
    background-color: #d1e7dd;
  }
  .payment-authorizations .permission-status-disabled {
    color: #842029;
    background-color: #f8d7da;
  }
</style>
CSS;
require __DIR__ . '/../partials/app_header.php';
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Autorisations de paiement</h1>
        <p class="text-muted">Gérez les rôles autorisés à enregistrer des paiements pour chaque frais.</p>
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
    <?php if (!empty($_SESSION['paiements_error'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['paiements_error']) ?></div>
      <?php unset($_SESSION['paiements_error']); ?>
    <?php endif; ?>

    <div class="card card-outline card-primary payment-authorizations">
      <div class="card-header">
        <h3 class="card-title">Autorisations de paiement par frais</h3>
        <p class="text-muted small mb-0 mt-1">Autorisez les rôles concernés à enregistrer un paiement pour chaque frais.</p>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
              <tr>
                <th scope="col">Frais</th>
                <?php foreach (($roleList ?? []) as $roleLabel): ?>
                  <th scope="col"><?= htmlspecialchars($roleLabel) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach (($fees ?? []) as $fee): ?>
                <?php $feeId = (int) ($fee['id'] ?? 0); ?>
                <tr>
                  <th scope="row"><?= htmlspecialchars((string) ($fee['type_frais'] ?? 'Frais')) ?></th>
                  <?php foreach (($roleList ?? []) as $roleKey => $roleLabel): ?>
                    <?php $allowed = !empty(($matrix[$roleKey] ?? [])) && in_array($feeId, (array) ($matrix[$roleKey] ?? []), true); ?>
                    <td class="text-center">
                      <form method="post" action="<?= BASE_URL ?>/paiements/gestionAutorisations<?= !empty($authorizationSchoolId) ? '?ecole_id=' . (int) $authorizationSchoolId : '' ?>">
                        <input type="hidden" name="permission_type" value="fee">
                        <input type="hidden" name="role" value="<?= htmlspecialchars($roleKey) ?>">
                        <input type="hidden" name="frais_id" value="<?= (int) $feeId ?>">
                        <input type="hidden" name="enabled" value="0">
                        <label class="permission-control">
                          <input class="form-check-input permission-switch" type="checkbox" name="enabled" value="1" aria-label="Autoriser <?= htmlspecialchars($roleLabel) ?> pour <?= htmlspecialchars((string) ($fee['type_frais'] ?? 'ce frais')) ?>" <?= $allowed ? 'checked' : '' ?> onchange="this.form.submit()">
                          <span class="permission-status <?= $allowed ? 'permission-status-enabled' : 'permission-status-disabled' ?>"><?= $allowed ? 'Autorisé' : 'Bloqué' ?></span>
                        </label>
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
