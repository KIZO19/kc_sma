<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$parents = $parents ?? [];
$sections = $sections ?? [];
$options = $options ?? [];
$oldInput = $oldInput ?? [];
$selectedSectionName = mb_strtolower(trim((string) ($selectedSection['nom_section'] ?? '')), 'UTF-8');
$showRegistrationOptions = $selectedSectionName === 'secondaire';
$pageStyles = <<<STYLE
<style>
  .registration-card { width: 100%; border-radius: 1rem; overflow: hidden; }
  .registration-card .form-control,
  .registration-card .form-select { min-width: 0; }
  .registration-card .card-header { padding: 1.25rem 1.5rem; }
  .registration-card .card-body { padding: 1.5rem; }
  .registration-intro { color: #64748b; max-width: 58rem; }
  .registration-section {
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    border: 1px solid #e2e8f0;
    border-radius: .875rem;
    background: #fff;
  }
  .registration-section-heading {
    display: flex;
    align-items: flex-start;
    gap: .875rem;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #edf0f4;
  }
  .registration-section-step {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 2.25rem;
    width: 2.25rem;
    height: 2.25rem;
    color: #fff;
    background: #198754;
    border-radius: 50%;
    font-weight: 700;
  }
  .registration-section-heading h4 { margin: 0 0 .2rem; font-size: 1rem; font-weight: 700; }
  .registration-section-heading p { margin: 0; color: #64748b; font-size: .875rem; }
  .registration-card .form-label { font-weight: 600; }
  .registration-card .form-actions {
    position: sticky;
    bottom: 0;
    z-index: 2;
    justify-content: flex-end !important;
    padding: 1rem 0 0;
    margin-top: .5rem;
    border-top: 1px solid #e2e8f0;
    background: #fff;
  }
  [data-theme="dark"] .registration-section { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.12); }
  [data-theme="dark"] .registration-section-heading { border-color: rgba(255,255,255,.1); }
  [data-theme="dark"] .registration-section-heading p { color: #adb5bd; }
  [data-theme="dark"] .registration-card .form-actions { background: #212529; border-color: rgba(255,255,255,.12); }
  @media (max-width: 575.98px) {
    .content-header h1 { font-size: 1.5rem; }
    .content-header .breadcrumb { float: none !important; flex-wrap: wrap; margin-top: .75rem; }
    .registration-card .card-header { padding: 1rem; }
    .registration-card .card-body { padding: .75rem; }
    .registration-section { padding: 1rem .75rem; }
    .registration-card .form-actions { flex-direction: column-reverse; }
    .registration-card .form-actions .btn { width: 100%; }
    .registration-card .input-group { flex-wrap: nowrap; }
    .registration-card .input-group .btn { white-space: nowrap; }
  }
</style>
STYLE;
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Nouvelle inscription</h1>
              <p class="registration-intro mb-0">Renseignez les informations de l’élève, son affectation scolaire et les coordonnées de son parent ou tuteur.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inscriptions">Dossiers d’inscription</a></li>
                <li class="breadcrumb-item active">Nouvelle inscription</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['inscriptions_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= htmlspecialchars($_SESSION['inscriptions_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['inscriptions_success']); ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['inscriptions_errors'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <ul class="mb-0">
                <?php foreach ($_SESSION['inscriptions_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['inscriptions_errors']); ?>
          <?php endif; ?>

          <div class="row justify-content-center">
            <div class="col-12 col-xl-10 col-xxl-9">
              <div class="card card-outline card-success registration-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                  <div>
                    <h3 class="card-title fw-bold mb-1">Formulaire d’inscription</h3>
                    <div class="small text-muted">Les champs marqués d’un astérisque sont obligatoires.</div>
                  </div>
                  <span class="badge text-bg-success rounded-pill px-3 py-2">Nouveau dossier</span>
                </div>
                <div class="card-body">
                  <form id="registration-form" method="post" action="<?= BASE_URL ?>/inscriptions/submit" autocomplete="off" novalidate enctype="multipart/form-data">
                    <section class="registration-section" aria-labelledby="registration-identity-heading">
                      <div class="registration-section-heading">
                        <span class="registration-section-step">1</span>
                        <div>
                          <h4 id="registration-identity-heading">Identité de l’élève</h4>
                          <p>État civil et identification scolaire.</p>
                        </div>
                      </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="nom-input">Nom <span class="text-danger">*</span></label>
                        <input id="nom-input" type="text" name="nom" class="form-control" required minlength="2" maxlength="100" value="<?= htmlspecialchars($oldInput['nom'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="postnom-input">Postnom <span class="text-danger">*</span></label>
                        <input id="postnom-input" type="text" name="postnom" class="form-control" required minlength="2" maxlength="100" value="<?= htmlspecialchars($oldInput['postnom'] ?? '') ?>">
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="prenom-input">Prénom</label>
                        <input id="prenom-input" type="text" name="prenom" class="form-control" maxlength="100" value="<?= htmlspecialchars($oldInput['prenom'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Genre <span class="text-danger">*</span></label>
                        <select name="genre" class="form-select" required>
                          <option value="">Sélectionnez</option>
                          <option value="M" <?= ($oldInput['genre'] ?? '') === 'M' ? 'selected' : '' ?>>Masculin</option>
                          <option value="F" <?= ($oldInput['genre'] ?? '') === 'F' ? 'selected' : '' ?>>Féminin</option>
                        </select>
                      </div>
                    </div>

                    <div class="row align-items-end">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="date-naissance-input">Date de naissance <span class="text-danger">*</span></label>
                        <input id="date-naissance-input" type="date" name="date_naissance" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($oldInput['date_naissance'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="matricule-input">Matricule <span class="text-muted fw-normal">(optionnel)</span></label>
                        <div class="input-group">
                          <input id="matricule-input" type="text" name="matricule" class="form-control" pattern="[A-Z]{4}[0-9]{5}" maxlength="9" placeholder="Généré automatiquement si vide" value="<?= htmlspecialchars($oldInput['matricule'] ?? '') ?>">
                          <button id="generate-matricule-btn" type="button" class="btn btn-outline-secondary">Aperçu</button>
                        </div>
                        <div id="matricule-preview" class="form-text text-muted mt-1">Aperçu du matricule généré disponible après saisie du nom et du postnom. Format attendu : 4 lettres majuscules suivies de 5 chiffres (ex. ABCD12345).</div>
                      </div>
                    </div>
                    </section>

                    <section class="registration-section" aria-labelledby="registration-school-heading">
                      <div class="registration-section-heading">
                        <span class="registration-section-step">2</span>
                        <div>
                          <h4 id="registration-school-heading">Affectation scolaire</h4>
                          <p>Choisissez la section, l’option (pour le secondaire) et la classe d’inscription.</p>
                        </div>
                      </div>
                    <div class="mb-3">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Section <span class="text-danger">*</span></label>
                          <select id="registration-section-select" name="section_id" class="form-select" required>
                            <option value="">Sélectionnez une section</option>
                            <?php foreach ($sections as $section): ?>
                              <?php $sectionValue = (int) ($oldInput['section_id'] ?? ($selectedSection['id'] ?? 0)); ?>
                              <option value="<?= (int) $section['id'] ?>" data-section-name="<?= htmlspecialchars(mb_strtolower(trim((string) $section['nom_section']), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>" <?= $sectionValue === (int) $section['id'] ? 'selected' : '' ?>><?= htmlspecialchars($section['nom_section']) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-6 mb-3" id="registration-option-group" <?= $showRegistrationOptions ? '' : 'hidden' ?>>
                          <label class="form-label" for="registration-option-select">Option <span class="text-danger">*</span></label>
                          <select id="registration-option-select" name="option_id" class="form-select" <?= $showRegistrationOptions ? 'required' : 'disabled' ?>>
                            <option value="">Sélectionnez une option</option>
                            <?php foreach ($options as $option): ?>
                              <?php $optionValue = (int) ($oldInput['option_id'] ?? ($selectedOption['id'] ?? 0)); ?>
                              <option value="<?= (int) $option['id'] ?>" data-section-id="<?= (int) ($option['section_id'] ?? 0) ?>" <?= $optionValue === (int) $option['id'] ? 'selected' : '' ?>><?= htmlspecialchars($option['nom_option']) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <label class="form-label">Classe</label>
                      <select id="registration-classe-select" name="classe_id" class="form-select" required disabled>
                        <option value="">Sélectionnez une classe</option>
                        <?php if (!empty($classes)): ?>
                          <?php foreach ($classes as $classe): ?>
                            <option value="<?= (int) $classe['id'] ?>" data-section-id="<?= (int) ($classe['section_id'] ?? 0) ?>" data-option-id="<?= (int) ($classe['option_id'] ?? 0) ?>" <?= ((int) ($oldInput['classe_id'] ?? 0) === (int) $classe['id']) ? 'selected' : '' ?>>
                              <?= htmlspecialchars($classe['nom_classe']) ?>
                              <?php if (!empty($classe['nom_section'])): ?>
                                - <?= htmlspecialchars($classe['nom_section']) ?>
                              <?php endif; ?>
                              <?php if (!empty($classe['nom_option'])): ?>
                                <?= htmlspecialchars($classe['nom_option']) ? ' (' . htmlspecialchars($classe['nom_option']) . ')' : '' ?>
                              <?php endif; ?>
                            </option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                      <small id="registration-class-help" class="form-text text-muted">Sélectionnez d’abord une section et une option.</small>
                    </div>
                    </section>

                    <section class="registration-section" aria-labelledby="registration-parent-heading">
                      <div class="registration-section-heading">
                        <span class="registration-section-step">3</span>
                        <div>
                          <h4 id="registration-parent-heading">Parent ou tuteur</h4>
                          <p>Liez un parent déjà enregistré ou créez sa fiche.</p>
                        </div>
                      </div>
                    <div class="mb-3">
                      <label class="form-label">Choix du parent / tuteur</label>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="parent_choice" id="parent-choice-existing" value="existing" <?= (($oldInput['parent_choice'] ?? 'existing') === 'existing') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="parent-choice-existing">Parent existant</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="parent_choice" id="parent-choice-new" value="new" <?= (($oldInput['parent_choice'] ?? 'existing') === 'new') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="parent-choice-new">Créer nouveau parent</label>
                      </div>
                    </div>
                    <div class="mb-3 parent-choice-existing-section<?= (($oldInput['parent_choice'] ?? 'existing') === 'new') ? ' d-none' : '' ?>">
                      <label class="form-label">Parent lié</label>
                      <select name="parent_id" class="form-select">
                        <option value="">Aucun parent lié</option>
                        <?php foreach ($parents as $parent): ?>
                          <option value="<?= (int) $parent['id'] ?>" <?= ((int) ($oldInput['parent_id'] ?? '') === (int) $parent['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($parent['nom_responsable']) ?> - <?= htmlspecialchars($parent['telephone']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <small class="form-text text-muted">Sélectionnez un parent existant pour lier l’élève.</small>
                    </div>
                    <div class="mb-4 p-3 bg-light rounded border parent-choice-new-section<?= (($oldInput['parent_choice'] ?? 'existing') === 'new') ? '' : ' d-none' ?>">
                      <h5 class="mb-3">Créer un nouveau parent/tuteur</h5>
                      <p class="text-muted small">Remplissez ces champs uniquement si le parent n’existe pas encore.</p>
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Nom du parent/tuteur</label>
                          <input id="new-parent-name" type="text" name="new_parent_nom_responsable" class="form-control" maxlength="150" value="<?= htmlspecialchars($oldInput['new_parent_nom_responsable'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Téléphone du parent/tuteur</label>
                          <input id="new-parent-phone" type="tel" name="new_parent_telephone" class="form-control" maxlength="25" pattern="[0-9+() .-]{7,25}" value="<?= htmlspecialchars($oldInput['new_parent_telephone'] ?? '') ?>">
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Email du parent/tuteur</label>
                          <input id="new-parent-email" type="email" name="new_parent_email" class="form-control" maxlength="100" value="<?= htmlspecialchars($oldInput['new_parent_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="alert alert-info h-100 mb-0 d-flex align-items-center">
                            Renseignez ces informations uniquement si le parent n’existe pas encore dans la liste.
                          </div>
                        </div>
                      </div>
                    </div>
                    </section>

                    <section class="registration-section" aria-labelledby="registration-family-heading">
                      <div class="registration-section-heading">
                        <span class="registration-section-step">4</span>
                        <div>
                          <h4 id="registration-family-heading">Famille et origine</h4>
                          <p>Informations complémentaires sur les parents et l’origine de l’élève.</p>
                        </div>
                      </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nom du père</label>
                        <input type="text" name="nom_pere" class="form-control" value="<?= htmlspecialchars($oldInput['nom_pere'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nom de la mère</label>
                        <input type="text" name="nom_mere" class="form-control" value="<?= htmlspecialchars($oldInput['nom_mere'] ?? '') ?>">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Province d’origine</label>
                        <select name="province_origine" id="province-select" class="form-select">
                          <option value="">Sélectionnez une province</option>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" id="territory-label" for="territoire-select">Territoire</label>
                        <select name="territoire" id="territoire-select" class="form-select">
                          <option value="">Sélectionnez d’abord une province</option>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Numéro permanent</label>
                        <input type="text" name="num_permanent" class="form-control" value="<?= htmlspecialchars($oldInput['num_permanent'] ?? '') ?>" placeholder="Numéro permanent MINEDUB">
                      </div>
                    </div>
                    <div id="origin-location-error" class="alert alert-danger d-none" role="alert"></div>
                    </section>

                    <section class="registration-section" aria-labelledby="registration-contact-heading">
                      <div class="registration-section-heading">
                        <span class="registration-section-step">5</span>
                        <div>
                          <h4 id="registration-contact-heading">Coordonnées et photo</h4>
                          <p>Lieu de naissance, adresse et photo facultative.</p>
                        </div>
                      </div>
                    <div class="mb-3">
                      <label class="form-label" for="birthplace-input">Lieu de naissance</label>
                      <input id="birthplace-input" type="text" name="lieu_naissance" class="form-control" value="<?= htmlspecialchars($oldInput['lieu_naissance'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="student-address">Adresse</label>
                      <textarea id="student-address" name="adresse" class="form-control" rows="3"><?= htmlspecialchars($oldInput['adresse'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="student-photo">Photo de l’élève <span class="text-muted fw-normal">(facultative, PNG/JPEG, 250 Ko maximum)</span></label>
                      <input id="student-photo" type="file" name="photo" accept="image/png,image/jpeg" class="form-control">
                    </div>
                    </section>
                    <div class="d-flex justify-content-between form-actions">
                      <a href="<?= BASE_URL ?>/inscriptions" class="btn btn-secondary">Retour aux dossiers</a>
                      <button id="registration-submit" type="submit" class="btn btn-success">Enregistrer</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          const sectionSelect = document.getElementById('registration-section-select');
          const optionSelect = document.getElementById('registration-option-select');
          const optionGroup = document.getElementById('registration-option-group');
          const classSelect = document.getElementById('registration-classe-select');
          const classHelp = document.getElementById('registration-class-help');
          if (!sectionSelect || !optionSelect || !optionGroup || !classSelect) return;

          const options = Array.from(optionSelect.options).slice(1).map((item) => ({
            value: item.value,
            label: item.textContent,
            sectionId: item.dataset.sectionId
          }));
          const classes = Array.from(classSelect.options).slice(1).map((item) => ({
            value: item.value,
            label: item.textContent,
            sectionId: item.dataset.sectionId,
            optionId: item.dataset.optionId
          }));

          function refreshOptions() {
            const sectionId = sectionSelect.value;
            const selectedSection = sectionSelect.selectedOptions[0];
            const isSecondary = selectedSection?.dataset.sectionName === 'secondaire';
            const currentValue = optionSelect.value;
            optionGroup.hidden = !isSecondary;
            optionSelect.disabled = !isSecondary;
            optionSelect.required = isSecondary;
            optionSelect.innerHTML = '<option value="">Sélectionnez une option</option>';
            options.filter((item) => isSecondary && item.sectionId === sectionId).forEach((item) => {
              const option = document.createElement('option');
              option.value = item.value;
              option.textContent = item.label;
              optionSelect.appendChild(option);
            });
            const currentOptionIsValid = options.some((item) => item.value === currentValue && item.sectionId === sectionId);
            if (currentOptionIsValid) {
              optionSelect.value = currentValue;
            } else {
              optionSelect.value = '';
            }
          }

          function refreshClasses() {
            const sectionId = sectionSelect.value;
            const isSecondary = sectionSelect.selectedOptions[0]?.dataset.sectionName === 'secondaire';
            const optionId = optionSelect.value;
            const currentValue = classSelect.value;
            classSelect.innerHTML = '<option value="">Sélectionnez une classe</option>';
            const matchingClasses = sectionId && (isSecondary ? optionId : true)
              ? classes.filter((item) => item.sectionId === sectionId && item.optionId === (isSecondary ? optionId : '0'))
              : [];
            matchingClasses.forEach((item) => {
              const option = document.createElement('option');
              option.value = item.value;
              option.textContent = item.label;
              classSelect.appendChild(option);
            });
            const currentClassIsValid = matchingClasses.some((item) => item.value === currentValue);
            classSelect.value = currentClassIsValid ? currentValue : '';
            classSelect.disabled = matchingClasses.length === 0;

            if (classHelp) {
              classHelp.textContent = !sectionId || (isSecondary && !optionId)
                ? (isSecondary ? 'Sélectionnez d’abord une section et une option.' : 'Sélectionnez une section.')
                : matchingClasses.length === 0
                  ? (isSecondary
                    ? 'Aucune classe n’est disponible pour cette section et cette option.'
                    : 'Aucune classe sans option n’est disponible dans cette section.')
                  : 'Sélectionnez la classe où l’élève doit être inscrit.';
            }
          }

          sectionSelect.addEventListener('change', function () { refreshOptions(); refreshClasses(); });
          optionSelect.addEventListener('change', refreshClasses);
          refreshOptions();
          refreshClasses();
        });
      </script>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          const registrationForm = document.getElementById('registration-form');
          const registrationSubmit = document.getElementById('registration-submit');
          const existingRadio = document.getElementById('parent-choice-existing');
          const newRadio = document.getElementById('parent-choice-new');
          const existingSection = document.querySelector('.parent-choice-existing-section');
          const newSection = document.querySelector('.parent-choice-new-section');
          const newParentName = document.getElementById('new-parent-name');
          const newParentPhone = document.getElementById('new-parent-phone');

          function toggleParentSections() {
            const useNew = newRadio && newRadio.checked;
            if (existingSection) {
              existingSection.classList.toggle('d-none', useNew);
            }
            if (newSection) {
              newSection.classList.toggle('d-none', !useNew);
            }
            if (newParentName) newParentName.required = useNew;
            if (newParentPhone) newParentPhone.required = useNew;
          }

          if (existingRadio) {
            existingRadio.addEventListener('change', toggleParentSections);
          }
          if (newRadio) {
            newRadio.addEventListener('change', toggleParentSections);
          }

          toggleParentSections();

          if (registrationForm) {
            registrationForm.addEventListener('submit', function (event) {
              registrationForm.classList.add('was-validated');
              if (!registrationForm.checkValidity()) {
                event.preventDefault();
                const firstInvalid = registrationForm.querySelector(':invalid');
                if (firstInvalid) firstInvalid.focus();
                return;
              }

              const photo = document.getElementById('student-photo');
              if (photo && photo.files.length > 0) {
                const file = photo.files[0];
                if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 250 * 1024) {
                  event.preventDefault();
                  window.alert('La photo doit être au format PNG ou JPEG et ne pas dépasser 250 Ko.');
                  return;
                }
              }
              if (registrationSubmit) {
                registrationSubmit.disabled = true;
                registrationSubmit.textContent = 'Enregistrement...';
              }
            });
          }
        });
      </script>
      <script>
        (function () {
          const baseUrl = '<?= BASE_URL ?>';
          const provinceSelect = document.getElementById('province-select');
          const territoireSelect = document.getElementById('territoire-select');
          const territoryLabel = document.getElementById('territory-label');
          const locationError = document.getElementById('origin-location-error');
          const matriculePreviewText = document.getElementById('matricule-preview');
          const nomInput = document.getElementById('nom-input');
          const postnomInput = document.getElementById('postnom-input');
          const prenomInput = document.getElementById('prenom-input');
          const matriculeButton = document.getElementById('generate-matricule-btn');

          function populate(select, items, selected, placeholder) {
            select.innerHTML = '';
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = placeholder;
            select.appendChild(emptyOption);
            items.forEach(function (it) {
              const opt = document.createElement('option');
              opt.value = it.nom || it;
              opt.textContent = it.nom ? it.nom + ' — Chef-lieu : ' + it.chef_lieu : it;
              if (selected && selected === opt.value) opt.selected = true;
              select.appendChild(opt);
            });
          }

          function fetchJson(url) {
            return fetch(url, { credentials: 'same-origin' }).then(function (response) {
              if (!response.ok) throw new Error('Chargement des origines impossible.');
              return response.json();
            });
          }

          function updateMatriculePreview() {
            if (!nomInput || !postnomInput || !matriculePreviewText) {
              return;
            }
            const nom = nomInput.value.trim();
            const postnom = postnomInput.value.trim();
            const prenom = prenomInput ? prenomInput.value.trim() : '';
            if (!nom || !postnom) {
              matriculePreviewText.textContent = 'Saisissez le nom et le postnom pour générer un aperçu du matricule.';
              return;
            }
            const params = new URLSearchParams({ nom: nom, postnom: postnom, prenom: prenom });
            matriculePreviewText.textContent = 'Génération en cours…';
            fetch(baseUrl + '/inscriptions/matricule-preview?' + params.toString(), { credentials: 'same-origin' })
              .then(function (response) {
                return response.json();
              })
              .then(function (data) {
                if (data && data.matricule) {
                  matriculePreviewText.textContent = 'Aperçu du matricule : ' + data.matricule;
                } else if (data && data.error) {
                  matriculePreviewText.textContent = 'Erreur : ' + data.error;
                } else {
                  matriculePreviewText.textContent = 'Impossible de générer le matricule pour le moment.';
                }
              })
              .catch(function () {
                matriculePreviewText.textContent = 'Impossible de générer le matricule pour le moment.';
              });
          }

          if (matriculeButton) {
            matriculeButton.addEventListener('click', function (event) {
              event.preventDefault();
              updateMatriculePreview();
            });
          }

          if (nomInput) {
            nomInput.addEventListener('blur', updateMatriculePreview);
          }
          if (postnomInput) {
            postnomInput.addEventListener('blur', updateMatriculePreview);
          }
          if (prenomInput) {
            prenomInput.addEventListener('blur', updateMatriculePreview);
          }

          let restoredTerritory = <?= json_encode((string) ($oldInput['territoire'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
          fetchJson(baseUrl + '/inscriptions/provinces').then(function (data) {
            const selectedProvinceRaw = <?= json_encode((string) ($oldInput['province_origine'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            const selectedProvince = selectedProvinceRaw === 'Mai-Ndombe' ? 'Maï-Ndombe' : selectedProvinceRaw;
            populate(provinceSelect, data, selectedProvince || null, 'Sélectionnez une province');
            if (selectedProvince) {
              updateTerritories(selectedProvince, restoredTerritory);
              restoredTerritory = '';
            }
          }).catch(function (error) {
            console.error(error);
            locationError.textContent = 'Impossible de charger les provinces. Rechargez la page ou contactez l’administration.';
            locationError.classList.remove('d-none');
          });

          provinceSelect && provinceSelect.addEventListener('change', function () {
            updateTerritories(this.value, restoredTerritory);
            restoredTerritory = '';
          });

          function updateTerritories(val, selected) {
            const territoryPlaceholder = val === 'Kinshasa' ? 'Sélectionnez une commune' : 'Sélectionnez un territoire';
            territoryLabel.textContent = val === 'Kinshasa' ? 'Commune' : 'Territoire';
            populate(territoireSelect, [], null, val ? territoryPlaceholder : 'Sélectionnez d’abord une province');
            locationError.classList.add('d-none');
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/territoires?province=' + encodeURIComponent(val)).then(function (data) {
              populate(territoireSelect, data, selected || null, territoryPlaceholder);
            }).catch(function (error) {
              console.error(error);
              locationError.textContent = 'Impossible de charger les territoires ou les communes de cette province.';
              locationError.classList.remove('d-none');
            });
          }
        })();
      </script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
