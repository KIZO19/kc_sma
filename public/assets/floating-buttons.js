(function () {
  const savedPositionKeys = {
    aiChatToggle: 'kc_sma_ai_chat_position',
    pwaInstallButton: 'kc_sma_pwa_install_position'
  };

  window.kcSmaResolveFloatingButtonOverlap = function (activeButton) {
    const buttons = [
      document.getElementById('aiChatToggle'),
      document.getElementById('pwaInstallButton')
    ].filter(Boolean);

    if (buttons.length < 2 || !buttons.includes(activeButton)) {
      return;
    }

    const otherButton = buttons.find(function (button) {
      return button !== activeButton;
    });
    const activeRect = activeButton.getBoundingClientRect();
    const otherRect = otherButton.getBoundingClientRect();
    const overlaps = activeRect.left < otherRect.right
      && activeRect.right > otherRect.left
      && activeRect.top < otherRect.bottom
      && activeRect.bottom > otherRect.top;

    if (!overlaps) {
      return;
    }

    const margin = 8;
    const maxLeft = Math.max(0, window.innerWidth - otherRect.width - margin);
    const maxTop = Math.max(0, window.innerHeight - otherRect.height - margin);
    const positions = [
      { left: margin, top: maxTop },
      { left: maxLeft, top: margin },
      { left: margin, top: margin },
      { left: maxLeft, top: maxTop }
    ];
    const availablePosition = positions.find(function (position) {
      const candidateRight = position.left + otherRect.width;
      const candidateBottom = position.top + otherRect.height;
      return !(position.left < activeRect.right
        && candidateRight > activeRect.left
        && position.top < activeRect.bottom
        && candidateBottom > activeRect.top);
    });

    if (!availablePosition) {
      return;
    }

    const positionElement = otherButton.id === 'aiChatToggle'
      ? document.querySelector('.ai-chat')
      : otherButton;
    positionElement.style.left = availablePosition.left + 'px';
    positionElement.style.top = availablePosition.top + 'px';
    positionElement.style.right = 'auto';
    positionElement.style.bottom = 'auto';

    const storageKey = savedPositionKeys[otherButton.id];
    if (storageKey) {
      try {
        window.localStorage.setItem(storageKey, JSON.stringify(availablePosition));
      } catch (error) {
        console.warn('Impossible d’enregistrer la position du bouton flottant.', error);
      }
    }
  };
})();
