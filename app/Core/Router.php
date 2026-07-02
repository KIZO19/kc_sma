<?php

namespace App\Core;

class Router
{
    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';

        if ($basePath !== '' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = $uri === '' ? '/' : $uri;
        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));

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
        } elseif ($segments[0] === 'ecoles') {
            $controllerName = 'Ecoles';
            $actionName = $segments[1] ?? 'index';
        } elseif ($segments[0] === 'profile') {
            $controllerName = 'Profile';
            $actionName = $segments[1] ?? 'index';
        } else {
            $controllerName = ucfirst($segments[0]);
            $actionName = $segments[1] ?? $defaultAction;
        }

        $controllerClass = 'App\\Controllers\\' . $controllerName . 'Controller';

        if (!class_exists($controllerClass)) {
            $controllerClass = 'App\\Controllers\\HomeController';
            $actionName = 'index';
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $actionName)) {
            $actionName = 'index';
        }

        $controller->{$actionName}();
    }
}
