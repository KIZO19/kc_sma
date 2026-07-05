<?php
$pageStyles = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">'
    . '<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">';
$pageScripts = '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>'
    . '<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>'
    . '<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" crossorigin="anonymous"></script>'
    . '<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"></script>'
    . '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" crossorigin="anonymous"></script>'
    . '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" crossorigin="anonymous"></script>'
    . '<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js" crossorigin="anonymous"></script>'
    . '<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js" crossorigin="anonymous"></script>'
    . '<script>document.addEventListener("DOMContentLoaded", function() { $("#ecolesTable").DataTable({ dom: "Bfrtip", buttons: [ { extend: "excelHtml5", text: "Excel" }, { extend: "pdfHtml5", text: "PDF" }, { extend: "print", text: "Imprimer" } ], responsive: true, pageLength: 10, lengthMenu: [[5,10,25,50],[5,10,25,50]], language: { search: "Filtrer :", lengthMenu: "Afficher _MENU_ écoles", info: "Affichage de _START_ à _END_ sur _TOTAL_ écoles", infoEmpty: "Aucune école disponible", zeroRecords: "Aucune école trouvée", paginate: { first: "Premier", previous: "Précédent", next: "Suivant", last: "Dernier" }, buttons: { excel: "Excel", pdf: "PDF", print: "Imprimer" } }, columnDefs: [ { orderable: false, targets: [0,8] }, { searchable: false, targets: [0,8] } ], exportOptions: { columns: [1,2,3,4,5,6,7] } }); });</script>';
require __DIR__ . '/../partials/app_header.php'; ?>
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
            <div class="col-12">
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
                  <div class="card-body">
                    <p class="text-muted mb-0">Utilisez les pages dédiées de la sidebar pour créer une école ou ajouter un abonnement.</p>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid px-0">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Liste des écoles</h3>
            </div>
            <div class="card-body table-responsive">
              <table id="ecolesTable" class="table table-striped table-hover align-middle" style="width:100%">
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
                        <?php if (($role ?? '') === 'super_admin'): ?>
                          <a href="<?= BASE_URL ?>/ecoles/edit?id=<?= (int) $school['id'] ?>" class="btn btn-sm btn-outline-secondary me-2">Modifier</a>
                        <?php endif; ?>
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
      </section>
      
<?php require __DIR__ . '/../partials/app_footer.php'; ?>