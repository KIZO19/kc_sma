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
      <div class="col-12">
        <div class="card card-outline card-primary">
          <div class="card-header"><h3 class="card-title">Informations</h3></div>
          <div class="card-body">
            <table class="table table-sm">
              <tr><th>Type</th><td><?= htmlspecialchars($fee['type_frais'] ?? '-') ?></td></tr>
              <tr><th>Montant</th><td><?= number_format((float) ($fee['montant_total'] ?? 0), 2) ?> <?= htmlspecialchars($fee['devise'] ?? '') ?></td></tr>
              <tr><th>Classe / portée</th><td><?= htmlspecialchars($fee['scope_label'] ?? (($fee['nom_classe'] ?? '-') . (!empty($fee['class_option_name']) ? ' · ' . $fee['class_option_name'] : ''))) ?></td></tr>
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

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 my-3">
      <div>
        <h2 class="h4 mb-1">Élèves concernés</h2>
        <p class="text-muted mb-0">Situation calculée pour l’année scolaire <?= htmlspecialchars($fee['annee_scolaire'] ?? '-') ?>.</p>
      </div>
      <span class="badge text-bg-primary"><?= count($feeStudents) ?> élève(s)</span>
    </div>

    <?php if (!empty($feeStudents)): ?>
      <div class="row">
        <div class="col-sm-6 col-xl-3">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= (int) $paidStudentsCount ?></h3>
              <p>Payés entièrement</p>
            </div>
            <div class="icon"><i class="bi bi-check-circle"></i></div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= (int) $indebtedStudentsCount ?></h3>
              <p>Avec un solde dû</p>
            </div>
            <div class="icon"><i class="bi bi-exclamation-circle"></i></div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= (int) $exoneratedStudentsCount ?></h3>
              <p>Exonérés entièrement</p>
            </div>
            <div class="icon"><i class="bi bi-shield-check"></i></div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 class="fs-4"><?= number_format((float) $totalRemaining, 2, ',', ' ') ?> <?= htmlspecialchars($fee['devise'] ?? '') ?></h3>
              <p>Total restant dû</p>
            </div>
            <div class="icon"><i class="bi bi-cash-stack"></i></div>
          </div>
        </div>
      </div>

      <div class="card card-outline card-secondary">
        <div class="card-header">
          <h3 class="card-title">Détail par élève</h3>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Élève</th>
                  <th>Matricule</th>
                  <th>Classe / option</th>
                  <th class="text-end">Montant prévu</th>
                  <th class="text-end">Déjà payé</th>
                  <th class="text-end">Exonéré</th>
                  <th class="text-end">Reste dû</th>
                  <th>Situation</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($feeStudents as $student): ?>
                  <?php
                    $studentName = trim(implode(' ', array_filter([
                        $student['nom'] ?? '',
                        $student['postnom'] ?? '',
                        $student['prenom'] ?? '',
                    ], static function ($part) {
                        return trim((string) $part) !== '';
                    })));
                    $statusBadge = match ($student['statut_paiement'] ?? '') {
                        'Payé' => 'text-bg-success',
                        'Exonéré' => 'text-bg-info',
                        'Partiellement payé' => 'text-bg-warning',
                        default => 'text-bg-danger',
                    };
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($studentName !== '' ? $studentName : '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($student['matricule'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(($student['nom_classe'] ?? '-') . (!empty($student['nom_option']) ? ' · ' . $student['nom_option'] : ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end text-nowrap"><?= number_format((float) ($student['montant_initial'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($fee['devise'] ?? '') ?></td>
                    <td class="text-end text-nowrap"><?= number_format((float) ($student['montant_paye'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($fee['devise'] ?? '') ?></td>
                    <td class="text-end text-nowrap"><?= number_format((float) ($student['montant_exonere'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($fee['devise'] ?? '') ?></td>
                    <td class="text-end text-nowrap fw-semibold"><?= number_format((float) ($student['montant_restant'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($fee['devise'] ?? '') ?></td>
                    <td><span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($student['statut_paiement'] ?? 'En dette', ENT_QUOTES, 'UTF-8') ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info" role="status">
        Aucun élève inscrit dans l’année et le périmètre concernés par ce frais.
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>