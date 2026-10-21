<?php require __DIR__ . '/../partials/app_header.php'; ?>
<?php
$formatMoney = static fn ($amount): string => number_format((float) $amount, 2, ',', ' ');
$formatFinancial = static function (array $financial, string $key) use ($formatMoney): string {
  $amounts = [];
  foreach ($financial as $currency => $values) {
    $amounts[] = $formatMoney($values[$key] ?? 0) . ' ' . $currency;
  }
  return $amounts ? implode('<br>', array_map(static fn (string $amount): string => htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'), $amounts)) : '<span class="text-muted">—</span>';
};
$formatFinancialText = static function (array $financial, string $key) use ($formatMoney): string {
  $amounts = [];
  foreach ($financial as $currency => $values) {
    $amounts[] = $formatMoney($values[$key] ?? 0) . ' ' . $currency;
  }
  return $amounts ? implode(' · ', $amounts) : '0,00';
};
?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Dérogations</h1>
    <p class="text-muted mb-0">Protégez temporairement un élève contre le renvoi jusqu’à une date déterminée.</p>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <?php if (!empty($_SESSION['derogations_success'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['derogations_success']) ?></div>
      <?php unset($_SESSION['derogations_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['derogations_errors'])): ?>
      <div class="alert alert-danger">
        <?php foreach ((array) $_SESSION['derogations_errors'] as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
      </div>
      <?php unset($_SESSION['derogations_errors']); ?>
    <?php endif; ?>

    <?php if (!empty($canRequest)): ?>
      <div class="card card-outline card-primary mb-4">
        <div class="card-header"><h3 class="card-title">Nouvelle demande</h3></div>
        <div class="card-body">
          <form method="post" action="<?= BASE_URL ?>/derogations/request" class="row g-3">
            <div class="col-12 col-lg-4">
              <label class="form-label" for="derogationEleve">Élève à protéger</label>
              <select class="form-select" id="derogationEleve" name="eleve_id" required>
                <option value="">Sélectionner un élève</option>
                <?php foreach (($students ?? []) as $student): ?>
                  <?php $studentFinancial = $financialSummary[(int) $student['id']] ?? []; ?>
                  <option value="<?= (int) $student['id'] ?>" data-paid="<?= htmlspecialchars($formatFinancialText($studentFinancial, 'paid'), ENT_QUOTES, 'UTF-8') ?>" data-exonerated="<?= htmlspecialchars($formatFinancialText($studentFinancial, 'exonerated'), ENT_QUOTES, 'UTF-8') ?>" data-remaining="<?= htmlspecialchars($formatFinancialText($studentFinancial, 'remaining'), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(\App\Core\PersonName::format($student)) ?> (<?= htmlspecialchars($student['matricule'] ?? 'sans matricule') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div id="derogationFinancialSummary" class="form-text mt-2" aria-live="polite">Sélectionnez un élève pour consulter les montants déjà payés et le solde dû.</div>
            </div>
            <div class="col-12 col-lg-4">
              <label class="form-label" for="derogationDateFin">Protégé jusqu’au</label>
              <input class="form-control" id="derogationDateFin" name="date_fin" type="date" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="derogationMotif">Motif</label>
              <textarea class="form-control" id="derogationMotif" name="motif" rows="2" maxlength="500" required></textarea>
            </div>
            <div class="col-12 text-end"><button class="btn btn-primary" type="submit"><i class="bi bi-send me-1"></i>Demander la dérogation</button></div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="card card-outline card-secondary">
      <div class="card-header"><h3 class="card-title">Historique des demandes</h3></div>
      <div class="card-body p-0">
        <?php if (empty($requests)): ?>
          <div class="p-4 text-center text-muted">Aucune demande de dérogation enregistrée.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light"><tr><th>Élève</th><th>Protégé jusqu’au</th><th class="text-end">Déjà payé</th><th class="text-end">Exonéré</th><th class="text-end">Reste dû</th><th>Motif</th><th>Statut</th><?php if (!empty($canValidate)): ?><th>Décision</th><?php endif; ?></tr></thead>
              <tbody>
                <?php foreach ($requests as $request): ?>
                  <?php $status = $request['statut'] ?? 'En_attente'; ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($request['eleve_nom'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($request['matricule'] ?? '') ?></small></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($request['date_fin'] ?? 'now'))) ?></td>
                    <td class="text-end text-nowrap"><?= $formatFinancial($request['financial_summary'] ?? [], 'paid') ?></td>
                    <td class="text-end text-nowrap"><?= $formatFinancial($request['financial_summary'] ?? [], 'exonerated') ?></td>
                    <td class="text-end text-nowrap fw-semibold text-danger"><?= $formatFinancial($request['financial_summary'] ?? [], 'remaining') ?></td>
                    <td><?= htmlspecialchars($request['motif'] ?? '') ?></td>
                    <td><span class="badge <?= $status === 'Approuvee' ? 'bg-success' : ($status === 'Refusee' ? 'bg-danger' : 'bg-warning text-dark') ?>"><?= htmlspecialchars(str_replace('_', ' ', $status)) ?></span></td>
                    <?php if (!empty($canValidate)): ?>
                      <td>
                        <?php if ($status === 'En_attente'): ?>
                          <form method="post" action="<?= BASE_URL ?>/derogations/decide" class="d-flex gap-1">
                            <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
                            <button class="btn btn-sm btn-success" name="decision" value="Approuvee" type="submit">Approuver</button>
                            <button class="btn btn-sm btn-outline-danger" name="decision" value="Refusee" type="submit">Refuser</button>
                          </form>
                        <?php else: ?><small class="text-muted"><?= htmlspecialchars($request['commentaire'] ?? '') ?></small><?php endif; ?>
                      </td>
                    <?php endif; ?>
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
    const studentSelect = document.getElementById('derogationEleve');
    const financialSummary = document.getElementById('derogationFinancialSummary');
    if (!studentSelect || !financialSummary) return;

    const updateFinancialSummary = () => {
      const selected = studentSelect.selectedOptions[0];
      if (!selected || !selected.value) {
        financialSummary.textContent = 'Sélectionnez un élève pour consulter les montants déjà payés et le solde dû.';
        return;
      }
      financialSummary.textContent = `Déjà payé : ${selected.dataset.paid || '0,00'} · Exonéré : ${selected.dataset.exonerated || '0,00'} · Reste dû : ${selected.dataset.remaining || '0,00'}`;
    };

    studentSelect.addEventListener('change', updateFinancialSummary);
    updateFinancialSummary();
  })();
</script>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
