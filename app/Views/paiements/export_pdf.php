<?php require __DIR__ . '/../partials/app_header.php'; ?>
<style>
  body { font-family: Arial, Helvetica, sans-serif; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ddd; padding: 8px; }
  th { background: #f4f4f4; }
</style>
<section class="content">
  <div class="container-fluid">
    <div class="text-center mb-3">
      <h2>Liste des paiements</h2>
      <p>Exporté le <?= date('Y-m-d H:i') ?></p>
    </div>
    <table>
      <thead>
        <tr><th>#</th><th>Réf reçu</th><th>Élève</th><th>Date</th><th>Montant</th><th>Caisse</th><th>Agent</th><th>Libellé</th></tr>
      </thead>
      <tbody>
        <?php foreach (($payments ?? []) as $i => $p): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($p['reference_recu'] ?? '') ?></td>
            <td><?= htmlspecialchars((($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['postnom'] ?? ''))) ?></td>
            <td><?= htmlspecialchars($p['date_operation'] ?? '') ?></td>
            <td><?= htmlspecialchars($p['montant_affiche'] ?? number_format((float) ($p['montant'] ?? 0), 2)) ?></td>
            <td><?= htmlspecialchars($p['nom_compte'] ?? '') ?></td>
            <td><?= htmlspecialchars($p['agent_nom'] ?? '') ?></td>
            <td><?= htmlspecialchars($p['libelle'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mt-3 text-end">
      <button class="btn btn-primary" onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>