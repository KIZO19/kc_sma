(function () {
  const widget = document.querySelector('.ai-chat');
  if (!widget) {
    return;
  }

  const panel = document.getElementById('aiChatPanel');
  const toggle = document.getElementById('aiChatToggle');
  const close = document.getElementById('aiChatClose');
  const reset = document.getElementById('aiChatReset');
  const messagesElement = document.getElementById('aiChatMessages');
  const form = document.getElementById('aiChatForm');
  const input = document.getElementById('aiChatInput');
  const sendButton = document.getElementById('aiChatSend');
  const conversation = [];
  const greeting = 'Bonjour ! Je peux vous aider avec des questions générales sur KC_SMA et son utilisation. Que souhaitez-vous savoir ?';
  const savedPositionKey = 'kc_sma_ai_chat_position';
  let dragState = null;
  let ignoreNextClick = false;

  function setLauncherPosition(left, top) {
    const maxLeft = Math.max(0, window.innerWidth - toggle.offsetWidth);
    const maxTop = Math.max(0, window.innerHeight - toggle.offsetHeight);
    const position = {
      left: Math.min(Math.max(0, left), maxLeft),
      top: Math.min(Math.max(0, top), maxTop)
    };

    widget.style.left = position.left + 'px';
    widget.style.top = position.top + 'px';
    widget.style.right = 'auto';
    widget.style.bottom = 'auto';
    return position;
  }

  try {
    const savedPosition = JSON.parse(window.localStorage.getItem(savedPositionKey) || 'null');
    if (
      savedPosition
      && Number.isFinite(savedPosition.left)
      && Number.isFinite(savedPosition.top)
    ) {
      setLauncherPosition(savedPosition.left, savedPosition.top);
    }
  } catch (error) {
    console.warn('Impossible de restaurer la position de l’assistant IA.', error);
  }

  window.addEventListener('resize', function () {
    if (widget.style.left && widget.style.top) {
      const position = setLauncherPosition(
        Number.parseFloat(widget.style.left),
        Number.parseFloat(widget.style.top)
      );
      try {
        window.localStorage.setItem(savedPositionKey, JSON.stringify(position));
      } catch (error) {
        console.warn('Impossible d’enregistrer la position de l’assistant IA.', error);
      }
    }
    if (!panel.hidden) {
      positionPanel();
    }
  });

  function positionPanel() {
    panel.classList.remove('ai-chat-panel-below', 'ai-chat-panel-align-left');
    const launcherRect = toggle.getBoundingClientRect();
    const panelRect = panel.getBoundingClientRect();

    if (launcherRect.top < panelRect.height + 16) {
      panel.classList.add('ai-chat-panel-below');
    }
    if (launcherRect.right - panelRect.width < 8) {
      panel.classList.add('ai-chat-panel-align-left');
    }
  }

  function setOpen(isOpen) {
    panel.hidden = !isOpen;
    toggle.setAttribute('aria-expanded', String(isOpen));
    if (isOpen) {
      positionPanel();
      input.focus();
    } else {
      panel.classList.remove('ai-chat-panel-below', 'ai-chat-panel-align-left');
      toggle.focus();
    }
  }

  function addMessage(text, kind) {
    const message = document.createElement('div');
    message.className = 'ai-chat-message ai-chat-message-' + kind;
    message.textContent = text;
    messagesElement.appendChild(message);
    messagesElement.scrollTop = messagesElement.scrollHeight;
    return message;
  }

  function resetConversation() {
    conversation.length = 0;
    messagesElement.replaceChildren();
    addMessage(greeting, 'assistant');
    input.focus();
  }

  toggle.addEventListener('pointerdown', function (event) {
    if (event.button !== undefined && event.button !== 0) {
      return;
    }

    const rect = toggle.getBoundingClientRect();
    dragState = {
      pointerId: event.pointerId,
      startX: event.clientX,
      startY: event.clientY,
      left: rect.left,
      top: rect.top,
      moved: false
    };
    toggle.setPointerCapture(event.pointerId);
  });

  toggle.addEventListener('pointermove', function (event) {
    if (!dragState || dragState.pointerId !== event.pointerId) {
      return;
    }

    const deltaX = event.clientX - dragState.startX;
    const deltaY = event.clientY - dragState.startY;
    if (!dragState.moved && Math.hypot(deltaX, deltaY) < 5) {
      return;
    }

    dragState.moved = true;
    toggle.classList.add('ai-chat-dragging');
    event.preventDefault();
    setLauncherPosition(dragState.left + deltaX, dragState.top + deltaY);
    if (typeof window.kcSmaResolveFloatingButtonOverlap === 'function') {
      window.kcSmaResolveFloatingButtonOverlap(toggle);
    }
    if (!panel.hidden) {
      positionPanel();
    }
  });

  function finishDrag(event, suppressClick) {
    if (!dragState || dragState.pointerId !== event.pointerId) {
      return;
    }

    if (dragState.moved) {
      ignoreNextClick = suppressClick;
      if (typeof window.kcSmaResolveFloatingButtonOverlap === 'function') {
        window.kcSmaResolveFloatingButtonOverlap(toggle);
      }
      const rect = toggle.getBoundingClientRect();
      try {
        window.localStorage.setItem(savedPositionKey, JSON.stringify({
          left: rect.left,
          top: rect.top
        }));
      } catch (error) {
        console.warn('Impossible d’enregistrer la position de l’assistant IA.', error);
      }
    }

    dragState = null;
    toggle.classList.remove('ai-chat-dragging');
  }

  toggle.addEventListener('pointerup', function (event) {
    finishDrag(event, true);
  });
  toggle.addEventListener('pointercancel', function (event) {
    finishDrag(event, false);
  });

  toggle.addEventListener('click', function () {
    if (ignoreNextClick) {
      ignoreNextClick = false;
      return;
    }
    setOpen(panel.hidden);
  });
  close.addEventListener('click', function () {
    setOpen(false);
  });
  reset.addEventListener('click', resetConversation);

  input.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      form.requestSubmit();
    }
  });

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    const message = input.value.trim();
    if (!message || sendButton.disabled) {
      return;
    }

    addMessage(message, 'user');
    input.value = '';
    input.disabled = true;
    sendButton.disabled = true;
    reset.disabled = true;
    const pendingMessage = addMessage('L’assistant réfléchit…', 'assistant');

    try {
      const response = await fetch(widget.dataset.endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': widget.dataset.csrfToken
        },
        body: JSON.stringify({
          messages: conversation.slice(-10).concat([{ role: 'user', content: message }])
        })
      });
      const result = await response.json();
      if (!response.ok) {
        throw new Error(result.error || 'Impossible d’obtenir une réponse pour le moment.');
      }
      if (typeof result.answer !== 'string' || !result.answer.trim()) {
        throw new Error('La réponse de l’assistant est vide. Veuillez réessayer.');
      }

      pendingMessage.textContent = result.answer;
      conversation.push({ role: 'user', content: message });
      conversation.push({ role: 'assistant', content: result.answer });
    } catch (error) {
      pendingMessage.classList.remove('ai-chat-message-assistant');
      pendingMessage.classList.add('ai-chat-message-error');
      pendingMessage.textContent = error instanceof Error
        ? error.message
        : 'Une erreur est survenue. Veuillez réessayer.';
    } finally {
      input.disabled = false;
      sendButton.disabled = false;
      reset.disabled = false;
      input.focus();
    }
  });
})();
