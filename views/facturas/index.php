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
            <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/upload" class="btn btn-success mb-3">Subir Facturas / Órdenes de Compra</a>
        </p>

        <!-- Filtro por estado -->
        <div class="mb-3">
            <label for="filtroEstado" class="form-label">Filtrar por Estado:</label>
            <select id="filtroEstado" class="form-select" onchange="filtrarFacturas()">
                <option value="">Todos</option>
                <option value="activa">Activa</option>
                <option value="cancelada">Cancelada</option>
                <option value="complemento">Complemento</option>
            </select>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
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
            <tbody id="facturasTableBody">
                <?php foreach ($facturas as $factura): ?>
                    <tr data-estado="<?php echo htmlspecialchars($factura['estado']); ?>">
                        <td><?php echo htmlspecialchars($factura['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($factura['fact']); ?></td>
                        <td><?php echo htmlspecialchars($factura['folio_fiscal']); ?></td>
                        <td><?php echo htmlspecialchars($factura['cliente']); ?></td>
                        <td><?php echo number_format($factura['subtotal'], 2); ?></td>
                        <td><?php echo number_format($factura['iva'], 2); ?></td>
                        <td><?php echo number_format($factura['total'], 2); ?></td>
                        <td><?php echo htmlspecialchars($factura['fecha_pago'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($factura['estado']); ?></td>
                        <td><?php echo htmlspecialchars($factura['orden_compra'] ?: 'N/A'); ?></td>
                        <td>
                            <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/edit/<?php echo $factura['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/delete/<?php echo $factura['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta factura?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="/administracion/public/scripts.js"></script>
</body>
</html>