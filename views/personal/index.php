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
                            <th>RFC</th>
                            <th>Teléfono Casa</th>
                            <th>Teléfono Móvil</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Empresa</th>
                            <th>Puesto</th>
                            <th>Área Laboral</th>
                            <th>Especialidad</th>
                            <th>Sueldo IMSS</th>
                            <th>Número IMSS</th>
                            <th>Número Cuenta Banamex</th>
                            <th>Supervisor</th>
                            <th>Fecha de Ingreso</th>
                            <th>Inicio/Fin Contrato</th>
                            <th>Vencimiento Contrato</th>
                            <th>Renovación Contrato</th>
                            <th>Aviso Fin Contrato</th>
                            <th>Tipo Empleado</th>
                            <th>Contrato Confidencialidad</th>
                            <th>Estado Civil</th>
                            <th>Motivo de Baja</th>
                            <th>Sexo</th>
                            <th>Ayuda Pasajes</th>
                            <th>INE</th>
                            <th>CURP Doc</th>
                            <th>RFC Doc</th>
                            <th>Comp. Domicilio</th>
                            <th>Acta Nacimiento</th>
                            <th>NSS Doc</th>
                            <th>Contrato Doc</th>
                            <th>CMC Doc</th>
                            <th>Reglamento/Aviso</th>
                            <th>Pagaré</th>
                            <th>Fecha de Baja</th>
                            <th>Observaciones</th>
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
                                <td><?php echo htmlspecialchars($empleado['RFCP']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['TELCASA']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['TELMOVIL']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['EMAIL']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DIRECCION']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['NOMBRE_EMPRESA']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['PUESTO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['NOMBRE_AREALABORAL']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['NOMBRE_ESPECIALIDAD']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['SUELDOIMSS']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['NUMIMSS']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['NUMCTABANAMEX']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['SUPERVISOR']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['FECHAINGRESO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['FECHAINICIOFINCONTRATO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['VENCIMIENTOCONTRATO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['RENOVACIONCONTRATO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['AVISOFINDECONTRATO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['TIPOEMPLEADO']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['FECHADECONTRATODECONFIDENCIALIDAD']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['ESTADOCIVIL'] == 1 ? 'Soltero(a)' : ($empleado['ESTADOCIVIL'] == 2 ? 'Casado(a)' : ($empleado['ESTADOCIVIL'] == 3 ? 'Divorciado(a)' : ($empleado['ESTADOCIVIL'] == 4 ? 'Viudo(a)' : ($empleado['ESTADOCIVIL'] == 5 ? 'Unión Libre' : 'N/A'))))); ?></td>
                                <td><?php echo htmlspecialchars($empleado['MOTIVODEBAJA']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['SEXO'] == 1 ? 'Masculino' : ($empleado['SEXO'] == 0 ? 'Femenino' : 'No especificado')); ?></td>
                                <td><?php echo htmlspecialchars($empleado['AYUDAPASAJESXDIA']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCINE'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCCURP'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCRFC'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCCOMPDOM'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCACTNAC'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCNSS'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCCONTRATO'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCCMC'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCREGLAYAVISO'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['DOCPAGARE'] ? 'Sí' : 'No'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['FECHADEBAJA'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['OBSERVACIONES']); ?></td>
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