<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="mb-0">Mon profil</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['access_error'])): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['access_error']) ?></div>
            <?php unset($_SESSION['access_error']); ?>
          <?php endif; ?>
          <?php if (!empty($success)): ?>
            <div class="alert alert-success">
              <?= $success === 'agent' ? 'Agent affecté à l’école avec succès.' : 'Profil mis à jour avec succès.' ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Informations personnelles</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/profile/update" enctype="multipart/form-data">
                    <div class="mb-3">
                      <label class="form-label">Photo de profil (avatar)</label>
                      <input type="file" name="avatar" accept="image/png,image/jpeg" class="form-control">
                      <div class="form-text">Formats autorisés: PNG, JPEG. Taille max: 250 KB.</div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nom complet</label>
                      <input type="text" name="nom_complet" class="form-control" value="<?= htmlspecialchars($user['nom_complet'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Identifiant</label>
                      <input type="text" name="identifiant" class="form-control" value="<?= htmlspecialchars($user['identifiant'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Rôle</label>
                      <input type="text" class="form-control" value="<?= htmlspecialchars($roleLabel) ?>" disabled>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nouveau mot de passe</label>
                      <input type="password" name="mot_de_passe" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Confirmer le mot de passe</label>
                      <input type="password" name="mot_de_passe_confirm" class="form-control" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <?php if (($role ?? '') === 'super_admin'): ?>
            <div class="row mt-4">
              <div class="col-lg-8">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Affecter les agents aux écoles</h3>
                  </div>
                  <div class="card-body">
                    <?php if (empty($agents)): ?>
                      <div class="alert alert-info mb-0">Aucun compte agent disponible.</div>
                    <?php elseif (empty($schools)): ?>
                      <div class="alert alert-warning mb-0">Aucune école disponible.</div>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                          <thead>
                            <tr>
                              <th>Agent</th>
                              <th>École actuelle</th>
                              <th>Affecter à</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($agents as $agent): ?>
                              <tr>
                                <td>
                                  <div><?= htmlspecialchars($agent['nom_complet'] ?? '') ?></div>
                                  <small class="text-muted"><?= htmlspecialchars($agent['identifiant'] ?? '') ?></small>
                                </td>
                                <td>
                                  <?php
                                    $currentSchoolName = 'Non affecté';
                                    foreach ($schools as $school) {
                                        if ((int) ($school['id'] ?? 0) === (int) ($agent['ecole_id'] ?? 0)) {
                                            $currentSchoolName = $school['nom_etablissement'] ?? 'École';
                                            break;
                                        }
                                    }
                                  ?>
                                  <?= htmlspecialchars($currentSchoolName) ?>
                                </td>
                                <td>
                                  <form method="post" action="<?= BASE_URL ?>/profile/assign-agent" class="d-flex gap-2">
                                    <input type="hidden" name="agent_id" value="<?= (int) $agent['id'] ?>">
                                    <select name="ecole_id" class="form-select form-select-sm" required>
                                      <option value="">Choisir une école</option>
                                      <?php foreach ($schools as $school): ?>
                                        <option value="<?= (int) $school['id'] ?>" <?= (int) ($agent['ecole_id'] ?? 0) === (int) ($school['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars($school['nom_etablissement'] ?? 'École') ?></option>
                                      <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Affecter</button>
                                  </form>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>