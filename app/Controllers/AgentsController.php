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
        Auth::requireRoles(['super_admin', 'ecole_admin']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        $agents = [];
        $agentAccounts = [];
        if ($ecoleId > 0) {
            $agents = Agent::getAll($ecoleId);
            foreach ($agents as $agent) {
                $agentAccounts[$agent['id']] = User::findByReference('enseignant_école', (int) $agent['id']);
            }
        }

        $this->view('agents/index', [
            'title' => APP_NAME . ' - Agents',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'agents' => $agents,
            'agentAccounts' => $agentAccounts,
        ]);
    }

    public function createAccount(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $agentId = (int) ($_POST['agent_id'] ?? 0);
            if ($agentId > 0) {
                try {
                    $user = Agent::createUserAccount($agentId);
                    $_SESSION['agents_success'] = 'Compte utilisateur créé pour l\'agent ' . htmlspecialchars($user['identifiant']);
                } catch (\Throwable $e) {
                    $_SESSION['agents_errors'] = ['Impossible de créer le compte : ' . $e->getMessage()];
                }
            }
        }

        $this->redirect('/agents');
    }
}
