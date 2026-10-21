<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$students = $students ?? [];
$availableFees = $availableFees ?? [];
$requests = $requests ?? [];
$selectedStudent = $student ?? null;
$formatMoney = static fn ($amount): string => number_format((float) $amount, 2, ',', ' ');
$formatFinancial = static function (array $financial, string $key) use ($formatMoney): string {
  $amounts = [];
  foreach ($financial as $currency => $values) {
    $amounts[] = $formatMoney($values[$key] ?? 0) . ' ' . $currency;
  }
  return $amounts ? implode('<br>', array_map(static fn (string $amount): string => htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'), $amounts)) : '<span class="text-muted">—</span>';
};
?>
<section class="content-header">
  <div class="container-fluid d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
      <h1 class="mb-1">Exonérations</h1>
      <p class="text-muted mb-0">Demandes partielles ou totales, avec validation et traçabilité par frais.</p>
    </div>
    <a href="<?= BASE_URL ?>/recouvrements" class="btn btn-outline-primary"><i class="bi bi-cash-stack me-1"></i>Retour aux recouvrements</a>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['exonerations_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['exonerations_success']) ?></div>
      <?php unset($_SESSION['exonerations_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['exonerations_errors'])): ?>
      <div class="alert alert-danger">
        <?php foreach ((array) $_SESSION['exonerations_errors'] as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
      </div>
      <?php unset($_SESSION['exonerations_errors']); ?>
    <?php endif; ?>

    <?php if (!empty($canRequest)): ?>
      <div class="card card-outline card-primary mb-4">
        <div class="card-header"><h2 class="h6 fw-bold mb-0">Nouvelle demande</h2></div>
        <div class="card-body">
          <form method="post" action="<?= BASE_URL ?>/exonerations/request" id="exonerationForm" class="row g-3 align-items-end">
            <div class="col-12 col-xl-4">
              <label for="exonerationStudent" class="form-label">Élève</label>
              <select id="exonerationStudent" name="eleve_id" class="form-select" required>
                <option value="">Sélectionner un élève</option>
                <?php foreach ($students as $student): ?>
                  <option value="<?= (int) $student['id'] ?>" <?= (int) ($selectedStudent['id'] ?? 0) === (int) $student['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(\App\Core\PersonName::format($student)) ?> · <?= htmlspecialchars($student['matricule'] ?? 'Sans matricule') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-xl-4">
              <label for="exonerationFee" class="form-label">Frais restant dû</label>
              <select id="exonerationFee" name="frais_id" class="form-select" required <?= empty($availableFees) ? 'disabled' : '' ?>>
                <option value="">Sélectionner d’abord un élève</option>
                <?php foreach ($availableFees as $fee): ?>
                  <option value="<?= (int) $fee['id'] ?>" data-remaining="<?= htmlspecialchars((string) $fee['montant_restant'], ENT_QUOTES, 'UTF-8') ?>" data-currency="<?= htmlspecialchars(strtoupper($fee['devise'] ?? 'USD'), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($fee['type_frais']) ?> · reste <?= $formatMoney($fee['montant_restant']) ?> <?= htmlspecialchars(strtoupper($fee['devise'] ?? 'USD')) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div id="exonerationFeeHint" class="form-text" aria-live="polite">Seuls les frais actifs applicables à l’inscription sont proposés.</div>
            </div>
            <div class="col-8 col-xl-2">
              <label for="exonerationAmount" class="form-label">Montant exonéré</label>
              <input id="exonerationAmount" name="montant" type="number" min="0.01" step="0.01" class="form-control" required disabled>
            </div>
            <div class="col-4 col-xl-2">
              <button class="btn btn-primary w-100" type="submit"><i class="bi bi-send me-1"></i>Soumettre</button>
            </div>
            <div class="col-12">
              <label for="exonerationReason" class="form-label">Motif</label>
              <textarea id="exonerationReason" name="motif" class="form-control" rows="2" maxlength="500" required></textarea>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="card card-outline card-secondary">
      <div class="card-header d-flex justify-content-between align-items-center gap-2">
        <h2 class="h6 fw-bold mb-0">Historique des demandes</h2>
        <span class="small text-muted"><?= number_format(count($requests), 0, ',', ' ') ?> demande(s)</span>
      </div>
      <div class="card-body p-0">
        <?php if (!$requests): ?>
          <div class="p-4 text-center text-muted">Aucune demande d’exonération enregistrée.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light"><tr><th class="ps-3">Élève</th><th>Frais</th><th class="text-end">Demandé</th><th class="text-end">Déjà payé</th><th class="text-end">Déjà exonéré</th><th class="text-end">Reste dû</th><th>Motif</th><th>Demandeur</th><th>Statut</th><th class="text-end pe-3">Décision</th></tr></thead>
              <tbody>
                <?php foreach ($requests as $request): ?>
                  <?php $status = (string) ($request['statut'] ?? 'En_attente'); ?>
                  <tr>
                    <td class="ps-3"><strong><?= htmlspecialchars(\App\Core\PersonName::format($request)) ?></strong><br><small class="text-muted"><?= htmlspecialchars($request['matricule'] ?? '') ?></small></td>
                    <td><?= htmlspecialchars($request['type_frais'] ?? 'Frais scolaire') ?></td>
                    <td class="text-end text-nowrap fw-semibold"><?= $formatMoney($request['montant'] ?? 0) ?> <?= htmlspecialchars(strtoupper($request['devise'] ?? 'USD')) ?></td>
                    <td class="text-end text-nowrap"><?= $formatFinancial($request['financial_summary'] ?? [], 'paid') ?></td>
                    <td class="text-end text-nowrap"><?= $formatFinancial($request['financial_summary'] ?? [], 'exonerated') ?></td>
                    <td class="text-end text-nowrap fw-semibold text-danger"><?= $formatFinancial($request['financial_summary'] ?? [], 'remaining') ?></td>
                    <td class="text-break" style="min-width: 180px;"><?= htmlspecialchars($request['motif'] ?? '') ?></td>
                    <td><?= htmlspecialchars($request['demandeur_nom'] ?? 'Utilisateur') ?></td>
                    <td><span class="badge rounded-pill <?= $status === 'Approuvee' ? 'text-bg-success' : ($status === 'Refusee' ? 'text-bg-danger' : 'text-bg-warning') ?>"><?= htmlspecialchars(str_replace('_', ' ', $status)) ?></span></td>
                    <td class="text-end pe-3">
                      <?php if (!empty($canValidate) && $status === 'En_attente' && (int) $request['demandeur_id'] !== (int) ($user['id'] ?? 0)): ?>
                        <form method="post" action="<?= BASE_URL ?>/exonerations/decide" class="d-inline-flex flex-wrap justify-content-end gap-1">
                          <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
                          <input type="text" name="commentaire" class="form-control form-control-sm" maxlength="500" placeholder="Commentaire (optionnel)" aria-label="Commentaire de décision" style="max-width: 190px;">
                          <button class="btn btn-sm btn-success" name="decision" value="Approuvee" type="submit" title="Approuver"><i class="bi bi-check-lg"></i></button>
                          <button class="btn btn-sm btn-outline-danger" name="decision" value="Refusee" type="submit" title="Refuser"><i class="bi bi-x-lg"></i></button>
                        </form>
                      <?php else: ?>
                        <small class="text-muted"><?= htmlspecialchars($request['validateur_nom'] ?? $request['commentaire'] ?? '') ?></small>
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
  </div>
</section>

<script>
  (() => {
    const studentSelect = document.getElementById('exonerationStudent');
    const feeSelect = document.getElementById('exonerationFee');
    const amountInput = document.getElementById('exonerationAmount');
    const hint = document.getElementById('exonerationFeeHint');
    if (!studentSelect || !feeSelect || !amountInput) return;

    const setFees = (fees) => {
      feeSelect.replaceChildren(new Option('Sélectionner un frais', ''));
      fees.forEach((fee) => {
        const option = new Option(`${fee.label} · reste ${Number(fee.montant_restant).toLocaleString('fr-FR', { minimumFractionDigits: 2 })} ${fee.devise}`, fee.id);
        option.dataset.remaining = String(fee.montant_restant);
        option.dataset.currency = fee.devise;
        feeSelect.add(option);
      });
      feeSelect.disabled = fees.length === 0;
      if (hint) hint.textContent = fees.length ? 'Le montant est plafonné au reste dû.' : 'Aucun frais actif applicable avec un solde restant.';
      amountInput.value = '';
      amountInput.max = '';
      amountInput.disabled = true;
    };

    const loadFees = async () => {
      if (!studentSelect.value) {
        setFees([]);
        if (hint) hint.textContent = 'Sélectionnez un élève pour charger les frais applicables.';
        return;
      }
      feeSelect.disabled = true;
      if (hint) hint.textContent = 'Chargement des frais applicables…';
      try {
        const response = await fetch(`<?= BASE_URL ?>/exonerations/availableFees?eleve_id=${encodeURIComponent(studentSelect.value)}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Chargement impossible');
        const data = await response.json();
        setFees(data.fees || []);
      } catch (error) {
        setFees([]);
        if (hint) hint.textContent = 'Impossible de charger les frais. Réessayez.';
      }
    };

    studentSelect.addEventListener('change', loadFees);
    feeSelect.addEventListener('change', () => {
      const option = feeSelect.selectedOptions[0];
      const remaining = Number(option?.dataset.remaining || 0);
      amountInput.max = remaining > 0 ? String(remaining) : '';
      amountInput.value = '';
      amountInput.disabled = remaining <= 0;
      if (hint && remaining > 0) {
        hint.textContent = `Maximum exonérable : ${remaining.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} ${option.dataset.currency}.`;
      }
    });

    if (studentSelect.value) loadFees();
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>