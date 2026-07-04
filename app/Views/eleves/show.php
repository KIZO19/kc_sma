<?php require __DIR__ . '/../partials/app_header.php'; ?>
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
                    <p><strong>Date de naissance:</strong> <?= htmlspecialchars($dobRaw ?? '-') ?> <small class="text-muted">(Âge: <?= htmlspecialchars($ageDisplay) ?>)</small></p>
                  <p><strong>Adresse:</strong> <?= nl2br(htmlspecialchars($eleve['adresse'] ?? '-')) ?></p>
                  <p><strong>Parent/Tuteur:</strong> <?= htmlspecialchars($eleve['nom_pere'] ?? ($eleve['parent_nom_responsable'] ?? '-')) ?></p>
                </div>
              </div>

              <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Situation comptable</h3></div>
                <div class="card-body">
                  <?php if (!empty($compte)): ?>
                    <p><strong>Solde dû:</strong> <?= number_format((float) ($compte['solde_debiteur'] ?? 0), 2) ?></p>
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
                              <td><?= htmlspecialchars($n['date_evaluation'] ?? '-') ?></td>
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
                          <strong><?= htmlspecialchars($d['date_evenement'] ?? '') ?></strong> — <?= htmlspecialchars($d['faute'] ?? '') ?>
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
                  <?php if (empty($ecritures)): ?>
                    <div class="alert alert-info">Aucune écriture comptable trouvée.</div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-sm table-striped">
                        <thead><tr><th>#</th><th>Date</th><th>Type</th><th>Montant</th><th>Libellé</th></tr></thead>
                        <tbody>
                          <?php foreach ($ecritures as $i => $ec): ?>
                            <tr>
                              <td><?= $i+1 ?></td>
                              <td><?= htmlspecialchars($ec['date_operation'] ?? '') ?></td>
                              <td><?= htmlspecialchars($ec['type_mouvement'] ?? '') ?></td>
                              <td><?= htmlspecialchars(number_format((float) ($ec['montant'] ?? 0), 2)) ?></td>
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