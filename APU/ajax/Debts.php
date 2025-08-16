<?php
require_once "../controllers/DebtsController.php";
$controller = new DebtsController();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$accountId = $_GET['accountId'] ?? null;
$controller->handleRequest($method, $id, $accountId);