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
                  <form method="post" action="<?= BASE_URL ?>/inscriptions/submit" autocomplete="off">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($oldInput['nom'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Postnom</label>
                        <input type="text" name="postnom" class="form-control" required value="<?= htmlspecialchars($oldInput['postnom'] ?? '') ?>">
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($oldInput['prenom'] ?? '') ?>">
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

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control" required value="<?= htmlspecialchars($oldInput['date_naissance'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Matricule (optionnel)</label>
                        <input type="text" name="matricule" class="form-control" placeholder="Généré automatiquement si vide" value="<?= htmlspecialchars($oldInput['matricule'] ?? '') ?>">
                      </div>
                    </div>

                    <div class="mb-3">
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
                        <input type="text" name="province_origine" class="form-control" value="<?= htmlspecialchars($oldInput['province_origine'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Territoire</label>
                        <input type="text" name="territoire" class="form-control" value="<?= htmlspecialchars($oldInput['territoire'] ?? '') ?>">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Secteur</label>
                        <input type="text" name="secteur" class="form-control" value="<?= htmlspecialchars($oldInput['secteur'] ?? '') ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Groupement</label>
                        <input type="text" name="groupement" class="form-control" value="<?= htmlspecialchars($oldInput['groupement'] ?? '') ?>">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Village</label>
                        <input type="text" name="village" class="form-control" value="<?= htmlspecialchars($oldInput['village'] ?? '') ?>">
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
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
