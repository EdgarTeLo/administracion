<?php
// upload_massive.php - Versión 1.0.10
$fileVersion = '1.0.10'; // Incrementado de 1.0.9 para corregir la validación de archivos

require_once 'dependencies.php';
checkFileVersion(__DIR__ . '/upload_massive.php', $fileVersion, '1.0.0');

require_once 'includes/functions.php';

// Directorio de uploads
$uploadDir = __DIR__ . '/uploads/';
$batchSize = 50;

// Iniciar buffer de salida para limpiar cualquier salida no deseada
ob_start();

// Inicializar sesión para almacenar el estado del procesamiento
if (!isset($_SESSION['xml_files']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xml_files'])) {
    $uploadedFiles = $_FILES['xml_files']['tmp_name'];
    $fileNames = $_FILES['xml_files']['name'];
    $_SESSION['xml_files'] = [];
    for ($i = 0; $i < count($uploadedFiles); $i++) {
        if ($_FILES['xml_files']['error'][$i] === UPLOAD_ERR_OK) {
            $destination = $uploadDir . basename($fileNames[$i]);
            if (move_uploaded_file($uploadedFiles[$i], $destination)) {
                $_SESSION['xml_files'][] = $destination;
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Archivo subido con éxito: " . $fileNames[$i] . " -> $destination");
                }
            } else {
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Error al mover el archivo: " . $fileNames[$i]);
                }
            }
        } else {
            if (defined('DEBUG') && DEBUG) {
                debugLog("Error al subir el archivo " . $fileNames[$i] . ": " . $_FILES['xml_files']['error'][$i]);
            }
        }
    }
    $_SESSION['processed_files'] = [];
    $_SESSION['current_batch'] = 0;
    $_SESSION['debug_logs'] = [];
}

// Procesar archivos existentes en uploads si se solicita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_existing'])) {
    $_SESSION['xml_files'] = glob($uploadDir . '*.xml') ?: [];
    $_SESSION['processed_files'] = [];
    $_SESSION['current_batch'] = 0;
    $_SESSION['debug_logs'] = [];
    if (defined('DEBUG') && DEBUG) {
        debugLog("Procesando archivos existentes en $uploadDir: " . print_r($_SESSION['xml_files'], true));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_batch'])) {
    ob_clean();

    $files = $_SESSION['xml_files'] ?? [];
    $processed = $_SESSION['processed_files'] ?? [];
    $currentBatch = $_SESSION['current_batch'] ?? 0;
    $debugLogs = &$_SESSION['debug_logs'];

    $start = $currentBatch * $batchSize;
    $end = min($start + $batchSize, count($files));
    $batchFiles = array_slice($files, $start, $batchSize);

    if (defined('DEBUG') && DEBUG) {
        debugLog("Procesando lote #$currentBatch. Archivos: " . print_r($batchFiles, true));
    }

    foreach ($batchFiles as $file) {
        if (defined('DEBUG') && DEBUG) {
            debugLog("Procesando archivo XML: " . $file);
        }
        try {
            $result = processCFDIXML($file);
            if ($result === "duplicate") {
                $processed[] = "El archivo " . basename($file) . " ya está registrado (duplicado).";
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Archivo duplicado: " . basename($file));
                }
            } else if ($result === false) {
                $processed[] = "Error al procesar el archivo " . basename($file) . ".";
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Error al procesar el archivo XML: " . $file);
                }
            } else if (is_numeric($result)) {
                $processed[] = "Procesado " . basename($file) . " con ID: " . $result . " (Factura FF)";
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Archivo procesado con éxito (FF): " . basename($file) . " - ID: $result");
                }
            } else if ($result === true) {
                $processed[] = "Procesado " . basename($file) . " (Pago PP actualizado)";
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Archivo procesado con éxito (PP): " . basename($file));
                }
            }
        } catch (Exception $e) {
            $processed[] = "Error al procesar el archivo " . basename($file) . ": " . $e->getMessage();
            if (defined('DEBUG') && DEBUG) {
                debugLog("Excepción al procesar el archivo XML: " . $file . " - " . $e->getMessage());
            }
        }
    }

    $_SESSION['processed_files'] = $processed;
    $_SESSION['current_batch'] = $currentBatch + 1;

    $totalFiles = count($files);
    $processedFiles = min(($currentBatch + 1) * $batchSize, $totalFiles);
    $progress = ($totalFiles > 0) ? min(100, ($processedFiles / $totalFiles) * 100) : 0;

    if (defined('DEBUG') && DEBUG) {
        debugLog("Progreso del lote #$currentBatch: $progress% completado");
    }

    header('Content-Type: application/json');
    echo json_encode([
        'progress' => $progress,
        'results' => array_slice($processed, $start, $batchSize),
        'completed' => $processedFiles >= $totalFiles
    ]);
    ob_end_flush();
    exit;
}

