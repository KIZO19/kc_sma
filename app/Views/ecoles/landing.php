<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="KC_SMA est une solution professionnelle de gestion scolaire pour les écoles et établissements.">
  <meta name="keywords" content="gestion scolaire, école, logiciel, administration">
  <meta name="author" content="KC_SMA">
  <title><?= $title ?></title>
  <link rel="icon" href="<?= BASE_URL ?>/assets/favicon.ico" type="image/x-icon">
  <link rel="alternate icon" href="<?= BASE_URL ?>/assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" crossorigin="anonymous">
  <style>
    body { background: #f4f7fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .hero { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 3rem 1rem; }
    .hero-content { max-width: 960px; width: 100%; text-align: center; }
    .hero-title { font-size: clamp(2.5rem, 5vw, 4.5rem); font-weight: 700; margin-bottom: 1rem; color: #0d6efd; }
    .hero-text { font-size: 1.1rem; color: #4b5563; margin-bottom: 2rem; }
    .hero-buttons .btn { min-width: 180px; margin: 0.25rem; }
    .features { display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-top: 3rem; }
    .feature-card { background: #fff; border: 1px solid rgba(13,110,253,.12); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 12px 30px rgba(15,23,42,.06); }
    .feature-card h3 { margin-bottom: 0.75rem; font-size: 1.2rem; }
    .feature-card p { color: #6b7280; line-height: 1.7; }
    .footer { padding: 2rem 0 1rem; text-align: center; color: #6b7280; }
  </style>
</head>
<body>
  <main class="hero">
    <div class="hero-content">
      <p class="text-uppercase text-primary fw-bold mb-3">Plateforme scolaire</p>
      <h1 class="hero-title">KC_SMA, votre solution de gestion scolaire tout-en-un</h1>
      <p class="hero-text">Pilotez l'administration, les élèves, les paiements et les bulletins depuis une interface simple, moderne et sécurisée.</p>
      <div class="hero-buttons">
        <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-lg"><i class="bi bi-box-arrow-in-right me-2"></i>Se connecter</a>
        <a href="<?= BASE_URL ?>/register" class="btn btn-outline-primary btn-lg"><i class="bi bi-person-plus me-2"></i>S'inscrire</a>
      </div>
      <div class="features">
        <div class="feature-card">
          <h3><i class="bi bi-people-fill me-2"></i>Gestion des utilisateurs</h3>
          <p>Créez et gérez les rôles d'administration, enseignants, parents et élèves avec un accès dédié.</p>
        </div>
        <div class="feature-card">
          <h3><i class="bi bi-journal-richtext me-2"></i>Bulletins & notes</h3>
          <p>Saisissez les évaluations, publiez les bulletins et suivez la progression des élèves en temps réel.</p>
        </div>
        <div class="feature-card">
          <h3><i class="bi bi-calendar-check me-2"></i>Présences & emplois</h3>
          <p>Suivez les présences, planifiez les emplois du temps et gardez un historique clair des activités.</p>
        </div>
        <div class="feature-card">
          <h3><i class="bi bi-currency-dollar me-2"></i>Facturation & paiements</h3>
          <p>Gérez les frais scolaires, enregistrez les paiements et obtenez des rapports financiers précis.</p>
        </div>
      </div>
    </div>
  </main>
  <footer class="footer">
    <p>© <?= date('Y') ?> KC_SMA. Tous droits réservés.</p>
  </footer>
</body>
</html>
