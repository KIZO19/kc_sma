<?php
$items = $items ?? [];
$generatedAt = $generatedAt ?? date('d/m/Y H:i');
$formatAmount = static fn ($amount): string => number_format((float) $amount, 2, ',', ' ');
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title ?? 'Rapport de livraison', ENT_QUOTES, 'UTF-8') ?></title>
  <style>
    @page { size: A4 landscape; margin: 12mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #1f2933; font-size: 9px; }
    h1 { margin: 0 0 4px; font-size: 18px; }
    p { margin: 3px 0 12px; color: #52606d; }
    .print { margin-bottom: 12px; }
    @media print { .print { display: none; } }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #bcc6d0; padding: 6px; text-align: left; }
    th { background: #eaf0f6; }
    .right { text-align: right; }
    .status { font-weight: bold; }
  </style>
</head>
<body>
  <?php if (!empty($printFallback)): ?><div class="print"><button onclick="window.print()">Imprimer / Enregistrer en PDF</button></div><?php endif; ?>
  <h1><?= htmlspecialchars($title ?? 'Rapport de livraison des objets achetés', ENT_QUOTES, 'UTF-8') ?></h1>
  <p><?= htmlspecialchars($schoolName ?? '', ENT_QUOTES, 'UTF-8') ?> · Année <?= htmlspecialchars($schoolYear ?? '', ENT_QUOTES, 'UTF-8') ?> · Généré le <?= htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') ?></p>
  <table>
    <thead><tr><th>Élève</th><th>Matricule</th><th>Classe</th><th>Objet acheté</th><th class="right">Prix payé</th><th>État</th><th>Date de livraison</th><th>Remis par</th></tr></thead>
    <tbody>
      <?php if (empty($items)): ?><tr><td colspan="8">Aucun objet entièrement payé n’est disponible pour livraison.</td></tr><?php endif; ?>
      <?php foreach ($items as $item): ?>
        <?php $isDelivered = !empty($item['date_livraison']); ?>
        <tr>
          <td><?= htmlspecialchars(\App\Core\PersonName::format($item), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($item['matricule'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($item['nom_classe'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($item['type_frais'], ENT_QUOTES, 'UTF-8') ?></td>
          <td class="right"><?= $formatAmount($item['montant_initial']) ?> <?= htmlspecialchars($item['devise'], ENT_QUOTES, 'UTF-8') ?></td>
          <td class="status"><?= $isDelivered ? 'Livré' : 'Payé · à remettre' ?></td>
          <td><?= $isDelivered ? htmlspecialchars(date('d/m/Y H:i', strtotime($item['date_livraison'])), ENT_QUOTES, 'UTF-8') : '—' ?></td>
          <td><?= htmlspecialchars($item['livre_par_nom'] ?? ($isDelivered ? 'Utilisateur supprimé' : '—'), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>