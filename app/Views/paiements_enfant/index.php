<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$displayDate = static function ($value): string {
  $timestamp = strtotime((string) $value);
  return $timestamp === false ? '—' : date('d/m/Y H:i', $timestamp);
};
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Paiements de mes enfants</h1>
    <p class="text-muted mb-0">Consultez les paiements enregistrés pour chacun de vos enfants dans cet établissement.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (empty($children)): ?>
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="bi bi-wallet2 display-5 text-muted" aria-hidden="true"></i>
          <p class="text-muted mt-3 mb-0">Aucun enfant n’est actuellement associé à votre compte dans cette école. Contactez le secrétariat si cette liste vous semble incomplète.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($children as $child): ?>
          <?php
            $childId = (int) $child['id'];
            $childName = \App\Core\PersonName::format($child);
            $childPayments = $paymentsByChild[$childId]['payments'] ?? [];
            $totals = $paymentsByChild[$childId]['total_by_currency'] ?? [];
            $outstanding = $solvencyByChild[$childId] ?? [];
          ?>
          <div class="col-12" id="enfant-<?= $childId ?>">
            <article class="card card-outline card-success">
              <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h2 class="h5 mb-1"><?= $escape($childName) ?></h2>
                  <div class="small text-muted">
                    Matricule : <?= $escape($child['matricule'] ?: '—') ?>
                    <span class="mx-1">·</span>
                    Classe : <?= $escape($child['nom_classe'] ?: 'Non renseignée') ?><?= !empty($child['nom_option']) ? ' · ' . $escape($child['nom_option']) : '' ?>
                  </div>
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-3 text-sm-end">
                  <div>
                    <div class="small text-muted">Solvabilité</div>
                    <?php if ($outstanding === []): ?>
                      <span class="badge text-bg-success">À jour</span>
                      <div class="small">Aucun solde dû</div>
                    <?php else: ?>
                      <span class="badge text-bg-warning">Reste à payer</span>
                      <?php foreach ($outstanding as $currency => $amount): ?>
                        <strong class="d-block"><?= $escape($currency) ?> <?= number_format((float) $amount, 2, ',', ' ') ?></strong>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <div>
                    <div class="small text-muted">Total payé</div>
                    <?php if ($totals === []): ?>
                      <strong>0,00</strong>
                    <?php else: ?>
                      <?php foreach ($totals as $currency => $amount): ?>
                        <strong class="d-block"><?= $escape($currency) ?> <?= number_format((float) $amount, 2, ',', ' ') ?></strong>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="card-body p-0">
                <?php if ($childPayments === []): ?>
                  <p class="text-muted text-center py-4 mb-0">Aucun paiement n’est encore enregistré pour cet enfant.</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                      <thead>
                        <tr><th class="ps-3">Date</th><th>Frais / libellé</th><th>Référence</th><th class="text-end">Montant payé</th><th class="text-end pe-3">Reçu</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($childPayments as $payment): ?>
                          <tr>
                            <td class="ps-3 text-nowrap"><?= $escape($displayDate($payment['date_operation'] ?? null)) ?></td>
                            <td><?= $escape(($payment['frais_type_frais'] ?? $payment['libelle'] ?? '') ?: 'Paiement') ?></td>
                            <td class="font-monospace"><?= $escape($payment['reference_recu'] ?: '—') ?></td>
                            <td class="text-end pe-3 text-nowrap fw-semibold">
                              <?= number_format((float) ($payment['montant'] ?? 0), 2, ',', ' ') ?>
                              <?= $escape(strtoupper((string) ($payment['frais_devise'] ?? 'USD')) ?: 'USD') ?>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                              <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/paiements/receipt?id=<?= rawurlencode((string) ($payment['id'] ?? '')) ?>">Voir le reçu</a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
