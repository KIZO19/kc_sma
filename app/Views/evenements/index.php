<?php
require __DIR__ . '/../partials/app_header.php';
$e = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$ownEvent = static fn (array $event): bool =>
    (int) ($event['auteur_user_id'] ?? 0) === (int) ($user['id'] ?? 0);
$localDate = static fn ($value): string =>
    $value ? str_replace(' ', 'T', substr((string) $value, 0, 16)) : '';
$displayDate = static fn ($value): string =>
    $value ? date('d/m/Y à H:i', strtotime((string) $value)) : '—';
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h1>Planificateur d’événements</h1>
        <p class="text-muted mb-0">Consultez les événements de l’école et gérez ceux que vous avez planifiés.</p>
      </div>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Événements</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <?php if ($success !== ''): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>

    <?php if ($canPlan): ?>
      <div class="card card-outline card-primary">
        <div class="card-header"><h2 class="card-title">Planifier un événement</h2></div>
        <div class="card-body">
          <?php if (!$activeYear): ?>
            <div class="alert alert-warning mb-0">Aucune année scolaire active n’est configurée. Contactez l’administration de votre école.</div>
          <?php elseif ($categories === []): ?>
            <div class="alert alert-warning mb-0">Aucune catégorie d’événement n’est disponible. Contactez l’administration de votre école.</div>
          <?php else: ?>
            <form method="post" action="<?= BASE_URL ?>/evenements/store" class="row g-3">
              <input type="hidden" name="form_token" value="<?= $e($formToken) ?>">
              <div class="col-12 col-lg-6">
                <label class="form-label" for="eventTitle">Titre</label>
                <input class="form-control" id="eventTitle" name="titre" maxlength="150" required>
              </div>
              <div class="col-12 col-lg-6">
                <label class="form-label" for="eventCategory">Catégorie</label>
                <select class="form-select" id="eventCategory" name="categorie_id" required>
                  <option value="">Sélectionner une catégorie</option>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"><?= $e($category['nom_categorie']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="eventStart">Date et heure de début</label>
                <input class="form-control" type="datetime-local" id="eventStart" name="date_debut" required>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="eventEnd">Date et heure de fin</label>
                <input class="form-control" type="datetime-local" id="eventEnd" name="date_fin" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="eventLocation">Lieu</label>
                <input class="form-control" id="eventLocation" name="lieu" maxlength="150" placeholder="Ex. Salle de réunion">
              </div>
              <div class="col-12">
                <label class="form-label" for="eventDescription">Description</label>
                <textarea class="form-control" id="eventDescription" name="description" rows="3" maxlength="5000"></textarea>
              </div>
              <div class="col-12">
                <button class="btn btn-primary" type="submit"><i class="bi bi-calendar-plus me-1" aria-hidden="true"></i>Planifier l’événement</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><h2 class="card-title">Calendrier des événements</h2></div>
      <div class="card-body">
        <?php if ($events === []): ?>
          <p class="text-muted mb-0">Aucun événement planifié pour cette école.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Événement</th>
                  <th>Catégorie</th>
                  <th>Début</th>
                  <th>Fin</th>
                  <th>Lieu</th>
                  <th>Planifié par</th>
                  <?php if ($canPlan): ?><th>Actions</th><?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($events as $event): ?>
                  <tr>
                    <td>
                      <strong><?= $e($event['titre']) ?></strong>
                      <?php if (!empty($event['description'])): ?>
                        <details class="small mt-1">
                          <summary>Voir la description</summary>
                          <p class="mb-0 mt-1"><?= nl2br($e($event['description'])) ?></p>
                        </details>
                      <?php endif; ?>
                    </td>
                    <td><?= $e($event['nom_categorie'] ?? '—') ?></td>
                    <td><?= $e($displayDate($event['date_debut'] ?? null)) ?></td>
                    <td><?= $e($displayDate($event['date_fin'] ?? null)) ?></td>
                    <td><?= $e($event['lieu'] ?: '—') ?></td>
                    <td><?= $e($event['auteur_nom'] ?? 'Événement existant') ?></td>
                    <?php if ($canPlan): ?>
                      <td>
                        <?php if ($ownEvent($event)): ?>
                          <details>
                            <summary class="btn btn-sm btn-outline-primary">Modifier</summary>
                            <form method="post" action="<?= BASE_URL ?>/evenements/update" class="border rounded p-3 mt-2" style="min-width: 280px;">
                              <input type="hidden" name="form_token" value="<?= $e($formToken) ?>">
                              <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                              <div class="mb-2">
                                <label class="form-label" for="title-<?= (int) $event['id'] ?>">Titre</label>
                                <input class="form-control form-control-sm" id="title-<?= (int) $event['id'] ?>" name="titre" maxlength="150" value="<?= $e($event['titre']) ?>" required>
                              </div>
                              <div class="mb-2">
                                <label class="form-label" for="category-<?= (int) $event['id'] ?>">Catégorie</label>
                                <select class="form-select form-select-sm" id="category-<?= (int) $event['id'] ?>" name="categorie_id" required>
                                  <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= (int) $category['id'] === (int) $event['categorie_id'] ? 'selected' : '' ?>><?= $e($category['nom_categorie']) ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <div class="mb-2">
                                <label class="form-label" for="start-<?= (int) $event['id'] ?>">Début</label>
                                <input class="form-control form-control-sm" type="datetime-local" id="start-<?= (int) $event['id'] ?>" name="date_debut" value="<?= $e($localDate($event['date_debut'])) ?>" required>
                              </div>
                              <div class="mb-2">
                                <label class="form-label" for="end-<?= (int) $event['id'] ?>">Fin</label>
                                <input class="form-control form-control-sm" type="datetime-local" id="end-<?= (int) $event['id'] ?>" name="date_fin" value="<?= $e($localDate($event['date_fin'])) ?>" required>
                              </div>
                              <div class="mb-2">
                                <label class="form-label" for="location-<?= (int) $event['id'] ?>">Lieu</label>
                                <input class="form-control form-control-sm" id="location-<?= (int) $event['id'] ?>" name="lieu" maxlength="150" value="<?= $e($event['lieu']) ?>">
                              </div>
                              <div class="mb-2">
                                <label class="form-label" for="description-<?= (int) $event['id'] ?>">Description</label>
                                <textarea class="form-control form-control-sm" id="description-<?= (int) $event['id'] ?>" name="description" rows="3" maxlength="5000"><?= $e($event['description']) ?></textarea>
                              </div>
                              <button class="btn btn-sm btn-primary" type="submit">Enregistrer</button>
                            </form>
                            <form method="post" action="<?= BASE_URL ?>/evenements/delete" class="mt-2" onsubmit="return confirm('Supprimer cet événement ?');">
                              <input type="hidden" name="form_token" value="<?= $e($formToken) ?>">
                              <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                              <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                            </form>
                          </details>
                        <?php else: ?>
                          <span class="text-muted">Lecture seule</span>
                        <?php endif; ?>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
