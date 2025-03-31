<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Factura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Editar Factura #<?php echo htmlspecialchars($factura['numero_factura']); ?></h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="<?php echo $_ENV['APP_URL']; ?>/facturas/editar/<?php echo $factura['id']; ?>" method="POST">
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha:</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?php echo htmlspecialchars($factura['fecha']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="fact" class="form-label">Número de Factura:</label>
                        <input type="text" name="fact" id="fact" class="form-control" value="<?php echo htmlspecialchars($factura['numero_factura']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="folio_fiscal" class="form-label">Folio Fiscal:</label>
                        <input type="text" name="folio_fiscal" id="folio_fiscal" class="form-control" value="<?php echo htmlspecialchars($factura['folio_fiscal']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="cliente" class="form-label">Cliente:</label>
                        <input type="text" name="cliente" id="cliente" class="form-control" value="<?php echo htmlspecialchars($factura['cliente']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="subtotal" class="form-label">Subtotal:</label>
                        <input type="number" step="0.01" name="subtotal" id="subtotal" class="form-control" value="<?php echo htmlspecialchars($factura['subtotal']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="iva" class="form-label">IVA:</label>
                        <input type="number" step="0.01" name="iva" id="iva" class="form-control" value="<?php echo htmlspecialchars($factura['iva']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="total" class="form-label">Total:</label>
                        <input type="number" step="0.01" name="total" id="total" class="form-control" value="<?php echo htmlspecialchars($factura['total']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_pago" class="form-label">Fecha de Pago:</label>
                        <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" value="<?php echo htmlspecialchars($factura['fecha_pago']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado:</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="activa" <?php echo $factura['estado'] === 'activa' ? 'selected' : ''; ?>>Activa</option>
                            <option value="cancelada" <?php echo $factura['estado'] === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="orden_compra" class="form-label">Orden de Compra:</label>
                        <select name="orden_compra" id="orden_compra" class="form-select">
                            <option value="">Ninguna</option>
                            <?php foreach ($ordenes as $orden): ?>
                                <option value="<?php echo htmlspecialchars($orden['numero_oc']); ?>" <?php echo $factura['orden_compra'] === $orden['numero_oc'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($orden['numero_oc']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Actualizar Factura</button>
                </form>
                <p class="mt-3 text-center">
                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas" class="btn btn-secondary">Volver a Facturas</a>
                </p>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/administracion/public/js/scripts.js"></script>
</body>
</html>