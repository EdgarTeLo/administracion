<?php
require_once '../header.php';
$pageTitle = "Detalles del Personal";

include "../config/conexion.php";
$db = new Database();
$conn = $db->getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: read.php");
    exit();
}

$id = (int)$_GET['id'];

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

// Lista de campos a mostrar
$campos = [
    ["label" => "ID", "value" => $persona['IDPERSONAL']],
    ["label" => "Fecha de Nacimiento", "value" => $persona['FECHANACIMIENTO']],
    ["label" => "CURP", "value" => $persona['CURP']],
    ["label" => "Teléfono Casa", "value" => $persona['TELCASA'] ?: 'N/A'],
    ["label" => "Teléfono Móvil", "value" => $persona['TELMOVIL'] ?: 'N/A'],
    ["label" => "Dirección", "value" => $persona['DIRECCION']],
    ["label" => "Email", "value" => $persona['EMAIL'] ?: 'N/A'],
    ["label" => "RFC", "value" => $persona['RFCP'] ?: 'N/A'],
    ["label" => "Empresa", "value" => $persona['EMPRESA'] ? getNombre($conn, 'empresa', 'EMPRESA', 'IDEMPRESA', $persona['EMPRESA']) : 'N/A'],
    ["label" => "Puesto", "value" => $persona['PUESTO'] ? getNombre($conn, 'puesto', 'PUESTO', 'IDPUESTO', $persona['PUESTO']) : 'N/A'],
    ["label" => "Área Laboral", "value" => $persona['AREALABORAL'] ? getNombre($conn, 'arealaboral', 'AREALABORAL', 'IDAREALABORAL', $persona['AREALABORAL']) : 'N/A'],
    ["label" => "Especialidad", "value" => $persona['ESPECIALIDAD'] ? getNombre($conn, 'especialidad', 'ESPECIALIDAD', 'IDESPECIALIDAD', $persona['ESPECIALIDAD']) : 'N/A'],
    ["label" => "Sueldo IMSS", "value" => $persona['SUELDOIMSS'] ?: 'N/A'],
    ["label" => "Número IMSS", "value" => $persona['NUMIMSS'] ?: 'N/A'],
    ["label" => "Número Cuenta Banamex", "value" => $persona['NUMCTABANAMEX'] ?: 'N/A'],
    ["label" => "Supervisor", "value" => $persona['SUPERVISOR'] ? getNombreSupervisor($conn, $persona['SUPERVISOR']) : 'N/A'],
    ["label" => "Fecha de Ingreso", "value" => $persona['FECHAINGRESO'] ?: 'N/A'],
    ["label" => "Inicio/Fin Contrato", "value" => $persona['FECHAINICIOFINCONTRATO'] ?: 'N/A'],
    ["label" => "Vencimiento Contrato", "value" => $persona['VENCIMIENTOCONTRATO'] ?: 'N/A'],
    ["label" => "Renovación Contrato", "value" => $persona['RENOVACIONCONTRATO'] ?: 'N/A'],
    ["label" => "Aviso Fin Contrato", "value" => $persona['AVISOFINDECONTRATO'] ?: 'N/A'],
    ["label" => "Fecha Contrato Confidencialidad", "value" => $persona['FECHADECONTRATODECONFIDENCIALIDAD'] ?: 'N/A'],
    ["label" => "Estado", "value" => $persona['ESTADO'] == 1 ? 'Activo' : 'Inactivo'],
    ["label" => "Fecha de Baja", "value" => $persona['FECHADEBAJA'] ?: 'N/A'],
    ["label" => "Estado Civil", "value" => $persona['ESTADOCIVIL'] ? getNombre($conn, 'estadocivil', 'ESTADOCIVIL', 'IDESTADOCIVIL', $persona['ESTADOCIVIL']) : 'N/A'],
    ["label" => "Motivo de Baja", "value" => $persona['MOTIVODEBAJA'] ?: 'N/A'],
    ["label" => "Sexo", "value" => $persona['SEXO'] ? getNombre($conn, 'sexo', 'SEXO', 'IDSEXO', $persona['SEXO']) : 'No especificado'],
    ["label" => "Ayuda Pasajes x Día", "value" => $persona['AYUDAPASAJESXDIA'] ?: '0.00'],
    ["label" => "Documentos", "value" => implode(', ', array_filter([
        $persona['DOCINE'] ? 'INE' : '',
        $persona['DOCCURP'] ? 'CURP' : '',
        $persona['DOCRFC'] ? 'RFC' : '',
        $persona['DOCCOMPDOM'] ? 'Comprobante Domicilio' : '',
        $persona['DOCACTNAC'] ? 'Acta Nacimiento' : '',
        $persona['DOCNSS'] ? 'NSS' : '',
        $persona['DOCCONTRATO'] ? 'Contrato' : '',
        $persona['DOCCMC'] ? 'CMC' : '',
        $persona['DOCREGLAYAVISO'] ? 'Reglamento y Aviso' : '',
        $persona['DOCPAGARE'] ? 'Pagaré' : ''
    ])) ?: 'Ninguno'],
    ["label" => "Observaciones", "value" => $persona['OBSERVACIONES'] ?: 'N/A'],
    ["label" => "Tipo de Empleado", "value" => $persona['TIPOEMPLEADO'] ? getNombre($conn, 'tipodeempleado', 'TIPODEEMPLEADO', 'IDTIPODEEMPLEADO', $persona['TIPOEMPLEADO']) : 'N/A']
];
?>

<h1>Detalles del Personal: <?php echo $persona['NOMBRE'] . ' ' . $persona['APELLIDOPATERNO'] . ' ' . $persona['APELLIDOMATERNO']; ?></h1>

<div class="mb-3 text-end">
    <a href="edit.php?id=<?php echo $persona['IDPERSONAL']; ?>" class="btn btn-primary me-2">Editar</a>
    <a href="read.php" class="btn btn-secondary">Volver</a>
</div>

<div class="row details g-3">
    <?php foreach ($campos as $campo): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <p><strong><?php echo $campo['label']; ?>:</strong> <span class="value"><?php echo htmlspecialchars($campo['value']); ?></span></p>
        </div>
    <?php endforeach; ?>
</div>

<?php
function getNombre($conn, $tabla, $campoNombre, $campoId, $id) {
    try {
        $stmt = $conn->prepare("SELECT $campoNombre FROM $tabla WHERE $campoId = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado[$campoNombre] ?? 'N/A';
    } catch (PDOException $e) {
        return 'Error: ' . $e->getMessage();
    }
}

function getNombreSupervisor($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT CONCAT(NOMBRE, ' ', APELLIDOPATERNO, ' ', APELLIDOMATERNO) AS NOMBRE_COMPLETO FROM personal WHERE IDPERSONAL = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['NOMBRE_COMPLETO'] ?? 'N/A';
    } catch (PDOException $e) {
        return 'Error: ' . $e->getMessage();
    }
}
?>

<?php include '../footer.php'; ?>