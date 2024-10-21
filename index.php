<?php
session_start();
include_once("configuration/Configuration.php");
$configuration = new Configuration();
$router = $configuration->getRouter();
// Page --> Controlador //Action --> Method
$page = isset($_GET["page"]) ? $_GET["page"] : "auth";
$action = isset($_GET["action"]) ? $_GET["action"] : "init";
$router->route($page, $action);