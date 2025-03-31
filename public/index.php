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

$uriParts = explode('/', $uri);
$route = $uriParts[0] ?? '';

switch ($route) {
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
        if (isset($uriParts[1])) {
            if ($uriParts[1] === 'upload') {
                $controller->upload();
            } elseif ($uriParts[1] === 'ordenes') {
                if (isset($uriParts[2]) && $uriParts[2] === 'detalle' && isset($uriParts[3])) {
                    $controller->detalleOrden($uriParts[3]);
                } elseif (isset($uriParts[2]) && $uriParts[2] === 'editar_orden' && isset($uriParts[3])) {
                    $controller->editarOrden($uriParts[3]);
                } elseif (isset($uriParts[2]) && $uriParts[2] === 'eliminar_orden' && isset($uriParts[3])) {
                    $controller->eliminarOrden($uriParts[3]);
                } else {
                    $controller->ordenesCompra();
                }
            } elseif ($uriParts[1] === 'detalle' && isset($uriParts[2])) {
                $controller->detalle($uriParts[2]);
            } elseif ($uriParts[1] === 'asociar' && isset($uriParts[2])) {
                $controller->asociar($uriParts[2]);
            } elseif ($uriParts[1] === 'crear') {
                $controller->crear();
            } elseif ($uriParts[1] === 'crear_orden') {
                $controller->crearOrden();
            } elseif ($uriParts[1] === 'editar' && isset($uriParts[2])) {
                $controller->editar($uriParts[2]);
            } elseif ($uriParts[1] === 'eliminar' && isset($uriParts[2])) {
                $controller->eliminar($uriParts[2]);
            } else {
                http_response_code(404);
                echo "404 - Página no encontrada";
            }
        } else {
            $controller->index();
        }
        break;
    case 'personal':
        $controller = new PersonalController();
        if (isset($uriParts[1])) {
            if ($uriParts[1] === 'crear') {
                $controller->crear();
            } elseif ($uriParts[1] === 'editar' && isset($uriParts[2])) {
                $controller->editar($uriParts[2]);
            } elseif ($uriParts[1] === 'eliminar' && isset($uriParts[2])) {
                $controller->eliminar($uriParts[2]);
            } else {
                http_response_code(404);
                echo "404 - Página no encontrada";
            }
        } else {
            $controller->index();
        }
        break;
    default:
        http_response_code(404);
        echo "404 - Página no encontrada";
        break;
}