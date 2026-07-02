<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['utilisateurs_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['utilisateurs_success']) ?></div>
      <?php unset($_SESSION['utilisateurs_success']); ?>
    <?php endif; ?>
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Validation des comptes</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Validation des comptes</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Comptes en attente</h3>
      </div>
      <div class="card-body">
        <?php if (empty($inactiveUsers)): ?>
          <div class="alert alert-info">Aucun compte en attente de validation.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nom</th>
                  <th>Identifiant</th>
                  <th>Rôle</th>
                  <th>Date de création</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($inactiveUsers as $inactive): ?>
                  <tr>
                    <td><?= (int) $inactive['id'] ?></td>
                    <td><?= htmlspecialchars($inactive['nom_complet']) ?></td>
                    <td><?= htmlspecialchars($inactive['identifiant']) ?></td>
                    <td><?= htmlspecialchars(User::getRoleLabel($inactive['role'])) ?></td>
                    <td><?= htmlspecialchars($inactive['created_at']) ?></td>
                    <td>
                      <form method="post" action="<?= BASE_URL ?>/utilisateurs/validate" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= (int) $inactive['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">Valider</button>
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
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>
