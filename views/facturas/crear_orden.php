<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Orden de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Crear Orden de Compra</h1>
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
                <form action="<?php echo $_ENV['APP_URL']; ?>/facturas/crear_orden" method="POST">
                    <div class="mb-3">
                        <label for="numero_oc" class="form-label">Número de OC:</label>
                        <input type="text" name="numero_oc" id="numero_oc" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="total" class="form-label">Total:</label>
                        <input type="number" step="0.01" name="total" id="total" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_emision" class="form-label">Fecha de Emisión:</label>
                        <input type="date" name="fecha_emision" id="fecha_emision" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="proveedor" class="form-label">Proveedor:</label>
                        <input type="text" name="proveedor" id="proveedor" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Crear Orden de Compra</button>
                </form>
                <p class="mt-3 text-center">
                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas/ordenes" class="btn btn-secondary">Volver a Órdenes de Compra</a>
                </p>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/administracion/public/js/scripts.js"></script>
</body>
</html>