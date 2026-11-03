(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.app-header[data-notifications-endpoint]');
    const messagesMenu = document.getElementById('messagesNotificationMenu');
    const eventsMenu = document.getElementById('eventsNotificationMenu');
    const messageBadge = document.getElementById('messagesNotificationBadge');
    const eventBadge = document.getElementById('eventsNotificationBadge');
    const toastRegion = document.getElementById('liveNotificationToasts');
    const soundToggle = document.getElementById('notificationSoundToggle');
    const phoneNotificationToggle = document.getElementById('phoneNotificationToggle');
    const phoneNotificationHelp = document.getElementById('phoneNotificationHelp');

    if (!header || !messagesMenu || !eventsMenu || !toastRegion || !messageBadge || !eventBadge) {
      return;
    }

    const endpoint = header.dataset.notificationsEndpoint;
    const baseUrl = header.dataset.notificationsBaseUrl;
    const notificationIcon = header.dataset.notificationsIcon;
    const canComposeMessages = header.dataset.canComposeMessages === '1';
    const soundStorageKey = 'adminkc_notification_sound';
    const phoneNotificationStorageKey = 'adminkc_phone_notifications';
    const knownNotificationIds = new Set();
    let hasInitialSnapshot = false;
    let lastRenderSignature = '';
    let audioContext = null;
    let isPolling = false;
    let soundEnabled = localStorage.getItem(soundStorageKey) !== 'false';
    let phoneNotificationsEnabled = localStorage.getItem(phoneNotificationStorageKey) === 'true';

    function setSoundEnabled(enabled) {
      soundEnabled = enabled;
      localStorage.setItem(soundStorageKey, enabled ? 'true' : 'false');
      if (soundToggle) {
        soundToggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
        soundToggle.innerHTML = enabled
          ? '<i class="bi bi-volume-up-fill me-2" aria-hidden="true"></i>Bip sonore de la page activé'
          : '<i class="bi bi-volume-mute-fill me-2" aria-hidden="true"></i>Bip sonore de la page désactivé';
      }
    }

    async function unlockAudio() {
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (!AudioContextClass) {
        return false;
      }
      try {
        if (!audioContext) {
          audioContext = new AudioContextClass();
        }
        if (audioContext.state === 'suspended') {
          await audioContext.resume();
        }
        return audioContext.state === 'running';
      } catch (error) {
        console.warn('Impossible d’activer le son des notifications :', error);
        return false;
      }
    }

    async function playNotificationSound() {
      if (!soundEnabled || phoneNotificationsEnabled || !audioContext) {
        return;
      }
      if (audioContext.state !== 'running' && !await unlockAudio()) {
        return;
      }

      const oscillator = audioContext.createOscillator();
      const gain = audioContext.createGain();
      const now = audioContext.currentTime;
      oscillator.type = 'sine';
      oscillator.frequency.setValueAtTime(880, now);
      gain.gain.setValueAtTime(0.0001, now);
      gain.gain.exponentialRampToValueAtTime(0.12, now + 0.015);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.18);
      oscillator.connect(gain);
      gain.connect(audioContext.destination);
      oscillator.start(now);
      oscillator.stop(now + 0.2);
    }

    function updatePhoneNotificationControl() {
      if (!phoneNotificationToggle || !phoneNotificationHelp) {
        return;
      }

      if (!('Notification' in window) || !('serviceWorker' in navigator)) {
        phoneNotificationToggle.disabled = true;
        phoneNotificationToggle.innerHTML = '<i class="bi bi-phone me-2" aria-hidden="true"></i>Notifications du téléphone indisponibles';
        phoneNotificationHelp.textContent = 'Ce navigateur ne prend pas en charge les notifications système.';
        return;
      }

      if (Notification.permission === 'denied') {
        phoneNotificationToggle.disabled = true;
        phoneNotificationToggle.setAttribute('aria-pressed', 'false');
        phoneNotificationToggle.innerHTML = '<i class="bi bi-bell-slash me-2" aria-hidden="true"></i>Notifications bloquées';
        phoneNotificationHelp.textContent = 'Autorisez les notifications de KC_SMA dans les réglages du navigateur ou du téléphone.';
        return;
      }

      const isEnabled = phoneNotificationsEnabled && Notification.permission === 'granted';
      phoneNotificationToggle.disabled = false;
      phoneNotificationToggle.setAttribute('aria-pressed', isEnabled ? 'true' : 'false');
      phoneNotificationToggle.innerHTML = isEnabled
        ? '<i class="bi bi-phone-vibrate me-2" aria-hidden="true"></i>Notifications du téléphone activées'
        : '<i class="bi bi-phone me-2" aria-hidden="true"></i>Activer les notifications du téléphone';
      phoneNotificationHelp.textContent = isEnabled
        ? 'Le son suit les réglages du téléphone ; la vibration dépend du navigateur et des réglages système.'
        : 'Autorisez les alertes système pour utiliser les réglages de notification du téléphone.';
    }

    async function showPhoneNotification(item) {
      if (
        !phoneNotificationsEnabled
        || !('Notification' in window)
        || Notification.permission !== 'granted'
        || !('serviceWorker' in navigator)
      ) {
        return;
      }

      try {
        const registration = await navigator.serviceWorker.ready;
        await registration.showNotification(item.title || 'Nouvelle notification KC_SMA', {
          body: item.body || '',
          icon: notificationIcon,
          badge: notificationIcon,
          tag: 'kc-sma-' + item.id,
          renotify: true,
          vibrate: [200, 100, 200],
          data: { url: new URL(item.href, window.location.origin).href }
        });
      } catch (error) {
        console.warn('Impossible d’afficher la notification système :', error);
        if (soundEnabled && navigator.vibrate) {
          navigator.vibrate([200, 100, 200]);
        }
      }
    }

    function createElement(tag, className, text) {
      const element = document.createElement(tag);
      if (className) {
        element.className = className;
      }
      if (text !== undefined) {
        element.textContent = text;
      }
      return element;
    }

    function addDivider(container) {
      container.appendChild(createElement('div', 'dropdown-divider'));
    }

    function addSectionTitle(container, text) {
      container.appendChild(createElement('span', 'dropdown-item-text fw-bold', text));
      addDivider(container);
    }

    function addLink(container, href, iconClass, title, detail) {
      const link = createElement('a', 'dropdown-item text-wrap');
      link.href = href;
      const icon = createElement('i', 'me-2 ' + iconClass);
      icon.setAttribute('aria-hidden', 'true');
      link.appendChild(icon);
      if (title) {
        link.appendChild(createElement('strong', '', title));
      }
      if (detail) {
        link.appendChild(createElement('span', 'd-block ms-4 small text-muted', detail));
      }
      container.appendChild(link);
    }

    function formatDate(value, includeTime) {
      if (!value) {
        return '';
      }
      const date = new Date(String(value).replace(' ', 'T'));
      if (Number.isNaN(date.getTime())) {
        return '';
      }
      return new Intl.DateTimeFormat('fr-FR', includeTime
        ? { dateStyle: 'short', timeStyle: 'short' }
        : { dateStyle: 'short' }).format(date);
    }

    function setBadge(badge, count) {
      const safeCount = Math.max(0, Number(count) || 0);
      badge.textContent = safeCount > 99 ? '99+' : String(safeCount);
      badge.hidden = safeCount === 0;
    }

    function renderMessages(data) {
      messagesMenu.replaceChildren();
      let hasSection = false;

      if (data.messages.length) {
        addSectionTitle(messagesMenu, 'Nouveaux messages');
        data.messages.forEach(function (message) {
          addLink(
            messagesMenu,
            baseUrl + '/messages/open?id=' + encodeURIComponent(message.id),
            'bi bi-envelope-fill text-primary',
            message.title,
            ''
          );
        });
        hasSection = true;
      }

      if (data.directMessages.length) {
        if (hasSection) addDivider(messagesMenu);
        addSectionTitle(messagesMenu, 'Nouveaux messages privés');
        data.directMessages.forEach(function (message) {
          addLink(
            messagesMenu,
            baseUrl + '/messages/thread?id=' + encodeURIComponent(message.thread_id),
            'bi bi-chat-left-text-fill text-primary',
            message.subject || 'Nouveau message',
            ''
          );
        });
        hasSection = true;
      }

      if (data.pendingDerogations.length) {
        if (hasSection) addDivider(messagesMenu);
        addSectionTitle(messagesMenu, 'Demandes à valider');
        data.pendingDerogations.forEach(function (item) {
          const endDate = formatDate(item.date_fin, false);
          const detail = 'Demande de ' + (item.demandeur_nom || 'Autorité scolaire')
            + (endDate ? ' · jusqu’au ' + endDate : '');
          addLink(
            messagesMenu,
            baseUrl + '/derogations#derogation-' + encodeURIComponent(item.id),
            'bi bi-file-earmark-check text-warning',
            item.eleve_nom || 'Élève',
            detail
          );
        });
        hasSection = true;
      } else if (data.pendingDerogationCount !== null) {
        if (hasSection) addDivider(messagesMenu);
        addSectionTitle(messagesMenu, 'Demandes à valider');
        messagesMenu.appendChild(createElement('span', 'dropdown-item-text text-muted small', 'Aucune demande de dérogation à valider.'));
        hasSection = true;
      }

      if (data.decisionMessages.length) {
        if (hasSection) addDivider(messagesMenu);
        addSectionTitle(messagesMenu, 'Décisions sur vos demandes');
        data.decisionMessages.forEach(function (item) {
          const approved = item.statut === 'Approuvee';
          const decision = approved ? 'demande approuvée' : 'demande refusée';
          const detail = 'Par ' + (item.validateur_nom || 'le comptable')
            + (item.commentaire ? ' · ' + item.commentaire : '');
          addLink(
            messagesMenu,
            baseUrl + '/derogations/open-message?id=' + encodeURIComponent(item.id),
            'bi ' + (approved ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'),
            (item.eleve_nom || 'Élève') + ' : ' + decision,
            detail
          );
        });
        hasSection = true;
      } else if (data.decisionMessageCount !== null) {
        if (hasSection) addDivider(messagesMenu);
        addSectionTitle(messagesMenu, 'Décisions sur vos demandes');
        messagesMenu.appendChild(createElement('span', 'dropdown-item-text text-muted small', 'Aucune nouvelle décision sur vos demandes.'));
        hasSection = true;
      }

      if (!hasSection) {
        messagesMenu.appendChild(createElement('span', 'dropdown-item-text text-muted small', 'Aucun nouveau message.'));
      }

      addDivider(messagesMenu);
      if (canComposeMessages) {
        addLink(
          messagesMenu,
          baseUrl + '/messages?compose=1#compose-message',
          'bi bi-pencil-square text-primary',
          'Commencer une conversation',
          ''
        );
        addDivider(messagesMenu);
      }
      addLink(messagesMenu, baseUrl + '/messages', '', 'Ouvrir les messages', '');
      if (data.pendingDerogationCount !== null || data.decisionMessageCount !== null) {
        addDivider(messagesMenu);
        addLink(messagesMenu, baseUrl + '/derogations', '', 'Ouvrir les dérogations', '');
      }
      addDivider(messagesMenu);
      if (phoneNotificationToggle) {
        messagesMenu.appendChild(phoneNotificationToggle);
        if (phoneNotificationHelp) {
          messagesMenu.appendChild(phoneNotificationHelp);
        }
        addDivider(messagesMenu);
      }
      messagesMenu.appendChild(soundToggle);
    }

    function renderEvents(events) {
      eventsMenu.replaceChildren();
      eventsMenu.appendChild(createElement('span', 'dropdown-item-text fw-bold', 'Notifications d’événements'));
      addDivider(eventsMenu);

      if (events.length) {
        events.forEach(function (event) {
          const date = formatDate(event.date_debut, true);
          const detail = [date, event.lieu].filter(Boolean).join(' · ');
          addLink(
            eventsMenu,
            baseUrl + '/evenements/open-notification?id=' + encodeURIComponent(event.id),
            'bi bi-calendar-event text-primary',
            event.titre,
            detail
          );
        });
      } else {
        eventsMenu.appendChild(createElement('span', 'dropdown-item-text text-muted small', 'Aucune notification pour le moment.'));
      }

      addDivider(eventsMenu);
      addLink(eventsMenu, baseUrl + '/evenements', '', 'Voir les événements', '');
    }

    function notificationItems(data) {
      const items = [];
      data.messages.forEach(item => items.push({
        id: 'message-' + item.id,
        title: item.title,
        body: item.body,
        href: baseUrl + '/messages/open?id=' + encodeURIComponent(item.id)
      }));
      data.directMessages.forEach(item => items.push({
        id: 'direct-message-' + item.id,
        title: item.subject || 'Nouveau message',
        body: [item.sender_name, item.body].filter(Boolean).join(' · '),
        href: baseUrl + '/messages/thread?id=' + encodeURIComponent(item.thread_id)
      }));
      data.pendingDerogations.forEach(item => items.push({
        id: 'derogation-' + item.id,
        title: 'Nouvelle demande de dérogation',
        body: [
          item.eleve_nom,
          item.demandeur_nom ? 'Demande de ' + item.demandeur_nom : null,
          item.date_fin ? 'Jusqu’au ' + formatDate(item.date_fin, false) : null,
          item.motif
        ].filter(Boolean).join(' · ') || 'Une demande attend votre validation.',
        href: baseUrl + '/derogations#derogation-' + encodeURIComponent(item.id)
      }));
      data.decisionMessages.forEach(item => items.push({
        id: 'decision-' + item.id,
        title: 'Décision sur une dérogation',
        body: item.eleve_nom || '',
        href: baseUrl + '/derogations/open-message?id=' + encodeURIComponent(item.id)
      }));
      data.events.filter(item => Number(item.is_read) === 0).forEach(item => items.push({
        id: 'event-' + item.id,
        title: item.titre,
        body: 'Nouvel événement' + (item.date_debut ? ' · ' + formatDate(item.date_debut, true) : ''),
        href: baseUrl + '/evenements/open-notification?id=' + encodeURIComponent(item.id)
      }));
      return items;
    }

    function showToast(item) {
      const toast = createElement('div', 'alert alert-primary alert-dismissible live-notification-toast mb-0');
      toast.setAttribute('role', 'status');
      const link = createElement('a', 'text-reset text-decoration-none');
      link.href = item.href;
      link.appendChild(createElement('strong', 'd-block', item.title || 'Nouvelle notification'));
      if (item.body) {
        link.appendChild(createElement('span', 'small', item.body));
      }
      toast.appendChild(link);
      const close = createElement('button', 'btn-close');
      close.type = 'button';
      close.setAttribute('aria-label', 'Fermer');
      close.addEventListener('click', function () {
        toast.remove();
      });
      toast.appendChild(close);
      toastRegion.appendChild(toast);
      window.setTimeout(function () {
        toast.remove();
      }, 8000);
    }

    function updateNotifications(data) {
      const items = notificationItems(data);
      const currentIds = new Set(items.map(item => item.id));
      if (hasInitialSnapshot) {
        items.forEach(function (item) {
          if (!knownNotificationIds.has(item.id)) {
            showToast(item);
            showPhoneNotification(item);
            void playNotificationSound();
          }
        });
      }
      currentIds.forEach(id => knownNotificationIds.add(id));
      while (knownNotificationIds.size > 500) {
        knownNotificationIds.delete(knownNotificationIds.values().next().value);
      }
      hasInitialSnapshot = true;

      const totalMessages = Number(data.messageCount || 0)
        + Number(data.directMessageCount || 0)
        + Number(data.pendingDerogationCount || 0)
        + Number(data.decisionMessageCount || 0);
      const signature = JSON.stringify(data);
      if (signature !== lastRenderSignature) {
        setBadge(messageBadge, totalMessages);
        setBadge(eventBadge, data.eventCount);
        renderMessages(data);
        renderEvents(data.events);
        lastRenderSignature = signature;
      }
    }

    async function pollNotifications() {
      if (isPolling || document.visibilityState !== 'visible') {
        return;
      }
      isPolling = true;
      try {
        const response = await fetch(endpoint, {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
          cache: 'no-store'
        });
        if (response.status === 401) {
          window.clearInterval(pollTimer);
          return;
        }
        if (response.redirected || !(response.headers.get('content-type') || '').includes('application/json')) {
          window.clearInterval(pollTimer);
          return;
        }
        if (!response.ok) {
          throw new Error('Notification polling returned HTTP ' + response.status);
        }
        const data = await response.json();
        updateNotifications(data);
      } catch (error) {
        console.error('Échec de la mise à jour des notifications :', error);
      } finally {
        isPolling = false;
      }
    }

    if (soundToggle) {
      setSoundEnabled(soundEnabled);
      soundToggle.addEventListener('click', async function () {
        await unlockAudio();
        setSoundEnabled(!soundEnabled);
        if (soundEnabled) {
          await playNotificationSound();
        }
      });
    }

    if (phoneNotificationToggle) {
      updatePhoneNotificationControl();
      phoneNotificationToggle.addEventListener('click', async function () {
        if (!('Notification' in window)) {
          updatePhoneNotificationControl();
          return;
        }

        try {
          if (Notification.permission !== 'granted') {
            const permission = await Notification.requestPermission();
            phoneNotificationsEnabled = permission === 'granted';
          } else {
            phoneNotificationsEnabled = !phoneNotificationsEnabled;
          }
          localStorage.setItem(phoneNotificationStorageKey, phoneNotificationsEnabled ? 'true' : 'false');
          updatePhoneNotificationControl();
        } catch (error) {
          console.error('Impossible de modifier l’autorisation des notifications :', error);
          phoneNotificationHelp.textContent = 'Impossible de modifier cette autorisation dans le navigateur.';
        }
      });
    }

    document.addEventListener('pointerdown', function () {
      void unlockAudio();
    }, { once: true });
    document.addEventListener('keydown', function () {
      void unlockAudio();
    }, { once: true });
    document.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'visible') {
        pollNotifications();
      }
    });

    const pollInterval = Number(header.dataset.notificationsInterval);
    const pollTimer = window.setInterval(
      pollNotifications,
      Number.isFinite(pollInterval) && pollInterval >= 1000 ? pollInterval : 5000
    );
    pollNotifications();
  });
})();
