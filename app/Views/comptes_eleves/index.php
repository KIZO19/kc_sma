<?php require __DIR__ . '/../partials/app_header.php'; ?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Comptes élèves</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Home</a></li>
                <li class="breadcrumb-item active">Comptes élèves</li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">Liste des comptes élèves</div>
            <div class="card-body p-0">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Élève</th>
                    <th>Solde débiteur</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($accounts as $a): ?>
                  <tr>
                    <td><?= htmlspecialchars(trim(($a['prenom'] ?? '') . ' ' . ($a['nom'] ?? '') . ' ' . ($a['postnom'] ?? ''))) ?></td>
                    <td><?= number_format((float) ($a['solde'] ?? 0), 2) ?></td>
                    <td>
                      <a href="<?= BASE_URL ?>/eleves/view?id=<?= urlencode($a['eleve_id'] ?? '') ?>" class="btn btn-sm btn-secondary">Voir élève</a>
                      <a href="<?= BASE_URL ?>/paiements?eleve_id=<?= urlencode($a['eleve_id'] ?? '') ?>" class="btn btn-sm btn-primary">Enregistrer paiement</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($accounts)): ?>
                  <tr><td colspan="3" class="text-center text-muted">Aucun compte trouvé.</td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
