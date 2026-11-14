<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$accounts = $accounts ?? [];
$outflows = $outflows ?? [];
$canManage = !empty($canManage);
$canApprove = !empty($canApprove);
$csrfToken = (string) ($csrfToken ?? '');
$formatAmount = static fn ($amount): string => number_format((float) $amount, 2, ',', ' ');
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Caisses &amp; Banques</h1>
    <p class="text-muted mb-0">Les sorties et paiements de salaires sont débités uniquement après approbation.</p>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['cash_outflows_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars((string) $_SESSION['cash_outflows_success'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php unset($_SESSION['cash_outflows_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['cash_outflows_error'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars((string) $_SESSION['cash_outflows_error'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php unset($_SESSION['cash_outflows_error']); ?>
    <?php endif; ?>

    <div class="row g-3">
      <?php if ($canManage): ?>
        <div class="col-xl-4">
          <div class="card card-outline card-primary">
            <div class="card-header"><h2 class="h5 mb-0">Demander une sortie</h2></div>
            <form method="post" action="<?= BASE_URL ?>/comptes/createOutflow">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
              <div class="card-body">
                <p class="small text-muted">La demande restera en attente et ne débitera pas le compte avant l’approbation d’un rôle désigné.</p>
                <?php if (!$accounts): ?>
                  <div class="alert alert-warning mb-0">Aucune caisse ou banque n’est configurée pour cette école.</div>
                <?php else: ?>
                  <div class="mb-3">
                    <label class="form-label" for="outflow-account">Caisse ou banque</label>
                    <select class="form-select" id="outflow-account" name="caisse_banque_id" required>
                      <option value="">Choisir un compte…</option>
                      <?php foreach ($accounts as $account): ?>
                        <option value="<?= (int) $account['id'] ?>">
                          <?= htmlspecialchars($account['nom_compte'] . ' · Solde ' . $formatAmount($account['solde_actuel']) . ' ' . $account['devise'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="outflow-amount">Montant</label>
                    <input class="form-control" id="outflow-amount" name="montant" type="number" min="0.01" max="9999999999.99" step="0.01" required>
                  </div>
                  <div>
                    <label class="form-label" for="outflow-reason">Motif de la sortie</label>
                    <input class="form-control" id="outflow-reason" name="motif" maxlength="255" required>
                  </div>
                <?php endif; ?>
              </div>
              <?php if ($accounts): ?>
                <div class="card-footer">
                  <button class="btn btn-primary" type="submit">Soumettre pour approbation</button>
                </div>
              <?php endif; ?>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <div class="<?= $canManage ? 'col-xl-8' : 'col-12' ?>">
        <div class="card card-outline card-secondary">
          <div class="card-header"><h2 class="h5 mb-0">Comptes de l’école</h2></div>
          <div class="card-body p-0">
            <?php if (!$accounts): ?>
              <p class="p-3 mb-0">Aucune caisse ou banque n’est configurée pour cette école.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light"><tr><th>Compte</th><th>Type</th><th class="text-end">Solde disponible</th></tr></thead>
                  <tbody>
                    <?php foreach ($accounts as $account): ?>
                      <tr>
                        <td><?= htmlspecialchars((string) $account['nom_compte'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $account['type_compte'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end fw-semibold"><?= $formatAmount($account['solde_actuel']) ?> <?= htmlspecialchars((string) $account['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card card-outline card-info">
          <div class="card-header"><h2 class="h5 mb-0">Sorties et validations</h2></div>
          <div class="card-body p-0">
            <?php if (!$outflows): ?>
              <p class="p-3 mb-0">Aucune sortie n’a été soumise.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr><th>Date</th><th>Motif</th><th>Compte</th><th class="text-end">Montant</th><th>Demandeur</th><th>Statut / décision</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($outflows as $outflow): ?>
                      <?php
                        $isPending = $outflow['statut'] === 'En attente';
                        $isPayroll = $outflow['paie_agent_id'] !== null;
                        $agentName = trim(implode(' ', array_filter([
                            $outflow['agent_nom'] ?? '',
                            $outflow['agent_postnom'] ?? '',
                            $outflow['agent_prenom'] ?? '',
                        ])));
                      ?>
                      <tr>
                        <td><?= htmlspecialchars((string) $outflow['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                          <?= htmlspecialchars($isPayroll ? 'Paie — ' . $agentName : (string) $outflow['motif'], ENT_QUOTES, 'UTF-8') ?>
                          <?php if ($isPayroll): ?><div class="small text-muted">Fiche de paie #<?= (int) $outflow['paie_agent_id'] ?></div><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($outflow['nom_compte'] . ' · ' . $outflow['type_compte'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end fw-semibold"><?= $formatAmount($outflow['montant']) ?> <?= htmlspecialchars((string) $outflow['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $outflow['demandeur_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                          <?php if ($outflow['statut'] === 'Approuvée'): ?>
                            <span class="badge text-bg-success">Approuvée</span>
                          <?php elseif ($outflow['statut'] === 'Refusée'): ?>
                            <span class="badge text-bg-danger">Refusée</span>
                          <?php else: ?>
                            <span class="badge text-bg-warning">En attente</span>
                          <?php endif; ?>
                          <?php if (!$isPending): ?>
                            <div class="small"><?= htmlspecialchars((string) ($outflow['decideur_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if (!empty($outflow['decision_comment'])): ?>
                              <div class="small text-muted"><?= htmlspecialchars((string) $outflow['decision_comment'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                          <?php elseif ($canApprove && (int) $outflow['created_by_user_id'] !== (int) ($user['id'] ?? 0)): ?>
                            <form method="post" action="<?= BASE_URL ?>/comptes/approveOutflow" class="mt-2">
                              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                              <input type="hidden" name="sortie_id" value="<?= (int) $outflow['id'] ?>">
                              <label class="visually-hidden" for="outflow-comment-<?= (int) $outflow['id'] ?>">Commentaire de décision</label>
                              <input class="form-control form-control-sm mb-1" id="outflow-comment-<?= (int) $outflow['id'] ?>" name="commentaire" maxlength="255" placeholder="Commentaire (facultatif)">
                              <button class="btn btn-sm btn-success" type="submit">Approuver et débiter</button>
                            </form>
                            <form method="post" action="<?= BASE_URL ?>/comptes/rejectOutflow" class="mt-1">
                              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                              <input type="hidden" name="sortie_id" value="<?= (int) $outflow['id'] ?>">
                              <label class="visually-hidden" for="outflow-reject-comment-<?= (int) $outflow['id'] ?>">Motif du refus</label>
                              <input class="form-control form-control-sm mb-1" id="outflow-reject-comment-<?= (int) $outflow['id'] ?>" name="commentaire" maxlength="255" placeholder="Motif du refus (facultatif)">
                              <button class="btn btn-sm btn-outline-danger" type="submit">Refuser</button>
                            </form>
                          <?php elseif ($isPending): ?>
                            <div class="small text-muted mt-1">En attente d’un autre utilisateur ayant un rôle approbateur.</div>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
