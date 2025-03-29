<?php
namespace App\Controllers;

use App\Models\Factura;

class FacturasController {
    private $facturaModel;

    public function __construct() {
        $this->facturaModel = new Factura();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $facturas = $this->facturaModel->getAll();
        require_once __DIR__ . '/../views/facturas/index.php';
    }
}