<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Demandes des écoles</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/ecoles">Écoles</a></li>
          <li class="breadcrumb-item active">Demandes</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['ecoles_subscription_errors'])): ?>
      <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
          <?php foreach ($_SESSION['ecoles_subscription_errors'] as $error): ?>
            <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php unset($_SESSION['ecoles_subscription_errors']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['ecoles_subscription_success'])): ?>
      <div class="alert alert-success" role="status"><?= htmlspecialchars((string) $_SESSION['ecoles_subscription_success'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php unset($_SESSION['ecoles_subscription_success']); ?>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title h5 mb-0">Demandes de changement d’année scolaire</h2>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>École</th>
              <th>Demandeur</th>
              <th>Année actuelle</th>
              <th>Année demandée</th>
              <th>Traitement</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($pendingSchoolYearRequests ?? []) as $request): ?>
              <?php $reviewYearFormId = 'reviewSchoolYear' . (int) $request['id']; ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars((string) ($request['nom_etablissement'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?= htmlspecialchars((string) ($request['requester_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  <div class="small text-muted"><?= htmlspecialchars(formatDate($request['created_at'] ?? null), ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td><?= htmlspecialchars((string) ($request['annee_actuelle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="fw-semibold"><?= htmlspecialchars((string) ($request['annee_demandee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="min-width: 230px">
                  <form id="<?= $reviewYearFormId ?>" method="post" action="<?= BASE_URL ?>/ecoles/reviewSchoolYearChange">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                    <textarea name="reponse" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" placeholder="Réponse facultative à l’école"></textarea>
                  </form>
                  <div class="d-flex gap-1">
                    <button form="<?= $reviewYearFormId ?>" name="statut" value="Approuvee" class="btn btn-sm btn-success">Approuver</button>
                    <button form="<?= $reviewYearFormId ?>" name="statut" value="Refusee" class="btn btn-sm btn-outline-danger">Refuser</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($pendingSchoolYearRequests)): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">Aucune demande de changement d’année scolaire en attente.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title h5 mb-0">Demandes d’abonnement</h2>
        <a href="<?= BASE_URL ?>/ecoles/subscriptions" class="btn btn-sm btn-outline-primary">Gérer les abonnements</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>École</th>
              <th>Demandeur</th>
              <th>Type</th>
              <th>Tarif demandé</th>
              <th>Message</th>
              <th>Traitement</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($pendingSubscriptionRequests ?? []) as $request): ?>
              <?php $reviewFormId = 'reviewSubscriptionRequest' . (int) $request['id']; ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars((string) ($request['nom_etablissement'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?= htmlspecialchars((string) ($request['requester_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  <div class="small text-muted"><?= htmlspecialchars(formatDate($request['created_at'] ?? null), ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td><?= ($request['type_demande'] ?? '') === 'renouvellement' ? 'Renouvellement' : 'Nouvel abonnement' ?></td>
                <td>
                  <?= htmlspecialchars((string) ($request['nom_plan'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  <div class="small text-muted">
                    <?= htmlspecialchars(number_format((float) ($request['prix'] ?? 0), 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?> USD /
                    <?= (int) ($request['duree_mois'] ?? 0) ?> mois
                  </div>
                </td>
                <td><?= nl2br(htmlspecialchars((string) ($request['message'] ?? '—'), ENT_QUOTES, 'UTF-8')) ?></td>
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
            <?php if (empty($pendingSubscriptionRequests)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">Aucune demande d’abonnement en attente.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header"><h2 class="card-title h5 mb-0">Inscriptions d’écoles à valider</h2></div>
          <div class="card-body">
            <?php if (empty($pending)): ?>
              <div class="alert alert-info">Aucune demande en attente.</div>
            <?php else: ?>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending as $p): ?>
                    <tr>
                      <td><?= (int)$p['id'] ?></td>
                      <td><?= htmlspecialchars($p['nom_etablissement']) ?></td>
                      <td><?= htmlspecialchars($p['email_officiel'] ?? '') ?></td>
                      <td><?= htmlspecialchars($p['telephone_contact'] ?? '') ?></td>
                      <td><?= htmlspecialchars(formatDate($p['date_creation_compte'] ?? null)) ?></td>
                      <td>
                        <form method="post" action="<?= BASE_URL ?>/ecoles/confirm" style="display:inline">
                          <input type="hidden" name="ecole_id" value="<?= (int)$p['id'] ?>">
                          <button class="btn btn-sm btn-success">Confirmer</button>
                        </form>
                        <a href="<?= BASE_URL ?>/ecoles/edit?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                        <form method="post" action="<?= BASE_URL ?>/ecoles/delete" style="display:inline" onsubmit="return confirm('Confirmer la suppression de cette école ?');">
                          <input type="hidden" name="ecole_id" value="<?= (int)$p['id'] ?>">
                          <button class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <?php if (!empty($pagination)): ?>
                <nav>
                  <ul class="pagination">
                    <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                      <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/ecoles/requests?page=<?= $i ?>"><?= $i ?></a>
                      </li>
                    <?php endfor; ?>
                  </ul>
                </nav>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>