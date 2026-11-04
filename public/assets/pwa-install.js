(function () {
  let installPrompt = null;
  let previousFocus = null;
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

  if (isStandalone || document.getElementById('pwaInstallButton')) {
    return;
  }

  const installStyle = document.createElement('style');
  installStyle.textContent = [
    '#pwaInstallButton { position:fixed !important; right:1rem; bottom:calc(1rem + env(safe-area-inset-bottom)); z-index:1100; display:inline-flex; align-items:center; border-radius:999px; padding:.75rem 1.1rem; box-shadow:0 .35rem 1rem rgba(0,0,0,.3); cursor:grab; touch-action:none; user-select:none; }',
    '#pwaInstallButton.pwa-install-dragging { cursor:grabbing; }',
    '#pwaInstallOverlay { position:fixed; inset:0; z-index:1200; display:flex; align-items:center; justify-content:center; padding:1rem; background:rgba(15,23,42,.6); }',
    '#pwaInstallOverlay[hidden] { display:none !important; }',
    '#pwaInstallDialog { width:min(100%, 28rem); max-height:calc(100vh - 2rem); overflow:auto; padding:1.25rem; border-radius:1rem; background:#fff; color:#1f2937; box-shadow:0 1rem 3rem rgba(0,0,0,.25); }',
    '#pwaInstallDialog h2 { margin:0 2.5rem .75rem 0; font-size:1.25rem; }',
    '#pwaInstallDialog p { margin:0 0 .75rem; line-height:1.5; }',
    '#pwaInstallClose { float:right; }',
    '@media (max-width:480px) { #pwaInstallButton { right:.75rem; bottom:calc(.75rem + env(safe-area-inset-bottom)); padding:.7rem 1rem; } #pwaInstallDialog { padding:1rem; } }'
  ].join('\n');
  document.head.appendChild(installStyle);

  const installButton = document.createElement('button');
  installButton.id = 'pwaInstallButton';
  installButton.type = 'button';
  installButton.className = 'btn btn-primary';
  installButton.innerHTML = '<i class="bi bi-download me-1" aria-hidden="true"></i>Installer l’application';
  installButton.setAttribute('aria-label', 'Installer KC_SMA sur cet appareil');
  installButton.setAttribute('aria-controls', 'pwaInstallOverlay');
  document.body.appendChild(installButton);

  let dragState = null;
  let ignoreNextClick = false;
  const savedPositionKey = 'kc_sma_pwa_install_position';

  function clampButtonPosition(left, top) {
    const maxLeft = Math.max(0, window.innerWidth - installButton.offsetWidth);
    const maxTop = Math.max(0, window.innerHeight - installButton.offsetHeight);
    return {
      left: Math.min(Math.max(0, left), maxLeft),
      top: Math.min(Math.max(0, top), maxTop)
    };
  }

  function setButtonPosition(left, top) {
    const position = clampButtonPosition(left, top);
    installButton.style.left = position.left + 'px';
    installButton.style.top = position.top + 'px';
    installButton.style.right = 'auto';
    installButton.style.bottom = 'auto';
    return position;
  }

  function saveButtonPosition(position) {
    try {
      window.localStorage.setItem(savedPositionKey, JSON.stringify(position));
    } catch (error) {
      console.warn('Unable to save the app install button position:', error);
    }
  }

  try {
    const savedPosition = JSON.parse(window.localStorage.getItem(savedPositionKey) || 'null');
    if (
      savedPosition
      && Number.isFinite(savedPosition.left)
      && Number.isFinite(savedPosition.top)
    ) {
      setButtonPosition(savedPosition.left, savedPosition.top);
    }
  } catch (error) {
    console.warn('Unable to restore the app install button position:', error);
  }

  installButton.addEventListener('pointerdown', function (event) {
    if (event.button !== undefined && event.button !== 0) {
      return;
    }

    const rect = installButton.getBoundingClientRect();
    dragState = {
      pointerId: event.pointerId,
      startX: event.clientX,
      startY: event.clientY,
      left: rect.left,
      top: rect.top,
      moved: false
    };
    installButton.setPointerCapture(event.pointerId);
  });

  installButton.addEventListener('pointermove', function (event) {
    if (!dragState || dragState.pointerId !== event.pointerId) {
      return;
    }

    const deltaX = event.clientX - dragState.startX;
    const deltaY = event.clientY - dragState.startY;
    if (!dragState.moved && Math.hypot(deltaX, deltaY) < 5) {
      return;
    }

    dragState.moved = true;
    installButton.classList.add('pwa-install-dragging');
    event.preventDefault();
    setButtonPosition(dragState.left + deltaX, dragState.top + deltaY);
  });

  function finishButtonDrag(event, suppressClick) {
    if (!dragState || dragState.pointerId !== event.pointerId) {
      return;
    }

    if (dragState.moved) {
      ignoreNextClick = suppressClick;
      const rect = installButton.getBoundingClientRect();
      saveButtonPosition({ left: rect.left, top: rect.top });
    }
    dragState = null;
    installButton.classList.remove('pwa-install-dragging');
  }

  installButton.addEventListener('pointerup', function (event) {
    finishButtonDrag(event, true);
  });
  installButton.addEventListener('pointercancel', function (event) {
    finishButtonDrag(event, false);
  });

  window.addEventListener('resize', function () {
    if (!installButton.style.left || !installButton.style.top) {
      return;
    }
    const position = setButtonPosition(
      Number.parseFloat(installButton.style.left),
      Number.parseFloat(installButton.style.top)
    );
    saveButtonPosition(position);
  });

  const overlay = document.createElement('div');
  overlay.id = 'pwaInstallOverlay';
  overlay.hidden = true;
  overlay.innerHTML = [
    '<section id="pwaInstallDialog" role="dialog" aria-modal="true" aria-labelledby="pwaInstallTitle" aria-describedby="pwaInstallInstructions" tabindex="-1">',
    '<button id="pwaInstallClose" type="button" class="btn-close" aria-label="Fermer"></button>',
    '<h2 id="pwaInstallTitle">Installer KC_SMA</h2>',
    '<div id="pwaInstallInstructions"></div>',
    '<div class="text-end mt-3"><button id="pwaInstallDone" type="button" class="btn btn-primary">Compris</button></div>',
    '</section>'
  ].join('');
  document.body.appendChild(overlay);

  const dialog = overlay.querySelector('#pwaInstallDialog');
  const instructions = overlay.querySelector('#pwaInstallInstructions');

  function closeInstructions() {
    overlay.hidden = true;
    document.body.style.overflow = '';
    if (previousFocus && typeof previousFocus.focus === 'function') {
      previousFocus.focus();
    }
  }

  function showInstructions(message) {
    instructions.replaceChildren();
    message.forEach(function (line) {
      const paragraph = document.createElement('p');
      paragraph.textContent = line;
      instructions.appendChild(paragraph);
    });

    previousFocus = document.activeElement;
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
    dialog.focus();
  }

  function getManualInstallInstructions() {
    const userAgent = window.navigator.userAgent || '';
    const isIos = /iPad|iPhone|iPod/.test(userAgent)
      || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);
    const isAndroid = /Android/i.test(userAgent);

    if (isIos) {
      return [
        'Sur iPhone ou iPad, l’installation se fait depuis le menu de partage du navigateur.',
        'Dans Safari, touchez Partager (le carré avec la flèche vers le haut), puis « Sur l’écran d’accueil » ou « Ajouter à l’écran d’accueil », et confirmez avec « Ajouter ».',
        'Dans Chrome sur iPhone/iPad, ouvrez Partager, choisissez « Ajouter à l’écran d’accueil », puis confirmez.'
      ];
    }

    if (isAndroid) {
      return [
        'Si la fenêtre d’installation ne s’est pas ouverte, ouvrez le menu ⋮ du navigateur.',
        'Choisissez « Installer l’application » ou « Ajouter à l’écran d’accueil », puis confirmez.'
      ];
    }

    return [
      'Ouvrez le menu de votre navigateur et choisissez « Installer l’application » ou « Créer un raccourci ».',
      'Sur un téléphone, l’installation est proposée dans le menu de partage (iPhone/iPad) ou dans le menu ⋮ (Android).'
    ];
  }

  function showInstallInstructions() {
    if (!window.isSecureContext) {
      showInstructions([
        'L’installation nécessite une connexion sécurisée HTTPS.',
        'Ouvrez KC_SMA avec son adresse HTTPS sur votre téléphone, puis réessayez. Une adresse localhost ne fonctionne que sur l’appareil qui héberge le site.'
      ]);
      return;
    }

    showInstructions(getManualInstallInstructions());
  }

  installButton.addEventListener('click', async function () {
    if (ignoreNextClick) {
      ignoreNextClick = false;
      return;
    }

    if (!installPrompt) {
      showInstallInstructions();
      return;
    }

    const prompt = installPrompt;
    installPrompt = null;
    installButton.disabled = true;

    try {
      await prompt.prompt();
      const choice = await prompt.userChoice;
      if (choice.outcome === 'accepted') {
        installButton.remove();
      } else {
        showInstallInstructions();
      }
    } catch (error) {
      console.error('Unable to prompt the browser to install the app:', error);
      showInstallInstructions();
    } finally {
      installButton.disabled = false;
    }
  });

  overlay.addEventListener('click', function (event) {
    if (event.target === overlay || event.target.id === 'pwaInstallClose' || event.target.id === 'pwaInstallDone') {
      closeInstructions();
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !overlay.hidden) {
      closeInstructions();
    }
  });

  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    installPrompt = event;
  });

  window.addEventListener('appinstalled', function () {
    installPrompt = null;
    installButton.remove();
    closeInstructions();
  });

  if ('serviceWorker' in navigator) {
    const manifestLink = document.querySelector('link[rel="manifest"]');
    if (manifestLink) {
      const manifestUrl = new URL(manifestLink.href);
      const appScope = new URL('../../', manifestUrl);
      navigator.serviceWorker.register(new URL('../service-worker.js', manifestUrl), {
        scope: appScope.pathname
      }).catch(function (error) {
        console.error('Unable to register the app service worker:', error);
      });
    }
  }
})();