// Limpiar sesión al finalizar o si el usuario recarga
if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
    session_destroy();
    header("Location: upload_massive.php");
    exit;
}

// Obtener logs de depuración
if (isset($_GET['get_debug_logs'])) {
    ob_clean();
    if (defined('DEBUG') && DEBUG) {
        echo htmlspecialchars(implode("\n", $_SESSION['debug_logs'] ?? []));
    }
    header('Content-Type: text/plain');
    ob_end_flush();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carga Masiva de Facturas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress { margin-top: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .debug-section { margin-top: 20px; display: none; }
        .debug-section.active { display: block; }
        pre { white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow-y: auto; }
        .results-container { max-height: 400px; overflow-y: auto; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Carga Masiva de Facturas</h2>
        <form id="uploadForm" method="post" enctype="multipart/form-data" class="mb-4">
            <input type="hidden" name="start_batch" value="1" id="start_batch">
            <div class="mb-3">
                <label for="xml_files" class="form-label">Selecciona los archivos XML (FF y PP):</label>
                <input type="file" name="xml_files[]" id="xml_files" class="form-control" multiple accept=".xml">
            </div>
            <button type="submit" class="btn btn-primary" id="uploadButton">Iniciar Carga Masiva por Lotes</button>
            <button type="submit" name="process_existing" class="btn btn-secondary ms-2" id="processExistingButton">Procesar Archivos Existentes</button>
        </form>
        <div class="progress">
            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0.00%</div>
        </div>
        <div id="resultsContainer" class="results-container">
            <h3>Resultados:</h3>
            <ul id="resultsList" class="list-group"></ul>
        </div>
        <a href="index.php" class="btn btn-secondary mt-3">Volver a la Lista</a>
        <a href="?clear=true" class="btn btn-danger mt-3 ms-2" onclick="return confirm('¿Estás seguro de limpiar la sesión y reiniciar?')">Reiniciar</a>

        <?php if (defined('DEBUG') && DEBUG): ?>
            <div class="debug-section mt-4 active">
                <h3>Logs de Depuración</h3>
                <pre id="debugLogs"><?php echo htmlspecialchars(implode("\n", $_SESSION['debug_logs'] ?? [])); ?></pre>
                <button id="downloadLogs" class="btn btn-success mt-2">Descargar Logs</button>
                <button id="copyLogs" class="btn btn-info mt-2 ms-2">Copiar Logs al Portapapeles</button>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let processing = false;

            $('#uploadForm').submit(function(e) {
                e.preventDefault();
                if (processing) return;

                const processExisting = $(this).find('[name="process_existing"]').is(':submit') && e.originalEvent.submitter.name === 'process_existing';
                const startBatch = $(this).find('#start_batch').val() === '1' && e.originalEvent.submitter.id === 'uploadButton';

                if (processExisting) {
                    processing = true;
                    $('#progressBar').css('width', '0%').text('0.00%');
                    $('#resultsList').empty();

                    const formData = new FormData(this);
                    formData.append('process_existing', '1');

                    $.ajax({
                        url: 'upload_massive.php',
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.progress) {
                                const progressFormatted = response.progress.toFixed(2) + '%';
                                $('#progressBar').css('width', response.progress + '%').text(progressFormatted).attr('aria-valuenow', response.progress);
                            }
                            if (response.results) {
                                response.results.forEach(result => {
                                    const li = $('<li>').addClass('list-group-item')
                                        .addClass(result.includes('Error') || result.includes('duplicado') ? 'list-group-item-danger' : 'list-group-item-success')
                                        .text(result);
                                    $('#resultsList').append(li);
                                });
                            }
                            if (!response.completed) {
                                processBatch();
                            } else {
                                processing = false;
                                alert('Carga masiva completada.');
                                <?php if (defined('DEBUG') && DEBUG): ?>
                                    $.ajax({
                                        url: 'upload_massive.php',
                                        method: 'GET',
                                        data: { get_debug_logs: 1 },
                                        success: function(logs) {
                                            $('#debugLogs').text(logs);
                                        }
                                    });
                                <?php endif; ?>
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log("Error al procesar el lote:");
                            console.log("Estado: " + status);
                            console.log("Error: " + error);
                            console.log("Respuesta cruda del servidor:");
                            console.log(xhr.responseText);
                            alert('Error al procesar el lote: ' + error + '\nRevisa la consola para más detalles.');
                            processing = false;
                        }
                    });
                } else if (startBatch) {
                    const xmlFiles = $('#xml_files')[0].files;
                    if (xmlFiles.length === 0) {
                        alert('Por favor, selecciona al menos un archivo XML.');
                        return;
                    }
                    processing = true;
                    $('#progressBar').css('width', '0%').text('0.00%');
                    $('#resultsList').empty();

                    const formData = new FormData(this);
                    $.ajax({
                        url: 'upload_massive.php',
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.progress) {
                                const progressFormatted = response.progress.toFixed(2) + '%';
                                $('#progressBar').css('width', response.progress + '%').text(progressFormatted).attr('aria-valuenow', response.progress);
                            }
                            if (response.results) {
                                response.results.forEach(result => {
                                    const li = $('<li>').addClass('list-group-item')
                                        .addClass(result.includes('Error') || result.includes('duplicado') ? 'list-group-item-danger' : 'list-group-item-success')
                                        .text(result);
                                    $('#resultsList').append(li);
                                });
                            }
                            if (!response.completed) {
                                processBatch();
                            } else {
                                processing = false;
                                alert('Carga masiva completada.');
                                <?php if (defined('DEBUG') && DEBUG): ?>
                                    $.ajax({
                                        url: 'upload_massive.php',
                                        method: 'GET',
                                        data: { get_debug_logs: 1 },
                                        success: function(logs) {
                                            $('#debugLogs').text(logs);
                                        }
                                    });
                                <?php endif; ?>
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log("Error al procesar el lote:");
                            console.log("Estado: " + status);
                            console.log("Error: " + error);
                            console.log("Respuesta cruda del servidor:");
                            console.log(xhr.responseText);
                            alert('Error al procesar el lote: ' + error + '\nRevisa la consola para más detalles.');
                            processing = false;
                        }
                    });
                }
            });

            function processBatch() {
                $.ajax({
                    url: 'upload_massive.php',
                    method: 'POST',
                    data: { start_batch: 1 },
                    dataType: 'json',
                    success: function(response) {
                        if (response.progress) {
                            const progressFormatted = response.progress.toFixed(2) + '%';
                            $('#progressBar').css('width', response.progress + '%').text(progressFormatted).attr('aria-valuenow', response.progress);
                        }
                        if (response.results) {
                            response.results.forEach(result => {
                                const li = $('<li>').addClass('list-group-item')
                                    .addClass(result.includes('Error') || result.includes('duplicado') ? 'list-group-item-danger' : 'list-group-item-success')
                                    .text(result);
                                $('#resultsList').append(li);
                            });
                        }
                        if (!response.completed) {
                            processBatch();
                        } else {
                            processing = false;
                            alert('Carga masiva completada.');
                            <?php if (defined('DEBUG') && DEBUG): ?>
                                $.ajax({
                                    url: 'upload_massive.php',
                                    method: 'GET',
                                    data: { get_debug_logs: 1 },
                                    success: function(logs) {
                                        $('#debugLogs').text(logs);
                                    }
                                });
                            <?php endif; ?>
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Error al procesar el lote:");
                        console.log("Estado: " + status);
                        console.log("Error: " + error);
                        console.log("Respuesta cruda del servidor:");
                        console.log(xhr.responseText);
                        alert('Error al procesar el lote: ' + error + '\nRevisa la consola para más detalles.');
                        processing = false;
                    }
                });
            }

            $('#downloadLogs').click(function() {
                const logs = $('#debugLogs').text();
                const blob = new Blob([logs], { type: 'text/plain;charset=utf-8' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'debug_logs_' + new Date().toISOString().replace(/[:.]/g, '-') + '.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            });

            $('#copyLogs').click(function() {
                const logs = $('#debugLogs').text();
                navigator.clipboard.writeText(logs).then(() => {
                    alert('Logs copiados al portapapeles.');
                }).catch(err => {
                    alert('Error al copiar los logs: ' + err);
                });
            });
        });
    </script>
    <?php if (defined('DEBUG') && DEBUG): ?>
        <script>
            console.log("Depuración activa (DEBUG=true). Revisar logs en C:\\xampp\\php\\logs\\php_error.log o usar los botones para descargar/copiar.");
        </script>
    <?php endif; ?>
</body>
</html>