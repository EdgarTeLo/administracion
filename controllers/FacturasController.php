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

        $cliente = $_GET['cliente'] ?? '';
        $estado = $_GET['estado'] ?? 'activa';
        $facturas = $this->facturaModel->getAll($cliente, $estado);
        if (isset($facturas['error'])) {
            $error = $facturas['error'];
            $facturas = [];
        }
        require_once __DIR__ . '/../views/facturas/index.php';
    }

    public function crear() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $ordenes = $this->facturaModel->getAllOrdenesCompra();
        if (isset($ordenes['error'])) {
            $error = $ordenes['error'];
            $ordenes = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $factura = [
                'fecha' => $_POST['fecha'] ?? '0000-01-01',
                'fact' => $_POST['fact'] ?? '',
                'folio_fiscal' => $_POST['folio_fiscal'] ?? '',
                'cliente' => $_POST['cliente'] ?? '',
                'subtotal' => (float)($_POST['subtotal'] ?? 0.00),
                'iva' => (float)($_POST['iva'] ?? 0.00),
                'total' => (float)($_POST['total'] ?? 0.00),
                'fecha_pago' => $_POST['fecha_pago'] ?: null,
                'estado' => $_POST['estado'] ?? 'activa',
                'orden_compra' => $_POST['orden_compra'] ?: null
            ];

            $result = $this->facturaModel->crearFactura($factura);
            if ($result === true) {
                $success = true;
                $message = "Factura creada exitosamente.";
            } else {
                $success = false;
                $message = "Error al crear la factura: " . $result;
            }
        }

        require_once __DIR__ . '/../views/facturas/crear.php';
    }

    public function editar($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $factura = $this->facturaModel->getById($id);
        if (isset($factura['error'])) {
            $error = $factura['error'];
            $factura = [];
        }

        $ordenes = $this->facturaModel->getAllOrdenesCompra();
        if (isset($ordenes['error'])) {
            $error = $ordenes['error'];
            $ordenes = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $facturaData = [
                'fecha' => $_POST['fecha'] ?? '0000-01-01',
                'fact' => $_POST['fact'] ?? '',
                'folio_fiscal' => $_POST['folio_fiscal'] ?? '',
                'cliente' => $_POST['cliente'] ?? '',
                'subtotal' => (float)($_POST['subtotal'] ?? 0.00),
                'iva' => (float)($_POST['iva'] ?? 0.00),
                'total' => (float)($_POST['total'] ?? 0.00),
                'fecha_pago' => $_POST['fecha_pago'] ?: null,
                'estado' => $_POST['estado'] ?? 'activa',
                'orden_compra' => $_POST['orden_compra'] ?: null
            ];

            $result = $this->facturaModel->editarFactura($id, $facturaData);
            if ($result === true) {
                $success = true;
                $message = "Factura actualizada exitosamente.";
            } else {
                $success = false;
                $message = "Error al actualizar la factura: " . $result;
            }
        }

        require_once __DIR__ . '/../views/facturas/editar.php';
    }

    public function eliminar($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $result = $this->facturaModel->eliminarFactura($id);
        if ($result === true) {
            header("Location: " . $_ENV['APP_URL'] . "/facturas?message=Factura eliminada exitosamente");
            exit();
        } else {
            header("Location: " . $_ENV['APP_URL'] . "/facturas?error=" . urlencode($result));
            exit();
        }
    }

    public function crearOrden() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenCompra = [
                'numero_oc' => $_POST['numero_oc'] ?? '',
                'total' => (float)($_POST['total'] ?? 0.00),
                'fecha_emision' => $_POST['fecha_emision'] ?? '0000-01-01',
                'proveedor' => $_POST['proveedor'] ?? ''
            ];

            $result = $this->facturaModel->crearOrdenCompra($ordenCompra);
            if ($result === true) {
                $success = true;
                $message = "Orden de compra creada exitosamente.";
            } else {
                $success = false;
                $message = "Error al crear la orden de compra: " . $result;
            }
        }

        require_once __DIR__ . '/../views/facturas/crear_orden.php';
    }

    public function editarOrden($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $orden = $this->facturaModel->getOrdenById($id);
        if (isset($orden['error'])) {
            $error = $orden['error'];
            $orden = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenCompra = [
                'numero_oc' => $_POST['numero_oc'] ?? '',
                'total' => (float)($_POST['total'] ?? 0.00),
                'fecha_emision' => $_POST['fecha_emision'] ?? '0000-01-01',
                'proveedor' => $_POST['proveedor'] ?? ''
            ];

            $result = $this->facturaModel->editarOrdenCompra($id, $ordenCompra);
            if ($result === true) {
                $success = true;
                $message = "Orden de compra actualizada exitosamente.";
            } else {
                $success = false;
                $message = "Error al actualizar la orden de compra: " . $result;
            }
        }

        require_once __DIR__ . '/../views/facturas/editar_orden.php';
    }

    public function eliminarOrden($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $result = $this->facturaModel->eliminarOrdenCompra($id);
        if ($result === true) {
            header("Location: " . $_ENV['APP_URL'] . "/facturas/ordenes?message=Orden de compra eliminada exitosamente");
            exit();
        } else {
            header("Location: " . $_ENV['APP_URL'] . "/facturas/ordenes?error=" . urlencode($result));
            exit();
        }
    }

    public function detalle($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $factura = $this->facturaModel->getById($id);
        if (isset($factura['error'])) {
            $error = $factura['error'];
            $factura = [];
            $items = [];
        } else {
            $items = $this->facturaModel->getItemsByFacturaId($id);
            if (isset($items['error'])) {
                $error = $items['error'];
                $items = [];
            }
        }
        require_once __DIR__ . '/../views/facturas/detalle.php';
    }

    public function ordenesCompra() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $ordenes = $this->facturaModel->getAllOrdenesCompra();
        if (isset($ordenes['error'])) {
            $error = $ordenes['error'];
            $ordenes = [];
        }

        if (isset($_GET['message'])) {
            $message = $_GET['message'];
            $success = true;
        }
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $success = false;
        }

        require_once __DIR__ . '/../views/facturas/ordenes_compra.php';
    }

    public function detalleOrden($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $orden = $this->facturaModel->getOrdenById($id);
        if (isset($orden['error'])) {
            $error = $orden['error'];
            $orden = [];
            $items = [];
        } else {
            $items = $this->facturaModel->getItemsByOrdenId($id);
            if (isset($items['error'])) {
                $error = $items['error'];
                $items = [];
            }
        }
        require_once __DIR__ . '/../views/facturas/detalle_orden.php';
    }

    public function asociar($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $factura = $this->facturaModel->getById($id);
        if (isset($factura['error'])) {
            $error = $factura['error'];
            $factura = [];
        }

        $ordenes = $this->facturaModel->getAllOrdenesCompra();
        if (isset($ordenes['error'])) {
            $error = $ordenes['error'];
            $ordenes = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ordenCompra = $_POST['orden_compra'] ?? null;
            if ($ordenCompra) {
                $result = $this->facturaModel->asociarOrdenCompra($id, $ordenCompra);
                if ($result === true) {
                    $success = true;
                    $message = "Factura asociada exitosamente con la orden de compra.";
                } else {
                    $success = false;
                    $message = "Error al asociar la factura: " . $result;
                }
            } else {
                $success = false;
                $message = "Debes seleccionar una orden de compra.";
            }
        }

        require_once __DIR__ . '/../views/facturas/asociar.php';
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
    
                // Validar la extensión del archivo según el tipo seleccionado
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = [
                    'xml' => ['xml'],
                    'csv' => ['csv'],
                    'pdf' => ['pdf'],
                    'csv_facturas' => ['csv']
                ];
    
                if (!isset($allowedExtensions[$fileType]) || !in_array($fileExtension, $allowedExtensions[$fileType])) {
                    $success = false;
                    $message = "El archivo debe tener la extensión " . implode(' o ', $allowedExtensions[$fileType]) . " para el tipo seleccionado ($fileType).";
                } else {
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
                                $message = "Orden de compra procesada exitosamente (CSV). Archivo guardado en: uploads/" . basename($fileName);
                            } else {
                                $success = false;
                                $message = "Error al procesar el archivo CSV: " . $result;
                            }
                        } elseif ($fileType === 'pdf') {
                            $result = $this->facturaModel->processPdf($uploadPath);
                            if ($result === true) {
                                $success = true;
                                $message = "Orden de compra procesada exitosamente (PDF). Archivo guardado en: uploads/" . basename($fileName);
                            } else {
                                $success = false;
                                $message = "Error al procesar el archivo PDF: " . $result;
                            }
                        } elseif ($fileType === 'csv_facturas') {
                            $result = $this->facturaModel->processCsvFacturas($uploadPath);
                            if ($result === true) {
                                $success = true;
                                $message = "Facturas procesadas exitosamente (CSV). Archivo guardado en: uploads/" . basename($fileName);
                            } else {
                                $success = false;
                                $message = "Error al procesar el archivo CSV de facturas: " . $result;
                            }
                        } else {
                            $success = false;
                            $message = "Tipo de archivo no válido.";
                        }
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