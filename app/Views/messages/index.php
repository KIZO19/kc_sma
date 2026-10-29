<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Messagerie</h1>
    <p class="text-muted mb-0">Échanges privés entre les familles et les responsables de l’établissement.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['messages_success'])): ?>
      <div class="alert alert-success" role="status"><?= htmlspecialchars((string) $_SESSION['messages_success'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php unset($_SESSION['messages_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['messages_error'])): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $_SESSION['messages_error'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php unset($_SESSION['messages_error']); ?>
    <?php endif; ?>

    <?php if (!empty($canCompose)): ?>
      <div class="card card-outline card-primary mb-4">
        <div class="card-header"><h2 class="card-title h5 mb-0">Nouveau message</h2></div>
        <div class="card-body">
          <?php if (empty($contacts) || (!empty($isParent) && empty($children))): ?>
            <p class="text-muted mb-0"><?= !empty($isParent) ? 'Aucun responsable ou enfant lié à votre compte ne peut être sélectionné.' : 'Aucun parent actif associé à un élève de votre périmètre.' ?></p>
          <?php else: ?>
            <form method="post" action="<?= BASE_URL ?>/messages/send" class="row g-3">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!empty($isParent)): ?>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="message-child">Enfant concerné</label>
                  <select class="form-select" id="message-child" name="student_id" required>
                    <option value="">Choisir un enfant</option>
                    <?php foreach (($children ?? []) as $child): ?>
                      <option value="<?= (int) $child['id'] ?>"><?= htmlspecialchars(trim(implode(' ', array_filter([$child['nom'] ?? '', $child['postnom'] ?? '', $child['prenom'] ?? '']))), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="message-recipient">Responsable de l’école</label>
                  <select class="form-select" id="message-recipient" name="recipient_user_id" required>
                    <option value="">Choisir un destinataire</option>
                    <?php foreach (($contacts ?? []) as $contact): ?>
                      <option value="<?= (int) $contact['id'] ?>"><?= htmlspecialchars($contact['nom_complet'] . ' — ' . \App\Models\User::getRoleLabel($contact['role']), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php else: ?>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="message-recipient">Parent et enfant concerné</label>
                  <select class="form-select" id="message-recipient" name="recipient" required>
                    <option value="">Choisir un parent et un enfant</option>
                    <?php foreach (($contacts ?? []) as $contact): ?>
                      <?php $studentName = trim(implode(' ', array_filter([$contact['nom'] ?? '', $contact['postnom'] ?? '', $contact['prenom'] ?? '']))); ?>
                      <option value="<?= (int) $contact['parent_user_id'] ?>:<?= (int) $contact['eleve_id'] ?>">
                        <?= htmlspecialchars($contact['parent_nom'] . ' — ' . $studentName, ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
              <div class="col-12">
                <label class="form-label" for="message-subject">Objet</label>
                <input class="form-control" id="message-subject" name="subject" maxlength="160" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="message-body">Message</label>
                <textarea class="form-control" id="message-body" name="body" rows="4" maxlength="5000" required></textarea>
              </div>
              <div class="col-12 text-end">
                <button class="btn btn-primary" type="submit"><i class="bi bi-send me-1" aria-hidden="true"></i>Envoyer</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="card mb-4">
      <div class="card-header"><h2 class="card-title h5 mb-0">Conversations</h2></div>
      <div class="card-body p-0">
        <?php if (empty($threads)): ?>
          <p class="text-muted p-3 mb-0">Aucune conversation pour le moment.</p>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($threads as $thread): ?>
              <a class="list-group-item list-group-item-action py-3" href="<?= BASE_URL ?>/messages/thread?id=<?= (int) $thread['id'] ?>">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                  <div class="me-auto">
                    <div class="fw-semibold">
                      <?= htmlspecialchars((string) $thread['subject'], ENT_QUOTES, 'UTF-8') ?>
                      <?php if ((int) $thread['unread_count'] > 0): ?><span class="badge text-bg-primary ms-1"><?= (int) $thread['unread_count'] ?> nouveau<?= (int) $thread['unread_count'] > 1 ? 'x' : '' ?></span><?php endif; ?>
                    </div>
                    <div class="small text-muted"><?= htmlspecialchars((string) $thread['other_name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $thread['student_name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="small mt-1"><?= htmlspecialchars(mb_strimwidth((string) ($thread['last_body'] ?? ''), 0, 160, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></div>
                  </div>
                  <small class="text-muted"><?= !empty($thread['last_message_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime((string) $thread['last_message_at'])), ENT_QUOTES, 'UTF-8') : '' ?></small>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h2 class="card-title h5 mb-0">Notifications reçues</h2></div>
      <div class="card-body p-0">
        <?php if (empty($messages)): ?>
          <p class="text-muted p-3 mb-0">Aucune notification reçue.</p>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($messages as $message): ?>
              <article class="list-group-item py-3 <?= (int) $message['is_read'] === 0 ? 'bg-body-tertiary' : '' ?>" id="message-<?= (int) $message['id'] ?>">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div>
                    <h3 class="h6 mb-1">
                      <?= htmlspecialchars((string) $message['title'], ENT_QUOTES, 'UTF-8') ?>
                      <?php if ((int) $message['is_read'] === 0): ?><span class="badge text-bg-primary ms-1">Nouveau</span><?php endif; ?>
                    </h3>
                    <p class="mb-1"><?= nl2br(htmlspecialchars((string) $message['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <small class="text-muted"><?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string) $message['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                  </div>
                  <a class="btn btn-sm btn-outline-primary flex-shrink-0" href="<?= BASE_URL ?>/messages/open?id=<?= (int) $message['id'] ?>">
                    <i class="bi bi-envelope-open me-1" aria-hidden="true"></i>Ouvrir
                  </a>
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
