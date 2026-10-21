<?php
$pendingCount = count($pendingStudents);
$enrolledCount = count($enrolledStudents);
$totalStudentsCount = $pendingCount + $enrolledCount;
$pageStyles = <<<'CSS'
<style>
  .inscriptions-page .content-header h1 { font-weight: 700; letter-spacing: -.03em; }
  .inscriptions-page .registration-summary { border: 0; border-radius: .85rem; box-shadow: 0 .25rem 1rem rgba(31, 41, 55, .06); }
  .inscriptions-page .registration-summary .inner { padding: 1.15rem 1.25rem; }
  .inscriptions-page .registration-summary h3 { font-size: 1.85rem; font-weight: 700; }
  .inscriptions-page .registration-summary p { margin-bottom: 0; opacity: .9; }
  .inscriptions-page .registration-summary .icon { top: .65rem; right: 1rem; font-size: 2.5rem; opacity: .2; }
  .inscriptions-page .registration-card { border: 0; border-radius: .85rem; box-shadow: 0 .25rem 1rem rgba(31, 41, 55, .06); }
  .inscriptions-page .registration-card > .card-header { padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid rgba(0, 0, 0, .07); }
  .inscriptions-page .registration-grid-title { display: flex; align-items: center; gap: .65rem; margin: 0; font-size: 1rem; font-weight: 650; }
  .inscriptions-page .registration-grid-title i { color: #2563eb; }
  .inscriptions-page .registration-grid .table { margin-bottom: 0; }
  .inscriptions-page .registration-grid .table thead th { padding: .85rem .75rem; color: #64748b; background: #f8fafc; border-bottom-width: 1px; font-size: .73rem; font-weight: 700; letter-spacing: .045em; text-transform: uppercase; }
  .inscriptions-page .registration-grid .table tbody td { padding: .8rem .75rem; vertical-align: middle; }
  .inscriptions-page .student-name { color: #1e293b; font-weight: 600; }
  .inscriptions-page .student-reference { color: #64748b; font-size: .82rem; }
  .inscriptions-page .registration-actions { display: flex; align-items: center; gap: .4rem; white-space: nowrap; }
  .inscriptions-page .registration-actions form { margin: 0; }
  .inscriptions-page .registration-grid .app-datagrid-toolbar { border: 0; border-bottom: 1px solid rgba(0, 0, 0, .07); }
  .inscriptions-page .registration-export { white-space: nowrap; }
  [data-theme="dark"] .inscriptions-page .registration-card > .card-header { background: var(--surface, #1e293b); border-color: rgba(255, 255, 255, .12); }
  [data-theme="dark"] .inscriptions-page .registration-grid .table thead th { color: #cbd5e1; background: #172232; }
  [data-theme="dark"] .inscriptions-page .student-name { color: #e2e8f0; }
  @media print {
    .inscriptions-page .content-header .btn, .inscriptions-page .registration-export,
    .inscriptions-page .app-datagrid-toolbar, .inscriptions-page .registration-actions { display: none !important; }
    .inscriptions-page .registration-card { box-shadow: none; }
  }
</style>
CSS;
require __DIR__ . '/../partials/app_header.php';
?>
<div class="inscriptions-page">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Inscriptions</h1>
              <p class="text-muted mb-0">Suivez les dossiers à valider et consultez les élèves déjà inscrits.</p>
            </div>
            <div class="col-sm-6 d-flex align-items-center justify-content-sm-end mt-3 mt-sm-0">
              <ol class="breadcrumb mb-0 me-sm-3">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Inscriptions</li>
              </ol>
              <?php if ($canSubmit): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectionSelectModal">
                  <i class="bi bi-plus-lg me-2"></i>Nouvelle inscription
                </button>
              <?php endif; ?>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="small-box bg-primary registration-summary">
                <div class="inner">
                  <h3><?= $totalStudentsCount ?></h3>
                  <p>Élèves suivis</p>
                </div>
                <div class="icon"><i class="bi bi-people"></i></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="small-box bg-warning registration-summary">
                <div class="inner">
                  <h3><?= $pendingCount ?></h3>
                  <p>Dossiers en attente</p>
                </div>
                <div class="icon"><i class="bi bi-hourglass-split"></i></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="small-box bg-success registration-summary">
                <div class="inner">
                  <h3><?= $enrolledCount ?></h3>
                  <p>Élèves déjà inscrits</p>
                </div>
                <div class="icon"><i class="bi bi-person-check"></i></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="container-fluid">
          <?php if (!empty($_SESSION['inscriptions_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= htmlspecialchars($_SESSION['inscriptions_success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['inscriptions_success']); ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['inscriptions_errors'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <ul class="mb-0">
                <?php foreach ($_SESSION['inscriptions_errors'] as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['inscriptions_errors']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['inscriptions_notification'])): ?>
            <?php $registrationNotification = $_SESSION['inscriptions_notification']; unset($_SESSION['inscriptions_notification']); ?>
            <?php
              $registrationStatus = $registrationNotification['status'] ?? '';
              $registrationAlertClass = $registrationStatus === 'success' ? 'alert-success' : ($registrationStatus === 'partial' ? 'alert-warning' : 'alert-danger');
              $registrationTitle = $registrationStatus === 'success'
                  ? 'Message de confirmation créé'
                  : ($registrationStatus === 'partial' ? 'Message de confirmation partiel' : 'Message au parent non créé');
            ?>
            <div class="alert <?= $registrationAlertClass ?> alert-dismissible">
              <strong><?= htmlspecialchars($registrationTitle, ENT_QUOTES, 'UTF-8') ?></strong><br>
              <?= htmlspecialchars($registrationNotification['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
              <?php if (!empty($registrationNotification['details'])): ?>
                <div class="small mt-1"><?= htmlspecialchars($registrationNotification['details'], ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-xl-12">
              <div class="card registration-card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                  <div>
                    <h3 class="registration-grid-title"><i class="bi bi-card-checklist"></i>Dossiers d’inscription</h3>
                    <small class="text-muted">Espace de gestion · <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></small>
                  </div>
                </div>
                <div class="card-body">
                  <p class="text-muted mb-4">Recherchez, triez et parcourez les dossiers. La validation finale est assurée par le secrétaire.</p>

                  <?php if (empty($pendingStudents)): ?>
                    <div class="alert alert-light border d-flex align-items-center gap-2 mb-0">
                      <i class="bi bi-check-circle text-success"></i>
                      <span>Aucun dossier en attente de validation.</span>
                    </div>
                  <?php else: ?>
                    <section class="registration-grid mb-5" aria-labelledby="pendingRegistrationsTitle">
                      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h4 class="registration-grid-title" id="pendingRegistrationsTitle">
                          <i class="bi bi-hourglass-split text-warning"></i>
                          À valider <span class="badge rounded-pill text-bg-warning"><?= $pendingCount ?></span>
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline-secondary registration-export" data-export-table="pendingRegistrationsGrid" data-export-name="inscriptions-en-attente">
                          <i class="bi bi-download me-1"></i>Exporter CSV
                        </button>
                      </div>
                      <div class="table-responsive">
                        <table id="pendingRegistrationsGrid" class="table table-hover align-middle">
                        <thead class="table-light">
                          <tr>
                            <th data-grid-sort="off" style="width: 60px;">N°</th>
                            <th>Matricule</th>
                            <th>Nom complet</th>
                            <th data-grid-sort-type="date">Date de naissance</th>
                            <th>Parent / responsable</th>
                            <th style="width: 130px;">Statut</th>
                            <?php if ($canEdit || $canApprove): ?>
                              <th class="registration-actions" data-grid-sort="off" style="width: 180px;">Actions</th>
                            <?php endif; ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($pendingStudents as $index => $student): ?>
                            <?php $studentFullName = trim(implode(' ', array_filter([
                                trim((string) ($student['nom'] ?? '')),
                                trim((string) ($student['postnom'] ?? '')),
                                trim((string) ($student['prenom'] ?? '')),
                            ], static fn ($part) => $part !== ''))); ?>
                            <tr>
                              <td class="text-muted"><?= $index + 1 ?></td>
                              <td><span class="student-reference"><?= htmlspecialchars($student['matricule'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                              <td class="student-name"><?= htmlspecialchars($studentFullName !== '' ? $studentFullName : '-', ENT_QUOTES, 'UTF-8') ?></td>
                              <td><?= htmlspecialchars(formatDate($student['date_naissance'] ?? null)) ?></td>
                              <td><?= htmlspecialchars($student['parent_nom_responsable'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                              <td><span class="badge rounded-pill text-bg-warning"><i class="bi bi-clock me-1"></i>En attente</span></td>
                              <?php if ($canEdit || $canApprove): ?>
                                <td class="registration-actions">
                                  <?php if ($canEdit): ?>
                                    <a href="<?= BASE_URL ?>/inscriptions/edit?id=<?= (int) $student['id'] ?>" class="btn btn-sm btn-outline-primary" aria-label="Modifier <?= htmlspecialchars(trim(($student['prenom'] ?? '') . ' ' . ($student['nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                                      <i class="bi bi-pencil-square me-1"></i>Modifier
                                    </a>
                                  <?php endif; ?>
                                  <?php if ($canApprove): ?>
                                    <form method="post" action="<?= BASE_URL ?>/inscriptions/approve" class="d-inline">
                                      <input type="hidden" name="eleve_id" value="<?= (int) $student['id'] ?>">
                                      <button type="submit" class="btn btn-sm btn-success" aria-label="Valider <?= htmlspecialchars(trim(($student['prenom'] ?? '') . ' ' . ($student['nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-check2 me-1"></i>Valider
                                      </button>
                                    </form>
                                  <?php endif; ?>
                                </td>
                              <?php endif; ?>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    </section>
                  <?php endif; ?>

                  <section class="registration-grid" aria-labelledby="enrolledStudentsTitle">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                      <h4 class="registration-grid-title" id="enrolledStudentsTitle">
                        <i class="bi bi-person-check text-success"></i>
                        Élèves déjà inscrits <span class="badge rounded-pill text-bg-success"><?= $enrolledCount ?></span>
                      </h4>
                      <?php if (!empty($enrolledStudents)): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary registration-export" data-export-table="enrolledStudentsGrid" data-export-name="eleves-inscrits">
                          <i class="bi bi-download me-1"></i>Exporter CSV
                        </button>
                      <?php endif; ?>
                    </div>
                    <?php if (empty($enrolledStudents)): ?>
                      <div class="alert alert-light border d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-info-circle text-muted"></i>
                        <span>Aucun élève inscrit pour le moment.</span>
                      </div>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table id="enrolledStudentsGrid" class="table table-hover align-middle">
                          <thead class="table-light">
                            <tr>
                              <th data-grid-sort="off" style="width: 60px;">N°</th>
                              <th>Matricule</th>
                              <th>Nom complet</th>
                              <th data-grid-sort-type="date">Date de naissance</th>
                              <th>Parent / responsable</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($enrolledStudents as $index => $student): ?>
                              <?php $studentFullName = trim(implode(' ', array_filter([
                                  trim((string) ($student['nom'] ?? '')),
                                  trim((string) ($student['postnom'] ?? '')),
                                  trim((string) ($student['prenom'] ?? '')),
                              ], static fn ($part) => $part !== ''))); ?>
                              <tr>
                                <td class="text-muted"><?= $index + 1 ?></td>
                                <td><span class="student-reference"><?= htmlspecialchars($student['matricule'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>
                                  <a href="<?= BASE_URL ?>/eleves/show?id=<?= (int) ($student['id'] ?? 0) ?>">
                                    <span class="student-name"><?= htmlspecialchars($studentFullName !== '' ? $studentFullName : '-', ENT_QUOTES, 'UTF-8') ?></span>
                                  </a>
                                </td>
                                <td><?= htmlspecialchars(formatDate($student['date_naissance'] ?? null)) ?></td>
                                <td><?= htmlspecialchars($student['parent_nom_responsable'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>
                  </section>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <?php if (!empty($sections)): ?>
        <div class="modal fade" id="sectionSelectModal" tabindex="-1" aria-labelledby="sectionSelectModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="sectionSelectModalLabel">Choisir une section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <p>Sélectionnez la section dans laquelle vous souhaitez créer une inscription.</p>
                <div class="list-group">
                  <?php foreach ($sections as $section): ?>
                    <?php $isSecondary = mb_strtolower($section['nom_section'], 'UTF-8') === 'secondaire'; ?>
                    <a href="<?= $isSecondary ? 'javascript:void(0)' : BASE_URL . '/inscriptions/create?section_id=' . (int) $section['id'] ?>"
                       class="list-group-item list-group-item-action<?= $isSecondary ? ' secondary-section-item' : '' ?>"
                       data-section-id="<?= (int) $section['id'] ?>"
                       data-section-name="<?= htmlspecialchars($section['nom_section'], ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($section['nom_section']) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="modal-footer">
                <a href="<?= BASE_URL ?>/inscriptions/create" class="btn btn-outline-secondary">Créer sans section</a>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Annuler</button>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($options)): ?>
        <div class="modal fade" id="optionSelectModal" tabindex="-1" aria-labelledby="optionSelectModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="optionSelectModalLabel">Choisir une option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <p>Sélectionnez l’option dans laquelle l’élève doit être inscrit en secondaire.</p>
                <div class="list-group" id="optionListGroup">
                  <?php foreach ($options as $option): ?>
                    <a href="#" class="list-group-item list-group-item-action option-choice-item" data-option-id="<?= (int) $option['id'] ?>">
                      <?= htmlspecialchars($option['nom_option']) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <script>
        document.addEventListener('DOMContentLoaded', function () {
          document.querySelectorAll('.registration-export').forEach(function (button) {
            button.addEventListener('click', function () {
              const table = document.getElementById(button.dataset.exportTable);
              if (!table) {
                return;
              }

              const rows = Array.from(table.rows).map(function (row) {
                return Array.from(row.cells).filter(function (cell) {
                  return !cell.classList.contains('registration-actions');
                }).map(function (cell) {
                  const value = cell.innerText.trim().replace(/\s+/g, ' ');
                  const safeValue = /^[=+\-@]/.test(value) ? "'" + value : value;
                  return '"' + safeValue.replace(/"/g, '""') + '"';
                }).join(';');
              });
              const csv = '\uFEFF' + rows.join('\r\n');
              const link = document.createElement('a');
              link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
              link.download = (button.dataset.exportName || 'inscriptions') + '.csv';
              link.click();
              URL.revokeObjectURL(link.href);
            });
          });

          const optionModalElement = document.getElementById('optionSelectModal');
          const optionItems = document.querySelectorAll('.option-choice-item');
          let selectedSecondarySectionId = null;

          const sectionLinks = document.querySelectorAll('.secondary-section-item');
          sectionLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
              event.preventDefault();
              selectedSecondarySectionId = this.dataset.sectionId;
              if (optionModalElement) {
                const modal = new bootstrap.Modal(optionModalElement);
                modal.show();
                return;
              }
              window.location.href = '<?= BASE_URL ?>/inscriptions/create?section_id=' + encodeURIComponent(selectedSecondarySectionId);
            });
          });

          optionItems.forEach(function (link) {
            link.addEventListener('click', function (event) {
              event.preventDefault();
              if (!selectedSecondarySectionId) {
                return;
              }
              const optionId = this.dataset.optionId;
              window.location.href = '<?= BASE_URL ?>/inscriptions/create?section_id=' + encodeURIComponent(selectedSecondarySectionId) + '&option_id=' + encodeURIComponent(optionId);
            });
          });
        });
      </script>
      </div>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
