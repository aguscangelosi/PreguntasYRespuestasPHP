<?php
session_start();
include_once("configuration/Configuration.php");
$configuration = new Configuration();
$router = $configuration->getRouter();

$page = isset($_GET["page"]) ? $_GET["page"] : "login";
$action = isset($_GET["action"]) ? $_GET["action"] : "init";
$router->route($page, $action);
