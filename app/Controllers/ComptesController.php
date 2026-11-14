<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\CashOutflow;
use App\Models\User;
use Throwable;

class ComptesController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::refresh() ?: Auth::user();
        if (!$user) {
            $this->redirect('/error/accessDenied');
        }

        $role = (string) ($user['role'] ?? '');
        $canManage = \in_array($role, ['ecole_admin', 'comptable_école'], true);
        $canApprove = CashOutflow::isUserDesignatedApprover($user);
        if (!$canManage && !$canApprove) {
            $this->redirect('/error/accessDenied');
        }

        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if ($schoolId <= 0) {
            $this->redirect('/error/accessDenied');
        }
        if (empty($_SESSION['cash_outflows_csrf'])) {
            $_SESSION['cash_outflows_csrf'] = bin2hex(random_bytes(32));
        }

        $this->view('comptes/index', [
            'title' => APP_NAME . ' - Caisses & Banques',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'accounts' => CashOutflow::getAccounts($schoolId),
            'outflows' => CashOutflow::getOutflows($schoolId),
            'canManage' => $canManage,
            'canApprove' => $canApprove,
            'csrfToken' => $_SESSION['cash_outflows_csrf'],
        ]);
    }

    public function createOutflow(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();
        $this->processPost('La sortie est en attente d’approbation. Aucun compte n’a encore été débité.', function () use ($user): void {
            CashOutflow::requestExpense(
                (int) ($user['ecole_id'] ?? 0),
                $this->postedInteger('caisse_banque_id'),
                $this->postedAmount('montant'),
                $this->postedString('motif'),
                (int) ($user['id'] ?? 0)
            );
        });
    }

    public function approveOutflow(): void
    {
        $this->decideOutflow(true);
    }

    public function rejectOutflow(): void
    {
        $this->decideOutflow(false);
    }

    private function decideOutflow(bool $approve): void
    {
        Auth::requireAuth();
        $user = Auth::refresh() ?: Auth::user();
        $this->processPost(
            $approve ? 'Sortie approuvée et débit enregistré.' : 'La demande de sortie a été refusée.',
            function () use ($user, $approve): void {
                CashOutflow::decide(
                    (int) ($user['ecole_id'] ?? 0),
                    $this->postedInteger('sortie_id'),
                    $user ?? [],
                    $approve,
                    $this->postedString('commentaire')
                );
            }
        );
    }

    private function processPost(string $successMessage, callable $operation): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect('/comptes');
        }

        $token = $_POST['csrf_token'] ?? '';
        $postedToken = \is_string($token) ? $token : '';
        $expectedToken = (string) ($_SESSION['cash_outflows_csrf'] ?? '');
        if ($postedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $postedToken)) {
            $_SESSION['cash_outflows_error'] = 'Votre session a expiré. Rechargez la page puis réessayez.';
            $this->redirect('/comptes');
        }

        try {
            $operation();
            $_SESSION['cash_outflows_success'] = $successMessage;
        } catch (\InvalidArgumentException $exception) {
            $_SESSION['cash_outflows_error'] = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log('ComptesController operation failed: ' . $exception->getMessage());
            $_SESSION['cash_outflows_error'] = 'Une erreur est survenue pendant l’opération. Rechargez la page et réessayez.';
        }

        $this->redirect('/comptes');
    }

    private function postedInteger(string $key): int
    {
        $value = $_POST[$key] ?? '';
        if (!\is_scalar($value)) {
            return 0;
        }
        $integer = filter_var((string) $value, FILTER_VALIDATE_INT);

        return $integer !== false && $integer > 0 ? $integer : 0;
    }

    private function postedAmount(string $key): float
    {
        $value = $_POST[$key] ?? '';
        if (!\is_scalar($value) || trim((string) $value) === '' || !is_numeric((string) $value)) {
            throw new \InvalidArgumentException('Saisissez un montant numérique valide.');
        }

        return (float) $value;
    }

    private function postedString(string $key): string
    {
        $value = $_POST[$key] ?? '';
        if (!\is_scalar($value)) {
            throw new \InvalidArgumentException('Une valeur envoyée au serveur est invalide.');
        }

        return trim((string) $value);
    }
}
