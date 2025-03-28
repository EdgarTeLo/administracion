<?php
$fileVersion = '1.0.0'; // Versión del archivo

require_once __DIR__ . '/dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.0.0');

require_once 'includes/debug.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar Archivo CSV de Facturas v<?php echo $fileVersion; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error { color: #dc3545; }
        .success { color: #28a745; }
        #proceedButton { display: none; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Importar Archivo CSV de Facturas v<?php echo $fileVersion; ?></h2>
        <form id="csvForm" enctype="multipart/form-data" method="post" action="process_csv.php" class="mb-4">
            <div class="mb-3">
                <label for="csv_file" class="form-label">Selecciona un archivo CSV:</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="delimiter" class="form-label">Delimitador (por defecto ','):</label>
                <input type="text" id="delimiter" name="delimiter" value="," class="form-control" placeholder="Ejemplo: ,, ;, o \t">
            </div>
            <button type="submit" class="btn btn-primary">Subir y Validar CSV</button>
            <button type="button" class="btn btn-secondary mt-2" onclick="debugCSV()">Depurar Encabezados del CSV</button>
        </form>

        <div id="responseContainer"></div>
        <div id="debugInfo"></div>

        <button id="proceedButton" class="btn btn-success" onclick="proceedWithValidRows()">Continuar con las celdas válidas</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function handleResponse(response) {
            const container = $('#responseContainer');
            if (response.success) {
                container.html('<p class="success">' + response.message + '</p>');
                if (response.validRowsCount > 0) {
                    $('#proceedButton').show();
                }
                if (response.hasErrors) {
                    container.append('<p class="error">Errores encontrados: ' + JSON.stringify(response.errors) + '</p>');
                }
            } else {
                container.html('<p class="error">' + response.message + '</p>');
            }
            <?php if (defined('DEBUG') && DEBUG): ?>
                $('#debugInfo').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
            <?php endif; ?>
        }

        $('#csvForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: 'process_csv.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: handleResponse,
                error: function(xhr, status, error) {
                    $('#responseContainer').html('<p class="error">Error al procesar la solicitud: ' + error + '</p>');
                }
            });
        });

        function proceedWithValidRows() {
            $.ajax({
                url: 'process_csv.php?proceed=true',
                type: 'GET',
                success: handleResponse,
                error: function(xhr, status, error) {
                    $('#responseContainer').html('<p class="error">Error al proceder con las filas válidas: ' + error + '</p>');
                }
            });
        }

        function debugCSV() {
            const fileInput = $('#csv_file')[0];
            if (fileInput.files.length === 0) {
                alert('Por favor, selecciona un archivo CSV antes de depurar.');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', fileInput.files[0]);
            formData.append('delimiter', $('#delimiter').val() || ',');

            $.ajax({
                url: 'process_csv.php?debug_response=true',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    handleResponse(response);
                    if (response.hasErrors) {
                        alert('Errores al depurar los encabezados: ' + JSON.stringify(response.errors));
                    } else {
                        alert('Encabezados depurados con éxito. Revisa los detalles en #debugInfo.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#responseContainer').html('<p class="error">Error al depurar los encabezados: ' + error + '</p>');
                }
            });
        }
    </script>
</body>
</html>