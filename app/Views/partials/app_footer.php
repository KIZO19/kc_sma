    </div>
  </div>
  <?php
    $footerAppName = APP_NAME;
    if (!empty($user['role']) && $user['role'] !== 'super_admin') {
      $schoolName = null;
      if (!empty($user['ecole_id'])) {
        $school = \App\Models\Ecole::findById((int) $user['ecole_id']);
        if (!empty($school['nom_etablissement'])) {
          $schoolName = $school['nom_etablissement'];
        }
      }
      if (!empty($schoolName)) {
        $footerAppName = $schoolName;
      }
    }
  ?>
  <footer class="main-footer border-top py-3 bg-body-tertiary">
    <div class="container-fluid d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2">
      <span class="text-muted">&copy; <?= date('Y') ?> <?= htmlspecialchars($footerAppName) ?>. Tous droits réservés.</span>
      <span class="text-muted d-none d-sm-inline" aria-hidden="true">·</span>
      <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="modal" data-bs-target="#cookiePreferencesModal">
        Gérer mes cookies
      </button>
    </div>
  </footer>
  <?php if (!empty($user['id']) && !empty($user['ecole_id']) && !empty($_SESSION['location_consent_nonce'])): ?>
  <div class="modal fade" id="locationConsentModal" tabindex="-1" aria-labelledby="locationConsentTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="locationConsentTitle">Partager la localisation de cet appareil</h2>
        </div>
        <div class="modal-body">
          <p>Pour la sécurité et le suivi de connexion, le super administrateur souhaite connaître la position approximative de cet appareil. Si vous acceptez, la position partagée sera visible par le super administrateur et conservée avec sa date dans l’historique de votre compte. Le partage n’est demandé qu’à cette connexion et votre navigateur vous demandera également son autorisation. Cette demande fonctionne sur téléphone lorsque le site est ouvert dans un navigateur et utilise une connexion HTTPS sécurisée.</p>
          <p class="small text-muted mb-0">Votre position ne sera ni transmise ni enregistrée si vous refusez. Vous pouvez aussi refuser l’autorisation dans la demande de votre navigateur.</p>
          <div id="locationConsentStatus" class="small mt-3" role="status" aria-live="polite"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Ne pas partager</button>
          <button type="button" class="btn btn-primary" id="shareDeviceLocation">Partager ma position</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <div class="modal fade" id="cookiePreferencesModal" tabindex="-1" aria-labelledby="cookiePreferencesTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="cookiePreferencesTitle">Cookies et préférences</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <p>Voici les cookies utilisés par KC_SMA sur ce site. Les cookies nécessaires à la connexion restent actifs. Aucun cookie publicitaire ou de mesure d’audience n’a été identifié.</p>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th scope="col">Cookie</th>
                  <th scope="col">Utilité</th>
                  <th scope="col">Durée</th>
                  <th scope="col">État</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row"><?= htmlspecialchars(session_name()) ?></th>
                  <td>Connexion et sécurité de session</td>
                  <td>Session du navigateur</td>
                  <td><span class="badge text-bg-secondary">Toujours actif</span></td>
                </tr>
                <tr>
                  <th scope="row">adminkc_theme_mode</th>
                  <td>Mémoriser le thème choisi</td>
                  <td>1 an</td>
                  <td id="themeCookieStatus">Non enregistré</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="allowThemeCookie">
            <label class="form-check-label" for="allowThemeCookie">Mémoriser mon choix de thème sur cet appareil</label>
          </div>
          <p class="form-text mb-0 mt-2">Le cookie de session est indispensable au fonctionnement de votre compte. Sa valeur n’est pas affichée pour protéger votre connexion.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
          <button type="button" class="btn btn-primary" id="saveCookiePreferences">Enregistrer mes choix</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" crossorigin="anonymous"></script>
  <script src="<?= BASE_URL ?>/assets/adminlte.min.js"></script>
  <script src="<?= BASE_URL ?>/assets/app-datagrid.js"></script>
  <script src="<?= BASE_URL ?>/assets/live-notifications.js" defer></script>
  <script>
    const themeCookieName = 'adminkc_theme_mode';

    function getCookieValue(name) {
      const cookie = document.cookie.split('; ').find(item => item.startsWith(name + '='));
      return cookie ? cookie.slice(name.length + 1) : '';
    }

    function setThemeMode(mode, persist = true) {
      const root = document.documentElement;
      root.dataset.theme = mode;
      if (persist) {
        document.cookie = `${themeCookieName}=${mode}; path=/; max-age=${60 * 60 * 24 * 365}; SameSite=Lax`;
      }

      const themeIcon = document.getElementById('themeModeIcon');
      if (themeIcon) {
        themeIcon.className = mode === 'dark' ? 'bi bi-moon-fill' : mode === 'light' ? 'bi bi-sun' : 'bi bi-circle-half';
      }
    }

    function getThemeMode() {
      return getCookieValue(themeCookieName) || 'auto';
    }

    function applyThemeMode(mode, persist = true) {
      if (mode === 'auto') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        setThemeMode(prefersDark ? 'dark' : 'light', persist);
      } else {
        setThemeMode(mode, persist);
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      const searchToggle = document.getElementById('navbarSearchToggle');
      const searchForm = document.getElementById('navbarSearchForm');
      const searchClose = document.getElementById('navbarSearchClose');
      const themeButtons = document.querySelectorAll('[data-theme-mode]');
      const currentMode = getThemeMode();
      const themeCookieExists = Boolean(getCookieValue(themeCookieName));
      const allowThemeCookie = document.getElementById('allowThemeCookie');
      const themeCookieStatus = document.getElementById('themeCookieStatus');
      const saveCookiePreferences = document.getElementById('saveCookiePreferences');

      function updateThemeCookieStatus(isSaved) {
        if (themeCookieStatus) {
          themeCookieStatus.textContent = isSaved ? 'Enregistré' : 'Non enregistré (thème système)';
          themeCookieStatus.className = isSaved ? 'text-success' : 'text-muted';
        }
      }

      if (allowThemeCookie) {
        allowThemeCookie.checked = themeCookieExists;
      }
      updateThemeCookieStatus(themeCookieExists);

      if (saveCookiePreferences && allowThemeCookie) {
        saveCookiePreferences.addEventListener('click', function () {
          if (allowThemeCookie.checked) {
            applyThemeMode(getThemeMode(), true);
            updateThemeCookieStatus(true);
          } else {
            document.cookie = `${themeCookieName}=; path=/; max-age=0; SameSite=Lax`;
            applyThemeMode('auto', false);
            updateThemeCookieStatus(false);
          }
          bootstrap.Modal.getOrCreateInstance(document.getElementById('cookiePreferencesModal')).hide();
        });
      }

      const cookiePreferencesModal = document.getElementById('cookiePreferencesModal');
      if (cookiePreferencesModal && allowThemeCookie) {
        cookiePreferencesModal.addEventListener('show.bs.modal', function () {
          const hasThemeCookie = Boolean(getCookieValue(themeCookieName));
          allowThemeCookie.checked = hasThemeCookie;
          updateThemeCookieStatus(hasThemeCookie);
        });
      }

      if (cookiePreferencesModal) {
        const cookieNoticeSeenKey = 'adminkc_cookie_notice_seen_v1';
        cookiePreferencesModal.addEventListener('hidden.bs.modal', function () {
          try {
            localStorage.setItem(cookieNoticeSeenKey, '1');
          } catch (error) {
            console.error('Impossible d’enregistrer la fermeture de la notification des cookies.', error);
          }
        });

        let shouldShowCookieNotice = false;
        try {
          shouldShowCookieNotice = localStorage.getItem(cookieNoticeSeenKey) !== '1';
        } catch (error) {
          console.error('Impossible de vérifier si la notification des cookies a déjà été affichée.', error);
          shouldShowCookieNotice = true;
        }

        if (shouldShowCookieNotice) {
          bootstrap.Modal.getOrCreateInstance(cookiePreferencesModal).show();
        }
      }

      if (searchToggle && searchForm) {
        searchToggle.addEventListener('click', function (event) {
          event.preventDefault();
          searchForm.classList.toggle('d-none');
          if (!searchForm.classList.contains('d-none')) {
            searchForm.querySelector('input[type="search"]')?.focus();
          }
        });

        // Auto-collapse sidebar on small screens for better UX
        function updateSidebarForViewport() {
          const body = document.body;
          if (window.innerWidth < 768) {
            body.classList.add('sidebar-collapse');
          } else {
            body.classList.remove('sidebar-collapse');
          }
        }

        updateSidebarForViewport();
        window.addEventListener('resize', function () {
          updateSidebarForViewport();
        });

        // Ensure sidebar not collapsed on larger screens (override persisted AdminLTE state)
        if (window.innerWidth >= 768) {
          document.body.classList.remove('sidebar-collapse');
        }

      }

      const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
      const appSidebar = document.querySelector('.app-sidebar');
      const sidebarBackdrop = document.getElementById('sidebarBackdrop');

      function syncSidebarState(isOpen) {
        const isMobile = window.innerWidth < 768;
        if (isMobile) {
          document.body.classList.toggle('sidebar-collapse', !isOpen);
        }
        if (sidebarBackdrop) {
          sidebarBackdrop.style.display = isOpen && isMobile ? 'block' : 'none';
        }
        if (sidebarToggle) {
          sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
        document.body.classList.toggle('sidebar-open', isOpen && isMobile);
      }

      if (sidebarToggle && appSidebar) {
        sidebarToggle.addEventListener('click', function () {
          if (window.innerWidth < 768) {
            syncSidebarState(!document.body.classList.contains('sidebar-open'));
          } else {
            document.body.classList.toggle('sidebar-collapse');
            syncSidebarState(false);
          }
        });
      }

      if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
          if (sidebarToggle) {
            sidebarToggle.click();
          } else {
            syncSidebarState(false);
          }
        });
      }

      window.addEventListener('resize', function () {
        if (window.innerWidth >= 768) {
          syncSidebarState(false);
        } else {
          document.body.classList.add('sidebar-collapse');
          syncSidebarState(false);
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
          syncSidebarState(false);
        }
      });

      if (searchClose && searchForm) {
        searchClose.addEventListener('click', function () {
          searchForm.classList.add('d-none');
        });
      }

      const sidebarSearchInput = document.getElementById('sidebarSearchInput');
      const sidebarSearchButton = document.getElementById('sidebarSearchButton');
      const sidebarNavMenu = document.getElementById('sidebarNavMenu');
      const sidebarSearchEmptyState = document.getElementById('sidebarSearchEmptyState');

      function applySidebarSearch() {
        if (!sidebarNavMenu || !sidebarSearchInput) return;

        const query = sidebarSearchInput.value.trim().toLowerCase();
        const items = sidebarNavMenu.querySelectorAll('li.nav-item, li.nav-header');
        let visibleCount = 0;

        items.forEach((item) => {
          const text = (item.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
          const shouldShow = !query || text.includes(query);

          if (shouldShow) {
            item.style.display = '';
            visibleCount += 1;
          } else {
            item.style.display = 'none';
          }
        });

        if (sidebarSearchEmptyState) {
          sidebarSearchEmptyState.classList.toggle('d-none', visibleCount > 0 || !query);
        }
      }

      if (sidebarSearchInput) {
        sidebarSearchInput.addEventListener('input', applySidebarSearch);
        sidebarSearchInput.addEventListener('keyup', function (event) {
          if (event.key === 'Enter') {
            event.preventDefault();
            applySidebarSearch();
          }
        });
      }

      if (sidebarSearchButton) {
        sidebarSearchButton.addEventListener('click', function (event) {
          event.preventDefault();
          applySidebarSearch();
        });
      }

      applySidebarSearch();

      if (themeButtons.length > 0) {
        themeButtons.forEach(button => {
          button.addEventListener('click', function () {
            const mode = this.dataset.themeMode;
            applyThemeMode(mode);
          });
        });
      }

      applyThemeMode(currentMode, themeCookieExists);

      // Sidebar color picker
      const sidebarColorKey = 'adminkc_sidebar_color';
      const sidebarColorInput = document.getElementById('sidebarColorInput');
      const sidebarColorReset = document.getElementById('sidebarColorReset');
      const root = document.documentElement;

      function applySidebarColor(c) {
        if (c) root.style.setProperty('--sidebar', c);
        else root.style.removeProperty('--sidebar');
      }

      if (sidebarColorInput) {
        const saved = localStorage.getItem(sidebarColorKey);
        if (saved) {
          sidebarColorInput.value = saved;
          applySidebarColor(saved);
        }
        sidebarColorInput.addEventListener('input', function () {
          const v = this.value;
          localStorage.setItem(sidebarColorKey, v);
          applySidebarColor(v);
        });
      }

      if (sidebarColorReset) {
        sidebarColorReset.addEventListener('click', function (e) {
          e.preventDefault();
          localStorage.removeItem(sidebarColorKey);
          sidebarColorInput.value = '#172230';
          applySidebarColor(null);
        });
      }

      // Client-side validation for image inputs (avatar & logo)
      function validateImageFile(input) {
        if (!input || !input.files || input.files.length === 0) return true;
        const file = input.files[0];
        const maxBytes = 250 * 1024;
        const allowedTypes = ['image/png', 'image/jpeg'];
        if (!allowedTypes.includes(file.type)) {
          return { ok: false, error: 'Seuls les formats PNG et JPEG sont autorisés.' };
        }
        if (file.size > maxBytes) {
          return { ok: false, error: 'La taille maximale autorisée est 250 KB.' };
        }
        return { ok: true };
      }

      // Profile avatar input
      const avatarInput = document.querySelector('input[name="avatar"]');
      if (avatarInput) {
        avatarInput.addEventListener('change', function () {
          const res = validateImageFile(this);
          if (!res.ok) {
            alert(res.error);
            this.value = '';
          }
        });
      }

      // School logo input on ecools page (if present)
      const logoInput = document.querySelector('input[name="logo"]');
      if (logoInput) {
        logoInput.addEventListener('change', function () {
          const res = validateImageFile(this);
          if (!res.ok) {
            alert(res.error);
            this.value = '';
          }
        });
      }

      // Student photo input on inscriptions page
      const photoInput = document.querySelector('input[name="photo"]');
      if (photoInput) {
        photoInput.addEventListener('change', function () {
          const res = validateImageFile(this);
          if (!res.ok) {
            alert(res.error);
            this.value = '';
          }
        });
      }
    });
  </script>
  <?php if (!empty($user['id'])): ?>
  <script>
    (function () {
      const sendPresenceHeartbeat = function (recordPageVisit) {
        if (document.visibilityState !== 'visible') {
          return;
        }

        const payload = recordPageVisit
          ? {
              page_visit: true,
              path: window.location.pathname,
              title: document.title
            }
          : {};

        fetch('<?= BASE_URL ?>/dashboard/presence', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
          keepalive: true
        }).then(function (response) {
          if (!response.ok) {
            throw new Error('HTTP ' + response.status);
          }
        }).catch(function (error) {
          console.error('Impossible d’actualiser la présence de la session.', error);
        });
      };

      sendPresenceHeartbeat(true);
      window.setInterval(sendPresenceHeartbeat, 15000);
      document.addEventListener('visibilitychange', sendPresenceHeartbeat);
    })();
  </script>
  <?php endif; ?>
  <?php if (!empty($user['id']) && !empty($user['ecole_id']) && !empty($_SESSION['location_consent_nonce'])): ?>
  <script>
    (function () {
      const sessionKey = 'adminkc_location_consent_session';
      const sessionNonce = <?= json_encode((string) $_SESSION['location_consent_nonce'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
      const modalElement = document.getElementById('locationConsentModal');
      const shareButton = document.getElementById('shareDeviceLocation');
      const status = document.getElementById('locationConsentStatus');
      let alreadyAsked = false;

      if (!window.isSecureContext) {
        status.textContent = 'La localisation mobile nécessite une connexion HTTPS sécurisée. Ouvrez le site avec son adresse HTTPS pour pouvoir partager votre position.';
        status.className = 'small mt-3 text-warning';
        shareButton.disabled = true;
      } else if (!navigator.geolocation) {
        status.textContent = 'La géolocalisation n’est pas disponible dans ce navigateur. Essayez un navigateur mobile récent.';
        status.className = 'small mt-3 text-warning';
        shareButton.disabled = true;
      }

      try {
        alreadyAsked = sessionStorage.getItem(sessionKey) === sessionNonce;
      } catch (error) {
        console.error('Impossible de vérifier la demande de localisation pour cette connexion.', error);
      }

      if (!alreadyAsked) {
        try {
          sessionStorage.setItem(sessionKey, sessionNonce);
        } catch (error) {
          console.error('Impossible de mémoriser la demande de localisation pour cette connexion.', error);
        }
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
      }

      shareButton.addEventListener('click', function () {
        if (!window.isSecureContext) {
          status.textContent = 'La localisation mobile nécessite une connexion HTTPS sécurisée. Ouvrez le site avec son adresse HTTPS pour pouvoir partager votre position.';
          status.className = 'small mt-3 text-danger';
          return;
        }
        if (!navigator.geolocation) {
          status.textContent = 'La géolocalisation n’est pas disponible dans ce navigateur.';
          status.className = 'small mt-3 text-danger';
          return;
        }

        shareButton.disabled = true;
        status.textContent = 'En attente de votre autorisation et de la position de l’appareil…';
        status.className = 'small mt-3 text-muted';
        navigator.geolocation.getCurrentPosition(function (position) {
          fetch('<?= BASE_URL ?>/dashboard/presence', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                location_nonce: sessionNonce,
                location: {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
              }
            }),
            keepalive: true
          }).then(function (response) {
            if (!response.ok) {
              throw new Error('HTTP ' + response.status);
            }
            status.textContent = 'Votre position a été partagée avec le super administrateur.';
            status.className = 'small mt-3 text-success';
            shareButton.classList.add('d-none');
            window.setTimeout(function () {
              bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            }, 1200);
          }).catch(function (error) {
            console.error('Impossible d’envoyer la position partagée.', error);
            status.textContent = 'La position n’a pas pu être envoyée. Veuillez réessayer.';
            status.className = 'small mt-3 text-danger';
            shareButton.disabled = false;
          });
        }, function (error) {
          const message = error.code === error.PERMISSION_DENIED
            ? 'Autorisation refusée. Votre position n’a pas été partagée.'
            : error.code === error.POSITION_UNAVAILABLE
              ? 'La position de l’appareil est indisponible.'
              : 'La demande de localisation a expiré. Veuillez réessayer.';
          status.textContent = message;
          status.className = 'small mt-3 text-danger';
          shareButton.disabled = false;
        }, { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 });
      });
    })();
  </script>
  <?php endif; ?>
  <?= $pageScripts ?? '' ?>
  <script src="<?= BASE_URL ?>/assets/pwa-install.js" defer></script>
</body>
</html>
