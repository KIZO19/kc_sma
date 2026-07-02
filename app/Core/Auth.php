<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(array $user): void
    {
        self::start();
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nom_complet' => $user['nom_complet'],
            'role' => $user['role'],
            'ecole_id' => isset($user['ecole_id']) ? (int) $user['ecole_id'] : null,
            'statut' => $user['statut'] ?? 'Actif',
        ];
    }

    public static function user(): ?array
    {
        self::start();

        if (empty($_SESSION['user'])) {
            return null;
        }

        return $_SESSION['user'];
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function requireAuth(): void
    {
        if (self::guest()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireRoles(array $roles): void
    {
        $user = self::user();
        if (!$user || !in_array($user['role'] ?? '', $roles, true)) {
            header('Location: ' . BASE_URL . self::getLandingPage($user['role'] ?? null));
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: ' . BASE_URL . self::getLandingPage(self::user()['role'] ?? null));
            exit;
        }
    }

    public static function getLandingPage(?string $role = null): string
    {
        return match ($role) {
            'super_admin',
            'ecole_admin',
            'préfet_école',
            'DE_école',
            'DD_école',
            'DP_école',
            'DA_école',
            'comptable_école',
            'sec_école',
            'promoteur_école',
            'enseignant_école',
            'eleve_ecole',
            'parent_ecole' => '/dashboard',
            default => '/login',
        };
    }

    public static function refresh(): ?array
    {
        $user = self::user();
        if (!$user) {
            return null;
        }

        $freshUser = User::findById($user['id']);
        if (!$freshUser) {
            self::logout();
            return null;
        }

        self::login($freshUser);
        return self::user();
    }
}
