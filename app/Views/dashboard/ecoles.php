<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['ecoles_errors'])): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($_SESSION['ecoles_errors'] as $err): ?>
                  <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php unset($_SESSION['ecoles_errors']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['ecoles_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['ecoles_success']) ?></div>
            <?php unset($_SESSION['ecoles_success']); ?>
          <?php endif; ?>
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="mb-0">Gestion des écoles</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Écoles</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="row mb-4">
            <div class="col-12">
              <p class="text-muted">Super-admin : validez les comptes écoles et gérez les abonnements.</p>
            </div>
          </div>

          <div class="row gy-4">
            <div class="col-lg-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Liste des écoles</h3>
                </div>
                <div class="card-body table-responsive">
                  <table class="table table-hover align-middle">
                    <thead>
                      <tr>
                        <th>Logo</th>
                        <th>École</th>
                        <th>Matricule</th>
                        <th>Email</th>
                        <th>Statut système</th>
                        <th>Abonnement</th>
                        <th>Plan</th>
                        <th>Fin</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($schools as $school): ?>
                        <tr>
                          <td>
                            <?php if (!empty($school['logo_url'])): ?>
                              <img src="<?= htmlspecialchars(BASE_URL . $school['logo_url']) ?>" alt="Logo <?= htmlspecialchars($school['nom_etablissement']) ?>" class="img-thumbnail" style="width: 60px; height: auto;">
                            <?php else: ?>
                              <span class="badge bg-secondary">N/A</span>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($school['nom_etablissement']) ?></td>
                          <td><?= htmlspecialchars($school['matricule'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($school['email_officiel']) ?></td>
                          <td>
                            <span class="badge <?= $school['statut_systeme'] === 'Actif' ? 'bg-success' : ($school['statut_systeme'] === 'Suspendu' ? 'bg-danger' : 'bg-warning') ?>">
                              <?= htmlspecialchars($school['statut_systeme']) ?>
                            </span>
                          </td>
                          <td><?= htmlspecialchars($school['statut_abonnement'] ?? 'Aucun') ?></td>
                          <td><?= htmlspecialchars($school['plan_name'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($school['date_fin'] ?? '-') ?></td>
                          <td>
                            <form method="post" action="<?= BASE_URL ?>/ecoles/updateStatus" class="d-inline">
                              <input type="hidden" name="ecole_id" value="<?= (int) $school['id'] ?>">
                              <select name="statut_systeme" class="form-select form-select-sm d-inline w-auto me-2">
                                <option value="Actif" <?= $school['statut_systeme'] === 'Actif' ? 'selected' : '' ?>>Actif</option>
                                <option value="En_Attente" <?= $school['statut_systeme'] === 'En_Attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="Suspendu" <?= $school['statut_systeme'] === 'Suspendu' ? 'selected' : '' ?>>Suspendu</option>
                              </select>
                              <button type="submit" class="btn btn-sm btn-outline-primary">Valider</button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="card mb-4">
                <div class="card-header">
                  <h3 class="card-title">Demandes en attente</h3>
                </div>
                <div class="card-body">
                  <?php if (!empty($pending)): ?>
                    <ul class="list-group list-group-flush">
                      <?php foreach ($pending as $school): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                          <div>
                            <strong><?= htmlspecialchars($school['nom_etablissement']) ?></strong>
                            <div class="text-muted small"><?= htmlspecialchars($school['email_officiel']) ?></div>
                          </div>
                          <form method="post" action="<?= BASE_URL ?>/ecoles/confirm" class="ms-3">
                            <input type="hidden" name="ecole_id" value="<?= (int) $school['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success">Valider</button>
                          </form>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <p class="text-muted mb-0">Aucune école en attente.</p>
                  <?php endif; ?>
                </div>
              </div>
              <?php if (($role ?? '') === 'super_admin'): ?>
                <div class="card mb-4">
                <div class="card-header">
                  <h3 class="card-title">Créer une école</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/ecoles/create" enctype="multipart/form-data">
                    <div class="mb-3">
                      <label class="form-label">Nom de l'école</label>
                      <input type="text" name="nom_etablissement" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Matricule</label>
                      <input type="text" name="matricule" class="form-control" placeholder="Généré automatiquement si vide">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Email officiel</label>
                      <input type="email" name="email_officiel" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Identifiant</label>
                      <input type="text" name="identifiant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Logo de l'école</label>
                      <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg">
                      <div class="form-text">Formats autorisés: PNG, JPEG. Taille max: 250 KB.</div>
                    </div>
                    <hr>
                    <h5 class="mb-3">Admin de l'école</h5>
                    <div class="mb-3">
                      <label class="form-label">Attribuer un admin existant</label>
                      <select name="existing_admin_id" class="form-select">
                        <option value="0">-- Aucun (créer un nouveau) --</option>
                        <?php foreach (($availableAdmins ?? []) as $adm): ?>
                          <option value="<?= (int) $adm['id'] ?>"><?= htmlspecialchars($adm['nom_complet']) ?> (<?= htmlspecialchars($adm['identifiant']) ?>)</option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Ou créer un nouvel admin</label>
                      <input type="text" name="admin_nom" class="form-control" placeholder="Nom complet de l'admin">
                    </div>
                    <div class="mb-3">
                      <input type="text" name="admin_identifiant" class="form-control" placeholder="Identifiant (email ou téléphone)">
                    </div>
                    <div class="mb-3">
                      <input type="text" name="admin_mot_de_passe" class="form-control" placeholder="Mot de passe (laisser vide pour générer)">
                      <div class="form-text">Si vous ne spécifiez pas de mot de passe, un mot de passe temporaire sera généré.</div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Téléphone</label>
                      <input type="text" name="telephone" class="form-control">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Adresse</label>
                      <input type="text" name="adresse" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Créer l'école</button>
                  </form>
                </div>
              </div>
            <?php endif; ?>

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
                        <?php foreach ($schools as $school): ?>
                          <option value="<?= (int) $school['id'] ?>"><?= htmlspecialchars($school['nom_etablissement']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Plan</label>
                      <select name="plan_id" class="form-select" required>
                        <option value="">Sélectionnez un plan</option>
                        <?php foreach ($plans as $plan): ?>
                          <option value="<?= (int) $plan['id'] ?>"><?= htmlspecialchars($plan['nom_plan']) ?> - <?= htmlspecialchars($plan['prix']) ?> FCFA</option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Montant payé</label>
                      <input type="number" step="0.01" name="montant_paye" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Date début</label>
                      <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Date fin</label>
                      <input type="date" name="date_fin" class="form-control" required>
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