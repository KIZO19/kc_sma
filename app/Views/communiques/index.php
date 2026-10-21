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
          Le communiqué sera visible dans la messagerie des rôles sélectionnés. Les parents ciblés recevront également un SMS/WhatsApp si leurs coordonnées sont disponibles.
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
          </div>
          <fieldset class="border rounded p-3 mb-3">
            <legend class="float-none w-auto px-2 fs-6">Rôles destinataires <span class="text-danger">*</span></legend>
            <div class="row g-2">
              <?php foreach ($audienceRoles as $roleKey => $roleLabel): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="destinataires_roles[]" id="communique-role-<?= htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8') ?>" <?= $roleKey === 'parent_ecole' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="communique-role-<?= htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </fieldset>
          <fieldset class="border rounded p-3 mb-3">
            <legend class="float-none w-auto px-2 fs-6">Sections concernées</legend>
            <p class="small text-muted">Laissez vide pour toutes les sections auxquelles vous avez accès.</p>
            <div class="row g-2">
              <?php foreach ($sections as $section): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="destinataires_sections[]" id="communique-section-<?= (int) $section['id'] ?>" value="<?= (int) $section['id'] ?>" <?= count($sections) === 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="communique-section-<?= (int) $section['id'] ?>"><?= htmlspecialchars($section['nom_section'], ENT_QUOTES, 'UTF-8') ?></label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </fieldset>
          <button type="submit" class="btn btn-primary" id="sendCommunique">
            <i class="bi bi-send me-1"></i>Envoyer le communiqué
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
  if (!window.confirm('Envoyer ce communiqué aux rôles et sections sélectionnés ?')) {
    event.preventDefault();
  }
});
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
