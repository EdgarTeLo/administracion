<?php
require_once '../header.php';
$pageTitle = "Editar Personal";

include "../config/conexion.php";
$db = new Database();
$conn = $db->getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: read.php");
    exit();
}

$id = (int)$_GET['id'];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellidoPaterno = trim($_POST['apellidoPaterno']);
    $apellidoMaterno = trim($_POST['apellidoMaterno']);
    $fechaNacimiento = $_POST['fechaNacimiento'] ?: '1900-01-01';
    $curp = trim($_POST['curp']) ?: 'XXXX000000XXXXXX00';
    $telCasa = !empty($_POST['telCasa']) ? $_POST['telCasa'] : null;
    $telMovil = !empty($_POST['telMovil']) ? $_POST['telMovil'] : null;
    $direccion = trim($_POST['direccion']) ?: 'Sin dirección';
    $empresa = !empty($_POST['empresa']) ? $_POST['empresa'] : null;
    $puesto = !empty($_POST['puesto']) ? $_POST['puesto'] : null;
    $areaLaboral = !empty($_POST['areaLaboral']) ? $_POST['areaLaboral'] : null;
    $especialidad = !empty($_POST['especialidad']) ? $_POST['especialidad'] : null;
    $sueldoImss = !empty($_POST['sueldoImss']) ? $_POST['sueldoImss'] : null;
    $numImss = trim($_POST['numImss']) ?: null;
    $numCtaBanamex = trim($_POST['numCtaBanamex']) ?: null;
    $supervisor = !empty($_POST['supervisor']) ? $_POST['supervisor'] : null;
    $fechaIngreso = !empty($_POST['fechaIngreso']) ? $_POST['fechaIngreso'] : null;
    $fechaInicioFinContrato = !empty($_POST['fechaInicioFinContrato']) ? $_POST['fechaInicioFinContrato'] : null;
    $vencimientoContrato = !empty($_POST['vencimientoContrato']) ? $_POST['vencimientoContrato'] : null;
    $renovacionContrato = !empty($_POST['renovacionContrato']) ? $_POST['renovacionContrato'] : null;
    $avisoFinContrato = !empty($_POST['avisoFinContrato']) ? $_POST['avisoFinContrato'] : null;
    $observaciones = trim($_POST['observaciones']) ?: null;
    $tipoEmpleado = !empty($_POST['tipoEmpleado']) ? $_POST['tipoEmpleado'] : null;
    $fechaContratoConfidencialidad = !empty($_POST['fechaContratoConfidencialidad']) ? $_POST['fechaContratoConfidencialidad'] : null;
    $estado = !empty($_POST['estado']) ? $_POST['estado'] : 0;
    $fechaBaja = !empty($_POST['fechaBaja']) ? $_POST['fechaBaja'] : null;
    $estadoCivil = !empty($_POST['estadoCivil']) ? $_POST['estadoCivil'] : null;
    $motivoBaja = trim($_POST['motivoBaja']) ?: null;
    $email = trim($_POST['email']) ?: null;
    $sexo = !empty($_POST['sexo']) ? $_POST['sexo'] : 2;
    $ayudaPasajesXDia = !empty($_POST['ayudaPasajesXDia']) ? $_POST['ayudaPasajesXDia'] : 0.00;
    $docIne = isset($_POST['docIne']) ? 1 : 0;
    $docCurp = isset($_POST['docCurp']) ? 1 : 0;
    $docRfc = isset($_POST['docRfc']) ? 1 : 0;
    $docCompDom = isset($_POST['docCompDom']) ? 1 : 0;
    $docActNac = isset($_POST['docActNac']) ? 1 : 0;
    $docNss = isset($_POST['docNss']) ? 1 : 0;
    $docContrato = isset($_POST['docContrato']) ? 1 : 0;
    $docCmc = isset($_POST['docCmc']) ? 1 : 0;
    $docReglaYaviso = isset($_POST['docReglaYaviso']) ? 1 : 0;
    $docPagare = isset($_POST['docPagare']) ? 1 : 0;
    $rfcP = trim($_POST['rfcP']) ?: null;

    $query = "UPDATE personal SET 
        NOMBRE = :nombre, APELLIDOPATERNO = :apellidoPaterno, APELLIDOMATERNO = :apellidoMaterno, 
        FECHANACIMIENTO = :fechaNacimiento, CURP = :curp, TELCASA = :telCasa, TELMOVIL = :telMovil, 
        DIRECCION = :direccion, EMPRESA = :empresa, PUESTO = :puesto, AREALABORAL = :areaLaboral, 
        ESPECIALIDAD = :especialidad, SUELDOIMSS = :sueldoImss, NUMIMSS = :numImss, NUMCTABANAMEX = :numCtaBanamex, 
        SUPERVISOR = :supervisor, FECHAINGRESO = :fechaIngreso, FECHAINICIOFINCONTRATO = :fechaInicioFinContrato, 
        VENCIMIENTOCONTRATO = :vencimientoContrato, RENOVACIONCONTRATO = :renovacionContrato, 
        AVISOFINDECONTRATO = :avisoFinContrato, OBSERVACIONES = :observaciones, TIPOEMPLEADO = :tipoEmpleado, 
        FECHADECONTRATODECONFIDENCIALIDAD = :fechaContratoConfidencialidad, ESTADO = :estado, 
        FECHADEBAJA = :fechaBaja, ESTADOCIVIL = :estadoCivil, MOTIVODEBAJA = :motivoBaja, EMAIL = :email, 
        SEXO = :sexo, AYUDAPASAJESXDIA = :ayudaPasajesXDia, DOCINE = :docIne, DOCCURP = :docCurp, 
        DOCRFC = :docRfc, DOCCOMPDOM = :docCompDom, DOCACTNAC = :docActNac, DOCNSS = :docNss, 
        DOCCONTRATO = :docContrato, DOCCMC = :docCmc, DOCREGLAYAVISO = :docReglaYaviso, DOCPAGARE = :docPagare, 
        RFCP = :rfcP
        WHERE IDPERSONAL = :id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellidoPaterno', $apellidoPaterno);
    $stmt->bindParam(':apellidoMaterno', $apellidoMaterno);
    $stmt->bindParam(':fechaNacimiento', $fechaNacimiento);
    $stmt->bindParam(':curp', $curp);
    $stmt->bindParam(':telCasa', $telCasa, PDO::PARAM_INT);
    $stmt->bindParam(':telMovil', $telMovil, PDO::PARAM_INT);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':empresa', $empresa, PDO::PARAM_INT);
    $stmt->bindParam(':puesto', $puesto, PDO::PARAM_INT);
    $stmt->bindParam(':areaLaboral', $areaLaboral, PDO::PARAM_INT);
    $stmt->bindParam(':especialidad', $especialidad, PDO::PARAM_INT);
    $stmt->bindParam(':sueldoImss', $sueldoImss, PDO::PARAM_INT);
    $stmt->bindParam(':numImss', $numImss);
    $stmt->bindParam(':numCtaBanamex', $numCtaBanamex);
    $stmt->bindParam(':supervisor', $supervisor, PDO::PARAM_INT);
    $stmt->bindParam(':fechaIngreso', $fechaIngreso);
    $stmt->bindParam(':fechaInicioFinContrato', $fechaInicioFinContrato);
    $stmt->bindParam(':vencimientoContrato', $vencimientoContrato);
    $stmt->bindParam(':renovacionContrato', $renovacionContrato);
    $stmt->bindParam(':avisoFinContrato', $avisoFinContrato);
    $stmt->bindParam(':observaciones', $observaciones);
    $stmt->bindParam(':tipoEmpleado', $tipoEmpleado, PDO::PARAM_INT);
    $stmt->bindParam(':fechaContratoConfidencialidad', $fechaContratoConfidencialidad);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
    $stmt->bindParam(':fechaBaja', $fechaBaja);
    $stmt->bindParam(':estadoCivil', $estadoCivil, PDO::PARAM_INT);
    $stmt->bindParam(':motivoBaja', $motivoBaja);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':sexo', $sexo, PDO::PARAM_INT);
    $stmt->bindParam(':ayudaPasajesXDia', $ayudaPasajesXDia);
    $stmt->bindParam(':docIne', $docIne, PDO::PARAM_INT);
    $stmt->bindParam(':docCurp', $docCurp, PDO::PARAM_INT);
    $stmt->bindParam(':docRfc', $docRfc, PDO::PARAM_INT);
    $stmt->bindParam(':docCompDom', $docCompDom, PDO::PARAM_INT);
    $stmt->bindParam(':docActNac', $docActNac, PDO::PARAM_INT);
    $stmt->bindParam(':docNss', $docNss, PDO::PARAM_INT);
    $stmt->bindParam(':docContrato', $docContrato, PDO::PARAM_INT);
    $stmt->bindParam(':docCmc', $docCmc, PDO::PARAM_INT);
    $stmt->bindParam(':docReglaYaviso', $docReglaYaviso, PDO::PARAM_INT);
    $stmt->bindParam(':docPagare', $docPagare, PDO::PARAM_INT);
    $stmt->bindParam(':rfcP', $rfcP);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    try {
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Registro actualizado exitosamente.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al actualizar el registro.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

try {
    $stmt = $conn->prepare("SELECT * FROM personal WHERE IDPERSONAL = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $persona = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$persona) {
        header("Location: read.php");
        exit();
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al consultar los datos: " . $e->getMessage() . "</div>";
    die();
}

$empresas = $conn->query("SELECT IDEMPRESA, EMPRESA FROM empresa")->fetchAll(PDO::FETCH_ASSOC);
$puestos = $conn->query("SELECT IDPUESTO, PUESTO FROM puesto")->fetchAll(PDO::FETCH_ASSOC);
$areasLaborales = $conn->query("SELECT IDAREALABORAL, AREALABORAL FROM arealaboral")->fetchAll(PDO::FETCH_ASSOC);
$especialidades = $conn->query("SELECT IDESPECIALIDAD, ESPECIALIDAD FROM especialidad")->fetchAll(PDO::FETCH_ASSOC);
$supervisores = $conn->query("SELECT IDPERSONAL, CONCAT(NOMBRE, ' ', APELLIDOPATERNO, ' ', APELLIDOMATERNO) AS NOMBRE_COMPLETO FROM personal")->fetchAll(PDO::FETCH_ASSOC);
$tiposEmpleado = $conn->query("SELECT IDTIPODEEMPLEADO, TIPODEEMPLEADO FROM tipodeempleado")->fetchAll(PDO::FETCH_ASSOC);
$estadosCiviles = $conn->query("SELECT IDESTADOCIVIL, ESTADOCIVIL FROM estadocivil")->fetchAll(PDO::FETCH_ASSOC);
$estados = $conn->query("SELECT IDESTADO, ESTADO FROM estado")->fetchAll(PDO::FETCH_ASSOC);
$sexos = $conn->query("SELECT IDSEXO, SEXO FROM sexo")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Editar Personal</h1>
<?php if ($message) echo $message; ?>

<div class="mb-3 text-end">
    <button type="submit" form="editForm" class="btn btn-primary me-2">Guardar Cambios</button>
    <a href="read.php" class="btn btn-secondary">Cancelar</a>
</div>

<form id="editForm" method="POST" class="row form-details g-3">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($persona['NOMBRE']); ?>" required>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Apellido Paterno:</label>
            <input type="text" name="apellidoPaterno" value="<?php echo htmlspecialchars($persona['APELLIDOPATERNO']); ?>" required>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Apellido Materno:</label>
            <input type="text" name="apellidoMaterno" value="<?php echo htmlspecialchars($persona['APELLIDOMATERNO']); ?>" required>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Fecha Nacimiento:</label>
            <input type="date" name="fechaNacimiento" value="<?php echo $persona['FECHANACIMIENTO']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>CURP:</label>
            <input type="text" name="curp" maxlength="18" value="<?php echo htmlspecialchars($persona['CURP']); ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Teléfono Casa:</label>
            <input type="number" name="telCasa" value="<?php echo $persona['TELCASA']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Teléfono Móvil:</label>
            <input type="number" name="telMovil" value="<?php echo $persona['TELMOVIL']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Dirección:</label>
            <textarea name="direccion"><?php echo htmlspecialchars($persona['DIRECCION']); ?></textarea>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($persona['EMAIL']); ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>RFC:</label>
            <input type="text" name="rfcP" maxlength="13" value="<?php echo htmlspecialchars($persona['RFCP']); ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Empresa:</label>
            <select name="empresa">
                <option value="">Seleccione</option>
                <?php foreach ($empresas as $empresa) { ?>
                    <option value="<?php echo $empresa['IDEMPRESA']; ?>" <?php if ($empresa['IDEMPRESA'] == $persona['EMPRESA']) echo 'selected'; ?>><?php echo $empresa['EMPRESA']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Puesto:</label>
            <select name="puesto">
                <option value="">Seleccione</option>
                <?php foreach ($puestos as $puesto) { ?>
                    <option value="<?php echo $puesto['IDPUESTO']; ?>" <?php if ($puesto['IDPUESTO'] == $persona['PUESTO']) echo 'selected'; ?>><?php echo $puesto['PUESTO']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Área Laboral:</label>
            <select name="areaLaboral">
                <option value="">Seleccione</option>
                <?php foreach ($areasLaborales as $area) { ?>
                    <option value="<?php echo $area['IDAREALABORAL']; ?>" <?php if ($area['IDAREALABORAL'] == $persona['AREALABORAL']) echo 'selected'; ?>><?php echo $area['AREALABORAL']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Especialidad:</label>
            <select name="especialidad">
                <option value="">Seleccione</option>
                <?php foreach ($especialidades as $esp) { ?>
                    <option value="<?php echo $esp['IDESPECIALIDAD']; ?>" <?php if ($esp['IDESPECIALIDAD'] == $persona['ESPECIALIDAD']) echo 'selected'; ?>><?php echo $esp['ESPECIALIDAD']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Sueldo IMSS:</label>
            <input type="number" name="sueldoImss" value="<?php echo $persona['SUELDOIMSS']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Número IMSS:</label>
            <input type="text" name="numImss" maxlength="12" value="<?php echo htmlspecialchars($persona['NUMIMSS']); ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Número Cuenta Banamex:</label>
            <input type="text" name="numCtaBanamex" maxlength="22" value="<?php echo htmlspecialchars($persona['NUMCTABANAMEX']); ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Supervisor:</label>
            <select name="supervisor">
                <option value="">Seleccione</option>
                <?php foreach ($supervisores as $sup) { ?>
                    <option value="<?php echo $sup['IDPERSONAL']; ?>" <?php if ($sup['IDPERSONAL'] == $persona['SUPERVISOR']) echo 'selected'; ?>><?php echo $sup['NOMBRE_COMPLETO']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Fecha Ingreso:</label>
            <input type="date" name="fechaIngreso" value="<?php echo $persona['FECHAINGRESO']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Inicio Fin Contrato:</label>
            <input type="date" name="fechaInicioFinContrato" value="<?php echo $persona['FECHAINICIOFINCONTRATO']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Vencimiento Contrato:</label>
            <input type="date" name="vencimientoContrato" value="<?php echo $persona['VENCIMIENTOCONTRATO']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Renovación Contrato:</label>
            <input type="date" name="renovacionContrato" value="<?php echo $persona['RENOVACIONCONTRATO']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Aviso Fin Contrato:</label>
            <input type="date" name="avisoFinContrato" value="<?php echo $persona['AVISOFINDECONTRATO']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Fecha Contrato Confidencialidad:</label>
            <input type="date" name="fechaContratoConfidencialidad" value="<?php echo $persona['FECHADECONTRATODECONFIDENCIALIDAD']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Fecha Baja:</label>
            <input type="date" name="fechaBaja" value="<?php echo $persona['FECHADEBAJA']; ?>">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Observaciones:</label>
            <textarea name="observaciones"><?php echo htmlspecialchars($persona['OBSERVACIONES']); ?></textarea>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Tipo Empleado:</label>
            <select name="tipoEmpleado">
                <option value="">Seleccione</option>
                <?php foreach ($tiposEmpleado as $tipo) { ?>
                    <option value="<?php echo $tipo['IDTIPODEEMPLEADO']; ?>" <?php if ($tipo['IDTIPODEEMPLEADO'] == $persona['TIPOEMPLEADO']) echo 'selected'; ?>><?php echo $tipo['TIPODEEMPLEADO']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Estado:</label>
            <select name="estado">
                <option value="">Seleccione</option>
                <?php foreach ($estados as $estado) { ?>
                    <option value="<?php echo $estado['IDESTADO']; ?>" <?php if ($estado['IDESTADO'] == $persona['ESTADO']) echo 'selected'; ?>><?php echo $estado['ESTADO']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Estado Civil:</label>
            <select name="estadoCivil">
                <option value="">Seleccione</option>
                <?php foreach ($estadosCiviles as $ec) { ?>
                    <option value="<?php echo $ec['IDESTADOCIVIL']; ?>" <?php if ($ec['IDESTADOCIVIL'] == $persona['ESTADOCIVIL']) echo 'selected'; ?>><?php echo $ec['ESTADOCIVIL']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Motivo Baja:</label>
            <textarea name="motivoBaja"><?php echo htmlspecialchars($persona['MOTIVODEBAJA']); ?></textarea>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Sexo:</label>
            <select name="sexo">
                <option value="2" <?php if ($persona['SEXO'] == 2) echo 'selected'; ?>>No especificado</option>
                <?php foreach ($sexos as $sexo) { ?>
                    <option value="<?php echo $sexo['IDSEXO']; ?>" <?php if ($sexo['IDSEXO'] == $persona['SEXO']) echo 'selected'; ?>><?php echo $sexo['SEXO']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">
            <label>Ayuda Pasajes x Día:</label>
            <input type="number" step="0.01" name="ayudaPasajesXDia" value="<?php echo $persona['AYUDAPASAJESXDIA']; ?>">
        </div>
    </div>
    <!-- Checkboxes en columnas -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>INE:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docIne" <?php if ($persona['DOCINE']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>CURP:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docCurp" <?php if ($persona['DOCCURP']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>RFC:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docRfc" <?php if ($persona['DOCRFC']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>Comprobante Domicilio:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docCompDom" <?php if ($persona['DOCCOMPDOM']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>Acta Nacimiento:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docActNac" <?php if ($persona['DOCACTNAC']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>NSS:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docNss" <?php if ($persona['DOCNSS']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>Contrato:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docContrato" <?php if ($persona['DOCCONTRATO']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>CMC:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docCmc" <?php if ($persona['DOCCMC']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>Reglamento y Aviso:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docReglaYaviso" <?php if ($persona['DOCREGLAYAVISO']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <div class="checkbox-group">
            <label>Pagaré:</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="docPagare" <?php if ($persona['DOCPAGARE']) echo 'checked'; ?>>
            </div>
        </div>
    </div>
</form>

<?php include '../footer.php'; ?>