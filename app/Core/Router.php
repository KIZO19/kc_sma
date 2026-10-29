<?php

namespace App\Core;

class Router
{
    public function dispatch(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $applicationBasePath = Auth::getApplicationBasePath();

        if (
            $applicationBasePath !== ''
            && strpos($uri, $applicationBasePath . '/') === 0
        ) {
            $uri = substr($uri, strlen($applicationBasePath));
        }

        $uri = $uri === '' ? '/' : $uri;
        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));
        $schoolSlug = Auth::getSchoolSlug();
        $hasLegacyPublicPrefix = ($segments[0] ?? null) === 'public';
        if ($hasLegacyPublicPrefix) {
            array_shift($segments);
        }

        $requestedSchoolSlug = null;
        if (preg_match('/^[a-z0-9-]+-ecole-\d+$/', $segments[0] ?? '')) {
            $requestedSchoolSlug = array_shift($segments);
        }
        $hasSchoolPrefix = $schoolSlug !== null && $requestedSchoolSlug === $schoolSlug;
        $uri = empty($segments) ? '/' : '/' . implode('/', $segments);

        if ($hasSchoolPrefix) {
            if ($hasLegacyPublicPrefix) {
                $query = parse_url($requestUri, PHP_URL_QUERY);
                $targetUrl = Auth::getSchoolRouteBaseUrl() . $uri;
                if ($query !== null && $query !== '') {
                    $targetUrl .= '?' . $query;
                }

                header('Location: ' . $targetUrl);
                exit;
            }

            $_SERVER['REQUEST_URI'] = BASE_URL . $uri
                . (($query = parse_url($requestUri, PHP_URL_QUERY)) !== null ? '?' . $query : '');
        } elseif (
            $schoolSlug !== null
            && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)
        ) {
            $targetPath = $uri === '/' ? '/dashboard' : $uri;
            $query = parse_url($requestUri, PHP_URL_QUERY);
            $targetUrl = Auth::getSchoolRouteBaseUrl() . $targetPath;
            if ($query !== null && $query !== '') {
                $targetUrl .= '?' . $query;
            }

            header('Location: ' . $targetUrl);
            exit;
        }

        $defaultController = 'Home';
        $defaultAction = 'index';

        if (empty($segments)) {
            $controllerName = $defaultController;
            $actionName = $defaultAction;
        } elseif ($segments[0] === 'login') {
            $controllerName = 'Auth';
            $actionName = 'login';
        } elseif ($segments[0] === 'logout') {
            $controllerName = 'Auth';
            $actionName = 'logout';
        } elseif ($segments[0] === 'register') {
            $controllerName = 'Auth';
            $actionName = 'register';
        } elseif ($segments[0] === 'forgot-password') {
            $controllerName = 'Auth';
            $actionName = 'forgotPassword';
        } elseif ($segments[0] === 'dashboard') {
            $controllerName = 'Dashboard';
            $actionName = 'index';
        } elseif ($segments[0] === 'ecoles' || $segments[0] === 'Ecole') {
            $controllerName = 'Ecoles';
            $actionName = $segments[1] ?? 'index';
        } elseif ($segments[0] === 'profile') {
            $controllerName = 'Profile';
            $actionName = $segments[1] ?? 'index';
        } else {
            $controllerName = $this->formatSegment($segments[0]);
            $actionName = $this->formatSegment($segments[1] ?? $defaultAction);
        }

        $controllerClass = 'App\\Controllers\\' . $controllerName . 'Controller';
        $fallbackController = 'App\\Controllers\\ErrorController';

        if (!class_exists($controllerClass)) {
            $controllerClass = $fallbackController;
            $actionName = 'notFound';
        }

        // If an URL uses /<resource>/view (any case) but the controller implements show(), prefer that method.
        if (strtolower($actionName) === 'view') {
            if (method_exists($controllerClass, 'show')) {
                $actionName = 'show';
            }
        }

        $controller = new $controllerClass();

        if (!is_callable([$controller, $actionName])) {
            if ($controllerClass !== $fallbackController) {
                $controller = new $fallbackController();
            }
            $actionName = 'notFound';
        }

        if ((Auth::user()['role'] ?? '') === 'enseignant_école') {
            $teacherRoutes = [
                'App\\Controllers\\DashboardController' => ['index'],
                'App\\Controllers\\PresencesController' => ['index'],
                'App\\Controllers\\NotesController' => ['index'],
                'App\\Controllers\\ElevesController' => ['index', 'show'],
                'App\\Controllers\\MonEmploiController' => ['index'],
                'App\\Controllers\\MessagesController' => ['index', 'open'],
                'App\\Controllers\\ProfileController' => ['index', 'update'],
                'App\\Controllers\\AuthController' => ['logout'],
                'App\\Controllers\\EcolesController' => ['subscriptionrequired'],
            ];
            if (!in_array(strtolower($actionName), $teacherRoutes[$controllerClass] ?? [], true)) {
                header('Location: ' . BASE_URL . '/dashboard');
                exit;
            }
        }

        $controller->{$actionName}();
    }

    private function formatSegment(string $segment): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $segment)));
    }
}
