<?php
$fileVersion = '1.0.0'; // No había FILEופ_VERSION en el original, pero lo añado para consistencia

require_once 'dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.0.0');

require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $estado = $_POST['estado'] ?? '';
    
    if (!empty($id) && !empty($estado)) {
        $conn = getDBConnection();
        if ($conn) {
            $stmt = $conn->prepare("UPDATE facturas SET estado = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $estado, $id);
                if ($stmt->execute()) {
                    if (defined('DEBUG') && DEBUG) {
                        debugLog("Estado de la factura con ID $id actualizado a $estado con éxito.");
                    }
                } else {
                    error_log("Error al actualizar el estado: " . $stmt->error);
                    if (defined('DEBUG') && DEBUG) {
                        debugLog("Error al actualizar el estado de la factura con ID $id: " . $stmt->error);
                    }
                }
                $stmt->close();
            } else {
                error_log("Error preparando la consulta de actualización: " . $conn->error);
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Error preparando la consulta de actualización para ID $id: " . $conn->error);
                }
            }
            $conn->close();
        }
    }
    header("Location: update_status.php");
    exit;
}

// Obtener lista de facturas activas
$invoices = getInvoiceList(1000, 0, 'fact', 'DESC', ''); // Mostrar todas las facturas activas
if (defined('DEBUG') && DEBUG) {
    debugLog("Cargando lista de facturas para actualizar estados. Total: " . count($invoices));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Estados de Facturas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive { margin-top: 20px; }
        .form-select { width: auto; display: inline-block; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Actualizar Estados de Facturas</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>FACT</th>
                        <th>Folio Fiscal</th>
                        <th>Receptor</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['fact'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['folio_fiscal'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['cliente'] ?? '-'); ?></td>
                        <td><?php echo number_format($invoice['total'] ?? 0, 2); ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $invoice['id']; ?>">
                                <select name="estado" class="form-select" onchange="this.form.submit()">
                                    <option value="activa" <?php echo ($invoice['estado'] ?? 'activa') === 'activa' ? 'selected' : ''; ?>>Activa</option>
                                    <option value="cancelada" <?php echo ($invoice['estado'] ?? 'activa') === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-info btn-sm">Ver</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php" class="btn btn-secondary mt-3">Volver a la Lista</a>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <?php if (defined('DEBUG') && DEBUG): ?>
        <script>
            console.log("Depuración activa (DEBUG=true). Revisar logs in C:\\xampp\\php\\logs\\php_error.log.");
        </script>
    <?php endif; ?>
</body>
</html>