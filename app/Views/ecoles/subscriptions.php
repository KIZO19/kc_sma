<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1><?= empty($isSuperAdmin) ? 'Mon abonnement' : 'Gestion des abonnements' ?></h1>
        <p class="text-muted mb-0"><?= empty($isSuperAdmin)
            ? 'Consultez l’état et l’historique de l’abonnement de votre école.'
            : 'Consultez les abonnements, traitez les demandes des écoles et gérez les accès.' ?></p>
      </div>
      <?php if (!empty($isSuperAdmin)): ?>
        <a href="<?= BASE_URL ?>/ecoles/addSubscription<?= !empty($selectedSchoolId) ? '?ecole_id=' . (int) $selectedSchoolId : '' ?>" class="btn btn-primary">
          <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Attribuer un abonnement
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (empty($isSuperAdmin)): ?>
      <?php
        $latestSubscription = $subscriptions[0] ?? null;
        $hasPendingRequest = false;
        foreach (($subscriptionRequests ?? []) as $request) {
            if (($request['statut'] ?? '') === 'En_attente') {
                $hasPendingRequest = true;
                break;
            }
        }
      ?>
      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header"><h2 class="card-title">Abonnement de <?= htmlspecialchars($school['nom_etablissement'] ?? 'votre école', ENT_QUOTES, 'UTF-8') ?></h2></div>
        <div class="card-body">
          <?php if ($latestSubscription): ?>
            <?php
              $subscriptionActive = ($latestSubscription['statut_abonnement'] ?? '') === 'Actif'
                  && strtotime((string) ($latestSubscription['date_debut'] ?? '')) <= time()
                  && strtotime((string) ($latestSubscription['date_fin'] ?? '')) >= strtotime('today');
            ?>
            <div class="row g-3">
              <div class="col-md-3"><div class="text-muted small">Plan</div><strong><?= htmlspecialchars((string) ($latestSubscription['nom_plan'] ?? 'Plan'), ENT_QUOTES, 'UTF-8') ?></strong></div>
              <div class="col-md-3"><div class="text-muted small">Statut</div>
                <span class="badge text-bg-<?= $subscriptionActive ? 'success' : 'danger' ?>"><?= $subscriptionActive ? 'Actif' : htmlspecialchars((string) ($latestSubscription['statut_abonnement'] ?? 'Expiré'), ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <div class="col-md-3"><div class="text-muted small">Début</div><?= htmlspecialchars(formatDate($latestSubscription['date_debut'] ?? null), ENT_QUOTES, 'UTF-8') ?></div>
              <div class="col-md-3"><div class="text-muted small">Fin</div><?= htmlspecialchars(formatDate($latestSubscription['date_fin'] ?? null), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          <?php else: ?>
            <p class="mb-0 text-muted">Aucun abonnement n’est encore enregistré pour votre école.</p>
          <?php endif; ?>
          <?php if ($hasPendingRequest): ?>
            <div class="alert alert-info mt-3 mb-0"><i class="bi bi-hourglass-split me-1" aria-hidden="true"></i>Une demande est en attente de traitement par le super-administrateur.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="alert alert-info">
        Cette page est en lecture seule. Pour modifier ou renouveler l’abonnement, veuillez contacter le super-administrateur.
      </div>

      <div class="card">
        <div class="card-header"><h2 class="card-title">Historique des demandes</h2></div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Demande</th><th>Tarif</th><th>Date</th><th>Statut</th><th>Réponse</th></tr></thead>
            <tbody>
              <?php foreach (($subscriptionRequests ?? []) as $request): ?>
                <?php
                  $requestStatus = (string) ($request['statut'] ?? '');
                  $requestBadge = $requestStatus === 'Approuvee' ? 'success' : ($requestStatus === 'Refusee' ? 'danger' : 'warning');
                ?>
                <tr>
                  <td><?= $request['type_demande'] === 'renouvellement' ? 'Renouvellement' : 'Nouvel abonnement' ?>
                    <?php if (!empty($request['message'])): ?><div class="small text-muted"><?= nl2br(htmlspecialchars((string) $request['message'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars((string) ($request['nom_plan'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars(formatDate($request['created_at'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge text-bg-<?= $requestBadge ?>"><?= htmlspecialchars($requestStatus, ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars((string) ($request['reponse'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($subscriptionRequests)): ?><tr><td colspan="5" class="text-center text-muted py-4">Aucune demande n’a été soumise.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php else: ?>
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

    <?php
      $pendingRequests = array_values(array_filter(
          $subscriptionRequests ?? [],
          static fn (array $request): bool => ($request['statut'] ?? '') === 'En_attente'
      ));
    ?>
    <div class="card">
      <div class="card-header"><h2 class="card-title">Demandes d’abonnement des écoles</h2></div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light"><tr><th>École</th><th>Demandeur</th><th>Type</th><th>Tarif demandé</th><th>Message</th><th>Traitement</th></tr></thead>
          <tbody>
            <?php foreach ($pendingRequests as $request): ?>
              <?php $reviewFormId = 'reviewSubscriptionRequest' . (int) $request['id']; ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars((string) ($request['nom_etablissement'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($request['requester_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?><div class="small text-muted"><?= htmlspecialchars(formatDate($request['created_at'] ?? null), ENT_QUOTES, 'UTF-8') ?></div></td>
                <td><?= $request['type_demande'] === 'renouvellement' ? 'Renouvellement' : 'Nouvel abonnement' ?></td>
                <td><?= htmlspecialchars((string) ($request['nom_plan'] ?? ''), ENT_QUOTES, 'UTF-8') ?><div class="small text-muted"><?= htmlspecialchars(number_format((float) ($request['prix'] ?? 0), 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?> USD / <?= (int) ($request['duree_mois'] ?? 0) ?> mois</div></td>
                <td><?= htmlspecialchars((string) ($request['message'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="min-width: 230px">
                  <form id="<?= $reviewFormId ?>" method="post" action="<?= BASE_URL ?>/ecoles/reviewSubscriptionRequest">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                    <textarea name="reponse" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" placeholder="Réponse facultative à l’école"></textarea>
                  </form>
                  <div class="d-flex gap-1">
                    <button form="<?= $reviewFormId ?>" name="statut" value="Approuvee" class="btn btn-sm btn-success">Approuver</button>
                    <button form="<?= $reviewFormId ?>" name="statut" value="Refusee" class="btn btn-sm btn-outline-danger">Refuser</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($pendingRequests)): ?><tr><td colspan="6" class="text-center text-muted py-4">Aucune demande en attente.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

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
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
