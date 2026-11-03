<?php
$pageScripts = <<<'SCRIPT'
<script>
  (function () {
    const planSelect = document.querySelector('select[name="plan_id"]');
    const typeSelect = document.getElementById('subscriptionType');
    const trialDurationGroup = document.getElementById('trialDurationGroup');
    const trialDurationInput = document.getElementById('trialDurationDays');
    const priceInput = document.getElementById('subscriptionPrice');
    const startInput = document.getElementById('subscriptionStart');
    const endInput = document.getElementById('subscriptionEnd');
    const descriptionPreview = document.getElementById('subscriptionDescriptionPreview');
    const quotaInputs = [
      document.getElementById('maxStudents'),
      document.getElementById('maxParentAccounts'),
      document.getElementById('maxStaffAccounts')
    ];
    const additionalDescription = document.getElementById('subscriptionDescription');
    const trialQuotaGroup = document.getElementById('trialQuotaGroup');

    function updateSubscriptionPreview() {
      const isTrial = typeSelect.value === 'trial';
      planSelect.required = !isTrial;
      planSelect.disabled = isTrial;
      document.getElementById('subscriptionPlanGroup').hidden = isTrial;
      trialQuotaGroup.hidden = !isTrial;
      quotaInputs.forEach(input => {
        input.disabled = !isTrial;
      });
      trialDurationGroup.hidden = !isTrial;
      trialDurationInput.required = isTrial;
      priceInput.value = isTrial ? '0.00' : '';
      const selectedPlan = planSelect.options[planSelect.selectedIndex];
      const planName = isTrial ? 'Essai gratuit' : (selectedPlan.textContent.split(' - ')[0] || 'Abonnement').trim();
      const planDescription = isTrial
        ? 'Essai gratuit de ' + (trialDurationInput.value || '0') + ' jour(s).'
        : (selectedPlan.dataset.description || '').trim();
      const limits = isTrial
        ? quotaInputs.map(input => input.value)
        : [
            selectedPlan.dataset.maxStudents || '0',
            selectedPlan.dataset.maxParents || '0',
            selectedPlan.dataset.maxStaff || '0'
          ];
      const duration = isTrial
        ? Number(trialDurationInput.value || 0)
        : Number(selectedPlan.dataset.duration || 0);
      if (!isTrial) {
        priceInput.value = selectedPlan.dataset.price || '';
      }
      endInput.value = '';

      if (!startInput.value || duration <= 0) {
        descriptionPreview.textContent = 'Sélectionnez un plan ou renseignez la durée de l’essai, puis indiquez la date de début.';
        return;
      }

      const [year, month, day] = startInput.value.split('-').map(Number);
      let endDate;
      if (isTrial) {
        endDate = new Date(year, month - 1, day + duration - 1);
      } else {
        const targetFirst = new Date(year, month - 1 + duration, 1);
        const targetDay = Math.min(day, new Date(targetFirst.getFullYear(), targetFirst.getMonth() + 1, 0).getDate());
        endDate = new Date(targetFirst.getFullYear(), targetFirst.getMonth(), targetDay - 1);
      }
      const format = value => String(value).padStart(2, '0');
      endInput.value = `${endDate.getFullYear()}-${format(endDate.getMonth() + 1)}-${format(endDate.getDate())}`;
      const formatDate = date => `${format(date.getDate())}/${format(date.getMonth() + 1)}/${date.getFullYear()}`;
      const formatLimit = value => Number(value) > 0 ? value : 'illimité';
      descriptionPreview.textContent = [
        `Plan : ${planName} · Tarif : ${Number(priceInput.value || 0).toFixed(2)} USD · Période : ${formatDate(new Date(year, month - 1, day))} au ${formatDate(endDate)}`,
        `Maximum : ${formatLimit(limits[0])} élèves, ${formatLimit(limits[1])} comptes parents et ${formatLimit(limits[2])} comptes agents (enseignants et administratifs).`,
        planDescription,
        additionalDescription.value.trim()
      ].filter(Boolean).join('\n');
    }

    typeSelect.addEventListener('change', updateSubscriptionPreview);
    planSelect.addEventListener('change', updateSubscriptionPreview);
    startInput.addEventListener('change', updateSubscriptionPreview);
    trialDurationInput.addEventListener('input', updateSubscriptionPreview);
    quotaInputs.forEach(input => input.addEventListener('input', updateSubscriptionPreview));
    additionalDescription.addEventListener('input', updateSubscriptionPreview);
    updateSubscriptionPreview();
  })();
