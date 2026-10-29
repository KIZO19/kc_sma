<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Profil du parent</h1>
        <p class="text-muted"><?= htmlspecialchars($parent['nom_responsable'] ?? '-') ?></p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/eleves">Élèves</a></li>
          <li class="breadcrumb-item active">Profil parent</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-5">
        <div class="card card-outline card-primary">
          <div class="card-header"><h3 class="card-title">Coordonnées</h3></div>
          <div class="card-body">
            <p><strong>Nom :</strong> <?= htmlspecialchars($parent['nom_responsable'] ?? '-') ?></p>
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($parent['telephone'] ?? '-') ?></p>
            <p><strong>Email :</strong> <?= htmlspecialchars($parent['email'] ?? '-') ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-7">
        <div class="card card-outline card-secondary">
          <div class="card-header"><h3 class="card-title">Élèves associés</h3></div>
          <div class="card-body">
            <?php if (!empty($_SESSION['parents_success'])): ?>
              <div class="alert alert-success" role="status"><?= htmlspecialchars((string) $_SESSION['parents_success'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php unset($_SESSION['parents_success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['parents_errors'])): ?>
              <div class="alert alert-danger" role="alert">
                <?php foreach ((array) $_SESSION['parents_errors'] as $error): ?>
                  <div><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
              </div>
              <?php unset($_SESSION['parents_errors']); ?>
            <?php endif; ?>
            <?php if (!empty($pendingChildRequests)): ?>
              <div class="alert alert-warning">
                <strong>Demandes en attente de validation parent</strong>
                <ul class="mb-0 mt-2">
                  <?php foreach ($pendingChildRequests as $request): ?>
                    <?php $requestStudentName = \App\Core\PersonName::format($request); ?>
                    <li>
                      <?= htmlspecialchars($requestStudentName !== '' ? $requestStudentName : '-', ENT_QUOTES, 'UTF-8') ?>
                      (<?= htmlspecialchars($request['matricule'] ?? '-', ENT_QUOTES, 'UTF-8') ?>)
                      — demande envoyée par <?= htmlspecialchars($request['requested_by_name'] ?? 'une autorité scolaire', ENT_QUOTES, 'UTF-8') ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
            <?php if (empty($children)): ?>
              <div class="alert alert-info mb-0">Aucun élève associé à ce parent.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead>
                    <tr><th>Nom</th><th>Matricule</th><th>Classe</th><th>Total payé</th><th>Reste à payer</th><th>Action</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($children as $child): ?>
                      <?php $childName = \App\Core\PersonName::format($child); ?>
                      <?php $financialSummary = $childrenFinancialSummaries[(int) ($child['id'] ?? 0)] ?? ['paid' => [], 'debt' => []]; ?>
                      <tr>
                        <td><?= htmlspecialchars($childName !== '' ? $childName : '-') ?></td>
                        <td><?= htmlspecialchars($child['matricule'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($child['nom_classe'] ?? '-') ?><?= !empty($child['nom_option']) ? ' · ' . htmlspecialchars($child['nom_option'], ENT_QUOTES, 'UTF-8') : '' ?></td>
                        <td>
                          <?php if (empty($financialSummary['paid'])): ?>
                            0,00
                          <?php else: ?>
                            <?php foreach ($financialSummary['paid'] as $currency => $amount): ?>
                              <div><?= htmlspecialchars($currency) ?> <?= number_format((float) $amount, 2, ',', ' ') ?></div>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if (empty($financialSummary['debt'])): ?>
                            0,00
                          <?php else: ?>
                            <?php foreach ($financialSummary['debt'] as $currency => $amount): ?>
                              <div><?= htmlspecialchars($currency) ?> <?= number_format((float) $amount, 2, ',', ' ') ?></div>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </td>
                        <td><a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) $child['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php if (!empty($canAssignChildren)): ?>
          <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Rattacher un élève à ce parent</h3></div>
            <div class="card-body">
              <?php if (empty($unassignedChildren)): ?>
                <p class="text-muted mb-0">Aucun élève sans parent n’est disponible dans votre périmètre.</p>
              <?php else: ?>
                <form method="post" action="<?= BASE_URL ?>/parents/assignChild">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="parent_id" value="<?= (int) $parent['id'] ?>">
                  <div class="input-group">
                    <select class="form-select" name="eleve_id" required>
                      <option value="">Sélectionner un élève sans parent</option>
                      <?php foreach ($unassignedChildren as $student): ?>
                        <?php $studentName = \App\Core\PersonName::format($student); ?>
                        <option value="<?= (int) $student['id'] ?>">
                          <?= htmlspecialchars($studentName !== '' ? $studentName : '-', ENT_QUOTES, 'UTF-8') ?>
                          (<?= htmlspecialchars($student['matricule'] ?? '-', ENT_QUOTES, 'UTF-8') ?>)
                          <?php if (!empty($student['nom_classe'])): ?>
                            — <?= htmlspecialchars($student['nom_classe'], ENT_QUOTES, 'UTF-8') ?><?= !empty($student['nom_option']) ? ' · ' . htmlspecialchars($student['nom_option'], ENT_QUOTES, 'UTF-8') : '' ?>
                          <?php endif; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-send me-1"></i> Envoyer la demande</button>
                  </div>
                  <small class="form-text text-muted">L’élève sera rattaché uniquement après validation par le parent.</small>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>