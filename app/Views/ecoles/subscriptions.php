<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1>Gestion des abonnements</h1>
        <p class="text-muted mb-0">Consultez l’historique, modifiez un abonnement ou résiliez son accès.</p>
      </div>
      <a href="<?= BASE_URL ?>/ecoles/addSubscription<?= !empty($selectedSchoolId) ? '?ecole_id=' . (int) $selectedSchoolId : '' ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Attribuer ou renouveler
      </a>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['ecoles_subscription_errors'])): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($_SESSION['ecoles_subscription_errors'] as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php unset($_SESSION['ecoles_subscription_errors']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['ecoles_subscription_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['ecoles_subscription_success']) ?></div>
      <?php unset($_SESSION['ecoles_subscription_success']); ?>
    <?php endif; ?>

    <div class="alert alert-info">
      La modification d’un abonnement ne change pas le montant déjà enregistré comme payé. Pour prolonger l’accès, modifiez la date de fin ou attribuez un nouvel abonnement.
    </div>

    <form method="get" action="<?= BASE_URL ?>/ecoles/subscriptions" class="row g-2 align-items-end mb-3">
      <div class="col-sm-6 col-lg-4">
        <label for="subscriptionSchoolFilter" class="form-label">Filtrer par école</label>
        <select id="subscriptionSchoolFilter" name="ecole_id" class="form-select">
          <option value="">Toutes les écoles</option>
          <?php foreach (($schools ?? []) as $school): ?>
            <option value="<?= (int) $school['id'] ?>" <?= (int) ($selectedSchoolId ?? 0) === (int) $school['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($school['nom_etablissement'] ?? '') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto"><button type="submit" class="btn btn-outline-primary">Filtrer</button></div>
    </form>

    <div class="card">
      <div class="card-body p-0">
        <?php foreach (($subscriptions ?? []) as $subscription): ?>
          <form id="subscription<?= (int) $subscription['id'] ?>" method="post" action="<?= BASE_URL ?>/ecoles/updateSubscription">
            <input type="hidden" name="subscription_id" value="<?= (int) $subscription['id'] ?>">
          </form>
        <?php endforeach; ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">École</th>
                <th>Plan</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Montant payé</th>
                <th>Statut</th>
                <th class="text-end pe-3">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (($subscriptions ?? []) as $subscription): ?>
                <?php $formId = 'subscription' . (int) $subscription['id']; ?>
                <tr>
                  <td class="ps-3 fw-semibold"><?= htmlspecialchars($subscription['nom_etablissement'] ?? '') ?></td>
                  <td>
                    <select form="<?= $formId ?>" name="plan_id" class="form-select form-select-sm">
                      <option value="" <?= empty($subscription['plan_id']) ? 'selected' : '' ?>>Essai gratuit</option>
                      <?php foreach (($plans ?? []) as $plan): ?>
                        <option value="<?= (int) $plan['id'] ?>" <?= (int) ($subscription['plan_id'] ?? 0) === (int) $plan['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($plan['nom_plan']) ?> (<?= htmlspecialchars((string) $plan['prix']) ?> USD)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td><input form="<?= $formId ?>" type="date" name="date_debut" class="form-control form-control-sm" value="<?= htmlspecialchars(substr((string) ($subscription['date_debut'] ?? ''), 0, 10)) ?>" required></td>
                  <td><input form="<?= $formId ?>" type="date" name="date_fin" class="form-control form-control-sm" value="<?= htmlspecialchars(substr((string) ($subscription['date_fin'] ?? ''), 0, 10)) ?>" required></td>
                  <td><?= htmlspecialchars(number_format((float) ($subscription['montant_paye'] ?? 0), 2, '.', ' ')) ?> USD</td>
                  <td>
                    <select form="<?= $formId ?>" name="statut_abonnement" class="form-select form-select-sm">
                      <option value="Actif" <?= ($subscription['statut_abonnement'] ?? '') === 'Actif' ? 'selected' : '' ?>>Actif</option>
                      <option value="Expire" <?= ($subscription['statut_abonnement'] ?? '') === 'Expire' ? 'selected' : '' ?>>Expiré</option>
                      <option value="Resilie" <?= ($subscription['statut_abonnement'] ?? '') === 'Resilie' ? 'selected' : '' ?>>Résilié</option>
                    </select>
                  </td>
                  <td class="text-end pe-3">
                    <button form="<?= $formId ?>" type="submit" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($subscriptions)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun abonnement n’est encore attribué.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
