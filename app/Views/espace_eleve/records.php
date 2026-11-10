<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$isParentView = in_array($role ?? '', ['parent_ecole', 'parent_école'], true);
$titles = [
    'presences' => $isParentView ? 'Présences de l’enfant' : 'Mes présences',
    'discipline' => 'Mon dossier disciplinaire',
    'derogations' => 'Mes dérogations',
    'cours' => 'Mes cours',
    'evaluations' => 'Mes évaluations',
    'communiques' => 'Mes communiqués',
];
?>
<section class="content-header">
  <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
    <h1><?= $escape($titles[$recordType] ?? 'Mon espace scolaire') ?></h1>
    <p class="text-muted mb-0">
      <?= $escape(\App\Core\PersonName::format($student)) ?>
      <?php if (!empty($student['matricule'])): ?> · <?= $escape($student['matricule']) ?><?php endif; ?>
      <?php if (!empty($studentClass['nom_classe'])): ?> · Classe <?= $escape($studentClass['nom_classe']) ?><?php endif; ?>
    </p>
    </div>
    <?php if ($isParentView && $recordType === 'presences'): ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/mes-enfants">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Retour à mes enfants
      </a>
    <?php endif; ?>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body p-0">
        <?php if ($records === []): ?>
          <p class="text-muted text-center py-5 mb-0">
            <?php if ($recordType === 'presences'): ?>
              Aucun relevé de présence n’est disponible pour <?= $isParentView ? 'cet enfant' : 'votre compte' ?>.
            <?php elseif ($recordType === 'discipline'): ?>
              Aucun incident disciplinaire n’est enregistré pour votre compte.
            <?php elseif ($recordType === 'derogations'): ?>
              Aucune dérogation ne vous concerne.
            <?php elseif ($recordType === 'cours'): ?>
              Aucune liste de cours n’est disponible pour votre classe.
            <?php elseif ($recordType === 'evaluations'): ?>
              Aucune évaluation n’est publiée pour votre classe.
            <?php else: ?>
              Aucun communiqué ne vous est actuellement destiné.
            <?php endif; ?>
          </p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <?php if ($recordType === 'presences'): ?>
                  <tr><th>Date</th><th>Statut</th></tr>
                <?php elseif ($recordType === 'discipline'): ?>
                  <tr><th>Date</th><th>Fait consigné</th><th>Sanction</th><th class="text-end">Points retirés</th></tr>
                <?php elseif ($recordType === 'derogations'): ?>
                  <tr><th>Date de fin</th><th>Motif</th><th>Statut</th><th>Commentaire</th></tr>
                <?php elseif ($recordType === 'cours'): ?>
                  <tr><th>Cours</th><th>Enseignant</th><th class="text-end">Périodes par semaine</th></tr>
                <?php elseif ($recordType === 'evaluations'): ?>
                  <tr><th>Cours</th><th>Période</th><th>Type</th><th>Date</th><th class="text-end">Résultat</th></tr>
                <?php else: ?>
                  <tr><th>Date</th><th>Communiqué</th><th>Message</th></tr>
                <?php endif; ?>
              </thead>
              <tbody>
                <?php foreach ($records as $record): ?>
                  <?php if ($recordType === 'presences'): ?>
                    <tr>
                      <td class="text-nowrap"><?= $escape(formatDate($record['date_jour'] ?? null)) ?></td>
                      <td><?= $escape($record['statut'] ?? '—') ?></td>
                    </tr>
                  <?php elseif ($recordType === 'discipline'): ?>
                    <tr>
                      <td class="text-nowrap"><?= $escape(formatDate($record['date_evenement'] ?? null)) ?></td>
                      <td><?= $escape($record['faute'] ?? '—') ?></td>
                      <td><?= $escape($record['sanction'] ?? '—') ?></td>
                      <td class="text-end"><?= (int) ($record['retrait_points'] ?? 0) ?></td>
                    </tr>
                  <?php elseif ($recordType === 'derogations'): ?>
                    <tr>
                      <td class="text-nowrap"><?= $escape(formatDate($record['date_fin'] ?? null)) ?></td>
                      <td><?= $escape($record['motif'] ?? '—') ?></td>
                      <td><?= $escape(str_replace('_', ' ', (string) ($record['statut'] ?? '—'))) ?></td>
                      <td><?= $escape($record['commentaire'] ?? '—') ?></td>
                    </tr>
                  <?php elseif ($recordType === 'cours'): ?>
                    <?php $teacherName = trim(implode(' ', array_filter([
                        $record['enseignant_nom'] ?? '',
                        $record['enseignant_postnom'] ?? '',
                        $record['enseignant_prenom'] ?? '',
                    ], static fn ($part): bool => trim((string) $part) !== ''))); ?>
                    <tr>
                      <td><?= $escape($record['nom_cours'] ?? '—') ?></td>
                      <td><?= $escape($teacherName !== '' ? $teacherName : 'Non renseigné') ?></td>
                      <td class="text-end"><?= (int) ($record['volume_horaire_hebdo'] ?? 0) ?></td>
                    </tr>
                  <?php elseif ($recordType === 'evaluations'): ?>
                    <tr>
                      <td><?= $escape($record['nom_cours'] ?? '—') ?></td>
                      <td><?= $escape($record['nom_periode'] ?? '—') ?></td>
                      <td><?= $escape($record['type_evaluation'] ?? '—') ?></td>
                      <td class="text-nowrap"><?= $escape(formatDate($record['date_evaluation'] ?? null)) ?></td>
                      <td class="text-end text-nowrap">
                        <?php if ($record['note_obtenue'] !== null): ?>
                          <strong><?= $escape($record['note_obtenue']) ?> / <?= $escape($record['ponderation_max'] ?? '—') ?></strong>
                        <?php else: ?>
                          <span class="text-muted">Résultat non publié</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php else: ?>
                    <tr>
                      <td class="text-nowrap"><?= $escape(formatDate($record['created_at'] ?? null)) ?></td>
                      <td class="fw-semibold"><?= $escape($record['titre'] ?? '—') ?></td>
                      <td class="text-break"><?= nl2br($escape($record['message'] ?? '')) ?></td>
                    </tr>
                  <?php endif; ?>
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
