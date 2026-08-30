<?php require __DIR__ . '/../partials/app_header.php'; ?>
<style>
  body {
    background: linear-gradient(135deg, #eef6ff 0%, #f7f9fc 100%);
  }
  .summary-shell {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 16px;
  }
  .summary-card {
    width: min(100%, 420px);
    background: rgba(255,255,255,0.94);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    overflow: hidden;
  }
  .summary-top {
    background: linear-gradient(135deg, #0d6efd 0%, #198754 100%);
    color: #fff;
    padding: 26px 22px 18px;
    text-align: center;
  }
  .summary-top .badge {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 11px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
  .summary-amount {
    font-size: 2rem;
    font-weight: 700;
    margin: 12px 0 8px;
  }
  .summary-body {
    padding: 22px;
  }
  .stat-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    margin-bottom: 12px;
  }
  .stat-label {
    color: #475569;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .stat-value {
    font-weight: 700;
    color: #0f172a;
    font-size: 14px;
    text-align: right;
  }
  .progress-wrap {
    background: #edf2ff;
    border-radius: 999px;
    overflow: hidden;
    height: 12px;
    margin-top: 8px;
  }
  .progress-bar {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #28a745, #198754);
  }
  .summary-footer {
    text-align: center;
    padding: 0 22px 18px;
    color: #64748b;
    font-size: 12px;
  }
  @media (max-width: 480px) {
    .summary-amount {
      font-size: 1.6rem;
    }
  }
</style>

<?php
  $currency = strtoupper(trim($ecriture['transaction_devise'] ?? $ecriture['frais_devise'] ?? 'USD')) ?: 'USD';
  $formatCurrency = function (float $amount) use ($currency) {
      $usdEquivalent = $currency !== 'USD' ? \App\Models\Devise::convertToUsd($amount, $currency) : null;
      return \App\Models\Devise::formatAmountWithCurrency($amount, $currency, $usdEquivalent);
  };
?>

<section class="summary-shell">
  <div class="summary-card">
    <div class="summary-top">
      <div class="badge">Paiement reçu</div>
      <div class="summary-amount"><?= htmlspecialchars($formatCurrency((float) ($montant_paye ?? 0))) ?></div>
      <div><?= htmlspecialchars($eleve_name ?? '-') ?></div>
    </div>

    <div class="summary-body">
      <div class="stat-box">
        <div>
          <div class="stat-label">Date</div>
          <div class="stat-value"><?= htmlspecialchars(formatDate($date_paiement ?? null)) ?></div>
        </div>
        <div class="text-success">
          <i class="bi bi-calendar-check fs-4"></i>
        </div>
      </div>

      <div class="stat-box">
        <div>
          <div class="stat-label">Élève</div>
          <div class="stat-value"><?= htmlspecialchars($eleve_name ?? '-') ?></div>
        </div>
        <div class="text-primary">
          <i class="bi bi-person-badge fs-4"></i>
        </div>
      </div>

      <div class="stat-box">
        <div>
          <div class="stat-label">Reste à payer</div>
          <div class="stat-value"><?= htmlspecialchars($formatCurrency((float) ($reste_a_payer ?? 0))) ?></div>
        </div>
        <div class="text-warning">
          <i class="bi bi-wallet2 fs-4"></i>
        </div>
      </div>

      <?php
        $paid = (float) ($montant_paye ?? 0);
        $rest = max(0.0, (float) ($reste_a_payer ?? 0));
        $total = $paid + $rest;
        $progress = $total > 0 ? min(100, ($paid / $total) * 100) : 0;
      ?>
      <div class="mt-3">
        <div class="d-flex justify-content-between small text-muted mb-1">
          <span>Progression</span>
          <span><?= round($progress, 0) ?>%</span>
        </div>
        <div class="progress-wrap">
          <div class="progress-bar" style="width: <?= $progress ?>%"></div>
        </div>
      </div>
    </div>

    <div class="summary-footer">
      <div class="mb-2">Réf: <?= htmlspecialchars($ecriture['reference_recu'] ?? '') ?></div>
      <a href="<?= BASE_URL ?>/paiements/receipt?id=<?= urlencode((string) ($ecriture['id'] ?? $_GET['id'] ?? '')) ?>" class="btn btn-outline-primary btn-sm">Voir le reçu complet</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/app_footer.php'; ?>
