<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Lista de Órdenes de Compra</h1>
        <p class="text-center">
            <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/crear_orden" class="btn btn-success mb-3">Crear Nueva Orden de Compra</a>
        </p>
        <?php if (isset($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($ordenes)): ?>
            <div class="alert alert-info">No hay órdenes de compra registradas.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Número de OC</th>
                            <th>Total</th>
                            <th>Fecha de Emisión</th>
                            <th>Proveedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes as $orden): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($orden['id']); ?></td>
                                <td><?php echo htmlspecialchars($orden['numero_oc']); ?></td>
                                <td><?php echo htmlspecialchars($orden['total']); ?></td>
                                <td><?php echo htmlspecialchars($orden['fecha_emision']); ?></td>
                                <td><?php echo htmlspecialchars($orden['proveedor']); ?></td>
                                <td>
                                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/ordenes/detalle/<?php echo $orden['id']; ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/editar_orden/<?php echo $orden['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/eliminar_orden/<?php echo $orden['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta orden de compra?');">Eliminar</a>
                                </td>
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