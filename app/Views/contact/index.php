<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Contacter KC_SMA</h1>
    <p class="text-muted mb-0">Envoyez un message à l’équipe KC_SMA. Votre nom et votre école seront joints automatiquement.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($success)): ?>
      <div class="alert alert-success" role="status"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-xl-8 col-lg-10">
        <div class="card card-outline card-primary">
          <div class="card-header"><h2 class="card-title h5 mb-0">Nouveau message</h2></div>
          <form method="post" action="<?= BASE_URL ?>/contact/send">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="contact-subject">Objet</label>
                <input class="form-control" id="contact-subject" name="subject" maxlength="160" required>
              </div>
              <div>
                <label class="form-label" for="contact-body">Message</label>
                <textarea class="form-control" id="contact-body" name="body" rows="6" maxlength="5000" required></textarea>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
              <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/dashboard">Annuler</a>
              <button class="btn btn-primary" type="submit"><i class="bi bi-send me-1" aria-hidden="true"></i>Envoyer à KC_SMA</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
