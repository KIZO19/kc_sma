<?php
$columns = $columns ?? [];
$debts = $debts ?? [];
$generatedAt = $generatedAt ?? date('d/m/Y à H:i');
$formatAmount = static fn ($amount) => number_format((float) $amount, 2, ',', ' ');
$headers = [
    'classe' => 'Classe', 'eleve' => 'Élève', 'matricule' => 'Matricule',
  'frais' => 'Frais', 'initial' => 'Montant initial',
    'paye' => 'Montant déjà payé', 'restant' => 'Dette restante',
];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title ?? 'Recouvrements') ?></title>
  <style>
    @page { size: A4 portrait; margin: 12px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #20252b; }
    h1 { font-size: 18px; margin: 0 0 12px; }
    table { border-collapse: collapse; table-layout: fixed; width: 100%; }
    th, td { border: 1px solid #b8c0c8; padding: 4px; text-align: left; overflow-wrap: anywhere; }
    th { background: #e9f0f7; }
    .number { text-align: right; }
    .class-row td { background: #dbeafe; font-weight: bold; }
    .print { margin-bottom: 12px; }
    @media print { .print { display: none; } }
  </style>
</head>
<body>
  <?php if (!empty($printFallback)): ?><div class="print"><button onclick="window.print()">Imprimer / Enregistrer en PDF</button></div><?php endif; ?>
  <h1><?= htmlspecialchars($title ?? 'Recouvrements') ?></h1>
  <p>Liste générée le <?= htmlspecialchars($generatedAt) ?></p>
  <table>
    <thead><tr><?php foreach ($columns as $column): ?><th><?= htmlspecialchars($headers[$column]) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php $currentClass = null; ?>
      <?php foreach ($debts as $debt): ?>
        <?php
            $formatAmounts = static function (array $amounts) use ($formatAmount): string {
              $values = [];
              foreach ($amounts as $currency => $amount) $values[] = $formatAmount($amount) . ' ' . $currency;
              return implode(' | ', $values);
            };
          $values = [
              'classe' => $debt['nom_classe'] ?? 'Classe non définie',
              'eleve' => trim(($debt['nom'] ?? '') . ' ' . ($debt['postnom'] ?? '') . ' ' . ($debt['prenom'] ?? '')),
              'matricule' => $debt['matricule'] ?? '',
              'frais' => $debt['frais_details_liste'] ?? ($debt['frais_liste'] ?? ''),
              'initial' => $formatAmounts($debt['initial_by_currency'] ?? []),
              'paye' => $formatAmounts($debt['paid_by_currency'] ?? []),
              'restant' => $formatAmounts($debt['remaining_by_currency'] ?? []),
          ];
          $studentClass = $values['classe'];
        ?>
        <?php if ($currentClass !== $studentClass): $currentClass = $studentClass; ?><tr class="class-row"><td colspan="<?= count($columns) ?>"><?= htmlspecialchars($studentClass) ?></td></tr><?php endif; ?>
        <tr><?php foreach ($columns as $column): ?><td class="<?= in_array($column, ['initial', 'paye', 'restant'], true) ? 'number' : '' ?>"><?= htmlspecialchars($values[$column]) ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>