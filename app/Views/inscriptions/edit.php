<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$parents = $parents ?? [];
$oldInput = $oldInput ?? [];
$student = $student ?? [];
$classes = $classes ?? [];
$sections = $sections ?? [];
$options = $options ?? [];
$currentInscription = $currentInscription ?? [];
$selectedClasseId = (int) ($oldInput['classe_id'] ?? ($currentInscription['classe_id'] ?? 0));
$selectedSectionId = (int) ($oldInput['section_id'] ?? ($currentInscription['section_id'] ?? 0));
$selectedOptionId = (int) ($oldInput['option_id'] ?? ($currentInscription['option_id'] ?? 0));
$selectedParentId = (int) ($oldInput['parent_id'] ?? ($student['parent_id'] ?? 0));
$selectedParent = null;
foreach ($parents as $parent) {
  if ((int) ($parent['id'] ?? 0) === $selectedParentId) {
    $selectedParent = $parent;
    break;
  }
}
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Éditer inscription</h1>
              <p class="text-muted">Modifiez les informations du dossier d’inscription avant validation.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inscriptions">Dossiers d’inscription</a></li>
                <li class="breadcrumb-item active">Modifier inscription</li>
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
            <div class="col-lg-8">
              <div class="card card-outline card-primary">
                <div class="card-header">
                  <h3 class="card-title">Modifier l’inscription de <?= htmlspecialchars(\App\Core\PersonName::format($student)) ?></h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/inscriptions/update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="eleve_id" value="<?= (int) $student['id'] ?>">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($oldInput['nom'] ?? $student['nom']) ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Postnom</label>
                        <input type="text" name="postnom" class="form-control" required value="<?= htmlspecialchars($oldInput['postnom'] ?? $student['postnom']) ?>">
                      </div>
                    </fieldset>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($oldInput['prenom'] ?? $student['prenom']) ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Genre</label>
                        <select name="genre" class="form-select" required>
                          <option value="">Sélectionnez</option>
                          <option value="M" <?= (($oldInput['genre'] ?? $student['genre']) === 'M') ? 'selected' : '' ?>>Masculin</option>
                          <option value="F" <?= (($oldInput['genre'] ?? $student['genre']) === 'F') ? 'selected' : '' ?>>Féminin</option>
                        </select>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control" required value="<?= htmlspecialchars($oldInput['date_naissance'] ?? $student['date_naissance']) ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Matricule</label>
                        <input type="text" name="matricule" class="form-control" value="<?= htmlspecialchars($oldInput['matricule'] ?? $student['matricule']) ?>" placeholder="Si vide, un matricule sera généré automatiquement">
                        <div class="form-text">Le matricule doit contenir 4 lettres majuscules suivies de 5 chiffres (ex. ABCD12345).</div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Section <span class="text-danger">*</span></label>
                        <select id="edit-section-select" name="section_id" class="form-select" required>
                          <option value="">Sélectionnez une section</option>
                          <?php foreach ($sections as $section): ?>
                            <option value="<?= (int) $section['id'] ?>" <?= $selectedSectionId === (int) $section['id'] ? 'selected' : '' ?>><?= htmlspecialchars($section['nom_section']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Option <span class="text-danger">*</span></label>
                        <select id="edit-option-select" name="option_id" class="form-select" required>
                          <option value="">Sélectionnez une option</option>
                          <?php foreach ($options as $option): ?>
                            <option value="<?= (int) $option['id'] ?>" data-section-id="<?= (int) ($option['section_id'] ?? 0) ?>" <?= $selectedOptionId === (int) $option['id'] ? 'selected' : '' ?>><?= htmlspecialchars($option['nom_option']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Classe <span class="text-danger">*</span></label>
                        <select id="edit-classe-select" name="classe_id" class="form-select" required>
                          <option value="">Sélectionnez une classe</option>
                          <?php foreach ($classes as $classe): ?>
                            <option value="<?= (int) $classe['id'] ?>" data-section-id="<?= (int) ($classe['section_id'] ?? 0) ?>" data-option-id="<?= (int) ($classe['option_id'] ?? 0) ?>" <?= $selectedClasseId === (int) $classe['id'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars($classe['nom_classe']) ?>
                              <?php if (!empty($classe['nom_section'])): ?> - <?= htmlspecialchars($classe['nom_section']) ?><?php endif; ?>
                              <?php if (!empty($classe['nom_option'])): ?> (<?= htmlspecialchars($classe['nom_option']) ?>)<?php endif; ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Parent lié</label>
                      <select id="edit-parent-select" name="parent_id" class="form-select">
                        <option value="">Aucun parent lié</option>
                        <?php foreach ($parents as $parent): ?>
                          <option
                            value="<?= (int) $parent['id'] ?>"
                            data-parent-name="<?= htmlspecialchars($parent['nom_responsable'], ENT_QUOTES, 'UTF-8') ?>"
                            data-parent-phone="<?= htmlspecialchars($parent['telephone'], ENT_QUOTES, 'UTF-8') ?>"
                            data-parent-email="<?= htmlspecialchars($parent['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            <?= $selectedParentId === (int) $parent['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($parent['nom_responsable']) ?> - <?= htmlspecialchars($parent['telephone']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <small class="form-text text-muted">Choisissez la fiche parent à rattacher. Les changements ci-dessous s’appliqueront à tous les enfants liés à cette fiche.</small>
                    </div>
                    <fieldset id="edit-parent-details" class="border rounded p-3 mb-3" <?= $selectedParentId <= 0 ? 'disabled' : '' ?>>
                      <legend class="float-none w-auto px-2 fs-6">Coordonnées du parent/tuteur</legend>
                      <div class="row">
                        <div class="col-md-4 mb-2">
                          <label for="edit-parent-name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                          <input id="edit-parent-name" type="text" name="parent_nom_responsable" class="form-control" maxlength="150" required value="<?= htmlspecialchars($oldInput['parent_nom_responsable'] ?? ($selectedParent['nom_responsable'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4 mb-2">
                          <label for="edit-parent-phone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                          <input id="edit-parent-phone" type="tel" name="parent_telephone" class="form-control" maxlength="25" required value="<?= htmlspecialchars($oldInput['parent_telephone'] ?? ($selectedParent['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4 mb-2">
                          <label for="edit-parent-email" class="form-label">Email</label>
                          <input id="edit-parent-email" type="email" name="parent_email" class="form-control" maxlength="100" value="<?= htmlspecialchars($oldInput['parent_email'] ?? ($selectedParent['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                      </div>
                      <small class="form-text text-muted">L’email est facultatif. Le téléphone doit être valide et unique dans l’école.</small>
                    </fieldset>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nom du père</label>
                        <input type="text" name="nom_pere" class="form-control" value="<?= htmlspecialchars($oldInput['nom_pere'] ?? $student['nom_pere']) ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nom de la mère</label>
                        <input type="text" name="nom_mere" class="form-control" value="<?= htmlspecialchars($oldInput['nom_mere'] ?? $student['nom_mere']) ?>">
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
                    <input type="hidden" name="secteur" value="<?= htmlspecialchars($oldInput['secteur'] ?? $student['secteur'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="groupement" value="<?= htmlspecialchars($oldInput['groupement'] ?? $student['groupement'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="village" value="<?= htmlspecialchars($oldInput['village'] ?? $student['village'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Numéro permanent</label>
                        <input type="text" name="num_permanent" class="form-control" value="<?= htmlspecialchars($oldInput['num_permanent'] ?? $student['num_permanent']) ?>" placeholder="Numéro permanent MINEDUB">
                      </div>
                    </div>
                    <div id="origin-location-error" class="alert alert-danger d-none" role="alert"></div>
                    <div class="mb-3">
                      <label class="form-label">Lieu de naissance</label>
                      <input type="text" name="lieu_naissance" class="form-control" value="<?= htmlspecialchars($oldInput['lieu_naissance'] ?? $student['lieu_naissance']) ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Adresse</label>
                      <textarea name="adresse" class="form-control" rows="4"><?= htmlspecialchars($oldInput['adresse'] ?? $student['adresse']) ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Photo de l'élève (PNG/JPEG, ≤250KB)</label>
                      <input type="file" name="photo" accept="image/png,image/jpeg" class="form-control">
                      <?php if (!empty($student['photo'])): ?>
                        <div class="mt-2"><img src="<?= htmlspecialchars($student['photo']) ?>" alt="Photo élève" width="96" height="96" style="object-fit:cover;border-radius:6px"></div>
                      <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="<?= BASE_URL ?>/inscriptions" class="btn btn-secondary">Retour aux dossiers</a>
                      <button type="submit" class="btn btn-primary">Mettre à jour</button>
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
          const parentSelect = document.getElementById('edit-parent-select');
          const parentDetails = document.getElementById('edit-parent-details');
          const parentName = document.getElementById('edit-parent-name');
          const parentPhone = document.getElementById('edit-parent-phone');
          const parentEmail = document.getElementById('edit-parent-email');
          if (parentSelect && parentDetails) {
            parentSelect.addEventListener('change', function () {
              const selected = parentSelect.options[parentSelect.selectedIndex];
              const hasParent = Boolean(parentSelect.value);
              parentDetails.disabled = !hasParent;
              if (hasParent) {
                parentName.value = selected.dataset.parentName || '';
                parentPhone.value = selected.dataset.parentPhone || '';
                parentEmail.value = selected.dataset.parentEmail || '';
              } else {
                parentName.value = '';
                parentPhone.value = '';
                parentEmail.value = '';
              }
            });
          }

          const sectionSelect = document.getElementById('edit-section-select');
          const optionSelect = document.getElementById('edit-option-select');
          const classSelect = document.getElementById('edit-classe-select');
          if (!sectionSelect || !optionSelect || !classSelect) return;

          const classOptions = Array.from(classSelect.options).slice(1).map((option) => ({
            value: option.value,
            label: option.textContent,
            sectionId: option.dataset.sectionId,
            optionId: option.dataset.optionId
          }));
          const academicOptions = Array.from(optionSelect.options).slice(1).map((option) => ({
            value: option.value,
            label: option.textContent,
            sectionId: option.dataset.sectionId
          }));

          function filterOptions() {
            const sectionId = sectionSelect.value;
            const currentValue = optionSelect.value;
            optionSelect.innerHTML = '<option value="">Sélectionnez une option</option>';
            academicOptions
              .filter((item) => !sectionId || item.sectionId === sectionId)
              .forEach((item) => {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.label;
                optionSelect.appendChild(option);
              });
            if (academicOptions.some((item) => item.value === currentValue && (!sectionId || item.sectionId === sectionId))) {
              optionSelect.value = currentValue;
            }
          }

          function filterClasses() {
            const sectionId = sectionSelect.value;
            const optionId = optionSelect.value;
            const currentValue = classSelect.value;
            classSelect.innerHTML = '<option value="">Sélectionnez une classe</option>';
            classOptions
              .filter((item) => {
                if (!sectionId || !optionId) return true;
                return item.sectionId === sectionId && item.optionId === optionId;
              })
              .forEach((item) => {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.label;
                classSelect.appendChild(option);
              });
            if (classOptions.some((item) => item.value === currentValue && (!sectionId || !optionId || (item.sectionId === sectionId && item.optionId === optionId)))) {
              classSelect.value = currentValue;
            }
          }

          sectionSelect.addEventListener('change', function () { filterOptions(); filterClasses(); });
          optionSelect.addEventListener('change', filterClasses);
          filterOptions();
          filterClasses();
        });
      </script>
      <script>
        (function () {
          const baseUrl = '<?= BASE_URL ?>';
          const provinceSelect = document.getElementById('province-select');
          const territoireSelect = document.getElementById('territoire-select');
          const territoryLabel = document.getElementById('territory-label');
          const locationError = document.getElementById('origin-location-error');
          const legacyFields = ['secteur', 'groupement', 'village'].map(function (name) {
            return document.querySelector('input[name="' + name + '"]');
          });

          function populate(select, items, selected, placeholder, labelItems) {
            select.innerHTML = '';
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = placeholder;
            select.appendChild(emptyOption);
            items.forEach(function (it) {
              const opt = document.createElement('option');
              opt.value = it.nom || it;
              opt.textContent = labelItems && it.nom ? it.nom + ' — Chef-lieu : ' + it.chef_lieu : (it.nom || it);
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

          const initialProvince = <?= json_encode((string) ($oldInput['province_origine'] ?? $student['province_origine'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
          const initialTerritoire = <?= json_encode((string) ($oldInput['territoire'] ?? $student['territoire'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
          let restoredTerritory = initialTerritoire;

          fetchJson(baseUrl + '/inscriptions/provinces').then(function (data) {
            const province = initialProvince === 'Mai-Ndombe' ? 'Maï-Ndombe' : initialProvince;
            populate(provinceSelect, data, province || null, 'Sélectionnez une province', true);
            updateTerritories(province, restoredTerritory);
            restoredTerritory = '';
          }).catch(function (error) {
            console.error(error);
            locationError.textContent = 'Impossible de charger les provinces. Rechargez la page ou contactez l’administration.';
            locationError.classList.remove('d-none');
          });

          provinceSelect && provinceSelect.addEventListener('change', function () {
            legacyFields.forEach(function (field) {
              if (field) field.value = '';
            });
            updateTerritories(this.value, '');
          });

          territoireSelect && territoireSelect.addEventListener('change', function () {
            legacyFields.forEach(function (field) {
              if (field) field.value = '';
            });
          });

          function updateTerritories(province, selected) {
            const isKinshasa = province === 'Kinshasa';
            const placeholder = isKinshasa ? 'Sélectionnez une commune' : 'Sélectionnez un territoire';
            territoryLabel.textContent = isKinshasa ? 'Commune' : 'Territoire';
            populate(territoireSelect, [], null, province ? placeholder : 'Sélectionnez d’abord une province');
            locationError.classList.add('d-none');
            if (!province) return;

            fetchJson(baseUrl + '/inscriptions/territoires?province=' + encodeURIComponent(province)).then(function (data) {
              populate(territoireSelect, data, selected || null, placeholder, false);
            }).catch(function (error) {
              console.error(error);
              locationError.textContent = 'Impossible de charger les territoires ou les communes de cette province.';
              locationError.classList.remove('d-none');
            });
          }
        })();
      </script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
