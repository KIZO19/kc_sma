<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$notificationStatus = $_SESSION['payment_notification_status'] ?? null;
unset($_SESSION['payment_notification_status']);
?>
<style>
  /* POS receipt styling */
  .pos-receipt { width: 280px; max-width: 100%; margin: 0 auto; padding: 2px; font-family: 'Courier New', monospace; font-size: 11px; position: relative; overflow: hidden; }
  .receipt-watermark { position: absolute; top: 25%; left: 5%; width: 90%; height: 45%; object-fit: contain; opacity: .08; pointer-events: none; z-index: 0; }
  .pos-receipt > *:not(.receipt-watermark) { position: relative; z-index: 1; }
  .pos-header { text-align: center; }
  .pos-header h4 { font-size: 14px; margin: 2px 0; overflow-wrap: anywhere; }
  .pos-line { border-top: 1px dashed #000; margin: 4px 0; }
  .pos-row { display:flex; justify-content:space-between; gap: 8px; line-height: 1.15; margin-bottom: 2px; }
  .pos-row > div:last-child { text-align:right; overflow-wrap:anywhere; }
  .pos-amount { font-weight: bold; font-size: 14px; }
  .qr { text-align:center; margin-top:3px; }
  @media print {
    @page { size: 80mm 150mm; margin: 0; }
    html, body {
      width: 80mm !important;
      height: auto !important;
      min-height: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
      overflow: visible !important;
      background: #fff !important;
    }
    body > *:not(.app-wrapper) { display: none !important; }
    body > .app-wrapper {
      display: block !important;
      position: static !important;
      width: 80mm !important;
      height: auto !important;
      min-height: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
      overflow: visible !important;
    }
    .app-wrapper > *:not(.content-wrapper) { display: none !important; }
    .app-wrapper > .content-wrapper {
      display: block !important;
      position: static !important;
      width: 80mm !important;
      height: auto !important;
      min-height: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
      overflow: visible !important;
    }
    .content-wrapper > *:not(.content) { display: none !important; }
    .content-wrapper > .content {
      display: block !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    .content > *:not(.container-fluid) { display: none !important; }
    .content > .container-fluid,
    .container-fluid > .row,
    .row > .col-md-6,
    .col-md-6 > .card,
    .card > .card-body {
      display: block !important;
      position: static !important;
      width: 80mm !important;
      height: auto !important;
      min-height: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
      overflow: visible !important;
      border: 0 !important;
      box-shadow: none !important;
    }
    .container-fluid > .row { display: block !important; }
    .card-body > *:not(.pos-receipt) { display: none !important; }
    .pos-receipt {
      position: relative;
      width: 76mm;
      max-width: 76mm;
      margin: 0 auto;
      padding: 0;
      overflow: visible;
      transform: none;
    }
    .pos-receipt .pos-row { display: flex !important; }
    .pos-receipt .no-print { display: none !important; }
    .receipt-watermark { opacity: .10; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
  }
</style>
<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <?php if (!empty($notificationStatus)): ?>
          <?php
            $notificationClass = match ($notificationStatus['status'] ?? '') {
              'success' => 'alert-success',
              'partial' => 'alert-warning',
              default => 'alert-danger',
            };
            $notificationTitle = match ($notificationStatus['status'] ?? '') {
              'success' => 'Message interne créé',
              'partial' => 'Message interne créé partiellement',
              default => 'Message interne au parent non créé',
            };
          ?>
          <div class="alert <?= $notificationClass ?> alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($notificationTitle, ENT_QUOTES, 'UTF-8') ?></strong><br>
            <?= htmlspecialchars($notificationStatus['message'] ?? 'Statut de notification indisponible.') ?>
            <?php if (!empty($notificationStatus['attempts']) && is_array($notificationStatus['attempts'])): ?>
              <ul class="mt-1 mb-0 small">
                <?php foreach ($notificationStatus['attempts'] as $attempt): ?>
                  <?php $channelLabel = ($attempt['channel'] ?? '') === 'sms' ? 'SMS' : 'WhatsApp'; ?>
                  <li>
                    <strong><?= htmlspecialchars($channelLabel, ENT_QUOTES, 'UTF-8') ?> :</strong>
                    <?= !empty($attempt['sent']) ? 'accepté par le fournisseur — livraison SIM non confirmée' : 'échec' ?>
                    <?php if (!empty($attempt['details'])): ?>
                      — <?= htmlspecialchars($attempt['details'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php elseif (!empty($notificationStatus['details'])): ?>
              <div class="mt-1 small"><?= htmlspecialchars($notificationStatus['details']) ?></div>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>
        <?php endif; ?>
        <div class="card">
          <div class="card-body">
            <div class="pos-receipt">
              <?php if (!empty($ecole_logo)): ?>
                <img class="receipt-watermark" src="<?= htmlspecialchars($ecole_logo, ENT_QUOTES, 'UTF-8') ?>" alt="">
              <?php endif; ?>
              <div class="pos-header">
                <?php if (!empty($ecole_logo)): ?>
                  <img src="<?= htmlspecialchars($ecole_logo, ENT_QUOTES, 'UTF-8') ?>" alt="Logo de l'école" style="display:block;width:30px;height:30px;object-fit:contain;margin:0 auto 3px;">
                <?php endif; ?>
                <h4><?= htmlspecialchars($ecole_name ?? APP_NAME) ?></h4>
                <div class="pos-line"></div>
                <div>REÇU DE PAIEMENT</div>
                <div class="text-muted">Réf: <strong><?= htmlspecialchars($ecriture['reference_recu'] ?? '') ?></strong></div>
              </div>

              <?php
                $eleveName = \App\Core\PersonName::format($ecriture);
                $totalPayeParDevise = is_array($total_paye_par_devise ?? null) ? $total_paye_par_devise : [];
                $resteParDevise = is_array($reste_a_payer_par_devise ?? null) ? $reste_a_payer_par_devise : [];
                $montantPaye = isset($ecriture['montant']) ? (float) $ecriture['montant'] : 0.0;
                $devise = strtoupper(trim($ecriture['transaction_devise'] ?? $ecriture['frais_devise'] ?? 'USD')) ?: 'USD';
                $applicationBasePath = \App\Core\Auth::getApplicationBasePath();
                $schoolSlug = \App\Core\Auth::getSchoolSlug();
                $qrRouteBase = rtrim($applicationBasePath, '/')
                    . ($schoolSlug !== null ? '/' . $schoolSlug : '/public');
                $requestHost = (string) ($_SERVER['HTTP_HOST'] ?? '');
                $isValidHost = preg_match('/^(?:[a-zA-Z0-9.-]+|\[[a-fA-F0-9:]+\])(?::\d{1,5})?$/D', $requestHost) === 1;
                $scheme = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off'
                    ? 'https'
                    : 'http';
                $qrOrigin = $isValidHost ? $scheme . '://' . $requestHost : '';
                $qrPayload = $qrOrigin . $qrRouteBase . '/paiements/qrSummary?id='
                    . urlencode((string) ($ecriture['id'] ?? ''));
                $formatMontant = function (float $amount, ?string $currency = null) use ($devise) {
                    $currency = strtoupper(trim($currency ?? $devise)) ?: $devise;
                    $usdEquivalent = $currency !== 'USD'
                        ? \App\Models\Devise::convertToUsd($amount, $currency)
                        : null;
                    return \App\Models\Devise::formatAmountWithCurrency($amount, $currency, $usdEquivalent);
                };
                $currenciesForTotals = array_unique(array_merge([$devise], array_keys($totalPayeParDevise)));
                $currenciesForRemaining = array_keys($resteParDevise);
                if (empty($currenciesForRemaining)) {
                    $currenciesForRemaining = [$devise];
                }
              ?>

              <div class="mt-3">
                <div class="pos-row"><div>Élève:</div><div><?= htmlspecialchars($eleveName ?: '-') ?></div></div>
                <div class="pos-row"><div>Date:</div><div><?= htmlspecialchars(formatDate($ecriture['date_operation'] ?? null)) ?></div></div>
                <div class="pos-row"><div>Motif:</div><div><?= htmlspecialchars($ecriture['libelle'] ?? '') ?></div></div>
                <?php
                  $agentNom = trim((string) ($ecriture['agent_nom'] ?? '')) ?: '-';
                  $agentFonction = trim((string) ($ecriture['agent_fonction'] ?? ''));
                ?>
                <div class="pos-row"><div>Perçu par:</div><div><?= htmlspecialchars($agentNom . ($agentFonction !== '' ? ' (' . $agentFonction . ')' : '')) ?></div></div>
                <div class="pos-line"></div>
                <div class="pos-row"><div>Montant payé</div><div class="pos-amount"><?= htmlspecialchars($ecriture['montant_affiche'] ?? $formatMontant($montantPaye)) ?></div></div>
                <?php if (!empty($ecriture['montant_usd_equivalent']) && strtoupper(trim($ecriture['transaction_devise'] ?? 'USD')) !== 'USD'): ?>
                  <div class="pos-row"><div>Equivalent USD</div><div><?= number_format((float) $ecriture['montant_usd_equivalent'], 2) ?> USD</div></div>
                <?php endif; ?>
                <div class="pos-line"></div>
                <?php foreach ([
                    ['Total déjà payé', $totalPayeParDevise, $currenciesForTotals],
                    ['Reste à payer', $resteParDevise, $currenciesForRemaining],
                ] as [$amountLabel, $amountsByCurrency, $currencies]): ?>
                  <div class="pos-row">
                    <div><?= htmlspecialchars($amountLabel) ?></div>
                    <div>
                      <?php foreach ($currencies as $currency): ?>
                        <div><?= htmlspecialchars($formatMontant((float) ($amountsByCurrency[$currency] ?? 0.0), (string) $currency)) ?></div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endforeach; ?>

                <div class="qr">
                  <img src="https://api.qrserver.com/v1/create-qr-code?size=120x120&data=<?= urlencode($qrPayload) ?>" alt="QR code" style="width:70px;height:70px;" />
                  <div style="font-size:11px;margin-top:6px;">Scannez pour vérifier le total payé et la dette totale</div>
                </div>

                <div class="pos-line"></div>
                <div style="text-align:center;margin-top:8px;">Merci pour votre paiement</div>

                <div class="mt-3 d-flex justify-content-between no-print">
                  <a href="<?= BASE_URL ?>/paiements" class="btn btn-secondary btn-sm">Retour</a>
                  <button class="btn btn-primary btn-sm" type="button" onclick="printReceipt()">Imprimer le reçu</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
  function printReceipt() {
    const receipt = document.querySelector('.pos-receipt');
    if (receipt) {
      const measurementContainer = document.createElement('div');
      measurementContainer.style.cssText = 'position:fixed;left:-10000px;top:0;width:80mm;visibility:hidden;';
      const measuredReceipt = receipt.cloneNode(true);
      measuredReceipt.querySelectorAll('.no-print').forEach(element => element.remove());
      measuredReceipt.style.cssText += ';position:relative;width:76mm;max-width:76mm;margin:0;padding:0;overflow:visible;transform:none;';
      measurementContainer.appendChild(measuredReceipt);
      document.body.appendChild(measurementContainer);

      const heightMillimeters = Math.ceil(measuredReceipt.getBoundingClientRect().height * 25.4 / 96 + 2);
      measurementContainer.remove();

      let pageSizeStyle = document.getElementById('receiptPrintPageSize');
      if (!pageSizeStyle) {
        pageSizeStyle = document.createElement('style');
        pageSizeStyle.id = 'receiptPrintPageSize';
        document.head.appendChild(pageSizeStyle);
      }
      pageSizeStyle.textContent = '@page { size: 80mm ' + heightMillimeters + 'mm; margin: 0; }';
    }

    window.print();
  }
</script>
<?php if (!empty($autoPrint)): ?>
  <script>
    window.addEventListener('load', function () {
      window.setTimeout(function () {
        printReceipt();
      }, 300);
    }, { once: true });
  </script>
<?php endif; ?>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>