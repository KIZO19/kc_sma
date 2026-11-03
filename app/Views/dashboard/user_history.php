<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1>Historique utilisateur</h1>
      </div>
      <div class="col-sm-4">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/activities#real-time">Activité</a></li>
          <li class="breadcrumb-item active">Historique utilisateur</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (empty($history['user'])): ?>
      <div class="alert alert-warning">Utilisateur introuvable. <a href="<?= BASE_URL ?>/activities#real-time">Retourner à la section Activité</a>.</div>
    <?php else: ?>
      <?php
        $trackedUser = $history['user'];
        $lastLoginAt = strtotime((string) ($trackedUser['last_login_at'] ?? ''));
        $dashboardAccessAt = strtotime((string) ($trackedUser['dashboard_accessed_at'] ?? ''));
      ?>
      <div class="card mb-4">
        <div class="card-body">
          <h2 class="h4 mb-2"><?= htmlspecialchars($trackedUser['nom_complet'] ?? '') ?></h2>
          <div class="row g-3">
            <div class="col-md-4"><span class="text-muted">École :</span> <?= htmlspecialchars($trackedUser['school_name'] ?? '—') ?></div>
            <div class="col-md-4"><span class="text-muted">Rôle :</span> <?= htmlspecialchars(\App\Models\User::getRoleLabel((string) ($trackedUser['role'] ?? ''))) ?></div>
            <div class="col-md-4"><span class="text-muted">Dernière connexion :</span> <span id="lastLoginAt"><?= $lastLoginAt !== false ? htmlspecialchars(date('d/m/Y H:i:s', $lastLoginAt)) : 'Non enregistrée' ?></span></div>
            <div class="col-md-4"><span class="text-muted">Dernier accès au tableau de bord :</span> <span id="dashboardAccessAt"><?= $dashboardAccessAt !== false ? htmlspecialchars(date('d/m/Y H:i:s', $dashboardAccessAt)) : 'Non enregistré' ?></span></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2 class="h6 fw-bold mb-1">Historique des positions partagées</h2>
          <span class="small text-muted">Les 500 derniers partages volontaires, enregistrés à partir de l’activation de cette fonctionnalité. Actualisé automatiquement : <time id="locationsUpdatedAt">en attente</time></span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-3">Date et heure</th>
                  <th>Coordonnées</th>
                  <th>Précision</th>
                  <th>Carte</th>
                </tr>
              </thead>
              <tbody id="userLocationHistory">
                <?php foreach ($history['locations'] as $location): ?>
                  <?php
                    $sharedAt = strtotime((string) ($location['shared_at'] ?? ''));
                    $latitude = (float) ($location['latitude'] ?? 0);
                    $longitude = (float) ($location['longitude'] ?? 0);
                    $mapUrl = 'https://www.openstreetmap.org/?mlat=' . rawurlencode((string) $latitude)
                        . '&mlon=' . rawurlencode((string) $longitude)
                        . '#map=16/' . rawurlencode((string) $latitude) . '/' . rawurlencode((string) $longitude);
                  ?>
                  <tr>
                    <td class="ps-3"><?= $sharedAt !== false ? htmlspecialchars(date('d/m/Y H:i:s', $sharedAt)) : '—' ?></td>
                    <td><code><?= htmlspecialchars((string) $location['latitude']) ?>, <?= htmlspecialchars((string) $location['longitude']) ?></code></td>
                    <td><?= htmlspecialchars((string) round((float) $location['accuracy_meters'])) ?> m</td>
                    <td><a href="<?= htmlspecialchars($mapUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Voir sur la carte</a></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($history['locations'])): ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">Aucune position n’a encore été partagée volontairement.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2 class="h6 fw-bold mb-1">Pages visitées</h2>
          <span class="small text-muted">Historique conservé depuis l’activation du suivi, jusqu’à 500 visites récentes. Les paramètres des URL ne sont pas enregistrés. Actualisé automatiquement : <time id="historyUpdatedAt">en attente</time></span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-3">Date et heure</th>
                  <th>Page</th>
                  <th>Adresse</th>
                </tr>
              </thead>
              <tbody id="userPageVisits">
                <?php foreach ($history['visits'] as $visit): ?>
                  <?php $visitedAt = strtotime((string) ($visit['visited_at'] ?? '')); ?>
                  <tr>
                    <td class="ps-3"><?= $visitedAt !== false ? htmlspecialchars(date('d/m/Y H:i:s', $visitedAt)) : '—' ?></td>
                    <td><?= htmlspecialchars($visit['page_title'] ?? '') ?></td>
                    <td><code><?= htmlspecialchars($visit['page_path'] ?? '') ?></code></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($history['visits'])): ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                      Aucune visite n’a encore été enregistrée. L’historique commence avec les pages visitées après l’activation du suivi.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php if (!empty($history['user'])): ?>
