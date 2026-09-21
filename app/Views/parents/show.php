<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Profil du parent</h1>
        <p class="text-muted"><?= htmlspecialchars($parent['nom_responsable'] ?? '-') ?></p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/eleves">Élèves</a></li>
          <li class="breadcrumb-item active">Profil parent</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-5">
        <div class="card card-outline card-primary">
          <div class="card-header"><h3 class="card-title">Coordonnées</h3></div>
          <div class="card-body">
            <p><strong>Nom :</strong> <?= htmlspecialchars($parent['nom_responsable'] ?? '-') ?></p>
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($parent['telephone'] ?? '-') ?></p>
            <p><strong>Email :</strong> <?= htmlspecialchars($parent['email'] ?? '-') ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-7">
        <div class="card card-outline card-secondary">
          <div class="card-header"><h3 class="card-title">Élèves associés</h3></div>
          <div class="card-body">
            <?php if (empty($children)): ?>
              <div class="alert alert-info mb-0">Aucun élève associé à ce parent.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead>
                    <tr><th>Nom</th><th>Matricule</th><th>Classe</th><th>Total payé</th><th>Action</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($children as $child): ?>
                      <?php $childName = trim(($child['nom'] ?? '') . ' ' . ($child['postnom'] ?? '') . ' ' . ($child['prenom'] ?? '')); ?>
                      <tr>
                        <td><?= htmlspecialchars($childName !== '' ? $childName : '-') ?></td>
                        <td><?= htmlspecialchars($child['matricule'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($child['nom_classe'] ?? '-') ?></td>
                        <td>
                          <?php if (!empty($child['total_paid_by_currency'])): ?>
                            <?php foreach ($child['total_paid_by_currency'] as $currency => $totalPaid): ?>
                              <div><?= number_format((float) $totalPaid, 2, ',', ' ') ?> <?= htmlspecialchars($currency) ?></div>
                            <?php endforeach; ?>
                          <?php else: ?>
                            0,00
                          <?php endif; ?>
                        </td>
                        <td><a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) $child['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a></td>
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