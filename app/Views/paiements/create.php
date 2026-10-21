<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$oldInput = $_SESSION['paiements_old'] ?? [];
$errors = $_SESSION['paiements_errors'] ?? [];
$oldStudentId = (int) ($oldInput['eleve_id'] ?? 0);
$oldStudent = null;
if ($oldStudentId > 0) {
    foreach (($students ?? []) as $student) {
        if ((int) ($student['id'] ?? 0) === $oldStudentId) {
            $oldStudent = $student;
            break;
        }
    }
}
unset($_SESSION['paiements_old'], $_SESSION['paiements_errors']);
?>
<section class="content-header">
  <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h1 class="mb-1">Enregistrer un paiement</h1>
      <?php if (!empty($eleve)): ?>
        <p class="text-muted mb-0"><?= htmlspecialchars(\App\Core\PersonName::format($eleve), ENT_QUOTES, 'UTF-8') ?></p>
      <?php else: ?>
        <p class="text-muted mb-0">Sélectionnez un élève, vérifiez son solde, puis enregistrez le paiement.</p>
      <?php endif; ?>
    </div>
    <a href="<?= BASE_URL ?><?= !empty($eleve) ? '/eleves/show?id=' . (int) $eleve['id'] : '/paiements' ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Retour</a>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
        <?php if (!empty($paymentSuccess)): ?>
          <div class="alert alert-success" role="status">
            <?= htmlspecialchars($paymentSuccess, ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($paymentReceiptId)): ?>
              <div class="mt-2"><a href="<?= BASE_URL ?>/paiements/receipt?id=<?= (int) $paymentReceiptId ?>" class="alert-link">Consulter ou imprimer le reçu</a></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($notificationStatus)): ?>
          <?php
            $notificationClass = match ($notificationStatus['status'] ?? '') {
              'success' => 'alert-success',
              'partial' => 'alert-warning',
              default => 'alert-danger',
            };
            $notificationTitle = match ($notificationStatus['status'] ?? '') {
              'success' => 'Message interne créé',
              'partial' => 'Message interne créé partiellement',
              default => 'Message interne au parent non créé',
            };
          ?>
          <div class="alert <?= $notificationClass ?> alert-dismissible fade show" role="status">
            <strong><?= htmlspecialchars($notificationTitle, ENT_QUOTES, 'UTF-8') ?></strong><br>
            <?= htmlspecialchars($notificationStatus['message'] ?? 'Statut de notification indisponible.', ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($notificationStatus['attempts']) && is_array($notificationStatus['attempts'])): ?>
              <ul class="mt-1 mb-0 small">
                <?php foreach ($notificationStatus['attempts'] as $attempt): ?>
                  <?php $channelLabel = ($attempt['channel'] ?? '') === 'sms' ? 'SMS' : 'WhatsApp'; ?>
                  <li>
                    <strong><?= htmlspecialchars($channelLabel, ENT_QUOTES, 'UTF-8') ?> :</strong>
                    <?= !empty($attempt['sent']) ? 'accepté par le fournisseur — livraison SIM non confirmée' : 'échec' ?>
                    <?php if (!empty($attempt['details'])): ?>
                      — <?= htmlspecialchars($attempt['details'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php elseif (!empty($notificationStatus['details'])): ?>
              <div class="mt-1 small"><?= htmlspecialchars($notificationStatus['details'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger alert-dismissible">
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>
        <?php endif; ?>
        <div class="row g-3 align-items-start">
            <div class="col-12 col-xl-7">
            <div class="card card-outline card-primary">
          <div class="card-header"><h3 class="card-title">Détails paiement</h3></div>
          <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/paiements/store">
              <input type="hidden" name="payment_form_token" value="<?= htmlspecialchars($paymentFormToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!empty($eleve)): ?>
                <input type="hidden" name="eleve_id" value="<?= (int) $eleve['id'] ?>">
              <?php else: ?>
                <div class="mb-3">
                  <label class="form-label">Élève</label>
                  <input type="hidden" name="eleve_id" id="studentId" value="<?= $oldStudent ? (int) $oldStudent['id'] : '' ?>">
                  <input type="search" id="studentSearch" class="form-control" autocomplete="off" placeholder="Tapez le nom ou le postnom de l’élève" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="studentSearchResults" value="<?= htmlspecialchars(\App\Core\PersonName::format($oldStudent), ENT_QUOTES, 'UTF-8') ?>">
                  <div id="studentSearchResults" class="list-group position-absolute shadow w-100" role="listbox" hidden style="z-index:1080; max-height:16rem; overflow-y:auto"></div>
                  <div id="studentDebtStatus" class="form-text" aria-live="polite"></div>
                  <script type="application/json" id="studentData"><?= json_encode(array_map(static function (array $student): array {
                      return [
                          'id' => (int) ($student['id'] ?? 0),
                          'name' => \App\Core\PersonName::format($student),
                          'settled' => !empty($student['is_settled']),
                          'debtByCurrency' => $student['remaining_debt_by_currency'] ?? [],
                          'feeDebts' => $student['outstanding_fee_details'] ?? [],
                      ];
                  }, $students ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
                </div>
              <?php endif; ?>

              <div class="mb-3" id="studentDebtSummary" aria-live="polite" <?= empty($eleve) ? 'hidden' : '' ?>>
                <div class="alert alert-light border py-2 mb-0">
                  <strong>Dette restante par devise</strong>
                  <ul id="studentDebtByCurrency" class="list-unstyled mb-0 mt-1">
                    <?php if (!empty($eleve)): ?>
                      <?php if (!empty($totalDebtByCurrency)): ?>
                        <?php foreach ($totalDebtByCurrency as $currency => $amount): ?>
                          <li><?= htmlspecialchars((string) $currency, ENT_QUOTES, 'UTF-8') ?> : <?= number_format((float) $amount, 2, ',', ' ') ?></li>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <li>Aucune dette restante.</li>
                      <?php endif; ?>
                    <?php else: ?>
                      <li>Sélectionnez un élève pour afficher sa dette.</li>
                    <?php endif; ?>
                  </ul>
                  <div id="studentDebtFeeHeading" class="fw-semibold mt-2">Frais restant à payer</div>
                  <ul id="studentDebtFees" class="list-unstyled mb-0 mt-1">
                    <?php if (!empty($eleve)): ?>
                      <?php if (!empty($feeDebtDetails)): ?>
                        <?php foreach ($feeDebtDetails as $feeDebt): ?>
                          <li><?= htmlspecialchars((string) $feeDebt['type_frais'], ENT_QUOTES, 'UTF-8') ?> : <?= number_format((float) $feeDebt['montant_restant'], 2, ',', ' ') ?> <?= htmlspecialchars((string) $feeDebt['devise'], ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <li>Aucun frais impayé.</li>
                      <?php endif; ?>
                    <?php else: ?>
                      <li>Sélectionnez un élève pour afficher ses frais impayés.</li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="fraisSelect">Frais à régler</label>
                <select name="frais_id" id="fraisSelect" class="form-select" required <?= empty($eleve) ? 'disabled' : '' ?> data-initial-fee-id="<?= (int) ($oldInput['frais_id'] ?? 0) ?>" data-initial-student-id="<?= $oldStudent ? (int) $oldStudent['id'] : 0 ?>" data-preserve-amount="<?= !empty($oldInput['montant']) ? '1' : '0' ?>">
                  <option value="">-- Sélectionner le motif --</option>
                  <?php if (!empty($eleve)): ?>
                    <?php foreach (($fees ?? []) as $f): ?>
                      <option value="<?= (int) $f['id'] ?>" data-amount="<?= htmlspecialchars($f['montant_total'] ?? '') ?>" data-remaining="<?= htmlspecialchars($f['remaining'] ?? '') ?>" data-devise="<?= htmlspecialchars($f['devise'] ?? 'USD') ?>" <?= ((int) ($oldInput['frais_id'] ?? 0) === (int) $f['id']) ? 'selected' : '' ?>><?= htmlspecialchars($f['type_frais'] . ' - ' . ($f['nom_classe'] ?? '') ) ?> (<?= htmlspecialchars($f['devise'] ?? '') ?>)</option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>

              <div id="feeSummary" class="alert alert-info py-2 mb-3 d-none"></div>

              <div class="mb-3">
                <label class="form-label" for="paymentAmount">Montant du paiement</label>
                <input id="paymentAmount" type="number" step="0.01" min="0" name="montant" class="form-control" required value="<?= htmlspecialchars($oldInput['montant'] ?? '') ?>">
                <div class="form-text" id="montantHint">Sélectionnez un frais pour connaître le montant restant et le maximum autorisé.</div>
              </div>

              <div class="mb-3">
                <label class="form-label">Libellé (optionnel)</label>
                <input type="text" name="libelle" id="libelleInput" class="form-control" value="<?= htmlspecialchars($oldInput['libelle'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Paiement enregistré par</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($paymentRecorderName ?? 'Utilisateur connecté', ENT_QUOTES, 'UTF-8') ?>" readonly>
              </div>

                <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Enregistrer et imprimer reçu</button>
                <?php if (!empty($eleve)): ?>
                  <a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) $eleve['id'] ?>" class="btn btn-secondary">Retour</a>
                <?php else: ?>
                  <a href="<?= BASE_URL ?>/paiements" class="btn btn-secondary">Retour</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

          </div>
        <?php if (!empty($compte)): ?>
          <div class="col-12 col-xl-5">
        <div class="card card-outline card-secondary">
          <div class="card-header"><h3 class="card-title">Compte élève</h3></div>
          <div class="card-body">
            <p><strong>Solde dû (compte):</strong> <?= number_format((float) ($compte['solde_debiteur'] ?? 0), 2) ?></p>
            <?php if (!empty($totalDebtByCurrency) && is_array($totalDebtByCurrency)): ?>
              <div class="mb-2"><strong>Solde dû par devise :</strong></div>
              <ul class="list-unstyled mb-0">
                <?php foreach ($totalDebtByCurrency as $dev => $amt): ?>
                  <?php
                    $usdEq = null;
                    if (strtoupper($dev) !== 'USD') {
                        try {
                            $usdEq = \App\Models\Devise::convertToUsd((float) $amt, strtoupper($dev));
                        } catch (\Throwable $e) {
                            $usdEq = null;
                        }
                    }
                  ?>
                  <li><?= htmlspecialchars($dev) ?>: <?= \App\Models\Devise::formatAmountWithCurrency((float) $amt, $dev, $usdEq) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php elseif (isset($totalDebt)): ?>
              <p><strong>Solde dû (total exact):</strong> <?= number_format((float) ($totalDebt ?? 0), 2) ?></p>
            <?php endif; ?>
          </div>
        </div>
            </div>
        <?php endif; ?>

        </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const studentSearch = document.getElementById('studentSearch');
    const studentId = document.getElementById('studentId');
    const studentSearchResults = document.getElementById('studentSearchResults');
    const students = JSON.parse(document.getElementById('studentData')?.textContent || '[]');
    const studentDebtStatus = document.getElementById('studentDebtStatus');
    const studentDebtSummary = document.getElementById('studentDebtSummary');
    const studentDebtByCurrency = document.getElementById('studentDebtByCurrency');
    const studentDebtFees = document.getElementById('studentDebtFees');
    const fraisSelect = document.getElementById('fraisSelect');
    let lastSelectedFeeId = fraisSelect?.value || '';
    let lastGeneratedFeeLabel = '';
    let restoreInitialFee = true;
    function updateStudentFees(student) {
      if (!studentSearch || !fraisSelect) return;
      fraisSelect.replaceChildren(new Option('-- Sélectionner le motif --', ''));
      let restoredFee = false;
      (student?.feeDebts || []).forEach((fee) => {
        const remaining = Number(fee.montant_restant || 0);
        if (remaining <= 0) return;
        const currency = fee.devise || 'USD';
        const option = new Option(
          `${fee.type_frais} (${currency}) — Reste: ${new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          }).format(remaining)}`,
          String(fee.frais_id)
        );
        option.dataset.label = fee.type_frais;
        option.dataset.amount = String(fee.montant_total || remaining);
        option.dataset.remaining = String(remaining);
        option.dataset.devise = currency;
        fraisSelect.appendChild(option);
        if (
          restoreInitialFee
          && Number(student.id) === Number(fraisSelect.dataset.initialStudentId)
          && Number(fee.frais_id) === Number(fraisSelect.dataset.initialFeeId)
        ) {
          option.selected = true;
          restoredFee = true;
        }
      });
      restoreInitialFee = false;
      fraisSelect.dataset.initialFeeId = '0';
      fraisSelect.disabled = fraisSelect.options.length <= 1;
      if (restoredFee) {
        lastSelectedFeeId = fraisSelect.value;
      }
      fraisSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function updateStudentDebtSummary(student) {
      if (studentDebtStatus) {
        studentDebtStatus.textContent = student?.settled ? 'Aucune dette restante pour cet élève.' : '';
      }
      if (studentDebtSummary && studentDebtByCurrency && studentDebtFees) {
        if (!student) {
          studentDebtSummary.hidden = true;
          return;
        }
        studentDebtSummary.hidden = false;
        const balances = student.debtByCurrency || {};
        studentDebtByCurrency.replaceChildren();
        studentDebtFees.replaceChildren();
        const currencies = Object.entries(balances).filter(([, amount]) => Number(amount) > 0);
        if (currencies.length === 0) {
          const item = document.createElement('li');
          item.textContent = 'Aucune dette restante.';
          studentDebtByCurrency.appendChild(item);
        } else {
          currencies.forEach(([currency, amount]) => {
            const item = document.createElement('li');
            const formattedAmount = new Intl.NumberFormat('fr-FR', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            }).format(Number(amount));
            item.textContent = `${currency} : ${formattedAmount}`;
            studentDebtByCurrency.appendChild(item);
          });
        }
        const feeDebts = (student.feeDebts || []).filter((fee) => Number(fee.montant_restant) > 0);
        if (feeDebts.length === 0) {
          const item = document.createElement('li');
          item.textContent = 'Aucun frais impayé.';
          studentDebtFees.appendChild(item);
        } else {
          feeDebts.forEach((fee) => {
            const item = document.createElement('li');
            const formattedAmount = new Intl.NumberFormat('fr-FR', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            }).format(Number(fee.montant_restant));
            item.textContent = `${fee.type_frais} : ${formattedAmount} ${fee.devise}`;
            studentDebtFees.appendChild(item);
          });
        }
      }
      updateStudentFees(student);
    }

    if (studentSearch && studentId && studentSearchResults) {
      let visibleStudents = [];
      let activeResultIndex = -1;
      const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase();

      function hideStudentResults() {
        studentSearchResults.hidden = true;
        studentSearch.setAttribute('aria-expanded', 'false');
        studentSearch.removeAttribute('aria-activedescendant');
        activeResultIndex = -1;
      }

      function selectStudent(student) {
        studentId.value = String(student.id);
        studentSearch.value = student.name;
        studentSearch.classList.toggle('text-muted', student.settled);
        updateStudentDebtSummary(student);
        hideStudentResults();
      }

      function renderStudentResults() {
        const query = normalize(studentSearch.value.trim());
        studentId.value = '';
        studentSearch.classList.remove('text-muted');
        updateStudentDebtSummary(null);
        studentSearchResults.replaceChildren();
        visibleStudents = query
          ? students.filter((student) => normalize(student.name).includes(query)).slice(0, 20)
          : [];
        activeResultIndex = -1;

        if (!query) {
          hideStudentResults();
          return;
        }
        if (visibleStudents.length === 0) {
          const empty = document.createElement('div');
          empty.className = 'list-group-item text-muted';
          empty.textContent = 'Aucun élève correspondant.';
          studentSearchResults.appendChild(empty);
        } else {
          visibleStudents.forEach((student, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.id = `student-search-result-${index}`;
            button.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', 'false');
            button.textContent = student.name;
            if (student.settled) {
              button.classList.add('text-muted');
              const settled = document.createElement('span');
              settled.className = 'badge text-bg-secondary ms-2';
              settled.textContent = 'Soldé';
              button.appendChild(settled);
            } else {
              const balances = Object.entries(student.debtByCurrency || {})
                .filter(([, amount]) => Number(amount) > 0)
                .map(([currency, amount]) => `${new Intl.NumberFormat('fr-FR', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).format(Number(amount))} ${currency}`);
              if (balances.length) {
                const remaining = document.createElement('span');
                remaining.className = 'small text-muted ms-2';
                remaining.textContent = balances.join(' / ');
                button.appendChild(remaining);
              }
            }
            button.addEventListener('click', () => selectStudent(student));
            studentSearchResults.appendChild(button);
          });
        }

        studentSearchResults.hidden = false;
        studentSearch.setAttribute('aria-expanded', 'true');
      }

      studentSearch.addEventListener('input', renderStudentResults);
      studentSearch.addEventListener('keydown', (event) => {
        if (studentSearchResults.hidden) {
          if (event.key === 'ArrowDown' && studentSearch.value.trim()) {
            renderStudentResults();
            event.preventDefault();
          }
          return;
        }
        if (event.key === 'ArrowDown' && visibleStudents.length) {
          event.preventDefault();
          activeResultIndex = (activeResultIndex + 1) % visibleStudents.length;
        } else if (event.key === 'ArrowUp' && visibleStudents.length) {
          event.preventDefault();
          activeResultIndex = (activeResultIndex - 1 + visibleStudents.length) % visibleStudents.length;
        } else if (event.key === 'Enter' && activeResultIndex >= 0) {
          event.preventDefault();
          selectStudent(visibleStudents[activeResultIndex]);
          return;
        } else if (event.key === 'Escape') {
          hideStudentResults();
          return;
        } else {
          return;
        }
        Array.from(studentSearchResults.querySelectorAll('[role="option"]')).forEach((option, index) => {
          const active = index === activeResultIndex;
          option.classList.toggle('active', active);
          option.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        studentSearch.setAttribute('aria-activedescendant', `student-search-result-${activeResultIndex}`);
      });
      document.addEventListener('click', (event) => {
        if (!studentSearch.contains(event.target) && !studentSearchResults.contains(event.target)) {
          hideStudentResults();
        }
      });
      studentSearch.addEventListener('focus', () => {
        if (studentSearch.value.trim() && !studentId.value) {
          renderStudentResults();
        }
      });

      const initialStudent = students.find((student) => String(student.id) === studentId.value);
      if (initialStudent) {
        selectStudent(initialStudent);
      }
    }

    const libelleInput = document.getElementById('libelleInput');
    const montantInput = document.querySelector('input[name="montant"]');
    const montantHint = document.getElementById('montantHint');
    const feeSummary = document.getElementById('feeSummary');
    const paiementForm = document.querySelector('form[action="<?= BASE_URL ?>/paiements/store"]');
    if (!fraisSelect || !montantInput || !montantHint || !paiementForm) return;

    function updateMontantHint() {
      const opt = fraisSelect.selectedOptions[0];
      if (!opt || !opt.value) {
        montantHint.textContent = 'Veuillez saisir le montant du paiement. Si un motif est sélectionné, le montant doit respecter les limites du frais.';
        montantHint.classList.add('text-muted');
        montantHint.classList.remove('text-danger');
        montantInput.min = 0;
        montantInput.max = '';
        if (feeSummary) {
          feeSummary.classList.add('d-none');
          feeSummary.textContent = '';
        }
        return;
      }

      const feeAmount = parseFloat(opt.dataset.amount || '0');
      const feeRemaining = parseFloat(opt.dataset.remaining || '0');
      const feeDevise = opt.dataset.devise || 'USD';
      if (feeSummary) {
        feeSummary.classList.remove('d-none');
        feeSummary.textContent = feeRemaining > 0
          ? `Frais sélectionné : reste à payer ${feeRemaining.toFixed(2)} ${feeDevise} sur ${feeAmount.toFixed(2)} ${feeDevise}.`
          : `Frais sélectionné : montant total ${feeAmount.toFixed(2)} ${feeDevise}.`;
      }

      if (feeAmount > 0) {
        if (feeRemaining > 0) {
          montantHint.textContent = `Montant autorisé : jusqu'à ${feeRemaining.toFixed(2)} ${feeDevise} (vous pouvez payer partiellement).`;
          montantInput.min = 0;
          montantInput.max = feeRemaining;
        } else {
          montantHint.textContent = `Montant autorisé : jusqu'à ${feeAmount.toFixed(2)} ${feeDevise}.`;
          montantInput.min = 0;
          montantInput.max = feeAmount;
        }
        montantHint.classList.add('text-muted');
        montantHint.classList.remove('text-danger');
      } else {
        montantHint.textContent = 'Veuillez saisir le montant du paiement. Si un motif est sélectionné, le montant doit respecter les limites du frais.';
        montantHint.classList.add('text-muted');
        montantHint.classList.remove('text-danger');
        montantInput.min = 0;
        montantInput.max = '';
      }
    }

    function selectDefaultFee() {
      const options = Array.from(fraisSelect.options).slice(1);
      const preferred = options.find((option) => parseFloat(option.dataset.remaining || '0') > 0) || options[0];
      if (!preferred) return;
      if (!fraisSelect.value) {
        fraisSelect.value = preferred.value;
      }
      const event = new Event('change', { bubbles: true });
      fraisSelect.dispatchEvent(event);
    }

    fraisSelect.addEventListener('change', function () {
      const opt = fraisSelect.selectedOptions[0];
      if (!opt) return;
      if (!opt.value) {
        if (lastSelectedFeeId && montantInput) {
          montantInput.value = '';
        }
        if (libelleInput && libelleInput.value === lastGeneratedFeeLabel) {
          libelleInput.value = '';
        }
        lastSelectedFeeId = '';
        lastGeneratedFeeLabel = '';
        updateMontantHint();
        return;
      }
      const amount = parseFloat(opt.dataset.amount || '0');
      const remaining = parseFloat(opt.dataset.remaining || '0');
      const feeChanged = opt.value !== lastSelectedFeeId;
      if (feeChanged && montantInput && fraisSelect.dataset.preserveAmount !== '1') {
        montantInput.value = remaining > 0 ? remaining.toFixed(2) : amount.toFixed(2);
      }
      if (libelleInput && (libelleInput.value.trim() === '' || libelleInput.value === lastGeneratedFeeLabel)) {
        libelleInput.value = opt.dataset.label || opt.textContent.trim();
        lastGeneratedFeeLabel = libelleInput.value;
      }
      lastSelectedFeeId = opt.value;
      fraisSelect.dataset.preserveAmount = '0';
      updateMontantHint();
    });

    paiementForm.addEventListener('submit', function (event) {
      if (studentId && !studentId.value) {
        event.preventDefault();
        if (studentSearch) {
          studentSearch.setCustomValidity('Sélectionnez un élève dans les résultats de recherche.');
          studentSearch.reportValidity();
          studentSearch.addEventListener('input', () => studentSearch.setCustomValidity(''), { once: true });
          studentSearch.focus();
        }
        return;
      }
      const opt = fraisSelect.selectedOptions[0];
      if (!opt) return;
      const feeAmount = parseFloat(opt.dataset.amount || '0');
      const feeRemaining = parseFloat(opt.dataset.remaining || '0');
      const amountValue = parseFloat(montantInput.value || '0');
      const devise = opt.dataset.devise || 'USD';

      if (feeAmount > 0) {
        if (amountValue > feeRemaining) {
          event.preventDefault();
          montantHint.textContent = `Le montant ne peut pas dépasser le reste à payer (${feeRemaining.toFixed(2)} ${devise}) pour ce frais.`;
          montantHint.classList.add('text-danger');
          montantHint.classList.remove('text-muted');
          montantInput.focus();
          return;
        }
        if (amountValue > feeAmount) {
          event.preventDefault();
          montantHint.textContent = `Le montant ne peut pas dépasser ${feeAmount.toFixed(2)} ${devise} pour ce frais.`;
          montantHint.classList.add('text-danger');
          montantHint.classList.remove('text-muted');
          montantInput.focus();
        }
      }
    });

    if (!fraisSelect.value) {
      selectDefaultFee();
    } else {
      updateMontantHint();
    }
  });
</script>