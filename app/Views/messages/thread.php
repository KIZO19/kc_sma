<?php
$currentUserId = (int) ($user['id'] ?? 0);
$initialMessages = $thread['messages'] ?? [];
$lastMessageId = $initialMessages ? (int) $initialMessages[count($initialMessages) - 1]['id'] : 0;
?>
<?php require __DIR__ . '/../partials/app_header.php'; ?>
<style>
  .school-chat {
    --chat-own-color: #0d6efd;
    --chat-own-text: #fff;
    --chat-font-size: 1rem;
  }
  .school-chat-log {
    height: min(62vh, 42rem);
    min-height: 20rem;
    overflow-y: auto;
    scroll-behavior: smooth;
    background: var(--bs-tertiary-bg);
  }
  .school-chat-message {
    width: fit-content;
    max-width: min(88%, 48rem);
    padding: .8rem 1rem;
    border: 1px solid var(--bs-border-color);
    border-radius: 1rem;
    overflow-wrap: anywhere;
    font-size: var(--chat-font-size);
    white-space: pre-wrap;
  }
  .school-chat-message.is-mine {
    margin-left: auto;
    color: var(--chat-own-text);
    border-color: var(--chat-own-color);
    background: var(--chat-own-color);
    border-bottom-right-radius: .25rem;
  }
  .school-chat-message.is-theirs {
    margin-right: auto;
    background: var(--bs-body-bg);
    border-bottom-left-radius: .25rem;
  }
  .school-chat.is-compact .school-chat-message {
    padding: .45rem .7rem;
  }
  @media (max-width: 576px) {
    .school-chat-log {
      height: 58vh;
      min-height: 16rem;
    }
    .school-chat-message {
      max-width: 94%;
    }
  }
