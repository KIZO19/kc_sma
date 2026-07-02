<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class PageController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $route = str_replace(BASE_URL, '', $route);
        $route = trim($route, '/');

        $page = $this->renderPage($route);

        $this->view($page['view'], array_merge($page['data'], [
            'title' => APP_NAME . ' - ' . $page['title'],
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
        ]));
    }

    private function renderPage(string $route): array
    {
        return match ($route) {
            'inscriptions' => [
                'view' => 'generic/inscriptions',
                'title' => 'Inscriptions',
                'data' => ['headline' => 'Gestion des inscriptions', 'description' => 'Inscrivez de nouveaux élèves et suivez les dossiers scolaires.'],
            ],
            'eleves' => [
                'view' => 'generic/eleves',
                'title' => 'Élèves',
                'data' => ['headline' => 'Liste des élèves', 'description' => 'Affichez et gérez les informations des élèves de l’école.'],
            ],
            'notes' => [
                'view' => 'generic/notes',
                'title' => 'Notes',
                'data' => ['headline' => 'Gestion des notes', 'description' => 'Créez des évaluations et enregistrez les notes.'],
            ],
            'ecoles/generatePassword' => [
                'view' => 'ecoles/generate_password',
                'title' => 'Générer mot de passe',
                'data' => [],
            ],
            default => [
                'view' => 'generic/page-not-found',
                'title' => 'Page introuvable',
                'data' => ['headline' => 'Page non trouvée', 'description' => 'La page demandée est introuvable ou n’est pas encore disponible.'],
            ],
        };
    }
}
