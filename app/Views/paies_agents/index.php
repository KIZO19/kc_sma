<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$agents = $agents ?? [];
$slips = $slips ?? [];
$accounts = $accounts ?? [];
$currencies = $currencies ?? [];
$missingTables = $missingTables ?? [];
$month = (int) ($month ?? date('n'));
$year = (int) ($year ?? date('Y'));
$csrfToken = (string) ($csrfToken ?? '');
$formatAmount = static fn ($amount): string => number_format((float) $amount, 2, ',', ' ');
$slipsByAgent = [];
foreach ($slips as $slip) {
    $slipsByAgent[(int) $slip['agent_id']] = $slip;
}
$monthNames = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
];
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Paie des agents</h1>
    <p class="text-muted mb-0">Préparez les fiches mensuelles et soumettez les paiements à l’approbation avant tout débit de la caisse ou de la banque.</p>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['paies_agents_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['paies_agents_success'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php unset($_SESSION['paies_agents_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['paies_agents_error'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['paies_agents_error'], ENT_QUOTES, 'UTF-8') ?></div>
      <?php unset($_SESSION['paies_agents_error']); ?>
    <?php endif; ?>

    <?php if ($missingTables): ?>
      <div class="alert alert-warning" role="alert">
        <h2 class="h5 alert-heading">Installation du module requise</h2>
        <p>La base de données ne contient pas encore toutes les tables de paie et d’approbation. Tables manquantes :</p>
        <ul>
          <?php foreach ($missingTables as $table): ?>
            <li><code><?= htmlspecialchars((string) $table, ENT_QUOTES, 'UTF-8') ?></code></li>
          <?php endforeach; ?>
        </ul>
        <p class="mb-0">
          Dans phpMyAdmin, sélectionnez la base <code>school_management_db</code>, puis exécutez dans cet ordre
          <code>app/Config/migration_create_agent_payroll.sql</code> et
          <code>app/Config/migration_create_cash_outflows_approval.sql</code>, si elles n’ont pas déjà été appliquées.
          Les tables existantes ne sont pas supprimées par ces migrations.
        </p>
      </div>
    <?php else: ?>
    <div class="card card-outline card-primary">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h2 class="h5 mb-0">Période de paie</h2>
        <form method="get" action="<?= BASE_URL ?>/paies-agents" class="d-flex flex-wrap gap-2 align-items-center">
          <label class="visually-hidden" for="paieMonth">Mois</label>
          <select class="form-select form-select-sm" id="paieMonth" name="mois">
            <?php foreach ($monthNames as $number => $label): ?>
              <option value="<?= $number ?>" <?= $month === $number ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
          <label class="visually-hidden" for="paieYear">Année</label>
          <input class="form-control form-control-sm" id="paieYear" type="number" name="annee" min="2000" max="2100" value="<?= $year ?>" style="width: 100px">
          <button class="btn btn-sm btn-primary" type="submit">Afficher</button>
        </form>
      </div>
      <div class="card-body">
        <p class="small text-muted">Le net est calculé ainsi : salaire de base + primes − retenues. La devise est définie agent par agent. Le paiement n’est débité qu’après approbation par un rôle désigné dans les paramètres de l’école.</p>
        <?php if (!$agents): ?>
          <div class="alert alert-info mb-0">Aucun agent n’est enregistré pour cette école.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Agent</th>
                  <th style="min-width: 290px">Salaire de base et devise</th>
                  <th>Fiche du mois</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($agents as $agent): ?>
                  <?php
                    $agentId = (int) $agent['id'];
                    $savedCurrency = (string) ($agent['devise_salaire'] ?? '');
                    $existingSlip = $slipsByAgent[$agentId] ?? null;
                  ?>
                  <tr>
                    <td>
                      <strong><?= htmlspecialchars(\App\Core\PersonName::format($agent), ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($agent['fonction'])): ?><div class="small text-muted"><?= htmlspecialchars($agent['fonction'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </td>
                    <td>
                      <form method="post" action="<?= BASE_URL ?>/paies-agents/updateSalary" class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="mois" value="<?= $month ?>">
                        <input type="hidden" name="annee" value="<?= $year ?>">
                        <input type="hidden" name="agent_id" value="<?= $agentId ?>">
                        <label class="visually-hidden" for="salary-<?= $agentId ?>">Salaire de base</label>
                        <input class="form-control form-control-sm" id="salary-<?= $agentId ?>" name="salaire_de_base" type="number" min="0" max="9999999999.99" step="0.01" value="<?= htmlspecialchars((string) $agent['salaire_de_base'], ENT_QUOTES, 'UTF-8') ?>" required style="width: 145px">
                        <label class="visually-hidden" for="currency-<?= $agentId ?>">Devise du salaire</label>
                        <select class="form-select form-select-sm" id="currency-<?= $agentId ?>" name="devise" required style="width: 100px">
                          <option value="">Devise…</option>
                          <?php foreach ($currencies as $currency): ?>
                            <option value="<?= htmlspecialchars($currency['code'], ENT_QUOTES, 'UTF-8') ?>" <?= $savedCurrency === $currency['code'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars($currency['code'] . ' — ' . $currency['libelle'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-outline-primary" type="submit">Enregistrer</button>
                      </form>
                      <?php if ($savedCurrency === ''): ?><div class="small text-warning mt-1">Définissez la devise avant de créer une fiche.</div><?php endif; ?>
                    </td>
                    <td>
                      <?php if ($existingSlip): ?>
                        <span class="badge rounded-pill text-bg-<?= $existingSlip['statut_paiement'] === 'Payé' ? 'success' : 'warning' ?>">
                          <?= htmlspecialchars($existingSlip['statut_paiement'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <div class="small mt-1"><?= $formatAmount($existingSlip['net_a_payer']) ?> <?= htmlspecialchars($existingSlip['devise'], ENT_QUOTES, 'UTF-8') ?></div>
                      <?php elseif ($savedCurrency === ''): ?>
                        <span class="text-muted small">Enregistrez d’abord le salaire et sa devise.</span>
                      <?php else: ?>
                        <details>
                          <summary class="btn btn-sm btn-outline-success">Préparer la fiche</summary>
                          <form method="post" action="<?= BASE_URL ?>/paies-agents/createSlip" class="mt-2 payroll-slip-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="mois" value="<?= $month ?>">
                            <input type="hidden" name="annee" value="<?= $year ?>">
                            <input type="hidden" name="agent_id" value="<?= $agentId ?>">
                            <div class="small mb-2">Base : <?= $formatAmount($agent['salaire_de_base']) ?> <?= htmlspecialchars($savedCurrency, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="mb-2">
                              <strong class="small">Primes</strong>
                              <div data-payroll-lines="primes">
                                <div class="d-flex gap-1 mt-1 payroll-line">
                                  <input class="form-control form-control-sm" name="primes_libelle[]" placeholder="Libellé de la prime" maxlength="150">
                                  <input class="form-control form-control-sm" name="primes_montant[]" type="number" min="0" step="0.01" placeholder="Montant" style="max-width: 110px">
                                </div>
                              </div>
                              <button type="button" class="btn btn-link btn-sm p-0" data-add-payroll-line="primes">Ajouter une prime</button>
                            </div>
                            <div class="mb-2">
                              <strong class="small">Retenues</strong>
                              <div data-payroll-lines="retenues">
                                <div class="d-flex gap-1 mt-1 payroll-line">
                                  <input class="form-control form-control-sm" name="retenues_libelle[]" placeholder="Libellé de la retenue" maxlength="150">
                                  <input class="form-control form-control-sm" name="retenues_montant[]" type="number" min="0" step="0.01" placeholder="Montant" style="max-width: 110px">
                                </div>
                              </div>
                              <button type="button" class="btn btn-link btn-sm p-0" data-add-payroll-line="retenues">Ajouter une retenue</button>
                            </div>
                            <button class="btn btn-sm btn-success" type="submit">Créer la fiche</button>
                          </form>
                        </details>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card card-outline card-secondary">
      <div class="card-header"><h2 class="h5 mb-0">Fiches de <?= htmlspecialchars($monthNames[$month] . ' ' . $year, ENT_QUOTES, 'UTF-8') ?></h2></div>
      <div class="card-body">
        <?php if (!$slips): ?>
          <p class="text-muted mb-0">Aucune fiche de paie pour cette période.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead><tr><th>Agent</th><th>Base</th><th>Primes</th><th>Retenues</th><th>Net à payer</th><th>Statut / paiement</th></tr></thead>
              <tbody>
                <?php foreach ($slips as $slip): ?>
                  <tr>
                    <td>
                      <strong><?= htmlspecialchars(\App\Core\PersonName::format($slip), ENT_QUOTES, 'UTF-8') ?></strong>
                      <?php if (!empty($slip['fonction'])): ?><div class="small text-muted"><?= htmlspecialchars($slip['fonction'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                      <?php if (!empty($slip['lignes'])): ?>
                        <details class="small mt-1"><summary>Détail des ajustements</summary>
                          <ul class="mb-0 mt-1">
                            <?php foreach ($slip['lignes'] as $line): ?>
                              <li><?= $line['type_ligne'] === 'prime' ? 'Prime' : 'Retenue' ?> — <?= htmlspecialchars($line['libelle'], ENT_QUOTES, 'UTF-8') ?> : <?= $formatAmount($line['montant']) ?> <?= htmlspecialchars($slip['devise'], ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                          </ul>
                        </details>
                      <?php endif; ?>
                      <?php if ($slip['statut_paiement'] === 'En attente'): ?>
                        <details class="small mt-1">
                          <summary class="btn btn-link btn-sm p-0">Modifier les ajustements</summary>
                          <form method="post" action="<?= BASE_URL ?>/paies-agents/updateSlip" class="mt-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="mois" value="<?= $month ?>">
                            <input type="hidden" name="annee" value="<?= $year ?>">
                            <input type="hidden" name="paie_id" value="<?= (int) $slip['id'] ?>">
                            <?php foreach (['prime' => 'primes', 'retenue' => 'retenues'] as $lineType => $fieldName): ?>
                              <?php
                                $matchingLines = array_values(array_filter(
                                    $slip['lignes'],
                                    static fn (array $line): bool => $line['type_ligne'] === $lineType
                                ));
                                if (!$matchingLines) $matchingLines = [['libelle' => '', 'montant' => '']];
                              ?>
                              <strong><?= $lineType === 'prime' ? 'Primes' : 'Retenues' ?></strong>
                              <div data-payroll-lines="<?= $fieldName ?>">
                                <?php foreach ($matchingLines as $line): ?>
                                  <div class="d-flex gap-1 mt-1 payroll-line">
                                    <input class="form-control form-control-sm" name="<?= $fieldName ?>_libelle[]" value="<?= htmlspecialchars($line['libelle'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Libellé" maxlength="150">
                                    <input class="form-control form-control-sm" name="<?= $fieldName ?>_montant[]" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string) $line['montant'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Montant" style="max-width: 110px">
                                  </div>
                                <?php endforeach; ?>
                              </div>
                              <button type="button" class="btn btn-link btn-sm p-0" data-add-payroll-line="<?= $fieldName ?>">Ajouter</button>
                              <br>
                            <?php endforeach; ?>
                            <button class="btn btn-sm btn-outline-primary mt-1" type="submit">Enregistrer les ajustements</button>
                          </form>
                        </details>
                      <?php endif; ?>
                    </td>
                    <td><?= $formatAmount($slip['salaire_base_historique']) ?> <?= htmlspecialchars($slip['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $formatAmount($slip['total_primes']) ?> <?= htmlspecialchars($slip['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $formatAmount($slip['total_retenues']) ?> <?= htmlspecialchars($slip['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="fw-semibold"><?= $formatAmount($slip['net_a_payer']) ?> <?= htmlspecialchars($slip['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                      <?php if ($slip['statut_paiement'] === 'Payé'): ?>
                        <span class="badge text-bg-success">Payé</span>
                        <div class="small"><?= htmlspecialchars($slip['nom_compte'] ?? 'Compte supprimé', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="small text-muted"><?= htmlspecialchars((string) ($slip['date_paiement_effectif'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if (!empty($slip['payeur_nom'])): ?><div class="small text-muted">Enregistré par <?= htmlspecialchars($slip['payeur_nom'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                      <?php elseif (($slip['sortie_statut'] ?? null) === 'En attente'): ?>
                        <span class="badge text-bg-warning">En attente d’approbation</span>
                        <div class="small text-muted mt-1">Le compte ne sera débité qu’après validation.</div>
                        <a class="btn btn-sm btn-outline-secondary mt-1" href="<?= BASE_URL ?>/comptes">Voir les sorties</a>
                      <?php else: ?>
                        <?php if (($slip['sortie_statut'] ?? null) === 'Refusée'): ?>
                          <div class="small text-danger mb-1">La demande précédente a été refusée. Vous pouvez la soumettre à nouveau.</div>
                        <?php endif; ?>
                        <form method="post" action="<?= BASE_URL ?>/paies-agents/paySlip" onsubmit="return confirm('Soumettre cette paie pour approbation ? Le compte ne sera pas débité avant validation.')">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="mois" value="<?= $month ?>">
                          <input type="hidden" name="annee" value="<?= $year ?>">
                          <input type="hidden" name="paie_id" value="<?= (int) $slip['id'] ?>">
                          <label class="visually-hidden" for="account-<?= (int) $slip['id'] ?>">Caisse ou banque</label>
                          <select class="form-select form-select-sm mb-1" id="account-<?= (int) $slip['id'] ?>" name="caisse_banque_id" required>
                            <option value="">Compte de paiement…</option>
                            <?php foreach ($accounts as $account): ?>
                              <?php if ($account['devise'] === $slip['devise']): ?>
                                <option value="<?= (int) $account['id'] ?>" <?= (float) $account['solde_actuel'] < (float) $slip['net_a_payer'] ? 'disabled' : '' ?>>
                                  <?= htmlspecialchars($account['nom_compte'] . ' · ' . $account['type_compte'] . ' · Solde ' . $formatAmount($account['solde_actuel']) . ' ' . $account['devise'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </select>
                          <button class="btn btn-sm btn-outline-success" type="submit">Soumettre pour approbation</button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card card-outline card-info">
      <div class="card-header"><h2 class="h5 mb-0">Devises des caisses et banques</h2></div>
      <div class="card-body">
        <p class="small text-muted">Les comptes existants sont initialisés en USD. Un compte ayant un historique de paie ne peut pas changer de devise. Si son solde est non nul, confirmez qu’il est déjà exprimé dans la devise choisie : aucun montant ne sera converti.</p>
        <?php if (!$accounts): ?>
          <p class="mb-0">Aucune caisse ou banque n’est configurée pour cette école.</p>
        <?php else: ?>
          <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Compte</th><th>Solde</th><th>Devise</th><th>Enregistrer</th></tr></thead>
            <tbody>
              <?php foreach ($accounts as $account): ?>
                <tr>
                  <td><?= htmlspecialchars($account['nom_compte'] . ' · ' . $account['type_compte'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= $formatAmount($account['solde_actuel']) ?> <?= htmlspecialchars($account['devise'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <form id="account-currency-<?= (int) $account['id'] ?>" method="post" action="<?= BASE_URL ?>/paies-agents/updateAccountCurrency">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="mois" value="<?= $month ?>">
                      <input type="hidden" name="annee" value="<?= $year ?>">
                      <input type="hidden" name="caisse_banque_id" value="<?= (int) $account['id'] ?>">
                      <select class="form-select form-select-sm" name="devise" aria-label="Devise de <?= htmlspecialchars($account['nom_compte'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php foreach ($currencies as $currency): ?>
                          <option value="<?= htmlspecialchars($currency['code'], ENT_QUOTES, 'UTF-8') ?>" <?= $account['devise'] === $currency['code'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($currency['code'], ENT_QUOTES, 'UTF-8') ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <label class="small text-muted mt-1">
                        <input type="checkbox" name="confirmer_solde_devise" value="1">
                        Le solde actuel est déjà dans cette devise (aucune conversion).
                      </label>
                    </form>
                  </td>
                  <td><button class="btn btn-sm btn-outline-primary" type="submit" form="account-currency-<?= (int) $account['id'] ?>">Enregistrer</button></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<template id="payrollLineTemplate">
  <div class="d-flex gap-1 mt-1 payroll-line">
    <input class="form-control form-control-sm" data-name-suffix="_libelle" placeholder="Libellé" maxlength="150">
    <input class="form-control form-control-sm" data-name-suffix="_montant" type="number" min="0" step="0.01" placeholder="Montant" style="max-width: 110px">
  </div>
</template>
<script>
  document.querySelectorAll('[data-add-payroll-line]').forEach((button) => {
    button.addEventListener('click', () => {
      const type = button.dataset.addPayrollLine;
      const container = document.querySelector(`[data-payroll-lines="${type}"]`);
      const template = document.getElementById('payrollLineTemplate');
      if (!container || !template) return;
      const line = template.content.firstElementChild.cloneNode(true);
      line.querySelectorAll('[data-name-suffix]').forEach((input) => {
        input.name = `${type}${input.dataset.nameSuffix}[]`;
        input.removeAttribute('data-name-suffix');
      });
      container.appendChild(line);
    });
  });
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
