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
        if (isset($facturas['error'])) {
            $error = $facturas['error'];
            $facturas = [];
        }
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
                $fileName = $_FILES['file']['name'];
                $uploadDir = __DIR__ . '/../public/uploads/';
                $uploadPath = $uploadDir . basename($fileName);

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
                    $success = false;
                    $message = "Error al mover el archivo al directorio de uploads.";
                } else {
                    if ($fileType === 'xml') {
                        $result = $this->facturaModel->processXml($uploadPath);
                        if ($result === true) {
                            $success = true;
                            $message = "Factura procesada exitosamente. Archivo guardado en: uploads/" . basename($fileName);
                        } else {
                            $success = false;
                            $message = "Error al procesar el archivo XML: " . $result;
                        }
                    } elseif ($fileType === 'csv') {
                        $result = $this->facturaModel->processCsv($uploadPath);
                        if ($result === true) {
                            $success = true;
                            $message = "Orden de compra procesada exitosamente. Archivo guardado en: uploads/" . basename($fileName);
                        } else {
                            $success = false;
                            $message = "Error al procesar el archivo CSV: " . $result;
                        }
                    } else {
                        $success = false;
                        $message = "Tipo de archivo no válido.";
                    }
                }
            } else {
                $success = false;
                $message = "Error al subir el archivo.";
            }
        }

        require_once __DIR__ . '/../views/facturas/upload.php';
    }
}