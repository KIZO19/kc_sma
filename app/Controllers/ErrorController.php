<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('errors/error', [
            'title' => APP_NAME . ' - Page non trouvée',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'statusCode' => 404,
            'statusLabel' => 'Page non trouvée',
            'message' => 'La page demandée est introuvable. Vérifiez l’URL ou revenez au tableau de bord.',
            'buttonText' => 'Retour au dashboard',
            'buttonUrl' => BASE_URL . '/dashboard',
        ]);
    }

    public function accessDenied(): void
    {
        http_response_code(403);
        Auth::requireAuth();

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('errors/error', [
            'title' => APP_NAME . ' - Accès refusé',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'statusCode' => 403,
            'statusLabel' => 'Accès refusé',
            'message' => 'Vous n’êtes pas autorisé à accéder à cette section. Contactez l’administrateur si nécessaire.',
            'buttonText' => 'Retour au dashboard',
            'buttonUrl' => BASE_URL . '/dashboard',
        ]);
    }
}
