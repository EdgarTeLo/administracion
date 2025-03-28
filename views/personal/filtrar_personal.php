<?php
include "../config/conexion.php";
$db = new Database();
$conn = $db->getConnection();

// Obtener filtros desde POST
$estado = isset($_POST['estado']) ? $_POST['estado'] : '1'; // Default a "Activo"
$busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';
$orden = isset($_POST['orden']) ? trim($_POST['orden']) : '';

// Construir la consulta SQL
$sql = "SELECT p.IDPERSONAL, p.NOMBRE, p.APELLIDOPATERNO, p.APELLIDOMATERNO, p.EMAIL, p.TELMOVIL, p.ESTADO, e.EMPRESA 
        FROM personal p 
        LEFT JOIN empresa e ON p.EMPRESA = e.IDEMPRESA 
        WHERE 1=1";
$params = [];

if ($estado !== '') {
    $sql .= " AND p.ESTADO = :estado";
    $params[':estado'] = $estado;
}

if ($busqueda !== '') {
    $sql .= " AND (p.NOMBRE LIKE :busqueda OR p.APELLIDOPATERNO LIKE :busqueda OR p.APELLIDOMATERNO LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}

// Ordenamiento
if ($orden !== '') {
    $sql .= " ORDER BY " . $orden;
}

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($personal)) {
        echo '<div class="alert alert-info">No se encontraron registros.</div>';
    } else {
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Nombre</th>';
        echo '<th>Apellido Paterno</th>';
        echo '<th>Apellido Materno</th>';
        echo '<th>Email</th>';
        echo '<th>Teléfono Móvil</th>';
        echo '<th><a href="#" onclick="ordenarPor(\'EMPRESA\'); return false;" id="orden_EMPRESA" data-orden="ASC">Empresa</a></th>';
        echo '<th>Estado</th>';
        echo '<th>Acciones</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($personal as $persona) {
            echo '<tr>';
            echo '<td>' . $persona['IDPERSONAL'] . '</td>';
            echo '<td>' . $persona['NOMBRE'] . '</td>';
            echo '<td>' . $persona['APELLIDOPATERNO'] . '</td>';
            echo '<td>' . $persona['APELLIDOMATERNO'] . '</td>';
            echo '<td>' . ($persona['EMAIL'] ?: 'N/A') . '</td>';
            echo '<td>' . ($persona['TELMOVIL'] ?: 'N/A') . '</td>';
            echo '<td>' . ($persona['EMPRESA'] ?: 'N/A') . '</td>';
            echo '<td>' . ($persona['ESTADO'] == 1 ? 'Activo' : 'Inactivo') . '</td>';
            echo '<td>';
            echo '<a href="view.php?id=' . $persona['IDPERSONAL'] . '" class="btn btn-info btn-sm">Ver</a> ';
            echo '<a href="edit.php?id=' . $persona['IDPERSONAL'] . '" class="btn btn-warning btn-sm">Editar</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error al consultar los datos: ' . $e->getMessage() . '</div>';
}
?>