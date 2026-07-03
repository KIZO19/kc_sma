<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$parents = $parents ?? [];
$oldInput = $oldInput ?? [];
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Nouvelle inscription</h1>
              <p class="text-muted">Créez un dossier d'élève en attente de validation par le secrétaire.</p>
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
            <div class="col-lg-8">
              <div class="card card-outline card-success">
                <div class="card-header">
                  <h3 class="card-title">Formulaire d’inscription</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/inscriptions/submit" autocomplete="off" enctype="multipart/form-data">
                    <?php if (!empty($selectedSection)): ?>
                      <div class="alert alert-info">
                        Section sélectionnée : <strong><?= htmlspecialchars($selectedSection['nom_section']) ?></strong>
                        <?php if (!empty($selectedOption)): ?>
                          <br>Option sélectionnée : <strong><?= htmlspecialchars($selectedOption['nom_option']) ?></strong>
                        <?php endif; ?>
                      </div>
                      <input type="hidden" name="section_id" value="<?= (int) $selectedSection['id'] ?>">
                      <?php if (!empty($selectedOption)): ?>
                        <input type="hidden" name="option_id" value="<?= (int) $selectedOption['id'] ?>">
                      <?php endif; ?>
                    <?php endif; ?>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nom</label>
                        <input id="nom-input" type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($oldInput['nom'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Postnom</label>
                        <input id="postnom-input" type="text" name="postnom" class="form-control" required value="<?= htmlspecialchars($oldInput['postnom'] ?? '') ?>">
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom</label>
                        <input id="prenom-input" type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($oldInput['prenom'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Genre</label>
                        <select name="genre" class="form-select" required>
                          <option value="">Sélectionnez</option>
                          <option value="M" <?= ($oldInput['genre'] ?? '') === 'M' ? 'selected' : '' ?>>Masculin</option>
                          <option value="F" <?= ($oldInput['genre'] ?? '') === 'F' ? 'selected' : '' ?>>Féminin</option>
                        </select>
                      </div>
                    </div>

                    <div class="row align-items-end">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Date de naissance</label>
                        <input id="date-naissance-input" type="date" name="date_naissance" class="form-control" required value="<?= htmlspecialchars($oldInput['date_naissance'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Matricule (optionnel)</label>
                        <div class="input-group">
                          <input id="matricule-input" type="text" name="matricule" class="form-control" placeholder="Généré automatiquement si vide" value="<?= htmlspecialchars($oldInput['matricule'] ?? '') ?>">
                          <button id="generate-matricule-btn" type="button" class="btn btn-outline-secondary">Aperçu</button>
                        </div>
                        <div id="matricule-preview" class="form-text text-muted mt-1">Aperçu du matricule généré disponible après saisie du nom et du postnom.</div>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Classe</label>
                      <select name="classe_id" class="form-select" required>
                        <option value="">Sélectionnez une classe</option>
                        <?php if (!empty($classes)): ?>
                          <?php foreach ($classes as $classe): ?>
                            <option value="<?= (int) $classe['id'] ?>" <?= ((int) ($oldInput['classe_id'] ?? 0) === (int) $classe['id']) ? 'selected' : '' ?>>
                              <?= htmlspecialchars($classe['nom_classe']) ?>
                              <?php if (!empty($classe['nom_section'])): ?>
                                - <?= htmlspecialchars($classe['nom_section']) ?>
                              <?php endif; ?>
                              <?php if (!empty($classe['nom_option'])): ?>
                                <?= htmlspecialchars($classe['nom_option']) ? ' (' . htmlspecialchars($classe['nom_option']) . ')' : '' ?>
                              <?php endif; ?>
                            </option>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <option value="">Aucune classe disponible</option>
                        <?php endif; ?>
                      </select>
                      <small class="form-text text-muted">Sélectionnez la classe où l’élève doit être inscrit.</small>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Parent / tuteur</label>
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
                          <input type="text" name="new_parent_nom_responsable" class="form-control" value="<?= htmlspecialchars($oldInput['new_parent_nom_responsable'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Téléphone du parent/tuteur</label>
                          <input type="text" name="new_parent_telephone" class="form-control" value="<?= htmlspecialchars($oldInput['new_parent_telephone'] ?? '') ?>">
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Email du parent/tuteur</label>
                          <input type="email" name="new_parent_email" class="form-control" value="<?= htmlspecialchars($oldInput['new_parent_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Note</label>
                          <textarea class="form-control" rows="3" readonly>Remplissez ces champs uniquement si le parent n’existe pas dans la liste ci-dessus.</textarea>
                        </div>
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
                        <label class="form-label">Territoire</label>
                        <select name="territoire" id="territoire-select" class="form-select">
                          <option value="">Sélectionnez un territoire</option>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Secteur</label>
                        <select name="secteur" id="secteur-select" class="form-select">
                          <option value="">Sélectionnez un secteur</option>
                        </select>

                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Groupement</label>
                        <select name="groupement" id="groupement-select" class="form-select">
                          <option value="">Sélectionnez un groupement</option>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Village</label>
                        <select name="village" id="village-select" class="form-select">
                          <option value="">Sélectionnez un village</option>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Numéro permanent</label>
                        <input type="text" name="num_permanent" class="form-control" value="<?= htmlspecialchars($oldInput['num_permanent'] ?? '') ?>" placeholder="Numéro permanent MINEDUB">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Lieu de naissance</label>
                      <input type="text" name="lieu_naissance" class="form-control" value="<?= htmlspecialchars($oldInput['lieu_naissance'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Adresse</label>
                      <textarea name="adresse" class="form-control" rows="4"><?= htmlspecialchars($oldInput['adresse'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Photo de l'élève (PNG/JPEG, ≤250KB)</label>
                      <input type="file" name="photo" accept="image/png,image/jpeg" class="form-control">
                      <small class="form-text text-muted">Optionnel — téléversez une photo de l'élève.</small>
                    </div>
                    <div class="d-flex justify-content-between">
                      <a href="<?= BASE_URL ?>/inscriptions" class="btn btn-secondary">Retour aux dossiers</a>
                      <button type="submit" class="btn btn-success">Enregistrer</button>
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
          const existingRadio = document.getElementById('parent-choice-existing');
          const newRadio = document.getElementById('parent-choice-new');
          const existingSection = document.querySelector('.parent-choice-existing-section');
          const newSection = document.querySelector('.parent-choice-new-section');

          function toggleParentSections() {
            const useNew = newRadio && newRadio.checked;
            if (existingSection) {
              existingSection.classList.toggle('d-none', useNew);
            }
            if (newSection) {
              newSection.classList.toggle('d-none', !useNew);
            }
          }

          if (existingRadio) {
            existingRadio.addEventListener('change', toggleParentSections);
          }
          if (newRadio) {
            newRadio.addEventListener('change', toggleParentSections);
          }

          toggleParentSections();
        });
      </script>
      <script>
        (function () {
          const baseUrl = '<?= BASE_URL ?>';
          const provinceSelect = document.getElementById('province-select');
          const territoireSelect = document.getElementById('territoire-select');
          const secteurSelect = document.getElementById('secteur-select');
          const groupementSelect = document.getElementById('groupement-select');
          const villageSelect = document.getElementById('village-select');
          const matriculePreviewText = document.getElementById('matricule-preview');
          const nomInput = document.getElementById('nom-input');
          const postnomInput = document.getElementById('postnom-input');
          const prenomInput = document.getElementById('prenom-input');
          const matriculeButton = document.getElementById('generate-matricule-btn');

          function populate(select, items, selected) {
            select.innerHTML = '<option value="">Sélectionnez</option>';
            items.forEach(function (it) {
              const opt = document.createElement('option');
              opt.value = it;
              opt.textContent = it;
              if (selected && selected === it) opt.selected = true;
              select.appendChild(opt);
            });
          }

          function fetchJson(url) {
            return fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).catch(function () {
              try {
                const u = new URL(url, location.origin);
                const pathname = u.pathname || '';
                const params = Object.fromEntries(u.searchParams.entries());
                if (pathname.endsWith('/inscriptions/provinces')) return Promise.resolve(LOCAL_LOCATIONS.provinces || []);
                if (pathname.endsWith('/inscriptions/territoires')) {
                  return Promise.resolve(LOCAL_LOCATIONS.territoires[params.province] || []);
                }
                if (pathname.endsWith('/inscriptions/secteurs')) {
                  return Promise.resolve(LOCAL_LOCATIONS.secteurs[params.territoire] || []);
                }
                if (pathname.endsWith('/inscriptions/groupements')) {
                  return Promise.resolve(LOCAL_LOCATIONS.groupements[params.secteur] || []);
                }
                if (pathname.endsWith('/inscriptions/villages')) {
                  return Promise.resolve(LOCAL_LOCATIONS.villages[params.groupement] || []);
                }
              } catch (e) {
                // ignore
              }
              return Promise.resolve([]);
            });
          }

          const LOCAL_LOCATIONS = {
            provinces: ['Kinshasa','Nord-Kivu','Sud-Kivu','Haut-Katanga','Kongo Central','Équateur','Maniema','Ituri'],
            territoires: {
              'Kinshasa': ['Funa','Kalamu','Kasa-Vubu','Lingwala'],
              'Nord-Kivu': ['Goma','Beni','Masisi','Rutshuru'],
              'Sud-Kivu': ['Uvira','Bukavu','Fizi'],
              'Haut-Katanga': ['Lubumbashi','Kambove','Likasi'],
              'Kongo Central': ['Matadi','Boma','Kimpese']
            },
            secteurs: {
              'Goma': ['Sector 1','Sector 2'],
              'Beni': ['Beni-Centre','Mabalako'],
              'Lubumbashi': ['Kawama','Katanga-Centre']
            },
            groupements: {
              'Sector 1': ['Group A','Group B'],
              'Beni-Centre': ['Group X','Group Y']
            },
            villages: {
              'Group A': ['Village 1','Village 2'],
              'Group X': ['Village X1','Village X2']
            }
          };

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

          // Load provinces
          fetchJson(baseUrl + '/inscriptions/provinces').then(function (data) {
            const sel = '<?= htmlspecialchars($oldInput['province_origine'] ?? '') ?>';
            populate(provinceSelect, data, sel || null);
            if (sel) provinceSelect.dispatchEvent(new Event('change'));
          }).catch(()=>{});

          provinceSelect && provinceSelect.addEventListener('change', function () {
            const val = this.value;
            populate(territoireSelect, [], null);
            populate(secteurSelect, [], null);
            populate(groupementSelect, [], null);
            populate(villageSelect, [], null);
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/territoires?province=' + encodeURIComponent(val)).then(function (data) {
              const sel = '<?= htmlspecialchars($oldInput['territoire'] ?? '') ?>';
              populate(territoireSelect, data, sel || null);
              if (sel) territoireSelect.dispatchEvent(new Event('change'));
            }).catch(()=>{});
          });

          territoireSelect && territoireSelect.addEventListener('change', function () {
            const val = this.value;
            populate(secteurSelect, [], null);
            populate(groupementSelect, [], null);
            populate(villageSelect, [], null);
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/secteurs?territoire=' + encodeURIComponent(val)).then(function (data) {
              const sel = '<?= htmlspecialchars($oldInput['secteur'] ?? '') ?>';
              populate(secteurSelect, data, sel || null);
              if (sel) secteurSelect.dispatchEvent(new Event('change'));
            }).catch(()=>{});
          });

          secteurSelect && secteurSelect.addEventListener('change', function () {
            const val = this.value;
            populate(groupementSelect, [], null);
            populate(villageSelect, [], null);
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/groupements?secteur=' + encodeURIComponent(val)).then(function (data) {
              const sel = '<?= htmlspecialchars($oldInput['groupement'] ?? '') ?>';
              populate(groupementSelect, data, sel || null);
              if (sel) groupementSelect.dispatchEvent(new Event('change'));
            }).catch(()=>{});
          });

          groupementSelect && groupementSelect.addEventListener('change', function () {
            const val = this.value;
            populate(villageSelect, [], null);
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/villages?groupement=' + encodeURIComponent(val)).then(function (data) {
              const sel = '<?= htmlspecialchars($oldInput['village'] ?? '') ?>';
              populate(villageSelect, data, sel || null);
            }).catch(()=>{});
          });
        })();
      </script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
