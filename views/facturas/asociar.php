<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asociar Factura con Orden de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Asociar Factura con Orden de Compra</h1>
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
                <form action="<?php echo $_ENV['APP_URL']; ?>/facturas/asociar/<?php echo $factura['id']; ?>" method="POST">
                    <div class="mb-3">
                        <label for="factura_id" class="form-label">Factura:</label>
                        <input type="text" class="form-control" id="factura_id" value="<?php echo htmlspecialchars($factura['numero_factura']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="orden_compra" class="form-label">Orden de Compra:</label>
                        <select name="orden_compra" id="orden_compra" class="form-select" required>
                            <option value="">Selecciona una orden de compra</option>
                            <?php foreach ($ordenes as $orden): ?>
                                <option value="<?php echo htmlspecialchars($orden['numero_oc']); ?>" <?php echo $factura['orden_compra'] === $orden['numero_oc'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($orden['numero_oc']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Asociar</button>
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