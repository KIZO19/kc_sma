<?php

namespace App\Core;

use App\Models\User;
use App\Entities\RoleFactory;

class Auth
{
    public static function start(): void
    {
        $sessionStatus = session_status();
        if ($sessionStatus === PHP_SESSION_ACTIVE) {
            return;
        }
        if ($sessionStatus === PHP_SESSION_DISABLED) {
            throw new \RuntimeException('Sessions are disabled in the PHP configuration.');
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_cookies', '1');

        $isHttps = (
            !empty($_SERVER['HTTPS'])
            && strtolower((string) $_SERVER['HTTPS']) !== 'off'
        ) || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

        if (!session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ])) {
            throw new \RuntimeException('Unable to configure session cookie parameters.');
        }

        if (!session_start()) {
            throw new \RuntimeException('Unable to start the user session.');
        }
    }

    public static function login(array $user): void
    {
        self::start();
        session_regenerate_id(true);

        self::storeUser($user);
    }

    private static function storeUser(array $user): void
    {
        $role = self::canonicalizeRole((string) ($user['role'] ?? ''));
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nom_complet' => $user['nom_complet'],
            'identifiant' => $user['identifiant'] ?? '',
            'role' => $role,
            'ecole_id' => isset($user['ecole_id']) ? (int) $user['ecole_id'] : null,
            'section_id' => isset($user['section_id']) ? (int) $user['section_id'] : null,
            'reference_id' => isset($user['reference_id']) ? (int) $user['reference_id'] : null,
            'statut' => $user['statut'] ?? 'Actif',
        ];
    }

    private static function canonicalizeRole(string $role): string
    {
        $normalized = strtolower(strtr(trim($role), [
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
        ]));
        $normalized = preg_replace('/[\s-]+/', '_', $normalized) ?? $normalized;

        return match ($normalized) {
            'prefet_ecole', 'prefet_des_etudes' => 'préfet_école',
            default => $role,
        };
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

    public static function getSchoolId(): ?int
    {
        $user = self::user();
        return isset($user['ecole_id']) && $user['ecole_id'] !== null ? (int) $user['ecole_id'] : null;
    }

    public static function getSchoolSlug(): ?string
    {
        $ecoleId = self::getSchoolId();
        if ($ecoleId === null || $ecoleId <= 0) {
            return null;
        }

        static $schoolSlugs = [];
        if (isset($schoolSlugs[$ecoleId])) {
            return $schoolSlugs[$ecoleId];
        }

        $school = \App\Models\Ecole::findById($ecoleId);
        $schoolName = trim((string) ($school['nom_etablissement'] ?? ''));
        $transliteratedName = function_exists('iconv')
            ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $schoolName)
            : $schoolName;
        $schoolNameSlug = strtolower((string) ($transliteratedName === false ? '' : $transliteratedName));
        $schoolNameSlug = preg_replace('/[^a-z0-9]+/', '-', $schoolNameSlug) ?? '';
        $schoolNameSlug = trim($schoolNameSlug, '-');
        $schoolSlug = ($schoolNameSlug !== '' ? $schoolNameSlug : 'ecole') . '-ecole-' . $ecoleId;

        $schoolSlugs[$ecoleId] = $schoolSlug;
        return $schoolSlug;
    }

    public static function getApplicationBasePath(): string
    {
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        $applicationPath = dirname(rtrim($basePath, '/'));

        return $applicationPath === '/' || $applicationPath === '.'
            ? ''
            : rtrim($applicationPath, '/');
    }

    public static function getSchoolRouteBaseUrl(): ?string
    {
        $schoolSlug = self::getSchoolSlug();
        if ($schoolSlug === null) {
            return null;
        }

        return self::getApplicationBasePath() . '/' . $schoolSlug;
    }

    public static function requireSchoolScope(int $ecoleId): void
    {
        if (self::guest()) {
            self::requireAuth();
            return;
        }

        if ((self::user()['role'] ?? '') === 'super_admin') {
            return;
        }

        $currentSchoolId = self::getSchoolId();
        if ($currentSchoolId !== null && $currentSchoolId !== $ecoleId) {
            header('Location: ' . BASE_URL . '/error/accessDenied');
            exit;
        }
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
            header('Location: ' . BASE_URL . '/Ecole/landing');
            exit;
        }

        $user = self::user();
        if (($user['role'] ?? '') === 'super_admin') {
            return;
        }

        self::requireActiveSubscription($user);
    }

    private static function requireActiveSubscription(array $user): void
    {
        if (($user['role'] ?? '') === 'super_admin') {
            return;
        }

        $ecoleId = (int) ($user['ecole_id'] ?? 0);
        $isPendingUnassignedAgent = ($user['role'] ?? '') === 'agent_ecole'
            && ($user['statut'] ?? '') === 'Inactif'
            && $ecoleId <= 0;
        if (
            $isPendingUnassignedAgent
            || self::isSubscriptionAccessRoute()
            || ($ecoleId > 0 && \App\Models\Ecole::hasPlatformAccess($ecoleId))
        ) {
            return;
        }

        header('Location: ' . BASE_URL . '/ecoles/subscriptionRequired');
        exit;
    }

    private static function isSubscriptionAccessRoute(): bool
    {
        return in_array(
            self::currentRoutePath(),
            [
                '/logout',
                '/ecoles/subscriptionRequired',
                '/ecoles/subscriptions',
                '/ecoles/submitSubscriptionRequest',
                '/contact',
                '/contact/send',
            ],
            true
        );
    }

    private static function currentRoutePath(): string
    {
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        if ($basePath !== '' && strpos($uriPath, $basePath) === 0) {
            $uriPath = substr($uriPath, strlen($basePath));
        }

        return rtrim($uriPath, '/') ?: '/';
    }

    public static function requireRoles(array $roles): void
    {
        $user = self::user();
        if (!$user) {
            header('Location: ' . BASE_URL . self::getLandingPage(null));
            exit;
        }

        if (($user['role'] ?? '') === 'super_admin') {
            return;
        }

        if (!in_array($user['role'] ?? '', $roles, true)) {
            if (self::check()) {
                header('Location: ' . BASE_URL . '/error/accessDenied');
            } else {
                header('Location: ' . BASE_URL . self::getLandingPage($user['role'] ?? null));
            }
            exit;
        }

        self::requireActiveSubscription($user);
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        if (($user['role'] ?? '') === 'super_admin') {
            return true;
        }

        $entity = RoleFactory::make($user['role'] ?? '');
        return $entity->can($permission);
    }

    public static function requirePermission(string $permission): void
    {
        if (!self::can($permission)) {
            if (self::check()) {
                header('Location: ' . BASE_URL . '/error/accessDenied');
            } else {
                header('Location: ' . BASE_URL . self::getLandingPage(self::user()['role'] ?? null));
            }
            exit;
        }
    }

    public static function requireAssignedSchool(): void
    {
        $user = self::user();
        if (!$user) {
            self::requireAuth();
            return;
        }

        $role = (string) ($user['role'] ?? '');
        $isPendingUnassignedAgent = $role === 'agent_ecole' && (($user['statut'] ?? 'Actif') === 'Inactif') && (empty($user['ecole_id']) || (int) $user['ecole_id'] === 0);

        if (($user['role'] ?? '') !== 'super_admin' && empty($user['ecole_id']) && !$isPendingUnassignedAgent) {
            $_SESSION['access_error'] = 'Vous devez être affecté(e) à une école pour accéder au dashboard.';
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }

        self::requireActiveSubscription($user);
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    public static function getLandingPage(?string $role = null): string
    {
        return match ($role) {
            'super_admin',
            'ecole_admin',
            'agent_ecole',
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
            default => '/Ecole/landing',
        };
    }

    public static function refresh(): ?array
    {
        $user = self::user();
        if (!$user || !isset($user['id']) || !is_int($user['id'])) {
            self::logout();
            return null;
        }

        $freshUser = User::findById($user['id']);
        if (!$freshUser) {
            self::logout();
            return null;
        }

        self::storeUser($freshUser);
        return self::user();
    }
}
