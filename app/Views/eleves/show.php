<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$totalPaidByCurrency = $totalPaidByCurrency ?? [];
$totalDebtByCurrency = $totalDebtByCurrency ?? [];
$entryTotalsByCurrency = $entryTotalsByCurrency ?? [];
$enrollmentHistory = $enrollmentHistory ?? [];
$feeStatuses = $feeStatuses ?? [];
$selectedYearLabel = $selectedEnrollment['annee'] ?? null;
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Fiche élève</h1>
              <p class="text-muted"><?= htmlspecialchars(\App\Core\PersonName::format($eleve)) ?></p>
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
          <?php if (!empty($enrollmentHistory)): ?>
            <div class="card card-outline card-primary">
              <div class="card-body">
                <form method="get" action="<?= BASE_URL ?>/eleves/show" class="row g-2 align-items-end">
                  <input type="hidden" name="id" value="<?= (int) ($eleve['id'] ?? 0) ?>">
                  <div class="col-md-5">
                    <label for="studentAcademicYear" class="form-label">Année scolaire à consulter</label>
                    <select class="form-select" id="studentAcademicYear" name="annee_scolaire_id">
                      <?php foreach ($enrollmentHistory as $enrollment): ?>
                        <option value="<?= (int) $enrollment['annee_scolaire_id'] ?>" <?= (int) $selectedYearId === (int) $enrollment['annee_scolaire_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($enrollment['annee'] ?? '-') ?> — <?= htmlspecialchars($enrollment['classe_label'] ?? '-') ?><?= !empty($enrollment['est_active']) ? ' (année active)' : '' ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-clock-history me-1" aria-hidden="true"></i>Afficher l’année
                    </button>
                  </div>
                  <?php if ($selectedYearLabel !== null): ?>
                    <div class="col-12">
                      <small class="text-muted">Consultation en lecture seule : situation de l’élève en <?= htmlspecialchars($selectedYearLabel) ?>.</small>
                    </div>
                  <?php endif; ?>
                </form>
              </div>
            </div>
          <?php endif; ?>
          <div class="row">
            <div class="col-md-4">
              <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Identité</h3></div>
                <div class="card-body">
                  <p><strong>Matricule:</strong> <?= htmlspecialchars($eleve['matricule'] ?? '-') ?></p>
                  <p><strong>Nom:</strong> <?= htmlspecialchars($eleve['nom'] ?? '-') ?></p>
                  <p><strong>Postnom:</strong> <?= htmlspecialchars($eleve['postnom'] ?? '-') ?></p>
                  <p><strong>Prénom:</strong> <?= htmlspecialchars($eleve['prenom'] ?? '-') ?></p>
                  <p><strong>Année consultée:</strong> <?= htmlspecialchars($selectedYearLabel ?? '-') ?></p>
                  <p><strong>Classe fréquentée cette année-là:</strong> <?= htmlspecialchars($currentClass ?? '-') ?></p>
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
                  <?php if (in_array($role, ['super_admin', 'comptable_école', 'préfet_école', 'sec_école'], true)): ?>
                    <a href="<?= BASE_URL ?>/inscriptions/edit?id=<?= (int) ($eleve['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">Modifier l’identité</a>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Situation comptable — <?= htmlspecialchars($selectedYearLabel ?? 'année non sélectionnée') ?></h3></div>
                <div class="card-body">
                  <?php if ($selectedYearId > 0): ?>
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
                    <?php if (!empty($feeStatuses)): ?>
                      <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered">
                          <thead>
                            <tr>
                              <th>Frais</th>
                              <th>Concerné par</th>
                              <th>Montant prévu</th>
                              <th>Déjà payé</th>
                              <th>Exonéré</th>
                              <th>Reste dû</th>
                              <th>Situation</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($feeStatuses as $d): ?>
                              <?php
                                $statusClass = match ($d['statut_paiement'] ?? '') {
                                    'Payé' => 'text-bg-success',
                                    'Exonéré' => 'text-bg-info',
                                    'Partiellement payé' => 'text-bg-warning',
                                    default => 'text-bg-danger',
                                };
                              ?>
                              <tr>
                                <td><?= htmlspecialchars($d['type_frais'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['scope_label'] ?? 'N/A') ?></td>
                                <td class="text-nowrap"><?= number_format((float) ($d['montant_initial'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($d['devise'] ?? 'USD') ?></td>
                                <td class="text-nowrap"><?= number_format((float) ($d['montant_paye'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($d['devise'] ?? 'USD') ?></td>
                                <td class="text-nowrap"><?= number_format((float) ($d['montant_exonere'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($d['devise'] ?? 'USD') ?></td>
                                <td class="text-nowrap fw-semibold"><?= number_format((float) ($d['montant_restant'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($d['devise'] ?? 'USD') ?></td>
                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($d['statut_paiement'] ?? 'En dette') ?></span></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php else: ?>
                      <div class="alert alert-info">Aucun frais applicable n’a été enregistré pour cette année scolaire.</div>
                    <?php endif; ?>
                    <p>
                      <a href="#ecritures" class="btn btn-sm btn-outline-primary">Voir écritures</a>
                      <?php if (!empty($selectedEnrollment['est_active']) && in_array($role, ['super_admin','comptable_école'], true)): ?>
                        <a href="<?= BASE_URL ?>/paiements/create?eleve_id=<?= (int) ($eleve['id'] ?? 0) ?>" class="btn btn-sm btn-success">Enregistrer paiement</a>
                      <?php endif; ?>
                    </p>
                  <?php else: ?>
                    <div class="alert alert-info">Aucune inscription de cet élève n’est disponible pour une année scolaire de cette école.</div>
                  <?php endif; ?>
                </div>
              </div>

            </div>
            <div class="col-md-8">
              <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Notes — <?= htmlspecialchars($selectedYearLabel ?? 'année non sélectionnée') ?></h3></div>
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

              <div class="card card-outline card-warning" id="discipline">
                <div class="card-header"><h3 class="card-title">Dossier disciplinaire</h3></div>
                <div class="card-body">
                  <?php if (!empty($disciplineSuccess)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($disciplineSuccess, ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                  <?php if (!empty($disciplineError)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($disciplineError, ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>

                  <?php if (!empty($canManageDiscipline)): ?>
                    <div class="border rounded p-3 mb-3">
                      <h4 class="h6 mb-3">Consigner un incident</h4>
                      <?php if (empty($disciplinePeriods)): ?>
                        <div class="alert alert-warning mb-0">Aucune période scolaire n’est disponible pour cette école.</div>
                      <?php else: ?>
                        <form method="post" action="<?= BASE_URL ?>/eleves/saveDiscipline" class="row g-2">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($disciplineCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="eleve_id" value="<?= (int) ($eleve['id'] ?? 0) ?>">
                          <div class="col-12 col-md-4">
                            <label class="form-label" for="disciplineDate">Date de l’incident</label>
                            <input class="form-control" id="disciplineDate" type="date" name="date_evenement" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required>
                          </div>
                          <div class="col-12 col-md-4">
                            <label class="form-label" for="disciplinePeriod">Période scolaire</label>
                            <select class="form-select" id="disciplinePeriod" name="periode_id" required>
                              <option value="">Sélectionner une période</option>
                              <?php foreach ($disciplinePeriods as $period): ?>
                                <option value="<?= (int) $period['id'] ?>"><?= htmlspecialchars(($period['annee'] ?? '') . ' — ' . ($period['nom_periode'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-12 col-md-4">
                            <label class="form-label" for="disciplinePoints">Points retirés</label>
                            <input class="form-control" id="disciplinePoints" type="number" name="retrait_points" min="0" max="2147483647" step="1" value="0" required>
                          </div>
                          <div class="col-12 col-md-6">
                            <label class="form-label" for="disciplineFault">Fait consigné</label>
                            <input class="form-control" id="disciplineFault" type="text" name="faute" maxlength="255" required>
                          </div>
                          <div class="col-12 col-md-6">
                            <label class="form-label" for="disciplineSanction">Sanction / mesure prise</label>
                            <input class="form-control" id="disciplineSanction" type="text" name="sanction" maxlength="255">
                          </div>
                          <div class="col-12">
                            <button class="btn btn-warning" type="submit"><i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Ajouter au dossier</button>
                          </div>
                        </form>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <?php if (empty($discipline)): ?>
                    <div class="alert alert-info">Aucun incident enregistré.</div>
                  <?php else: ?>
                    <div class="list-group">
                      <?php foreach ($discipline as $d): ?>
                        <div class="list-group-item">
                          <div class="d-flex flex-wrap justify-content-between gap-2">
                            <div>
                              <strong><?= htmlspecialchars(formatDate($d['date_evenement'] ?? null)) ?></strong>
                              <span class="ms-1"><?= htmlspecialchars($d['faute'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                              <div class="small text-muted">
                                <?= htmlspecialchars(($d['annee'] ?? '') . ' — ' . ($d['nom_periode'] ?? 'Période non précisée'), ENT_QUOTES, 'UTF-8') ?>
                                · Points retirés : <?= (int) ($d['retrait_points'] ?? 0) ?>
                              </div>
                              <?php if (!empty($d['sanction'])): ?>
                                <div class="text-muted">Sanction : <?= htmlspecialchars($d['sanction'], ENT_QUOTES, 'UTF-8') ?></div>
                              <?php endif; ?>
                            </div>
                            <?php if (!empty($canManageDiscipline)): ?>
                              <form method="post" action="<?= BASE_URL ?>/eleves/deleteDiscipline" onsubmit="return confirm('Supprimer cet incident du dossier disciplinaire ?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($disciplineCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="eleve_id" value="<?= (int) ($eleve['id'] ?? 0) ?>">
                                <input type="hidden" name="discipline_id" value="<?= (int) ($d['id'] ?? 0) ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Supprimer l’incident"><i class="bi bi-trash" aria-hidden="true"></i></button>
                              </form>
                            <?php endif; ?>
                          </div>
                          <?php if (!empty($canManageDiscipline) && !empty($disciplinePeriods)): ?>
                            <details class="mt-2">
                              <summary class="small">Modifier cet incident</summary>
                              <form method="post" action="<?= BASE_URL ?>/eleves/saveDiscipline" class="row g-2 mt-1">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($disciplineCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="eleve_id" value="<?= (int) ($eleve['id'] ?? 0) ?>">
                                <input type="hidden" name="discipline_id" value="<?= (int) ($d['id'] ?? 0) ?>">
                                <div class="col-12 col-md-4">
                                  <label class="form-label small">Date</label>
                                  <input class="form-control form-control-sm" type="date" name="date_evenement" value="<?= htmlspecialchars($d['date_evenement'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="col-12 col-md-4">
                                  <label class="form-label small">Période scolaire</label>
                                  <select class="form-select form-select-sm" name="periode_id" required>
                                    <?php foreach ($disciplinePeriods as $period): ?>
                                      <option value="<?= (int) $period['id'] ?>" <?= (int) ($d['periode_id'] ?? 0) === (int) $period['id'] ? 'selected' : '' ?>><?= htmlspecialchars(($period['annee'] ?? '') . ' — ' . ($period['nom_periode'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>
                                <div class="col-12 col-md-4">
                                  <label class="form-label small">Points retirés</label>
                                  <input class="form-control form-control-sm" type="number" name="retrait_points" min="0" max="2147483647" step="1" value="<?= (int) ($d['retrait_points'] ?? 0) ?>" required>
                                </div>
                                <div class="col-12 col-md-6">
                                  <label class="form-label small">Fait consigné</label>
                                  <input class="form-control form-control-sm" type="text" name="faute" maxlength="255" value="<?= htmlspecialchars($d['faute'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="col-12 col-md-6">
                                  <label class="form-label small">Sanction / mesure prise</label>
                                  <input class="form-control form-control-sm" type="text" name="sanction" maxlength="255" value="<?= htmlspecialchars($d['sanction'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="col-12">
                                  <button class="btn btn-sm btn-primary" type="submit">Enregistrer les modifications</button>
                                </div>
                              </form>
                            </details>
                          <?php endif; ?>
                          <?php if (!empty($canManageAuditions)): ?>
                            <details class="mt-2">
                              <summary class="small fw-semibold">Dresser un procès-verbal d’audition et convoquer le parent</summary>
                              <form method="post" action="<?= BASE_URL ?>/eleves/recordAudition" class="row g-2 mt-1" onsubmit="return confirm('Enregistrer le PV, tenter la convocation du parent par email et SMS, puis transmettre le rapport au directeur des études et au préfet des études ?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($disciplineCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="eleve_id" value="<?= (int) ($eleve['id'] ?? 0) ?>">
                                <input type="hidden" name="discipline_id" value="<?= (int) ($d['id'] ?? 0) ?>">
                                <div class="col-12 col-md-6">
                                  <label class="form-label small">Date et heure de l’audition</label>
                                  <input class="form-control form-control-sm" type="datetime-local" name="hearing_at" value="<?= htmlspecialchars(date('Y-m-d\TH:i'), ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="col-12 col-md-6">
                                  <label class="form-label small">Lieu de l’audition</label>
                                  <input class="form-control form-control-sm" type="text" name="location" maxlength="255" placeholder="Bureau du directeur de discipline">
                                </div>
                                <div class="col-12">
                                  <label class="form-label small">Faits examinés</label>
                                  <textarea class="form-control form-control-sm" name="alleged_facts" rows="3" maxlength="20000" required><?= htmlspecialchars((string) ($d['faute'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <div class="col-12">
                                  <label class="form-label small">Déclaration de l’élève</label>
                                  <textarea class="form-control form-control-sm" name="student_statement" rows="3" maxlength="20000" required></textarea>
                                </div>
                                <div class="col-12">
                                  <label class="form-label small">Observations complémentaires</label>
                                  <textarea class="form-control form-control-sm" name="observations" rows="2" maxlength="20000"></textarea>
                                </div>
                                <div class="col-12">
                                  <label class="form-label small">Conclusion ou mesure proposée</label>
                                  <textarea class="form-control form-control-sm" name="decision" rows="2" maxlength="20000"></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                  <label class="form-label small">Date et heure de convocation du parent</label>
                                  <input class="form-control form-control-sm" type="datetime-local" name="parent_meeting_at" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime('+1 day')), ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="col-12 col-md-6">
                                  <label class="form-label small">Lieu de convocation</label>
                                  <input class="form-control form-control-sm" type="text" name="parent_meeting_location" maxlength="255" placeholder="Établissement scolaire">
                                </div>
                                <div class="col-12">
                                  <label class="form-label small">Message complémentaire pour le parent (email)</label>
                                  <textarea class="form-control form-control-sm" name="parent_notice" rows="2" maxlength="20000"></textarea>
                                  <div class="form-text">Le SMS reste discret et ne mentionne pas les faits disciplinaires.</div>
                                </div>
                                <div class="col-12">
                                  <button class="btn btn-sm btn-warning" type="submit"><i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Enregistrer le PV, convoquer et transmettre</button>
                                </div>
                              </form>
                            </details>
                          <?php endif; ?>
                          <?php foreach (($auditionsByIncident[(int) ($d['id'] ?? 0)] ?? []) as $audition): ?>
                            <?php
                              $statusLabel = static fn (string $status): string => match ($status) {
                                  'sent' => 'Envoyé',
                                  'partial' => 'Partiellement envoyé',
                                  'failed' => 'Échec',
                                  'skipped' => 'Non envoyé',
                                  'no_recipients' => 'Aucun destinataire',
                                  default => 'En attente',
                              };
                              $statusClass = static fn (string $status): string => match ($status) {
                                  'sent' => 'success',
                                  'partial' => 'warning',
                                  'failed', 'no_recipients' => 'danger',
                                  'skipped' => 'secondary',
                                  default => 'info',
                              };
                            ?>
                            <div class="border rounded p-3 mt-3">
                              <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div>
                                  <strong>PV d’audition du <?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string) $audition['hearing_at'])), ENT_QUOTES, 'UTF-8') ?></strong>
                                  <div class="small text-muted">Rédigé par <?= htmlspecialchars((string) ($audition['author_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/eleves/disciplineReport?id=<?= (int) $audition['id'] ?>" target="_blank" rel="noopener">
                                  <i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimer / enregistrer en PDF
                                </a>
                              </div>
                              <div class="small mt-2">
                                Convocation parent : <?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string) $audition['parent_meeting_at'])), ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($audition['parent_meeting_location'])): ?> · <?= htmlspecialchars((string) $audition['parent_meeting_location'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                              </div>
                              <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php foreach (['email' => 'Email parent', 'sms' => 'SMS parent', 'report' => 'Rapport aux autorités'] as $channel => $label): ?>
                                  <?php $deliveryStatus = (string) ($audition[$channel . '_status'] ?? 'pending'); ?>
                                  <span class="badge text-bg-<?= $statusClass($deliveryStatus) ?>">
                                    <?= htmlspecialchars($label . ' : ' . $statusLabel($deliveryStatus), ENT_QUOTES, 'UTF-8') ?>
                                  </span>
                                <?php endforeach; ?>
                              </div>
                              <?php foreach (['email' => 'Email', 'sms' => 'SMS', 'report' => 'Rapport'] as $channel => $label): ?>
                                <?php if (!empty($audition[$channel . '_details'])): ?>
                                  <div class="small text-muted mt-1"><?= htmlspecialchars($label . ' — ' . $audition[$channel . '_details'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                              <?php endforeach; ?>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card card-outline card-light">
                <div class="card-header"><h3 class="card-title">Écritures comptables — <?= htmlspecialchars($selectedYearLabel ?? 'année non sélectionnée') ?></h3></div>
                <div class="card-body" id="ecritures">
                  <?php if (!empty($entryTotalsByCurrency)): ?>
                    <div class="table-responsive mb-3">
                      <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>Devise</th><th>Total des débits</th><th>Total des crédits</th></tr></thead>
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
                        <thead><tr><th>#</th><th>Date</th><th>Situation</th><th>Montant</th><th>Devise</th><th>Référence</th><th>Libellé</th></tr></thead>
                        <tbody>
                          <?php foreach ($ecritures as $i => $ec): ?>
                            <tr>
                              <td><?= $i+1 ?></td>
                              <td><?= htmlspecialchars(formatDate($ec['date_operation'] ?? null)) ?></td>
                              <?php $movementType = strtoupper((string) ($ec['type_mouvement'] ?? '')); ?>
                              <?php $isCancelledPayment = !empty($ec['annulee_at']); ?>
                              <?php $movementLabel = $isCancelledPayment ? 'Paiement annulé' : ($movementType === 'CREDIT' ? 'Paiement enregistré' : 'Frais facturé'); ?>
                              <td><span class="badge <?= $isCancelledPayment ? 'bg-secondary' : ($movementType === 'CREDIT' ? 'bg-success' : 'bg-danger') ?>"><?= htmlspecialchars($movementLabel) ?></span></td>
                              <td><?= htmlspecialchars(number_format((float) ($ec['montant'] ?? 0), 2, ',', ' ')) ?></td>
                              <td><?= htmlspecialchars($ec['frais_devise'] ?? 'USD') ?></td>
                              <td><?= htmlspecialchars($ec['reference_recu'] ?? '-') ?></td>
                              <td><?= htmlspecialchars($ec['frais_type_frais'] ?? $ec['libelle'] ?? '') ?></td>
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