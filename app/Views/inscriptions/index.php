<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Inscriptions</h1>
              <p class="text-muted">Gestion des dossiers d’inscription et validation par le secrétaire.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Inscriptions</li>
              </ol>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="small-box bg-info">
                <div class="inner">
                  <h3><?= count($pendingStudents) ?></h3>
                  <p>Dossiers en attente</p>
                </div>
                <div class="icon"><i class="bi bi-clock-history"></i></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="small-box bg-secondary">
                <div class="inner">
                  <h3><?= htmlspecialchars($roleLabel) ?></h3>
                  <p>Rôle connecté</p>
                </div>
                <div class="icon"><i class="bi bi-person-badge"></i></div>
              </div>
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

          <div class="row">
            <div class="col-xl-12">
              <div class="card card-outline card-primary">
                <div class="card-header">
                  <h3 class="card-title">Dossiers d’inscription</h3>
                </div>
                <div class="card-body">
                  <p class="mb-4">Les rôles autorisés à soumettre une inscription peuvent créer un dossier. La validation finale est assurée par le secrétaire.</p>

                  <?php if (empty($pendingStudents)): ?>
                    <div class="alert alert-info">Aucune inscription en attente.</div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-striped table-hover table-bordered">
                        <thead class="table-light">
                          <tr>
                            <th style="width: 50px;">#</th>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Date de naissance</th>
                            <th>Parent</th>
                            <th style="width: 120px;">Statut</th>
                            <?php if ($canEdit || $canApprove): ?>
                              <th style="width: 220px;">Action</th>
                            <?php endif; ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($pendingStudents as $index => $student): ?>
                            <tr>
                              <td><?= $index + 1 ?></td>
                              <td><?= htmlspecialchars($student['matricule'] ?? 'N/A') ?></td>
                              <td><?= htmlspecialchars($student['nom']) ?></td>
                              <td><?= htmlspecialchars($student['prenom'] ?? '-') ?></td>
                              <td><?= htmlspecialchars($student['date_naissance']) ?></td>
                              <td><?= htmlspecialchars($student['parent_nom_responsable'] ?? '-') ?></td>
                              <td><span class="badge bg-warning">En attente</span></td>
                              <?php if ($canEdit || $canApprove): ?>
                                <td>
                                  <?php if ($canEdit): ?>
                                    <a href="<?= BASE_URL ?>/inscriptions/edit?id=<?= (int) $student['id'] ?>" class="btn btn-sm btn-primary me-1">Modifier</a>
                                  <?php endif; ?>
                                  <?php if ($canApprove): ?>
                                    <form method="post" action="<?= BASE_URL ?>/inscriptions/approve" class="d-inline">
                                      <input type="hidden" name="eleve_id" value="<?= (int) $student['id'] ?>">
                                      <button type="submit" class="btn btn-sm btn-success">Valider</button>
                                    </form>
                                  <?php endif; ?>
                                </td>
                              <?php endif; ?>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <?php if ($canSubmit): ?>
                <div class="mt-3">
                  <a href="<?= BASE_URL ?>/inscriptions/create" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Nouvelle inscription</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
