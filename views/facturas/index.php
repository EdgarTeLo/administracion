<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas</title>
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main>
        <h1>Lista de Facturas</h1>
        <?php if (empty($facturas)): ?>
            <p>No hay facturas activas registradas.</p>
        <?php else: ?>
            <table border="1">
                <thead>
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
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>