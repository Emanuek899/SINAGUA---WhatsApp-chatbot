<?php
require_once "../controllers/PaymentsController.php";

$controller = new PaymentsController();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$controller->handleRequest($method, $id);