<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$formatAmount = static fn ($amount) => number_format((float) $amount, 2, ',', ' ');
$currencies = array_unique(array_merge(array_keys($summary['initial'] ?? []), array_keys($summary['remaining'] ?? [])));
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1 class="mb-1">Recouvrements</h1>
        <p class="text-muted mb-0">Suivi des élèves ayant encore une dette à régler.</p>
      </div>
      <a class="btn btn-primary" href="<?= BASE_URL ?>/paiements">
        <i class="bi bi-wallet2 me-1"></i>Enregistrer un paiement
      </a>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-12 col-md-4">
        <div class="small-box bg-primary mb-0 h-100">
          <div class="inner"><h3><?= (int) ($summary['students'] ?? 0) ?></h3><p>Élèves à relancer</p></div>
          <div class="icon"><i class="bi bi-people"></i></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="small-box bg-warning mb-0 h-100">
          <div class="inner"><h3><?= (int) ($summary['records'] ?? 0) ?></h3><p>Dettes ouvertes</p></div>
          <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="small-box bg-danger mb-0 h-100">
          <div class="inner">
            <?php foreach ($currencies as $currency): ?>
              <h3 class="mb-0"><?= $formatAmount($summary['remaining'][$currency] ?? 0) ?> <small><?= htmlspecialchars($currency) ?></small></h3>
            <?php endforeach; ?>
            <?php if (!$currencies): ?><h3>0,00</h3><?php endif; ?>
            <p>Dette restante</p>
          </div>
          <div class="icon"><i class="bi bi-cash-stack"></i></div>
        </div>
      </div>
    </div>

    <div class="card card-outline card-primary">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0">Dossiers à recouvrer</h3>
        <div class="input-group" style="max-width: 360px;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="recouvrementSearch" type="search" class="form-control" placeholder="Rechercher un élève ou des frais..." aria-label="Rechercher">
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($debts)): ?>
          <div class="p-4 text-center text-muted">
            <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
            Aucun recouvrement ouvert pour le moment.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="recouvrementsTable">
              <thead class="table-light">
                <tr>
                  <th>Élève</th>
                  <th>Frais</th>
                  <th>Année scolaire</th>
                  <th class="text-end">Montant initial</th>
                  <th class="text-end">Déjà payé</th>
                  <th class="text-end">Dette restante</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($debts as $debt): ?>
                  <?php
                    $initial = (float) ($debt['montant_initial'] ?? 0);
                    $remaining = (float) ($debt['montant_restant'] ?? 0);
                    $paid = max(0, $initial - $remaining);
                    $progress = $initial > 0 ? min(100, max(0, ($paid / $initial) * 100)) : 0;
                    $name = trim(($debt['nom'] ?? '') . ' ' . ($debt['postnom'] ?? '') . ' ' . ($debt['prenom'] ?? ''));
                  ?>
                  <tr data-search="<?= htmlspecialchars(strtolower($name . ' ' . ($debt['matricule'] ?? '') . ' ' . ($debt['type_frais'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
                      <small class="text-muted"><?= htmlspecialchars($debt['matricule'] ?? 'Sans matricule') ?></small>
                    </td>
                    <td><?= htmlspecialchars($debt['type_frais'] ?? 'Frais scolaire') ?></td>
                    <td><?= htmlspecialchars($debt['annee_scolaire'] ?? 'Non précisée') ?></td>
                    <td class="text-end"><?= $formatAmount($initial) ?> <?= htmlspecialchars($debt['devise'] ?? 'USD') ?></td>
                    <td class="text-end">
                      <?= $formatAmount($paid) ?> <?= htmlspecialchars($debt['devise'] ?? 'USD') ?>
                      <div class="progress mt-1" style="height: 5px;" title="<?= round($progress) ?> % payé">
                        <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                      </div>
                    </td>
                    <td class="text-end fw-semibold text-danger"><?= $formatAmount($remaining) ?> <?= htmlspecialchars($debt['devise'] ?? 'USD') ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/paiements?eleve_id=<?= (int) $debt['eleve_id'] ?>" title="Voir les paiements">
                        <i class="bi bi-eye"></i><span class="visually-hidden">Voir les paiements</span>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div id="recouvrementEmptySearch" class="p-4 text-center text-muted d-none">Aucun dossier ne correspond à votre recherche.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
  (() => {
    const input = document.getElementById('recouvrementSearch');
    const table = document.getElementById('recouvrementsTable');
    const empty = document.getElementById('recouvrementEmptySearch');
    if (!input || !table) return;
    input.addEventListener('input', () => {
      const query = input.value.trim().toLowerCase();
      let visible = 0;
      table.querySelectorAll('tbody tr').forEach((row) => {
        const match = !query || (row.dataset.search || '').includes(query);
        row.classList.toggle('d-none', !match);
        if (match) visible++;
      });
      if (empty) empty.classList.toggle('d-none', visible !== 0);
    });
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>