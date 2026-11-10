<?php
$pageStyles = <<<'CSS'
<style>
  .accounting-dashboard .accounting-intro {
    border: 0;
    border-radius: 1rem;
    background: linear-gradient(120deg, #102a43 0%, #174a73 58%, #236b85 100%);
    color: #fff;
    box-shadow: 0 1rem 2rem rgba(16, 42, 67, .14);
  }
  .accounting-dashboard .accounting-intro p { color: rgba(255,255,255,.76); }
  .accounting-dashboard .accounting-intro .btn-light { color: #174a73; }
  .accounting-dashboard .accounting-kpi {
    border: 1px solid rgba(20, 45, 70, .08);
    border-radius: .9rem;
    box-shadow: 0 .35rem 1.2rem rgba(20, 45, 70, .06);
  }
  .accounting-dashboard .accounting-kpi .kpi-icon {
    display: grid;
    width: 2.8rem;
    height: 2.8rem;
    place-items: center;
    border-radius: .85rem;
    font-size: 1.2rem;
  }
  .accounting-dashboard .accounting-kpi .kpi-value {
    margin: .8rem 0 .15rem;
    font-size: clamp(1.25rem, 2vw, 1.8rem);
    font-weight: 700;
    letter-spacing: -.03em;
    overflow-wrap: anywhere;
  }
  .accounting-dashboard .accounting-panel {
    border: 1px solid rgba(20, 45, 70, .08);
    border-radius: .9rem;
    box-shadow: 0 .35rem 1.2rem rgba(20, 45, 70, .05);
  }
  .accounting-dashboard .accounting-panel > .card-header {
    padding: 1rem 1.15rem;
    background: transparent;
    border-bottom: 1px solid rgba(20, 45, 70, .08);
  }
  .accounting-dashboard .accounting-action {
    display: flex;
    align-items: center;
    gap: .8rem;
    padding: .85rem;
    color: inherit;
    text-decoration: none;
    border: 1px solid rgba(20, 45, 70, .1);
    border-radius: .75rem;
    transition: border-color .15s ease, transform .15s ease;
  }
  .accounting-dashboard .accounting-action:hover {
    border-color: #2876a5;
    transform: translateY(-1px);
  }
  .accounting-dashboard .accounting-section-filter {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    height: 100%;
    padding: .9rem;
    color: inherit;
    text-decoration: none;
    border: 1px solid rgba(20, 45, 70, .1);
    border-radius: .75rem;
    transition: border-color .15s ease, background-color .15s ease;
  }
  .accounting-dashboard .accounting-section-filter:hover,
  .accounting-dashboard .accounting-section-filter:focus-visible {
    border-color: #2876a5;
    background: #f6fafc;
  }
  .accounting-dashboard .accounting-action i {
    display: grid;
    width: 2.5rem;
    height: 2.5rem;
    place-items: center;
    color: #17618a;
    background: #eaf4f8;
    border-radius: .7rem;
    font-size: 1.15rem;
  }
  .accounting-dashboard .table thead th {
    color: #64748b;
    background: #f8fafc;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
  }
  [data-theme="dark"] .accounting-dashboard .accounting-kpi,
  [data-theme="dark"] .accounting-dashboard .accounting-panel { border-color: rgba(255,255,255,.1); }
  [data-theme="dark"] .accounting-dashboard .accounting-panel > .card-header { border-color: rgba(255,255,255,.1); }
  [data-theme="dark"] .accounting-dashboard .accounting-action { border-color: rgba(255,255,255,.14); }
  [data-theme="dark"] .accounting-dashboard .accounting-section-filter { border-color: rgba(255,255,255,.14); }
  [data-theme="dark"] .accounting-dashboard .accounting-section-filter:hover,
  [data-theme="dark"] .accounting-dashboard .accounting-section-filter:focus-visible { background: rgba(255,255,255,.05); }
  [data-theme="dark"] .accounting-dashboard .accounting-action i { color: #b8e3f4; background: rgba(49,130,170,.2); }
  [data-theme="dark"] .accounting-dashboard .table thead th { color: #cbd5e1; background: rgba(255,255,255,.04); }
  .school-admin-dashboard .school-admin-intro {
    border: 0;
    border-radius: 1rem;
    color: #fff;
    background: linear-gradient(120deg, #172554 0%, #1d4ed8 58%, #0891b2 100%);
    box-shadow: 0 1rem 2rem rgba(30, 64, 175, .16);
  }
  .school-admin-dashboard .school-admin-intro p { color: rgba(255,255,255,.78); }
  .school-admin-dashboard .school-kpi,
  .school-admin-dashboard .school-panel {
    border: 1px solid rgba(20, 45, 70, .08);
    border-radius: .9rem;
    box-shadow: 0 .35rem 1.2rem rgba(20, 45, 70, .05);
  }
  .school-admin-dashboard .school-kpi-icon {
    display: grid;
    width: 2.8rem;
    height: 2.8rem;
    place-items: center;
    border-radius: .85rem;
    font-size: 1.2rem;
  }
  .school-admin-dashboard .school-action {
    display: flex;
    align-items: center;
    gap: .8rem;
    padding: .85rem;
    color: inherit;
    text-decoration: none;
    border: 1px solid rgba(20, 45, 70, .1);
    border-radius: .75rem;
    transition: border-color .15s ease, transform .15s ease;
  }
  .school-admin-dashboard .school-action:hover {
    border-color: #2876a5;
    transform: translateY(-1px);
  }
  .school-admin-dashboard .school-action i {
    display: grid;
    width: 2.5rem;
    height: 2.5rem;
    place-items: center;
    color: #17618a;
    background: #eaf4f8;
    border-radius: .7rem;
    font-size: 1.15rem;
  }
  .school-admin-dashboard .school-panel .table thead th {
    color: #64748b;
    background: #f8fafc;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
  }
  [data-theme="dark"] .school-admin-dashboard .school-kpi,
  [data-theme="dark"] .school-admin-dashboard .school-panel { border-color: rgba(255,255,255,.1); }
  [data-theme="dark"] .school-admin-dashboard .school-action { border-color: rgba(255,255,255,.14); }
  [data-theme="dark"] .school-admin-dashboard .school-action i { color: #b8e3f4; background: rgba(49,130,170,.2); }
  [data-theme="dark"] .school-admin-dashboard .school-panel .table thead th { color: #cbd5e1; background: rgba(255,255,255,.04); }
  .academic-dashboard .academic-intro {
    border: 0;
    border-radius: 1rem;
    color: #fff;
    background: linear-gradient(120deg, #12243a 0%, #174b70 58%, #16859a 100%);
    box-shadow: 0 1rem 2rem rgba(18, 36, 58, .16);
  }
  .academic-dashboard .academic-intro p { color: rgba(255,255,255,.78); }
  .academic-dashboard .academic-kpi,
  .academic-dashboard .academic-panel {
    border: 1px solid rgba(20, 45, 70, .08);
    border-radius: .9rem;
    box-shadow: 0 .35rem 1.2rem rgba(20, 45, 70, .05);
  }
  .academic-dashboard .academic-kpi-icon {
    display: grid;
    width: 2.8rem;
    height: 2.8rem;
    place-items: center;
    border-radius: .85rem;
    font-size: 1.2rem;
  }
  .academic-dashboard .academic-kpi-value {
    margin: .8rem 0 .15rem;
    font-size: clamp(1.5rem, 2vw, 2rem);
    font-weight: 750;
    letter-spacing: -.03em;
  }
  .academic-dashboard .academic-action {
    display: flex;
    align-items: center;
    gap: .85rem;
    min-height: 4.5rem;
    padding: .8rem;
    color: inherit;
    text-decoration: none;
    border: 1px solid rgba(20, 45, 70, .1);
    border-radius: .75rem;
    transition: border-color .15s ease, transform .15s ease, box-shadow .15s ease;
  }
  .academic-dashboard .academic-action:hover {
    border-color: #2876a5;
    box-shadow: 0 .4rem 1rem rgba(20, 45, 70, .08);
    transform: translateY(-1px);
  }
  .academic-dashboard .academic-action i {
    display: grid;
    width: 2.6rem;
    height: 2.6rem;
    flex: 0 0 auto;
    place-items: center;
    color: #17618a;
    background: #eaf4f8;
    border-radius: .7rem;
    font-size: 1.15rem;
  }
  [data-theme="dark"] .academic-dashboard .academic-kpi,
  [data-theme="dark"] .academic-dashboard .academic-panel { border-color: rgba(255,255,255,.1); }
  [data-theme="dark"] .academic-dashboard .academic-action { border-color: rgba(255,255,255,.14); }
  [data-theme="dark"] .academic-dashboard .academic-action i { color: #b8e3f4; background: rgba(49,130,170,.2); }
  .workspace-dashboard .workspace-intro {
    border: 0;
    border-radius: .9rem;
    color: #fff;
    background: linear-gradient(120deg, #17324d 0%, #17647a 100%);
  }
  .workspace-dashboard .workspace-intro p { color: rgba(255,255,255,.78); }
  .workspace-dashboard .workspace-panel,
  .workspace-dashboard .workspace-kpi {
    height: 100%;
    border: 1px solid var(--border);
    border-radius: .75rem;
    background: var(--card);
  }
  .workspace-dashboard .workspace-kpi-value { margin: .65rem 0 .15rem; font-size: 1.65rem; font-weight: 700; color: var(--text); }
  .workspace-dashboard .workspace-kpi-icon { display: grid; width: 2.8rem; height: 2.8rem; place-items: center; border-radius: .75rem; font-size: 1.15rem; }
  .workspace-dashboard .workspace-action {
    display: flex;
    min-height: 4.25rem;
    align-items: center;
    gap: .75rem;
    padding: .75rem;
    border: 1px solid var(--border);
    border-radius: .65rem;
    color: var(--text);
    text-decoration: none;
    transition: border-color .15s ease, background-color .15s ease;
  }
  .workspace-dashboard .workspace-action:hover { border-color: var(--sidebar-active); background: var(--surface-muted); }
  .workspace-dashboard .workspace-action i { display: grid; width: 2.4rem; height: 2.4rem; flex: 0 0 auto; place-items: center; border-radius: .55rem; background: var(--surface-muted); color: var(--sidebar-active); font-size: 1.1rem; }
  [data-theme="dark"] .workspace-dashboard .workspace-action i { background: rgba(255,255,255,.06); }
  .access-control-table .access-control-switch {
    display: inline-flex;
    min-width: 3.5rem;
    min-height: 2.5rem;
    align-items: center;
    justify-content: center;
    padding: .35rem;
  }
  .access-control-table .access-control-toggle.form-check-input {
    display: block;
    width: 3rem !important;
    min-width: 3rem;
    max-width: 3rem !important;
    height: 1.6rem !important;
    margin: 0 !important;
    flex: 0 0 3rem;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    border: 2px solid #64748b;
    border-radius: 2rem;
    background-color: #cbd5e1;
    background-position: left center;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .22);
    transition: background-color .15s ease, border-color .15s ease, background-position .15s ease;
  }
  .access-control-table .access-control-toggle.form-check-input:checked {
    border-color: #0756c9;
    background-color: #0d6efd;
    background-position: right center;
  }
  .access-control-table .access-control-toggle.form-check-input:focus-visible {
    outline: 3px solid rgba(13, 110, 253, .38);
    outline-offset: 2px;
    box-shadow: none;
  }
  [data-theme="dark"] .access-control-table .access-control-toggle.form-check-input:not(:checked) {
    border-color: #cbd5e1;
    background-color: #475569;
  }
</style>
CSS;
require __DIR__ . '/../partials/app_header.php';
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><?= ($role ?? '') === 'comptable_école' ? 'Espace comptable' : ((($role ?? '') === 'super_admin') ? 'Pilotage des écoles' : (in_array(($role ?? ''), ['préfet_école', 'DP_école'], true) ? 'Pilotage pédagogique' : 'Tableau de bord')) ?></h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Accueil</a></li>
                <li class="breadcrumb-item active"><?= ($role ?? '') === 'comptable_école' ? 'Espace comptable' : ((($role ?? '') === 'super_admin') ? 'Pilotage des écoles' : (in_array(($role ?? ''), ['préfet_école', 'DP_école'], true) ? 'Pilotage pédagogique' : 'Tableau de bord')) ?></li>
              </ol>
            </div>
          </div>
        </div>
      <!-- </section> -->

      <section class="content">
        <div class="container-fluid">
          <?php if (($role ?? '') === 'agent_ecole' && (($user['statut'] ?? 'Actif') === 'Inactif') && (empty($user['ecole_id']) || (int) $user['ecole_id'] === 0)): ?>
            <div class="alert alert-warning border-0 shadow-sm mb-4">
              <strong>Compte en attente :</strong> votre compte agent est bien créé mais n’a pas encore été affecté à une école. L’administrateur de l’école doit le valider pour l’activer. Il sera automatiquement supprimé après 6 jours s’il n’est pas approuvé.
            </div>
          <?php endif; ?>
          <?php if (($role ?? '') === 'super_admin'): ?>
          <div class="school-admin-dashboard">
            <div class="card school-admin-intro mb-4">
              <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                <div>
                  <span class="badge rounded-pill text-bg-light mb-3">ADMINISTRATION DE LA PLATEFORME</span>
                  <h2 class="h3 fw-bold mb-2">Vue d’ensemble du réseau scolaire</h2>
                  <p class="mb-0">Gérez les établissements, traitez les demandes d’inscription et suivez leur statut depuis un espace centralisé.</p>
                </div>
                <a href="<?= BASE_URL ?>/ecoles/create" class="btn btn-light fw-semibold px-4 py-2 flex-shrink-0">
                  <i class="bi bi-plus-lg me-2"></i>Ajouter une école
                </a>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <?php foreach ($dashboardData['schoolManagement']['stats'] ?? [] as $stat): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                  <div class="card school-kpi h-100 mb-0">
                    <div class="card-body p-3 p-lg-4">
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted fw-semibold"><?= htmlspecialchars($stat['title']) ?></span>
                        <span class="school-kpi-icon text-<?= htmlspecialchars($stat['tone']) ?> bg-<?= htmlspecialchars($stat['tone']) ?>-subtle">
                          <i class="bi <?= htmlspecialchars($stat['icon']) ?>" aria-hidden="true"></i>
                        </span>
                      </div>
                      <div class="h2 fw-bold my-3"><?= htmlspecialchars((string) $stat['value']) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($stat['hint']) ?></div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-xl-8">
                <div class="card school-panel h-100 mb-0">
                  <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                    <div>
                      <h2 class="h6 fw-bold mb-1">Écoles récemment enregistrées</h2>
                      <span class="small text-muted">Suivez les derniers établissements ajoutés à la plateforme.</span>
                    </div>
                    <a href="<?= BASE_URL ?>/ecoles" class="btn btn-sm btn-outline-primary">
                      Toutes les écoles <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                    </a>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle mb-0">
                        <thead>
                          <tr>
                            <th class="ps-3">Établissement</th>
                            <th>Abonnement</th>
                            <th>Statut</th>
                            <th>Inscription</th>
                            <th class="text-end pe-3">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($dashboardData['schoolManagement']['recentSchools'] ?? [] as $school): ?>
                            <?php
                              $schoolStatus = (string) ($school['statut_systeme'] ?? '');
                              $subscriptionStatus = (string) ($school['statut_abonnement'] ?? '');
                              $subscriptionStart = !empty($school['date_debut']) ? strtotime((string) $school['date_debut']) : false;
                              $subscriptionEnd = !empty($school['date_fin']) ? strtotime((string) $school['date_fin']) : false;
                              if (
                                  $subscriptionStatus === 'Actif'
                                  && $subscriptionStart !== false
                                  && $subscriptionStart > time()
                              ) {
                                  $subscriptionStatus = 'À venir';
                              } elseif (
                                  $subscriptionStatus === 'Actif'
                                  && $subscriptionEnd !== false
                                  && date('Y-m-d', $subscriptionEnd) < date('Y-m-d')
                              ) {
                                  $subscriptionStatus = 'Expiré';
                              }
                              $statusClass = match ($schoolStatus) {
                                  'Actif' => 'success',
                                  'Suspendu' => 'danger',
                                  default => 'warning',
                              };
                              $statusLabel = $schoolStatus === 'En_Attente' ? 'En attente' : ($schoolStatus !== '' ? $schoolStatus : 'Inconnu');
                              $subscriptionClass = match ($subscriptionStatus) {
                                  'Actif' => 'success',
                                  'Expiré', 'Expire', 'À venir' => 'warning',
                                  'Resilie' => 'danger',
                                  default => 'secondary',
                              };
                            ?>
                            <tr>
                              <td class="ps-3">
                                <div class="fw-semibold"><?= htmlspecialchars($school['nom_etablissement'] ?? 'École sans nom') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($school['email_officiel'] ?? 'Aucun email renseigné') ?></small>
                              </td>
                              <td>
                                <span class="badge text-bg-<?= $subscriptionClass ?>"><?= htmlspecialchars($subscriptionStatus !== '' ? $subscriptionStatus : 'Aucun') ?></span>
                                <div class="small text-muted mt-1"><?= htmlspecialchars($school['plan_name'] ?? 'Aucun plan') ?></div>
                                <?php if (!empty($school['date_fin'])): ?>
                                  <div class="small text-muted">Fin : <?= htmlspecialchars(formatDate($school['date_fin'])) ?></div>
                                <?php endif; ?>
                              </td>
                              <td><span class="badge text-bg-<?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                              <td><?= htmlspecialchars(formatDate($school['date_creation_compte'] ?? null)) ?></td>
                              <td class="text-end pe-3">
                                <a href="<?= BASE_URL ?>/ecoles/edit?id=<?= (int) ($school['id'] ?? 0) ?>" class="btn btn-sm btn-outline-secondary" aria-label="Modifier <?= htmlspecialchars($school['nom_etablissement'] ?? 'cette école') ?>" title="Modifier">
                                  <i class="bi bi-pencil" aria-hidden="true"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/ecoles/addSubscription?ecole_id=<?= (int) ($school['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary" aria-label="Gérer l’abonnement de <?= htmlspecialchars($school['nom_etablissement'] ?? 'cette école') ?>" title="Gérer l’abonnement">
                                  <i class="bi bi-credit-card" aria-hidden="true"></i>
                                </a>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                          <?php if (empty($dashboardData['schoolManagement']['recentSchools'])): ?>
                            <tr>
                              <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-buildings fs-2 d-block mb-2" aria-hidden="true"></i>
                                Aucune école enregistrée pour le moment.
                              </td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

              </div>
              <div class="col-xl-4">
                <div class="card school-panel h-100 mb-0">
                  <div class="card-header">
                    <h2 class="h6 fw-bold mb-1">Gestion des établissements</h2>
                    <span class="small text-muted">Accès direct aux opérations principales</span>
                  </div>
                  <div class="card-body d-grid gap-2">
                    <a class="school-action" href="<?= BASE_URL ?>/ecoles">
                      <i class="bi bi-buildings" aria-hidden="true"></i>
                      <span><strong class="d-block">Gérer les écoles</strong><small class="text-muted">Consulter les statuts et abonnements</small></span>
                    </a>
                    <a class="school-action" href="<?= BASE_URL ?>/ecoles/addSubscription">
                      <i class="bi bi-credit-card" aria-hidden="true"></i>
                      <span><strong class="d-block">Gérer les abonnements</strong><small class="text-muted">Accorder ou renouveler un accès</small></span>
                    </a>
                    <a class="school-action" href="<?= BASE_URL ?>/ecoles/plans">
                      <i class="bi bi-tags" aria-hidden="true"></i>
                      <span><strong class="d-block">Définir les tarifs</strong><small class="text-muted">Gérer les prix et la durée des plans</small></span>
                    </a>
                    <a class="school-action" href="<?= BASE_URL ?>/ecoles/requests">
                      <i class="bi bi-inbox" aria-hidden="true"></i>
                      <span><strong class="d-block">Demandes d’inscription</strong><small class="text-muted">Examiner les demandes reçues</small></span>
                    </a>
                    <a class="school-action" href="<?= BASE_URL ?>/activities#real-time">
                      <i class="bi bi-activity" aria-hidden="true"></i>
                      <span><strong class="d-block">Activité en temps réel</strong><small class="text-muted">Suivre les écoles et utilisateurs connectés</small></span>
                    </a>
                    <a class="school-action" href="<?= BASE_URL ?>/ecoles/create">
                      <i class="bi bi-plus-circle" aria-hidden="true"></i>
                      <span><strong class="d-block">Créer une école</strong><small class="text-muted">Enregistrer un établissement</small></span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php elseif (in_array(($role ?? ''), ['préfet_école', 'DP_école'], true)): ?>
          <?php
            $isPrefect = (bool) ($dashboardData['direction']['isPrefect'] ?? false);
            $quickActions = $isPrefect
                ? [
                    ['name' => 'Créer une classe', 'description' => 'Organiser les groupes scolaires', 'path' => '/classes/create', 'icon' => 'bi-plus-square'],
                    ['name' => 'Gérer les options', 'description' => 'Configurer les filières disponibles', 'path' => '/options', 'icon' => 'bi-grid-1x2'],
                    ['name' => 'Admissions', 'description' => 'Suivre les dossiers d’inscription', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                    ['name' => 'Répertoire des élèves', 'description' => 'Consulter les effectifs de l’école', 'path' => '/eleves', 'icon' => 'bi-people'],
                    ['name' => 'Notes et résultats', 'description' => 'Accéder au suivi des évaluations', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                    ['name' => 'Vérifier la solvabilité', 'description' => 'Scanner un reçu ou rechercher un paiement', 'path' => '/paiements/verifierSolvabilite', 'icon' => 'bi-qr-code-scan'],
                ]
                : [
                    ['name' => 'Créer une classe', 'description' => 'Mettre à jour l’organisation scolaire', 'path' => '/classes/create', 'icon' => 'bi-plus-square'],
                    ['name' => 'Suivi des élèves', 'description' => 'Consulter les effectifs de l’école', 'path' => '/eleves', 'icon' => 'bi-people'],
                    ['name' => 'Notes et résultats', 'description' => 'Suivre les performances scolaires', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                    ['name' => 'Bulletins', 'description' => 'Consulter les résultats publiés', 'path' => '/bulletins', 'icon' => 'bi-file-earmark-text'],
                    ['name' => 'Présences', 'description' => 'Suivre l’assiduité des élèves', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                    ['name' => 'Activités scolaires', 'description' => 'Consulter les évaluations et événements', 'path' => '/evaluations', 'icon' => 'bi-journal-check'],
                ];
            $availableModulePaths = array_column($modules ?? [], 'path');
            $quickActions = array_values(array_filter($quickActions, static function (array $action) use ($availableModulePaths): bool {
                $modulePath = '/' . explode('/', trim($action['path'], '/'), 2)[0];
                return in_array($modulePath, $availableModulePaths, true);
            }));
          ?>
          <div class="academic-dashboard">
            <div class="card academic-intro mb-4">
              <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                <div>
                  <span class="badge rounded-pill text-bg-light mb-3"><?= $isPrefect ? 'ESPACE DU PRÉFET' : 'DIRECTION DES ÉTUDES' ?></span>
                  <h2 class="h3 fw-bold mb-2">Bonjour <?= htmlspecialchars($user['nom_complet'] ?? '') ?>, bienvenue dans votre espace de pilotage.</h2>
                  <p class="mb-0"><?= htmlspecialchars($dashboardData['direction']['schoolName'] ?? 'Votre établissement') ?> · <?= $isPrefect ? 'Organisez les classes et les parcours scolaires.' : 'Suivez les effectifs et les activités pédagogiques.' ?></p>
                </div>
                <a href="<?= BASE_URL ?>/classes" class="btn btn-light fw-semibold px-4 py-2 flex-shrink-0">
                  <i class="bi bi-houses me-2"></i>Voir les classes
                </a>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <?php foreach ($dashboardData['stats'] ?? [] as $stat): ?>
                <?php
                  $tone = match ($stat['bg'] ?? '') {
                      'bg-success' => 'success',
                      'bg-info' => 'info',
                      'bg-warning' => 'warning',
                      'bg-danger' => 'danger',
                      default => 'primary',
                  };
                ?>
                <div class="col-12 col-sm-6 col-xl-3">
                  <div class="card academic-kpi h-100 mb-0">
                    <div class="card-body p-3 p-lg-4">
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted fw-semibold"><?= htmlspecialchars($stat['title'] ?? '') ?></span>
                        <span class="academic-kpi-icon text-<?= $tone ?> bg-<?= $tone ?>-subtle"><i class="bi <?= htmlspecialchars($stat['icon'] ?? 'bi-bar-chart-line') ?>"></i></span>
                      </div>
                      <div class="academic-kpi-value"><?= htmlspecialchars((string) ($stat['value'] ?? '0')) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($stat['hint'] ?? '') ?></div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="row g-3">
              <div class="col-xl-8">
                <div class="card academic-panel h-100 mb-0">
                  <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <span class="small text-uppercase fw-bold text-primary">Accès direct</span>
                    <h2 class="h5 fw-bold mb-1 mt-1">Actions de pilotage</h2>
                    <p class="small text-muted mb-0">Les outils sont adaptés à vos responsabilités dans l’établissement.</p>
                  </div>
                  <div class="card-body px-4 pt-3">
                    <div class="row g-2">
                      <?php foreach ($quickActions as $action): ?>
                        <div class="col-md-6">
                          <a class="academic-action h-100" href="<?= BASE_URL . $action['path'] ?>">
                            <i class="bi <?= htmlspecialchars($action['icon']) ?>" aria-hidden="true"></i>
                            <span><strong class="d-block"><?= htmlspecialchars($action['name']) ?></strong><small class="text-muted"><?= htmlspecialchars($action['description']) ?></small></span>
                          </a>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xl-4">
                <div class="card academic-panel h-100 mb-0">
                  <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <span class="small text-uppercase fw-bold text-primary">Repères</span>
                    <h2 class="h5 fw-bold mb-1 mt-1"><?= htmlspecialchars($dashboardData['overview']['title'] ?? 'Vue synthétique') ?></h2>
                    <p class="small text-muted mb-0">Indicateurs calculés pour votre établissement.</p>
                  </div>
                  <div class="card-body px-4 pt-2">
                    <?php foreach ($dashboardData['overview']['items'] ?? [] as $item): ?>
                      <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <span class="small text-muted"><?= htmlspecialchars($item['label'] ?? '') ?></span>
                        <strong><?= htmlspecialchars($item['value'] ?? '0') ?></strong>
                      </div>
                    <?php endforeach; ?>
                    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-primary btn-sm mt-3">
                      <i class="bi bi-arrow-clockwise me-1"></i>Actualiser les indicateurs
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php elseif (($role ?? '') !== 'comptable_école'): ?>
          <?php
            $workspaceProfile = match ($role ?? '') {
                'ecole_admin' => ['title' => 'Pilotage de l’établissement', 'eyebrow' => 'ADMINISTRATION'],
                'promoteur_école' => ['title' => 'Vue d’ensemble de l’établissement', 'eyebrow' => 'PROMOTEUR'],
                'DE_école' => ['title' => 'Pilotage des études', 'eyebrow' => 'DIRECTION DES ÉTUDES', 'description' => 'Suivez les présences des enseignants et pilotez les activités pédagogiques.'],
                'DD_école' => ['title' => 'Suivi des départements', 'eyebrow' => 'DIRECTION DÉPARTEMENTALE'],
                'DA_école' => ['title' => 'Suivi de l’établissement', 'eyebrow' => 'DIRECTION ADJOINTE'],
                'sec_école' => ['title' => 'Espace secrétariat', 'eyebrow' => 'SECRÉTARIAT'],
                'enseignant_école' => ['title' => 'Espace enseignant', 'eyebrow' => 'ENSEIGNEMENT'],
                'eleve_ecole', 'eleve_école' => ['title' => 'Espace élève', 'eyebrow' => 'SCOLARITÉ'],
                'parent_ecole' => ['title' => 'Espace famille', 'eyebrow' => 'SUIVI FAMILIAL'],
                'agent_ecole' => ['title' => 'Espace agent', 'eyebrow' => 'GESTION SCOLAIRE'],
                default => ['title' => 'Tableau de bord', 'eyebrow' => strtoupper($roleLabel ?? 'ESPACE PERSONNEL')],
            };
            $preferredPaths = match ($role ?? '') {
                'ecole_admin' => ['/ecoles', '/agents', '/inscriptions', '/eleves', '/paiements', '/rapports', '/paiements/gestionAutorisations'],
                'DE_école' => ['/presences', '/evaluations', '/notes', '/bulletins', '/horaires', '/classes', '/eleves', '/inscriptions'],
                'promoteur_école', 'DD_école', 'DA_école' => ['/inscriptions', '/utilisateurs', '/classes', '/eleves', '/derogations', '/notes', '/bulletins', '/rapports'],
                'sec_école' => ['/inscriptions', '/parents', '/eleves', '/communiques', '/presences', '/rapports'],
                'enseignant_école' => ['/presences', '/notes', '/eleves'],
                'eleve_ecole' => [
                    '/mes-notes',
                    '/espace-eleve/discipline',
                    '/mes-paiements',
                    '/espace-eleve/derogations',
                    '/espace-eleve/cours',
                    '/espace-eleve/evaluations',
                    '/mon-emploi',
                    '/espace-eleve/communiques',
                    '/espace-eleve/presences',
                ],
                'eleve_école' => [
                    '/mes-notes',
                    '/espace-eleve/discipline',
                    '/mes-paiements',
                    '/espace-eleve/derogations',
                    '/espace-eleve/cours',
                    '/espace-eleve/evaluations',
                    '/mon-emploi',
                    '/espace-eleve/communiques',
                    '/espace-eleve/presences',
                ],
                'parent_ecole' => ['/mes-enfants', '/bulletins-enfant', '/paiements-enfant', '/messages'],
                'agent_ecole' => ['/inscriptions'],
                default => array_column($modules ?? [], 'path'),
            };
            $moduleByPath = [];
            foreach ($modules ?? [] as $module) {
                $moduleByPath[$module['path']] = $module;
            }
            $workspaceActions = [];
            foreach ($preferredPaths as $path) {
                if (isset($moduleByPath[$path])) {
                    $workspaceActions[$path] = $moduleByPath[$path];
                }
            }
            if (($role ?? '') === 'DE_école' && isset($workspaceActions['/presences'])) {
              $workspaceActions['/presences']['name'] = 'Présence des enseignants';
              $workspaceActions = ['/presences' => $workspaceActions['/presences']] + $workspaceActions;
            }
            $workspaceActions = array_slice(array_values($workspaceActions), 0, 9);
            $primaryAction = count($workspaceActions) > 1 ? $workspaceActions[0] : null;
            $secondaryActions = $primaryAction ? array_slice($workspaceActions, 1) : $workspaceActions;
          ?>
          <div class="workspace-dashboard">
            <div class="card workspace-intro mb-4">
              <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                <div>
                  <span class="badge rounded-pill text-bg-light mb-3"><?= htmlspecialchars($workspaceProfile['eyebrow']) ?></span>
                  <h2 class="h3 fw-bold mb-2"><?= htmlspecialchars($workspaceProfile['title']) ?></h2>
                  <p class="mb-0"><?= htmlspecialchars($user['nom_complet'] ?? ($roleLabel ?? 'Votre espace')) ?></p>
                  <?php if (!empty($workspaceProfile['description'])): ?>
                    <p class="small mb-0 mt-2"><?= htmlspecialchars($workspaceProfile['description']) ?></p>
                  <?php endif; ?>
                </div>
                <?php if ($primaryAction): ?>
                  <a href="<?= BASE_URL . $primaryAction['path'] ?>" class="btn btn-light fw-semibold px-4 py-2 flex-shrink-0">
                    <i class="bi <?= htmlspecialchars($primaryAction['icon']) ?> me-2"></i><?= htmlspecialchars($primaryAction['name']) ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($dashboardData['stats'])): ?>
              <div class="row g-3 mb-4">
                <?php foreach ($dashboardData['stats'] as $stat): ?>
                  <?php
                    $tone = match ($stat['bg'] ?? '') {
                        'bg-success' => 'success', 'bg-info' => 'info', 'bg-warning' => 'warning', 'bg-danger' => 'danger', default => 'primary',
                    };
                  ?>
                  <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card workspace-kpi mb-0">
                      <div class="card-body p-3 p-lg-4">
                        <div class="d-flex justify-content-between align-items-center">
                          <span class="small text-muted fw-semibold"><?= htmlspecialchars($stat['title'] ?? '') ?></span>
                          <span class="workspace-kpi-icon text-<?= $tone ?> bg-<?= $tone ?>-subtle"><i class="bi <?= htmlspecialchars($stat['icon'] ?? 'bi-bar-chart-line') ?>"></i></span>
                        </div>
                        <div class="workspace-kpi-value"><?= htmlspecialchars((string) ($stat['value'] ?? '0')) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($stat['hint'] ?? '') ?></div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="card workspace-panel">
              <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                <span class="small text-uppercase fw-bold text-primary">Espace de travail</span>
                <h2 class="h5 fw-bold mb-0 mt-1">Accès rapides</h2>
              </div>
              <div class="card-body px-4 pt-3">
                <?php if ($secondaryActions): ?>
                  <div class="row g-2">
                    <?php foreach ($secondaryActions as $action): ?>
                      <div class="col-12 col-md-6 col-xl-4">
                        <a class="workspace-action h-100" href="<?= BASE_URL . $action['path'] ?>">
                          <i class="bi <?= htmlspecialchars($action['icon']) ?>" aria-hidden="true"></i>
                          <strong><?= htmlspecialchars($action['name']) ?></strong>
                          <i class="bi bi-arrow-up-right ms-auto" aria-hidden="true"></i>
                        </a>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <p class="text-muted mb-0">Aucun module n’est actuellement associé à ce rôle.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php else: ?>
          <div class="accounting-dashboard">
            <div class="card accounting-intro mb-4">
              <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                <div>
                  <span class="badge rounded-pill text-bg-light mb-3">GESTION FINANCIÈRE</span>
                  <h2 class="h3 fw-bold mb-2">Bonjour, voici la situation financière de votre établissement.</h2>
                  <p class="mb-0">Suivez les encaissements, les soldes à recouvrer et les opérations récentes depuis un seul espace.</p>
                </div>
                <a href="<?= BASE_URL ?>/paiements/create" class="btn btn-light fw-semibold px-4 py-2 flex-shrink-0">
                  <i class="bi bi-plus-circle me-2"></i>Enregistrer un paiement
                </a>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <?php foreach ($dashboardData['stats'] ?? [] as $index => $stat): ?>
                <?php
                  $kpiPalette = [
                      ['icon' => 'text-danger bg-danger-subtle', 'label' => 'bi-wallet2'],
                      ['icon' => 'text-success bg-success-subtle', 'label' => 'bi-cash-coin'],
                      ['icon' => 'text-primary bg-primary-subtle', 'label' => 'bi-people'],
                      ['icon' => 'text-warning bg-warning-subtle', 'label' => 'bi-person-lines-fill'],
                  ];
                  $kpi = $kpiPalette[$index % count($kpiPalette)];
                ?>
                <div class="col-12 col-sm-6 col-xl-3">
                  <div class="card accounting-kpi h-100 mb-0">
                    <div class="card-body p-3 p-lg-4">
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted fw-semibold"><?= htmlspecialchars($stat['title'] ?? '') ?></span>
                        <span class="kpi-icon <?= htmlspecialchars($kpi['icon']) ?>"><i class="bi <?= htmlspecialchars($kpi['label']) ?>"></i></span>
                      </div>
                      <div class="kpi-value"><?= htmlspecialchars((string) ($stat['value'] ?? '0')) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($stat['hint'] ?? '') ?></div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="card accounting-panel mb-4">
              <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                  <h2 class="h6 fw-bold mb-1">Filtrer les recouvrements par section</h2>
                  <span class="small text-muted">Choisir une section pour afficher ses dettes</span>
                </div>
                <a href="<?= BASE_URL ?>/recouvrements" class="btn btn-sm btn-outline-secondary">Toutes les sections</a>
              </div>
              <div class="card-body py-2">
                <div class="row g-0">
                  <?php foreach ($dashboardData['accounting']['sectionStats'] ?? [] as $index => $sectionStat): ?>
                    <?php
                      $sectionTones = [
                        ['icon' => 'bi-house-heart', 'color' => 'text-warning'],
                        ['icon' => 'bi-book', 'color' => 'text-success'],
                        ['icon' => 'bi-mortarboard', 'color' => 'text-primary'],
                      ];
                      $sectionTone = $sectionTones[$index % count($sectionTones)];
                    ?>
                    <div class="col-12 col-sm-4">
                      <a class="accounting-section-filter" href="<?= BASE_URL ?>/recouvrements?section_id=<?= (int) ($sectionStat['id'] ?? 0) ?>" aria-label="Filtrer les recouvrements par <?= htmlspecialchars($sectionStat['nom_section'] ?? 'section', ENT_QUOTES, 'UTF-8') ?>">
                        <span class="d-flex align-items-center gap-3">
                          <span class="kpi-icon <?= $sectionTone['color'] ?> bg-light"><i class="bi <?= $sectionTone['icon'] ?>"></i></span>
                          <span>
                            <span class="small text-muted fw-semibold d-block"><?= htmlspecialchars($sectionStat['nom_section'] ?? 'Section') ?></span>
                            <span class="h4 fw-bold mb-0 d-block"><?= number_format((int) ($sectionStat['total_eleves'] ?? 0), 0, ',', ' ') ?></span>
                            <span class="small text-muted">élève(s) inscrit(s)</span>
                          </span>
                        </span>
                        <i class="bi bi-funnel text-primary" aria-hidden="true"></i>
                      </a>
                    </div>
                  <?php endforeach; ?>
                  <?php if (empty($dashboardData['accounting']['sectionStats'])): ?>
                    <div class="col-12 py-3 text-center small text-muted">Aucune inscription pour l’année scolaire active.</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-xl-8">
                <div class="card accounting-panel h-100 mb-0">
                  <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                    <div>
                      <h2 class="h6 fw-bold mb-1"><?= htmlspecialchars($dashboardData['chart']['title'] ?? 'Évolution des encaissements') ?></h2>
                      <span class="small text-muted">Encaissements du <?= date('d/m/Y', strtotime($dashboardData['chart']['from'] ?? 'today')) ?> au <?= date('d/m/Y', strtotime($dashboardData['chart']['to'] ?? 'today')) ?></span>
                    </div>
                    <div class="d-flex flex-column flex-xl-row flex-wrap align-items-xl-center gap-2">
                      <form method="get" action="<?= BASE_URL ?>/dashboard" class="d-flex flex-wrap align-items-end gap-2">
                        <div>
                          <label for="paymentDateFrom" class="form-label small mb-1">Du</label>
                          <input id="paymentDateFrom" class="form-control form-control-sm" type="date" name="payment_from" value="<?= htmlspecialchars($dashboardData['chart']['from'] ?? '') ?>" required>
                        </div>
                        <div>
                          <label for="paymentDateTo" class="form-label small mb-1">Au</label>
                          <input id="paymentDateTo" class="form-control form-control-sm" type="date" name="payment_to" value="<?= htmlspecialchars($dashboardData['chart']['to'] ?? '') ?>" required>
                        </div>
                        <?php if (!empty($dashboardData['chart']['currencies'])): ?>
                          <div>
                            <label for="paymentCurrency" class="form-label small mb-1">Devise</label>
                            <select id="paymentCurrency" name="payment_currency" class="form-select form-select-sm">
                              <?php foreach ($dashboardData['chart']['currencies'] as $currency): ?>
                                <option value="<?= htmlspecialchars($currency, ENT_QUOTES, 'UTF-8') ?>" <?= ($dashboardData['chart']['currency'] ?? '') === $currency ? 'selected' : '' ?>><?= htmlspecialchars($currency) ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-primary" type="submit" title="Afficher la période"><i class="bi bi-filter me-1"></i>Afficher</button>
                      </form>
                      <?php if (!empty($dashboardData['chart']['datasets'])): ?>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                          <label class="visually-hidden" for="paymentSectionFilter">Section du graphique</label>
                          <select id="paymentSectionFilter" class="form-select form-select-sm" aria-label="Filtrer par section" style="width: auto;">
                            <option value="">Toutes les sections</option>
                            <?php foreach ($dashboardData['chart']['datasets'] as $dataset): ?>
                              <option value="<?= htmlspecialchars($dataset['label'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($dataset['label'] ?? 'Section') ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="btn-group btn-group-sm" role="group" aria-label="Type de graphique">
                            <button type="button" class="btn btn-outline-primary active" data-chart-type="line" aria-pressed="true" title="Courbe"><i class="bi bi-graph-up"></i></button>
                            <button type="button" class="btn btn-outline-primary" data-chart-type="bar" aria-pressed="false" title="Barres"><i class="bi bi-bar-chart"></i></button>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php if (!empty($dashboardData['chart']['rangeError'])): ?>
                    <div class="px-3 pt-2"><div class="alert alert-warning py-2 mb-0 small"><?= htmlspecialchars($dashboardData['chart']['rangeError']) ?></div></div>
                  <?php endif; ?>
                  <div class="card-body">
                    <?php if (!empty($dashboardData['chart']['datasets'])): ?>
                      <canvas id="dashboardChart" height="115" aria-label="Évolution des paiements par section" role="img"></canvas>
                    <?php else: ?>
                      <div class="text-center text-muted py-5">
                        <i class="bi bi-bar-chart-line fs-2 d-block mb-2"></i>
                        Aucune donnée d’encaissement disponible pour cette période.
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-xl-4">
                <div class="card accounting-panel h-100 mb-0">
                  <div class="card-header">
                    <h2 class="h6 fw-bold mb-1">Actions rapides</h2>
                    <span class="small text-muted">Accédez aux opérations courantes</span>
                  </div>
                  <div class="card-body d-grid gap-2">
                    <a class="accounting-action" href="<?= BASE_URL ?>/paiements/create">
                      <i class="bi bi-receipt"></i><span><strong class="d-block">Nouveau paiement</strong><small class="text-muted">Saisir un encaissement</small></span>
                    </a>
                    <a class="accounting-action" href="<?= BASE_URL ?>/recouvrements">
                      <i class="bi bi-arrow-repeat"></i><span><strong class="d-block">Suivi des recouvrements</strong><small class="text-muted">Consulter les soldes à recouvrer</small></span>
                    </a>
                    <a class="accounting-action" href="<?= BASE_URL ?>/comptes_eleves">
                      <i class="bi bi-journal-text"></i><span><strong class="d-block">Comptes des élèves</strong><small class="text-muted">Vérifier les mouvements</small></span>
                    </a>
                    <a class="accounting-action" href="<?= BASE_URL ?>/paiements/verifierSolvabilite">
                      <i class="bi bi-qr-code-scan"></i><span><strong class="d-block">Vérifier solvabilité</strong><small class="text-muted">Scanner un reçu et consulter les totaux de l’élève</small></span>
                    </a>
                    <a class="accounting-action" href="<?= BASE_URL ?>/frais/create">
                      <i class="bi bi-file-earmark-plus"></i><span><strong class="d-block">Créer un frais</strong><small class="text-muted">Ajouter une ligne de facturation</small></span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if (($role ?? '') === 'ecole_admin'): ?>
          <?php if (!empty($_SESSION['dashboard_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= htmlspecialchars($_SESSION['dashboard_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['dashboard_success']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['dashboard_error'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <?= htmlspecialchars($_SESSION['dashboard_error']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['dashboard_error']); ?>
          <?php endif; ?>

          <div class="card workspace-panel mt-3">
            <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
              <span class="small text-uppercase fw-bold text-primary">Contrôle des accès</span>
              <h5 class="card-title mb-1">Actions de l’administration</h5>
              <p class="text-muted small mb-0">Définissez les rôles autorisés à accéder aux modules d’inscription et de notes.</p>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0 access-control-table">
                  <thead class="table-light">
                    <tr>
                      <th>Rôle</th>
                      <?php foreach (($adminModules ?? []) as $moduleLabel): ?>
                        <th class="text-center"><?= htmlspecialchars($moduleLabel) ?></th>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach (($adminRoleList ?? []) as $roleKey => $roleLabel): ?>
                      <tr>
                        <th scope="row"><?= htmlspecialchars($roleLabel) ?></th>
                        <?php foreach (($adminModules ?? []) as $moduleKey => $moduleLabel): ?>
                          <?php $roleCanUseModule = in_array($moduleKey, $adminRoleModules[$roleKey] ?? [], true); ?>
                          <td class="text-center">
                            <?php if ($roleCanUseModule): ?>
                              <?php $allowed = $adminMatrix[$roleKey][$moduleKey] ?? true; ?>
                              <form method="post" action="<?= BASE_URL ?>/dashboard">
                                <input type="hidden" name="admin_permission" value="module">
                                <input type="hidden" name="role" value="<?= htmlspecialchars($roleKey) ?>">
                                <input type="hidden" name="module" value="<?= htmlspecialchars($moduleKey) ?>">
                                <input type="hidden" name="enabled" value="0">
                                <div class="form-check form-switch access-control-switch">
                                  <input class="form-check-input access-control-toggle" type="checkbox" name="enabled" value="1" role="switch"
                                    aria-label="<?= htmlspecialchars($moduleLabel . ' pour ' . $roleLabel) ?>"
                                    <?= $allowed ? 'checked' : '' ?> onchange="this.form.submit()">
                                </div>
                              </form>
                            <?php else: ?>
                              <span class="text-muted" title="Ce rôle n’a pas accès à ce module">—</span>
                            <?php endif; ?>
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <?php endif; ?>

          <?php if (($role ?? '') === 'comptable_école'): ?>
          <div class="accounting-dashboard">
            <div class="row g-3 mb-4">
              <div class="col-xl-7">
                <div class="card accounting-panel h-100 mb-0">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                      <h2 class="h6 fw-bold mb-1">Paiements récents</h2>
                      <span class="small text-muted">Derniers encaissements enregistrés</span>
                    </div>
                    <a href="<?= BASE_URL ?>/paiements" class="btn btn-sm btn-outline-primary">Voir les paiements</a>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle mb-0">
                        <thead>
                          <tr><th class="ps-3">Référence</th><th>Élève</th><th>Caisse</th><th>Date</th><th class="text-end pe-3">Montant</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($dashboardData['accounting']['recentPayments'] ?? [] as $p): ?>
                          <tr>
                            <td class="ps-3 fw-semibold"><?= htmlspecialchars($p['reference_recu'] ?? '—') ?></td>
                            <td><?= htmlspecialchars(\App\Core\PersonName::format($p)) ?></td>
                            <td><?= htmlspecialchars($p['nom_compte'] ?? '—') ?></td>
                            <td><?= htmlspecialchars(formatDate($p['date_operation'] ?? null)) ?></td>
                            <td class="text-end pe-3 fw-semibold"><?= number_format((float) ($p['montant'] ?? 0), 2, ',', ' ') ?></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dashboardData['accounting']['recentPayments'])): ?>
                          <tr><td colspan="5" class="text-center text-muted py-4">Aucun paiement récent.</td></tr>
                        <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xl-5">
                <div class="card accounting-panel h-100 mb-0">
                  <div class="card-header">
                    <h2 class="h6 fw-bold mb-1">Comptes à suivre</h2>
                    <span class="small text-muted">Élèves avec les soldes débiteurs les plus élevés</span>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle mb-0">
                        <thead><tr><th class="ps-3">Élève</th><th class="text-end pe-3">Solde</th></tr></thead>
                        <tbody>
                        <?php foreach ($dashboardData['accounting']['topDebtors'] ?? [] as $d): ?>
                          <tr>
                            <td class="ps-3"><?= htmlspecialchars(\App\Core\PersonName::format($d)) ?></td>
                            <td class="text-end pe-3 fw-semibold text-danger"><?= number_format((float) ($d['debt'] ?? 0), 2, ',', ' ') ?></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dashboardData['accounting']['topDebtors'])): ?>
                          <tr><td colspan="2" class="text-center text-muted py-4">Aucun solde débiteur trouvé.</td></tr>
                        <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card accounting-panel mb-4">
              <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                  <h2 class="h6 fw-bold mb-1">Soldes à recouvrer</h2>
                  <span class="small text-muted">Répartition par frais, classe, option et section</span>
                </div>
                <a href="<?= BASE_URL ?>/recouvrements" class="btn btn-sm btn-outline-primary">Ouvrir les recouvrements</a>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead>
                      <tr><th class="ps-3">Frais</th><th>Classe</th><th>Option</th><th>Section</th><th class="text-end pe-3">Solde restant</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardData['accounting']['debtBreakdown'] ?? [] as $breakdown): ?>
                      <tr>
                        <td class="ps-3"><?= htmlspecialchars($breakdown['type_frais'] ?? 'Frais scolaire') ?></td>
                        <td><?= htmlspecialchars($breakdown['nom_classe'] ?? 'Classe non définie') ?></td>
                        <td><?= htmlspecialchars($breakdown['nom_option'] ?? 'Sans option') ?></td>
                        <td><?= htmlspecialchars($breakdown['nom_section'] ?? 'Section non définie') ?></td>
                        <td class="text-end pe-3 fw-semibold text-danger">
                          <?= number_format((float) ($breakdown['dette_restante'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($breakdown['devise'] ?? '') ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dashboardData['accounting']['debtBreakdown'])): ?>
                      <tr><td colspan="5" class="text-center text-muted py-4">Aucun solde restant à recouvrer.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="card accounting-panel mb-4">
              <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                  <h2 class="h6 fw-bold mb-1">Dettes restantes par section</h2>
                  <span class="small text-muted">Comparer les soldes dans une même devise</span>
                </div>
                <?php if (!empty($dashboardData['accounting']['debtBySectionChart']['datasets'])): ?>
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <label class="visually-hidden" for="debtChartCurrency">Devise du graphique</label>
                    <select id="debtChartCurrency" class="form-select form-select-sm" aria-label="Devise du graphique" style="width: auto;">
                      <?php foreach ($dashboardData['accounting']['debtBySectionChart']['currencies'] as $currency): ?>
                        <option value="<?= htmlspecialchars($currency, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($currency) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Type de graphique des dettes">
                      <button type="button" class="btn btn-outline-primary active" data-debt-chart-type="bar" aria-pressed="true" title="Barres"><i class="bi bi-bar-chart"></i></button>
                      <button type="button" class="btn btn-outline-primary" data-debt-chart-type="doughnut" aria-pressed="false" title="Anneau"><i class="bi bi-pie-chart"></i></button>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
              <div class="card-body">
                <?php if (!empty($dashboardData['accounting']['debtBySectionChart']['datasets'])): ?>
                  <div style="position: relative; height: 290px;">
                    <canvas id="debtSectionChart" aria-label="Dettes restantes par section et devise" role="img"></canvas>
                  </div>
                <?php else: ?>
                  <div class="text-center text-muted py-5">Aucune dette à représenter.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </section>
<?php $pageScripts = <<<'SCRIPT'
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const chartCtx = document.getElementById('dashboardChart');
    if (chartCtx) {
      const chartData = __DASHBOARD_CHART_DATA__;
      const datasets = chartData.datasets && chartData.datasets.length
        ? chartData.datasets
        : [{
            label: chartData.label || 'Indicateur scolaire',
            data: chartData.values || [],
            borderColor: chartData.borderColor || '#0d6efd',
            backgroundColor: chartData.backgroundColor || 'rgba(13, 110, 253, 0.15)',
            tension: 0.3,
            fill: false,
          }];

      const chart = new Chart(chartCtx, {
        type: 'line',
        data: {
          labels: chartData.labels || [],
          datasets: datasets
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: true, position: 'bottom' },
            tooltip: { mode: 'index', intersect: false }
          },
          interaction: { mode: 'nearest', axis: 'x', intersect: false },
          scales: {
            x: { grid: { display: false } },
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
                }
              }
            }
          }
        }
      });

      const paymentSectionFilter = document.getElementById('paymentSectionFilter');
      if (paymentSectionFilter) {
        paymentSectionFilter.addEventListener('change', function () {
          chart.data.datasets.forEach(function (dataset, index) {
            chart.setDatasetVisibility(index, !this.value || dataset.label === this.value);
          }, this);
          chart.update();
        });
      }

      document.querySelectorAll('[data-chart-type]').forEach(function (button) {
        button.addEventListener('click', function () {
          chart.config.type = button.dataset.chartType;
          chart.update();

          document.querySelectorAll('[data-chart-type]').forEach(function (typeButton) {
            const isActive = typeButton === button;
            typeButton.classList.toggle('active', isActive);
            typeButton.setAttribute('aria-pressed', String(isActive));
          });
        });
      });
    }

    const debtChartCanvas = document.getElementById('debtSectionChart');
    const debtCurrencyFilter = document.getElementById('debtChartCurrency');
    if (debtChartCanvas && debtCurrencyFilter) {
      const debtChartData = __DASHBOARD_DEBT_CHART_DATA__;
      const sectionPalette = ['#0d6efd', '#0b5ed7', '#0a58ca', '#3d8bfd', '#6ea8fe', '#084298', '#052c65'];
      const currencyPalette = ['#1769aa', '#1d70b8', '#2878c7', '#367fa9', '#4a90d9', '#5b9bd5'];
      let debtChartType = 'bar';
      const selectedCurrency = debtCurrencyFilter.value;
      const debtDatasets = (debtChartData.datasets || []).map(function (dataset, index) {
        return {
          ...dataset,
          hidden: dataset.label !== selectedCurrency,
          backgroundColor: sectionPalette.slice(0, (debtChartData.labels || []).length),
          borderColor: '#ffffff',
          borderWidth: 1
        };
      });
      const debtChart = new Chart(debtChartCanvas, {
        type: debtChartType,
        data: {
          labels: debtChartData.labels || [],
          datasets: debtDatasets
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          plugins: {
            legend: { display: false, position: 'bottom' },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const amount = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(context.raw || 0);
                  return `${amount} ${context.dataset.label}`;
                }
              }
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
                }
              }
            },
            y: { grid: { display: false } }
          }
        }
      });

      const updateDebtChart = function () {
        debtChart.data.datasets.forEach(function (dataset, index) {
          const currencyIndex = debtChartData.currencies.indexOf(dataset.label);
          debtChart.setDatasetVisibility(index, dataset.label === debtCurrencyFilter.value);
          dataset.backgroundColor = debtChartType === 'doughnut'
            ? sectionPalette.slice(0, debtChartData.labels.length)
            : currencyPalette[currencyIndex % currencyPalette.length];
          dataset.borderColor = debtChartType === 'doughnut' ? '#ffffff' : dataset.backgroundColor;
        });
        debtChart.config.type = debtChartType;
        debtChart.options.indexAxis = debtChartType === 'bar' ? 'y' : 'x';
        debtChart.options.plugins.legend.display = debtChartType === 'doughnut';
        debtChart.update();
      };

      debtCurrencyFilter.addEventListener('change', updateDebtChart);
      document.querySelectorAll('[data-debt-chart-type]').forEach(function (button) {
        button.addEventListener('click', function () {
          debtChartType = button.dataset.debtChartType;
          document.querySelectorAll('[data-debt-chart-type]').forEach(function (typeButton) {
            const isActive = typeButton === button;
            typeButton.classList.toggle('active', isActive);
            typeButton.setAttribute('aria-pressed', String(isActive));
          });
          updateDebtChart();
        });
      });
      updateDebtChart();
    }

    const searchToggle = document.getElementById('navbarSearchToggle');
    const searchForm = document.getElementById('navbarSearchForm');
    const searchClose = document.getElementById('navbarSearchClose');

    if (searchToggle && searchForm) {
      searchToggle.addEventListener('click', function (event) {
        event.preventDefault();
        searchForm.classList.toggle('d-none');
        if (!searchForm.classList.contains('d-none')) {
          searchForm.querySelector('input[type="search"]')?.focus();
        }
      });
    }

    if (searchClose && searchForm) {
      searchClose.addEventListener('click', function () {
        searchForm.classList.add('d-none');
      });
    }
  });
</script>
SCRIPT;
$pageScripts = str_replace(
  ['__DASHBOARD_CHART_DATA__', '__DASHBOARD_DEBT_CHART_DATA__'],
  [
    json_encode($dashboardData['chart'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
    json_encode($dashboardData['accounting']['debtBySectionChart'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
  ],
  $pageScripts
);
?>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
