<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Ajouter un abonnement</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/ecoles">Écoles</a></li>
                <li class="breadcrumb-item active">Ajouter un abonnement</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['ecoles_subscription_errors'])): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($_SESSION['ecoles_subscription_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php unset($_SESSION['ecoles_subscription_errors']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['ecoles_subscription_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['ecoles_subscription_success']) ?></div>
            <?php unset($_SESSION['ecoles_subscription_success']); ?>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-8 mx-auto">
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
                        <?php foreach (($schools ?? []) as $school): ?>
                          <option value="<?= (int) $school['id'] ?>"><?= htmlspecialchars($school['nom_etablissement']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Plan</label>
                      <select name="plan_id" class="form-select" required>
                        <option value="">Sélectionnez un plan</option>
                        <?php foreach (($plans ?? []) as $plan): ?>
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