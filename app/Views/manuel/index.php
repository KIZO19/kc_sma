<?php
$escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$guides = $guides ?? [];
$moduleCount = count($modules ?? []);
?>
<?php
$pageStyles = <<<'STYLE'
<style>
  .user-manual { max-width: 1120px; margin: 0 auto; color: #172033; }
  .manual-toolbar { display: flex; justify-content: flex-end; gap: .75rem; margin-bottom: 1rem; }
  .manual-cover { padding: 2rem; color: #fff; border-radius: 1.25rem; background: linear-gradient(125deg,#123b64,#157a72); }
  .manual-cover-heading { display: flex; align-items: center; gap: 1rem; margin-bottom: 3rem; }
  .manual-cover-logo { width: 4.5rem; height: 4.5rem; object-fit: contain; padding: .35rem; border-radius: .6rem; background: #fff; }
  .manual-cover-school { font-size: 1.05rem; font-weight: 700; }
  .manual-cover h1 { font-size: 2.2rem; font-weight: 800; margin: 0 0 .4rem; }
  .manual-cover p { max-width: 44rem; margin: 0; color: #e2f4f3; }
  .manual-meta { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1.25rem; }
  .manual-meta span { padding: .35rem .7rem; border: 1px solid rgba(255,255,255,.32); border-radius: 99px; font-size: .85rem; }
  .manual-cover-document { margin-top: 2.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,.35); font-size: .85rem; }
  .manual-section { margin-top: 1.5rem; padding: 1.4rem; border: 1px solid #dfe6ee; border-radius: 1rem; background: #fff; }
  .manual-section h2 { margin: 0 0 .75rem; font-size: 1.25rem; font-weight: 750; }
  .manual-section-intro { color: #5b6678; margin-bottom: 1rem; }
  .manual-screen { display: grid; grid-template-columns: 150px 1fr; min-height: 180px; overflow: hidden; border: 1px solid #cbd5e1; border-radius: .75rem; background: #f7f9fc; }
  .manual-screen-sidebar { padding: .8rem; color: #dce8f8; background: #193752; font-size: .72rem; }
  .manual-screen-brand { margin-bottom: .8rem; font-size: .83rem; font-weight: 700; }
  .manual-screen-link { padding: .35rem .45rem; margin: .2rem 0; border-radius: .3rem; }
  .manual-screen-link.active { color: #fff; background: #237b79; }
  .manual-screen-main { padding: .9rem; }
  .manual-screen-top { display: flex; align-items: center; justify-content: space-between; padding-bottom: .6rem; border-bottom: 1px solid #dbe3ec; font-size: .75rem; }
  .manual-screen-title { margin: .8rem 0 .65rem; font-weight: 750; font-size: .9rem; }
  .manual-screen-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
  .manual-screen-card { min-height: 52px; padding: .55rem; border: 1px solid #dbe3ec; border-radius: .45rem; background: #fff; font-size: .68rem; }
  .manual-screen-card strong { display: block; margin-bottom: .3rem; color: #176b68; font-size: .8rem; }
  .manual-screen-table { display: grid; grid-template-columns: 1.2fr 1fr .8fr; gap: .25rem; margin-top: .6rem; font-size: .62rem; }
  .manual-screen-table span { padding: .35rem; background: #fff; border: 1px solid #e1e7ef; }
  .manual-caption { margin-top: .45rem; color: #64748b; font-size: .75rem; }
  .manual-workflow { display: grid; grid-template-columns: repeat(3, 1fr); gap: .7rem; }
  .manual-step { padding: .85rem; border: 1px solid #dfe6ee; border-radius: .7rem; background: #f8fafc; }
  .manual-step b { display: block; margin-bottom: .35rem; color: #176b68; }
  .manual-module { padding: 1rem; border: 1px solid #e1e7ef; border-radius: .75rem; margin-top: .65rem; break-inside: avoid; }
  .manual-module h3 { margin: 0; font-size: 1rem; font-weight: 750; }
  .manual-module p { margin: .35rem 0; color: #526074; }
  .manual-route { display: block; padding: .4rem .55rem; overflow-wrap: anywhere; border-radius: .35rem; background: #f0f4f8; color: #174e71; font-family: Consolas, monospace; font-size: .75rem; }
  .manual-tip { padding: .8rem 1rem; border-left: 4px solid #16877e; background: #eef9f7; border-radius: .4rem; }
  .manual-access { padding: 1rem; border: 1px solid #b8dcd7; border-radius: .7rem; background: #f0faf8; }
  .manual-access strong { display: block; margin-bottom: .3rem; color: #135c57; }
  .manual-list { margin: .3rem 0 0; padding-left: 1.1rem; color: #526074; font-size: .9rem; }
  @media (max-width: 700px) {
    .manual-screen { grid-template-columns: 105px 1fr; }
    .manual-workflow { grid-template-columns: 1fr; }
    .manual-screen-cards { grid-template-columns: 1fr; }
  }
  @media print {
    @page {
      size: A4;
      margin: 20mm 17mm 18mm;
      @top-left {
        content: "KC_SMA | MANUEL D’UTILISATION";
        color: #435267;
        font: 8pt Arial, sans-serif;
      }
      @top-right {
        content: "DIFFUSION INTERNE";
        color: #435267;
        font: 8pt Arial, sans-serif;
      }
      @bottom-left {
        content: "Réf. KC-SMA/MAN-UTILISATEUR";
        color: #617086;
        font: 8pt Arial, sans-serif;
      }
      @bottom-right {
        content: "Page " counter(page) " / " counter(pages);
        color: #435267;
        font: 8pt Arial, sans-serif;
      }
    }
    @page :first {
      size: A4;
      margin: 0;
      @top-left { content: none; }
      @top-right { content: none; }
      @bottom-left { content: none; }
      @bottom-right { content: none; }
    }
    html, body { margin: 0 !important; background: #fff !important; color: #172033 !important; font-size: 10pt; }
    .app-header, .app-sidebar, #sidebarBackdrop, .main-footer, .manual-toolbar,
    .content-header, .control-sidebar, #liveNotificationToasts, .floating-buttons,
    .ai-chat-widget { display: none !important; }
    .app-wrapper, .content-wrapper, .user-manual { display: block !important; width: auto !important; max-width: none !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; }
    .content-wrapper { margin-left: 0 !important; }
    .content { padding: 0 !important; }
    .manual-cover {
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      justify-content: center;
      width: 210mm;
      height: 297mm;
      min-height: 297mm;
      padding: 18mm;
      break-after: page;
      page-break-after: always;
      print-color-adjust: exact;
      -webkit-print-color-adjust: exact;
      border-radius: 0;
    }
    .manual-section, .manual-module, .manual-screen, .manual-tip, .manual-step, .manual-access {
      print-color-adjust: exact;
      -webkit-print-color-adjust: exact;
    }
    .manual-section { padding: 1rem; }
    .manual-section h2, .manual-module h3 { break-after: avoid; }
    .manual-section { orphans: 3; widows: 3; }
    .manual-module { break-inside: avoid-page; page-break-inside: avoid; }
    .manual-screen, .manual-workflow, .manual-access { break-inside: avoid-page; }
    a { color: inherit; text-decoration: none; }
    .manual-route { font-size: 8pt; }
    .manual-cover-heading { margin-bottom: 25mm; }
    .manual-cover-logo { width: 25mm; height: 25mm; }
    .manual-cover h1 { font-size: 30pt; }
    .manual-cover-document { margin-top: 30mm; }
  }
</style>
STYLE;
require __DIR__ . '/../partials/app_header.php';
?>
<main class="content user-manual">
  <div class="manual-toolbar">
    <a class="btn btn-outline-secondary" href="<?= $escape($routeBaseUrl) ?>/dashboard">Retour à l’accueil</a>
    <button class="btn btn-success" type="button" onclick="window.print()">
      <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>Générer le PDF
    </button>
  </div>

  <section class="manual-cover">
    <div class="manual-cover-heading">
      <img class="manual-cover-logo" src="<?= BASE_URL ?>/assets/kc-logo.svg" alt="Logo KC_SMA">
      <div>
        <div class="small text-uppercase fw-bold">Guide utilisateur</div>
        <div class="manual-cover-school"><?= $escape($schoolName) ?></div>
      </div>
    </div>
    <div class="small text-uppercase fw-bold mb-2">Plateforme de gestion scolaire KC_SMA</div>
    <h1>Manuel d’utilisation</h1>
    <p>Procédures essentielles et espaces accessibles selon le rôle de l’utilisateur.</p>
    <div class="manual-meta">
      <span><?= $escape($roleLabel) ?></span>
      <span><?= $moduleCount ?> espace(s) accessible(s)</span>
    </div>
    <div class="manual-cover-document">
      <div><strong>Référence :</strong> <?= $escape($documentReference) ?></div>
      <div><strong>Version :</strong> 1.0</div>
      <div><strong>Date d’édition :</strong> <?= $escape($generatedAt) ?></div>
      <div><strong>Diffusion :</strong> Utilisateurs autorisés de l’établissement</div>
    </div>
  </section>

  <section class="manual-section">
    <h2>Périmètre de votre accès</h2>
    <div class="manual-access">
      <strong>Profil : <?= $escape($roleLabel) ?></strong>
      <?= $escape($roleAccessSummary) ?>
    </div>
    <p class="manual-section-intro mt-3 mb-0">
      Ce guide n’affiche que les espaces associés à votre profil et à vos autorisations actives.
      Les boutons proposés dans un espace peuvent varier selon le dossier, la section et l’année scolaire.
      Si un espace attendu n’apparaît pas, contactez l’administrateur de l’établissement.
    </p>
  </section>

  <section class="manual-section">
    <h2>Se connecter et utiliser son compte</h2>
    <ol class="manual-list">
      <li>Sur la page de connexion, choisissez le portail correspondant à votre profil : Parent, Élève ou Personnel.</li>
      <li>Saisissez l’identifiant demandé : téléphone enregistré pour un parent, matricule pour un élève, courriel ou identifiant pour le personnel. Entrez votre mot de passe, puis cliquez sur « Connexion ».</li>
      <li>Si votre compte vient d’être créé, il peut être en attente d’activation. Contactez l’administration si l’accès est refusé ou si le compte n’est pas encore rattaché à une école.</li>
      <li>Un mot de passe temporaire doit être remplacé lorsqu’un changement obligatoire est demandé. Choisissez-en un nouveau et confirmez-le avant de continuer.</li>
      <li>En cas d’oubli, cliquez sur « Mot de passe oublié ? », indiquez le courriel ou le téléphone associé au compte, puis suivez le lien reçu par courriel. Si aucun message n’arrive, vérifiez l’adresse de récupération auprès de l’administration.</li>
      <li>Pour quitter la plateforme, ouvrez le menu de votre compte et choisissez « Se déconnecter », surtout sur un appareil partagé.</li>
    </ol>
  </section>

  <section class="manual-section">
    <h2>Créer un compte ou faire enregistrer une personne</h2>
    <p class="manual-section-intro">
      « Créer un compte » concerne l’accès à la plateforme ; « Inscription » dans le menu concerne un dossier scolaire.
      Choisissez le bon parcours pour ne pas créer un doublon.
    </p>
    <ol class="manual-list">
      <li>Si le bouton « Créer un compte » est proposé, renseignez votre nom, le rôle autorisé, l’école, l’identifiant et le mot de passe, puis confirmez le mot de passe.</li>
      <li>Après l’envoi, attendez la validation de l’administration. La création du compte ne donne pas nécessairement un accès immédiat.</li>
      <li>Pour un élève ou un parent déjà enregistré par l’école, utilisez le matricule ou le téléphone rattaché au dossier. Si l’identifiant ne fonctionne pas, demandez à l’administration de vérifier le rattachement au lieu de créer un second compte.</li>
      <li>Pour enregistrer un nouvel élève dans l’école, ouvrez l’espace « Inscriptions » si votre rôle y donne accès et suivez les étapes du dossier scolaire.</li>
    </ol>
  </section>

  <section class="manual-section">
    <h2>Consulter, enregistrer et corriger une opération</h2>
    <div class="manual-workflow">
      <div class="manual-step">
        <b>Consulter</b>
        Ouvrez l’espace autorisé, recherchez la personne ou le dossier, puis ouvrez la ligne correspondante pour lire son détail.
      </div>
      <div class="manual-step">
        <b>Créer ou enregistrer</b>
        Cliquez sur « Nouveau », « Ajouter » ou le bouton équivalent. Remplissez les champs obligatoires, vérifiez les données, puis cliquez une seule fois sur « Enregistrer » ou « Valider ».
      </div>
      <div class="manual-step">
        <b>Modifier et vérifier</b>
        Ouvrez le dossier, choisissez « Modifier » si l’action est disponible, corrigez les champs puis enregistrez. Lisez le message de confirmation et vérifiez le résultat dans la liste.
      </div>
    </div>
    <p class="manual-section-intro mt-3 mb-0">
      Si un message d’erreur apparaît, corrigez les champs signalés et réessayez. Ne répétez pas une opération financière ou scolaire tant que vous n’avez pas vérifié si elle a déjà été enregistrée.
      Une action non affichée n’est pas autorisée pour votre profil ou pour ce dossier ; ne contournez pas cette restriction.
    </p>
  </section>

  <section class="manual-section">
    <h2>Gérer votre profil</h2>
    <ol class="manual-list">
      <li>Ouvrez « Mon profil » en cliquant sur votre nom ou votre photo, puis vérifiez que le rôle affiché correspond à votre compte.</li>
      <li>Modifiez votre nom ou votre identifiant lorsque le champ est modifiable. Le matricule d’un élève est en lecture seule.</li>
      <li>Pour changer la photo, choisissez un fichier PNG ou JPEG de 250 Ko maximum.</li>
      <li>Pour changer le mot de passe depuis le profil, renseignez le nouveau mot de passe et sa confirmation. Laissez ces deux champs vides pour conserver le mot de passe actuel.</li>
      <li>Cliquez sur « Enregistrer ». Attendez le message de confirmation ; si une erreur est affichée, corrigez-la avant de réessayer.</li>
    </ol>
  </section>

  <section class="manual-section">
    <h2>Comment naviguer dans la plateforme</h2>
    <p class="manual-section-intro">Ouvrez un espace depuis le menu latéral. L’illustration ci-dessous montre les repères habituels ; son contenu varie selon votre profil.</p>
    <div class="manual-screen" role="img" aria-label="Illustration schématique du tableau de bord et de son menu">
      <div class="manual-screen-sidebar">
        <div class="manual-screen-brand">KC_SMA</div>
        <div class="manual-screen-link">Tableau de bord</div>
        <div class="manual-screen-link active"><?= $escape($modules[0]['name'] ?? 'Mes espaces') ?></div>
        <div class="manual-screen-link">Autres modules…</div>
        <div class="manual-screen-link">Profil</div>
      </div>
      <div class="manual-screen-main">
        <div class="manual-screen-top"><span><?= $escape($schoolName) ?></span><span><?= $escape($roleLabel) ?>　◉　🔔</span></div>
        <div class="manual-screen-title">Tableau de bord</div>
        <div class="manual-screen-cards">
          <div class="manual-screen-card"><strong>01</strong>Indicateurs utiles</div>
          <div class="manual-screen-card"><strong>02</strong>Accès à vos modules</div>
          <div class="manual-screen-card"><strong>03</strong>Notifications récentes</div>
        </div>
      </div>
    </div>
    <div class="manual-caption">Illustration schématique, sans données réelles. Les menus et indicateurs varient selon le rôle et les autorisations de l’école.</div>
    <div class="manual-workflow mt-3">
      <div class="manual-step"><b>1. Ouvrir un espace</b>Choisissez son nom dans le menu latéral ou dans la liste des modules du présent guide.</div>
      <div class="manual-step"><b>2. Suivre l’écran</b>Les champs obligatoires portent généralement un astérisque. Vérifiez les informations avant d’enregistrer.</div>
      <div class="manual-step"><b>3. Vérifier le résultat</b>Après une action, lisez la confirmation affichée et contrôlez l’enregistrement dans la liste ou la fiche concernée.</div>
    </div>
  </section>

  <section class="manual-section">
    <h2>Vos fonctionnalités et leurs procédures</h2>
    <p class="manual-section-intro">Choisissez l’espace dans le menu, recherchez le dossier concerné, vérifiez les informations, puis utilisez l’action affichée. Ne confirmez pas une opération si un renseignement est incertain.</p>
    <?php foreach (($modules ?? []) as $module): ?>
      <?php
        $path = (string) ($module['path'] ?? '/dashboard');
        $guide = $guides[$path] ?? [
            'purpose' => 'Accéder aux informations et actions prévues pour cet espace.',
            'steps' => 'Ouvrez le module, choisissez l’élément concerné, suivez les consignes affichées puis vérifiez le résultat.',
        ];
        $guideSteps = is_array($guide['steps'] ?? null)
            ? $guide['steps']
            : [(string) ($guide['steps'] ?? '')];
      ?>
      <article class="manual-module">
        <h3><?= $escape($module['name'] ?? 'Module') ?></h3>
        <p><?= $escape($guide['purpose']) ?></p>
        <span class="manual-route"><?= $escape($routeBaseUrl . $path) ?></span>
        <ol class="manual-list">
          <?php foreach ($guideSteps as $step): ?>
            <li><?= $escape($step) ?></li>
          <?php endforeach; ?>
        </ol>
      </article>
    <?php endforeach; ?>
  </section>

  <section class="manual-section">
    <h2>Bonnes pratiques et aide</h2>
    <ul class="manual-list">
      <li>Utilisez uniquement votre propre compte et ne partagez jamais votre mot de passe.</li>
      <li>Vérifiez l’élève, la classe, l’année et les montants avant de confirmer une opération.</li>
      <li>Les menus et données peuvent différer selon l’école, la section, l’année active et les autorisations.</li>
      <li>En cas de doute, n’enregistrez pas une action financière ou administrative avant d’avoir vérifié le dossier.</li>
      <li>Pour enregistrer ce manuel en PDF, cliquez sur « Générer le PDF », puis choisissez « Enregistrer au format PDF » dans la fenêtre d’impression.</li>
    </ul>
  </section>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
