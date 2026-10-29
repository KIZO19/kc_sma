<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$editItem = $editItem ?? null;
$csrfToken = $csrfToken ?? '';
$schoolCurrency = $schoolCurrency ?? 'USD';
$items = $items ?? [];
$otherSchoolFees = $otherSchoolFees ?? [];
$activeYear = $activeYear ?? null;
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1>Objets classiques</h1>
        <p class="text-muted mb-0">Choisissez les articles à présenter comme objets obligatoires; les autres frais conservent leur portée.</p>
      </div>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/frais">Frais</a></li>
        <li class="breadcrumb-item active">Objets classiques</li>
      </ol>
    </div>
    <div class="mt-3">
      <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/frais/livraisons"><i class="bi bi-box-seam me-1" aria-hidden="true"></i>Rapport des livraisons</a>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['frais_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['frais_success'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php unset($_SESSION['frais_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['frais_errors'])): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($_SESSION['frais_errors'] as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
      <?php unset($_SESSION['frais_errors']); ?>
    <?php endif; ?>

    <div class="row g-3">
      <div class="col-12 col-xl-4">
        <div class="card card-outline card-primary">
          <div class="card-header"><h2 class="h6 mb-0"><?= $editItem ? 'Modifier un article' : 'Ajouter un article' ?></h2></div>
          <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/frais/saveSchoolItem">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="id" value="<?= (int) ($editItem['id'] ?? 0) ?>">
              <div class="mb-3">
                <label for="schoolItemName" class="form-label">Objet ou frais obligatoire</label>
                <input id="schoolItemName" class="form-control" name="type_frais" maxlength="100" value="<?= htmlspecialchars($editItem['type_frais'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Cahier de communication, logo, uniforme…" required>
              </div>
              <div class="mb-3">
                <label for="schoolItemAmount" class="form-label">Prix (<?= htmlspecialchars($schoolCurrency, ENT_QUOTES, 'UTF-8') ?>)</label>
                <input id="schoolItemAmount" class="form-control" type="number" name="montant_total" min="0.01" step="0.01" value="<?= htmlspecialchars((string) ($editItem['montant_total'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
              </div>
              <div class="small text-muted mb-3">Année active : <?= htmlspecialchars($activeYear['annee'] ?? 'non configurée', ENT_QUOTES, 'UTF-8') ?>. Les prix seront débités au nouvel élève lors de son inscription; modifier cette fiche ne refacture pas les élèves déjà inscrits.</div>
              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i><?= $editItem ? 'Enregistrer' : 'Ajouter à la fiche' ?></button>
                <?php if ($editItem): ?><a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/frais/articles">Annuler</a><?php endif; ?>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-8">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center gap-2">
            <h2 class="h6 mb-0">Objets classiques de l’année active</h2>
            <span class="badge text-bg-secondary"><?= count($items) ?> objet(s)</span>
          </div>
          <div class="card-body p-0">
            <?php if (empty($items)): ?>
              <p class="text-muted text-center p-4 mb-0">Aucun article n’est configuré. Ajoutez les objets obligatoires avant de procéder aux inscriptions.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead><tr><th class="ps-3">Objet</th><th class="text-end">Prix</th><th>Année</th><th class="text-end pe-3">Actions</th></tr></thead>
                  <tbody>
                    <?php foreach ($items as $item): ?>
                      <tr>
                        <td class="ps-3 fw-semibold"><?= htmlspecialchars($item['type_frais'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end"><?= number_format((float) $item['montant_total'], 2, ',', ' ') ?> <?= htmlspecialchars($item['devise'] ?? $schoolCurrency, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['annee_scolaire'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end pe-3 text-nowrap">
                          <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/frais/articles?id=<?= (int) $item['id'] ?>" title="Modifier" aria-label="Modifier <?= htmlspecialchars($item['type_frais'], ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                          <form method="post" action="<?= BASE_URL ?>/frais/toggleSchoolItem" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Retirer des objets obligatoires" aria-label="Retirer <?= htmlspecialchars($item['type_frais'], ENT_QUOTES, 'UTF-8') ?> des objets obligatoires"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
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
        <div class="card mt-3">
          <div class="card-header"><h2 class="h6 mb-0">Autres frais de portée école entière</h2></div>
          <div class="card-body p-0">
            <?php if (empty($otherSchoolFees)): ?>
              <p class="text-muted text-center p-4 mb-0">Tous les frais école entière de l’année active sont déjà sélectionnés, ou aucun autre frais n’est défini.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead><tr><th class="ps-3">Frais</th><th class="text-end">Montant</th><th class="text-end pe-3">Action</th></tr></thead>
                  <tbody>
                    <?php foreach ($otherSchoolFees as $fee): ?>
                      <tr>
                        <td class="ps-3 fw-semibold"><?= htmlspecialchars($fee['type_frais'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end"><?= number_format((float) $fee['montant_total'], 2, ',', ' ') ?> <?= htmlspecialchars($fee['devise'] ?? $schoolCurrency, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end pe-3">
                          <form method="post" action="<?= BASE_URL ?>/frais/toggleSchoolItem" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id" value="<?= (int) $fee['id'] ?>">
                            <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Ajouter à la fiche</button>
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
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>