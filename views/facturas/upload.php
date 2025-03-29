<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Facturas y Órdenes de Compra</title>
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main>
        <h1>Subir Facturas y Órdenes de Compra</h1>
        <?php if (isset($message)): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <form action="<?php echo $_ENV['APP_URL']; ?>/facturas/upload" method="POST" enctype="multipart/form-data">
            <label for="file_type">Tipo de Archivo:</label>
            <select name="file_type" id="file_type" required>
                <option value="xml">Factura (XML)</option>
                <option value="csv">Orden de Compra (CSV)</option>
            </select>
            <br>
            <label for="file">Seleccionar Archivo:</label>
            <input type="file" name="file" id="file" required>
            <br>
            <button type="submit">Subir</button>
        </form>
        <p><a href="<?php echo $_ENV['APP_URL']; ?>/facturas">Volver a la Lista de Facturas</a></p>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>