<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Lista de Personal</h1>
        <p class="text-center">
            <a href="<?php echo $_ENV['APP_URL']; ?>/personal/crear" class="btn btn-success mb-3">Crear Nuevo Empleado</a>
        </p>
        <?php if (isset($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($empleados)): ?>
            <div class="alert alert-info">No hay empleados activos registrados.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido Paterno</th>
                            <th>Apellido Materno</th>
                            <th>Fecha de Nacimiento</th>
                            <th>CURP</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Empresa</th>
                            <th>Especialidad</th>
                            <th>Área Laboral</th>
                            <th>Fecha de Ingreso</th>
                            <th>Fecha de Baja</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empleados as $empleado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($empleado['IDPERSONAL']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['NOMBRE']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['APELLIDOPATERNO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['APELLIDOMATERNO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['FECHANACIMIENTO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['CURP']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['TELMOVIL']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['EMAIL']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['EMPRESA']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['ESPECIALIDAD']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['AREALABORAL']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['FECHAINGRESO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['FECHADEBAJA'] ?: 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo $_ENV['APP_URL']; ?>/personal/editar/<?php echo $empleado['IDPERSONAL']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?php echo $_ENV['APP_URL']; ?>/personal/eliminar/<?php echo $empleado['IDPERSONAL']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este empleado?');">Eliminar</a>
                                </td>
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