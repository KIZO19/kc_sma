<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Parcours scolaire</h1>
    <p class="text-muted mb-0"><?= $escape(\App\Core\PersonName::format($student)) ?> · <?= $escape($student['matricule'] ?? '') ?></p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">Documents et provenance</h2>
        <a href="<?= BASE_URL ?>/inscriptions" class="btn btn-sm btn-outline-secondary">Retour aux inscriptions</a>
      </div>
      <div class="card-body">
        <?php if ($academicPaths === []): ?>
          <p class="text-center text-muted py-4 mb-0">Aucune information de parcours scolaire n’a encore été enregistrée.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr><th>Année d’inscription</th><th>École de provenance</th><th>Année du bulletin</th><th>Mention</th><th>Bulletins</th></tr>
              </thead>
              <tbody>
                <?php foreach ($academicPaths as $path): ?>
                  <tr>
                    <td><?= $escape($path['annee'] ?? '') ?></td>
                    <td><?= $escape($path['ecole_provenance'] ?? '') ?></td>
                    <td><?= $escape($path['annee_document'] ?? '') ?></td>
                    <td><?= ($path['mention'] ?? '') === 'reussite' ? 'Réussite' : 'Échec' ?></td>
                    <td>
                      <?php if (empty($path['bulletins'])): ?>
                        <span class="text-muted">Aucune image jointe</span>
                      <?php else: ?>
                        <div class="d-flex flex-column gap-1">
                          <?php foreach ($path['bulletins'] as $bulletin): ?>
                            <a href="<?= BASE_URL ?>/inscriptions/bulletin?id=<?= (int) $bulletin['id'] ?>" target="_blank" rel="noopener">
                              <i class="bi bi-image me-1" aria-hidden="true"></i><?= $escape($bulletin['nom_original'] ?? 'Bulletin') ?>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
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
