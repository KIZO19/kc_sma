<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Créer une école</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/ecoles">Écoles</a></li>
                <li class="breadcrumb-item active">Créer une école</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['ecoles_create_errors'])): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($_SESSION['ecoles_create_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php unset($_SESSION['ecoles_create_errors']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['ecoles_create_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['ecoles_create_success']) ?></div>
            <?php unset($_SESSION['ecoles_create_success']); ?>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-8 mx-auto">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Créer une nouvelle école</h3>
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
                    <h5>Admin de l'école</h5>
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
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>