<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Factura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Detalles de Factura #<?php echo htmlspecialchars($factura['numero_factura']); ?></h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Información de la Factura</h5>
                <p><strong>ID:</strong> <?php echo htmlspecialchars($factura['id']); ?></p>
                <p><strong>Fecha:</strong> <?php echo htmlspecialchars($factura['fecha']); ?></p>
                <p><strong>Folio Fiscal:</strong> <?php echo htmlspecialchars($factura['folio_fiscal']); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($factura['cliente']); ?></p>
                <p><strong>Subtotal:</strong> <?php echo htmlspecialchars($factura['subtotal']); ?></p>
                <p><strong>IVA:</strong> <?php echo htmlspecialchars($factura['iva']); ?></p>
                <p><strong>Total:</strong> <?php echo htmlspecialchars($factura['total']); ?></p>
                <p><strong>Fecha de Pago:</strong> <?php echo htmlspecialchars($factura['fecha_pago'] ?: 'N/A'); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars($factura['estado']); ?></p>
                <p><strong>Orden de Compra:</strong> <?php echo htmlspecialchars($factura['orden_compra'] ?: 'N/A'); ?></p>
                <p><a href="<?php echo $_ENV['APP_URL']; ?>/facturas/asociar/<?php echo $factura['id']; ?>" class="btn btn-primary btn-sm">Asociar Orden de Compra</a></p>
            </div>
        </div>
        <h2 class="text-center">Ítems de la Factura</h2>
        <?php if (empty($items)): ?>
            <div class="alert alert-info">No hay ítems registrados para esta factura.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Clave Producto/Servicio</th>
                            <th>Número de Identificación</th>
                            <th>Cantidad</th>
                            <th>Clave Unidad</th>
                            <th>Descripción Unidad</th>
                            <th>Descripción</th>
                            <th>Precio Unitario</th>
                            <th>Importe</th>
                            <th>Importe IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['id']); ?></td>
                                <td><?php echo htmlspecialchars($item['clave_prod_serv']); ?></td>
                                <td><?php echo htmlspecialchars($item['numero_identificacion']); ?></td>
                                <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($item['clave_unidad']); ?></td>
                                <td><?php echo htmlspecialchars($item['descripcion_unidad']); ?></td>
                                <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($item['precio_unitario']); ?></td>
                                <td><?php echo htmlspecialchars($item['importe']); ?></td>
                                <td><?php echo htmlspecialchars($item['importe_iva']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <p class="text-center">
            <a href="<?php echo $_ENV['APP_URL']; ?>/facturas" class="btn btn-secondary">Volver a Facturas</a>
        </p>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/administracion/public/js/scripts.js"></script>
</body>
</html>