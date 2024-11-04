<?php
session_start();
include_once("configuration/Configuration.php");
date_default_timezone_set('America/Argentina/Buenos_Aires');

$configuration = new Configuration();
$router = $configuration->getRouter();
// Page --> Controlador //Action --> Method
$page = isset($_GET["page"]) ? $_GET["page"] : "auth";
$action = isset($_GET["action"]) ? $_GET["action"] : "init";
$router->route($page, $action);
