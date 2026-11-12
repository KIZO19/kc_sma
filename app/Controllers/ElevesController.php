<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\AnneeScolaire;
use App\Models\DisciplineAudition;
use App\Models\DetteEleve;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\UserMessage;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\PaymentParentNotifier;
use App\Services\SchoolEmailNotifier;

class ElevesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $isTeacher = $role === 'enseignant_école';
        $classes = $isTeacher
            ? TeacherAssignment::getClasses($ecoleId, (int) ($user['reference_id'] ?? 0))
            : ($ecoleId > 0 ? Classe::getAllBySchool($ecoleId, $sectionScopeId) : []);
        $selectedClasseId = (int) ($_GET['classe_id'] ?? 0);
        $validClasseIds = array_map(static fn (array $classe): int => (int) $classe['id'], $classes);
        if ($selectedClasseId > 0 && !in_array($selectedClasseId, $validClasseIds, true)) {
            $selectedClasseId = 0;
        }
        $students = $isTeacher
            ? TeacherAssignment::getStudents($ecoleId, (int) ($user['reference_id'] ?? 0), $selectedClasseId > 0 ? $selectedClasseId : null)
            : ($ecoleId > 0
                ? Eleve::getAllBySchool($ecoleId, null, $sectionScopeId)
                : ($role === 'super_admin' ? Eleve::getAll() : []));

        $this->view('eleves/index', [
            'title' => APP_NAME . ' - Élèves',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'students' => $students,
            'classes' => $classes,
            'selectedClasseId' => $selectedClasseId,
            'isTeacher' => $isTeacher,
        ]);
    }

    public function show(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $sectionScopeId = $this->getSectionScopeForUser($user);
        $eleveId = (int) ($_GET['id'] ?? 0);

        if ($eleveId <= 0) {
            header('Location: ' . BASE_URL . '/eleves');
            exit;
        }

        $isTeacher = ($user['role'] ?? '') === 'enseignant_école';
        if ($isTeacher && !TeacherAssignment::teacherCanAccessStudent($ecoleId, (int) ($user['reference_id'] ?? 0), $eleveId)) {
            header('Location: ' . BASE_URL . '/error/accessDenied');
            exit;
        }

        if ($isTeacher) {
            $eleve = Eleve::findByIdAndSchool($eleveId, $ecoleId);
            if (!$eleve) {
                header('Location: ' . BASE_URL . '/error/notFound');
                exit;
            }
            $teacherStudents = TeacherAssignment::getStudents($ecoleId, (int) ($user['reference_id'] ?? 0));
            $teacherClass = null;
            foreach ($teacherStudents as $teacherStudent) {
                if ((int) $teacherStudent['id'] === $eleveId) {
                    $teacherClass = (string) ($teacherStudent['nom_classe'] ?? '')
                        . (!empty($teacherStudent['nom_option']) ? ' · ' . $teacherStudent['nom_option'] : '');
                    break;
                }
            }

            $this->view('eleves/discipline', [
                'title' => APP_NAME . ' - Fiche de discipline',
                'user' => $user,
                'role' => $user['role'],
                'roleLabel' => User::getRoleLabel($user['role']),
                'modules' => $this->getModulesForRole($user['role']),
                'eleve' => $eleve,
                'currentClass' => $teacherClass,
                'discipline' => Eleve::getDiscipline($eleveId),
            ]);
            return;
        }

        // Ensure the eleve belongs to the same school unless super_admin
        if (
            ($user['role'] ?? '') !== 'super_admin'
            && ($ecoleId <= 0 || !Eleve::findByIdAndSchool($eleveId, $ecoleId, $sectionScopeId))
        ) {
            header('Location: ' . BASE_URL . '/error/accessDenied');
            exit;
        }

        $eleve = \App\Models\Eleve::findById($eleveId);
        if (!$eleve) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        $studentSchoolId = $ecoleId > 0 ? $ecoleId : (int) ($eleve['ecole_id'] ?? 0);
        $enrollmentHistory = Eleve::getEnrollmentHistoryForSchool($eleveId, $studentSchoolId, $sectionScopeId);
        $activeYear = $studentSchoolId > 0 ? AnneeScolaire::getActiveBySchool($studentSchoolId) : null;
        $requestedYearId = (int) ($_GET['annee_scolaire_id'] ?? 0);
        $selectedEnrollment = null;
        foreach ($enrollmentHistory as $enrollment) {
            if ((int) $enrollment['annee_scolaire_id'] === $requestedYearId) {
                $selectedEnrollment = $enrollment;
                break;
            }
        }
        if ($selectedEnrollment === null && $activeYear) {
            foreach ($enrollmentHistory as $enrollment) {
                if ((int) $enrollment['annee_scolaire_id'] === (int) $activeYear['id']) {
                    $selectedEnrollment = $enrollment;
                    break;
                }
            }
        }
        if ($selectedEnrollment === null && !empty($enrollmentHistory)) {
            $selectedEnrollment = $enrollmentHistory[0];
        }

        $selectedYearId = (int) ($selectedEnrollment['annee_scolaire_id'] ?? 0);
        $currentClass = $selectedEnrollment['classe_label'] ?? null;
        $ecritures = $selectedYearId > 0
            ? Eleve::getAccountingEntries($eleveId, $selectedYearId)
            : [];
        $feeStatuses = $selectedYearId > 0
            ? DetteEleve::getFeeStatusForStudentYear($eleveId, $studentSchoolId, $selectedYearId)
            : [];
        $totalDebtByCurrency = [];
        foreach ($feeStatuses as &$feeStatus) {
            $initialAmount = (float) ($feeStatus['montant_initial'] ?? 0);
            $paidAmount = (float) ($feeStatus['montant_paye'] ?? 0);
            $exoneratedAmount = (float) ($feeStatus['montant_exonere'] ?? 0);
            $remainingAmount = max(0.0, $initialAmount - $paidAmount - $exoneratedAmount);
            if ($remainingAmount <= 0.005) {
                $feeStatus['statut_paiement'] = $paidAmount + 0.005 >= $initialAmount ? 'Payé' : 'Exonéré';
            } elseif ($paidAmount > 0.005) {
                $feeStatus['statut_paiement'] = 'Partiellement payé';
            } else {
                $feeStatus['statut_paiement'] = 'En dette';
            }
            $feeStatus['montant_restant'] = $remainingAmount;
            $currency = strtoupper(trim((string) ($feeStatus['devise'] ?? 'USD'))) ?: 'USD';
            $totalDebtByCurrency[$currency] = ($totalDebtByCurrency[$currency] ?? 0.0) + $remainingAmount;
        }
        unset($feeStatus);
        foreach ($totalDebtByCurrency as $currency => $amount) {
            $totalDebtByCurrency[$currency] = round($amount, 2);
        }
        ksort($totalDebtByCurrency);
        $totalPaidByCurrency = $selectedYearId > 0
            ? $this->getPaidTotalsByCurrency($eleveId, $selectedYearId)
            : [];
        $entryTotalsByCurrency = $selectedYearId > 0
            ? $this->getEntryTotalsByCurrency($eleveId, $selectedYearId)
            : [];
        $notes = $selectedYearId > 0 ? Eleve::getNotes($eleveId, $selectedYearId) : [];
        $discipline = \App\Models\Eleve::getDiscipline($eleveId);
        $canManageDiscipline = ($user['role'] ?? '') === 'ecole_admin' || Auth::can('manage_discipline');
        $canManageAuditions = in_array($user['role'] ?? '', ['ecole_admin', 'DD_école'], true);
        $canViewAuditions = in_array(
            $user['role'] ?? '',
            ['super_admin', 'ecole_admin', 'DD_école', 'DE_école', 'préfet_école'],
            true
        );
        $auditionsByIncident = $canViewAuditions
            ? DisciplineAudition::getForDisciplineEntries(
                array_column($discipline, 'id'),
                $studentSchoolId
            )
            : [];
        $disciplinePeriods = $canManageDiscipline
            ? Eleve::getDisciplinePeriodsForSchool($studentSchoolId)
            : [];
        $disciplineCsrfToken = '';
        if ($canManageDiscipline) {
            $disciplineCsrfToken = (string) ($_SESSION['discipline_csrf_token'] ?? '');
            if ($disciplineCsrfToken === '') {
                $disciplineCsrfToken = bin2hex(random_bytes(32));
                $_SESSION['discipline_csrf_token'] = $disciplineCsrfToken;
            }
        }
        $disciplineSuccess = (string) ($_SESSION['discipline_success'] ?? '');
        $disciplineError = (string) ($_SESSION['discipline_error'] ?? '');
        unset($_SESSION['discipline_success'], $_SESSION['discipline_error']);

        $this->view('eleves/show', [
            'title' => APP_NAME . ' - Fiche élève',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'eleve' => $eleve,
            'enrollmentHistory' => $enrollmentHistory,
            'selectedEnrollment' => $selectedEnrollment,
            'selectedYearId' => $selectedYearId,
            'currentClass' => $currentClass,
            'ecritures' => $ecritures,
            'feeStatuses' => $feeStatuses,
            'totalPaidByCurrency' => $totalPaidByCurrency,
            'totalDebtByCurrency' => $totalDebtByCurrency,
            'entryTotalsByCurrency' => $entryTotalsByCurrency,
            'notes' => $notes,
            'discipline' => $discipline,
            'canManageDiscipline' => $canManageDiscipline,
            'canManageAuditions' => $canManageAuditions,
            'canViewAuditions' => $canViewAuditions,
            'auditionsByIncident' => $auditionsByIncident,
            'disciplinePeriods' => $disciplinePeriods,
            'disciplineCsrfToken' => $disciplineCsrfToken,
            'disciplineSuccess' => $disciplineSuccess,
            'disciplineError' => $disciplineError,
        ]);
    }

    public function saveDiscipline(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'DD_école']);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        if ($user === null) {
            $this->redirect('/error/accessDenied');
        }

        $studentId = (int) ($_POST['eleve_id'] ?? 0);
        $student = $this->findDisciplineStudent($user, $studentId);
        if ($student === null) {
            $this->redirect('/error/accessDenied');
        }
        $schoolId = (int) ($user['role'] === 'super_admin'
            ? ($student['ecole_id'] ?? 0)
            : ($user['ecole_id'] ?? 0));
        $returnPath = '/eleves/show?id=' . $studentId . '#discipline';

        if (!$this->hasValidDisciplineCsrfToken()) {
            $_SESSION['discipline_error'] = 'Votre session a expiré. Rechargez la page puis réessayez.';
            $this->redirect($returnPath);
        }

        $entryId = filter_var($_POST['discipline_id'] ?? 0, FILTER_VALIDATE_INT);
        if ($entryId === false || $entryId < 0) {
            $_SESSION['discipline_error'] = 'Le dossier disciplinaire sélectionné est invalide.';
            $this->redirect($returnPath);
        }

        $data = [
            'periode_id' => $_POST['periode_id'] ?? null,
            'faute' => $_POST['faute'] ?? '',
            'sanction' => $_POST['sanction'] ?? '',
            'retrait_points' => $_POST['retrait_points'] ?? null,
            'date_evenement' => $_POST['date_evenement'] ?? '',
        ];

        try {
            if ($entryId > 0) {
                Eleve::updateDiscipline($entryId, $studentId, $schoolId, $data);
                $_SESSION['discipline_success'] = 'Le dossier disciplinaire a été mis à jour.';
            } else {
                Eleve::createDiscipline($studentId, $schoolId, $data);
                $_SESSION['discipline_success'] = 'L’incident a été ajouté au dossier disciplinaire.';
            }
        } catch (\InvalidArgumentException | \DomainException $exception) {
            $_SESSION['discipline_error'] = $exception->getMessage();
        }

        $this->redirect($returnPath);
    }

    public function deleteDiscipline(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'DD_école']);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        if ($user === null) {
            $this->redirect('/error/accessDenied');
        }

        $studentId = (int) ($_POST['eleve_id'] ?? 0);
        $student = $this->findDisciplineStudent($user, $studentId);
        if ($student === null) {
            $this->redirect('/error/accessDenied');
        }
        $schoolId = (int) ($user['role'] === 'super_admin'
            ? ($student['ecole_id'] ?? 0)
            : ($user['ecole_id'] ?? 0));
        $returnPath = '/eleves/show?id=' . $studentId . '#discipline';

        if (!$this->hasValidDisciplineCsrfToken()) {
            $_SESSION['discipline_error'] = 'Votre session a expiré. Rechargez la page puis réessayez.';
            $this->redirect($returnPath);
        }

        $entryId = filter_var($_POST['discipline_id'] ?? null, FILTER_VALIDATE_INT);
        if ($entryId === false || $entryId <= 0) {
            $_SESSION['discipline_error'] = 'Le dossier disciplinaire sélectionné est invalide.';
            $this->redirect($returnPath);
        }

        try {
            Eleve::deleteDiscipline($entryId, $studentId, $schoolId);
            $_SESSION['discipline_success'] = 'L’incident a été supprimé du dossier disciplinaire.';
        } catch (\InvalidArgumentException | \DomainException $exception) {
            $_SESSION['discipline_error'] = $exception->getMessage();
        }

        $this->redirect($returnPath);
    }

    public function recordAudition(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'DD_école']);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $user = Auth::refresh() ?: Auth::user();
        if ($user === null) {
            $this->redirect('/error/accessDenied');
        }

        $studentId = filter_var($_POST['eleve_id'] ?? null, FILTER_VALIDATE_INT);
        $incidentId = filter_var($_POST['discipline_id'] ?? null, FILTER_VALIDATE_INT);
        if ($studentId === false || $studentId <= 0 || $incidentId === false || $incidentId <= 0) {
            $_SESSION['discipline_error'] = 'L’élève ou l’incident sélectionné est invalide.';
            $this->redirect('/eleves');
        }

        $student = $this->findDisciplineStudent($user, $studentId);
        if ($student === null) {
            $this->redirect('/error/accessDenied');
        }
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $returnPath = '/eleves/show?id=' . $studentId . '#discipline';
        if (!$this->hasValidDisciplineCsrfToken()) {
            $_SESSION['discipline_error'] = 'Votre session a expiré. Rechargez la page puis réessayez.';
            $this->redirect($returnPath);
        }

        try {
            $auditionId = DisciplineAudition::create([
                'discipline_id' => $incidentId,
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'created_by' => (int) $user['id'],
                'hearing_at' => $_POST['hearing_at'] ?? null,
                'location' => $_POST['location'] ?? '',
                'alleged_facts' => $_POST['alleged_facts'] ?? '',
                'student_statement' => $_POST['student_statement'] ?? '',
                'observations' => $_POST['observations'] ?? '',
                'decision' => $_POST['decision'] ?? '',
                'parent_meeting_at' => $_POST['parent_meeting_at'] ?? null,
                'parent_meeting_location' => $_POST['parent_meeting_location'] ?? '',
                'parent_notice' => $_POST['parent_notice'] ?? '',
            ]);
        } catch (\InvalidArgumentException | \DomainException $exception) {
            $_SESSION['discipline_error'] = $exception->getMessage();
            $this->redirect($returnPath);
        } catch (\PDOException $exception) {
            error_log('ElevesController::recordAudition database failure: ' . $exception->getMessage());
            $_SESSION['discipline_error'] = 'Le procès-verbal n’a pas pu être enregistré. Vérifiez que la migration disciplinaire a été appliquée.';
            $this->redirect($returnPath);
        }

        $audition = DisciplineAudition::findByIdAndSchool(
            $auditionId,
            $schoolId,
            $this->getSectionScopeForUser($user)
        );
        if ($audition === null) {
            throw new \RuntimeException('Le procès-verbal enregistré est introuvable.');
        }

        $this->sendParentSummons($audition);
        $this->transmitAuditionReport($audition);

        $_SESSION['discipline_success'] = 'Le PV d’audition est enregistré, la convocation a été tentée par email et SMS, et le rapport est transmis aux autorités scolaires. Vérifiez les statuts de livraison ci-dessous.';
        $this->redirect($returnPath);
    }

    public function disciplineReport(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'DD_école', 'DE_école', 'préfet_école']);
        $user = Auth::refresh() ?: Auth::user();
        if ($user === null) {
            $this->redirect('/error/accessDenied');
        }

        $auditionId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $audition = $auditionId !== false && $auditionId > 0
            ? DisciplineAudition::findByIdAndSchool(
                $auditionId,
                $schoolId,
                $this->getSectionScopeForUser($user)
            )
            : null;
        if ($audition === null) {
            $this->redirect('/error/accessDenied');
        }

        $isRecipient = in_array(
            (int) ($user['id'] ?? 0),
            DisciplineAudition::getReportRecipientIds((int) $audition['id']),
            true
        );
        if (!in_array($user['role'] ?? '', ['ecole_admin', 'DD_école'], true) && !$isRecipient) {
            $this->redirect('/error/accessDenied');
        }

        header('Content-Type: text/html; charset=utf-8');
        require dirname(__DIR__) . '/Views/eleves/discipline_report.php';
    }

    private function sendParentSummons(array $audition): void
    {
        $schoolName = trim((string) ($audition['school_name'] ?? '')) ?: 'Établissement scolaire';
        $parentName = trim((string) ($audition['parent_name'] ?? '')) ?: 'Parent/tuteur';
        $appointment = new \DateTimeImmutable((string) $audition['parent_meeting_at']);
        $appointmentLabel = $appointment->format('d/m/Y à H\hi');
        $location = trim((string) ($audition['parent_meeting_location'] ?? ''));
        $locationLabel = $location !== '' ? ' à ' . $location : '';

        $email = trim((string) ($audition['parent_email'] ?? ''));
        if ($email === '') {
            DisciplineAudition::recordDelivery(
                (int) $audition['id'],
                'email',
                'skipped',
                'Aucune adresse email n’est enregistrée pour le parent.'
            );
        } else {
            $emailBody = 'Bonjour ' . $parentName . ",\n\n"
                . 'L’établissement ' . $schoolName . ' vous prie de vous présenter le '
                . $appointmentLabel . $locationLabel . " pour une rencontre concernant "
                . \App\Core\PersonName::format($audition) . ".\n\n";
            $parentNotice = trim((string) ($audition['parent_notice'] ?? ''));
            if ($parentNotice !== '') {
                $emailBody .= $parentNotice . "\n\n";
            }
            $emailBody .= "Pour toute question, veuillez contacter l’établissement.\n";
            try {
                SchoolEmailNotifier::send($email, $parentName, 'Convocation de l’établissement', $emailBody);
                DisciplineAudition::recordDelivery(
                    (int) $audition['id'],
                    'email',
                    'sent',
                    'Le courriel a été accepté par le serveur SMTP.'
                );
            } catch (\Throwable $exception) {
                error_log('ElevesController::sendParentSummons email failed: ' . $exception->getMessage());
                DisciplineAudition::recordDelivery(
                    (int) $audition['id'],
                    'email',
                    'failed',
                    'Le courriel n’a pas pu être envoyé. Vérifiez la configuration SMTP et l’adresse du parent.'
                );
            }
        }

        $phone = trim((string) ($audition['parent_phone'] ?? ''));
        if ($phone === '') {
            DisciplineAudition::recordDelivery(
                (int) $audition['id'],
                'sms',
                'skipped',
                'Aucun numéro de téléphone n’est enregistré pour le parent.'
            );
            return;
        }

        $smsText = '[' . $schoolName . '] Vous êtes prié(e) de vous présenter le '
            . $appointmentLabel . $locationLabel . '. Merci de contacter l’établissement en cas d’empêchement.';
        try {
            $smsResult = (new PaymentParentNotifier())->sendDirectSms($phone, $smsText, $schoolName);
            DisciplineAudition::recordDelivery(
                (int) $audition['id'],
                'sms',
                !empty($smsResult['sent']) ? 'sent' : 'failed',
                (string) ($smsResult['details'] ?? $smsResult['message'] ?? 'Le SMS n’a pas pu être envoyé.')
            );
        } catch (\Throwable $exception) {
            error_log('ElevesController::sendParentSummons SMS failed: ' . $exception->getMessage());
            DisciplineAudition::recordDelivery(
                (int) $audition['id'],
                'sms',
                'failed',
                'Le SMS n’a pas pu être envoyé. Vérifiez la configuration du fournisseur SMS.'
            );
        }
    }

    private function transmitAuditionReport(array $audition): void
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id FROM utilisateurs
             WHERE ecole_id = :school_id
               AND statut = \'Actif\'
               AND role IN (\'DE_école\', \'préfet_école\')
             ORDER BY role, id'
        );
        $stmt->execute([':school_id' => (int) $audition['school_id']]);
        $recipientIds = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
        if ($recipientIds === []) {
            DisciplineAudition::recordDelivery(
                (int) $audition['id'],
                'report',
                'no_recipients',
                'Aucun compte actif de directeur des études ou de préfet des études n’a été trouvé.'
            );
            return;
        }

        $studentName = \App\Core\PersonName::format($audition);
        $title = 'Rapport disciplinaire — ' . ($studentName !== '' ? $studentName : 'Élève');
        $body = 'Un procès-verbal d’audition est disponible pour '
            . ($studentName !== '' ? $studentName : 'un élève')
            . '. Incident du ' . formatDate($audition['date_evenement'] ?? null)
            . '. Consultez le rapport depuis cette notification.';
        $deliveredIds = [];
        foreach ($recipientIds as $recipientId) {
            try {
                if (UserMessage::notifyUsers(
                    [$recipientId],
                    (int) $audition['id'],
                    $title,
                    $body
                ) === 1) {
                    $deliveredIds[] = $recipientId;
                }
            } catch (\Throwable $exception) {
                error_log('ElevesController::transmitAuditionReport notification failed: ' . $exception->getMessage());
            }
        }

        $status = count($deliveredIds) === count($recipientIds)
            ? 'sent'
            : ($deliveredIds !== [] ? 'partial' : 'failed');
        $details = count($deliveredIds) . ' notification(s) interne(s) créée(s) sur ' . count($recipientIds) . '.';
        DisciplineAudition::recordDelivery(
            (int) $audition['id'],
            'report',
            $status,
            $details,
            $deliveredIds
        );
    }

    private function findDisciplineStudent(array $user, int $studentId): ?array
    {
        if (($user['role'] ?? '') === 'super_admin') {
            return $studentId > 0 ? Eleve::findById($studentId) : null;
        }

        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if ($schoolId <= 0 || $studentId <= 0) {
            return null;
        }

        return Eleve::findByIdAndSchool(
            $studentId,
            $schoolId,
            $this->getSectionScopeForUser($user)
        );
    }

    private function hasValidDisciplineCsrfToken(): bool
    {
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        $sessionToken = (string) ($_SESSION['discipline_csrf_token'] ?? '');

        return $postedToken !== '' && $sessionToken !== '' && hash_equals($sessionToken, $postedToken);
    }

    private function getPaidTotalsByCurrency(int $eleveId, int $yearId): array
    {
        $db = \App\Core\Database::getConnection();
        $totals = [];
        $queries = [
            'SELECT COALESCE(fs.devise, \'USD\') AS devise, SUM(ece.montant) AS total
             FROM ecritures_comptables_eleves ece
             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
             LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id
             WHERE ce.eleve_id = :eleve AND ce.annee_scolaire_id = :year_id
               AND ece.type_mouvement = \'CREDIT\' AND ece.annulee_at IS NULL
               AND NULLIF(TRIM(ece.reference_recu), \'\') IS NOT NULL
               AND ece.reference_recu NOT LIKE \'REG-FRAIS-%\'
             GROUP BY COALESCE(fs.devise, \'USD\')',
        ];

        foreach ($queries as $query) {
            try {
                $stmt = $db->prepare($query);
                $stmt->execute([':eleve' => $eleveId, ':year_id' => $yearId]);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $currency = strtoupper(trim($row['devise'] ?? 'USD')) ?: 'USD';
                    $totals[$currency] = max($totals[$currency] ?? 0.0, (float) ($row['total'] ?? 0));
                }
            } catch (\Throwable $e) {
                // Keep the totals available if a legacy table is unavailable.
            }
        }
        ksort($totals);
        return $totals;
    }

    private function getEntryTotalsByCurrency(int $eleveId, int $yearId): array
    {
        $db = \App\Core\Database::getConnection();
        $totals = [];
        $queries = [
            'SELECT ece.type_mouvement, COALESCE(fs.devise, \'USD\') AS devise, SUM(ece.montant) AS total
             FROM ecritures_comptables_eleves ece
             INNER JOIN comptes_eleves ce ON ce.id = ece.compte_eleve_id
             LEFT JOIN frais_scolaires fs ON fs.id = ece.frais_id
             WHERE ce.eleve_id = :eleve AND ce.annee_scolaire_id = :year_id
               AND ece.annulee_at IS NULL
               AND (ece.type_mouvement <> \'CREDIT\' OR NULLIF(TRIM(ece.reference_recu), \'\') IS NOT NULL)
             GROUP BY ece.type_mouvement, COALESCE(fs.devise, \'USD\')',
        ];

        foreach ($queries as $query) {
            try {
                $stmt = $db->prepare($query);
                $stmt->execute([':eleve' => $eleveId, ':year_id' => $yearId]);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $type = strtoupper((string) ($row['type_mouvement'] ?? ''));
                    $currency = strtoupper(trim($row['devise'] ?? 'USD')) ?: 'USD';
                    if ($type === '') {
                        continue;
                    }
                    $totals[$currency][$type] = max($totals[$currency][$type] ?? 0.0, (float) ($row['total'] ?? 0));
                }
            } catch (\Throwable $e) {
                // Keep accounting totals available when the legacy table is absent.
            }
        }
        ksort($totals);
        return $totals;
    }

    
}
