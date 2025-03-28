<?php
$fileVersion = '1.0.0'; // No había FILE_VERSION en el original, pero lo añado para consistencia

require_once 'dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.0.0');

require_once 'includes/functions.php';

// session_start(); // Eliminado, ya que dependencies.php lo maneja

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$invoice = [];
$items = [];

if ($id > 0) {
    $conn = getDBConnection();
    if ($conn) {
        // Obtener los detalles de la factura
        $stmt = $conn->prepare("SELECT * FROM facturas WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $invoice = $result->fetch_assoc();
            $stmt->close();
        } else {
            error_log("Error preparando consulta de factura: " . $conn->error);
            if (DEBUG) {
                debugLog("Error preparando consulta de factura con ID $id: " . $conn->error);
            }
        }

        // Obtener los ítems asociados a la factura
        $stmt = $conn->prepare("SELECT descripcion AS descripcion_item, cantidad, clave_unidad, precio_unitario, importe, importe_iva FROM items_factura WHERE factura_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if (DEBUG) {
                debugLog("Ítems de factura: " . print_r($items, true));
            }
        } else {
            error_log("Error preparando consulta de ítems: " . $conn->error);
            if (DEBUG) {
                debugLog("Error preparando consulta de ítems para factura con ID $id: " . $conn->error);
            }
        }

        $conn->close();
    }
    if (DEBUG) {
        debugLog("Detalles de factura cargados para ID: $id - " . print_r($invoice, true));
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Factura</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-group { margin-bottom: 15px; }
        .table-responsive { margin-top: 20px; }
        .date-input { width: 150px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Detalle de Factura <?php echo htmlspecialchars($invoice['fact'] ?? 'No encontrada'); ?></h2>
        <?php if (empty($invoice)): ?>
            <div class="alert alert-danger">Factura no encontrada.</div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-12">
                    <h3>Información General</h3>
                    <p><strong>Folio Fiscal:</strong> <?php echo htmlspecialchars($invoice['folio_fiscal'] ?? '-'); ?></p>
                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars($invoice['fecha'] ?? '-'); ?></p>
                    <p><strong>Razón Social (Receptor):</strong> <?php echo htmlspecialchars($invoice['cliente'] ?? '-'); ?></p>
                    <p><strong>Orden de Compra:</strong> <?php echo htmlspecialchars($invoice['orden_compra'] ?? '-'); ?></p>
                    <p><strong>Obra:</strong> <?php echo htmlspecialchars($invoice['ubicacion'] ?? '-'); ?></p>
                    <p><strong>Subtotal:</strong> <?php echo number_format($invoice['subtotal'] ?? 0, 2, '.', ','); ?></p>
                    <p><strong>IVA:</strong> <?php echo number_format($invoice['iva'] ?? 0, 2, '.', ','); ?></p>
                    <p><strong>Total:</strong> <?php echo number_format($invoice['total'] ?? 0, 2, '.', ','); ?></p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($invoice['estado'] ?? '-'); ?></p>
                    <p><strong>Fecha de Cancelación:</strong> <?php echo htmlspecialchars($invoice['fecha_cancelacion'] ?? '-'); ?></p>
                    <p><strong>Cotización o Presupuesto:</strong> <?php echo htmlspecialchars($invoice['cotizacion'] ?? '-'); ?></p>
                    <p><strong>Fecha de Pago:</strong> <?php echo htmlspecialchars($invoice['fecha_pago'] ?? '-'); ?></p>
                    <p><strong>No. de Complemento:</strong> <?php echo htmlspecialchars($invoice['numero_pago'] ?? '-'); ?></p>
                    <p><strong>Interés NAFIN:</strong> <?php echo number_format($invoice['interes_nafin'] ?? 0, 2, '.', ','); ?></p>
                    <p><strong>Pago Recibido:</strong> <?php echo number_format($invoice['importe_pagado'] ?? 0, 2, '.', ','); ?></p>
                    <p><strong>Recibo:</strong> <?php echo htmlspecialchars($invoice['recibo'] ?? '-'); ?></p>
                    <p><strong>FACEL:</strong> <?php echo htmlspecialchars($invoice['facel'] ?? '-'); ?></p>
                    <p><strong>Fecha FACEL:</strong> <?php echo htmlspecialchars($invoice['fecha_facel'] ?? '-'); ?></p>
                    <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($invoice['observaciones'] ?? '-'); ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h3>Conceptos</h3>
                    <?php if (empty($items)): ?>
                        <div class="alert alert-warning">No hay conceptos registrados para esta factura.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Importe</th>
                                        <th>IVA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['descripcion_item'] ?? '-'); ?></td>
                                            <td><?php echo number_format($item['cantidad'] ?? 0, 2, '.', ','); ?></td>
                                            <td><?php echo htmlspecialchars($item['clave_unidad'] ?? '-'); ?></td>
                                            <td><?php echo number_format($item['precio_unitario'] ?? 0, 2, '.', ','); ?></td>
                                            <td><?php echo number_format($item['importe'] ?? 0, 2, '.', ','); ?></td>
                                            <td><?php echo number_format($item['importe_iva'] ?? 0, 2, '.', ','); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <a href="index.php" class="btn btn-secondary mt-3">Volver a la Lista</a>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <?php if (DEBUG): ?>
        <script>
            console.log("Depuración activa (DEBUG=true). Revisar logs en C:\\xampp\\php\\logs\\php_error.log.");
        </script>
    <?php endif; ?>
</body>
</html>