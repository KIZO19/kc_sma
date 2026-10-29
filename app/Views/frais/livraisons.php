<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$items = $items ?? [];
$summary = $summary ?? ['purchased' => 0, 'delivered' => 0, 'pending' => 0];
$success = $success ?? '';
$error = $error ?? '';
$csrfToken = $csrfToken ?? '';
$formatAmount = static fn ($amount): string => number_format((float) $amount, 2, ',', ' ');
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1>Livraisons des objets achetés</h1>
        <p class="text-muted mb-0">Suivez les objets entièrement payés et enregistrez leur remise aux élèves.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/frais/articles"><i class="bi bi-bag-check me-1" aria-hidden="true"></i>Fiche des objets</a>
        <a class="btn btn-outline-danger" href="<?= BASE_URL ?>/frais/exportDeliveries"><i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>Rapport PDF</a>
      </div>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if ($success !== ''): ?><div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <div class="row g-3 mb-3">
      <?php foreach ([['Achats entièrement payés', $summary['purchased'], 'primary'], ['Objets remis', $summary['delivered'], 'success'], ['À remettre', $summary['pending'], 'warning']] as [$label, $value, $tone]): ?>
        <div class="col-12 col-sm-6 col-xl-4"><div class="small-box bg-<?= $tone ?> mb-0"><div class="inner"><h3><?= (int) $value ?></h3><p><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></p></div><div class="icon"><i class="bi bi-box-seam" aria-hidden="true"></i></div></div></div>
      <?php endforeach; ?>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h2 class="h6 mb-1">Registre des achats et remises</h2>
          <span class="small text-muted">Année scolaire <?= htmlspecialchars($activeYear['annee'] ?? 'non configurée', ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <input id="deliverySearch" type="search" class="form-control form-control-sm" style="max-width: 320px" placeholder="Rechercher un élève ou un objet" aria-label="Rechercher dans les livraisons">
      </div>
      <div class="card-body p-0">
        <?php if (empty($items)): ?>
          <div class="text-center text-muted p-5">Aucun objet entièrement payé n’est disponible pour livraison.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="deliveryTable">
              <thead><tr><th class="ps-3">Élève</th><th>Classe</th><th>Objet</th><th class="text-end">Prix payé</th><th>État</th><th>Livraison</th><th>Remis par</th><th class="text-end pe-3">Action</th></tr></thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                  <?php $isDelivered = !empty($item['date_livraison']); $studentName = \App\Core\PersonName::format($item); ?>
                  <tr data-search="<?= htmlspecialchars(mb_strtolower($studentName . ' ' . ($item['matricule'] ?? '') . ' ' . ($item['nom_classe'] ?? '') . ' ' . ($item['type_frais'] ?? ''), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>">
                    <td class="ps-3"><strong><?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?></strong><small class="d-block text-muted"><?= htmlspecialchars($item['matricule'] ?? 'Sans matricule', ENT_QUOTES, 'UTF-8') ?></small></td>
                    <td><?= htmlspecialchars($item['nom_classe'] ?? 'Classe non définie', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($item['type_frais'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= $formatAmount($item['montant_initial']) ?> <?= htmlspecialchars($item['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge text-bg-<?= $isDelivered ? 'success' : 'warning' ?>"><?= $isDelivered ? 'Livré' : 'Payé · à remettre' ?></span></td>
                    <td><?= $isDelivered ? htmlspecialchars(date('d/m/Y H:i', strtotime($item['date_livraison'])), ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td><?= htmlspecialchars($item['livre_par_nom'] ?? ($isDelivered ? 'Utilisateur supprimé' : '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end pe-3 text-nowrap">
                      <form method="post" action="<?= BASE_URL ?>/frais/setDelivery" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="eleve_id" value="<?= (int) $item['eleve_id'] ?>">
                        <input type="hidden" name="frais_id" value="<?= (int) $item['frais_id'] ?>">
                        <input type="hidden" name="delivered" value="<?= $isDelivered ? '0' : '1' ?>">
                        <button class="btn btn-sm btn-outline-<?= $isDelivered ? 'secondary' : 'success' ?>" type="submit" aria-label="<?= $isDelivered ? 'Annuler la livraison' : 'Confirmer la livraison' ?>">
                          <i class="bi bi-<?= $isDelivered ? 'arrow-counterclockwise' : 'box-seam' ?> me-1" aria-hidden="true"></i><?= $isDelivered ? 'Annuler' : 'Marquer livré' ?>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const search = document.getElementById('deliverySearch');
  const rows = Array.from(document.querySelectorAll('#deliveryTable tbody tr'));
  if (!search) return;
  search.addEventListener('input', () => {
    const query = search.value.trim().toLocaleLowerCase();
    rows.forEach((row) => { row.hidden = !row.dataset.search.includes(query); });
  });
});
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>