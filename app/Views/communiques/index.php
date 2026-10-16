<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1>Communiqués aux parents</h1>
        <p class="text-muted mb-0">Envoyez une information aux parents de votre école par les canaux configurés.</p>
      </div>
      <div class="col-sm-4">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Communiqués</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($success)): ?>
      <div class="alert alert-info alert-dismissible">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger alert-dismissible">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>

    <div class="card card-outline card-primary">
      <div class="card-header"><h3 class="card-title">Nouveau communiqué</h3></div>
      <div class="card-body">
        <div class="alert alert-warning">
          L’envoi est immédiat à tous les numéros de parents connus de l’école. Vérifiez attentivement le texte avant l’envoi.
        </div>
        <form method="post" action="<?= BASE_URL ?>/communiques/send" id="communiqueForm">
          <input type="hidden" name="communique_form_token" value="<?= htmlspecialchars($formToken, ENT_QUOTES, 'UTF-8') ?>">
          <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" class="form-control" id="titre" name="titre" maxlength="160" required>
          </div>
          <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="5" maxlength="5000" required></textarea>
            <div class="form-text">Le nom de l’école sera automatiquement ajouté au message.</div>
          </div>
          <button type="submit" class="btn btn-primary" id="sendCommunique">
            <i class="bi bi-send me-1"></i>Envoyer aux parents
          </button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3 class="card-title">Historique des communiqués</h3></div>
      <div class="card-body table-responsive">
        <?php if (empty($communiques)): ?>
          <div class="text-muted">Aucun communiqué envoyé pour le moment.</div>
        <?php else: ?>
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr><th>Date</th><th>Titre</th><th>Auteur</th><th>Parents joints</th><th>Échec</th><th>Résultat</th></tr>
            </thead>
            <tbody>
              <?php foreach ($communiques as $communique): ?>
                <?php
                  $statusClass = match ($communique['statut'] ?? '') {
                    'success' => 'bg-success',
                    'partial' => 'bg-warning text-dark',
                    default => 'bg-danger',
                  };
                  $statusLabel = match ($communique['statut'] ?? '') {
                    'success' => 'Envoyé',
                    'partial' => 'Partiel',
                    default => 'Échec',
                  };
                ?>
                <tr>
                  <td><?= htmlspecialchars((string) ($communique['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <strong><?= htmlspecialchars((string) ($communique['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <details><summary>Afficher le message</summary><p class="mt-2 mb-0"><?= nl2br(htmlspecialchars((string) ($communique['message'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p></details>
                  </td>
                  <td><?= htmlspecialchars((string) ($communique['auteur_nom'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int) ($communique['destinataires_livres'] ?? 0) ?> / <?= (int) ($communique['destinataires_total'] ?? 0) ?></td>
                  <td><?= (int) ($communique['destinataires_echec'] ?? 0) ?></td>
                  <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
document.getElementById('communiqueForm')?.addEventListener('submit', function (event) {
  if (!window.confirm('Envoyer immédiatement ce communiqué à tous les parents de l’école ?')) {
    event.preventDefault();
  }
});
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
