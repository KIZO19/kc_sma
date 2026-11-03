<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1>Tarifs des abonnements</h1>
        <p class="text-muted mb-0">Définissez les tarifs, durées et plafonds applicables aux offres proposées aux écoles.</p>
      </div>
      <a href="<?= BASE_URL ?>/ecoles/addSubscription" class="btn btn-outline-primary">
        <i class="bi bi-credit-card me-1" aria-hidden="true"></i>Attribuer un abonnement
      </a>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['ecoles_plans_errors'])): ?>
      <div class="alert alert-danger alert-dismissible">
        <ul class="mb-0">
          <?php foreach ($_SESSION['ecoles_plans_errors'] as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
      <?php unset($_SESSION['ecoles_plans_errors']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['ecoles_plans_success'])): ?>
      <div class="alert alert-success alert-dismissible">
        <?= htmlspecialchars($_SESSION['ecoles_plans_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
      <?php unset($_SESSION['ecoles_plans_success']); ?>
    <?php endif; ?>

    <div class="card card-outline card-primary mb-4">
      <div class="card-header"><h2 class="h6 fw-bold mb-0">Créer un tarif</h2></div>
      <form method="post" action="<?= BASE_URL ?>/ecoles/plans">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="newPlanName" class="form-label">Nom du plan</label>
              <input id="newPlanName" type="text" name="nom_plan" class="form-control" maxlength="50" required>
            </div>
            <div class="col-md-2">
              <label for="newPlanDuration" class="form-label">Durée (mois)</label>
              <input id="newPlanDuration" type="number" name="duree_mois" class="form-control" min="1" max="1200" required>
            </div>
            <div class="col-md-3">
              <label for="newPlanPrice" class="form-label">Tarif (USD)</label>
              <input id="newPlanPrice" type="number" name="prix" class="form-control" min="0" step="0.01" required>
            </div>
            <div class="col-md-3">
              <label for="newPlanDescription" class="form-label">Description</label>
              <input id="newPlanDescription" type="text" name="description" class="form-control">
            </div>
          </div>
          <fieldset class="border rounded p-3 mt-3">
            <legend class="float-none w-auto px-2 fs-6 fw-semibold">Plafonds inclus dans ce tarif</legend>
            <p class="form-text mt-0">Ces limites seront reprises automatiquement lors de l’attribution de ce plan. 0 = illimité.</p>
            <div class="row g-3">
              <div class="col-md-4">
                <label for="newPlanMaxStudents" class="form-label">Maximum d’élèves</label>
                <input id="newPlanMaxStudents" type="number" name="max_students" class="form-control" min="0" max="1000000" value="0" required>
              </div>
              <div class="col-md-4">
                <label for="newPlanMaxParents" class="form-label">Comptes parents</label>
                <input id="newPlanMaxParents" type="number" name="max_parent_accounts" class="form-control" min="0" max="1000000" value="0" required>
              </div>
              <div class="col-md-4">
                <label for="newPlanMaxStaff" class="form-label">Comptes agents</label>
                <input id="newPlanMaxStaff" type="number" name="max_staff_accounts" class="form-control" min="0" max="1000000" value="0" required>
                <div class="form-text">Enseignants et personnel administratif.</div>
              </div>
            </div>
          </fieldset>
        </div>
        <div class="card-footer text-end">
          <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Créer le tarif</button>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="h6 fw-bold mb-0">Tarifs disponibles</h2>
      </div>
      <div class="card-body p-0">
        <?php foreach (($plans ?? []) as $plan): ?>
          <form id="updatePlan<?= (int) $plan['id'] ?>" method="post" action="<?= BASE_URL ?>/ecoles/plans">
            <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
          </form>
        <?php endforeach; ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th class="ps-3">Plan</th><th>Durée</th><th>Tarif (USD)</th><th>Description</th><th>Plafonds (0 = illimité)</th><th class="text-end pe-3">Action</th></tr>
            </thead>
            <tbody>
              <?php foreach (($plans ?? []) as $plan): ?>
                <tr>
                  <td class="ps-3"><input form="updatePlan<?= (int) $plan['id'] ?>" type="text" name="nom_plan" class="form-control form-control-sm" value="<?= htmlspecialchars($plan['nom_plan'] ?? '') ?>" maxlength="50" required></td>
                  <td><input form="updatePlan<?= (int) $plan['id'] ?>" type="number" name="duree_mois" class="form-control form-control-sm" value="<?= (int) ($plan['duree_mois'] ?? 0) ?>" min="1" max="1200" required></td>
                  <td><input form="updatePlan<?= (int) $plan['id'] ?>" type="number" name="prix" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($plan['prix'] ?? 0)) ?>" min="0" step="0.01" required></td>
                  <td><input form="updatePlan<?= (int) $plan['id'] ?>" type="text" name="description" class="form-control form-control-sm" value="<?= htmlspecialchars($plan['description'] ?? '') ?>"></td>
                  <td style="min-width: 180px">
                    <label class="form-label small mb-1">Élèves</label>
                    <input form="updatePlan<?= (int) $plan['id'] ?>" type="number" name="max_students" class="form-control form-control-sm mb-1" value="<?= (int) ($plan['max_students'] ?? 0) ?>" min="0" max="1000000" required>
                    <label class="form-label small mb-1">Parents</label>
                    <input form="updatePlan<?= (int) $plan['id'] ?>" type="number" name="max_parent_accounts" class="form-control form-control-sm mb-1" value="<?= (int) ($plan['max_parent_accounts'] ?? 0) ?>" min="0" max="1000000" required>
                    <label class="form-label small mb-1">Agents</label>
                    <input form="updatePlan<?= (int) $plan['id'] ?>" type="number" name="max_staff_accounts" class="form-control form-control-sm" value="<?= (int) ($plan['max_staff_accounts'] ?? 0) ?>" min="0" max="1000000" required>
                  </td>
                  <td class="text-end pe-3"><button form="updatePlan<?= (int) $plan['id'] ?>" type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer</button></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($plans)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun tarif n’est défini. Créez un plan pour pouvoir attribuer des abonnements.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer small text-muted">
        La modification d’un tarif et de ses plafonds s’applique aux prochains abonnements. Les abonnements déjà enregistrés conservent leur montant et leurs plafonds.
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
