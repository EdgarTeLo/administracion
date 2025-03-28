<?php
$fileVersion = '1.1.0'; // Reemplaza define('FILE_VERSION', '1.1.0')

require_once 'dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.1.0');

require_once 'includes/functions.php';

// session_start(); // Eliminado, ya que dependencies.php lo maneja

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$invoice = [];

if ($id > 0) {
    // Obtener los datos de la factura específica
    $conn = getDBConnection();
    if ($conn) {
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

        // Obtener los ítems asociados a la factura (si existen)
        $stmt = $conn->prepare("SELECT descripcion, cantidad, clave_unidad, precio_unitario, importe, importe_iva FROM items_factura WHERE factura_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $invoice['items'] = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if (DEBUG) {
                debugLog("Ítems cargados para factura ID $id: " . print_r($invoice['items'], true));
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
        debugLog("Cargando datos de factura con ID $id para edición: " . print_r($invoice, true));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y limpiar cualquier salida no deseada antes de la redirección
    ob_start();
    $id = $_POST['id'] ?? '';
    $orden_compra = $_POST['orden_compra'] ?? '';
    $cotizacion = $_POST['cotizacion'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $fecha_pago = !empty($_POST['fecha_pago']) ? date('Y-m-d', strtotime($_POST['fecha_pago'])) : null;
    $numero_pago = $_POST['numero_pago'] ?? '';
    $fecha_cancelacion = !empty($_POST['fecha_cancelacion']) ? date('Y-m-d', strtotime($_POST['fecha_cancelacion'])) : null;
    $estado_cancelacion = $_POST['estado_cancelacion'] ?? '';
    
    // Limpiar y validar datos numéricos, manejando posibles errores de formato
    $interes_nafin = $_POST['interes_nafin'] ?? '0.00';
    $interes_nafin = str_replace([' ', ','], '', $interes_nafin); // Eliminar espacios y comas
    $interes_nafin = floatval($interes_nafin) ?: 0.00; // Convertir a float, usar 0.00 si falla
    
    $importe_pagado = $_POST['importe_pagado'] ?? '0.00';
    $importe_pagado = str_replace([' ', ','], '', $importe_pagado); // Eliminar espacios y comas
    $importe_pagado = floatval($importe_pagado) ?: 0.00; // Convertir a float, usar 0.00 si falla
    
    $observaciones = $_POST['observaciones'] ?? '';
    $recibo = $_POST['recibo'] ?? '';
    $facel = $_POST['facel'] ?? '';
    $fecha_facel = !empty($_POST['fecha_facel']) ? date('Y-m-d', strtotime($_POST['fecha_facel'])) : null;

    // Capturar ítems (conceptos)
    $items = [];
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            $descripcion_item = $item['descripcion'] ?? '';
            $cantidad = floatval(str_replace([' ', ','], '', $item['cantidad'] ?? '0.00')) ?: 0.00;
            $clave_unidad = $item['clave_unidad'] ?? '';
            $precio_unitario = floatval(str_replace([' ', ','], '', $item['precio_unitario'] ?? '0.00')) ?: 0.00;
            $importe = floatval(str_replace([' ', ','], '', $item['importe'] ?? '0.00')) ?: 0.00;
            $importe_iva = floatval(str_replace([' ', ','], '', $item['importe_iva'] ?? '0.00')) ?: 0.00;

            if (!empty($descripcion_item) && $cantidad > 0 && !empty($clave_unidad) && $precio_unitario > 0) {
                $items[] = [
                    'descripcion' => $descripcion_item,
                    'cantidad' => $cantidad,
                    'clave_unidad' => $clave_unidad,
                    'precio_unitario' => $precio_unitario,
                    'importe' => $importe,
                    'importe_iva' => $importe_iva
                ];
            }
        }
    }

    if (DEBUG) {
        debugLog("Ítems recibidos via POST para actualizar factura ID $id: " . print_r($items, true));
    }

    if (!empty($id)) {
        $conn = getDBConnection();
        if ($conn) {
            // Actualizar la factura
            $sql = sprintf(
                "UPDATE facturas SET 
                    orden_compra = '%s', 
                    cotizacion = '%s', 
                    descripcion = '%s', 
                    ubicacion = '%s', 
                    fecha_pago = %s, 
                    numero_pago = '%s', 
                    fecha_cancelacion = %s, 
                    estado = '%s', 
                    importe_pagado = %.2f, 
                    observaciones = '%s', 
                    recibo = '%s', 
                    facel = '%s', 
                    fecha_facel = %s, 
                    interes_nafin = %.2f 
                    WHERE id = %d",
                $conn->real_escape_string($orden_compra),
                $conn->real_escape_string($cotizacion),
                $conn->real_escape_string($descripcion),
                $conn->real_escape_string($ubicacion),
                ($fecha_pago !== null ? "'" . $conn->real_escape_string($fecha_pago) . "'" : 'NULL'),
                $conn->real_escape_string($numero_pago),
                ($fecha_cancelacion !== null ? "'" . $conn->real_escape_string($fecha_cancelacion) . "'" : 'NULL'),
                $conn->real_escape_string($estado_cancelacion),
                floatval($importe_pagado),
                $conn->real_escape_string($observaciones),
                $conn->real_escape_string($recibo),
                $conn->real_escape_string($facel),
                ($fecha_facel !== null ? "'" . $conn->real_escape_string($fecha_facel) . "'" : 'NULL'),
                floatval($interes_nafin),
                $id
            );

            if (DEBUG) {
                debugLog("Consulta SQL generada para actualizar factura ID $id: $sql");
            }

            if ($conn->query($sql)) {
                if (DEBUG) {
                    debugLog("Datos de la factura con ID $id actualizados con éxito.");
                }

                // Insertar o actualizar ítems en items_factura
                if (!empty($items)) {
                    // Primero, eliminar ítems existentes para evitar duplicados (según tu lógica)
                    $conn->query("DELETE FROM items_factura WHERE factura_id = $id");

                    foreach ($items as $item) {
                        $insert_sql = sprintf(
                            "INSERT INTO items_factura (factura_id, descripcion, cantidad, clave_unidad, precio_unitario, importe, importe_iva) 
                             VALUES (%d, '%s', %.2f, '%s', %.2f, %.2f, %.2f)",
                            $id,
                            $conn->real_escape_string($item['descripcion']),
                            $item['cantidad'],
                            $conn->real_escape_string($item['clave_unidad']),
                            $item['precio_unitario'],
                            $item['importe'],
                            $item['importe_iva']
                        );
                        if ($conn->query($insert_sql)) {
                            if (DEBUG) {
                                debugLog("Ítem insertado con éxito para factura ID $id: " . print_r($item, true));
                            }
                        } else {
                            error_log("Error al insertar ítem: " . $conn->error);
                            if (DEBUG) {
                                debugLog("Error al insertar ítem para factura ID $id: " . $conn->error);
                            }
                        }
                    }
                }
            } else {
                error_log("Error al actualizar los datos: " . $conn->error);
                if (DEBUG) {
                    debugLog("Error al actualizar los datos de la factura con ID $id: " . $conn->error);
                }
            }
            $conn->close();
        }
    }
    // Limpiar salida y redirigir
    ob_end_clean();
    header("Location: edit_purchase_order.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Datos de Factura</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-control-sm { width: 100%; max-width: 300px; }
        .date-input { width: 150px; }
        .item-row { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Editar Datos de Factura</h2>
        <?php if (empty($invoice)): ?>
            <div class="alert alert-danger">Factura no encontrada.</div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $invoice['id']; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fact">FACT:</label>
                            <input type="text" id="fact" name="fact" value="<?php echo htmlspecialchars($invoice['fact'] ?? '-'); ?>" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="form-group">
                            <label for="folio_fiscal">Folio Fiscal:</label>
                            <input type="text" id="folio_fiscal" name="folio_fiscal" value="<?php echo htmlspecialchars($invoice['folio_fiscal'] ?? '-'); ?>" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="form-group">
                            <label for="orden_compra">Orden de Compra:</label>
                            <input type="text" id="orden_compra" name="orden_compra" value="<?php echo htmlspecialchars($invoice['orden_compra'] ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label for="cotizacion">Cotización o Presupuesto:</label>
                            <input type="text" id="cotizacion" name="cotizacion" value="<?php echo htmlspecialchars($invoice['cotizacion'] ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción General:</label>
                            <textarea id="descripcion" name="descripcion" class="form-control form-control-sm" rows="3"><?php echo htmlspecialchars($invoice['descripcion'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="ubicacion">Obra:</label>
                            <input type="text" id="ubicacion" name="ubicacion" value="<?php echo htmlspecialchars($invoice['ubicacion'] ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_pago">Fecha de Pago:</label>
                            <input type="date" id="fecha_pago" name="fecha_pago" value="<?php echo htmlspecialchars($invoice['fecha_pago'] ?? ''); ?>" class="form-control form-control-sm date-input">
                        </div>
                        <div class="form-group">
                            <label for="numero_pago">No. de Complemento:</label>
                            <input type="text" id="numero_pago" name="numero_pago" value="<?php echo htmlspecialchars($invoice['numero_pago'] ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label for="fecha_cancelacion">Fecha de Cancelación:</label>
                            <input type="date" id="fecha_cancelacion" name="fecha_cancelacion" value="<?php echo htmlspecialchars($invoice['fecha_cancelacion'] ?? ''); ?>" class="form-control form-control-sm date-input">
                        </div>
                        <div class="form-group">
                            <label for="estado_cancelacion">Estado de Cancelación:</label>
                            <select id="estado_cancelacion" name="estado_cancelacion" class="form-select form-select-sm">
                                <option value="activa" <?php echo ($invoice['estado_cancelacion'] ?? 'activa') === 'activa' ? 'selected' : ''; ?>>Activa</option>
                                <option value="cancelada" <?php echo ($invoice['estado_cancelacion'] ?? 'activa') === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="interes_nafin">Interés NAFIN:</label>
                            <input type="number" id="interes_nafin" name="interes_nafin" step="0.01" value="<?php echo number_format($invoice['interes_nafin'] ?? 0, 2, '.', ''); ?>" class="form-control form-control-sm" data-currency="USD">
                        </div>
                        <div class="form-group">
                            <label for="importe_pagado">Pago Recibido:</label>
                            <input type="number" id="importe_pagado" name="importe_pagado" step="0.01" value="<?php echo number_format($invoice['importe_pagado'] ?? 0, 2, '.', ''); ?>" class="form-control form-control-sm" data-currency="USD">
                        </div>
                        <div class="form-group">
                            <label for="observaciones">Observaciones:</label>
                            <input type="text" id="observaciones" name="observaciones" value="<?php echo htmlspecialchars($invoice['observaciones'] ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label for="recibo">Recibo:</label>
                            <input type="text" id="recibo" name="recibo" value="<?php echo htmlspecialchars($invoice['recibo'] ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label for="facel">FACEL:</label>
                            <input type="text" id="facel" name="facel" value="<?php echo htmlspecialchars($invoice['facel'] ?? ''); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label for="fecha_facel">Fecha FACEL:</label>
                            <input type="date" id="fecha_facel" name="fecha_facel" value="<?php echo htmlspecialchars($invoice['fecha_facel'] ?? ''); ?>" class="form-control form-control-sm date-input">
                        </div>
                    </div>
                </div>

                <!-- Sección para agregar conceptos (ítems) -->
                <div class="row">
                    <div class="col-md-12">
                        <h3>Conceptos</h3>
                        <div id="items-container">
                            <?php if (!empty($invoice['items'])): ?>
                                <?php foreach ($invoice['items'] as $index => $item): ?>
                                    <div class="item-row form-group">
                                        <label>Descripción:</label>
                                        <input type="text" name="items[<?php echo $index; ?>][descripcion]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($item['descripcion'] ?? ''); ?>" required>
                                        <label>Cantidad:</label>
                                        <input type="number" name="items[<?php echo $index; ?>][cantidad]" step="0.01" class="form-control form-control-sm" value="<?php echo number_format($item['cantidad'] ?? 0, 2, '.', ''); ?>" required>
                                        <label>Unidad (Clave):</label>
                                        <input type="text" name="items[<?php echo $index; ?>][clave_unidad]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($item['clave_unidad'] ?? ''); ?>" required>
                                        <label>Precio Unitario:</label>
                                        <input type="number" name="items[<?php echo $index; ?>][precio_unitario]" step="0.01" class="form-control form-control-sm" value="<?php echo number_format($item['precio_unitario'] ?? 0, 2, '.', ''); ?>" required>
                                        <label>Importe:</label>
                                        <input type="number" name="items[<?php echo $index; ?>][importe]" step="0.01" class="form-control form-control-sm" value="<?php echo number_format($item['importe'] ?? 0, 2, '.', ''); ?>" required>
                                        <label>IVA:</label>
                                        <input type="number" name="items[<?php echo $index; ?>][importe_iva]" step="0.01" class="form-control form-control-sm" value="<?php echo number_format($item['importe_iva'] ?? 0, 2, '.', ''); ?>" required>
                                        <button type="button" class="btn btn-danger remove-item" style="margin-top: 10px;">Eliminar</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="item-row form-group">
                                    <label>Descripción:</label>
                                    <input type="text" name="items[0][descripcion]" class="form-control form-control-sm" required>
                                    <label>Cantidad:</label>
                                    <input type="number" name="items[0][cantidad]" step="0.01" class="form-control form-control-sm" required>
                                    <label>Unidad (Clave):</label>
                                    <input type="text" name="items[0][clave_unidad]" class="form-control form-control-sm" required>
                                    <label>Precio Unitario:</label>
                                    <input type="number" name="items[0][precio_unitario]" step="0.01" class="form-control form-control-sm" required>
                                    <label>Importe:</label>
                                    <input type="number" name="items[0][importe]" step="0.01" class="form-control form-control-sm" required>
                                    <label>IVA:</label>
                                    <input type="number" name="items[0][importe_iva]" step="0.01" class="form-control form-control-sm" required>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="add-item" class="btn btn-secondary mt-2">Añadir Otro Concepto</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
                <a href="index.php" class="btn btn-secondary mt-3 ms-2">Volver a la Lista</a>
            </form>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        let itemCount = <?php echo count($invoice['items'] ?? [0]) ?: 1; ?>;

        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const newRow = document.createElement('div');
            newRow.className = 'item-row form-group';
            newRow.innerHTML = `
                <label>Descripción:</label>
                <input type="text" name="items[${itemCount}][descripcion]" class="form-control form-control-sm" required>
                <label>Cantidad:</label>
                <input type="number" name="items[${itemCount}][cantidad]" step="0.01" class="form-control form-control-sm" required>
                <label>Unidad (Clave):</label>
                <input type="text" name="items[${itemCount}][clave_unidad]" class="form-control form-control-sm" required>
                <label>Precio Unitario:</label>
                <input type="number" name="items[${itemCount}][precio_unitario]" step="0.01" class="form-control form-control-sm" required>
                <label>Importe:</label>
                <input type="number" name="items[${itemCount}][importe]" step="0.01" class="form-control form-control-sm" required>
                <label>IVA:</label>
                <input type="number" name="items[${itemCount}][importe_iva]" step="0.01" class="form-control form-control-sm" required>
                <button type="button" class="btn btn-danger remove-item" style="margin-top: 10px;">Eliminar</button>
            `;
            container.appendChild(newRow);
            itemCount++;

            // Añadir evento para eliminar ítems
            const removeButtons = document.querySelectorAll('.remove-item');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.remove();
                });
            });
        });

        // Añadir evento para eliminar ítems existentes
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.remove();
            });
        });
    </script>
    <?php if (DEBUG): ?>
        <script>
            console.log("Depuración activa (DEBUG=true). Revisar logs en C:\\xampp\\php\\logs\\php_error.log.");
        </script>
    <?php endif; ?>
</body>
</html>