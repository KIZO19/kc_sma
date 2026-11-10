<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$agents = $agents ?? [];
$agentAccounts = $agentAccounts ?? [];
$csrfToken = $csrfToken ?? '';
$linkedAccountCount = count($agentAccounts);
$agentsWithoutAccounts = max(0, count($agents) - $linkedAccountCount);
?>

<style>
  .agents-page {
    --agents-ink: #172b4d;
    --agents-muted: #64748b;
    --agents-line: #e2e8f0;
    --agents-brand: #2563eb;
    max-width: 1440px;
    margin: 0 auto;
    padding: 1.5rem clamp(.75rem, 2vw, 2rem) 2rem;
    color: var(--agents-ink);
  }

  .agents-page *,
  .agents-page *::before,
  .agents-page *::after {
    box-sizing: border-box;
  }

  .agents-page .agents-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
  }

  .agents-page .agents-heading h1 {
    margin: 0 0 .4rem;
    color: var(--agents-ink);
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 750;
    letter-spacing: -.035em;
  }

  .agents-page .agents-heading p {
    margin: 0;
    color: var(--agents-muted);
  }

  .agents-page .agents-add-button,
  .agents-page .agents-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    min-height: 2.5rem;
    padding: .6rem .9rem;
    border: 1px solid transparent;
    border-radius: .65rem;
    font: inherit;
    font-size: .9rem;
    font-weight: 650;
    line-height: 1.2;
    text-decoration: none;
    cursor: pointer;
    transition: background-color .15s ease, box-shadow .15s ease, transform .15s ease;
  }

  .agents-page .agents-add-button {
    flex: 0 0 auto;
    color: #fff;
    background: var(--agents-brand);
    box-shadow: 0 .35rem .85rem rgba(37, 99, 235, .2);
  }

  .agents-page .agents-add-button:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
  }

  .agents-page .agents-breadcrumb {
    display: flex;
    gap: .5rem;
    margin: 0 0 1.25rem;
    padding: 0;
    color: var(--agents-muted);
    font-size: .85rem;
    list-style: none;
  }

  .agents-page .agents-breadcrumb a {
    color: var(--agents-brand);
    text-decoration: none;
  }

  .agents-page .agents-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .9rem;
    margin-bottom: 1.25rem;
  }

  .agents-page .agents-stat {
    display: flex;
    align-items: center;
    gap: .85rem;
    min-width: 0;
    padding: 1rem 1.1rem;
    border: 1px solid var(--agents-line);
    border-radius: .85rem;
    background: #fff;
    box-shadow: 0 .25rem .75rem rgba(15, 23, 42, .035);
  }

  .agents-page .agents-stat-icon {
    display: grid;
    width: 2.75rem;
    height: 2.75rem;
    flex: 0 0 2.75rem;
    place-items: center;
    border-radius: .75rem;
    color: #1d4ed8;
    background: #eff6ff;
    font-size: 1.2rem;
  }

  .agents-page .agents-stat strong {
    display: block;
    color: var(--agents-ink);
    font-size: 1.25rem;
    line-height: 1.2;
  }

  .agents-page .agents-stat div span {
    color: var(--agents-muted);
    font-size: .8rem;
  }

  .agents-page .agents-notice {
    margin-bottom: 1rem;
    padding: .9rem 1rem;
    border: 1px solid #fde68a;
    border-radius: .75rem;
    color: #854d0e;
    background: #fffbeb;
    line-height: 1.5;
  }

  .agents-page .agents-notice > span {
    display: block;
  }

  .agents-page .agents-notice .agents-unlinked-account {
    display: inline-block;
    margin: .25rem .5rem 0 0;
  }

  .agents-page .agents-notice a,
  .agents-page .agents-notice button {
    color: inherit;
  }

  .agents-page .agents-card {
    overflow: hidden;
    border: 1px solid var(--agents-line);
    border-radius: .9rem;
    background: #fff;
    box-shadow: 0 .35rem 1rem rgba(15, 23, 42, .045);
  }

  .agents-page .agents-card-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    padding: 1rem 1.2rem;
    border-bottom: 1px solid var(--agents-line);
  }

  .agents-page .agents-card-heading h2 {
    margin: 0;
    color: var(--agents-ink);
    font-size: 1rem;
    font-weight: 700;
  }

  .agents-page .agents-count {
    padding: .25rem .6rem;
    border-radius: 999px;
    color: #1d4ed8;
    background: #eff6ff;
    font-size: .78rem;
    font-weight: 650;
  }

  .agents-page .agents-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .agents-page .agents-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
  }

  .agents-page .agents-table th {
    padding: .75rem 1rem;
    color: #64748b;
    background: #f8fafc;
    font-size: .73rem;
    font-weight: 700;
    letter-spacing: .045em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .agents-page .agents-table td {
    padding: .9rem 1rem;
    border-top: 1px solid #eef2f7;
    color: #334155;
    font-size: .88rem;
    vertical-align: middle;
  }

  .agents-page .agents-table tbody tr:hover {
    background: #f8fbff;
  }

  .agents-page .agents-person {
    color: var(--agents-ink);
    font-weight: 700;
  }

  .agents-page .agents-function {
    display: inline-flex;
    padding: .3rem .6rem;
    border-radius: 999px;
    color: #475569;
    background: #f1f5f9;
    font-size: .78rem;
    font-weight: 600;
  }

  .agents-page .agents-account-identifier {
    display: block;
    max-width: 18rem;
    overflow-wrap: anywhere;
    color: #334155;
    font-weight: 600;
  }

  .agents-page .agents-account-role {
    display: block;
    margin-top: .2rem;
    color: var(--agents-muted);
    font-size: .78rem;
  }

  .agents-page .agents-contact {
    display: block;
    overflow-wrap: anywhere;
  }

  .agents-page .agents-status {
    display: inline-flex;
    margin-top: .35rem;
    padding: .2rem .5rem;
    border-radius: 999px;
    color: #166534;
    background: #dcfce7;
    font-size: .72rem;
    font-weight: 700;
  }

  .agents-page .agents-status.is-inactive {
    color: #854d0e;
    background: #fef3c7;
  }

  .agents-page .agents-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    min-width: 12rem;
  }

  .agents-page .agents-button {
    min-height: 2.15rem;
    padding: .45rem .65rem;
    border-color: var(--agents-line);
    color: #334155;
    background: #fff;
    font-size: .78rem;
  }

  .agents-page .agents-button:hover {
    background: #f8fafc;
    box-shadow: 0 .2rem .5rem rgba(15, 23, 42, .08);
  }

  .agents-page .agents-button.is-primary {
    border-color: #bfdbfe;
    color: #1d4ed8;
    background: #eff6ff;
  }

  .agents-page .agents-empty {
    padding: clamp(1.5rem, 5vw, 3.5rem) 1rem;
    text-align: center;
  }

  .agents-page .agents-empty-icon {
    display: grid;
    width: 4rem;
    height: 4rem;
    margin: 0 auto .9rem;
    place-items: center;
    border-radius: 1.2rem;
    color: #2563eb;
    background: #eff6ff;
    font-size: 1.8rem;
  }

  .agents-page .agents-empty h2 {
    margin: 0 0 .45rem;
    color: var(--agents-ink);
    font-size: 1.15rem;
    font-weight: 700;
  }

  .agents-page .agents-empty p {
    max-width: 34rem;
    margin: 0 auto 1.1rem;
    color: var(--agents-muted);
    line-height: 1.55;
  }

  .agents-page .agents-alert {
    margin-bottom: 1rem;
    padding: .85rem 1rem;
    border: 1px solid;
    border-radius: .7rem;
    line-height: 1.5;
  }

  .agents-page .agents-alert-success {
    border-color: #bbf7d0;
    color: #166534;
    background: #f0fdf4;
  }

  .agents-page .agents-alert-error {
    border-color: #fecaca;
    color: #991b1b;
    background: #fef2f2;
  }

  .agents-page .agents-dialog {
    position: fixed;
    inset: 0;
    width: min(52rem, calc(100vw - 2rem));
    max-width: none;
    max-height: min(90vh, 52rem);
    margin: auto;
    padding: 0;
    overflow: auto;
    border: 1px solid var(--agents-line);
    border-radius: 1rem;
    color: var(--agents-ink);
    background: #fff;
    box-shadow: 0 1.5rem 5rem rgba(15, 23, 42, .28);
  }

  .agents-page .agents-dialog-small {
    width: min(34rem, calc(100vw - 2rem));
  }

  .agents-page .agents-dialog::backdrop {
    background: rgba(15, 23, 42, .48);
    backdrop-filter: blur(2px);
  }

  .agents-page .agents-form {
    min-width: 0;
  }

  .agents-page .agents-dialog-header,
  .agents-page .agents-dialog-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--agents-line);
  }

  .agents-page .agents-dialog-footer {
    justify-content: flex-end;
    border-top: 1px solid var(--agents-line);
    border-bottom: 0;
  }

  .agents-page .agents-dialog-header h2 {
    margin: 0;
    color: var(--agents-ink);
    font-size: 1.1rem;
    font-weight: 750;
  }

  .agents-page .agents-dialog-body {
    padding: 1.25rem;
  }

  .agents-page .agents-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .agents-page .agents-field {
    min-width: 0;
  }

  .agents-page .agents-field.is-full {
    grid-column: 1 / -1;
  }

  .agents-page .agents-field label {
    display: block;
    margin-bottom: .4rem;
    color: #334155;
    font-size: .84rem;
    font-weight: 650;
  }

  .agents-page .agents-field input,
  .agents-page .agents-field select {
    display: block;
    width: 100%;
    min-height: 2.7rem;
    padding: .6rem .75rem;
    border: 1px solid #cbd5e1;
    border-radius: .6rem;
    color: #172b4d;
    background: #fff;
    font: inherit;
    font-size: .9rem;
  }

  .agents-page .agents-field input:focus,
  .agents-page .agents-field select:focus {
    border-color: #60a5fa;
    outline: 3px solid rgba(59, 130, 246, .16);
  }

  .agents-page .agents-help {
    display: block;
    margin-top: .35rem;
    color: var(--agents-muted);
    font-size: .77rem;
    line-height: 1.4;
  }

  .agents-page .agents-dialog-note {
    margin: 1rem 0 0;
    padding: .75rem .85rem;
    border-radius: .65rem;
    color: #1e40af;
    background: #eff6ff;
    font-size: .82rem;
    line-height: 1.5;
  }

  .agents-page .agents-close {
    display: grid;
    width: 2rem;
    height: 2rem;
    flex: 0 0 2rem;
    place-items: center;
    border: 0;
    border-radius: .5rem;
    color: #64748b;
    background: transparent;
    font-size: 1.4rem;
    cursor: pointer;
  }

  @media (max-width: 800px) {
    .agents-page {
      padding-top: 1rem;
    }

    .agents-page .agents-table {
      min-width: 54rem;
    }
  }

  @media (max-width: 600px) {
    .agents-page .agents-heading {
      flex-direction: column;
      align-items: stretch;
    }

    .agents-page .agents-add-button {
      width: 100%;
    }

    .agents-page .agents-stats {
      grid-template-columns: 1fr;
      gap: .55rem;
    }

    .agents-page .agents-stat {
      padding: .75rem .9rem;
    }

    .agents-page .agents-stat-icon {
      width: 2.35rem;
      height: 2.35rem;
      flex-basis: 2.35rem;
      font-size: 1rem;
    }

    .agents-page .agents-dialog {
      width: calc(100vw - 1rem);
      max-height: calc(100dvh - 1rem);
      border-radius: .8rem;
    }

    .agents-page .agents-dialog-body {
      padding: .9rem;
    }

    .agents-page .agents-form-grid {
      grid-template-columns: minmax(0, 1fr);
      gap: .8rem;
    }

    .agents-page .agents-field.is-full {
      grid-column: auto;
    }
  }

  [data-theme="dark"] .agents-page {
    --agents-ink: #e2e8f0;
    --agents-muted: #94a3b8;
    --agents-line: rgba(148, 163, 184, .22);
  }

  [data-theme="dark"] .agents-page .agents-stat,
  [data-theme="dark"] .agents-page .agents-card,
  [data-theme="dark"] .agents-page .agents-dialog {
    color: var(--agents-ink);
    background: #1e293b;
  }

  [data-theme="dark"] .agents-page .agents-notice {
    border-color: #854d0e;
    color: #fde68a;
    background: #422006;
  }

  [data-theme="dark"] .agents-page .agents-dialog-note {
    color: #bfdbfe;
    background: #172554;
  }

  [data-theme="dark"] .agents-page .agents-button {
    border-color: #475569;
    color: #e2e8f0;
    background: #1e293b;
  }

  [data-theme="dark"] .agents-page .agents-button.is-primary {
    border-color: #1e40af;
    color: #bfdbfe;
    background: #172554;
  }

  [data-theme="dark"] .agents-page .agents-table th {
    color: #cbd5e1;
    background: #172033;
  }

  [data-theme="dark"] .agents-page .agents-table td,
  [data-theme="dark"] .agents-page .agents-person,
  [data-theme="dark"] .agents-page .agents-account-identifier,
  [data-theme="dark"] .agents-page .agents-field label {
    color: #e2e8f0;
  }

  [data-theme="dark"] .agents-page .agents-table tbody tr:hover {
    background: #263650;
  }

  [data-theme="dark"] .agents-page .agents-field input,
  [data-theme="dark"] .agents-page .agents-field select {
    border-color: #475569;
    color: #e2e8f0;
    background: #111827;
  }
