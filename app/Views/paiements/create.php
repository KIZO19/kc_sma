<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$oldInput = $_SESSION['paiements_old'] ?? [];
$errors = $_SESSION['paiements_errors'] ?? [];
unset($_SESSION['paiements_old'], $_SESSION['paiements_errors']);
?>
<div class="modal-backdrop fade show"></div>
<div class="modal fade show d-block" id="paymentCreateModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="paymentCreateModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h1 class="modal-title fs-5" id="paymentCreateModalLabel">Enregistrer un paiement</h1>
          <?php if (!empty($eleve)): ?>
            <div class="text-muted small"><?= htmlspecialchars(trim(($eleve['nom'] ?? '') . ' ' . ($eleve['postnom'] ?? '') . ' ' . ($eleve['prenom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
        </div>
        <a href="<?= BASE_URL ?><?= !empty($eleve) ? '/eleves/show?id=' . (int) $eleve['id'] : '/paiements' ?>" class="btn-close" aria-label="Fermer"></a>
      </div>
      <div class="modal-body">
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
        <div class="container-fluid p-0">
          <div class="row">
            <div class="col-12">
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
                  <select name="eleve_id" id="studentSelect" class="form-select" required>
                    <option value="">-- Sélectionner un élève --</option>
                    <?php foreach (($students ?? []) as $s): ?>
                      <?php $isSettled = !empty($s['is_settled']); ?>
                      <?php $fullName = trim((string) (($s['nom'] ?? '') . ' ' . ($s['postnom'] ?? '') . ' ' . ($s['prenom'] ?? ''))); ?>
                      <?php
                        $studentDebtLabel = [];
                        foreach (($s['remaining_debt_by_currency'] ?? []) as $currency => $amount) {
                            if ((float) $amount > 0) {
                                $studentDebtLabel[] = number_format((float) $amount, 2, ',', ' ') . ' ' . $currency;
                            }
                        }
                      ?>
                      <?php $selected = ((int) ($oldInput['eleve_id'] ?? 0) === (int) $s['id']) ? 'selected' : ''; ?>
                      <option value="<?= (int) $s['id'] ?>" <?= $selected ?> data-settled="<?= $isSettled ? '1' : '0' ?>" data-debt-by-currency="<?= htmlspecialchars(json_encode($s['remaining_debt_by_currency'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>" style="<?= $isSettled ? 'color:#8a8f98;' : '' ?>">
                        <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?><?= $isSettled ? ' — Soldé' : ' — Reste: ' . htmlspecialchars(implode(' / ', $studentDebtLabel), ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div id="studentDebtStatus" class="form-text" aria-live="polite"></div>
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
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Montant</label>
                <input type="number" step="0.01" name="montant" class="form-control" required value="<?= htmlspecialchars($oldInput['montant'] ?? '') ?>">
                <div class="form-text" id="montantHint">Veuillez saisir le montant du paiement. Si un motif est sélectionné, le montant ne doit pas dépasser le montant du frais.</div>
              </div>

              <div class="mb-3">
                <label class="form-label">Motif / Frais</label>
                <select name="frais_id" id="fraisSelect" class="form-select" required>
                  <option value="">-- Sélectionner le motif --</option>
                  <?php foreach (($fees ?? []) as $f): ?>
                    <option value="<?= (int) $f['id'] ?>" data-amount="<?= htmlspecialchars($f['montant_total'] ?? '') ?>" data-remaining="<?= htmlspecialchars($f['remaining'] ?? '') ?>" data-devise="<?= htmlspecialchars($f['devise'] ?? 'USD') ?>" <?= ((int) ($oldInput['frais_id'] ?? 0) === (int) $f['id']) ? 'selected' : '' ?>><?= htmlspecialchars($f['type_frais'] . ' - ' . ($f['nom_classe'] ?? '') ) ?> (<?= htmlspecialchars($f['devise'] ?? '') ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div id="feeSummary" class="alert alert-info py-2 mb-3 d-none"></div>

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

        <?php if (!empty($compte)): ?>
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
        <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('modal-open');
    const studentSelect = document.getElementById('studentSelect');
    if (!studentSelect) {
      return;
    }

    const options = Array.from(studentSelect.options);
    if (!options.length) {
      return;
    }

    const studentDebtStatus = document.getElementById('studentDebtStatus');
    const studentDebtSummary = document.getElementById('studentDebtSummary');
    const studentDebtByCurrency = document.getElementById('studentDebtByCurrency');
    function updateStudentDebtAppearance() {
      const selectedOption = studentSelect.selectedOptions[0];
      const isSettled = selectedOption && selectedOption.dataset.settled === '1';
      studentSelect.classList.toggle('text-muted', Boolean(isSettled));
      if (studentDebtStatus) {
        studentDebtStatus.textContent = isSettled ? 'Aucune dette restante pour cet élève.' : '';
      }
      if (studentDebtSummary && studentDebtByCurrency) {
        if (!selectedOption || !selectedOption.value) {
          studentDebtSummary.hidden = true;
          return;
        }
        studentDebtSummary.hidden = false;
        const balances = JSON.parse(selectedOption.dataset.debtByCurrency || '{}');
        studentDebtByCurrency.replaceChildren();
        const currencies = Object.entries(balances).filter(([, amount]) => Number(amount) > 0);
        if (currencies.length === 0) {
          const item = document.createElement('li');
          item.textContent = 'Aucune dette restante.';
          studentDebtByCurrency.appendChild(item);
          return;
        }
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
    }
    studentSelect.addEventListener('change', updateStudentDebtAppearance);
    updateStudentDebtAppearance();

    const fraisSelect = document.getElementById('fraisSelect');
    const libelleInput = document.getElementById('libelleInput');
    const montantInput = document.querySelector('input[name="montant"]');
    const montantHint = document.getElementById('montantHint');
    const feeSummary = document.getElementById('feeSummary');
    const paiementForm = document.querySelector('form[action="<?= BASE_URL ?>/paiements/store"]');
    if (!fraisSelect || !montantInput || !montantHint || !paiementForm) return;

    function updateMontantHint() {
      const opt = fraisSelect.selectedOptions[0];
      if (!opt) {
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
      const amount = parseFloat(opt.dataset.amount || '0');
      const remaining = parseFloat(opt.dataset.remaining || '0');
      if (amount && montantInput && (montantInput.value === '' || parseFloat(montantInput.value) === 0)) {
        montantInput.value = remaining > 0 ? remaining.toFixed(2) : amount.toFixed(2);
      }
      if (libelleInput && libelleInput.value.trim() === '') {
        libelleInput.value = opt.textContent.trim();
      }
      updateMontantHint();
    });

    paiementForm.addEventListener('submit', function (event) {
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