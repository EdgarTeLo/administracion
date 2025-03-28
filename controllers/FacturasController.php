<?php
namespace App\Controllers;

class FacturasController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        require_once __DIR__ . '/../views/facturas/index.php';
    }
}