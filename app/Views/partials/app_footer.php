    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" crossorigin="anonymous"></script>
  <script src="<?= BASE_URL ?>/assets/adminlte.min.js"></script>
  <script>
    const themeCookieName = 'adminkc_theme_mode';

    function setThemeMode(mode) {
      const root = document.documentElement;
      root.dataset.theme = mode;
      document.cookie = `${themeCookieName}=${mode}; path=/; max-age=${60 * 60 * 24 * 365}`;

      const themeIcon = document.getElementById('themeModeIcon');
      if (themeIcon) {
        themeIcon.className = mode === 'dark' ? 'bi bi-moon-fill' : mode === 'light' ? 'bi bi-sun' : 'bi bi-circle-half';
      }
    }

    function getThemeMode() {
      const match = document.cookie.match(new RegExp('(^| )' + themeCookieName + '=([^;]+)'));
      return match ? match[2] : 'auto';
    }

    function applyThemeMode(mode) {
      if (mode === 'auto') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        setThemeMode(prefersDark ? 'dark' : 'light');
      } else {
        setThemeMode(mode);
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      const searchToggle = document.getElementById('navbarSearchToggle');
      const searchForm = document.getElementById('navbarSearchForm');
      const searchClose = document.getElementById('navbarSearchClose');
      const themeButtons = document.querySelectorAll('[data-theme-mode]');
      const currentMode = getThemeMode();

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

        // Sidebar toggle: mobile overlay behavior
        const sidebarToggle = document.querySelector('[data-lte-toggle="sidebar"]');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        if (sidebarToggle) {
          sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (window.innerWidth < 768) {
              document.body.classList.toggle('sidebar-open');
              if (sidebarBackdrop) sidebarBackdrop.style.display = document.body.classList.contains('sidebar-open') ? 'block' : 'none';
            } else {
              document.body.classList.toggle('sidebar-collapse');
            }
          });
        }

        if (sidebarBackdrop) {
          sidebarBackdrop.addEventListener('click', function () {
            document.body.classList.remove('sidebar-open');
            this.style.display = 'none';
          });
        }

        // Close mobile sidebar on larger resize
        window.addEventListener('resize', function () {
          if (window.innerWidth >= 768) {
            document.body.classList.remove('sidebar-open');
            if (sidebarBackdrop) sidebarBackdrop.style.display = 'none';
          }
        });
      }

      if (searchClose && searchForm) {
        searchClose.addEventListener('click', function () {
          searchForm.classList.add('d-none');
        });
      }

      if (themeButtons.length > 0) {
        themeButtons.forEach(button => {
          button.addEventListener('click', function () {
            const mode = this.dataset.themeMode;
            applyThemeMode(mode);
          });
        });
      }

      if (currentMode) {
        applyThemeMode(currentMode);
      }

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
    });
  </script>
  <?= $pageScripts ?? '' ?>
</body>
</html>
