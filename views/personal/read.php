<?php
require_once '../header.php';
$pageTitle = "Lista de Personal";

include "../config/conexion.php";
$db = new Database();
$conn = $db->getConnection();
?>

<h1>Lista de Personal</h1>
<a href="create.php" class="btn btn-success mb-3">Agregar Nuevo Personal</a>

<div class="row mb-3">
    <div class="col-md-4">
        <label for="estadoFiltro" class="form-label">Filtrar por Estado:</label>
        <select id="estadoFiltro" class="form-select" onchange="filtrarTabla()">
            <option value="">Todos</option>
            <option value="1" selected>Activos</option>
            <option value="0">No Activos</option>
        </select>
    </div>
    <div class="col-md-4">
        <label for="busqueda" class="form-label">Buscar por Nombre o Apellido:</label>
        <input type="text" id="busqueda" class="form-control" placeholder="Escribe para buscar..." onkeyup="filtrarTabla()">
    </div>
</div>

<div id="tablaPersonal">
    <!-- La tabla se cargará aquí mediante AJAX -->
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function filtrarTabla(orden = '') {
    var estado = $('#estadoFiltro').val();
    var busqueda = $('#busqueda').val();

    $.ajax({
        url: 'filtrar_personal.php',
        method: 'POST',
        data: {
            estado: estado,
            busqueda: busqueda,
            orden: orden
        },
        success: function(response) {
            $('#tablaPersonal').html(response);
        },
        error: function() {
            $('#tablaPersonal').html('<div class="alert alert-danger">Error al cargar los datos.</div>');
        }
    });
}

// Cargar la tabla al iniciar la página con filtro "Activo"
$(document).ready(function() {
    filtrarTabla();
});

// Función para ordenar por columna
function ordenarPor(columna) {
    var ordenActual = $('#orden_' + columna).data('orden') || 'ASC';
    var nuevoOrden = (ordenActual === 'ASC') ? 'DESC' : 'ASC';
    $('#orden_' + columna).data('orden', nuevoOrden);
    filtrarTabla(columna + ' ' + nuevoOrden);
}
</script>

<?php include '../footer.php'; ?>