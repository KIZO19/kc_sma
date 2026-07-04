<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$oldInput = $_SESSION['paiements_old'] ?? [];
$errors = $_SESSION['paiements_errors'] ?? [];
unset($_SESSION['paiements_old'], $_SESSION['paiements_errors']);
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Enregistrer un paiement</h1>
        <p class="text-muted"><?= htmlspecialchars(($eleve['nom'] ?? '') . ' ' . ($eleve['postnom'] ?? '') . ' ' . ($eleve['prenom'] ?? '')) ?></p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/eleves">Élèves</a></li>
          <li class="breadcrumb-item active">Enregistrer paiement</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6">
            <div class="card card-outline card-primary">
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
          <div class="card-header"><h3 class="card-title">Détails paiement</h3></div>
          <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/paiements/store">
              <?php if (!empty($eleve)): ?>
                <input type="hidden" name="eleve_id" value="<?= (int) $eleve['id'] ?>">
              <?php else: ?>
                <div class="mb-3">
                  <label class="form-label">Élève</label>
                  <select name="eleve_id" class="form-select" required>
                    <option value="">-- Sélectionner un élève --</option>
                    <?php foreach (($students ?? []) as $s): ?>
                      <option value="<?= (int) $s['id'] ?>" <?= ((int) ($oldInput['eleve_id'] ?? 0) === (int) $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars(($s['prenom'] ?? '') . ' ' . ($s['nom'] ?? '') . ' ' . ($s['postnom'] ?? '')) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Montant</label>
                <input type="number" step="0.01" name="montant" class="form-control" required value="<?= htmlspecialchars($oldInput['montant'] ?? '') ?>">
                <div class="form-text" id="montantHint">Veuillez saisir le montant du paiement. Si un motif est sélectionné, le montant ne doit pas dépasser le montant du frais.</div>
              </div>

              <div class="mb-3">
                <label class="form-label">Motif / Frais</label>
                <select name="frais_id" id="fraisSelect" class="form-select">
                  <option value="">-- Sélectionner le motif --</option>
                  <?php foreach (($fees ?? []) as $f): ?>
                    <option value="<?= (int) $f['id'] ?>" data-amount="<?= htmlspecialchars($f['montant_total'] ?? '') ?>" data-remaining="<?= htmlspecialchars($f['remaining'] ?? '') ?>" data-devise="<?= htmlspecialchars($f['devise'] ?? 'USD') ?>" <?= ((int) ($oldInput['frais_id'] ?? 0) === (int) $f['id']) ? 'selected' : '' ?>><?= htmlspecialchars($f['type_frais'] . ' - ' . ($f['nom_classe'] ?? '') ) ?> (<?= htmlspecialchars($f['devise'] ?? '') ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Libellé (optionnel)</label>
                <input type="text" name="libelle" id="libelleInput" class="form-control" value="<?= htmlspecialchars($oldInput['libelle'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Caisse / Compte</label>
                <select name="caisse_id" class="form-select">
                  <option value="">-- Sélectionner --</option>
                  <?php foreach (($caisses ?? []) as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= ((int) ($oldInput['caisse_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nom_compte']) ?> (<?= htmlspecialchars($c['type_compte']) ?>)</option>
                  <?php endforeach; ?>
                </select>
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
            <p><strong>Solde dû:</strong> <?= number_format((float) ($compte['solde_debiteur'] ?? 0), 2) ?></p>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const fraisSelect = document.getElementById('fraisSelect');
    const libelleInput = document.getElementById('libelleInput');
    const montantInput = document.querySelector('input[name="montant"]');
    const montantHint = document.getElementById('montantHint');
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
        return;
      }

      const feeAmount = parseFloat(opt.dataset.amount || '0');
      const feeRemaining = parseFloat(opt.dataset.remaining || '0');
      const feeDevise = opt.dataset.devise || 'USD';
      if (feeAmount > 0) {
        if (feeRemaining > 0) {
          montantHint.textContent = `Montant autorisé : entre ${feeRemaining.toFixed(2)} et ${feeAmount.toFixed(2)} ${feeDevise}.`;
          montantInput.min = feeRemaining;
        } else {
          montantHint.textContent = `Montant autorisé : jusqu'à ${feeAmount.toFixed(2)} ${feeDevise}.`;
          montantInput.min = 0;
        }
        montantHint.classList.add('text-muted');
        montantHint.classList.remove('text-danger');
        montantInput.max = feeAmount;
      } else {
        montantHint.textContent = 'Veuillez saisir le montant du paiement. Si un motif est sélectionné, le montant doit respecter les limites du frais.';
        montantHint.classList.add('text-muted');
        montantHint.classList.remove('text-danger');
        montantInput.min = 0;
        montantInput.max = '';
      }
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
        if (feeRemaining > 0 && amountValue < feeRemaining) {
          event.preventDefault();
          montantHint.textContent = `Le montant ne peut pas être inférieur au reste à payer (${feeRemaining.toFixed(2)} ${devise}) pour ce frais.`;
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

    updateMontantHint();
  });
</script>