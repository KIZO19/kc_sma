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
        $agentAccountRoles = User::getAgentAccountRoleLabels();

        $agents = [];
        $agentAccounts = [];
        $suggestedAccountRoles = [];
        $unlinkedAccounts = [];
        $agentFunctions = [];
        if ($ecoleId > 0) {
            $agents = Agent::getAll($ecoleId);
            $agentFunctions = Agent::getAdministrativeRoles();
            $rolePlaceholders = [];
            $roleParams = [':ecole_id' => $ecoleId];
            foreach (User::getAgentAccountRoleAliases() as $index => $accountRole) {
                $placeholder = ':unlinked_role_' . $index;
                $rolePlaceholders[] = $placeholder;
                $roleParams[$placeholder] = $accountRole;
            }
            $unlinkedStmt = \App\Core\Database::getConnection()->prepare(
                'SELECT u.id, u.nom_complet, u.identifiant, u.role
                 FROM utilisateurs u
                 LEFT JOIN agents a ON a.id = u.reference_id AND a.ecole_id = u.ecole_id
                 WHERE u.ecole_id = :ecole_id
                   AND u.role IN (' . implode(',', $rolePlaceholders) . ')
                   AND a.id IS NULL
                 ORDER BY u.nom_complet'
            );
            $unlinkedStmt->execute($roleParams);
            $unlinkedAccounts = $unlinkedStmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($unlinkedAccounts as &$unlinkedAccount) {
                $unlinkedAccount['suggested_function_id'] = Agent::suggestAdministrativeRoleId(
                    (string) $unlinkedAccount['role'],
                    $agentFunctions
                );
            }
            unset($unlinkedAccount);
            if ($agents) {
                $db = \App\Core\Database::getConnection();
                $agentIds = array_map(static fn (array $agent): int => (int) $agent['id'], $agents);
                $placeholders = [];
                $params = [':ecole_id' => $ecoleId];
                $rolePlaceholders = [];
                foreach (User::getAgentAccountRoleAliases() as $index => $accountRole) {
                    $placeholder = ':role_' . $index;
                    $rolePlaceholders[] = $placeholder;
                    $params[$placeholder] = $accountRole;
                }
                foreach ($agentIds as $index => $agentId) {
                    $placeholder = ':agent_' . $index;
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $agentId;
                }
                $stmt = $db->prepare(
                    'SELECT * FROM utilisateurs
                     WHERE ecole_id = :ecole_id
                       AND role IN (' . implode(',', $rolePlaceholders) . ')
                       AND reference_id IN (' . implode(',', $placeholders) . ')
                     ORDER BY CASE WHEN role = \'enseignant_école\' THEN 0 WHEN role IN (\'agent_ecole\', \'agent_école\') THEN 2 ELSE 1 END, id ASC'
                );
                $stmt->execute($params);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $account) {
                    $agentId = (int) ($account['reference_id'] ?? 0);
                    if (!isset($agentAccounts[$agentId])) {
                        $agentAccounts[$agentId] = $account;
                    }
                }
            }
            foreach ($agents as $agent) {
                $suggestedAccountRoles[(int) $agent['id']] = Agent::suggestAccountRole((string) ($agent['fonction'] ?? ''));
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
            'agentAccountRoles' => $agentAccountRoles,
            'agentFunctions' => $agentFunctions,
            'unlinkedAccounts' => $unlinkedAccounts,
            'suggestedAccountRoles' => $suggestedAccountRoles,
            'csrfToken' => $_SESSION['agents_csrf'],
        ]);
    }

    public function createAgent(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['ecole_admin']);
        $user = Auth::refresh() ?: Auth::user();
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $schoolId <= 0) {
            $this->redirect('/agents');
        }

        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        $expectedToken = (string) ($_SESSION['agents_csrf'] ?? '');
        if ($postedToken === '' || $expectedToken === '' || !hash_equals($expectedToken, $postedToken)) {
            $_SESSION['agents_errors'] = ['Votre session a expiré. Rechargez la page puis réessayez.'];
            $this->redirect('/agents');
        }

        $nom = trim((string) ($_POST['nom'] ?? ''));
        $postnom = trim((string) ($_POST['postnom'] ?? ''));
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $telephone = trim((string) ($_POST['telephone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $functionId = (int) ($_POST['role_id'] ?? 0);
        $accountRole = (string) ($_POST['account_role'] ?? '');
        $identifier = trim((string) ($_POST['identifiant'] ?? ''));
        $password = (string) ($_POST['mot_de_passe'] ?? '');
        $passwordConfirmation = (string) ($_POST['mot_de_passe_confirm'] ?? '');
        $existingAccountId = (int) ($_POST['existing_account_id'] ?? 0);
        $functions = Agent::getAdministrativeRoles();
        $validFunctionIds = array_map(static fn (array $function): int => (int) $function['id'], $functions);
        $errors = [];

        if ($nom === '' || mb_strlen($nom, 'UTF-8') > 50) {
            $errors[] = 'Le nom est obligatoire et ne peut pas dépasser 50 caractères.';
        }
        if ($postnom === '' || mb_strlen($postnom, 'UTF-8') > 50) {
            $errors[] = 'Le postnom est obligatoire et ne peut pas dépasser 50 caractères.';
        }
        if (mb_strlen($prenom, 'UTF-8') > 50) {
            $errors[] = 'Le prénom ne peut pas dépasser 50 caractères.';
        }
        if ($telephone === '' || mb_strlen($telephone, 'UTF-8') > 20) {
            $errors[] = 'Le téléphone est obligatoire et ne peut pas dépasser 20 caractères.';
        }
        if ($email !== '' && (mb_strlen($email, 'UTF-8') > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $errors[] = 'Veuillez saisir une adresse e-mail valide (255 caractères maximum).';
        }
        if (!in_array($functionId, $validFunctionIds, true)) {
            $errors[] = 'Veuillez sélectionner une fonction valide pour la fiche agent.';
        }
        if (!in_array($accountRole, User::getAgentAccountRoles(), true)) {
            $errors[] = 'Veuillez sélectionner un rôle d’accès valide.';
        }
        if ($identifier === '' || mb_strlen($identifier, 'UTF-8') > 100) {
            $errors[] = 'L’identifiant est obligatoire et ne peut pas dépasser 100 caractères.';
        }
        if ($existingAccountId <= 0 && (strlen($password) < 8 || strlen($password) > 72)) {
            $errors[] = 'Le mot de passe doit contenir entre 8 et 72 caractères.';
        }
        if ($existingAccountId > 0 && $password !== '' && (strlen($password) < 8 || strlen($password) > 72)) {
            $errors[] = 'Le nouveau mot de passe doit contenir entre 8 et 72 caractères.';
        }
        if ($password !== $passwordConfirmation) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors) {
            $_SESSION['agents_errors'] = $errors;
            $this->redirect('/agents');
        }

        $db = \App\Core\Database::getConnection();
        try {
            $db->beginTransaction();
            $agent = Agent::create([
                'ecole_id' => $schoolId,
                'nom' => $nom,
                'postnom' => $postnom,
                'prenom' => $prenom !== '' ? $prenom : null,
                'telephone' => $telephone,
                'email' => $email !== '' ? $email : null,
                'role_id' => $functionId,
            ], false);
            if (!$agent) {
                throw new \RuntimeException('La fiche agent n’a pas pu être enregistrée.');
            }

            if ($existingAccountId > 0 && $password === '') {
                $existingAccount = User::findById($existingAccountId);
                if (!$existingAccount || (int) ($existingAccount['ecole_id'] ?? 0) !== $schoolId) {
                    throw new \RuntimeException('Le compte personnel sélectionné n’est plus disponible.');
                }
                $account = User::configureAgentAccount(
                    (int) $agent['id'],
                    $schoolId,
                    $accountRole,
                    $identifier,
                    $password,
                    $existingAccountId
                );
            } else {
                $account = User::configureAgentAccount(
                    (int) $agent['id'],
                    $schoolId,
                    $accountRole,
                    $identifier,
                    $password,
                    $existingAccountId > 0 ? $existingAccountId : null
                );
            }

            $db->commit();
            $_SESSION['agents_success'] = 'La fiche de ' . $account['nom_complet'] . ' et son accès ont été enregistrés.';
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['agents_errors'] = ['Impossible d’enregistrer la fiche et son accès : ' . $e->getMessage()];
        }

        $this->redirect('/agents');
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

        $accountRole = (string) ($_POST['account_role'] ?? '');
        $identifier = trim((string) ($_POST['identifiant'] ?? ''));
        $password = (string) ($_POST['mot_de_passe'] ?? '');
        $passwordConfirmation = (string) ($_POST['mot_de_passe_confirm'] ?? '');
        $errors = [];
        if (!in_array($accountRole, User::getAgentAccountRoles(), true)) {
            $errors[] = 'Veuillez sélectionner une fonction valide pour ce compte.';
        }
        if ($identifier === '' || mb_strlen($identifier, 'UTF-8') > 100) {
            $errors[] = 'L’identifiant est obligatoire et ne peut pas dépasser 100 caractères.';
        }
        if (strlen($password) < 8 || strlen($password) > 72) {
            $errors[] = 'Le mot de passe doit contenir entre 8 et 72 caractères.';
        }
        if ($password !== $passwordConfirmation) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors) {
            $_SESSION['agents_errors'] = $errors;
            $this->redirect('/agents');
        }

        try {
            $account = User::configureAgentAccount($agentId, $schoolId, $accountRole, $identifier, $password);
            $_SESSION['agents_success'] = 'Accès créé et activé pour ' . $account['nom_complet'] . '.';
        } catch (\Throwable $e) {
            $_SESSION['agents_errors'] = ['Impossible de configurer le compte : ' . $e->getMessage()];
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
        $account = $this->findSchoolAgentAccount($agentId, $schoolId);
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

    private function findSchoolAgentAccount(int $agentId, int $schoolId): ?array
    {
        $rolePlaceholders = [];
        $params = [
            ':ecole_id' => $schoolId,
            ':reference_id' => $agentId,
        ];
        foreach (User::getAgentAccountRoleAliases() as $index => $accountRole) {
            $placeholder = ':role_' . $index;
            $rolePlaceholders[] = $placeholder;
            $params[$placeholder] = $accountRole;
        }

        $stmt = \App\Core\Database::getConnection()->prepare(
            'SELECT * FROM utilisateurs
             WHERE ecole_id = :ecole_id
               AND reference_id = :reference_id
               AND role IN (' . implode(',', $rolePlaceholders) . ')
             ORDER BY CASE WHEN role = \'enseignant_école\' THEN 0 WHEN role IN (\'agent_ecole\', \'agent_école\') THEN 2 ELSE 1 END, id ASC
             LIMIT 1'
        );
        $stmt->execute($params);
        $account = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $account ?: null;
    }
}
