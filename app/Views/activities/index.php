<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Activités importantes de la plateforme</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Home</a></li>
          <li class="breadcrumb-item active">Activités</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['activity_log_warning'])): ?>
      <div class="alert alert-warning" role="alert">
        <?= htmlspecialchars((string) $_SESSION['activity_log_warning'], ENT_QUOTES, 'UTF-8') ?>
      </div>
      <?php unset($_SESSION['activity_log_warning']); ?>
    <?php endif; ?>
    <?php
      $priorityCards = [
          [
              'label' => 'Écoles à valider',
              'count' => $prioritySummary['pending_schools'] ?? 0,
              'icon' => 'bi-hourglass-split',
              'color' => 'warning',
              'link' => BASE_URL . '/ecoles',
          ],
          [
              'label' => 'Écoles suspendues',
              'count' => $prioritySummary['suspended_schools'] ?? 0,
              'icon' => 'bi-building-slash',
              'color' => 'danger',
              'link' => BASE_URL . '/ecoles',
          ],
          [
              'label' => 'Administrateurs école actifs',
              'count' => $prioritySummary['active_school_admins'] ?? 0,
              'icon' => 'bi-person-check',
              'color' => 'success',
              'link' => BASE_URL . '/utilisateurs',
          ],
          [
              'label' => 'Administrateurs école inactifs',
              'count' => $prioritySummary['inactive_school_admins'] ?? 0,
              'icon' => 'bi-person-lock',
              'color' => 'secondary',
              'link' => BASE_URL . '/utilisateurs',
          ],
          [
              'label' => 'Abonnements à échéance (14 jours)',
              'count' => $prioritySummary['expiring_subscriptions'] ?? 0,
              'icon' => 'bi-calendar-event',
              'color' => 'warning',
              'link' => BASE_URL . '/ecoles/subscriptions',
          ],
          [
              'label' => 'Abonnements expirés',
              'count' => $prioritySummary['expired_subscriptions'] ?? 0,
              'icon' => 'bi-calendar-x',
              'color' => 'danger',
              'link' => BASE_URL . '/ecoles/subscriptions',
          ],
      ];
    ?>
    <div class="row">
      <?php foreach ($priorityCards as $card): ?>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-<?= htmlspecialchars($card['color'], ENT_QUOTES, 'UTF-8') ?>">
              <i class="bi <?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="info-box-number"><?= (int) $card['count'] ?></span>
              <a class="small" href="<?= htmlspecialchars($card['link'], ENT_QUOTES, 'UTF-8') ?>">Consulter</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/realtime_section.php'; ?>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Journal des opérations importantes</h3>
          </div>
          <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              <?php if (empty($activities)): ?>
                <li class="list-group-item text-center text-muted py-5">
                  <i class="bi bi-activity fs-2 d-block mb-2" aria-hidden="true"></i>
                  Aucune activité récente à afficher.
                </li>
              <?php else: ?>
                <?php foreach ($activities as $act): ?>
                  <?php
                    $activityLabels = [
                        'school_created' => 'École créée',
                        'school_access_changed' => 'Accès de l’école modifié',
                        'school_notification_access_changed' => 'Notifications de l’école modifiées',
                        'school_admin_created' => 'Compte administrateur créé',
                        'school_admin_assigned' => 'Compte administrateur associé',
                        'school_admin_account_updated' => 'Compte administrateur modifié',
                        'school_admin_password_reset' => 'Mot de passe réinitialisé',
                        'school_admin_access_changed' => 'Accès administrateur modifiés',
                        'school_admin_validated' => 'Compte administrateur activé',
                        'subscription_added' => 'Abonnement ajouté',
                        'subscription_updated' => 'Abonnement modifié',
                        'subscription_requested' => 'Demande d’abonnement soumise',
                        'subscription_request_approved' => 'Demande d’abonnement approuvée',
                        'subscription_request_rejected' => 'Demande d’abonnement refusée',
                        'ecole' => 'École active',
                        'ecole_en_attente' => 'École à valider',
                        'ecole_suspendue' => 'École suspendue',
                        'abonnement' => 'Abonnement actif',
                        'abonnement_echeance' => 'Abonnement bientôt à échéance',
                        'abonnement_expire' => 'Abonnement expiré',
                        'abonnement_resilie' => 'Abonnement résilié',
                    ];
                    $activityIcons = [
                        'school_created' => 'bi-building-add text-primary',
                        'school_access_changed' => 'bi-shield-lock text-warning',
                        'school_notification_access_changed' => 'bi-bell text-warning',
                        'school_admin_created' => 'bi-person-plus text-success',
                        'school_admin_assigned' => 'bi-person-check text-primary',
                        'school_admin_account_updated' => 'bi-person-gear text-primary',
                        'school_admin_password_reset' => 'bi-key text-warning',
                        'school_admin_access_changed' => 'bi-shield-check text-warning',
                        'school_admin_validated' => 'bi-person-check-fill text-success',
                        'subscription_added' => 'bi-card-checklist text-success',
                        'subscription_updated' => 'bi-card-heading text-primary',
                        'subscription_requested' => 'bi-send text-primary',
                        'subscription_request_approved' => 'bi-check-circle text-success',
                        'subscription_request_rejected' => 'bi-x-circle text-danger',
                        'ecole' => 'bi-buildings text-primary',
                        'ecole_en_attente' => 'bi-hourglass-split text-warning',
                        'ecole_suspendue' => 'bi-building-slash text-danger',
                        'abonnement' => 'bi-card-checklist text-success',
                        'abonnement_echeance' => 'bi-calendar-event text-warning',
                        'abonnement_expire' => 'bi-calendar-x text-danger',
                        'abonnement_resilie' => 'bi-slash-circle text-secondary',
                    ];
                    $createdAt = strtotime((string) ($act['created_at'] ?? ''));
                  ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-start gap-3">
                      <i class="bi <?= htmlspecialchars($activityIcons[$act['type']] ?? 'bi-activity text-secondary', ENT_QUOTES, 'UTF-8') ?> fs-5 mt-1" aria-hidden="true"></i>
                      <div>
                        <strong><?= htmlspecialchars($activityLabels[$act['type']] ?? 'Activité', ENT_QUOTES, 'UTF-8') ?></strong>
                        <div><?= htmlspecialchars((string) ($act['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if (!empty($act['details'])): ?>
                          <div class="small text-muted"><?= htmlspecialchars((string) $act['details'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <?php if (!empty($act['actor_name'])): ?>
                          <div class="small text-muted">Par <?= htmlspecialchars((string) $act['actor_name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="d-flex flex-column align-items-end text-nowrap">
                      <span class="text-muted small">
                        <?= $createdAt !== false ? htmlspecialchars(date('d/m/Y H:i', $createdAt), ENT_QUOTES, 'UTF-8') : 'Date inconnue' ?>
                      </span>
                      <a class="small" href="<?= BASE_URL . htmlspecialchars((string) ($act['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">Ouvrir</a>
                    </div>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>