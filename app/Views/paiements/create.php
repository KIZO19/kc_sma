<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Enregistrer un paiement</h1>
        <p class="text-muted"><?= htmlspecialchars(($eleve['nom'] ?? '') . ' ' . ($eleve['postnom'] ?? '') . ' ' . ($eleve['prenom'] ?? '')) ?></p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/eleves">Élèves</a></li>
          <li class="breadcrumb-item active">Enregistrer paiement</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6">
        <div class="card card-outline card-primary">
          <div class="card-header"><h3 class="card-title">Détails paiement</h3></div>
          <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/paiements/store">
              <input type="hidden" name="eleve_id" value="<?= (int) $eleve['id'] ?>">

              <div class="mb-3">
                <label class="form-label">Montant</label>
                <input type="number" step="0.01" name="montant" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Libellé</label>
                <input type="text" name="libelle" class="form-control" value="Paiement frais scolaires">
              </div>

              <div class="mb-3">
                <label class="form-label">Caisse / Compte</label>
                <select name="caisse_id" class="form-select">
                  <option value="">-- Sélectionner --</option>
                  <?php foreach ($caisses as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nom_compte']) ?> (<?= htmlspecialchars($c['type_compte']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Enregistrer et imprimer reçu</button>
                <a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) $eleve['id'] ?>" class="btn btn-secondary">Retour</a>
              </div>
            </form>
          </div>
        </div>

        <?php if (!empty($compte)): ?>
        <div class="card card-outline card-secondary">
          <div class="card-header"><h3 class="card-title">Compte élève</h3></div>
          <div class="card-body">
            <p><strong>Solde dû:</strong> <?= number_format((float) ($compte['solde_debiteur'] ?? 0), 2) ?></p>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>