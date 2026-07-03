<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Détail du frais</h1>
        <p class="text-muted"><?= htmlspecialchars($fee['type_frais'] ?? '') ?></p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/frais">Frais</a></li>
          <li class="breadcrumb-item active">Détail</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8">
        <div class="card card-outline card-primary">
          <div class="card-header"><h3 class="card-title">Informations</h3></div>
          <div class="card-body">
            <table class="table table-sm">
              <tr><th>Type</th><td><?= htmlspecialchars($fee['type_frais'] ?? '-') ?></td></tr>
              <tr><th>Montant</th><td><?= number_format((float) ($fee['montant_total'] ?? 0), 2) ?> <?= htmlspecialchars($fee['devise'] ?? '') ?></td></tr>
              <tr><th>Classe / portée</th><td><?= htmlspecialchars($fee['scope_label'] ?? ($fee['nom_classe'] ?? '-')) ?></td></tr>
              <tr><th>Année scolaire</th><td><?= htmlspecialchars($fee['annee_scolaire'] ?? '-') ?></td></tr>
            </table>
          </div>
          <div class="card-footer">
            <?php if (in_array($role, ['super_admin','ecole_admin','comptable_école'], true)): ?>
              <a href="<?= BASE_URL ?>/frais/edit?id=<?= (int) $fee['id'] ?>" class="btn btn-sm btn-primary">Modifier</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/frais" class="btn btn-sm btn-secondary">Retour</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>