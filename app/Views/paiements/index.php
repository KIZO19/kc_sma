<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Paiements reçus</h1>
        <p class="text-muted">Liste des paiements enregistrés</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Paiements</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="mb-3">
      <?php if (in_array($role, ['super_admin','comptable_école'], true)): ?>
        <a href="<?= BASE_URL ?>/paiements/create" class="btn btn-success">Enregistrer paiement</a>
      <?php endif; ?>
    </div>

    <div class="card card-outline card-primary">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
              <tr><th>#</th><th>Réf reçu</th><th>Élève</th><th>Date</th><th>Montant</th><th>Caisse</th><th>Agent</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (empty($payments)): ?>
                <tr><td colspan="8" class="text-center py-4">Aucun paiement enregistré.</td></tr>
              <?php else: ?>
                <?php foreach ($payments as $i => $p): ?>
                  <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($p['reference_recu'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['postnom'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($p['date_operation'] ?? '') ?></td>
                    <td><?= number_format((float) ($p['montant'] ?? 0), 2) ?></td>
                    <td><?= htmlspecialchars($p['nom_compte'] ?? $p['nom_compte']) ?></td>
                    <td><?= htmlspecialchars($p['agent_nom'] ?? '') ?></td>
                    <td>
                      <a href="<?= BASE_URL ?>/paiements/receipt?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary">Reçu</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>