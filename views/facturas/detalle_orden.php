<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Orden de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Detalles de Orden de Compra #<?php echo htmlspecialchars($orden['numero_oc']); ?></h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Información de la Orden de Compra</h5>
                <p><strong>ID:</strong> <?php echo htmlspecialchars($orden['id']); ?></p>
                <p><strong>Número de OC:</strong> <?php echo htmlspecialchars($orden['numero_oc']); ?></p>
                <p><strong>Total:</strong> <?php echo htmlspecialchars($orden['total']); ?></p>
                <p><strong>Fecha de Emisión:</strong> <?php echo htmlspecialchars($orden['fecha_emision']); ?></p>
                <p><strong>Proveedor:</strong> <?php echo htmlspecialchars($orden['proveedor']); ?></p>
            </div>
        </div>
        <h2 class="text-center">Ítems de la Orden de Compra</h2>
        <?php if (empty($items)): ?>
            <div class="alert alert-info">No hay ítems registrados para esta orden de compra.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['id']); ?></td>
                                <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($item['precio_unitario']); ?></td>
                                <td><?php echo htmlspecialchars($item['importe']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <p class="text-center">
            <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/ordenes" class="btn btn-secondary">Volver a Órdenes de Compra</a>
        </p>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/administracion/public/js/scripts.js"></script>
</body>
</html>