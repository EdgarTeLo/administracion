<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\FacturasController;
use App\Controllers\PersonalController;

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
        $controller = new AuthController();
        $controller->login();
        break;
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
    case 'dashboard':
        $controller = new AuthController();
        $controller->dashboard();
        break;
    case 'facturas':
        $controller = new FacturasController();
        $controller->index();
        break;
    case 'facturas/upload':
        $controller = new FacturasController();
        $controller->upload();
        break;
    case 'personal':
        $controller = new PersonalController();
        $controller->index();
        break;
    default:
        http_response_code(404);
        echo "404 - Página no encontrada";
        break;
}