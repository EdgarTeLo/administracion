<?php
require_once '../header.php';
$pageTitle = "Crear Personal";

include "../config/conexion.php";
$db = new Database();
$conn = $db->getConnection();

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

    $query = "INSERT INTO personal (
        NOMBRE, APELLIDOPATERNO, APELLIDOMATERNO, FECHANACIMIENTO, CURP, TELCASA, TELMOVIL, DIRECCION, EMPRESA, PUESTO, 
        AREALABORAL, ESPECIALIDAD, SUELDOIMSS, NUMIMSS, NUMCTABANAMEX, SUPERVISOR, FECHAINGRESO, FECHAINICIOFINCONTRATO, 
        VENCIMIENTOCONTRATO, RENOVACIONCONTRATO, AVISOFINDECONTRATO, OBSERVACIONES, TIPOEMPLEADO, FECHADECONTRATODECONFIDENCIALIDAD, 
        ESTADO, FECHADEBAJA, ESTADOCIVIL, MOTIVODEBAJA, EMAIL, SEXO, AYUDAPASAJESXDIA, DOCINE, DOCCURP, DOCRFC, DOCCOMPDOM, 
        DOCACTNAC, DOCNSS, DOCCONTRATO, DOCCMC, DOCREGLAYAVISO, DOCPAGARE, RFCP
    ) VALUES (
        :nombre, :apellidoPaterno, :apellidoMaterno, :fechaNacimiento, :curp, :telCasa, :telMovil, :direccion, :empresa, :puesto, 
        :areaLaboral, :especialidad, :sueldoImss, :numImss, :numCtaBanamex, :supervisor, :fechaIngreso, :fechaInicioFinContrato, 
        :vencimientoContrato, :renovacionContrato, :avisoFinContrato, :observaciones, :tipoEmpleado, :fechaContratoConfidencialidad, 
        :estado, :fechaBaja, :estadoCivil, :motivoBaja, :email, :sexo, :ayudaPasajesXDia, :docIne, :docCurp, :docRfc, :docCompDom, 
        :docActNac, :docNss, :docContrato, :docCmc, :docReglaYaviso, :docPagare, :rfcP
    )";

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

    try {
        if ($stmt->execute()) {
            header("Location: read.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Error al crear el registro.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
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

<h1>Agregar Nuevo Personal</h1>
<?php if ($message) echo $message; ?>

<form method="POST">
    <div class="mb-3">
        <label class="form-label">Nombre:</label>
        <input type="text" class="form-control" name="nombre" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Apellido Paterno:</label>
        <input type="text" class="form-control" name="apellidoPaterno" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Apellido Materno:</label>
        <input type="text" class="form-control" name="apellidoMaterno" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Fecha Nacimiento:</label>
        <input type="date" class="form-control" name="fechaNacimiento" value="1900-01-01">
    </div>
    <div class="mb-3">
        <label class="form-label">CURP:</label>
        <input type="text" class="form-control" name="curp" maxlength="18" placeholder="XXXX000000XXXXXX00">
    </div>
    <div class="mb-3">
        <label class="form-label">Teléfono Casa:</label>
        <input type="number" class="form-control" name="telCasa">
    </div>
    <div class="mb-3">
        <label class="form-label">Teléfono Móvil:</label>
        <input type="number" class="form-control" name="telMovil">
    </div>
    <div class="mb-3">
        <label class="form-label">Dirección:</label>
        <textarea class="form-control" name="direccion" placeholder="Sin dirección"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Email:</label>
        <input type="email" class="form-control" name="email">
    </div>
    <div class="mb-3">
        <label class="form-label">RFC:</label>
        <input type="text" class="form-control" name="rfcP" maxlength="13">
    </div>
    <div class="mb-3">
        <label class="form-label">Empresa:</label>
        <select class="form-select" name="empresa">
            <option value="">Seleccione</option>
            <?php foreach ($empresas as $empresa) { ?>
                <option value="<?php echo $empresa['IDEMPRESA']; ?>"><?php echo $empresa['EMPRESA']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Puesto:</label>
        <select class="form-select" name="puesto">
            <option value="">Seleccione</option>
            <?php foreach ($puestos as $puesto) { ?>
                <option value="<?php echo $puesto['IDPUESTO']; ?>"><?php echo $puesto['PUESTO']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Área Laboral:</label>
        <select class="form-select" name="areaLaboral">
            <option value="">Seleccione</option>
            <?php foreach ($areasLaborales as $area) { ?>
                <option value="<?php echo $area['IDAREALABORAL']; ?>"><?php echo $area['AREALABORAL']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Especialidad:</label>
        <select class="form-select" name="especialidad">
            <option value="">Seleccione</option>
            <?php foreach ($especialidades as $esp) { ?>
                <option value="<?php echo $esp['IDESPECIALIDAD']; ?>"><?php echo $esp['ESPECIALIDAD']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Sueldo IMSS:</label>
        <input type="number" class="form-control" name="sueldoImss">
    </div>
    <div class="mb-3">
        <label class="form-label">Número IMSS:</label>
        <input type="text" class="form-control" name="numImss" maxlength="12">
    </div>
    <div class="mb-3">
        <label class="form-label">Número Cuenta Banamex:</label>
        <input type="text" class="form-control" name="numCtaBanamex" maxlength="22">
    </div>
    <div class="mb-3">
        <label class="form-label">Supervisor:</label>
        <select class="form-select" name="supervisor">
            <option value="">Seleccione</option>
            <?php foreach ($supervisores as $sup) { ?>
                <option value="<?php echo $sup['IDPERSONAL']; ?>"><?php echo $sup['NOMBRE_COMPLETO']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Fecha Ingreso:</label>
        <input type="date" class="form-control" name="fechaIngreso">
    </div>
    <div class="mb-3">
        <label class="form-label">Inicio Fin Contrato:</label>
        <input type="date" class="form-control" name="fechaInicioFinContrato">
    </div>
    <div class="mb-3">
        <label class="form-label">Vencimiento Contrato:</label>
        <input type="date" class="form-control" name="vencimientoContrato">
    </div>
    <div class="mb-3">
        <label class="form-label">Renovación Contrato:</label>
        <input type="date" class="form-control" name="renovacionContrato">
    </div>
    <div class="mb-3">
        <label class="form-label">Aviso Fin Contrato:</label>
        <input type="date" class="form-control" name="avisoFinContrato">
    </div>
    <div class="mb-3">
        <label class="form-label">Fecha Contrato Confidencialidad:</label>
        <input type="date" class="form-control" name="fechaContratoConfidencialidad">
    </div>
    <div class="mb-3">
        <label class="form-label">Fecha Baja:</label>
        <input type="date" class="form-control" name="fechaBaja">
    </div>
    <div class="mb-3">
        <label class="form-label">Observaciones:</label>
        <textarea class="form-control" name="observaciones"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Tipo Empleado:</label>
        <select class="form-select" name="tipoEmpleado">
            <option value="">Seleccione</option>
            <?php foreach ($tiposEmpleado as $tipo) { ?>
                <option value="<?php echo $tipo['IDTIPODEEMPLEADO']; ?>"><?php echo $tipo['TIPODEEMPLEADO']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Estado:</label>
        <select class="form-select" name="estado">
            <option value="">Seleccione</option>
            <?php foreach ($estados as $estado) { ?>
                <option value="<?php echo $estado['IDESTADO']; ?>" <?php if ($estado['IDESTADO'] == 0) echo 'selected'; ?>><?php echo $estado['ESTADO']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Estado Civil:</label>
        <select class="form-select" name="estadoCivil">
            <option value="">Seleccione</option>
            <?php foreach ($estadosCiviles as $ec) { ?>
                <option value="<?php echo $ec['IDESTADOCIVIL']; ?>"><?php echo $ec['ESTADOCIVIL']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Motivo Baja:</label>
        <textarea class="form-control" name="motivoBaja"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Sexo:</label>
        <select class="form-select" name="sexo">
            <option value="2" selected>No especificado</option>
            <?php foreach ($sexos as $sexo) { ?>
                <option value="<?php echo $sexo['IDSEXO']; ?>"><?php echo $sexo['SEXO']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Ayuda Pasajes x Día:</label>
        <input type="number" step="0.01" class="form-control" name="ayudaPasajesXDia" value="0.00">
    </div>
    <div class="mb-3">
        <label class="form-label">Documentos:</label><br>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docIne"> 
            <label class="form-check-label">INE</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docCurp"> 
            <label class="form-check-label">CURP</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docRfc"> 
            <label class="form-check-label">RFC</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docCompDom"> 
            <label class="form-check-label">Comprobante Domicilio</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docActNac"> 
            <label class="form-check-label">Acta Nacimiento</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docNss"> 
            <label class="form-check-label">NSS</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docContrato"> 
            <label class="form-check-label">Contrato</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docCmc"> 
            <label class="form-check-label">CMC</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docReglaYaviso"> 
            <label class="form-check-label">Reglamento y Aviso</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="docPagare"> 
            <label class="form-check-label">Pagaré</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="read.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php include '../footer.php'; ?>