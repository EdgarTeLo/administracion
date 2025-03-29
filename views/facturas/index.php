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
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($facturas)): ?>
            <div class="alert alert-info">No hay facturas activas registradas.</div>
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