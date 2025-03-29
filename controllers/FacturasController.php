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

    public function upload() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $fileType = $_POST['file_type'] ?? '';
                $fileTmpPath = $_FILES['file']['tmp_name'];

                if ($fileType === 'xml') {
                    $success = $this->facturaModel->processXml($fileTmpPath);
                    $message = $success ? "Factura procesada exitosamente." : "Error al procesar el archivo XML.";
                } elseif ($fileType === 'csv') {
                    $success = $this->facturaModel->processCsv($fileTmpPath);
                    $message = $success ? "Orden de compra procesada exitosamente." : "Error al procesar el archivo CSV.";
                } else {
                    $success = false;
                    $message = "Tipo de archivo no válido.";
                }
            } else {
                $success = false;
                $message = "Error al subir el archivo.";
            }
        }

        require_once __DIR__ . '/../views/facturas/upload.php';
    }
}