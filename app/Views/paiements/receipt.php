<?php require __DIR__ . '/../partials/app_header.php'; ?>
<style>
  /* POS receipt styling */
  .pos-receipt { max-width: 360px; margin: 0 auto; font-family: 'Courier New', monospace; font-size: 12px; }
  .pos-header { text-align: center; }
  .pos-line { border-top: 1px dashed #000; margin: 8px 0; }
  .pos-row { display:flex; justify-content:space-between; }
  .pos-amount { font-weight: bold; font-size: 16px; }
  .qr { text-align:center; margin-top:10px; }
  @media print { .no-print { display:none; } .pos-receipt { max-width: 320px; } }
</style>
<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <div class="pos-receipt">
              <div class="pos-header">
                <h4><?= htmlspecialchars($ecole_name ?? APP_NAME) ?></h4>
                <div><?= htmlspecialchars($ecriture['caisse_name'] ?? '') ?></div>
                <div class="pos-line"></div>
                <div>REÇU DE PAIEMENT</div>
                <div class="text-muted">Réf: <strong><?= htmlspecialchars($ecriture['reference_recu'] ?? '') ?></strong></div>
              </div>

              <?php
                $eleveName = trim(($ecriture['prenom'] ?? '') . ' ' . ($ecriture['nom'] ?? '') . ' ' . ($ecriture['postnom'] ?? ''));
                $solde = isset($compte['solde_debiteur']) ? (float) $compte['solde_debiteur'] : 0.0;
                $dette = $solde > 0 ? $solde : 0.0;
                $reste = isset($reste_a_payer) ? $reste_a_payer : null;
                $qrPayload = json_encode([
                  'eleve_id' => $ecriture['eleve_id'] ?? null,
                  'eleve' => $eleveName,
                  'solde' => number_format($solde, 2, '.', ''),
                  'dette' => number_format($dette, 2, '.', ''),
                  'reste' => is_null($reste) ? null : number_format($reste, 2, '.', ''),
                  'reference' => $ecriture['reference_recu'] ?? '',
                ], JSON_UNESCAPED_UNICODE);
                $qrPayloadEscaped = htmlspecialchars($qrPayload, ENT_QUOTES, 'UTF-8');
              ?>

              <div class="mt-3">
                <div class="pos-row"><div>Élève:</div><div><?= htmlspecialchars($eleveName) ?></div></div>
                <div class="pos-row"><div>Date:</div><div><?= htmlspecialchars($ecriture['date_operation'] ?? '') ?></div></div>
                <div class="pos-row"><div>Motif:</div><div><?= htmlspecialchars($ecriture['libelle'] ?? '') ?></div></div>
                <div class="pos-line"></div>
                <div class="pos-row"><div>Montant</div><div class="pos-amount"><?= htmlspecialchars($ecriture['montant_affiche'] ?? number_format((float) ($ecriture['montant'] ?? 0), 2)) ?></div></div>
                <?php if (!empty($ecriture['montant_usd_equivalent']) && strtoupper(trim($ecriture['transaction_devise'] ?? 'USD')) !== 'USD'): ?>
                  <div class="pos-row"><div>Equivalent USD</div><div><?= number_format((float) $ecriture['montant_usd_equivalent'], 2) ?> USD</div></div>
                <?php endif; ?>
                <div class="pos-line"></div>
                <div class="pos-row"><div>Solde élève</div><div><?= number_format($solde, 2) ?></div></div>
                <div class="pos-row"><div>Dette due</div><div><?= number_format($dette, 2) ?></div></div>
                <?php if (!is_null($reste)): ?>
                  <div class="pos-row"><div>Reste à payer</div><div><?= number_format((float) $reste, 2) ?></div></div>
                <?php endif; ?>

                <div class="qr">
                  <img src="https://api.qrserver.com/v1/create-qr-code?size=200x200&data=<?= urlencode($qrPayload) ?>" alt="QR code" style="width:140px;height:140px;" />
                  <div style="font-size:11px;margin-top:6px;">Scannez pour voir le solde et la dette</div>
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