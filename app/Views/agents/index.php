<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$agents = $agents ?? [];
$agentAccounts = $agentAccounts ?? [];
$csrfToken = $csrfToken ?? '';
?>

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
        <h1>Comptes des agents de l’école</h1>
        <p class="text-muted mb-0">Les enseignants et les fonctions administratives liés à une fiche agent sont inclus.</p>
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
      <div class="alert alert-info">Aucun enseignant, membre du personnel administratif ou agent trouvé pour cette école.</div>
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
                <th>Fonction</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Compte de connexion</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($agents as $agent): ?>
                <tr>
                  <td><?= (int) $agent['id'] ?></td>
                  <td><?= htmlspecialchars(\App\Core\PersonName::format($agent)) ?></td>
                  <td><?= htmlspecialchars((string) ($agent['fonction'] ?? 'Agent'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($agent['telephone'] ?? '') ?></td>
                  <td><?= htmlspecialchars($agent['email'] ?? '') ?></td>
                  <td>
                    <?php if (!empty($agentAccounts[$agent['id']])): ?>
                      <?php $account = $agentAccounts[$agent['id']]; ?>
                      <strong><?= htmlspecialchars($account['identifiant'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                      <span class="badge text-bg-<?= ($account['statut'] ?? '') === 'Actif' ? 'success' : 'secondary' ?> ms-1"><?= htmlspecialchars($account['statut'] ?? 'Inactif', ENT_QUOTES, 'UTF-8') ?></span>
                      <small class="d-block text-muted"><?= htmlspecialchars(\App\Models\User::getRoleLabel($account['role'] ?? 'agent_ecole'), ENT_QUOTES, 'UTF-8') ?></small>
                    <?php else: ?>
                      <span class="badge text-bg-secondary">Aucun compte</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (empty($agentAccounts[$agent['id']])): ?>
                      <form method="post" action="<?= BASE_URL ?>/agents/createAccount" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="agent_id" value="<?= (int) $agent['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Créer compte</button>
                      </form>
                    <?php else: ?>
                      <?php $nextStatus = ($account['statut'] ?? '') === 'Actif' ? 'Inactif' : 'Actif'; ?>
                      <form method="post" action="<?= BASE_URL ?>/agents/updateAccountStatus" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="agent_id" value="<?= (int) $agent['id'] ?>">
                        <input type="hidden" name="statut" value="<?= $nextStatus ?>">
                        <button type="submit" class="btn btn-sm btn-outline-<?= $nextStatus === 'Actif' ? 'success' : 'warning' ?>">
                          <i class="bi bi-<?= $nextStatus === 'Actif' ? 'check-circle' : 'pause-circle' ?> me-1" aria-hidden="true"></i><?= $nextStatus === 'Actif' ? 'Activer' : 'Désactiver' ?>
                        </button>
                      </form>
                      <a href="<?= BASE_URL ?>/utilisateurs" class="btn btn-sm btn-outline-secondary">Gérer les comptes</a>
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
