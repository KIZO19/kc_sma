<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Paramètres de l’école</h1>
    <p class="text-muted mb-0">Gérez les accès, consultez l’abonnement et réglez les fonctionnalités de votre école.</p>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row g-3">
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header"><h2 class="card-title">École</h2></div>
          <div class="card-body">
            <div class="text-muted small">Nom de l’établissement</div>
            <div class="fw-semibold"><?= htmlspecialchars((string) ($school['nom_etablissement'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            <a class="btn btn-outline-primary btn-sm mt-3" href="<?= BASE_URL ?>/ecoles">
              <i class="bi bi-building me-1" aria-hidden="true"></i>Voir la fiche de l’école
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header"><h2 class="card-title">Accès utilisateurs</h2></div>
          <div class="card-body">
            <p class="text-muted">Gérez les comptes et les accès des utilisateurs rattachés à votre école.</p>
            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>/utilisateurs">
              <i class="bi bi-people me-1" aria-hidden="true"></i>Gérer les utilisateurs
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header"><h2 class="card-title">Abonnement</h2></div>
          <div class="card-body">
            <?php if (!empty($subscription)): ?>
              <?php
                $subscriptionIsActive = ($subscription['statut_abonnement'] ?? '') === 'Actif'
                    && strtotime((string) ($subscription['date_debut'] ?? '')) <= time()
                    && strtotime((string) ($subscription['date_fin'] ?? '')) >= strtotime('today');
              ?>
              <div class="fw-semibold"><?= htmlspecialchars((string) ($subscription['nom_plan'] ?? 'Abonnement'), ENT_QUOTES, 'UTF-8') ?></div>
              <div class="mt-1"><span class="badge text-bg-<?= $subscriptionIsActive ? 'success' : 'secondary' ?>"><?= $subscriptionIsActive ? 'Actif' : htmlspecialchars((string) ($subscription['statut_abonnement'] ?? 'Inactif'), ENT_QUOTES, 'UTF-8') ?></span></div>
              <div class="small text-muted mt-2">Valable jusqu’au <?= htmlspecialchars(formatDate($subscription['date_fin'] ?? null), ENT_QUOTES, 'UTF-8') ?></div>
            <?php else: ?>
              <p class="text-muted">Aucun abonnement enregistré.</p>
            <?php endif; ?>
            <a class="btn btn-outline-primary btn-sm mt-2" href="<?= BASE_URL ?>/ecoles/subscriptions">
              <i class="bi bi-card-list me-1" aria-hidden="true"></i>Consulter l’historique
            </a>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Fonctionnalités de communication</h2></div>
          <form method="post" action="<?= BASE_URL ?>/ecoles/updateSchoolNotificationAccess">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-body">
              <p class="text-muted">Vous pouvez activer ou désactiver les notifications pour votre école. Un canal désactivé par le super-administrateur ne peut pas être activé ici.</p>
              <div class="row g-3">
                <?php foreach ([
                    'sms' => ['label' => 'SMS', 'allowed' => 'sms_allowed', 'enabled' => 'sms_enabled'],
                    'whatsapp' => ['label' => 'WhatsApp', 'allowed' => 'whatsapp_allowed', 'enabled' => 'whatsapp_enabled'],
                ] as $channel => $config): ?>
                  <?php
                    $isAllowed = !empty($notificationSettings[$config['allowed']]);
                    $isEnabled = !empty($notificationSettings[$config['enabled']]);
                    $fieldId = 'schoolNotification' . ucfirst($channel);
                  ?>
                  <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                      <input type="hidden" name="<?= $channel ?>_enabled" value="<?= $isAllowed ? '0' : ($isEnabled ? '1' : '0') ?>">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="<?= $fieldId ?>" name="<?= $channel ?>_enabled" value="1"
                               <?= $isEnabled ? 'checked' : '' ?> <?= $isAllowed ? '' : 'disabled' ?>>
                        <label class="form-check-label fw-semibold" for="<?= $fieldId ?>"><?= $config['label'] ?></label>
                      </div>
                      <div class="small text-muted mt-2">
                        <?php if (!$isAllowed): ?>
                          Canal bloqué par le super-administrateur.
                        <?php else: ?>
                          <?= $isEnabled ? 'Les notifications sont activées pour votre école.' : 'Les notifications sont désactivées pour votre école.' ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer les préférences</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
