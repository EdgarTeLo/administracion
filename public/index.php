<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

$request = $_SERVER['REQUEST_URI'];
$base_url = parse_url($_ENV['APP_URL'], PHP_URL_PATH);
$uri = str_replace($base_url, '', $request);
$uri = trim($uri, '/');

switch ($uri) {
    case '':
    case 'login':
        require __DIR__ . '/../controllers/AuthController.php';
        $controller = new App\Controllers\AuthController();
        $controller->login();
        break;
    case 'logout':
        require __DIR__ . '/../controllers/AuthController.php';
        $controller = new App\Controllers\AuthController();
        $controller->logout();
        break;
    case 'dashboard':
        require __DIR__ . '/../controllers/AuthController.php';
        $controller = new App\Controllers\AuthController();
        $controller->dashboard();
        break;
    case 'facturas':
        require __DIR__ . '/../controllers/FacturasController.php';
        $controller = new App\Controllers\FacturasController();
        $controller->index();
        break;
    case 'personal':
        require __DIR__ . '/../controllers/PersonalController.php';
        $controller = new App\Controllers\PersonalController();
        $controller->index();
        break;
    default:
        http_response_code(404);
        echo "404 - Página no encontrada";
        break;
}