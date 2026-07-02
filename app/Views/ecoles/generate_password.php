<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="mb-0">Générer mot de passe élève</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Générer mot de passe</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-6">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Générer un mot de passe temporaire</h3>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= BASE_URL ?>/ecoles/generatePassword">
                    <div class="mb-3">
                      <label class="form-label">Matricule de l'élève</label>
                      <input type="text" name="matricule" class="form-control" value="<?= htmlspecialchars($matricule ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Générer</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>