</style>

<div class="agents-page">
  <?php if (!empty($_SESSION['agents_success'])): ?>
    <div class="agents-alert agents-alert-success" role="status"><?= htmlspecialchars($_SESSION['agents_success']) ?></div>
    <?php unset($_SESSION['agents_success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['agents_errors'])): ?>
    <div class="agents-alert agents-alert-error" role="alert">
      <?php foreach ((array) $_SESSION['agents_errors'] as $error): ?>
        <div><?= htmlspecialchars($error) ?></div>
      <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['agents_errors']); ?>
  <?php endif; ?>

<section class="content-header">
  <div class="agents-heading">
    <div>
      <h1>Agents et comptes de l’école</h1>
      <p>Gérez le personnel, ses fonctions et ses accès à la plateforme.</p>
    </div>
    <button type="button" class="agents-add-button" popovertarget="createAgentModal">
      <i class="bi bi-person-plus" aria-hidden="true"></i>Ajouter un agent
    </button>
  </div>
  <ol class="agents-breadcrumb" aria-label="Fil d’Ariane">
    <li><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
    <li aria-hidden="true">/</li>
    <li aria-current="page">Agents</li>
  </ol>
</section>

<section class="content">
  <div class="agents-stats" aria-label="Résumé du personnel">
    <div class="agents-stat">
      <span class="agents-stat-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
      <div><strong><?= count($agents) ?></strong><span>Fiches agents</span></div>
    </div>
    <div class="agents-stat">
      <span class="agents-stat-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span>
      <div><strong><?= $linkedAccountCount ?></strong><span>Accès liés</span></div>
    </div>
    <div class="agents-stat">
      <span class="agents-stat-icon"><i class="bi bi-person-exclamation" aria-hidden="true"></i></span>
      <div><strong><?= $agentsWithoutAccounts ?></strong><span>Agents sans accès</span></div>
    </div>
  </div>

  <?php if (!empty($unlinkedAccounts)): ?>
    <div class="agents-notice">
      <strong>Comptes à rattacher</strong>
      <span>Certains comptes du personnel n’ont pas encore de fiche agent.</span>
      <?php foreach ($unlinkedAccounts as $unlinkedAccount): ?>
        <span class="agents-unlinked-account">
          <?= htmlspecialchars($unlinkedAccount['nom_complet'] ?? '', ENT_QUOTES, 'UTF-8') ?>
          · <?= htmlspecialchars(\App\Models\User::getRoleLabel($unlinkedAccount['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </span>
      <?php endforeach; ?>
      <span>Utilisez le bouton ci-dessus pour rattacher leur compte.</span>
    </div>
  <?php endif; ?>

  <div class="agents-card">
    <div class="agents-card-heading">
      <h2>Personnel de l’école</h2>
      <span class="agents-count"><?= count($agents) ?> agent<?= count($agents) === 1 ? '' : 's' ?></span>
    </div>
    <?php if (empty($agents)): ?>
      <div class="agents-empty">
        <span class="agents-empty-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
        <h2>Aucun agent enregistré</h2>
        <p>Ajoutez les enseignants et le personnel administratif de l’école. Vous pourrez créer un accès ou rattacher un compte existant en même temps.</p>
        <button type="button" class="agents-add-button" popovertarget="createAgentModal">
          <i class="bi bi-plus-lg" aria-hidden="true"></i>Ajouter le premier agent
        </button>
      </div>
    <?php else: ?>
      <div class="agents-table-wrap">
        <table class="agents-table">
          <thead>
            <tr>
              <th scope="col">Agent</th>
              <th scope="col">Fonction</th>
              <th scope="col">Coordonnées</th>
              <th scope="col">Accès utilisateur</th>
              <th scope="col">Gestion</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($agents as $agent): ?>
              <?php
              $hasAccount = !empty($agentAccounts[$agent['id']]);
              $account = $hasAccount ? $agentAccounts[$agent['id']] : [];
              $suggestedRole = $suggestedAccountRoles[$agent['id']] ?? 'agent_école';
              $currentRole = in_array($account['role'] ?? '', array_keys($agentAccountRoles), true)
                ? $account['role']
                : $suggestedRole;
              $defaultIdentifier = $agent['email'] ?? '';
              if ($defaultIdentifier === '') {
                  $defaultIdentifier = $agent['telephone'] ?? '';
              }
              ?>
              <tr>
                <td>
                  <span class="agents-person"><?= htmlspecialchars(\App\Core\PersonName::format($agent), ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td><span class="agents-function"><?= htmlspecialchars((string) ($agent['fonction'] ?? 'Agent'), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                  <span class="agents-contact"><?= htmlspecialchars($agent['telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                  <?php if (!empty($agent['email'])): ?>
                    <span class="agents-account-role"><?= htmlspecialchars($agent['email'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($hasAccount): ?>
                    <span class="agents-account-identifier"><?= htmlspecialchars($account['identifiant'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="agents-status <?= ($account['statut'] ?? '') === 'Actif' ? '' : 'is-inactive' ?>"><?= htmlspecialchars($account['statut'] ?? 'Inactif', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="agents-account-role"><?= htmlspecialchars(\App\Models\User::getRoleLabel($account['role'] ?? 'agent_ecole'), ENT_QUOTES, 'UTF-8') ?></span>
                  <?php else: ?>
                    <span class="agents-account-role">Aucun accès configuré</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="agents-actions">
                    <button
                      type="button"
                      class="agents-button is-primary"
                      popovertarget="agentAccountModal"
                      data-agent-id="<?= (int) $agent['id'] ?>"
                      data-agent-name="<?= htmlspecialchars(\App\Core\PersonName::format($agent), ENT_QUOTES, 'UTF-8') ?>"
                      data-account-role="<?= htmlspecialchars($currentRole, ENT_QUOTES, 'UTF-8') ?>"
                      data-account-identifier="<?= htmlspecialchars((string) ($account['identifiant'] ?? $defaultIdentifier), ENT_QUOTES, 'UTF-8') ?>"
                    >
                      <i class="bi bi-person-gear" aria-hidden="true"></i><?= $hasAccount ? 'Configurer' : 'Créer l’accès' ?>
                    </button>
                    <?php if ($hasAccount): ?>
                      <?php $nextStatus = ($account['statut'] ?? '') === 'Actif' ? 'Inactif' : 'Actif'; ?>
                      <form method="post" action="<?= BASE_URL ?>/agents/updateAccountStatus">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="agent_id" value="<?= (int) $agent['id'] ?>">
                        <input type="hidden" name="statut" value="<?= $nextStatus ?>">
                        <button type="submit" class="agents-button">
                          <i class="bi bi-<?= $nextStatus === 'Actif' ? 'check-circle' : 'pause-circle' ?>" aria-hidden="true"></i><?= $nextStatus === 'Actif' ? 'Activer' : 'Désactiver' ?>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if (!empty($agents)): ?>
  <div id="agentAccountModal" popover="auto" class="agents-dialog agents-dialog-small">
        <form method="post" action="<?= BASE_URL ?>/agents/createAccount" class="agents-form">
          <div class="agents-dialog-header">
            <h2 id="agentAccountModalLabel">Accès utilisateur de l’agent</h2>
            <button type="button" class="agents-close" popovertarget="agentAccountModal" popovertargetaction="hide" aria-label="Fermer">&times;</button>
          </div>
          <div class="agents-dialog-body">
            <p class="agents-help">Agent : <strong id="agentAccountName"></strong></p>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="agent_id" id="agentAccountId">
            <div class="agents-field">
              <label for="agentAccountRole">Fonction du compte</label>
              <select name="account_role" id="agentAccountRole" required>
                <?php foreach ($agentAccountRoles as $roleKey => $roleName): ?>
                  <option value="<?= htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
              <span class="agents-help">La fonction est proposée selon le poste de l’agent ; vous pouvez la modifier.</span>
            </div>
            <div class="agents-field">
              <label for="agentAccountIdentifier">Identifiant de connexion</label>
              <input type="text" name="identifiant" id="agentAccountIdentifier" maxlength="100" required autocomplete="username">
            </div>
            <div class="agents-field">
              <label for="agentAccountPassword">Mot de passe initial</label>
              <input type="password" name="mot_de_passe" id="agentAccountPassword" minlength="8" maxlength="72" required autocomplete="new-password">
              <span class="agents-help">Choisissez au moins 8 caractères et communiquez-le à l’agent de manière sécurisée.</span>
            </div>
            <div class="agents-field">
              <label for="agentAccountPasswordConfirm">Confirmer le mot de passe</label>
              <input type="password" name="mot_de_passe_confirm" id="agentAccountPasswordConfirm" minlength="8" maxlength="72" required autocomplete="new-password">
            </div>
            <div class="agents-dialog-note">Le compte sera activé immédiatement et lié à cette fiche agent.</div>
          </div>
          <div class="agents-dialog-footer">
            <button type="button" class="agents-button" popovertarget="agentAccountModal" popovertargetaction="hide">Annuler</button>
            <button type="submit" class="agents-button is-primary"><i class="bi bi-check2-circle" aria-hidden="true"></i>Enregistrer l’accès</button>
          </div>
        </form>
  </div>
  <script>
    document.querySelectorAll('[popovertarget="agentAccountModal"]').forEach(function (button) {
      button.addEventListener('click', function () {
        document.getElementById('agentAccountId').value = button.dataset.agentId || '';
        document.getElementById('agentAccountName').textContent = button.dataset.agentName || '';
        document.getElementById('agentAccountRole').value = button.dataset.accountRole || 'agent_école';
        document.getElementById('agentAccountIdentifier').value = button.dataset.accountIdentifier || '';
        document.getElementById('agentAccountPassword').value = '';
        document.getElementById('agentAccountPasswordConfirm').value = '';
      });
    });

    document.getElementById('agentAccountModal').querySelector('form').addEventListener('submit', function (event) {
      const password = document.getElementById('agentAccountPassword').value;
      const confirmation = document.getElementById('agentAccountPasswordConfirm').value;
      if (password !== confirmation) {
        event.preventDefault();
        document.getElementById('agentAccountPasswordConfirm').setCustomValidity('Les mots de passe ne correspondent pas.');
        document.getElementById('agentAccountPasswordConfirm').reportValidity();
      }
    });
    document.getElementById('agentAccountPasswordConfirm').addEventListener('input', function () {
      this.setCustomValidity('');
    });
  </script>
<?php endif; ?>

<div id="createAgentModal" popover="auto" class="agents-dialog">
  <form method="post" action="<?= BASE_URL ?>/agents/createAgent" id="createAgentForm" class="agents-form">
        <div class="agents-dialog-header">
          <h2 id="createAgentModalLabel">Ajouter un agent et son accès</h2>
          <button type="button" class="agents-close" popovertarget="createAgentModal" popovertargetaction="hide" aria-label="Fermer">&times;</button>
        </div>
        <div class="agents-dialog-body">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
          <div class="agents-form-grid">
            <div class="agents-field">
              <label for="newAgentNom">Nom</label>
              <input type="text" name="nom" id="newAgentNom" maxlength="50" required>
            </div>
            <div class="agents-field">
              <label for="newAgentPostnom">Postnom</label>
              <input type="text" name="postnom" id="newAgentPostnom" maxlength="50" required>
            </div>
            <div class="agents-field">
              <label for="newAgentPrenom">Prénom</label>
              <input type="text" name="prenom" id="newAgentPrenom" maxlength="50">
            </div>
            <div class="agents-field">
              <label for="newAgentTelephone">Téléphone</label>
              <input type="tel" name="telephone" id="newAgentTelephone" maxlength="20" required>
            </div>
            <div class="agents-field">
              <label for="newAgentEmail">E-mail <span class="agents-help">(facultatif)</span></label>
              <input type="email" name="email" id="newAgentEmail" maxlength="255">
            </div>
            <div class="agents-field">
              <label for="newAgentFunction">Fonction sur la fiche agent</label>
              <select name="role_id" id="newAgentFunction" required>
                <option value="">Choisir une fonction</option>
                <?php foreach (($agentFunctions ?? []) as $function): ?>
                  <option value="<?= (int) $function['id'] ?>"><?= htmlspecialchars($function['titre_role'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="agents-field">
              <label for="newAgentAccountRole">Rôle d’accès à l’application</label>
              <select name="account_role" id="newAgentAccountRole" required>
                <?php foreach ($agentAccountRoles as $roleKey => $roleName): ?>
                  <option value="<?= htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if (!empty($unlinkedAccounts)): ?>
              <div class="agents-field is-full">
                <label for="newAgentExistingAccount">Compte de connexion</label>
                <select name="existing_account_id" id="newAgentExistingAccount">
                  <option value="0">Créer un nouvel accès</option>
                  <?php foreach ($unlinkedAccounts as $unlinkedAccount): ?>
                    <option
                      value="<?= (int) $unlinkedAccount['id'] ?>"
                      data-role="<?= htmlspecialchars($unlinkedAccount['role'], ENT_QUOTES, 'UTF-8') ?>"
                      data-identifier="<?= htmlspecialchars($unlinkedAccount['identifiant'], ENT_QUOTES, 'UTF-8') ?>"
                      data-name="<?= htmlspecialchars($unlinkedAccount['nom_complet'], ENT_QUOTES, 'UTF-8') ?>"
                      data-function-id="<?= (int) ($unlinkedAccount['suggested_function_id'] ?? 0) ?>"
                    >
                      Rattacher <?= htmlspecialchars($unlinkedAccount['nom_complet'], ENT_QUOTES, 'UTF-8') ?>
                      — <?= htmlspecialchars(\App\Models\User::getRoleLabel($unlinkedAccount['role']), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <span class="agents-help">Le mot de passe existant est conservé si vous laissez les champs de mot de passe vides.</span>
              </div>
            <?php endif; ?>
            <div class="agents-field">
              <label for="newAgentIdentifier">Identifiant de connexion</label>
              <input type="text" name="identifiant" id="newAgentIdentifier" maxlength="100" required autocomplete="username">
            </div>
            <div class="agents-field">
              <label for="newAgentPassword">Mot de passe initial</label>
              <input type="password" name="mot_de_passe" id="newAgentPassword" minlength="8" maxlength="72" autocomplete="new-password">
              <span class="agents-help" id="newAgentPasswordHelp">Obligatoire pour créer un nouvel accès (8 caractères minimum).</span>
            </div>
            <div class="agents-field" id="newAgentPasswordConfirmField">
              <label for="newAgentPasswordConfirm">Confirmer le mot de passe</label>
              <input type="password" name="mot_de_passe_confirm" id="newAgentPasswordConfirm" minlength="8" maxlength="72" autocomplete="new-password">
            </div>
          </div>
          <div class="agents-dialog-note">La fiche et l’accès utilisateur seront enregistrés ensemble. Le rôle d’accès détermine les autorisations dans l’application.</div>
        </div>
        <div class="agents-dialog-footer">
          <button type="button" class="agents-button" popovertarget="createAgentModal" popovertargetaction="hide">Annuler</button>
          <button type="submit" class="agents-button is-primary"><i class="bi bi-check2-circle" aria-hidden="true"></i>Enregistrer l’agent</button>
        </div>
      </form>
</div>
<script>
  (function () {
    const form = document.getElementById('createAgentForm');
    const email = document.getElementById('newAgentEmail');
    const telephone = document.getElementById('newAgentTelephone');
    const identifier = document.getElementById('newAgentIdentifier');
    const password = document.getElementById('newAgentPassword');
    const passwordConfirm = document.getElementById('newAgentPasswordConfirm');
    const accountSelect = document.getElementById('newAgentExistingAccount');
    const accountRole = document.getElementById('newAgentAccountRole');
    const passwordHelp = document.getElementById('newAgentPasswordHelp');
    const nameFields = [
      document.getElementById('newAgentNom'),
      document.getElementById('newAgentPostnom'),
      document.getElementById('newAgentPrenom')
    ];
    const functionSelect = document.getElementById('newAgentFunction');
    const originalValues = {
      nom: nameFields[0].value,
      postnom: nameFields[1].value,
      prenom: nameFields[2].value,
      telephone: telephone.value,
      email: email.value,
      identifier: identifier.value,
      accountRole: accountRole.value,
      functionId: functionSelect.value
    };

    function updateIdentifier() {
      if (identifier.dataset.userEdited === 'true') return;
      identifier.value = email.value.trim() || telephone.value.trim();
    }

    function restoreNewAccountFields() {
      nameFields[0].value = originalValues.nom;
      nameFields[1].value = originalValues.postnom;
      nameFields[2].value = originalValues.prenom;
      telephone.value = originalValues.telephone;
      email.value = originalValues.email;
      identifier.value = originalValues.identifier;
      identifier.dataset.userEdited = 'false';
      accountRole.value = originalValues.accountRole;
      functionSelect.value = originalValues.functionId;
    }

    function updateExistingAccount() {
      const option = accountSelect ? accountSelect.selectedOptions[0] : null;
      const isExistingAccount = option && option.value !== '0';
      password.required = !isExistingAccount;
      passwordConfirm.required = !isExistingAccount;
      passwordHelp.textContent = isExistingAccount
        ? 'Laissez vide pour conserver le mot de passe actuel, ou saisissez-en un nouveau.'
        : 'Obligatoire pour créer un nouvel accès (8 caractères minimum).';
      if (isExistingAccount) {
        const nameParts = (option.dataset.name || '').trim().split(/\s+/).filter(Boolean);
        nameFields[0].value = nameParts.shift() || '';
        nameFields[1].value = nameParts.shift() || '';
        nameFields[2].value = nameParts.join(' ');
        const existingIdentifier = (option.dataset.identifier || '').trim();
        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(existingIdentifier);
        email.value = isEmail ? existingIdentifier : '';
        telephone.value = !isEmail ? existingIdentifier : '';
        identifier.value = option.dataset.identifier || '';
        identifier.dataset.userEdited = 'false';
        const roleAliases = { 'agent_ecole': 'agent_école', 'préfet_école': 'prefet_école' };
        accountRole.value = roleAliases[option.dataset.role] || option.dataset.role || accountRole.value;
        if (option.dataset.functionId && option.dataset.functionId !== '0') {
          functionSelect.value = option.dataset.functionId;
        }
        password.value = '';
        passwordConfirm.value = '';
      } else {
        restoreNewAccountFields();
        updateIdentifier();
      }
    }

    email.addEventListener('input', updateIdentifier);
    telephone.addEventListener('input', updateIdentifier);
    identifier.addEventListener('input', function () {
      this.dataset.userEdited = 'true';
    });
    if (accountSelect) accountSelect.addEventListener('change', updateExistingAccount);
    form.addEventListener('submit', function (event) {
      if (password.value !== passwordConfirm.value) {
        event.preventDefault();
        passwordConfirm.setCustomValidity('Les mots de passe ne correspondent pas.');
        passwordConfirm.reportValidity();
      }
    });
    passwordConfirm.addEventListener('input', function () {
      this.setCustomValidity('');
    });
    updateIdentifier();
    updateExistingAccount();
  })();
</script>
</div>
