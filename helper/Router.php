<?php

class Router
{
    private $defaultController;
    private $defaultMethod;
    private $configuration;
    private $authHelper;

    public function __construct($configuration, $defaultController, $defaultMethod, $authHelper)
    {
        $this->defaultController = $defaultController;
        $this->defaultMethod = $defaultMethod;
        $this->configuration = $configuration;
        $this->authHelper = $authHelper;
    }

    public function route($controllerName, $methodName)
    {
        $publicRoutes = ['auth' => ["init", "initLogin", 'login', 'register', "logout", "validateEmail"]];

        $defaultRoutesByRole = [
            1 => ['controller' => 'admin', 'method' => 'home'], //admin
            2 => ['controller' => 'game', 'method' => 'lobby'], // user
            3 => ['controller' => 'admin', 'method' => 'homeEdit'],// editor
        ];

        $isAuthenticated = $this->authHelper->isAuthenticated();
        $userRole = $isAuthenticated ? $this->authHelper->getRolId() : null;

        if (!$controllerName || !$methodName) {
            $this->redirectUserToDefault($userRole, $defaultRoutesByRole);
            return;
        }

        if ($controllerName === 'auth' && $methodName === 'logout') {
            $controller = $this->getControllerFrom($controllerName);
            $this->executeMethodFromController($controller, $methodName);
            return;
        }

        if (isset($publicRoutes[$controllerName]) && in_array($methodName, $publicRoutes[$controllerName])) {
            if (!$isAuthenticated) {
                $controller = $this->getControllerFrom($controllerName);
                $this->executeMethodFromController($controller, $methodName);
                return;
            }
            $this->redirectUserToDefault($userRole, $defaultRoutesByRole);
            return;
        }

        if (!isset($publicRoutes[$controllerName]) && !$isAuthenticated) {
            $controller = $this->getControllerFrom('auth');
            $this->executeMethodFromController($controller, "initLogin");
            return;
        }

        if (!$this->isAuthorizedForRoute($userRole, $controllerName, $methodName)) {
            $this->redirectUserToDefault($userRole, $defaultRoutesByRole);
            return;
        }

        $controller = $this->getControllerFrom($controllerName);
        $this->executeMethodFromController($controller, $methodName);
    }

    private function redirectUserToDefault($userRole, $defaultRoutesByRole)
    {
        if (isset($defaultRoutesByRole[$userRole])) {
            $defaultController = $defaultRoutesByRole[$userRole]['controller'];
            $defaultMethod = $defaultRoutesByRole[$userRole]['method'];
        } else {
            $defaultController = 'auth';
            $defaultMethod = 'initLogin';
        }

        $url = "http://localhost/PreguntasYRespuestasPHP/$defaultController/$defaultMethod";
        header("Location: $url");
        exit;
    }

    private function isAuthorizedForRoute($userRole, $controllerName, $methodName)
    {
        $rolePermissions = [
            1 => ['admin' => ['home']],
            2 => ['game' => [
                'play', "finish", "findQuestions",
                "sendQuestion", 'lobby',
                "reportQuestion", 'suggestQuestion',
                'suggestQuestionPost',
            ],
                'ranking' => ['rankingPosition', 'profile']
            ],
            3 => ['admin' => ['homeEdit', "createQuestion", "createQuestionPost", "deleteQuestion", "approveQuestion"]],
        ];


        return isset($rolePermissions[$userRole][$controllerName]) &&
            in_array($methodName, $rolePermissions[$userRole][$controllerName]);
    }


    private function getControllerFrom($module)
    {
        $controllerName = 'get' . ucfirst($module) . 'Controller';
        $validController = method_exists($this->configuration, $controllerName) ? $controllerName : $this->defaultController;
        return call_user_func(array($this->configuration, $validController));
    }

    private function executeMethodFromController($controller, $method)
    {
        $validMethod = method_exists($controller, $method) ? $method : $this->defaultMethod;
        call_user_func(array($controller, $validMethod));
    }
}