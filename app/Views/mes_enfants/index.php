<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$displayDate = static function ($value): string {
  $timestamp = strtotime((string) $value);
  return $timestamp === false ? '—' : date('d/m/Y', $timestamp);
};
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Mes enfants</h1>
    <p class="text-muted mb-0">Consultez les notes et le suivi disciplinaire de vos enfants inscrits dans cet établissement.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['mes_enfants_success'])): ?>
      <div class="alert alert-success" role="status"><?= $escape($_SESSION['mes_enfants_success']) ?></div>
      <?php unset($_SESSION['mes_enfants_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['mes_enfants_errors'])): ?>
      <div class="alert alert-danger" role="alert">
        <?php foreach ((array) $_SESSION['mes_enfants_errors'] as $error): ?>
          <div><?= $escape($error) ?></div>
        <?php endforeach; ?>
      </div>
      <?php unset($_SESSION['mes_enfants_errors']); ?>
    <?php endif; ?>
    <?php if (!empty($pendingChildRequests)): ?>
      <div class="card card-outline card-warning mb-3">
        <div class="card-header">
          <h2 class="h5 card-title mb-0">Demandes de rattachement à valider</h2>
        </div>
        <div class="card-body">
          <p class="text-muted">Une autorité scolaire souhaite associer ces élèves à votre compte. Vérifiez les informations avant de répondre.</p>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr><th>Élève</th><th>Matricule</th><th>Classe</th><th>Demande envoyée par</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($pendingChildRequests as $request): ?>
                  <?php $requestName = \App\Core\PersonName::format($request); ?>
                  <tr>
                    <td><?= $escape($requestName ?: '—') ?></td>
                    <td><?= $escape($request['matricule'] ?: '—') ?></td>
                    <td><?= $escape($request['nom_classe'] ?: 'Non renseignée') ?><?= !empty($request['nom_option']) ? ' · ' . $escape($request['nom_option']) : '' ?></td>
                    <td><?= $escape($request['requested_by_name'] ?: 'Autorité scolaire') ?></td>
                    <td>
                      <div class="d-flex flex-wrap gap-2">
                        <form method="post" action="<?= BASE_URL ?>/mes-enfants/respondToRequest">
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken ?? '') ?>">
                          <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                          <input type="hidden" name="decision" value="accepted">
                          <button class="btn btn-sm btn-success" type="submit">Valider</button>
                        </form>
                        <form method="post" action="<?= BASE_URL ?>/mes-enfants/respondToRequest">
                          <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken ?? '') ?>">
                          <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                          <input type="hidden" name="decision" value="rejected">
                          <button class="btn btn-sm btn-outline-danger" type="submit">Refuser</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (empty($children)): ?>
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="bi bi-people display-5 text-muted" aria-hidden="true"></i>
          <p class="text-muted mt-3 mb-0">Aucun enfant n’est actuellement associé à votre compte dans cette école. Contactez le secrétariat si cette liste vous semble incomplète.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($children as $child): ?>
          <?php
            $notes = $child['notes'] ?? [];
            $discipline = $child['discipline'] ?? [];
            $childName = \App\Core\PersonName::format($child);
            $childId = (int) $child['id'];
          ?>
          <div class="col-12">
            <article class="card card-outline card-primary">
              <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h2 class="h5 mb-1"><?= $escape($childName) ?></h2>
                  <div class="small text-muted">
                    Matricule : <?= $escape($child['matricule'] ?: '—') ?>
                    <span class="mx-1">·</span>
                    Classe : <?= $escape($child['nom_classe'] ?: 'Non renseignée') ?><?= !empty($child['nom_option']) ? ' · ' . $escape($child['nom_option']) : '' ?>
                  </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <span class="badge text-bg-primary"><?= count($notes) ?> note<?= count($notes) === 1 ? '' : 's' ?></span>
                  <span class="badge text-bg-secondary"><?= count($discipline) ?> incident<?= count($discipline) === 1 ? '' : 's' ?></span>
                </div>
              </div>
              <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                  <div>
                    <strong>Paiements enregistrés</strong>
                    <?php if (empty($child['payment_summary']['total_by_currency'])): ?>
                      <div class="small text-muted">Aucun paiement enregistré.</div>
                    <?php else: ?>
                      <div class="small">
                        <?php foreach ($child['payment_summary']['total_by_currency'] as $currency => $amount): ?>
                          <span class="me-3"><?= $escape($currency) ?> <?= number_format((float) $amount, 2, ',', ' ') ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/paiements-enfant#enfant-<?= $childId ?>">Voir les paiements</a>
                </div>
                <details class="mb-3">
                  <summary class="fw-semibold py-2">Notes scolaires <span class="text-muted fw-normal">(<?= count($notes) ?>)</span></summary>
                  <?php if ($notes === []): ?>
                    <p class="text-muted mt-3 mb-0">Aucune note n’a encore été enregistrée.</p>
                  <?php else: ?>
                    <div class="table-responsive mt-3">
                      <table class="table table-sm table-striped table-hover align-middle mb-0">
                        <thead>
                          <tr><th>Cours</th><th>Période</th><th>Évaluation</th><th>Date</th><th class="text-end">Note obtenue</th></tr>
                        </thead>
                        <tbody>
                          <?php foreach ($notes as $note): ?>
                            <tr>
                              <td><?= $escape($note['nom_cours'] ?: 'Cours non précisé') ?></td>
                              <td><?= $escape($note['nom_periode'] ?: '—') ?></td>
                              <td><?= $escape($note['type_evaluation'] ?: 'Évaluation') ?></td>
                              <td class="text-nowrap"><?= $escape($displayDate($note['date_evaluation'] ?? null)) ?></td>
                              <td class="text-end text-nowrap fw-semibold">
                                <?= $escape($note['note_obtenue']) ?>
                                <?php if ($note['ponderation_max'] !== null): ?> / <?= $escape($note['ponderation_max']) ?><?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </details>

                <details>
                  <summary class="fw-semibold py-2">Fiche disciplinaire <span class="text-muted fw-normal">(<?= count($discipline) ?> incident<?= count($discipline) === 1 ? '' : 's' ?>)</span></summary>
                  <?php if ($discipline === []): ?>
                    <p class="text-muted mt-3 mb-0">Aucun incident disciplinaire n’est enregistré.</p>
                  <?php else: ?>
                    <div class="table-responsive mt-3">
                      <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                          <tr><th>Date</th><th>Fait consigné</th><th>Sanction</th><th class="text-end">Points retirés</th></tr>
                        </thead>
                        <tbody>
                          <?php foreach ($discipline as $entry): ?>
                            <tr>
                              <td class="text-nowrap"><?= $escape($displayDate($entry['date_evenement'] ?? null)) ?></td>
                              <td><?= $escape($entry['faute'] ?: '—') ?></td>
                              <td><?= $escape($entry['sanction'] ?: '—') ?></td>
                              <td class="text-end"><?= (int) ($entry['retrait_points'] ?? 0) ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </details>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
