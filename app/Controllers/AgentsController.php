<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Agent;
use App\Models\User;

class AgentsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        $agents = [];
        $agentAccounts = [];
        if ($ecoleId > 0) {
            $agents = Agent::getAll($ecoleId);
            if ($agents) {
                $db = \App\Core\Database::getConnection();
                $agentIds = array_map(static fn (array $agent): int => (int) $agent['id'], $agents);
                $placeholders = [];
                $params = [':ecole_id' => $ecoleId];
                foreach ($agentIds as $index => $agentId) {
                    $placeholder = ':agent_' . $index;
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $agentId;
                }
                $stmt = $db->prepare(
                    'SELECT * FROM utilisateurs
                     WHERE ecole_id = :ecole_id
                       AND role IN (\'agent_ecole\', \'enseignant_école\')
                       AND reference_id IN (' . implode(',', $placeholders) . ')
                     ORDER BY CASE WHEN role = \'enseignant_école\' THEN 0 ELSE 1 END, id ASC'
                );
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $account) {
                    $agentId = (int) ($account['reference_id'] ?? 0);
                    if (!isset($agentAccounts[$agentId])) {
                        $agentAccounts[$agentId] = $account;
                    }
                }
            }
        }
        if (empty($_SESSION['agents_csrf'])) {
            $_SESSION['agents_csrf'] = bin2hex(random_bytes(32));
        }

        $this->view('agents/index', [
            'title' => APP_NAME . ' - Agents',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'agents' => $agents,
            'agentAccounts' => $agentAccounts,
            'csrfToken' => $_SESSION['agents_csrf'],
        ]);
    }

    public function createAccount(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if ($schoolId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/agents');
        }
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if ($postedToken === '' || !hash_equals((string) ($_SESSION['agents_csrf'] ?? ''), $postedToken)) {
            $_SESSION['agents_errors'] = ['Votre session a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/agents');
        }

        $agentId = (int) ($_POST['agent_id'] ?? 0);
        $agent = $agentId > 0 ? Agent::findById($agentId) : null;
        if (!$agent || (int) ($agent['ecole_id'] ?? 0) !== $schoolId) {
            $_SESSION['agents_errors'] = ['Cet agent n’appartient pas à votre école.'];
            $this->redirect('/agents');
        }

        $existingAccount = User::findByReference('enseignant_école', $agentId, $schoolId)
            ?? User::findByReference('agent_ecole', $agentId, $schoolId);
        if ($existingAccount) {
            $_SESSION['agents_errors'] = ['Cet agent possède déjà un compte utilisateur.'];
            $this->redirect('/agents');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $account = Agent::createUserAccount($agentId);
                $_SESSION['agents_success'] = 'Compte utilisateur créé pour l’agent ' . htmlspecialchars($account['identifiant']);
            } catch (\Throwable $e) {
                $_SESSION['agents_errors'] = ['Impossible de créer le compte : ' . $e->getMessage()];
            }
        }

        $this->redirect('/agents');
    }

    public function updateAccountStatus(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);
        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $schoolId <= 0) {
            $this->redirect('/agents');
        }
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if ($postedToken === '' || !hash_equals((string) ($_SESSION['agents_csrf'] ?? ''), $postedToken)) {
            $_SESSION['agents_errors'] = ['Votre session a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/agents');
        }

        $agentId = (int) ($_POST['agent_id'] ?? 0);
        $status = (string) ($_POST['statut'] ?? '');
        $agent = $agentId > 0 ? Agent::findById($agentId) : null;
        $account = User::findByReference('enseignant_école', $agentId, $schoolId)
            ?? User::findByReference('agent_ecole', $agentId, $schoolId);
        if (!$agent || (int) ($agent['ecole_id'] ?? 0) !== $schoolId || !$account) {
            $_SESSION['agents_errors'] = ['Compte introuvable dans votre école.'];
        } elseif (!in_array($status, ['Actif', 'Inactif'], true)) {
            $_SESSION['agents_errors'] = ['Statut de compte invalide.'];
        } elseif (User::updateStatus((int) $account['id'], $status)) {
            $_SESSION['agents_success'] = $status === 'Actif' ? 'Compte activé.' : 'Compte désactivé.';
        } else {
            $_SESSION['agents_errors'] = ['Impossible de mettre à jour ce compte.'];
        }

        $this->redirect('/agents');
    }
}
