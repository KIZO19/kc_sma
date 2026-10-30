<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= BASE_URL ?>/messages">
      <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Retour aux messages
    </a>
    <h1><?= htmlspecialchars((string) $message['title'], ENT_QUOTES, 'UTF-8') ?></h1>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <article class="card">
      <div class="card-header">
        <small class="text-muted"><?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string) $message['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
      </div>
      <div class="card-body">
        <p class="mb-0"><?= nl2br(htmlspecialchars((string) $message['body'], ENT_QUOTES, 'UTF-8')) ?></p>
      </div>
    </article>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