</style>
<section class="content-header">
  <div class="container-fluid">
    <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= BASE_URL ?>/messages"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Retour aux messages</a>
    <h1><?= htmlspecialchars((string) $thread['subject'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted mb-0">
      <?= htmlspecialchars((string) $thread['student_name'], ENT_QUOTES, 'UTF-8') ?>
      · Conversation avec <?= htmlspecialchars(
          (int) $thread['parent_user_id'] === $currentUserId
              ? (string) $thread['staff_name']
              : (string) $thread['parent_name'],
          ENT_QUOTES,
          'UTF-8'
      ) ?>
    </p>
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
    <div
      class="card school-chat"
      id="school-chat"
      data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>"
      data-thread-id="<?= (int) $thread['id'] ?>"
      data-user-id="<?= $currentUserId ?>"
      data-csrf-token="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>"
      data-last-message-id="<?= $lastMessageId ?>"
    >
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <strong>Conversation</strong>
          <div class="small text-muted" id="chat-connection-status" role="status" aria-live="polite">Connexion en cours…</div>
        </div>
        <details class="position-relative">
          <summary class="btn btn-sm btn-outline-secondary"><i class="bi bi-sliders me-1" aria-hidden="true"></i>Personnaliser</summary>
          <div class="position-absolute end-0 mt-2 p-3 bg-body border rounded shadow" style="z-index:5;min-width:16rem" id="chat-preferences">
            <label class="form-label small mb-1" for="chat-color">Couleur de mes messages</label>
            <input class="form-control form-control-color mb-3" type="color" id="chat-color" value="#0d6efd" title="Couleur des messages envoyés">
            <label class="form-label small mb-1" for="chat-font-size">Taille du texte</label>
            <select class="form-select form-select-sm mb-3" id="chat-font-size">
              <option value="14">Petite</option>
              <option value="16" selected>Normale</option>
              <option value="18">Grande</option>
              <option value="20">Très grande</option>
            </select>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="chat-compact">
              <label class="form-check-label" for="chat-compact">Espacement compact</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="chat-reset-preferences">Réinitialiser</button>
          </div>
        </details>
      </div>
      <div class="card-body">
        <div id="conversation" class="school-chat-log d-flex flex-column gap-3 p-3 rounded" role="log" aria-live="polite" aria-relevant="additions text" aria-label="Messages de la conversation">
          <?php foreach ($initialMessages as $message): ?>
            <?php $isMine = (int) $message['sender_user_id'] === $currentUserId; ?>
            <article
              class="school-chat-message <?= $isMine ? 'is-mine' : 'is-theirs' ?>"
              data-message-id="<?= (int) $message['id'] ?>"
            >
              <div class="d-flex justify-content-between gap-3 mb-1">
                <strong><?= htmlspecialchars((string) $message['sender_name'], ENT_QUOTES, 'UTF-8') ?><?= $isMine ? ' · Vous' : '' ?></strong>
                <small class="<?= $isMine ? 'opacity-75' : 'text-muted' ?> text-nowrap"><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $message['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
              </div>
              <div><?= nl2br(htmlspecialchars((string) $message['body'], ENT_QUOTES, 'UTF-8')) ?></div>
            </article>
          <?php endforeach; ?>
        </div>
        <div id="chat-typing-status" class="small text-muted mt-2" aria-live="polite"></div>
        <div id="chat-error" class="small text-danger mt-2" role="alert"></div>
        <form id="chat-reply-form" class="mt-4">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="thread_id" value="<?= (int) $thread['id'] ?>">
          <label class="form-label" for="reply-body">Votre réponse</label>
          <div class="input-group">
            <textarea class="form-control" id="reply-body" name="body" rows="2" maxlength="5000" placeholder="Écrivez votre message…" required></textarea>
            <button class="btn btn-primary px-3" type="submit" id="chat-send-button">
              <i class="bi bi-send me-1" aria-hidden="true"></i>Envoyer
            </button>
          </div>
          <div class="d-flex justify-content-between mt-2">
            <small class="text-muted">Entrée pour envoyer · Maj + Entrée pour une nouvelle ligne</small>
            <small class="text-muted"><span id="chat-character-count">0</span>/5000</small>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<script>
  (function () {
    const chat = document.getElementById('school-chat');
    const log = document.getElementById('conversation');
    const form = document.getElementById('chat-reply-form');
    const input = document.getElementById('reply-body');
    if (!chat || !log || !form || !input) return;

    const baseUrl = chat.dataset.baseUrl;
    const threadId = chat.dataset.threadId;
    const csrfToken = chat.dataset.csrfToken;
    const storageKey = 'kc-chat-preferences-v1-' + chat.dataset.userId;
    const status = document.getElementById('chat-connection-status');
    const typingStatus = document.getElementById('chat-typing-status');
    const errorStatus = document.getElementById('chat-error');
    const sendButton = document.getElementById('chat-send-button');
    const colorInput = document.getElementById('chat-color');
    const fontInput = document.getElementById('chat-font-size');
    const compactInput = document.getElementById('chat-compact');
    const characterCount = document.getElementById('chat-character-count');
    let lastMessageId = Number(chat.dataset.lastMessageId || 0);
    let typingTimer;
    let lastTypingSent = 0;
    let isTyping = false;
    let polling = false;

    function nearBottom() {
      return log.scrollHeight - log.scrollTop - log.clientHeight < 100;
    }

    function scrollToLatest() {
      log.scrollTop = log.scrollHeight;
    }

    function applyColor(color) {
      colorInput.value = color;
      chat.style.setProperty('--chat-own-color', color);
      const channels = color.match(/[0-9a-f]{2}/gi).map(value => parseInt(value, 16));
      const brightness = (channels[0] * 299 + channels[1] * 587 + channels[2] * 114) / 1000;
      chat.style.setProperty('--chat-own-text', brightness > 150 ? '#17212b' : '#ffffff');
    }

    function applyPreferences(preferences) {
      const color = /^#[0-9a-f]{6}$/i.test(preferences.color || '') ? preferences.color : '#0d6efd';
      const allowedFontSizes = [14, 16, 18, 20];
      const fontSize = allowedFontSizes.includes(Number(preferences.fontSize)) ? Number(preferences.fontSize) : 16;
      applyColor(color);
      fontInput.value = String(fontSize);
      chat.style.setProperty('--chat-font-size', fontInput.value + 'px');
      compactInput.checked = Boolean(preferences.compact);
      chat.classList.toggle('is-compact', compactInput.checked);
    }

    function savePreferences() {
      const preferences = {
        color: colorInput.value,
        fontSize: Number(fontInput.value),
        compact: compactInput.checked
      };
      applyPreferences(preferences);
      try {
        localStorage.setItem(storageKey, JSON.stringify(preferences));
      } catch (error) {
        console.warn('Les préférences du chat ne peuvent pas être enregistrées sur cet appareil.', error);
      }
    }

    try {
      const storedPreferences = localStorage.getItem(storageKey);
      applyPreferences(storedPreferences ? JSON.parse(storedPreferences) : {});
    } catch (error) {
      applyPreferences({});
      console.warn('Les préférences du chat n’ont pas pu être chargées.', error);
    }
    colorInput.addEventListener('input', savePreferences);
    fontInput.addEventListener('change', savePreferences);
    compactInput.addEventListener('change', savePreferences);
    document.getElementById('chat-reset-preferences').addEventListener('click', function () {
      try {
        localStorage.removeItem(storageKey);
      } catch (error) {
        console.warn('Les préférences du chat ne peuvent pas être supprimées.', error);
      }
      applyPreferences({});
    });

    function appendMessage(message) {
      const messageId = Number(message.id);
      if (!messageId || log.querySelector('[data-message-id="' + messageId + '"]')) return;
      const shouldScroll = nearBottom();
      const mine = Number(message.sender_user_id) === Number(chat.dataset.userId);
      const article = document.createElement('article');
      article.className = 'school-chat-message ' + (mine ? 'is-mine' : 'is-theirs');
      article.dataset.messageId = String(messageId);

      const header = document.createElement('div');
      header.className = 'd-flex justify-content-between gap-3 mb-1';
      const name = document.createElement('strong');
      name.textContent = (message.sender_name || 'Utilisateur') + (mine ? ' · Vous' : '');
      const time = document.createElement('small');
      time.className = (mine ? 'opacity-75' : 'text-muted') + ' text-nowrap';
      const date = new Date(String(message.created_at).replace(' ', 'T'));
      time.textContent = Number.isNaN(date.getTime()) ? '' : date.toLocaleString();
      header.append(name, time);
      const body = document.createElement('div');
      body.textContent = message.body || '';
      article.append(header, body);
      log.appendChild(article);
      lastMessageId = Math.max(lastMessageId, messageId);
      chat.dataset.lastMessageId = String(lastMessageId);
      if (shouldScroll) scrollToLatest();
    }

    async function updateTyping(typing) {
      if (isTyping === typing && !typing) return;
      isTyping = typing;
      const formData = new FormData();
      formData.set('csrf_token', csrfToken);
      formData.set('thread_id', threadId);
      formData.set('typing', typing ? '1' : '0');
      try {
        const response = await fetch(baseUrl + '/messages/chatTyping', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('HTTP ' + response.status);
        const result = await response.json();
        if (result.error) throw new Error(result.error);
      } catch (error) {
        console.error('Impossible de mettre à jour le statut de saisie :', error);
      }
    }

    async function refreshConversation() {
      if (polling || document.hidden) return;
      polling = true;
      try {
        const response = await fetch(
          baseUrl + '/messages/chatPoll?thread_id=' + encodeURIComponent(threadId) + '&after_id=' + encodeURIComponent(lastMessageId),
          { credentials: 'same-origin', headers: { 'Accept': 'application/json' }, cache: 'no-store' }
        );
        if (!response.ok) throw new Error('HTTP ' + response.status);
        const result = await response.json();
        if (result.error) throw new Error(result.error);
        result.messages.forEach(appendMessage);
        typingStatus.textContent = result.typingName ? result.typingName + ' écrit…' : '';
        status.textContent = 'Connecté · mise à jour instantanée';
        status.classList.remove('text-danger');
        errorStatus.textContent = '';
      } catch (error) {
        status.textContent = 'Reconnexion…';
        status.classList.add('text-danger');
        errorStatus.textContent = 'La conversation n’a pas pu être actualisée. Nouvelle tentative automatique.';
        console.error('Impossible d’actualiser la conversation :', error);
      } finally {
        polling = false;
      }
    }

    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      const body = input.value.trim();
      if (!body) {
        input.focus();
        return;
      }

      sendButton.disabled = true;
      errorStatus.textContent = '';
      const formData = new FormData(form);
      formData.set('body', body);
      try {
        const response = await fetch(baseUrl + '/messages/chatReply', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (!response.ok || result.error) throw new Error(result.error || 'Le message n’a pas pu être envoyé.');
        input.value = '';
        characterCount.textContent = '0';
        await updateTyping(false);
        await refreshConversation();
        input.focus();
      } catch (error) {
        errorStatus.textContent = error.message || 'Le message n’a pas pu être envoyé. Réessayez.';
      } finally {
        sendButton.disabled = false;
      }
    });

    input.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        form.requestSubmit();
      }
    });
    input.addEventListener('input', function () {
      characterCount.textContent = String(input.value.length);
      const now = Date.now();
      if (now - lastTypingSent > 2000) {
        lastTypingSent = now;
        updateTyping(true);
      }
      clearTimeout(typingTimer);
      typingTimer = setTimeout(function () {
        updateTyping(false);
      }, 1400);
    });
    input.addEventListener('blur', function () {
      clearTimeout(typingTimer);
      updateTyping(false);
    });
    document.addEventListener('visibilitychange', refreshConversation);
    window.addEventListener('beforeunload', function () {
      if (isTyping) {
        const formData = new FormData();
        formData.set('csrf_token', csrfToken);
        formData.set('thread_id', threadId);
        formData.set('typing', '0');
        navigator.sendBeacon(baseUrl + '/messages/chatTyping', formData);
      }
    });

    scrollToLatest();
    refreshConversation();
    window.setInterval(refreshConversation, 1800);
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
