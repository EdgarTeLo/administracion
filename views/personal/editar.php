<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/administracion/public/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-5">
        <h1 class="text-center">Editar Empleado #<?php echo htmlspecialchars($empleado['IDPERSONAL']); ?></h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="<?php echo $_ENV['APP_URL']; ?>/personal/editar/<?php echo $empleado['IDPERSONAL']; ?>" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($empleado['NOMBRE']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido_paterno" class="form-label">Apellido Paterno:</label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control" value="<?php echo htmlspecialchars($empleado['APELLIDOPATERNO']); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="apellido_materno" class="form-label">Apellido Materno:</label>
                            <input type="text" name="apellido_materno" id="apellido_materno" class="form-control" value="<?php echo htmlspecialchars($empleado['APELLIDOMATERNO']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHANACIMIENTO']); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="curp" class="form-label">CURP:</label>
                            <input type="text" name="curp" id="curp" class="form-control" value="<?php echo htmlspecialchars($empleado['CURP']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rfc" class="form-label">RFC:</label>
                            <input type="text" name="rfc" id="rfc" class="form-control" value="<?php echo htmlspecialchars($empleado['RFC']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nss" class="form-label">NSS:</label>
                            <input type="text" name="nss" id="nss" class="form-control" value="<?php echo htmlspecialchars($empleado['NSS']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tel_movil" class="form-label">Teléfono Móvil:</label>
                            <input type="text" name="tel_movil" id="tel_movil" class="form-control" value="<?php echo htmlspecialchars($empleado['TELMOVIL']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($empleado['EMAIL']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_ingreso" class="form-label">Fecha de Ingreso:</label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHAINGRESO']); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_baja" class="form-label">Fecha de Baja:</label>
                            <input type="date" name="fecha_baja" id="fecha_baja" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHADEBAJA']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="direccion" class="form-label">Dirección:</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($empleado['DIRECCION']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="codigopostal" class="form-label">Código Postal:</label>
                            <input type="text" name="codigopostal" id="codigopostal" class="form-control" value="<?php echo htmlspecialchars($empleado['CODIGOPOSTAL']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ciudad" class="form-label">Ciudad:</label>
                            <input type="text" name="ciudad" id="ciudad" class="form-control" value="<?php echo htmlspecialchars($empleado['CIUDAD']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="estadoresidencia" class="form-label">Estado de Residencia:</label>
                            <input type="text" name="estadoresidencia" id="estadoresidencia" class="form-control" value="<?php echo htmlspecialchars($empleado['ESTADORESIDENCIA']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pais" class="form-label">País:</label>
                            <input type="text" name="pais" id="pais" class="form-control" value="<?php echo htmlspecialchars($empleado['PAIS']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label">Estado:</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="1" <?php echo $empleado['ESTADO'] == 1 ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo $empleado['ESTADO'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecharegistro" class="form-label">Fecha de Registro:</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHAREGISTRO']); ?>" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones:</label>
                        <textarea name="observaciones" id="observaciones" class="form-control" rows="3"><?php echo htmlspecialchars($empleado['OBSERVACIONES']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Actualizar Empleado</button>
                </form>
                <p class="mt-3 text-center">
                    <a href="<?php echo $_ENV['APP_URL']; ?>/personal" class="btn btn-secondary">Volver a Personal</a>
                </p>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/administracion/public/js/scripts.js"></script>
</body>
</html>