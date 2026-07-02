<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Demandes d'inscription d'écoles</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/ecoles">Écoles</a></li>
          <li class="breadcrumb-item active">Demandes</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <?php if (empty($pending)): ?>
              <div class="alert alert-info">Aucune demande en attente.</div>
            <?php else: ?>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending as $p): ?>
                    <tr>
                      <td><?= (int)$p['id'] ?></td>
                      <td><?= htmlspecialchars($p['nom_etablissement']) ?></td>
                      <td><?= htmlspecialchars($p['email_officiel'] ?? '') ?></td>
                      <td><?= htmlspecialchars($p['telephone_contact'] ?? '') ?></td>
                      <td><?= htmlspecialchars($p['date_creation_compte'] ?? '') ?></td>
                      <td>
                        <form method="post" action="<?= BASE_URL ?>/ecoles/confirm" style="display:inline">
                          <input type="hidden" name="ecole_id" value="<?= (int)$p['id'] ?>">
                          <button class="btn btn-sm btn-success">Confirmer</button>
                        </form>
                        <a href="<?= BASE_URL ?>/ecoles/edit?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                        <form method="post" action="<?= BASE_URL ?>/ecoles/delete" style="display:inline" onsubmit="return confirm('Confirmer la suppression de cette école ?');">
                          <input type="hidden" name="ecole_id" value="<?= (int)$p['id'] ?>">
                          <button class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <?php if (!empty($pagination)): ?>
                <nav>
                  <ul class="pagination">
                    <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                      <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/ecoles/requests?page=<?= $i ?>"><?= $i ?></a>
                      </li>
                    <?php endfor; ?>
                  </ul>
                </nav>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>