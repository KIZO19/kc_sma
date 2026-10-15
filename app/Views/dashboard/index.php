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
</style>
CSS;
require __DIR__ . '/../partials/app_header.php';
?>
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1><?= ($role ?? '') === 'comptable_école' ? 'Espace comptable' : ((($role ?? '') === 'super_admin') ? 'Pilotage des écoles' : 'Dashboard') ?></h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Accueil</a></li>
                <li class="breadcrumb-item active"><?= ($role ?? '') === 'comptable_école' ? 'Espace comptable' : ((($role ?? '') === 'super_admin') ? 'Pilotage des écoles' : 'Dashboard') ?></li>
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
                    <a class="school-action" href="<?= BASE_URL ?>/ecoles/create">
                      <i class="bi bi-plus-circle" aria-hidden="true"></i>
                      <span><strong class="d-block">Créer une école</strong><small class="text-muted">Enregistrer un établissement</small></span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php elseif (($role ?? '') !== 'comptable_école'): ?>
          <div class="row">
            <?php foreach ($dashboardData['stats'] ?? [] as $stat): ?>
              <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="small-box <?= htmlspecialchars($stat['bg'] ?? 'bg-primary') ?> text-white">
                  <div class="inner">
                    <h3><?= htmlspecialchars($stat['value'] ?? '0') ?></h3>
                    <p><?= htmlspecialchars($stat['title'] ?? '') ?></p>
                  </div>
                  <div class="icon"><i class="bi <?= htmlspecialchars($stat['icon'] ?? 'bi-bar-chart-line') ?>"></i></div>
                  <a href="#" class="small-box-footer"><?= htmlspecialchars($stat['hint'] ?? 'Détails') ?> <i class="bi bi-arrow-right"></i></a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="card card-primary card-outline">
                <div class="card-header">
                  <h3 class="card-title"><?= htmlspecialchars($dashboardData['chart']['title'] ?? 'Performance scolaire') ?></h3>
                  <div class="card-tools">
                      <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i class="bi bi-dash"></i></button>
                  </div>
                </div>
                <div class="card-body">
                  <canvas id="dashboardChart" height="230"></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card card-outline shadow-sm h-100">
                <div class="card-header bg-info">
                  <h3 class="card-title text-white"><?= htmlspecialchars($dashboardData['overview']['title'] ?? 'Aperçu rapide') ?></h3>
                </div>
                <div class="card-body">
                  <div class="mb-4">
                    <h5 class="mb-1"><?= htmlspecialchars($dashboardData['overview']['title'] ?? 'Vue synthétique') ?></h5>
                    <p class="text-muted">Suivi des points clés qui influencent la gestion quotidienne de l’établissement.</p>
                  </div>

                  <?php foreach ($dashboardData['overview']['items'] ?? [] as $item): ?>
                    <div class="progress-group mb-3">
                      <span class="progress-text"><?= htmlspecialchars($item['label'] ?? '') ?></span>
                      <span class="float-right"><b><?= htmlspecialchars($item['value'] ?? '0') ?></b></span>
                      <div class="progress progress-sm">
                        <div class="progress-bar <?= htmlspecialchars($item['color'] ?? 'bg-primary') ?>" style="width: <?= isset($item['value']) && is_numeric(str_replace(['%', ',', ' '], '', (string) $item['value'])) ? min(100, max(10, (int) str_replace(['%', ',', ' '], '', (string) $item['value']))) : 75 ?>%"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
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

            <div class="row g-3 mb-4">
              <div class="col-xl-8">
                <div class="card accounting-panel h-100 mb-0">
                  <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                    <div>
                      <h2 class="h6 fw-bold mb-1"><?= htmlspecialchars($dashboardData['chart']['title'] ?? 'Évolution des encaissements') ?></h2>
                      <span class="small text-muted">Évolution mensuelle sur les six derniers mois</span>
                    </div>
                    <?php if (!empty($dashboardData['chart']['datasets'])): ?>
                      <div class="btn-group btn-group-sm" role="group" aria-label="Type de graphique">
                        <button type="button" class="btn btn-outline-primary active" data-chart-type="line" aria-pressed="true">
                          <i class="bi bi-graph-up me-1"></i>Courbe
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-chart-type="bar" aria-pressed="false">
                          <i class="bi bi-bar-chart me-1"></i>Barres
                        </button>
                      </div>
                    <?php endif; ?>
                  </div>
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
          <div class="row mt-3">
            <div class="col-12 mb-4">
              <div class="card border-primary shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                  <h5 class="card-title mb-0">Portail de l’école</h5>
                </div>
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <a href="<?= BASE_URL ?>/ecoles" class="btn btn-outline-primary w-100 h-100 p-3">
                        <i class="bi bi-gear fs-3 d-block mb-2"></i>
                        <strong>Paramètres de l’école</strong>
                      </a>
                    </div>
                    <div class="col-md-6">
                      <a href="<?= BASE_URL ?>/paiements/gestionAutorisations" class="btn btn-outline-success w-100 h-100 p-3">
                        <i class="bi bi-shield-check fs-3 d-block mb-2"></i>
                        <strong>Autorisations de paiement</strong>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

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

          <div class="card card-outline card-primary mt-3">
            <div class="card-header">
              <h5 class="card-title mb-1">Actions de l’administration</h5>
              <p class="text-muted small mb-0">Définissez les rôles autorisés à accéder aux modules d’inscription et de notes.</p>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
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
                                <div class="form-check form-switch d-inline-block">
                                  <input class="form-check-input" type="checkbox" name="enabled" value="1" role="switch"
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

          <div class="card mt-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <span>Dettes par frais, classe, option et section</span>
              <a href="<?= BASE_URL ?>/recouvrements" class="btn btn-sm btn-outline-primary">Voir les recouvrements</a>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Frais</th>
                      <th>Classe</th>
                      <th>Option</th>
                      <th>Section</th>
                      <th class="text-end">Dette restante</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($dashboardData['accounting']['debtBreakdown'] ?? [] as $breakdown): ?>
                    <tr>
                      <td><?= htmlspecialchars($breakdown['type_frais'] ?? 'Frais scolaire') ?></td>
                      <td><?= htmlspecialchars($breakdown['nom_classe'] ?? 'Classe non définie') ?></td>
                      <td><?= htmlspecialchars($breakdown['nom_option'] ?? 'Sans option') ?></td>
                      <td><?= htmlspecialchars($breakdown['nom_section'] ?? 'Section non définie') ?></td>
                      <td class="text-end fw-semibold text-danger">
                        <?= number_format((float) ($breakdown['dette_restante'] ?? 0), 2, ',', ' ') ?> <?= htmlspecialchars($breakdown['devise'] ?? '') ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($dashboardData['accounting']['debtBreakdown'])): ?>
                    <tr><td colspan="5" class="text-center text-muted">Aucune dette restante.</td></tr>
                  <?php endif; ?>
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
                            <td><?= htmlspecialchars(trim(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '') . ' ' . ($p['postnom'] ?? ''))) ?></td>
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
                            <td class="ps-3"><?= htmlspecialchars(trim(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '') . ' ' . ($d['postnom'] ?? ''))) ?></td>
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
          </div>
          <?php endif; ?>

          <?php if (($role ?? '') !== 'comptable_école' && ($role ?? '') !== 'super_admin'): ?>
          <div class="row">
            <?php foreach ($modules as $module): ?>
              <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card card-outline shadow-sm h-100 border-0">
                  <div class="card-body text-center">
                    <div class="mb-3">
                      <i class="bi <?= htmlspecialchars($module['icon']) ?> text-primary" style="font-size:2.2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1"><?= htmlspecialchars($module['name']) ?></h5>
                    <p class="text-muted small">Accédez à <?= htmlspecialchars($module['name']) ?>.</p>
                    <a href="<?= BASE_URL . $module['path'] ?>" class="btn btn-sm btn-primary rounded-pill">Ouvrir</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </section>
<?php $pageScripts = <<<'SCRIPT'
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const chartCtx = document.getElementById('dashboardChart');
    if (chartCtx) {
      const chartData = <?= json_encode($dashboardData['chart'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
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
?>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
