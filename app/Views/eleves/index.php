<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Élèves</h1>
              <p class="text-muted">Liste des élèves enregistrés dans votre école.</p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Élèves</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (empty($students)): ?>
            <div class="alert alert-info">Aucun élève disponible pour le périmètre de cette école.</div>
          <?php else: ?>
            <div class="card card-outline card-primary">
              <div class="card-header">
                <h3 class="card-title">Élèves inscrits</h3>
              </div>
              <div class="card-body table-responsive">
                <table class="table table-striped table-hover table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 50px;">#</th>
                      <th>Matricule</th>
                      <th>Nom</th>
                      <th>Postnom</th>
                      <th>Prénom</th>
                      <th>Date de naissance</th>
                      <th>Parent</th>
                      <th>Statut</th>
                      <th style="width:120px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($students as $index => $student): ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($student['matricule'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($student['nom'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($student['postnom'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($student['prenom'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($student['date_naissance'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($student['parent_nom_responsable'] ?? '-') ?></td>
                        <td>
                          <?php if (($student['statut_eleve'] ?? '') === 'actif'): ?>
                            <span class="badge bg-success">Actif</span>
                          <?php elseif (($student['statut_eleve'] ?? '') === 'inactif'): ?>
                            <span class="badge bg-warning">En attente</span>
                          <?php else: ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($student['statut_eleve'] ?? 'N/A') ?></span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if (in_array($role, ['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'comptable_école'], true)): ?>
                            <a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) $student['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>