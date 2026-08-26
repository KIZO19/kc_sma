<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$totalPaidByCurrency = $totalPaidByCurrency ?? [];
$totalDebtByCurrency = $totalDebtByCurrency ?? [];
$entryTotalsByCurrency = $entryTotalsByCurrency ?? [];
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Fiche élève</h1>
              <p class="text-muted"><?php echo htmlspecialchars(($eleve['nom'] ?? '') . ' ' . ($eleve['postnom'] ?? '') . ' ' . ($eleve['prenom'] ?? '')); ?></p>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/eleves">Élèves</a></li>
                <li class="breadcrumb-item active">Fiche</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Identité</h3></div>
                <div class="card-body">
                  <p><strong>Matricule:</strong> <?= htmlspecialchars($eleve['matricule'] ?? '-') ?></p>
                  <p><strong>Nom:</strong> <?= htmlspecialchars($eleve['nom'] ?? '-') ?></p>
                  <p><strong>Postnom:</strong> <?= htmlspecialchars($eleve['postnom'] ?? '-') ?></p>
                  <p><strong>Prénom:</strong> <?= htmlspecialchars($eleve['prenom'] ?? '-') ?></p>
                    <?php
                    $dobRaw = $eleve['date_naissance'] ?? null;
                    $ageDisplay = '-';
                    if (!empty($dobRaw)) {
                      try {
                        $dobDt = new \DateTime($dobRaw);
                        $nowDt = new \DateTime();
                        $diff = $nowDt->diff($dobDt);
                        $ageDisplay = $diff->y . ' ans';
                        if ($diff->y === 0 && $diff->m > 0) {
                          $ageDisplay = $diff->m . ' mois';
                        } elseif ($diff->y === 0 && $diff->m === 0) {
                          $ageDisplay = $diff->d . ' jours';
                        }
                      } catch (\Exception $e) {
                        $ageDisplay = '-';
                      }
                    }
                    ?>
                    <p><strong>Date de naissance:</strong> <?= htmlspecialchars(formatDate($dobRaw ?? null)) ?> <small class="text-muted">(Âge: <?= htmlspecialchars($ageDisplay) ?>)</small></p>
                  <p><strong>Adresse:</strong> <?= nl2br(htmlspecialchars($eleve['adresse'] ?? '-')) ?></p>
                  <p><strong>Parent/Tuteur:</strong> <?= htmlspecialchars($eleve['nom_pere'] ?? ($eleve['parent_nom_responsable'] ?? '-')) ?></p>
                </div>
              </div>

              <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Situation comptable</h3></div>
                <div class="card-body">
                  <?php if (!empty($compte)): ?>
                    <?php $currencies = array_unique(array_merge(array_keys($totalPaidByCurrency), array_keys($totalDebtByCurrency), ['USD'])); sort($currencies); ?>
                    <div class="table-responsive">
                      <table class="table table-sm table-bordered mb-3">
                        <thead>
                          <tr><th>Devise</th><th>Total payé</th><th>Dette restante</th></tr>
                        </thead>
                        <tbody>
                          <?php foreach ($currencies as $currency): ?>
                            <tr>
                              <td><strong><?= htmlspecialchars($currency) ?></strong></td>
                              <td><?= number_format((float) ($totalPaidByCurrency[$currency] ?? 0), 2, ',', ' ') ?></td>
                              <td><?= number_format((float) ($totalDebtByCurrency[$currency] ?? 0), 2, ',', ' ') ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    <?php if (!empty($dettes)): ?>
                      <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered">
                          <thead>
                            <tr>
                              <th>Frais</th>
                              <th>Montant initial</th>
                              <th>Restant</th>
                              <th>Portée</th>
                              <th>Année</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($dettes as $d): ?>
                              <tr>
                                <td><?= htmlspecialchars($d['type_frais'] ?? '-') ?></td>
                                <td><?= number_format((float) ($d['montant_initial'] ?? 0), 2) ?> <?= htmlspecialchars($d['devise'] ?? 'USD') ?></td>
                                <td><?= number_format((float) ($d['montant_restant'] ?? 0), 2) ?> <?= htmlspecialchars($d['devise'] ?? 'USD') ?></td>
                                <td><?= htmlspecialchars($d['scope_label'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['annee_scolaire'] ?? '-') ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php else: ?>
                      <div class="alert alert-info">Aucune dette en cours pour cet élève.</div>
                    <?php endif; ?>
                    <p>
                      <a href="#ecritures" class="btn btn-sm btn-outline-primary">Voir écritures</a>
                      <?php if (in_array($role, ['super_admin','comptable_école'], true)): ?>
                        <a href="<?= BASE_URL ?>/paiements/create?eleve_id=<?= (int) ($eleve['id'] ?? 0) ?>" class="btn btn-sm btn-success">Enregistrer paiement</a>
                      <?php endif; ?>
                    </p>
                  <?php else: ?>
                    <div class="alert alert-info">Aucun compte trouvé pour cet élève.</div>
                    <p>
                      <?php if (in_array($role, ['super_admin','comptable_école'], true)): ?>
                        <a href="<?= BASE_URL ?>/paiements/create?eleve_id=<?= (int) ($eleve['id'] ?? 0) ?>" class="btn btn-sm btn-success">Enregistrer paiement</a>
                      <?php endif; ?>
                    </p>
                  <?php endif; ?>
                </div>
              </div>

            </div>
            <div class="col-md-8">
              <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Notes</h3></div>
                <div class="card-body">
                  <?php if (empty($notes)): ?>
                    <div class="alert alert-info">Aucune note trouvée.</div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-sm table-striped">
                        <thead>
                          <tr><th>#</th><th>Evaluation</th><th>Date</th><th>Note</th><th>Pond.</th></tr>
                        </thead>
                        <tbody>
                          <?php foreach ($notes as $i => $n): ?>
                            <tr>
                              <td><?= $i+1 ?></td>
                              <td><?= htmlspecialchars($n['attribution_cours_id'] ?? 'N/A') ?></td>
                              <td><?= htmlspecialchars(formatDate($n['date_evaluation'] ?? null)) ?></td>
                              <td><?= htmlspecialchars($n['note_obtenue']) ?></td>
                              <td><?= htmlspecialchars($n['ponderation_max'] ?? '-') ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title">Discipline</h3></div>
                <div class="card-body">
                  <?php if (empty($discipline)): ?>
                    <div class="alert alert-info">Aucun incident enregistré.</div>
                  <?php else: ?>
                    <ul class="list-group">
                      <?php foreach ($discipline as $d): ?>
                        <li class="list-group-item">
                          <strong><?= htmlspecialchars(formatDate($d['date_evenement'] ?? null)) ?></strong> — <?= htmlspecialchars($d['faute'] ?? '') ?>
                          <?php if (!empty($d['sanction'])): ?>
                            <div class="text-muted">Sanction: <?= htmlspecialchars($d['sanction']) ?></div>
                          <?php endif; ?>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card card-outline card-light">
                <div class="card-header"><h3 class="card-title">Écritures comptables</h3></div>
                <div class="card-body" id="ecritures">
                  <?php if (!empty($entryTotalsByCurrency)): ?>
                    <div class="table-responsive mb-3">
                      <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>Devise</th><th>Total débit</th><th>Total crédit</th></tr></thead>
                        <tbody>
                          <?php foreach ($entryTotalsByCurrency as $currency => $totals): ?>
                            <tr>
                              <td><strong><?= htmlspecialchars($currency) ?></strong></td>
                              <td><?= number_format((float) ($totals['DEBIT'] ?? 0), 2, ',', ' ') ?></td>
                              <td><?= number_format((float) ($totals['CREDIT'] ?? 0), 2, ',', ' ') ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                  <?php if (empty($ecritures)): ?>
                    <div class="alert alert-info">Aucune écriture comptable trouvée.</div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-sm table-striped">
                        <thead><tr><th>#</th><th>Date</th><th>Type</th><th>Montant</th><th>Devise</th><th>Référence</th><th>Libellé</th></tr></thead>
                        <tbody>
                          <?php foreach ($ecritures as $i => $ec): ?>
                            <tr>
                              <td><?= $i+1 ?></td>
                              <td><?= htmlspecialchars(formatDate($ec['date_operation'] ?? null)) ?></td>
                              <td><span class="badge <?= (($ec['type_mouvement'] ?? '') === 'CREDIT') ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($ec['type_mouvement'] ?? '') ?></span></td>
                              <td><?= htmlspecialchars(number_format((float) ($ec['montant'] ?? 0), 2, ',', ' ')) ?></td>
                              <td><?= htmlspecialchars($ec['frais_devise'] ?? 'USD') ?></td>
                              <td><?= htmlspecialchars($ec['reference_recu'] ?? '-') ?></td>
                              <td><?= htmlspecialchars($ec['libelle'] ?? '') ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>