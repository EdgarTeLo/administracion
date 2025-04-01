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
            <div class="col-md-10">
                <form action="<?php echo $_ENV['APP_URL']; ?>/personal/editar/<?php echo $empleado['IDPERSONAL']; ?>" method="POST">
                    <h4>Datos Personales</h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($empleado['NOMBRE']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="apellido_paterno" class="form-label">Apellido Paterno:</label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control" value="<?php echo htmlspecialchars($empleado['APELLIDOPATERNO']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="apellido_materno" class="form-label">Apellido Materno:</label>
                            <input type="text" name="apellido_materno" id="apellido_materno" class="form-control" value="<?php echo htmlspecialchars($empleado['APELLIDOMATERNO']); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHANACIMIENTO']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="curp" class="form-label">CURP:</label>
                            <input type="text" name="curp" id="curp" class="form-control" value="<?php echo htmlspecialchars($empleado['CURP']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sexo" class="form-label">Sexo:</label>
                            <select name="sexo" id="sexo" class="form-select" required>
                                <option value="1" <?php echo $empleado['SEXO'] == 1 ? 'selected' : ''; ?>>Masculino</option>
                                <option value="0" <?php echo $empleado['SEXO'] == 0 ? 'selected' : ''; ?>>Femenino</option>
                                <option value="2" <?php echo $empleado['SEXO'] == 2 ? 'selected' : ''; ?>>No especificado</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="estado_civil" class="form-label">Estado Civil:</label>
                            <select name="estado_civil" id="estado_civil" class="form-select">
                                <option value="">Selecciona una opción</option>
                                <option value="1" <?php echo $empleado['ESTADOCIVIL'] == 1 ? 'selected' : ''; ?>>Soltero(a)</option>
                                <option value="2" <?php echo $empleado['ESTADOCIVIL'] == 2 ? 'selected' : ''; ?>>Casado(a)</option>
                                <option value="3" <?php echo $empleado['ESTADOCIVIL'] == 3 ? 'selected' : ''; ?>>Divorciado(a)</option>
                                <option value="4" <?php echo $empleado['ESTADOCIVIL'] == 4 ? 'selected' : ''; ?>>Viudo(a)</option>
                                <option value="5" <?php echo $empleado['ESTADOCIVIL'] == 5 ? 'selected' : ''; ?>>Unión Libre</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tel_casa" class="form-label">Teléfono Casa:</label>
                            <input type="text" name="tel_casa" id="tel_casa" class="form-control" value="<?php echo htmlspecialchars($empleado['TELCASA']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tel_movil" class="form-label">Teléfono Móvil:</label>
                            <input type="text" name="tel_movil" id="tel_movil" class="form-control" value="<?php echo htmlspecialchars($empleado['TELMOVIL']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($empleado['EMAIL']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="direccion" class="form-label">Dirección:</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($empleado['DIRECCION']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="rfcp" class="form-label">RFC:</label>
                            <input type="text" name="rfcp" id="rfcp" class="form-control" value="<?php echo htmlspecialchars($empleado['RFCP']); ?>">
                        </div>
                    </div>

                    <h4 class="mt-4">Datos Laborales</h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="empresa" class="form-label">Empresa:</label>
                            <input type="text" name="empresa" id="empresa" class="form-control" value="<?php echo htmlspecialchars($empleado['EMPRESA']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="puesto" class="form-label">Puesto:</label>
                            <input type="text" name="puesto" id="puesto" class="form-control" value="<?php echo htmlspecialchars($empleado['PUESTO']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="arealaboral" class="form-label">Área Laboral:</label>
                            <input type="text" name="arealaboral" id="arealaboral" class="form-control" value="<?php echo htmlspecialchars($empleado['AREALABORAL']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="especialidad" class="form-label">Especialidad:</label>
                            <input type="text" name="especialidad" id="especialidad" class="form-control" value="<?php echo htmlspecialchars($empleado['ESPECIALIDAD']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sueldo_imss" class="form-label">Sueldo IMSS:</label>
                            <input type="number" name="sueldo_imss" id="sueldo_imss" class="form-control" value="<?php echo htmlspecialchars($empleado['SUELDOIMSS']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="num_imss" class="form-label">Número IMSS:</label>
                            <input type="text" name="num_imss" id="num_imss" class="form-control" value="<?php echo htmlspecialchars($empleado['NUMIMSS']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="num_cta_banamex" class="form-label">Número Cuenta Banamex:</label>
                            <input type="text" name="num_cta_banamex" id="num_cta_banamex" class="form-control" value="<?php echo htmlspecialchars($empleado['NUMCTABANAMEX']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="supervisor" class="form-label">Supervisor:</label>
                            <input type="text" name="supervisor" id="supervisor" class="form-control" value="<?php echo htmlspecialchars($empleado['SUPERVISOR']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="fecha_ingreso" class="form-label">Fecha de Ingreso:</label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHAINGRESO']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="fecha_inicio_fin_contrato" class="form-label">Fecha Inicio/Fin Contrato:</label>
                            <input type="date" name="fecha_inicio_fin_contrato" id="fecha_inicio_fin_contrato" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHAINICIOFINCONTRATO']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="vencimiento_contrato" class="form-label">Vencimiento Contrato:</label>
                            <input type="date" name="vencimiento_contrato" id="vencimiento_contrato" class="form-control" value="<?php echo htmlspecialchars($empleado['VENCIMIENTOCONTRATO']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="renovacion_contrato" class="form-label">Renovación Contrato:</label>
                            <input type="date" name="renovacion_contrato" id="renovacion_contrato" class="form-control" value="<?php echo htmlspecialchars($empleado['RENOVACIONCONTRATO']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="aviso_fin_contrato" class="form-label">Aviso Fin Contrato:</label>
                            <input type="date" name="aviso_fin_contrato" id="aviso_fin_contrato" class="form-control" value="<?php echo htmlspecialchars($empleado['AVISOFINDECONTRATO']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tipo_empleado" class="form-label">Tipo de Empleado:</label>
                            <input type="text" name="tipo_empleado" id="tipo_empleado" class="form-control" value="<?php echo htmlspecialchars($empleado['TIPOEMPLEADO']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="fecha_contrato_confidencialidad" class="form-label">Fecha Contrato Confidencialidad:</label>
                            <input type="date" name="fecha_contrato_confidencialidad" id="fecha_contrato_confidencialidad" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHADECONTRATODECONFIDENCIALIDAD']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="ayuda_pasajes_x_dia" class="form-label">Ayuda Pasajes por Día:</label>
                            <input type="number" step="0.01" name="ayuda_pasajes_x_dia" id="ayuda_pasajes_x_dia" class="form-control" value="<?php echo htmlspecialchars($empleado['AYUDAPASAJESXDIA']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">Estado:</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="1" <?php echo $empleado['ESTADO'] == 1 ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo $empleado['ESTADO'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="fecha_baja" class="form-label">Fecha de Baja:</label>
                            <input type="date" name="fecha_baja" id="fecha_baja" class="form-control" value="<?php echo htmlspecialchars($empleado['FECHADEBAJA']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="motivo_baja" class="form-label">Motivo de Baja:</label>
                            <textarea name="motivo_baja" id="motivo_baja" class="form-control" rows="3"><?php echo htmlspecialchars($empleado['MOTIVODEBAJA']); ?></textarea>
                        </div>
                    </div>

                    <h4 class="mt-4">Documentos</h4>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="doc_ine" class="form-label">INE:</label>
                            <select name="doc_ine" id="doc_ine" class="form-select">
                                <option value="0" <?php echo $empleado['DOCINE'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCINE'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="doc_curp" class="form-label">CURP:</label>
                            <select name="doc_curp" id="doc_curp" class="form-select">
                                <option value="0" <?php echo $empleado['DOCCURP'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCCURP'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="doc_rfc" class="form-label">RFC:</label>
                            <select name="doc_rfc" id="doc_rfc" class="form-select">
                                <option value="0" <?php echo $empleado['DOCRFC'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCRFC'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="doc_comp_dom" class="form-label">Comprobante Domicilio:</label>
                            <select name="doc_comp_dom" id="doc_comp_dom" class="form-select">
                                <option value="0" <?php echo $empleado['DOCCOMPDOM'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCCOMPDOM'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="doc_act_nac" class="form-label">Acta de Nacimiento:</label>
                            <select name="doc_act_nac" id="doc_act_nac" class="form-select">
                                <option value="0" <?php echo $empleado['DOCACTNAC'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCACTNAC'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="doc_nss" class="form-label">NSS:</label>
                            <select name="doc_nss" id="doc_nss" class="form-select">
                                <option value="0" <?php echo $empleado['DOCNSS'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCNSS'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="doc_contrato" class="form-label">Contrato:</label>
                            <select name="doc_contrato" id="doc_contrato" class="form-select">
                                <option value="0" <?php echo $empleado['DOCCONTRATO'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCCONTRATO'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="doc_cmc" class="form-label">CMC:</label>
                            <select name="doc_cmc" id="doc_cmc" class="form-select">
                                <option value="0" <?php echo $empleado['DOCCMC'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCCMC'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="doc_reglamento_aviso" class="form-label">Reglamento y Aviso:</label>
                            <select name="doc_reglamento_aviso" id="doc_reglamento_aviso" class="form-select">
                                <option value="0" <?php echo $empleado['DOCREGLAYAVISO'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCREGLAYAVISO'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="doc_pagare" class="form-label">Pagaré:</label>
                            <select name="doc_pagare" id="doc_pagare" class="form-select">
                                <option value="0" <?php echo $empleado['DOCPAGARE'] == 0 ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo $empleado['DOCPAGARE'] == 1 ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                    </div>

                    <h4 class="mt-4">Observaciones</h4>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="observaciones" class="form-label">Observaciones:</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3"><?php echo htmlspecialchars($empleado['OBSERVACIONES']); ?></textarea>
                        </div>
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