</script>
SCRIPT;
require __DIR__ . '/../partials/app_header.php';
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Ajouter un abonnement</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/ecoles">Écoles</a></li>
                <li class="breadcrumb-item active">Ajouter un abonnement</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (empty($plans)): ?>
            <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
              <span>Aucun tarif payant n’est défini. Vous pouvez néanmoins attribuer un essai gratuit.</span>
              <a href="<?= BASE_URL ?>/ecoles/plans" class="btn btn-sm btn-outline-primary">Définir les tarifs</a>
            </div>
          <?php endif; ?>
          <?php if (!empty($_SESSION['ecoles_subscription_errors'])): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($_SESSION['ecoles_subscription_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php unset($_SESSION['ecoles_subscription_errors']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['ecoles_subscription_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['ecoles_subscription_success']) ?></div>
            <?php unset($_SESSION['ecoles_subscription_success']); ?>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-8 mx-auto">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Ajouter un abonnement</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/ecoles/addSubscription">
                    <div class="mb-3">
                      <label class="form-label">École</label>
                      <select name="ecole_id" class="form-select" required>
                        <option value="">Sélectionnez une école</option>
                        <?php foreach (($schools ?? []) as $school): ?>
                          <option value="<?= (int) $school['id'] ?>" <?= (int) ($selectedSchoolId ?? 0) === (int) $school['id'] ? 'selected' : '' ?>><?= htmlspecialchars($school['nom_etablissement']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="subscriptionType" class="form-label">Type d’accès</label>
                      <select id="subscriptionType" name="type_abonnement" class="form-select" required>
                        <option value="plan">Plan d’abonnement</option>
                        <option value="trial">Essai gratuit</option>
                      </select>
                    </div>
                    <div class="mb-3" id="subscriptionPlanGroup">
                      <label class="form-label">Plan</label>
                      <select name="plan_id" class="form-select" required>
                        <option value="">Sélectionnez un plan</option>
                        <?php foreach (($plans ?? []) as $plan): ?>
                          <option value="<?= (int) $plan['id'] ?>" data-price="<?= htmlspecialchars((string) $plan['prix'], ENT_QUOTES, 'UTF-8') ?>" data-duration="<?= (int) $plan['duree_mois'] ?>" data-description="<?= htmlspecialchars((string) ($plan['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-max-students="<?= (int) ($plan['max_students'] ?? 0) ?>" data-max-parents="<?= (int) ($plan['max_parent_accounts'] ?? 0) ?>" data-max-staff="<?= (int) ($plan['max_staff_accounts'] ?? 0) ?>">
                            <?= htmlspecialchars($plan['nom_plan']) ?> - <?= htmlspecialchars((string) $plan['prix']) ?> USD / <?= (int) $plan['duree_mois'] ?> mois
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <div class="form-text">Les plafonds des plans sont configurés sur la page <a href="<?= BASE_URL ?>/ecoles/plans">Tarifs des abonnements</a>.</div>
                    </div>
                    <div class="mb-3" id="trialDurationGroup" hidden>
                      <label for="trialDurationDays" class="form-label">Durée de l’essai (jours)</label>
                      <input id="trialDurationDays" type="number" name="duree_essai_jours" class="form-control" min="1" max="3650">
                      <div class="form-text">L’essai est gratuit et sa durée est indépendante des plans payants.</div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tarif appliqué</label>
                      <div class="input-group">
                        <input id="subscriptionPrice" type="number" class="form-control" readonly aria-describedby="subscriptionCurrency">
                        <span class="input-group-text" id="subscriptionCurrency">USD</span>
                      </div>
                      <div class="form-text">Le plan choisi détermine le tarif ; l’essai est gratuit.</div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Date début</label>
                      <input id="subscriptionStart" type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Date de fin calculée selon la durée sélectionnée</label>
                      <input id="subscriptionEnd" type="date" class="form-control" readonly>
                    </div>
                    <fieldset id="trialQuotaGroup" class="border rounded p-3 mb-3" hidden>
                      <legend class="float-none w-auto px-2 fs-6 fw-semibold">Plafonds de l’essai gratuit</legend>
                      <p class="form-text mt-0">La valeur 0 signifie illimité. Les plafonds des plans payants sont configurés dans Tarifs des abonnements.</p>
                      <div class="row g-3">
                        <div class="col-md-4">
                          <label for="maxStudents" class="form-label">Maximum d’élèves</label>
                          <input id="maxStudents" type="number" name="max_students" class="form-control" min="0" max="1000000" value="0" required>
                        </div>
                        <div class="col-md-4">
                          <label for="maxParentAccounts" class="form-label">Comptes parents</label>
                          <input id="maxParentAccounts" type="number" name="max_parent_accounts" class="form-control" min="0" max="1000000" value="0" required>
                        </div>
                        <div class="col-md-4">
                          <label for="maxStaffAccounts" class="form-label">Comptes agents</label>
                          <input id="maxStaffAccounts" type="number" name="max_staff_accounts" class="form-control" min="0" max="1000000" value="0" required>
                          <div class="form-text">Enseignants et personnel administratif.</div>
                        </div>
                      </div>
                    </fieldset>
                    <div class="mb-3">
                      <label for="subscriptionDescription" class="form-label">Détails complémentaires</label>
                      <textarea id="subscriptionDescription" name="description" class="form-control" rows="3" maxlength="2000"></textarea>
                      <div class="form-text">La description récapitulative du plan, de la période, du tarif et des plafonds sera enregistrée avec l’abonnement.</div>
                    </div>
                    <div class="alert alert-info" role="status">
                      <div class="fw-semibold mb-1">Description de l’abonnement</div>
                      <div id="subscriptionDescriptionPreview" class="small" style="white-space: pre-line">Sélectionnez le type d’accès et renseignez les dates et plafonds pour prévisualiser la description.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Créer abonnement</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>