<script>
  (function () {
    const historyEndpoint = <?= json_encode(BASE_URL . '/dashboard/userHistoryFeed', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const userId = <?= json_encode((int) $history['user']['id']) ?>;
    const visitsTable = document.getElementById('userPageVisits');
    const locationsTable = document.getElementById('userLocationHistory');
    const updatedAt = document.getElementById('historyUpdatedAt');
    const locationsUpdatedAt = document.getElementById('locationsUpdatedAt');
    const lastLoginAt = document.getElementById('lastLoginAt');
    const dashboardAccessAt = document.getElementById('dashboardAccessAt');
    let isRefreshing = false;

    function formatDate(value) {
      if (!value) {
        return 'Non enregistrée';
      }
      const date = new Date(String(value).replace(' ', 'T'));
      return Number.isNaN(date.getTime()) ? 'Non enregistrée' : date.toLocaleString('fr-FR');
    }

    function renderVisits(visits) {
      if (!visits.length) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 3;
        cell.className = 'text-center text-muted py-4';
        cell.textContent = 'Aucune visite n’a encore été enregistrée. L’historique commence avec les pages visitées après l’activation du suivi.';
        row.appendChild(cell);
        visitsTable.replaceChildren(row);
        return;
      }

      const rows = document.createDocumentFragment();
      visits.forEach(function (visit) {
        const row = document.createElement('tr');
        [formatDate(visit.visited_at), visit.page_title || '—', visit.page_path || '—'].forEach(function (value, index) {
          const cell = document.createElement('td');
          if (index === 0) {
            cell.className = 'ps-3';
          }
          if (index === 2) {
            const path = document.createElement('code');
            path.textContent = value;
            cell.appendChild(path);
          } else {
            cell.textContent = value;
          }
          row.appendChild(cell);
        });
        rows.appendChild(row);
      });
      visitsTable.replaceChildren(rows);
    }

    function renderLocations(locations) {
      if (!locations.length) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 4;
        cell.className = 'text-center text-muted py-4';
        cell.textContent = 'Aucune position n’a encore été partagée volontairement.';
        row.appendChild(cell);
        locationsTable.replaceChildren(row);
        return;
      }

      const rows = document.createDocumentFragment();
      locations.forEach(function (location) {
        const row = document.createElement('tr');
        const latitude = Number(location.latitude);
        const longitude = Number(location.longitude);
        const coordinatesAreValid = Number.isFinite(latitude)
          && Number.isFinite(longitude)
          && latitude >= -90 && latitude <= 90
          && longitude >= -180 && longitude <= 180;
        const values = [
          formatDate(location.shared_at),
          coordinatesAreValid ? latitude + ', ' + longitude : '—',
          '±' + Math.round(Number(location.accuracy_meters) || 0) + ' m'
        ];

        values.forEach(function (value, index) {
          const cell = document.createElement('td');
          if (index === 0) {
            cell.className = 'ps-3';
          }
          if (index === 1) {
            const coordinates = document.createElement('code');
            coordinates.textContent = value;
            cell.appendChild(coordinates);
          } else {
            cell.textContent = value;
          }
          row.appendChild(cell);
        });

        const mapCell = document.createElement('td');
        if (coordinatesAreValid) {
          const mapLink = document.createElement('a');
          const latitudeValue = encodeURIComponent(String(latitude));
          const longitudeValue = encodeURIComponent(String(longitude));
          mapLink.href = 'https://www.openstreetmap.org/?mlat=' + latitudeValue
            + '&mlon=' + longitudeValue + '#map=16/' + latitudeValue + '/' + longitudeValue;
          mapLink.target = '_blank';
          mapLink.rel = 'noopener noreferrer';
          mapLink.textContent = 'Voir sur la carte';
          mapCell.appendChild(mapLink);
        } else {
          mapCell.textContent = '—';
        }
        row.appendChild(mapCell);
        rows.appendChild(row);
      });
      locationsTable.replaceChildren(rows);
    }

    async function refreshHistory() {
      if (document.visibilityState !== 'visible' || isRefreshing) {
        return;
      }
      isRefreshing = true;
      try {
        const response = await fetch(historyEndpoint + '?id=' + encodeURIComponent(String(userId)), {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
          cache: 'no-store'
        });
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        const history = await response.json();
        if (!history.user) {
          throw new Error('Réponse d’historique invalide.');
        }
        lastLoginAt.textContent = formatDate(history.user.last_login_at);
        dashboardAccessAt.textContent = formatDate(history.user.dashboard_accessed_at);
        renderVisits(Array.isArray(history.visits) ? history.visits : []);
        renderLocations(Array.isArray(history.locations) ? history.locations : []);
        updatedAt.textContent = new Date().toLocaleTimeString('fr-FR');
        locationsUpdatedAt.textContent = new Date().toLocaleTimeString('fr-FR');
      } catch (error) {
        console.error('Impossible d’actualiser l’historique utilisateur.', error);
      } finally {
        isRefreshing = false;
      }
    }

    refreshHistory();
    window.setInterval(refreshHistory, 5000);
    document.addEventListener('visibilitychange', refreshHistory);
  })();
</script>
<?php endif; ?>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
