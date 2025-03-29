<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal</title>
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main>
        <h1>Lista de Personal</h1>
        <?php if (empty($empleados)): ?>
            <p>No hay empleados activos registrados.</p>
        <?php else: ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>CURP</th>
                        <th>RFC</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Empresa</th>
                        <th>Especialidad</th>
                        <th>Área de Trabajo</th>
                        <th>Fecha de Ingreso</th>
                        <th>Fecha de Baja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $empleado): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($empleado['ID']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['NOMBRE']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['APELLIDOPATERNO']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['APELLIDOMATERNO']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['CURP']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['RFC']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['TELMOVIL']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['EMAIL']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['EMPRESA']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['ESPECIALIDAD']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['AREADETRABAJO']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['FECHAINGRESO']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['FECHABAJA'] ?: 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>