<?php
require_once "../controllers/StatusController.php";
$controller = new StatusController();
$method = $_SERVER['REQUEST_METHOD'];
$num = $_GET['num'] ?? null;
$controller->handleRequest($method, $num);