<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$parents = $parents ?? [];
$oldInput = $oldInput ?? [];
$student = $student ?? [];
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
                  <h3 class="card-title">Modifier l’inscription de <?= htmlspecialchars($student['nom'] . ' ' . ($student['prenom'] ?? '')) ?></h3>
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
                    </div>

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
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Parent lié</label>
                      <select name="parent_id" class="form-select">
                        <option value="">Aucun parent lié</option>
                        <?php foreach ($parents as $parent): ?>
                          <option value="<?= (int) $parent['id'] ?>" <?= ((int) ($oldInput['parent_id'] ?? $student['parent_id']) === (int) $parent['id']) ? 'selected' : '' ?> >
                            <?= htmlspecialchars($parent['nom_responsable']) ?> - <?= htmlspecialchars($parent['telephone']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <small class="form-text text-muted">Sélectionnez un parent existant pour lier l’élève.</small>
                    </div>

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
                        <input type="text" name="num_permanent" class="form-control" value="<?= htmlspecialchars($oldInput['num_permanent'] ?? $student['num_permanent']) ?>" placeholder="Numéro permanent MINEDUB">
                      </div>
                    </div>
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
        (function () {
          const baseUrl = '<?= BASE_URL ?>';
          const provinceSelect = document.getElementById('province-select');
          const territoireSelect = document.getElementById('territoire-select');
          const secteurSelect = document.getElementById('secteur-select');
          const groupementSelect = document.getElementById('groupement-select');
          const villageSelect = document.getElementById('village-select');

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
            return fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); });
          }

          const initialProvince = '<?= htmlspecialchars($oldInput['province_origine'] ?? $student['province_origine'] ?? '') ?>';
          const initialTerritoire = '<?= htmlspecialchars($oldInput['territoire'] ?? $student['territoire'] ?? '') ?>';
          const initialSecteur = '<?= htmlspecialchars($oldInput['secteur'] ?? $student['secteur'] ?? '') ?>';
          const initialGroupement = '<?= htmlspecialchars($oldInput['groupement'] ?? $student['groupement'] ?? '') ?>';
          const initialVillage = '<?= htmlspecialchars($oldInput['village'] ?? $student['village'] ?? '') ?>';

          fetchJson(baseUrl + '/inscriptions/provinces').then(function (data) {
            populate(provinceSelect, data, initialProvince || null);
            if (initialProvince) provinceSelect.dispatchEvent(new Event('change'));
          }).catch(()=>{});

          provinceSelect && provinceSelect.addEventListener('change', function () {
            const val = this.value;
            populate(territoireSelect, [], null);
            populate(secteurSelect, [], null);
            populate(groupementSelect, [], null);
            populate(villageSelect, [], null);
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/territoires?province=' + encodeURIComponent(val)).then(function (data) {
              populate(territoireSelect, data, initialTerritoire || null);
              if (initialTerritoire) territoireSelect.dispatchEvent(new Event('change'));
            }).catch(()=>{});
          });

          territoireSelect && territoireSelect.addEventListener('change', function () {
            const val = this.value;
            populate(secteurSelect, [], null);
            populate(groupementSelect, [], null);
            populate(villageSelect, [], null);
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/secteurs?territoire=' + encodeURIComponent(val)).then(function (data) {
              populate(secteurSelect, data, initialSecteur || null);
              if (initialSecteur) secteurSelect.dispatchEvent(new Event('change'));
            }).catch(()=>{});
          });

          secteurSelect && secteurSelect.addEventListener('change', function () {
            const val = this.value;
            populate(groupementSelect, [], null);
            populate(villageSelect, [], null);
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/groupements?secteur=' + encodeURIComponent(val)).then(function (data) {
              populate(groupementSelect, data, initialGroupement || null);
              if (initialGroupement) groupementSelect.dispatchEvent(new Event('change'));
            }).catch(()=>{});
          });

          groupementSelect && groupementSelect.addEventListener('change', function () {
            const val = this.value;
            populate(villageSelect, [], null);
            if (!val) return;
            fetchJson(baseUrl + '/inscriptions/villages?groupement=' + encodeURIComponent(val)).then(function (data) {
              populate(villageSelect, data, initialVillage || null);
            }).catch(()=>{});
          });
        })();
      </script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
