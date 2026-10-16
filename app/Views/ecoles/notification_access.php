<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Accès aux notifications</h1>
    <p class="text-muted">Le super-administrateur contrôle l’envoi des SMS et messages WhatsApp pour chaque école.</p>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($success)): ?>
      <div class="alert alert-success alert-dismissible">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger alert-dismissible">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>
    <div class="alert alert-info">
      Les deux canaux restent autorisés par défaut afin de préserver le fonctionnement actuel. Désactivez un canal pour bloquer les notifications de paiement, d’inscription et les communiqués de l’école.
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">École</th>
                <th>SMS autorisés</th>
                <th>WhatsApp autorisé</th>
                <th class="text-end pe-3">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (($schoolNotificationAccess ?? []) as $school): ?>
                <?php $formId = 'notificationAccess' . (int) $school['ecole_id']; ?>
                <tr>
                  <td class="ps-3 fw-semibold"><?= htmlspecialchars($school['nom_etablissement'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <input form="<?= $formId ?>" type="hidden" name="sms_enabled" value="0">
                    <div class="form-check form-switch">
                      <input form="<?= $formId ?>" class="form-check-input" type="checkbox" role="switch"
                             id="<?= $formId ?>Sms" name="sms_enabled" value="1"
                             <?= !empty($school['sms_enabled']) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="<?= $formId ?>Sms">
                        <?= !empty($school['sms_enabled']) ? 'Activé' : 'Désactivé' ?>
                      </label>
                    </div>
                  </td>
                  <td>
                    <input form="<?= $formId ?>" type="hidden" name="whatsapp_enabled" value="0">
                    <div class="form-check form-switch">
                      <input form="<?= $formId ?>" class="form-check-input" type="checkbox" role="switch"
                             id="<?= $formId ?>Whatsapp" name="whatsapp_enabled" value="1"
                             <?= !empty($school['whatsapp_enabled']) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="<?= $formId ?>Whatsapp">
                        <?= !empty($school['whatsapp_enabled']) ? 'Activé' : 'Désactivé' ?>
                      </label>
                    </div>
                  </td>
                  <td class="text-end pe-3">
                    <form id="<?= $formId ?>" method="post" action="<?= BASE_URL ?>/ecoles/updateNotificationAccess">
                      <input type="hidden" name="ecole_id" value="<?= (int) $school['ecole_id'] ?>">
                      <input type="hidden" name="notification_access_form_token" value="<?= htmlspecialchars($formToken, ENT_QUOTES, 'UTF-8') ?>">
                      <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($schoolNotificationAccess)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Aucune école à gérer.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
