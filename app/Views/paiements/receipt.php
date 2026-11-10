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
                <?php if (!empty($ecriture['annulee_at'])): ?>
                  <div style="font-weight:bold;color:#b02a37;border:1px solid #b02a37;padding:3px;margin-top:3px;">PAIEMENT ANNULÉ</div>
                  <div>Annulé le <?= htmlspecialchars(formatDate($ecriture['annulee_at'])) ?></div>
                  <?php if (!empty($ecriture['motif_annulation'])): ?>
                    <div><?= htmlspecialchars($ecriture['motif_annulation']) ?></div>
                  <?php endif; ?>
                <?php endif; ?>
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
                  <?php if (empty($ecriture['annulee_at'])): ?>
                    <button class="btn btn-primary btn-sm" type="button" onclick="printReceipt()">Imprimer le reçu</button>
                  <?php endif; ?>
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
  async function printReceipt() {
    const receipt = document.querySelector('.pos-receipt');
    if (!receipt || window.receiptPrintFrame) {
      return;
    }

    const printFrame = document.createElement('iframe');
    printFrame.setAttribute('aria-hidden', 'true');
    printFrame.style.cssText = 'position:fixed;left:0;top:0;width:80mm;height:1000px;border:0;opacity:0;pointer-events:none;';
    window.receiptPrintFrame = printFrame;
    document.body.appendChild(printFrame);

    const printDocument = printFrame.contentDocument;
    if (!printDocument) {
      printFrame.remove();
      window.receiptPrintFrame = null;
      throw new Error('Impossible de préparer le reçu pour impression.');
    }

    printDocument.open();
    printDocument.write(`<!doctype html>
      <html lang="fr">
        <head>
          <meta charset="utf-8">
          <style>
            @page { size: 80mm 150mm; margin: 0; }
            * { box-sizing: border-box; }
            html, body { width: 80mm; margin: 0; padding: 0; background: #fff; color: #000; }
            body { font-family: "Courier New", monospace; font-size: 11px; }
            .pos-receipt { position: relative; width: 72mm; max-width: 72mm; margin: 0 auto; padding: 2px; overflow: visible; }
            .pos-receipt > *:not(.receipt-watermark) { position: relative; z-index: 1; }
            .receipt-watermark { position: absolute; top: 25%; left: 5%; width: 90%; height: 45%; object-fit: contain; opacity: .08; pointer-events: none; z-index: 0; }
            .pos-header { text-align: center; }
            .pos-header h4 { font-size: 14px; margin: 2px 0; overflow-wrap: anywhere; }
            .pos-line { border-top: 1px dashed #000; margin: 4px 0; }
            .pos-row { display: flex; justify-content: space-between; gap: 8px; line-height: 1.15; margin-bottom: 2px; }
            .pos-row > div:last-child { text-align: right; overflow-wrap: anywhere; }
            .pos-amount { font-weight: bold; font-size: 14px; }
            .qr { text-align: center; margin-top: 3px; }
            .text-muted { color: #000 !important; }
            .no-print { display: none !important; }
            img { max-width: 100%; }
            .pos-receipt, .pos-receipt * { break-inside: avoid; page-break-inside: avoid; }
          </style>
        </head>
        <body></body>
      </html>`);
    printDocument.close();

    const printableReceipt = receipt.cloneNode(true);
    printableReceipt.querySelectorAll('.no-print').forEach(element => element.remove());
    printDocument.body.appendChild(printableReceipt);

    const images = Array.from(printableReceipt.querySelectorAll('img'));
    await Promise.all(images.map(image => {
      if (image.complete) {
        return Promise.resolve();
      }
      return new Promise(resolve => {
        const timeout = window.setTimeout(resolve, 3000);
        const finish = () => {
          window.clearTimeout(timeout);
          resolve();
        };
        image.addEventListener('load', finish, { once: true });
        image.addEventListener('error', finish, { once: true });
      });
    }));

    if (printDocument.fonts && printDocument.fonts.ready) {
      await printDocument.fonts.ready;
    }

    const heightMillimeters = Math.ceil(printableReceipt.getBoundingClientRect().height * 25.4 / 96 + 1);
    const pageSize = printDocument.createElement('style');
    pageSize.textContent = '@page { size: 80mm ' + heightMillimeters + 'mm; margin: 0; }';
    printDocument.head.appendChild(pageSize);

    let cleanupTimeout;
    const cleanup = () => {
      window.clearTimeout(cleanupTimeout);
      printFrame.remove();
      window.receiptPrintFrame = null;
    };
    printFrame.contentWindow.addEventListener('afterprint', cleanup, { once: true });
    cleanupTimeout = window.setTimeout(cleanup, 60000);
    printFrame.contentWindow.focus();
    printFrame.contentWindow.print();
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