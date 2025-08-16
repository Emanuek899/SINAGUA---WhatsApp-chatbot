<?php
// Mostrar errores para depurar
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo donde está la clase PeliculasController
require_once '../controllers/UsersController.php';

// Crear instancia
$controller = new UserController();

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener id de GET (si existe)
$id = $_GET['id'] ?? null;
$accountId = $_GET['account'] ?? null;

// Llamar a handleRequest con método e id
$controller->handleRequest($method, $id, $accountId);
