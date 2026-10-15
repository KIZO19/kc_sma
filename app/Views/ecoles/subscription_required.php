<?php require __DIR__ . '/../partials/app_header.php'; ?>
<section class="content-header">
  <div class="container-fluid">
    <h1>Accès à la plateforme suspendu</h1>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-xl-7 col-lg-9">
        <div class="card border-warning shadow-sm">
          <div class="card-body p-4 p-lg-5 text-center">
            <div class="text-warning mb-3"><i class="bi bi-credit-card-2-front" style="font-size: 3rem;" aria-hidden="true"></i></div>
            <h2 class="h4 fw-bold">Un abonnement valide est nécessaire</h2>
            <p class="text-muted mb-4">
              L’accès aux services de votre école est momentanément indisponible. Veuillez contacter le super-administrateur pour activer ou renouveler l’abonnement de l’établissement.
            </p>

            <?php if (!empty($school)): ?>
              <div class="rounded border p-3 text-start mb-4">
                <div class="fw-semibold"><?= htmlspecialchars($school['nom_etablissement'] ?? 'Votre établissement') ?></div>
                <div class="small text-muted"><?= htmlspecialchars($school['email_officiel'] ?? '') ?></div>
                <?php if (!empty($subscription)): ?>
                  <hr>
                  <div class="d-flex flex-wrap justify-content-between gap-2">
                    <span>Plan : <strong><?= htmlspecialchars($subscription['nom_plan'] ?? 'Plan non défini') ?></strong></span>
                    <span>Statut : <strong><?= htmlspecialchars($subscription['statut_abonnement'] ?? 'Inconnu') ?></strong></span>
                  </div>
                  <div class="small text-muted mt-2">
                    Période : <?= htmlspecialchars(formatDate($subscription['date_debut'] ?? null)) ?>
                    au <?= htmlspecialchars(formatDate($subscription['date_fin'] ?? null)) ?>
                  </div>
                <?php else: ?>
                  <div class="small text-danger mt-2">Aucun abonnement n’est enregistré pour cet établissement.</div>
                <?php endif; ?>
                <?php if (($school['statut_systeme'] ?? '') !== 'Actif'): ?>
                  <div class="small text-warning mt-2">Le statut de l’établissement est : <?= htmlspecialchars($school['statut_systeme'] ?? 'non défini') ?>.</div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-secondary">
              <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Déconnexion
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
