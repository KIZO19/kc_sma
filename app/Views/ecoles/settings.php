<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
  $schoolProfile = array_merge(
      $school,
      is_array($schoolFormValues ?? null) ? $schoolFormValues : []
  );
  $schoolLogo = (string) ($school['logo_url'] ?? '');
  if ($schoolLogo !== '' && strpos($schoolLogo, 'http') !== 0) {
      $schoolLogo = BASE_URL . '/' . ltrim($schoolLogo, '/');
  }
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Paramètres de l’école</h1>
    <p class="text-muted mb-0">Modifiez les informations de l’école, gérez les accès et consultez l’abonnement.</p>
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
            <div class="fw-semibold"><?= htmlspecialchars((string) ($schoolProfile['nom_etablissement'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
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
              <?php if (!empty($subscription['description'])): ?>
                <div class="small mt-2"><?= nl2br(htmlspecialchars((string) $subscription['description'], ENT_QUOTES, 'UTF-8')) ?></div>
              <?php endif; ?>
            <?php else: ?>
              <p class="text-muted">Aucun abonnement enregistré.</p>
            <?php endif; ?>
            <a class="btn btn-outline-primary btn-sm mt-2" href="<?= BASE_URL ?>/ecoles/subscriptions#demander-tarif">
              <i class="bi bi-arrow-left-right me-1" aria-hidden="true"></i>Consulter / changer de tarif
            </a>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Année scolaire d’exercice</h2></div>
          <div class="card-body">
            <?php if (!empty($activeSchoolYear)): ?>
              <p class="mb-2">
                Année actuellement active :
                <strong><?= htmlspecialchars((string) $activeSchoolYear['annee'], ENT_QUOTES, 'UTF-8') ?></strong>
              </p>
              <?php if (($latestSchoolYearRequest['statut'] ?? '') === 'En_attente'): ?>
                <div class="alert alert-info mb-0" role="status">
                  Votre demande de passage à l’année
                  <strong><?= htmlspecialchars((string) ($latestSchoolYearRequest['annee_demandee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                  est en attente de vérification par KC_SMA.
                </div>
              <?php else: ?>
                <?php if (($latestSchoolYearRequest['statut'] ?? '') === 'Refusee'): ?>
                  <div class="alert alert-warning" role="status">
                    La dernière demande de changement vers
                    <strong><?= htmlspecialchars((string) ($latestSchoolYearRequest['annee_demandee'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    a été refusée.
                    <?php if (!empty($latestSchoolYearRequest['reponse'])): ?>
                      <div class="mt-1"><?= nl2br(htmlspecialchars((string) $latestSchoolYearRequest['reponse'], ENT_QUOTES, 'UTF-8')) ?></div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <p class="text-muted">
                  Une modification ne prend effet qu’après vérification et approbation par KC_SMA.
                </p>
                <form method="post" action="<?= BASE_URL ?>/ecoles/requestSchoolYearChange" class="row g-2 align-items-end">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  <div class="col-md-5">
                    <label class="form-label" for="school-year-change">Nouvelle année scolaire</label>
                    <input class="form-control" id="school-year-change" name="annee_scolaire" type="text"
                           inputmode="numeric" pattern="[0-9]{4}-[0-9]{4}" maxlength="9"
                           placeholder="2026-2027" required>
                    <div class="form-text">Format AAAA-AAAA, avec deux années consécutives.</div>
                  </div>
                  <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                      <i class="bi bi-send me-1" aria-hidden="true"></i>Soumettre une demande
                    </button>
                  </div>
                </form>
              <?php endif; ?>
            <?php else: ?>
              <p class="text-muted">
                Définissez l’année d’exercice de votre école. Cette opération ne peut être effectuée qu’une seule fois;
                toute modification ultérieure devra être approuvée par KC_SMA.
              </p>
              <form method="post" action="<?= BASE_URL ?>/ecoles/setInitialSchoolYear" class="row g-2 align-items-end">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <div class="col-md-5">
                  <label class="form-label" for="school-year-initial">Année scolaire d’exercice</label>
                  <input class="form-control" id="school-year-initial" name="annee_scolaire" type="text"
                         inputmode="numeric" pattern="[0-9]{4}-[0-9]{4}" maxlength="9"
                         placeholder="2026-2027" required>
                  <div class="form-text">Format AAAA-AAAA, avec deux années consécutives.</div>
                </div>
                <div class="col-md-auto">
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer l’année
                  </button>
                </div>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Informations de l’école</h2></div>
          <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/ecoles/updateSchoolProfile">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label class="form-label" for="school-name">Nom de l’établissement</label>
                    <input class="form-control" id="school-name" name="nom_etablissement" type="text"
                           maxlength="150" required
                           value="<?= htmlspecialchars((string) ($schoolProfile['nom_etablissement'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  </div>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label" for="school-email">E-mail officiel</label>
                      <input class="form-control" id="school-email" name="email_officiel" type="email"
                             maxlength="100"
                             value="<?= htmlspecialchars((string) ($schoolProfile['email_officiel'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="school-phone">Téléphone</label>
                      <input class="form-control" id="school-phone" name="telephone_contact" type="tel"
                             maxlength="20"
                             value="<?= htmlspecialchars((string) ($schoolProfile['telephone_contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                  </div>
                  <div class="mt-3">
                    <label class="form-label" for="school-address">Adresse</label>
                    <textarea class="form-control" id="school-address" name="adresse" rows="3"><?= htmlspecialchars((string) ($schoolProfile['adresse'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="school-logo">Logo de l’école (PNG ou JPEG, 250 KB maximum)</label>
                  <?php if ($schoolLogo !== ''): ?>
                    <div class="mb-3">
                      <img src="<?= htmlspecialchars($schoolLogo, ENT_QUOTES, 'UTF-8') ?>"
                           alt="Logo actuel de l’école" class="img-thumbnail"
                           style="width: 120px; height: 120px; object-fit: contain;">
                    </div>
                  <?php endif; ?>
                  <input class="form-control" id="school-logo" name="logo" type="file"
                         accept="image/png,image/jpeg">
                  <div class="form-text">Laissez ce champ vide pour conserver le logo actuel.</div>
                </div>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer les informations
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Destinataires des demandes de dérogation</h2></div>
          <form method="post" action="<?= BASE_URL ?>/ecoles/updateDerogationRecipients">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-body">
              <p class="text-muted">
                Choisissez les comptes actifs qui recevront une notification lorsqu’un parent demande une dérogation.
                Les personnes sélectionnées pourront consulter les demandes; seul le comptable peut les approuver ou les refuser.
              </p>
              <?php if (empty($derogationRecipientOptions)): ?>
                <div class="alert alert-warning mb-0">Aucun comptable, préfet ou directeur actif n’est disponible pour recevoir ces demandes.</div>
              <?php else: ?>
                <div class="row g-2">
                  <?php foreach ($derogationRecipientOptions as $recipient): ?>
                    <?php
                      $recipientId = (int) $recipient['id'];
                      $recipientCheckboxId = 'derogationRecipient' . $recipientId;
                    ?>
                    <div class="col-md-6 col-xl-4">
                      <label class="form-check border rounded p-3 h-100" for="<?= $recipientCheckboxId ?>">
                        <input class="form-check-input" type="checkbox" name="recipient_user_ids[]"
                               id="<?= $recipientCheckboxId ?>" value="<?= $recipientId ?>"
                               <?= in_array($recipientId, $derogationRecipientUserIds ?? [], true) ? 'checked' : '' ?>>
                        <span class="form-check-label ms-1">
                          <span class="d-block fw-semibold"><?= htmlspecialchars((string) ($recipient['nom_complet'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?></span>
                          <span class="small text-muted"><?= htmlspecialchars(\App\Models\User::getRoleLabel((string) $recipient['role']), ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary" <?= empty($derogationRecipientOptions) ? 'disabled' : '' ?>>
                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer les destinataires
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Destinataires des messages des parents</h2></div>
          <form method="post" action="<?= BASE_URL ?>/ecoles/updateMessageRecipients">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-body">
              <p class="text-muted">
                Choisissez les autorités scolaires que les parents pourront contacter dans la messagerie.
                Seuls les comptes actifs sélectionnés apparaîtront dans leur liste de destinataires.
              </p>
              <?php if (empty($messageRecipientOptions)): ?>
                <div class="alert alert-warning mb-0">Aucune autorité scolaire active n’est disponible pour recevoir les messages des parents.</div>
              <?php else: ?>
                <div class="row g-2">
                  <?php foreach ($messageRecipientOptions as $recipient): ?>
                    <?php
                      $recipientId = (int) $recipient['id'];
                      $recipientCheckboxId = 'messageRecipient' . $recipientId;
                    ?>
                    <div class="col-md-6 col-xl-4">
                      <label class="form-check border rounded p-3 h-100" for="<?= $recipientCheckboxId ?>">
                        <input class="form-check-input" type="checkbox" name="message_recipient_user_ids[]"
                               id="<?= $recipientCheckboxId ?>" value="<?= $recipientId ?>"
                               <?= in_array($recipientId, $messageRecipientUserIds ?? [], true) ? 'checked' : '' ?>>
                        <span class="form-check-label ms-1">
                          <span class="d-block fw-semibold"><?= htmlspecialchars((string) ($recipient['nom_complet'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?></span>
                          <span class="small text-muted"><?= htmlspecialchars(\App\Models\User::getRoleLabel((string) $recipient['role']), ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary" <?= empty($messageRecipientOptions) ? 'disabled' : '' ?>>
                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer les destinataires
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Fonctionnalités de communication</h2></div>
          <form method="post" action="<?= BASE_URL ?>/ecoles/updateSchoolNotificationAccess">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-body">
              <p class="text-muted">KC_SMA contrôle la messagerie interne entre les comptes de l’école. Vous pouvez gérer les préférences SMS et WhatsApp autorisées ci-dessous.</p>
              <div class="alert <?= !empty($notificationSettings['direct_messages_allowed']) ? 'alert-success' : 'alert-warning' ?>" role="status">
                Messagerie entre comptes :
                <strong><?= !empty($notificationSettings['direct_messages_allowed']) ? 'autorisée' : 'bloquée par KC_SMA' ?></strong>.
              </div>
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
                          Canal bloqué par KC_SMA.
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
