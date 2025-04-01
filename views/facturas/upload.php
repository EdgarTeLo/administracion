<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Facturas y Órdenes de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Subir Facturas y Órdenes de Compra</h1>
        <?php if (isset($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="<?php echo $_ENV['APP_URL']; ?>/facturas/upload" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="file_type" class="form-label">Tipo de Archivo:</label>
                        <select name="file_type" id="file_type" class="form-select" required>
                            <option value="xml">Factura (XML)</option>
                            <option value="csv">Orden de Compra (CSV)</option>
                            <option value="pdf">Orden de Compra (PDF)</option>
                            <option value="csv_facturas">Facturas Masivas (CSV)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Seleccionar Archivo:</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".xml,.csv,.pdf">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Subir</button>
                </form>
                <p class="mt-3 text-center">
                    <a href="<?php echo $_ENV['APP_URL']; ?>/facturas" class="btn btn-secondary">Volver a la Lista de Facturas</a>
                </p>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/administracion/public/js/scripts.js"></script>
</body>
</html>