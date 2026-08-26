<?php require __DIR__ . '/../partials/app_header.php'; ?>
          <?php
          $classes = $classes ?? [];
          $years = $years ?? [];
          $currencies = $currencies ?? [];
          $schoolCurrency = $schoolCurrency ?? 'USD';
          $oldInput = $oldInput ?? [];
          $sections = $sections ?? [];
          $options = $options ?? [];
          $defaultYearId = $defaultYearId ?? 0;
          ?>
          <section class="content-header">
            <div class="container-fluid">
              <div class="row mb-2">
                <div class="col-sm-6">
                  <h1>Nouveau frais</h1>
                  <p class="text-muted">Créez un nouveau frais scolaire pour une classe et une année.</p>
                </div>
                <div class="col-sm-6">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/frais">Frais</a></li>
                    <li class="breadcrumb-item active">Nouveau frais</li>
                  </ol>
                </div>
              </div>
            </div>
          </section>

          <section class="content">
            <div class="container-fluid">
              <?php if (!empty($_SESSION['frais_errors'])): ?>
                <div class="alert alert-danger alert-dismissible">
                  <ul class="mb-0">
                    <?php foreach ($_SESSION['frais_errors'] as $error): ?>
                      <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
                <?php unset($_SESSION['frais_errors']); ?>
              <?php endif; ?>

              <div class="row justify-content-center">
                <div class="col-lg-8">
                  <div class="card card-outline card-success">
                    <div class="card-header">
                      <h3 class="card-title">Création d’un frais</h3>
                    </div>
                    <div class="card-body">
                      <div id="clientErrorContainer"></div>
                      <form id="fraisForm" method="post" action="<?= BASE_URL ?>/frais/submit" autocomplete="off">
                        <div class="mb-3">
                          <label class="form-label">Type de frais</label>
                          <input type="text" name="type_frais" class="form-control" required value="<?= htmlspecialchars($oldInput['type_frais'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Montant total</label>
                          <input type="number" name="montant_total" class="form-control" required step="0.01" min="0" value="<?= htmlspecialchars($oldInput['montant_total'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Encodage (code unique)</label>
                          <div class="input-group">
                            <input id="encodageInput" type="text" name="encodage" class="form-control" required value="<?= htmlspecialchars($oldInput['encodage'] ?? ($suggestedEncodage ?? '')) ?>" placeholder="ex: FRAIS-2026-001">
                            <button id="regenEncodage" type="button" class="btn btn-outline-secondary" title="Régénérer">Régénérer</button>
                          </div>
                          <?php if (empty($oldInput['encodage'] ?? '') && !empty($suggestedEncodage)): ?>
                            <div class="form-text text-muted mt-1"><span id="encodageHint">(généré automatiquement)</span></div>
                          <?php endif; ?>
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Portée</label>
                          <select id="scopeSelect" name="scope" class="form-select">
                            <option value="class" <?= (($oldInput['scope'] ?? 'class') === 'class') ? 'selected' : '' ?>>Classe</option>
                            <option value="option" <?= (($oldInput['scope'] ?? '') === 'option') ? 'selected' : '' ?>>Option</option>
                            <option value="section" <?= (($oldInput['scope'] ?? '') === 'section') ? 'selected' : '' ?>>Section</option>
                            <option value="school" <?= (($oldInput['scope'] ?? '') === 'school') ? 'selected' : '' ?>>Toutes les options</option>
                          </select>
                        </div>

                        <div class="mb-3" id="classWrapper">
                          <label class="form-label">Classe</label>
                          <select id="classeSelect" name="classe_id" class="form-select">
                            <option value="">Sélectionnez une classe</option>
                            <?php foreach ($classes as $classe): ?>
                              <option value="<?= (int) $classe['id'] ?>" data-section-name="<?= htmlspecialchars($classe['nom_section'] ?? '') ?>" data-option-name="<?= htmlspecialchars($classe['nom_option'] ?? '') ?>" <?= ((int) ($oldInput['classe_id'] ?? 0) === (int) $classe['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($classe['nom_classe']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="mb-3" id="optionWrapper" style="display:none">
                          <label class="form-label">Option</label>
                          <select id="optionSelect" name="option_select" class="form-select">
                            <option value="">Sélectionnez une option</option>
                            <?php foreach ($options as $opt): ?>
                              <option value="<?= (int) $opt['id'] ?>" <?= ((int) ($oldInput['option_id'] ?? 0) === (int) $opt['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($opt['nom_option']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="mb-3" id="sectionWrapper" style="display:none">
                          <label class="form-label">Section</label>
                          <select id="sectionSelect" name="section_select" class="form-select">
                            <option value="">Sélectionnez une section</option>
                            <?php foreach ($sections as $section): ?>
                              <option value="<?= (int) $section['id'] ?>" <?= ((int) ($oldInput['section_id'] ?? 0) === (int) $section['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($section['nom_section']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <input type="hidden" name="scope_id" id="scopeIdHidden" value="<?= htmlspecialchars($oldInput['scope_id'] ?? '') ?>">

                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" id="classeSection" class="form-control" readonly value="">
                            <input type="hidden" name="section_name" id="hiddenSectionName" value="">
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Option</label>
                            <input type="text" id="classeOption" class="form-control" readonly value="">
                            <input type="hidden" name="option_name" id="hiddenOptionName" value="">
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Année scolaire</label>
                          <select name="annee_scolaire_id" class="form-select" required>
                            <option value="">Sélectionnez une année scolaire</option>
                            <?php foreach ($years as $year): ?>
                              <option value="<?= (int) $year['id'] ?>" <?= ((int) ($oldInput['annee_scolaire_id'] ?? $defaultYearId) === (int) $year['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['annee']) ?><?php if ((int) $year['id'] === (int) $defaultYearId): ?> - par défaut<?php endif; ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <?php if (!empty($years) && !empty($defaultYearId)): ?>
                            <div class="form-text">L’année préselectionnée est celle par défaut de l’école.</div>
                          <?php endif; ?>
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Devise</label>
                          <select name="devise" class="form-select" required>
                            <option value="">Sélectionnez une devise</option>
                            <?php foreach ($currencies as $code => $label): ?>
                              <option value="<?= htmlspecialchars($code) ?>" <?= (($oldInput['devise'] ?? $schoolCurrency) === $code) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="d-flex justify-content-between">
                          <a href="<?= BASE_URL ?>/frais" class="btn btn-secondary">Retour à la liste</a>
                          <button type="submit" class="btn btn-success">Créer le frais</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <?php require __DIR__ . '/../partials/app_footer.php'; ?>

          <script>
          document.addEventListener('DOMContentLoaded', function () {
            const classeSelect = document.getElementById('classeSelect');
            const classeSection = document.getElementById('classeSection');
            const classeOption = document.getElementById('classeOption');
            const hiddenSection = document.getElementById('hiddenSectionName');
            const hiddenOption = document.getElementById('hiddenOptionName');
            const scopeSelect = document.getElementById('scopeSelect');
            const optionSelect = document.getElementById('optionSelect');
            const sectionSelect = document.getElementById('sectionSelect');
            const scopeIdHidden = document.getElementById('scopeIdHidden');
            const classWrapper = document.getElementById('classWrapper');
            const optionWrapper = document.getElementById('optionWrapper');
            const sectionWrapper = document.getElementById('sectionWrapper');

            if (!classeSelect) return;

            function updateMeta() {
              const opt = classeSelect.options[classeSelect.selectedIndex];
              const sectionName = opt ? (opt.dataset.sectionName || '') : '';
              const optionName = opt ? (opt.dataset.optionName || '') : '';
              classeSection.value = sectionName;
              classeOption.value = optionName;
              if (hiddenSection) hiddenSection.value = sectionName;
              if (hiddenOption) hiddenOption.value = optionName;
              // keep scopeId in sync when scope is class
              if (scopeSelect && scopeSelect.value === 'class') {
                scopeIdHidden.value = classeSelect.value || '';
              }
            }

            function updateVisibility() {
              const scope = scopeSelect ? scopeSelect.value : 'class';
              classWrapper.style.display = scope === 'class' ? '' : 'none';
              optionWrapper.style.display = scope === 'option' ? '' : 'none';
              sectionWrapper.style.display = scope === 'section' ? '' : 'none';
              // set hidden scope id from visible control
              if (scope === 'class') {
                scopeIdHidden.value = classeSelect.value || '';
              } else if (scope === 'option') {
                scopeIdHidden.value = optionSelect ? optionSelect.value || '' : '';
              } else if (scope === 'section') {
                scopeIdHidden.value = sectionSelect ? sectionSelect.value || '' : '';
              } else {
                scopeIdHidden.value = '';
              }
            }

            if (scopeSelect) {
              scopeSelect.addEventListener('change', updateVisibility);
            }
            if (optionSelect) optionSelect.addEventListener('change', updateVisibility);
            if (sectionSelect) sectionSelect.addEventListener('change', updateVisibility);
            classeSelect.addEventListener('change', updateMeta);
            // Initialize on load
            updateMeta();
            updateVisibility();

            // Encodage: show generated hint and allow regen by reload
            const encodageInput = document.getElementById('encodageInput');
            const regenBtn = document.getElementById('regenEncodage');
            const encodageHint = document.getElementById('encodageHint');
            if (encodageInput && regenBtn) {
              // hide hint when user types manually
              encodageInput.addEventListener('input', function () {
                if (encodageHint) encodageHint.style.display = encodageInput.value.trim() === '<?= htmlspecialchars($suggestedEncodage ?? '') ?>' ? '' : 'none';
              });
              regenBtn.addEventListener('click', function () {
                // simple strategy: reload the page to get a new suggestedEncodage from server
                const params = new URLSearchParams(window.location.search);
                // add a cache-busting param
                params.set('_regen', Date.now());
                window.location.search = params.toString();
              });
            }

            // Ensure hidden scope id and trimmed values before submit with client validation
            const fraisForm = document.getElementById('fraisForm');
            if (fraisForm) {
              fraisForm.addEventListener('submit', function (ev) {
                // client-side validation
                const clientErrors = [];
                const typeVal = (fraisForm.querySelector('input[name="type_frais"]') || { value: '' }).value.trim();
                const montantVal = (fraisForm.querySelector('input[name="montant_total"]') || { value: '' }).value.trim();
                const encVal = (document.getElementById('encodageInput') || { value: '' }).value.trim();
                const anneeVal = (fraisForm.querySelector('select[name="annee_scolaire_id"]') || { value: '' }).value;
                const deviseVal = (fraisForm.querySelector('select[name="devise"]') || { value: '' }).value;

                if (typeVal === '') clientErrors.push('Le type de frais est requis.');
                if (montantVal === '' || isNaN(montantVal) || Number(montantVal) < 0) clientErrors.push('Le montant total doit être un nombre positif.');
                if (encVal === '') clientErrors.push("L'encodage est requis.");
                if (/\s/.test(encVal)) clientErrors.push("L'encodage ne doit pas contenir d'espaces.");
                if (!anneeVal || anneeVal === '') clientErrors.push('L\'année scolaire est requise.');
                if (!deviseVal || deviseVal === '') clientErrors.push('La devise est requise.');

                // ensure scopeIdHidden populated
                const scope = scopeSelect ? scopeSelect.value : 'class';
                if (scope === 'class') {
                  scopeIdHidden.value = classeSelect ? classeSelect.value || '' : '';
                  if (!scopeIdHidden.value) clientErrors.push('La classe est requise pour la portée classe.');
                } else if (scope === 'option') {
                  scopeIdHidden.value = optionSelect ? optionSelect.value || '' : '';
                  if (!scopeIdHidden.value) clientErrors.push('L\'option doit être sélectionnée pour la portée option.');
                } else if (scope === 'section') {
                  scopeIdHidden.value = sectionSelect ? sectionSelect.value || '' : '';
                  if (!scopeIdHidden.value) clientErrors.push('La section doit être sélectionnée pour la portée section.');
                } else {
                  scopeIdHidden.value = '';
                }

                if (clientErrors.length > 0) {
                  ev.preventDefault();
                  // render errors into clientErrorContainer
                  const container = document.getElementById('clientErrorContainer');
                  if (container) {
                    container.innerHTML = '<div class="alert alert-danger alert-dismissible" role="alert"><ul class="mb-0">' + clientErrors.map(e => '<li>' + e + '</li>').join('') + '</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button></div>';
                    // scroll into view
                    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                  }
                  return false;
                }

                // trim encodage and type_frais inputs to avoid accidental spaces
                const typeInput = fraisForm.querySelector('input[name="type_frais"]');
                const encInput = document.getElementById('encodageInput');
                if (typeInput) typeInput.value = typeInput.value.trim();
                if (encInput) encInput.value = encInput.value.trim();
                // allow normal submit to proceed
              });
            }

          });
          </script>
