<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\PaieAgent;
use App\Models\User;
use Throwable;

class PaiesAgentsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if ($schoolId <= 0) {
            $this->redirect('/dashboard');
        }

        $month = $this->validMonth($_GET['mois'] ?? date('n'));
        $year = $this->validYear($_GET['annee'] ?? date('Y'));
        if (empty($_SESSION['paies_agents_csrf'])) {
            $_SESSION['paies_agents_csrf'] = bin2hex(random_bytes(32));
        }

        $missingTables = PaieAgent::getMissingModuleTables();
        $moduleReady = $missingTables === [];
        $this->view('paies_agents/index', [
            'title' => APP_NAME . ' - Paie des agents',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel((string) ($user['role'] ?? 'default')),
            'modules' => $this->getModulesForRole((string) ($user['role'] ?? 'default')),
            'agents' => $moduleReady ? PaieAgent::getAgentsForSchool($schoolId) : [],
            'slips' => $moduleReady ? PaieAgent::getSlipsForPeriod($schoolId, $month, $year) : [],
            'accounts' => $moduleReady ? PaieAgent::getCashAccounts($schoolId) : [],
            'currencies' => $moduleReady ? PaieAgent::getCurrencies() : [],
            'missingTables' => $missingTables,
            'month' => $month,
            'year' => $year,
            'csrfToken' => $_SESSION['paies_agents_csrf'],
        ]);
    }

    public function updateSalary(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();

        $this->processPost('Salaire de base et devise enregistrés.', function () use ($user): void {
            PaieAgent::updateSalary(
                (int) ($user['ecole_id'] ?? 0),
                $this->postedInteger('agent_id'),
                $this->postedAmount('salaire_de_base'),
                strtoupper($this->postedString('devise')),
                (int) ($user['id'] ?? 0)
            );
        });
    }

    public function createSlip(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();

        $this->processPost('La fiche de paie a été créée.', function () use ($user): void {
            PaieAgent::createSlip(
                (int) ($user['ecole_id'] ?? 0),
                $this->postedInteger('agent_id'),
                $this->validMonth($_POST['mois'] ?? 0),
                $this->validYear($_POST['annee'] ?? 0),
                $this->postedLines('primes'),
                $this->postedLines('retenues'),
                (int) ($user['id'] ?? 0)
            );
        });
    }

    public function updateSlip(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();

        $this->processPost('La fiche de paie en attente a été mise à jour.', function () use ($user): void {
            PaieAgent::updateSlip(
                (int) ($user['ecole_id'] ?? 0),
                $this->postedInteger('paie_id'),
                $this->postedLines('primes'),
                $this->postedLines('retenues')
            );
        });
    }

    public function paySlip(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();

        $this->processPost('La demande de paiement a été transmise pour approbation. Aucun compte n’a encore été débité.', function () use ($user): void {
            PaieAgent::paySlip(
                (int) ($user['ecole_id'] ?? 0),
                $this->postedInteger('paie_id'),
                $this->postedInteger('caisse_banque_id'),
                (int) ($user['id'] ?? 0)
            );
        });
    }

    public function updateAccountCurrency(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin', 'comptable_école']);
        $user = Auth::refresh() ?: Auth::user();

        $this->processPost('La devise de la caisse ou banque a été mise à jour.', function () use ($user): void {
            PaieAgent::updateAccountCurrency(
                (int) ($user['ecole_id'] ?? 0),
                $this->postedInteger('caisse_banque_id'),
                strtoupper($this->postedString('devise')),
                (int) ($user['id'] ?? 0),
                $this->postedString('confirmer_solde_devise') === '1'
            );
        });
    }

    private function processPost(string $successMessage, callable $operation): void
    {
        $month = $this->validMonth($_POST['mois'] ?? date('n'));
        $year = $this->validYear($_POST['annee'] ?? date('Y'));
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirectToPeriod($month, $year);
        }

        $rawToken = $_POST['csrf_token'] ?? '';
        $postedToken = \is_string($rawToken) ? $rawToken : '';
        $expectedToken = (string) ($_SESSION['paies_agents_csrf'] ?? '');
        if ($postedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $postedToken)) {
            $_SESSION['paies_agents_error'] = 'Votre session a expiré. Rechargez la page puis réessayez.';
            $this->redirectToPeriod($month, $year);
        }

        try {
            $missingTables = PaieAgent::getMissingModuleTables();
            if ($missingTables !== []) {
                throw new \InvalidArgumentException('Le module n’est pas installé. Exécutez les migrations indiquées sur la page Paie des agents.');
            }
            $operation();
            $_SESSION['paies_agents_success'] = $successMessage;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['paies_agents_error'] = $e->getMessage();
        } catch (Throwable $e) {
            error_log('PaiesAgentsController operation failed: ' . $e->getMessage());
            $_SESSION['paies_agents_error'] = 'Une erreur est survenue pendant l’opération. Vérifiez la période et réessayez.';
        }

        $this->redirectToPeriod($month, $year);
    }

    private function postedAmount(string $key): float
    {
        $rawValue = $_POST[$key] ?? '';
        if (!\is_scalar($rawValue)) {
            throw new \InvalidArgumentException('Saisissez un montant numérique valide.');
        }
        $value = trim((string) $rawValue);
        if ($value === '' || !is_numeric($value)) {
            throw new \InvalidArgumentException('Saisissez un montant numérique valide.');
        }
        return (float) $value;
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

    private function postedString(string $key): string
    {
        $value = $_POST[$key] ?? '';
        if (!\is_scalar($value)) {
            throw new \InvalidArgumentException('Une valeur envoyée au serveur est invalide.');
        }
        return trim((string) $value);
    }

    private function postedLines(string $key): array
    {
        $labels = $_POST[$key . '_libelle'] ?? [];
        $amounts = $_POST[$key . '_montant'] ?? [];
        if (!\is_array($labels) || !\is_array($amounts) || \count($labels) !== \count($amounts)) {
            throw new \InvalidArgumentException('Les détails des primes ou retenues sont invalides.');
        }

        $lines = [];
        foreach ($labels as $index => $label) {
            $amount = $amounts[$index] ?? '';
            if (!\is_scalar($label) || !\is_scalar($amount)) {
                throw new \InvalidArgumentException('Les détails des primes ou retenues sont invalides.');
            }
            $lines[] = [
                'libelle' => (string) $label,
                'montant' => (string) $amount,
            ];
        }
        return $lines;
    }

    private function validMonth($value): int
    {
        $month = filter_var(\is_scalar($value) ? (string) $value : '', FILTER_VALIDATE_INT);
        return $month !== false && $month >= 1 && $month <= 12 ? $month : (int) date('n');
    }

    private function validYear($value): int
    {
        $year = filter_var(\is_scalar($value) ? (string) $value : '', FILTER_VALIDATE_INT);
        return $year !== false && $year >= 2000 && $year <= 2100 ? $year : (int) date('Y');
    }

    private function redirectToPeriod(int $month, int $year): void
    {
        $this->redirect('/paies-agents?mois=' . $month . '&annee=' . $year);
    }
}
