<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Mes paiements</h1>
    <p class="text-muted mb-0"><?= $escape(\App\Core\PersonName::format($student)) ?> · <?= $escape($student['matricule'] ?? '') ?></p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <div class="card h-100"><div class="card-body">
          <h2 class="h6">Total payé</h2>
          <?php if ($totalPaidByCurrency === []): ?><p class="text-muted mb-0">Aucun paiement enregistré.</p><?php endif; ?>
          <?php foreach ($totalPaidByCurrency as $currency => $amount): ?>
            <div class="fw-semibold"><?= $escape(number_format($amount, 2, ',', ' ') . ' ' . $currency) ?></div>
          <?php endforeach; ?>
        </div></div>
      </div>
      <div class="col-md-6">
        <div class="card h-100"><div class="card-body">
          <h2 class="h6">Solde restant</h2>
          <?php $hasOutstanding = false; ?>
          <?php foreach ($outstandingByCurrency as $currency => $amount): ?>
            <?php if ((float) $amount > 0): $hasOutstanding = true; ?>
              <div class="fw-semibold"><?= $escape(number_format((float) $amount, 2, ',', ' ') . ' ' . $currency) ?></div>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if (!$hasOutstanding): ?><p class="text-muted mb-0">Aucun solde restant enregistré.</p><?php endif; ?>
        </div></div>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h2 class="card-title">Historique des paiements</h2></div>
      <div class="card-body p-0">
        <?php if ($payments === []): ?>
          <p class="text-muted text-center py-5 mb-0">Aucun paiement enregistré pour votre compte.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead><tr><th>Date</th><th>Motif</th><th>Référence</th><th class="text-end">Montant</th><th class="text-end">Reçu</th></tr></thead>
              <tbody>
                <?php foreach ($payments as $payment): ?>
                  <tr>
                    <td class="text-nowrap"><?= $escape(formatDate($payment['date_operation'] ?? null)) ?></td>
                    <td><?= $escape($payment['frais_type_frais'] ?? $payment['libelle'] ?? 'Paiement') ?></td>
                    <td><?= $escape($payment['reference_recu'] ?? '—') ?></td>
                    <td class="text-end text-nowrap fw-semibold">
                      <?= $escape(number_format((float) ($payment['montant'] ?? 0), 2, ',', ' ')) ?>
                      <?= $escape(strtoupper(trim((string) ($payment['frais_devise'] ?? 'USD'))) ?: 'USD') ?>
                    </td>
                    <td class="text-end text-nowrap">
                      <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/paiements/receipt?id=<?= rawurlencode((string) ($payment['id'] ?? '')) ?>">Voir le reçu</a>
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
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
