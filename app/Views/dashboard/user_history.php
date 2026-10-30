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
    const updatedAt = document.getElementById('historyUpdatedAt');
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
        updatedAt.textContent = new Date().toLocaleTimeString('fr-FR');
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
