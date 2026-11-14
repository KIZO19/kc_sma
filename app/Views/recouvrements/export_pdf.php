<?php
$columns = $columns ?? [];
$debts = $debts ?? [];
$generatedAt = $generatedAt ?? date('d/m/Y à H:i');
$generatedByName = $generatedByName ?? '';
$generatedByRole = $generatedByRole ?? '';
$formatAmount = static fn ($amount) => number_format((float) $amount, 2, ',', ' ');
$headers = [
    'eleve' => 'Nom, postnom, prénom',
    'classe' => 'Classe',
    'paye' => 'Montant payé',
    'restant' => 'Dette restante',
];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title ?? 'Recouvrements') ?></title>
  <style>
    @page { size: A4 portrait; margin: 16px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #20252b; }
    h1 { font-size: 18px; margin: 0 0 8px; }
    table { border-collapse: collapse; table-layout: fixed; width: 100%; }
    th, td { border: 1px solid #b8c0c8; padding: 6px; text-align: left; overflow-wrap: anywhere; }
    th { background: #e9f0f7; }
    .number { text-align: right; }
    .print { margin-bottom: 12px; }
    @media print { .print { display: none; } }
  </style>
</head>
<body>
  <?php if (!empty($printFallback)): ?><div class="print"><button onclick="window.print()">Imprimer / Enregistrer en PDF</button></div><?php endif; ?>
  <h1><?= htmlspecialchars($title ?? 'Recouvrements') ?></h1>
  <p>Liste générée le <?= htmlspecialchars($generatedAt) ?><?php if ($generatedByName !== '' || $generatedByRole !== ''): ?> · Généré par : <?= htmlspecialchars($generatedByName) ?><?php if ($generatedByName !== '' && $generatedByRole !== ''): ?> — <?php endif; ?><?= htmlspecialchars($generatedByRole) ?><?php endif; ?></p>
  <table>
    <thead><tr><?php foreach ($columns as $column): ?><th><?= htmlspecialchars($headers[$column]) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php foreach ($debts as $debt): ?>
        <?php
            $formatAmounts = static function (array $amounts) use ($formatAmount): string {
              $values = [];
              foreach ($amounts as $currency => $amount) $values[] = $formatAmount($amount) . ' ' . $currency;
              return implode(' | ', $values);
            };
          $values = [
              'eleve' => \App\Core\PersonName::format($debt),
              'classe' => ($debt['nom_classe'] ?? 'Classe non définie') . (!empty($debt['nom_option']) ? ' · ' . $debt['nom_option'] : ''),
              'paye' => $formatAmounts($debt['paid_by_currency'] ?? []),
              'restant' => $formatAmounts($debt['remaining_by_currency'] ?? []),
          ];
        ?>
        <tr><?php foreach ($columns as $column): ?><td class="<?= in_array($column, ['paye', 'restant'], true) ? 'number' : '' ?>"><?= htmlspecialchars($values[$column]) ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>