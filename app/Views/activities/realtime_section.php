<section class="content-header" id="real-time">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-12"><h2 class="h4 mb-0">Activité en temps réel</h2></div>
    </div>
    <p class="text-muted">Les données se mettent à jour automatiquement toutes les 10 secondes. Un utilisateur est considéré en ligne si son activité remonte à moins d’une minute. À chaque connexion, l’utilisateur est informé et peut accepter ou refuser le partage de sa position. La position actuelle n’est visible que si l’appareil est en ligne et que l’utilisateur l’a partagée. Cliquez sur le nom d’un utilisateur pour consulter ses positions partagées et ses pages visitées.</p>
    <div id="presenceError" class="alert alert-danger d-none" role="alert"></div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row g-3 mb-4">
      <div class="col-sm-6">
        <div class="card h-100 mb-0">
          <div class="card-body">
            <div class="text-muted small">Écoles actuellement connectées</div>
            <div class="h2 mb-0" id="connectedSchoolCount">—</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="card h-100 mb-0">
          <div class="card-body">
            <div class="text-muted small">Utilisateurs en ligne</div>
            <div class="h2 mb-0" id="connectedUserCount">—</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <h2 class="h6 fw-bold mb-1">Écoles actuellement connectées</h2>
        <span class="small text-muted">Écoles avec au moins un utilisateur actif durant la dernière minute.</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-3">École</th>
                <th>Utilisateurs actifs</th>
                <th>Dernière activité</th>
                <th>État</th>
              </tr>
            </thead>
            <tbody id="connectedSchoolsTable">
              <tr><td colspan="4" class="text-center text-muted py-4">Chargement des écoles…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
          <h2 class="h6 fw-bold mb-1">Utilisateurs ayant accédé à leur tableau de bord</h2>
          <span class="small text-muted">Les 100 derniers utilisateurs suivis, avec leur dernière connexion et activité.</span>
        </div>
        <span class="small text-muted">Mise à jour : <time id="presenceUpdatedAt">en attente</time></span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-3">Utilisateur</th>
                <th>École</th>
                <th>Rôle</th>
                <th>Dernière connexion</th>
                <th>Accès au tableau de bord</th>
                <th>Localisation partagée</th>
                <th>Présence</th>
              </tr>
            </thead>
            <tbody id="presenceUsersTable">
              <tr><td colspan="7" class="text-center text-muted py-4">Chargement des utilisateurs…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  (function () {
    const presenceEndpoint = <?= json_encode(BASE_URL . '/dashboard/presence', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const schoolCount = document.getElementById('connectedSchoolCount');
    const userCount = document.getElementById('connectedUserCount');
    const schoolsTable = document.getElementById('connectedSchoolsTable');
    const usersTable = document.getElementById('presenceUsersTable');
    const updatedAt = document.getElementById('presenceUpdatedAt');
    const errorBanner = document.getElementById('presenceError');
    const defaultSchoolLogo = <?= json_encode(BASE_URL . '/assets/kc-logo.svg', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const roleLabels = {
      super_admin: 'Super administrateur',
      ecole_admin: 'Administrateur école',
      'préfet_école': 'Préfet des études',
      'DE_école': 'Directeur des études',
      'DD_école': 'Directeur département',
      'DP_école': 'Directeur principal',
      'DA_école': 'Directeur adjoint',
      'comptable_école': 'Comptable',
      'sec_école': 'Secrétaire',
      'promoteur_école': 'Promoteur',
      'enseignant_école': 'Enseignant',
      'eleve_école': 'Élève',
      'parent_école': 'Parent',
      'agent_école': 'Agent'
    };
    let isRefreshing = false;

    function addCell(row, value, className) {
      const cell = document.createElement('td');
      if (className) {
        cell.className = className;
      }
      cell.textContent = value;
      row.appendChild(cell);
      return cell;
    }

    function formatDate(value) {
      if (!value) {
        return '—';
      }
      const date = new Date(String(value).replace(' ', 'T'));
      return Number.isNaN(date.getTime()) ? '—' : date.toLocaleString('fr-FR');
    }

    function showEmptyRow(table, columnCount, message) {
      const row = document.createElement('tr');
      const cell = addCell(row, message, 'text-center text-muted py-4');
      cell.colSpan = columnCount;
      if (table === schoolsTable) {
        const logo = document.createElement('img');
        logo.src = defaultSchoolLogo;
        logo.alt = 'Logo KC_SMA';
        logo.width = 44;
        logo.height = 44;
        logo.className = 'd-block mx-auto mb-2';
        cell.prepend(logo);
      }
      table.replaceChildren(row);
    }

    function schoolLogoUrl(value) {
      if (typeof value !== 'string' || value.trim() === '') {
        return defaultSchoolLogo;
      }
      if (/^https?:\/\//i.test(value)) {
        return value;
      }
      const path = value.replace(/^\/+/, '');
      return <?= json_encode(BASE_URL . '/', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?> + path;
    }

    function appendSchoolIdentity(cell, logoUrl, schoolName) {
      const wrapper = document.createElement('div');
      wrapper.className = 'd-flex align-items-center gap-2';
      const logo = document.createElement('img');
      logo.src = schoolLogoUrl(logoUrl);
      logo.alt = schoolName ? 'Logo ' + schoolName : 'Logo KC_SMA';
      logo.width = 36;
      logo.height = 36;
      logo.className = 'rounded-circle flex-shrink-0';
      logo.style.objectFit = 'cover';
      logo.addEventListener('error', function () {
        logo.src = defaultSchoolLogo;
      }, { once: true });
      const name = document.createElement('span');
      name.className = 'fw-semibold';
      name.textContent = schoolName || 'Aucune école associée';
      wrapper.append(logo, name);
      cell.appendChild(wrapper);
    }

    function renderSchools(schools) {
      if (!schools.length) {
        showEmptyRow(schoolsTable, 4, 'Aucune école connectée pour le moment.');
        return;
      }

      const rows = document.createDocumentFragment();
      schools.forEach(function (school) {
        const row = document.createElement('tr');
        const schoolCell = addCell(row, '', 'ps-3');
        appendSchoolIdentity(schoolCell, school.logo_url, school.nom_etablissement || 'École sans nom');
        addCell(row, String(Number(school.connected_users) || 0));
        addCell(row, formatDate(school.last_seen_at));
        const status = addCell(row, '');
        const badge = document.createElement('span');
        badge.className = 'badge text-bg-success';
        badge.textContent = 'En ligne';
        status.appendChild(badge);
        rows.appendChild(row);
      });
      schoolsTable.replaceChildren(rows);
    }

    function renderUsers(users) {
      if (!users.length) {
        showEmptyRow(usersTable, 7, 'Aucun accès enregistré depuis l’activation du suivi. Demandez à un utilisateur d’école de se reconnecter et d’ouvrir son tableau de bord.');
        return;
      }

      const rows = document.createDocumentFragment();
      users.forEach(function (user) {
        const row = document.createElement('tr');
        const nameCell = addCell(row, '', 'ps-3 fw-semibold');
        const historyLink = document.createElement('a');
        historyLink.href = <?= json_encode(BASE_URL . '/dashboard/userHistory?id=', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?> + encodeURIComponent(String(user.user_id));
        historyLink.textContent = user.nom_complet || '—';
        nameCell.appendChild(historyLink);
        const schoolCell = addCell(row, '');
        appendSchoolIdentity(schoolCell, user.school_logo_url, user.school_name);
        addCell(row, roleLabels[user.role] || user.role || '—');
        addCell(row, formatDate(user.last_login_at));
        addCell(row, formatDate(user.dashboard_accessed_at));
        const location = addCell(row, '');
        if (Number(user.is_online) === 1 && user.latitude !== null && user.longitude !== null && Number.isFinite(Number(user.latitude)) && Number.isFinite(Number(user.longitude))) {
          const latitude = Number(user.latitude);
          const longitude = Number(user.longitude);
          const mapLink = document.createElement('a');
          mapLink.href = 'https://www.openstreetmap.org/?mlat=' + encodeURIComponent(String(latitude)) + '&mlon=' + encodeURIComponent(String(longitude)) + '#map=16/' + encodeURIComponent(String(latitude)) + '/' + encodeURIComponent(String(longitude));
          mapLink.target = '_blank';
          mapLink.rel = 'noopener noreferrer';
          mapLink.textContent = 'Voir la position';
          location.appendChild(mapLink);
          const accuracy = document.createElement('div');
          accuracy.className = 'small text-muted';
          accuracy.textContent = 'Précision ±' + Math.round(Number(user.accuracy_meters) || 0) + ' m · ' + formatDate(user.location_shared_at);
          location.appendChild(accuracy);
        } else {
          location.textContent = 'Non partagée';
        }
        const status = addCell(row, '');
        const badge = document.createElement('span');
        const isOnline = Number(user.is_online) === 1;
        badge.className = isOnline ? 'badge text-bg-success' : 'badge text-bg-secondary';
        badge.textContent = isOnline ? 'En ligne' : 'Hors ligne';
        status.appendChild(badge);
        rows.appendChild(row);
      });
      usersTable.replaceChildren(rows);
    }

    async function refreshPresence() {
      if (document.visibilityState !== 'visible' || isRefreshing) {
        return;
      }

      isRefreshing = true;
      try {
        const response = await fetch(presenceEndpoint, {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
          cache: 'no-store'
        });
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        const data = await response.json();
        const schools = Array.isArray(data.connectedSchools) ? data.connectedSchools : [];
        const users = Array.isArray(data.users) ? data.users : [];
        schoolCount.textContent = String(data.connectedSchoolCount ?? schools.length);
        userCount.textContent = String(data.connectedUserCount ?? 0);
        renderSchools(schools);
        renderUsers(users);
        updatedAt.textContent = new Date().toLocaleTimeString('fr-FR');
        errorBanner.classList.add('d-none');
        errorBanner.textContent = '';
      } catch (error) {
        console.error('Impossible de charger le flux de présence.', error);
        errorBanner.textContent = 'Le flux de présence est momentanément indisponible. Une nouvelle tentative sera effectuée automatiquement.';
        errorBanner.classList.remove('d-none');
      } finally {
        isRefreshing = false;
      }
    }

    refreshPresence();
    window.setInterval(refreshPresence, 10000);
    document.addEventListener('visibilitychange', refreshPresence);
  })();
</script>
