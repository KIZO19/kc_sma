<?php
require __DIR__ . '/../partials/app_header.php';
$e = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$studentName = trim(implode(' ', array_filter([$eleve['nom'] ?? '', $eleve['postnom'] ?? '', $eleve['prenom'] ?? ''])));
$totalPointsRemoved = array_sum(array_map(static fn (array $entry): int => (int) ($entry['retrait_points'] ?? 0), $discipline));
?>
<style>
  .discipline-page .discipline-panel { border: 1px solid var(--border); border-radius: .75rem; background: var(--card); }
  .discipline-page .discipline-panel h2,
  .discipline-page .student-name { color: var(--text); }
  .discipline-page .discipline-table thead th { background: var(--surface-muted); color: var(--text-muted); font-size: .75rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
  .discipline-page .discipline-table td { color: var(--text); vertical-align: middle; }
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h1>Fiche de discipline</h1>
        <p class="text-muted mb-0">Données limitées aux élèves de vos classes attribuées.</p>
      </div>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/eleves">Élèves inscrits</a></li>
        <li class="breadcrumb-item active">Discipline</li>
      </ol>
    </div>
  </div>
</section>
<section class="content discipline-page">
  <div class="container-fluid">
    <div class="card discipline-panel mb-3">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <div class="small text-muted">Élève</div>
          <h2 class="student-name h4 mb-1"><?= $e($studentName ?: '—') ?></h2>
          <div class="text-muted small">Matricule : <span class="font-monospace"><?= $e($eleve['matricule'] ?? '—') ?></span> · Classe : <?= $e($currentClass ?: '—') ?></div>
        </div>
        <div class="text-sm-end">
          <div class="small text-muted">Incidents consignés</div>
          <strong class="fs-4"><?= count($discipline) ?></strong>
          <div class="small text-muted">Points retirés : <?= $totalPointsRemoved ?></div>
        </div>
      </div>
    </div>

    <div class="card discipline-panel">
      <div class="card-header bg-transparent">
        <h2 class="h5 mb-0">Historique disciplinaire</h2>
      </div>
      <div class="card-body p-0">
        <?php if ($discipline === []): ?>
          <p class="text-muted text-center py-5 mb-0">Aucun incident disciplinaire enregistré pour cet élève.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover discipline-table align-middle mb-0">
              <thead><tr><th class="ps-3">Date</th><th>Fait consigné</th><th>Sanction</th><th class="text-end pe-3">Points retirés</th></tr></thead>
              <tbody>
                <?php foreach ($discipline as $entry): ?>
                  <tr>
                    <td class="ps-3 text-nowrap"><?= $e(formatDate($entry['date_evenement'] ?? null)) ?></td>
                    <td><?= $e($entry['faute'] ?? '—') ?></td>
                    <td><?= $e($entry['sanction'] ?: '—') ?></td>
                    <td class="text-end pe-3 fw-semibold"><?= (int) ($entry['retrait_points'] ?? 0) ?></td>
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
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
