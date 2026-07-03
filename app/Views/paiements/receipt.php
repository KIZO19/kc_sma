<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <div class="text-center mb-4">
              <h3>Reçu de paiement</h3>
              <p class="text-muted">Référence: <strong><?= htmlspecialchars($ecriture['reference_recu'] ?? '') ?></strong></p>
            </div>

            <table class="table table-borderless">
              <tr><th>Élève</th><td><?= htmlspecialchars(($ecriture['nom'] ?? '') . ' ' . ($ecriture['postnom'] ?? '') . ' ' . ($ecriture['prenom'] ?? '')) ?></td></tr>
              <tr><th>Date</th><td><?= htmlspecialchars($ecriture['date_operation'] ?? '') ?></td></tr>
              <tr><th>Montant</th><td><?= number_format((float) ($ecriture['montant'] ?? 0), 2) ?></td></tr>
              <tr><th>Libellé</th><td><?= htmlspecialchars($ecriture['libelle'] ?? '') ?></td></tr>
              <tr><th>Caisse</th><td><?= htmlspecialchars($ecriture['caisse_name'] ?? '-') ?></td></tr>
            </table>

            <div class="mt-4 d-flex justify-content-between">
              <a href="<?= BASE_URL ?>/paiements" class="btn btn-secondary">Retour</a>
              <button class="btn btn-primary" onclick="window.print()">Imprimer le reçu</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>