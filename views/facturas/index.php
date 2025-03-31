<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Lista de Facturas</h1>
        <p class="text-center">
            <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/crear" class="btn btn-success mb-3">Crear Nueva Factura</a>
        </p>
        <form method="GET" action="<?php echo $_ENV['APP_URL']; ?>/facturas" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="cliente" class="form-label">Cliente:</label>
                    <input type="text" name="cliente" id="cliente" class="form-control" value="<?php echo htmlspecialchars($_GET['cliente'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="estado" class="form-label">Estado:</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activa" <?php echo ($_GET['estado'] ?? '') === 'activa' ? 'selected' : ''; ?>>Activa</option>
                        <option value="cancelada" <?php echo ($_GET['estado'] ?? '') === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </form>
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($facturas)): ?>
            <div class="alert alert-info">No hay facturas que coincidan con los criterios de búsqueda.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Número de Factura</th>
                            <th>Folio Fiscal</th>
                            <th>Cliente</th>
                            <th>Subtotal</th>
                            <th>IVA</th>
                            <th>Total</th>
                            <th>Fecha de Pago</th>
                            <th>Estado</th>
                            <th>Orden de Compra</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facturas as $factura): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($factura['id']); ?></td>
                                <td><?php echo htmlspecialchars($factura['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($factura['numero_factura']); ?></td>
                                <td><?php echo htmlspecialchars($factura['folio_fiscal']); ?></td>
                                <td><?php echo htmlspecialchars($factura['cliente']); ?></td>
                                <td><?php echo htmlspecialchars($factura['subtotal']); ?></td>
                                <td><?php echo htmlspecialchars($factura['iva']); ?></td>
                                <td><?php echo htmlspecialchars($factura['total']); ?></td>
                                <td><?php echo htmlspecialchars($factura['fecha_pago'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($factura['estado']); ?></td>
                                <td><?php echo htmlspecialchars($factura['orden_compra'] ?: 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/detalle/<?php echo $factura['id']; ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/editar/<?php echo $factura['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/eliminar/<?php echo $factura['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta factura?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/administracion/public/js/scripts.js"></script>
</body>
</html>