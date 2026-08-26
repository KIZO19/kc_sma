<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php $payment = $payment ?? []; ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Modifier le paiement</h1>
    <p class="text-muted">Corrigez les informations du paiement puis revenez à l’historique.</p>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['paiements_errors'])): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($_SESSION['paiements_errors'] as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php unset($_SESSION['paiements_errors']); ?>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        <div class="card card-outline card-primary">
          <div class="card-header"><h3 class="card-title">Informations du paiement</h3></div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Élève</label>
              <input class="form-control" value="<?= htmlspecialchars(trim(($payment['prenom'] ?? '') . ' ' . ($payment['nom'] ?? '') . ' ' . ($payment['postnom'] ?? ''))) ?>" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">Frais</label>
              <input class="form-control" value="<?= htmlspecialchars(($payment['type_frais'] ?? 'Paiement') . ' (' . ($payment['devise'] ?? 'USD') . ')') ?>" readonly>
            </div>
            <form method="post" action="<?= BASE_URL ?>/paiements/update">
              <input type="hidden" name="id" value="<?= (int) $payment['id'] ?>">
              <div class="mb-3">
                <label class="form-label">Montant</label>
                <input type="number" name="montant" class="form-control" min="0.01" step="0.01" required value="<?= htmlspecialchars($payment['montant'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Libellé</label>
                <input type="text" name="libelle" class="form-control" required value="<?= htmlspecialchars($payment['libelle'] ?? 'Paiement élève') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Caisse / Compte</label>
                <select name="caisse_id" class="form-select">
                  <option value="">-- Sélectionner --</option>
                  <?php foreach (($caisses ?? []) as $caisse): ?>
                    <option value="<?= (int) $caisse['id'] ?>" <?= ((int) ($payment['caisse_banque_id'] ?? 0) === (int) $caisse['id']) ? 'selected' : '' ?>><?= htmlspecialchars($caisse['nom_compte']) ?> (<?= htmlspecialchars($caisse['type_compte']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer la modification</button>
                <a href="<?= BASE_URL ?>/paiements" class="btn btn-secondary">Retour à l’historique</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
