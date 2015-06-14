<?php

// Load
include("load.php");

// Get the page we're displaying from mod_rewrite rule
$route = isset($_GET['route']) ? strtolower(trim($_GET['route'], '/')) : 'index';
$parts = explode('/', trim(strtolower($route), '/\\'));
if (!isset($parts[0])) { $parts[0] = 'public'; }

// Load controller class
$controller = file_exists(SITE_PATH . '/data/controllers/' . $parts[0] . '.php') === true ? $parts[0] : 'default';
require_once(SITE_PATH . '/data/controllers/' . $controller . '.php');

$class_name = 'controller_' . $controller;
$controller = new $class_name($parts);

// Exit
exit(0);

?>
