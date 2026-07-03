<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['agents_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['agents_success']) ?></div>
      <?php unset($_SESSION['agents_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['agents_errors'])): ?>
      <div class="alert alert-danger">
        <?php foreach ((array) $_SESSION['agents_errors'] as $error): ?>
          <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
      </div>
      <?php unset($_SESSION['agents_errors']); ?>
    <?php endif; ?>
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Gestion des agents</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item active">Agents</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (empty($agents)): ?>
      <div class="alert alert-info">Aucun agent trouvé pour cette école.</div>
    <?php else: ?>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Liste des agents</h3>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-hover table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Statut du compte</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($agents as $agent): ?>
                <tr>
                  <td><?= (int) $agent['id'] ?></td>
                  <td><?= htmlspecialchars(trim(($agent['nom'] ?? '') . ' ' . ($agent['postnom'] ?? '') . ' ' . ($agent['prenom'] ?? ''))) ?></td>
                  <td><?= htmlspecialchars($agent['telephone'] ?? '') ?></td>
                  <td><?= htmlspecialchars($agent['email'] ?? '') ?></td>
                  <td>
                    <?php if (!empty($agentAccounts[$agent['id']])): ?>
                      <span class="badge bg-success">Compte créé</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Compte non créé</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (empty($agentAccounts[$agent['id']])): ?>
                      <form method="post" action="<?= BASE_URL ?>/agents/createAccount" class="d-inline">
                        <input type="hidden" name="agent_id" value="<?= (int) $agent['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-primary">Créer compte</button>
                      </form>
                    <?php else: ?>
                      <a href="<?= BASE_URL ?>/utilisateurs?reference_id=<?= (int) $agent['id'] ?>" class="btn btn-sm btn-secondary">Voir le compte</a>
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
