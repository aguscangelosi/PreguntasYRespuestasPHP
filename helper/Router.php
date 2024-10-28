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
        $publicRoutes = ['auth' => ['login', 'register']];

        if (!isset($publicRoutes[$controllerName]) && !$this->authHelper->isAuthenticated()) {
            $controller = $this->getControllerFrom('auth');
            $this->executeMethodFromController($controller, 'Login');
            return;
        }

        $controller = $this->getControllerFrom($controllerName);
        $this->executeMethodFromController($controller, $methodName);
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