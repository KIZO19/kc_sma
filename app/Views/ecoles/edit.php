<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Modifier une école</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/ecoles">Écoles</a></li>
          <li class="breadcrumb-item active">Modifier</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
                <?php if (!empty($_SESSION['ecole_edit_errors'])): ?>
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      <?php foreach ($_SESSION['ecole_edit_errors'] as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                  <?php unset($_SESSION['ecole_edit_errors']); ?>
                <?php endif; ?>
                <?php if (!empty($_SESSION['ecole_edit_success'])): ?>
                  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['ecole_edit_success']) ?></div>
                  <?php unset($_SESSION['ecole_edit_success']); ?>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/ecoles/edit?id=<?= (int)$school['id'] ?>">
              <div class="mb-3">
                <label class="form-label">Nom de l'établissement</label>
                <input type="text" name="nom_etablissement" class="form-control" value="<?= htmlspecialchars($school['nom_etablissement'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Identifiant</label>
                <input type="text" name="identifiant" class="form-control" value="<?= htmlspecialchars($school['identifiant'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email officiel</label>
                <input type="email" name="email_officiel" class="form-control" value="<?= htmlspecialchars($school['email_officiel'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Téléphone</label>
                <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($school['telephone_contact'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Adresse</label>
                <textarea name="adresse" class="form-control"><?= htmlspecialchars($school['adresse'] ?? '') ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Matricule</label>
                <input type="text" name="matricule" class="form-control" value="<?= htmlspecialchars($school['matricule'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Logo (PNG/JPEG, ≤250KB)</label>
                <input type="file" name="logo" accept="image/png,image/jpeg" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Statut</label>
                <select name="statut_systeme" class="form-select">
                  <option value="En_Attente" <?= ($school['statut_systeme'] ?? '') === 'En_Attente' ? 'selected' : '' ?>>En attente</option>
                  <option value="Actif" <?= ($school['statut_systeme'] ?? '') === 'Actif' ? 'selected' : '' ?>>Actif</option>
                  <option value="Suspendu" <?= ($school['statut_systeme'] ?? '') === 'Suspendu' ? 'selected' : '' ?>>Suspendu</option>
                </select>
              </div>
              <button class="btn btn-primary">Enregistrer</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>