<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php $students = $students ?? []; ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1>Gérer les mots de passe</h1>
        <p class="text-muted mb-0">Créez ou renvoyez les liens sécurisés de configuration des comptes parents et élèves.</p>
      </div>
      <div class="col-sm-4">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
          <li class="breadcrumb-item active">Gérer les mots de passe</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['inscriptions_errors'])): ?>
      <div class="alert alert-danger alert-dismissible" role="alert">
        <ul class="mb-0">
          <?php foreach ($_SESSION['inscriptions_errors'] as $error): ?>
            <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
      <?php unset($_SESSION['inscriptions_errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['account_access_notification'])): ?>
      <?php $accountAccessNotification = $_SESSION['account_access_notification']; unset($_SESSION['account_access_notification']); ?>
      <?php $status = $accountAccessNotification['status'] ?? ''; ?>
      <div class="alert <?= $status === 'success' ? 'alert-success' : 'alert-warning' ?> alert-dismissible" role="status">
        <?= htmlspecialchars((string) ($accountAccessNotification['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Accès des parents et des élèves</h2>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          Les liens sont envoyés au numéro du parent/tuteur et expirent après 24 heures. Le lien élève demande son matricule avant de permettre la création du mot de passe.
        </div>
        <?php if (empty($students)): ?>
          <p class="text-muted mb-0">Aucun élève trouvé dans votre périmètre.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Matricule</th>
                  <th>Élève</th>
                  <th>Classe</th>
                  <th>Parent/tuteur</th>
                  <th>Téléphone</th>
                  <th>Accès</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($students as $student): ?>
                  <?php
                    $studentName = trim(implode(' ', array_filter([
                        trim((string) ($student['nom'] ?? '')),
                        trim((string) ($student['postnom'] ?? '')),
                        trim((string) ($student['prenom'] ?? '')),
                    ], static fn (string $part): bool => $part !== '')));
                  ?>
                  <tr>
                    <td><?= htmlspecialchars((string) ($student['matricule'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($studentName !== '' ? $studentName : '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($student['nom_classe'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($student['parent_nom_responsable'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($student['parent_telephone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-nowrap">
                      <form method="post" action="<?= BASE_URL ?>/inscriptions/sendAccountAccess" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($accountAccessCsrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="eleve_id" value="<?= (int) ($student['id'] ?? 0) ?>">
                        <input type="hidden" name="account_type" value="student">
                        <input type="hidden" name="return_to" value="passwords">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-mortarboard me-1" aria-hidden="true"></i>Mot de passe élève
                        </button>
                      </form>
                      <form method="post" action="<?= BASE_URL ?>/inscriptions/sendAccountAccess" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($accountAccessCsrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="eleve_id" value="<?= (int) ($student['id'] ?? 0) ?>">
                        <input type="hidden" name="account_type" value="parent">
                        <input type="hidden" name="return_to" value="passwords">
                        <button type="submit" class="btn btn-sm btn-outline-success">
                          <i class="bi bi-people me-1" aria-hidden="true"></i>Mot de passe parent
                        </button>
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
