<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php $escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8'); ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Mes notes</h1>
    <p class="text-muted mb-0">
      <?= $escape(\App\Core\PersonName::format($student)) ?>
      · <?= $escape($student['matricule'] ?? '') ?>
    </p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body p-0">
        <?php if (empty($notes)): ?>
          <p class="text-muted text-center py-5 mb-0">Aucune note n’a encore été publiée pour votre compte.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead><tr><th>Cours</th><th>Période</th><th>Évaluation</th><th>Date</th><th class="text-end">Note</th></tr></thead>
              <tbody>
                <?php foreach ($notes as $note): ?>
                  <tr>
                    <td><?= $escape($note['nom_cours'] ?? 'Cours non précisé') ?></td>
                    <td><?= $escape($note['nom_periode'] ?? '—') ?></td>
                    <td><?= $escape($note['type_evaluation'] ?? 'Évaluation') ?></td>
                    <td><?= $escape(formatDate($note['date_evaluation'] ?? null)) ?></td>
                    <td class="text-end text-nowrap fw-semibold">
                      <?= $escape($note['note_obtenue'] ?? '—') ?>
                      <?php if (isset($note['ponderation_max'])): ?> / <?= $escape($note['ponderation_max']) ?><?php endif; ?>
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
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
