<?php
$e = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$dateTime = static function ($value): string {
    if (empty($value)) {
        return '—';
    }

    $date = new \DateTimeImmutable((string) $value);
    return $date->format('d/m/Y à H:i');
};
$incidentDate = !empty($audition['date_evenement'])
    ? (new \DateTimeImmutable((string) $audition['date_evenement']))->format('d/m/Y')
    : '—';
$studentName = \App\Core\PersonName::format($audition);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Procès-verbal d’audition — <?= $e($studentName) ?></title>
  <style>
    body { margin: 0; padding: 2rem; color: #172033; font: 14px/1.55 Arial, sans-serif; }
    main { max-width: 850px; margin: 0 auto; }
    h1 { margin-bottom: .25rem; text-align: center; font-size: 1.45rem; }
    h2 { margin: 1.5rem 0 .4rem; border-bottom: 1px solid #ccd3df; padding-bottom: .25rem; font-size: 1rem; }
    .muted { color: #5a6679; }
    .facts { white-space: pre-wrap; overflow-wrap: anywhere; }
    .identity { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .4rem 1rem; margin-top: 1.25rem; }
    .signature-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; margin-top: 3rem; }
    .signature { min-height: 5rem; border-top: 1px solid #8a94a5; padding-top: .5rem; }
    .toolbar { max-width: 850px; margin: 0 auto 1rem; display: flex; justify-content: space-between; }
    button, a { border: 1px solid #8794a8; border-radius: .35rem; padding: .5rem .75rem; background: white; color: #172033; text-decoration: none; font: inherit; cursor: pointer; }
    @media print {
      body { padding: 0; }
      .toolbar { display: none; }
      @page { margin: 18mm; }
    }
    @media (max-width: 600px) {
      body { padding: 1rem; }
      .identity, .signature-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <nav class="toolbar">
    <a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) ($audition['student_id'] ?? 0) ?>#discipline">Retour au dossier</a>
    <button type="button" onclick="window.print()">Imprimer / enregistrer en PDF</button>
  </nav>
  <main>
    <header>
      <p class="muted" style="text-align:center"><?= $e($audition['school_name'] ?? 'Établissement scolaire') ?></p>
      <h1>PROCÈS-VERBAL D’AUDITION DISCIPLINAIRE</h1>
      <p class="muted" style="text-align:center">Document scolaire confidentiel</p>
    </header>

    <section>
      <h2>Identification</h2>
      <div class="identity">
        <div><strong>Élève :</strong> <?= $e($studentName) ?></div>
        <div><strong>Matricule :</strong> <?= $e($audition['matricule'] ?? '—') ?></div>
        <div><strong>Classe :</strong> <?= $e($audition['nom_classe'] ?? '—') ?><?= !empty($audition['nom_option']) ? ' — ' . $e($audition['nom_option']) : '' ?></div>
        <div><strong>Incident du :</strong> <?= $e($incidentDate) ?></div>
        <div><strong>Période :</strong> <?= $e(($audition['annee'] ?? '') . ' — ' . ($audition['nom_periode'] ?? '')) ?></div>
        <div><strong>Audition tenue le :</strong> <?= $e($dateTime($audition['hearing_at'] ?? null)) ?></div>
        <div><strong>Lieu de l’audition :</strong> <?= $e($audition['location'] ?? '—') ?></div>
        <div><strong>Directeur de discipline :</strong> <?= $e($audition['author_name'] ?? '—') ?></div>
      </div>
    </section>

    <section>
      <h2>Incident consigné</h2>
      <p class="facts"><?= $e($audition['faute'] ?? '—') ?></p>
      <p><strong>Sanction initialement consignée :</strong> <?= $e($audition['sanction'] ?? 'Aucune') ?></p>
      <p><strong>Retrait de points :</strong> <?= (int) ($audition['retrait_points'] ?? 0) ?></p>
    </section>

    <section>
      <h2>Faits examinés lors de l’audition</h2>
      <p class="facts"><?= $e($audition['alleged_facts'] ?? '') ?></p>
    </section>
    <section>
      <h2>Déclaration de l’élève</h2>
      <p class="facts"><?= $e($audition['student_statement'] ?? '') ?></p>
    </section>
    <?php if (!empty($audition['observations'])): ?>
      <section>
        <h2>Observations complémentaires</h2>
        <p class="facts"><?= $e($audition['observations']) ?></p>
      </section>
    <?php endif; ?>
    <?php if (!empty($audition['decision'])): ?>
      <section>
        <h2>Conclusion ou mesure proposée</h2>
        <p class="facts"><?= $e($audition['decision']) ?></p>
      </section>
    <?php endif; ?>

    <section>
      <h2>Convocation du parent</h2>
      <p><strong>Rendez-vous :</strong> <?= $e($dateTime($audition['parent_meeting_at'] ?? null)) ?></p>
      <p><strong>Lieu :</strong> <?= $e($audition['parent_meeting_location'] ?? '—') ?></p>
      <?php if (!empty($audition['parent_notice'])): ?>
        <p class="facts"><strong>Message complémentaire :</strong><br><?= $e($audition['parent_notice']) ?></p>
      <?php endif; ?>
      <p class="muted">
        Email : <?= $e($audition['email_status'] ?? '—') ?> ·
        SMS : <?= $e($audition['sms_status'] ?? '—') ?> ·
        Transmission aux autorités : <?= $e($audition['report_status'] ?? '—') ?>
      </p>
    </section>

    <section class="signature-row">
      <div class="signature">Signature du directeur de discipline<br><?= $e($audition['author_name'] ?? '') ?></div>
      <div class="signature">Signature de l’élève</div>
      <div class="signature">Signature du parent / tuteur</div>
      <div class="signature">Date et observations de réception</div>
    </section>
  </main>
</body>
</html>
