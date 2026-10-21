<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Messages</h1>
    <p class="text-muted mb-0">Informations qui vous sont adressées selon votre compte et vos accès.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <?php if (empty($messages)): ?>
          <p class="text-muted mb-0">Aucun message reçu.</p>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($messages as $message): ?>
              <article class="list-group-item py-3 <?= (int) $message['is_read'] === 0 ? 'bg-body-tertiary' : '' ?>" id="message-<?= (int) $message['id'] ?>">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div>
                    <h2 class="h6 mb-1">
                      <?= htmlspecialchars((string) $message['title'], ENT_QUOTES, 'UTF-8') ?>
                      <?php if ((int) $message['is_read'] === 0): ?><span class="badge text-bg-primary ms-1">Nouveau</span><?php endif; ?>
                    </h2>
                    <p class="mb-1"><?= nl2br(htmlspecialchars((string) $message['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <small class="text-muted"><?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string) $message['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
