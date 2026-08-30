<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$notificationStatus = $_SESSION['payment_notification_status'] ?? null;
unset($_SESSION['payment_notification_status']);
?>
<style>
  /* POS receipt styling */
  .pos-receipt { width: 300px; max-width: 100%; margin: 0 auto; font-family: 'Courier New', monospace; font-size: 11px; position: relative; overflow: hidden; }
  .receipt-watermark { position: absolute; top: 25%; left: 5%; width: 90%; height: 45%; object-fit: contain; opacity: .08; pointer-events: none; z-index: 0; }
  .pos-receipt > *:not(.receipt-watermark) { position: relative; z-index: 1; }
  .pos-header { text-align: center; }
  .pos-header h4 { font-size: 14px; margin: 3px 0; overflow-wrap: anywhere; }
  .pos-line { border-top: 1px dashed #000; margin: 8px 0; }
  .pos-row { display:flex; justify-content:space-between; gap: 8px; }
  .pos-row > div:last-child { text-align:right; overflow-wrap:anywhere; }
  .pos-amount { font-weight: bold; font-size: 14px; }
  .qr { text-align:center; margin-top:10px; }
  @media print {
    @page { size: 80mm auto; margin: 3mm; }
    body { margin: 0; background: #fff; }
    .no-print, .card { box-shadow: none !important; }
    .no-print { display:none !important; }
    .pos-receipt { width: 72mm; max-width: 72mm; }
    .receipt-watermark { opacity: .10; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
  }
</style>
<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <?php if (!empty($notificationStatus)): ?>
          <div class="alert <?= !empty($notificationStatus['sent']) ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <strong><?= !empty($notificationStatus['sent']) ? 'Notification envoyée' : 'Notification non envoyée' ?></strong><br>
            <?= htmlspecialchars($notificationStatus['message'] ?? 'Statut de notification indisponible.') ?>
            <?php if (!empty($notificationStatus['details'])): ?>
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
                <div><?= htmlspecialchars($ecriture['caisse_name'] ?? '') ?></div>
                <div class="pos-line"></div>
                <div>REÇU DE PAIEMENT</div>
                <div class="text-muted">Réf: <strong><?= htmlspecialchars($ecriture['reference_recu'] ?? '') ?></strong></div>
              </div>

              <?php
                $parts = array_filter([
                  $ecriture['nom'] ?? '',
                  $ecriture['postnom'] ?? '',
                  $ecriture['prenom'] ?? '',
                ], fn($value) => $value !== null && trim((string) $value) !== '');
                $eleveName = implode(' ', array_map('trim', $parts));
                $solde = isset($compte['solde_debiteur']) ? (float) $compte['solde_debiteur'] : 0.0;
                $dette = $solde > 0 ? $solde : 0.0;
                $reste = isset($reste_a_payer) ? $reste_a_payer : null;
                $resteParFrais = isset($reste_par_frais) ? $reste_par_frais : null;
                $montantPaye = isset($ecriture['montant']) ? (float) $ecriture['montant'] : 0.0;
                $devise = strtoupper(trim($ecriture['transaction_devise'] ?? $ecriture['frais_devise'] ?? 'USD')) ?: 'USD';
                $qrPayload = BASE_URL . '/paiements/qrSummary?id=' . urlencode((string) ($ecriture['id'] ?? ''));
                $qrPayloadEscaped = htmlspecialchars($qrPayload, ENT_QUOTES, 'UTF-8');
                $formatMontant = function (float $amount) use ($devise) {
                    $usdEquivalent = $devise !== 'USD'
                        ? \App\Models\Devise::convertToUsd($amount, $devise)
                        : null;
                    return \App\Models\Devise::formatAmountWithCurrency($amount, $devise, $usdEquivalent);
                };
              ?>

              <div class="mt-3">
                <div class="pos-row"><div>Élève:</div><div><?= htmlspecialchars($eleveName ?: '-') ?></div></div>
                <div class="pos-row"><div>Date:</div><div><?= htmlspecialchars(formatDate($ecriture['date_operation'] ?? null)) ?></div></div>
                <div class="pos-row"><div>Motif:</div><div><?= htmlspecialchars($ecriture['libelle'] ?? '') ?></div></div>
                <div class="pos-row"><div>Perçu par:</div><div><?= htmlspecialchars($ecriture['agent_nom'] ?? '-') ?></div></div>
                <div class="pos-row"><div>Fonction:</div><div><?= htmlspecialchars($ecriture['agent_fonction'] ?? '-') ?></div></div>
                <div class="pos-line"></div>
                <div class="pos-row"><div>Montant payé</div><div class="pos-amount"><?= htmlspecialchars($ecriture['montant_affiche'] ?? $formatMontant($montantPaye)) ?></div></div>
                <?php if (!empty($ecriture['montant_usd_equivalent']) && strtoupper(trim($ecriture['transaction_devise'] ?? 'USD')) !== 'USD'): ?>
                  <div class="pos-row"><div>Equivalent USD</div><div><?= number_format((float) $ecriture['montant_usd_equivalent'], 2) ?> USD</div></div>
                <?php endif; ?>
                <div class="pos-line"></div>
                <div class="pos-row"><div>Solde élève</div><div><?= htmlspecialchars($formatMontant($solde)) ?></div></div>
                <div class="pos-row"><div>Dette due</div><div><?= htmlspecialchars($formatMontant($dette)) ?></div></div>
                <?php if (!is_null($reste)): ?>
                  <div class="pos-row"><div>Reste à payer</div><div><?= htmlspecialchars($formatMontant((float) $reste)) ?></div></div>
                <?php endif; ?>
                <?php if (!is_null($resteParFrais) && (float) $resteParFrais !== (float) $reste): ?>
                  <div class="pos-row"><div>Reste du frais</div><div><?= htmlspecialchars($formatMontant((float) $resteParFrais)) ?></div></div>
                <?php endif; ?>

                <div class="qr">
                  <img src="https://api.qrserver.com/v1/create-qr-code?size=200x200&data=<?= urlencode($qrPayload) ?>" alt="QR code" style="width:140px;height:140px;" />
                  <div style="font-size:11px;margin-top:6px;">Scannez pour voir le résumé du paiement</div>
                </div>

                <div class="pos-line"></div>
                <div style="text-align:center;margin-top:8px;">Merci pour votre paiement</div>

                <div class="mt-3 d-flex justify-content-between no-print">
                  <a href="<?= BASE_URL ?>/paiements" class="btn btn-secondary btn-sm">Retour</a>
                  <button class="btn btn-primary btn-sm" onclick="window.print()">Imprimer le reçu</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>