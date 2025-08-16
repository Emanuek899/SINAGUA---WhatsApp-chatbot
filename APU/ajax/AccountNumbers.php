<?php
require_once "../controllers/AccountNumbersController.php";
$controller = new AccountNumbersController();
$method = $_SERVER['REQUEST_METHOD'];
$num = $_GET['num'] ?? null;
$controller->handleRequest($method, $num);