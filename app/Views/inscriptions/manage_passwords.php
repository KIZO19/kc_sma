<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$students = $students ?? [];
$generatedAccountPassword = $generatedAccountPassword ?? null;
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1>Gérer les mots de passe</h1>
        <p class="text-muted mb-0">Générez un mot de passe temporaire pour un compte parent ou élève.</p>
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

    <?php if (is_array($generatedAccountPassword) && !empty($generatedAccountPassword['password'])): ?>
      <div class="alert alert-success" role="status">
        <h2 class="h5">Mot de passe temporaire généré</h2>
        <p class="mb-1">
          Compte <?= htmlspecialchars((string) ($generatedAccountPassword['type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
          <?php if (!empty($generatedAccountPassword['name'])): ?>
            — <?= htmlspecialchars((string) $generatedAccountPassword['name'], ENT_QUOTES, 'UTF-8') ?>
          <?php endif; ?>
        </p>
        <p class="mb-1">Identifiant : <strong><?= htmlspecialchars((string) ($generatedAccountPassword['identifier'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p class="mb-1">Mot de passe : <code class="fs-5 user-select-all"><?= htmlspecialchars((string) $generatedAccountPassword['password'], ENT_QUOTES, 'UTF-8') ?></code></p>
        <small>Communiquez-le à l’utilisateur de façon sécurisée. Il devra choisir un nouveau mot de passe dès sa première connexion. Ce mot de passe ne sera plus affiché après avoir quitté ou actualisé cette page.</small>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Accès des parents et des élèves</h2>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          Chaque génération remplace le mot de passe actuel. Le nouveau mot de passe est affiché une seule fois et l’utilisateur devra le modifier dès sa première connexion.
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
                      <form method="post" action="<?= BASE_URL ?>/inscriptions/generateAccountPassword" class="d-inline" onsubmit="return confirm('La génération remplacera le mot de passe actuel de ce compte. Continuer ?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($accountAccessCsrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="eleve_id" value="<?= (int) ($student['id'] ?? 0) ?>">
                        <input type="hidden" name="account_type" value="student">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-mortarboard me-1" aria-hidden="true"></i>Générer mot de passe élève
                        </button>
                      </form>
                      <form method="post" action="<?= BASE_URL ?>/inscriptions/generateAccountPassword" class="d-inline" onsubmit="return confirm('La génération remplacera le mot de passe actuel de ce compte. Continuer ?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($accountAccessCsrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="eleve_id" value="<?= (int) ($student['id'] ?? 0) ?>">
                        <input type="hidden" name="account_type" value="parent">
                        <button type="submit" class="btn btn-sm btn-outline-success">
                          <i class="bi bi-people me-1" aria-hidden="true"></i>Générer mot de passe parent
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
