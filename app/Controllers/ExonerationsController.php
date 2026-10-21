<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Eleve;
use App\Models\Exoneration;
use App\Models\User;

class ExonerationsController extends Controller
{
    private const REQUEST_ROLES = ['comptable_école', 'ecole_admin', 'préfet_école'];
    private const VALIDATION_ROLES = [
        'ecole_admin',
        'promoteur_école',
        'préfet_école',
        'sec_école',
        'DE_école',
        'DD_école',
        'DP_école',
        'DA_école',
    ];

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(array_values(array_unique(array_merge(self::REQUEST_ROLES, self::VALIDATION_ROLES))));

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $role = $user['role'] ?? 'default';
        $studentId = (int) ($_GET['eleve_id'] ?? 0);
        $student = $studentId > 0 ? Eleve::findByIdAndSchool($studentId, $ecoleId) : null;

        $this->view('exonerations/index', [
            'title' => APP_NAME . ' - Exonérations',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'students' => in_array($role, self::REQUEST_ROLES, true) ? Eleve::getAllBySchool($ecoleId) : [],
            'student' => $student,
            'availableFees' => $student ? Exoneration::getAvailableFeesForStudent($studentId, $ecoleId) : [],
            'requests' => Exoneration::getAllBySchool($ecoleId),
            'canRequest' => in_array($role, self::REQUEST_ROLES, true),
            'canValidate' => in_array($role, self::VALIDATION_ROLES, true),
        ]);
    }

    public function availableFees(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::REQUEST_ROLES);
        header('Content-Type: application/json; charset=utf-8');

        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $studentId = (int) ($_GET['eleve_id'] ?? 0);
        if ($ecoleId <= 0 || !Eleve::findByIdAndSchool($studentId, $ecoleId)) {
            http_response_code(404);
            echo json_encode(['fees' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $fees = array_map(static fn (array $fee): array => [
            'id' => (int) $fee['id'],
            'label' => (string) $fee['type_frais'],
            'devise' => strtoupper((string) ($fee['devise'] ?? 'USD')),
            'montant_restant' => (float) $fee['montant_restant'],
        ], Exoneration::getAvailableFeesForStudent($studentId, $ecoleId));
        echo json_encode(['fees' => $fees], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    public function request(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::REQUEST_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $studentId = (int) ($_POST['eleve_id'] ?? 0);
        $feeId = (int) ($_POST['frais_id'] ?? 0);
        $amount = trim((string) ($_POST['montant'] ?? ''));
        $motif = trim((string) ($_POST['motif'] ?? ''));
        $errors = [];

        if ($ecoleId <= 0 || !Eleve::findByIdAndSchool($studentId, $ecoleId)) {
            $errors[] = 'Élève invalide pour cette école.';
        }
        if (!is_numeric($amount) || (float) $amount <= 0) {
            $errors[] = 'Le montant de l’exonération doit être supérieur à zéro.';
        }
        if ($motif === '' || mb_strlen($motif) > 500) {
            $errors[] = 'Le motif est obligatoire et doit contenir au maximum 500 caractères.';
        }

        $fee = null;
        if (!$errors) {
            foreach (Exoneration::getAvailableFeesForStudent($studentId, $ecoleId) as $availableFee) {
                if ((int) $availableFee['id'] === $feeId) {
                    $fee = $availableFee;
                    break;
                }
            }
            if (!$fee) {
                $errors[] = 'Le frais choisi ne s’applique pas à cet élève ou ne présente plus de solde.';
            } elseif ((float) $amount > (float) $fee['montant_restant'] + 0.005) {
                $errors[] = 'Le montant demandé dépasse le reste dû ('
                    . number_format((float) $fee['montant_restant'], 2, ',', ' ')
                    . ' ' . strtoupper((string) ($fee['devise'] ?? 'USD')) . ').';
            }
        }

        if (!$errors && Exoneration::create([
            'ecole_id' => $ecoleId,
            'eleve_id' => $studentId,
            'frais_id' => $feeId,
            'montant' => round((float) $amount, 2),
            'devise' => strtoupper((string) ($fee['devise'] ?? 'USD')),
            'motif' => $motif,
            'demandeur_id' => (int) ($user['id'] ?? 0),
        ])) {
            $_SESSION['exonerations_success'] = 'La demande d’exonération est en attente de validation.';
        } else {
            $errors[] = 'La demande d’exonération n’a pas pu être enregistrée.';
        }

        if ($errors) {
            $_SESSION['exonerations_errors'] = $errors;
        }
        $this->redirect('/exonerations');
    }

    public function decide(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(self::VALIDATION_ROLES);
        $user = Auth::refresh() ?: Auth::user();
        $decision = (string) ($_POST['decision'] ?? '');
        $status = $decision === 'Approuvee' ? 'Approuvee' : ($decision === 'Refusee' ? 'Refusee' : '');
        $ok = $status !== '' && Exoneration::decide(
            (int) ($_POST['id'] ?? 0),
            (int) ($user['ecole_id'] ?? 0),
            (int) ($user['id'] ?? 0),
            $status,
            trim((string) ($_POST['commentaire'] ?? ''))
        );

        if ($ok) {
            $_SESSION['exonerations_success'] = $status === 'Approuvee'
                ? 'Exonération approuvée ; le solde de l’élève a été recalculé.'
                : 'Demande d’exonération refusée.';
        } else {
            $_SESSION['exonerations_errors'] = ['Décision impossible : vérifiez le solde, l’école et le statut de la demande.'];
        }
        $this->redirect('/exonerations');
    }
}