<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Détails de l’école</h1>
    <p class="text-muted mb-0">Informations de l’établissement rattaché à votre compte.</p>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php
      $schoolLogo = (string) ($school['logo_url'] ?? '');
      if ($schoolLogo !== '' && strpos($schoolLogo, 'http') !== 0) {
          $schoolLogo = BASE_URL . '/' . ltrim($schoolLogo, '/');
      }
      $systemStatus = (string) ($school['statut_systeme'] ?? 'Inconnu');
      $systemStatusClass = $systemStatus === 'Actif'
          ? 'success'
          : ($systemStatus === 'Suspendu' ? 'danger' : 'warning');
    ?>
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="card-title">Informations générales</h2>
            <a href="<?= BASE_URL ?>/ecoles/settings" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-gear me-1" aria-hidden="true"></i>Paramètres de l’école
            </a>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-4">
              <?php if ($schoolLogo !== ''): ?>
                <img src="<?= htmlspecialchars($schoolLogo, ENT_QUOTES, 'UTF-8') ?>"
                     alt="Logo de l’école" class="img-thumbnail"
                     style="width: 88px; height: 88px; object-fit: contain;">
              <?php else: ?>
                <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted"
                     style="width: 88px; height: 88px;" aria-label="Aucun logo">
                  <i class="bi bi-building fs-2" aria-hidden="true"></i>
                </div>
              <?php endif; ?>
              <div>
                <h2 class="h4 mb-2"><?= htmlspecialchars((string) ($school['nom_etablissement'] ?? 'École'), ENT_QUOTES, 'UTF-8') ?></h2>
                <span class="badge text-bg-<?= $systemStatusClass ?>"><?= htmlspecialchars($systemStatus, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>

            <dl class="row mb-0">
              <dt class="col-sm-4">Matricule</dt>
              <dd class="col-sm-8"><?= htmlspecialchars((string) ($school['matricule'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></dd>

              <dt class="col-sm-4">Email officiel</dt>
              <dd class="col-sm-8">
                <?php if (!empty($school['email_officiel'])): ?>
                  <a href="mailto:<?= htmlspecialchars((string) $school['email_officiel'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $school['email_officiel'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php else: ?>—<?php endif; ?>
              </dd>

              <dt class="col-sm-4">Téléphone</dt>
              <dd class="col-sm-8">
                <?php if (!empty($school['telephone_contact'])): ?>
                  <a href="tel:<?= htmlspecialchars((string) $school['telephone_contact'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $school['telephone_contact'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php else: ?>—<?php endif; ?>
              </dd>

              <dt class="col-sm-4">Adresse</dt>
              <dd class="col-sm-8"><?= nl2br(htmlspecialchars((string) ($school['adresse'] ?? '—'), ENT_QUOTES, 'UTF-8')) ?></dd>

              <dt class="col-sm-4">Date de création</dt>
              <dd class="col-sm-8"><?= htmlspecialchars(formatDate($school['date_creation_compte'] ?? null), ENT_QUOTES, 'UTF-8') ?></dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header"><h2 class="card-title">Abonnement</h2></div>
          <div class="card-body">
            <?php if (!empty($subscription)): ?>
              <?php
                $subscriptionStatus = (string) ($subscription['statut_abonnement'] ?? 'Inconnu');
                $subscriptionStart = strtotime((string) ($subscription['date_debut'] ?? ''));
                $subscriptionEnd = strtotime((string) ($subscription['date_fin'] ?? ''));
                $subscriptionIsActive = $subscriptionStatus === 'Actif'
                    && $subscriptionStart !== false
                    && $subscriptionStart <= time()
                    && $subscriptionEnd !== false
                    && date('Y-m-d', $subscriptionEnd) >= date('Y-m-d');
              ?>
              <div class="text-muted small">Plan</div>
              <div class="fw-semibold mb-3"><?= htmlspecialchars((string) ($subscription['nom_plan'] ?? 'Abonnement'), ENT_QUOTES, 'UTF-8') ?></div>

              <div class="text-muted small">Statut</div>
              <div class="mb-3"><span class="badge text-bg-<?= $subscriptionIsActive ? 'success' : 'secondary' ?>"><?= htmlspecialchars($subscriptionStatus, ENT_QUOTES, 'UTF-8') ?></span></div>

              <div class="text-muted small">Période</div>
              <div><?= htmlspecialchars(formatDate($subscription['date_debut'] ?? null), ENT_QUOTES, 'UTF-8') ?> – <?= htmlspecialchars(formatDate($subscription['date_fin'] ?? null), ENT_QUOTES, 'UTF-8') ?></div>
              <div class="text-muted small mt-3">Plafonds</div>
              <div class="small">
                Élèves : <?= (int) ($subscription['max_students'] ?? 0) > 0 ? (int) $subscription['max_students'] : 'Illimité' ?><br>
                Comptes parents : <?= (int) ($subscription['max_parent_accounts'] ?? 0) > 0 ? (int) $subscription['max_parent_accounts'] : 'Illimité' ?><br>
                Comptes agents : <?= (int) ($subscription['max_staff_accounts'] ?? 0) > 0 ? (int) $subscription['max_staff_accounts'] : 'Illimité' ?>
              </div>
              <?php if (!empty($subscription['description'])): ?>
                <div class="text-muted small mt-3">Description de l’abonnement</div>
                <div class="small"><?= nl2br(htmlspecialchars((string) $subscription['description'], ENT_QUOTES, 'UTF-8')) ?></div>
              <?php endif; ?>
            <?php else: ?>
              <p class="text-muted">Aucun abonnement enregistré pour cette école.</p>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/ecoles/subscriptions" class="btn btn-sm btn-outline-primary mt-3">
              <i class="bi bi-card-list me-1" aria-hidden="true"></i>Voir l’historique
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
