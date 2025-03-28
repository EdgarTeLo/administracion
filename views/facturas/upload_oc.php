<?php
// upload_oc.php - Versión 1.1.8
$fileVersion = '1.1.8'; // Incrementado para corregir selectFilesToProcess y credenciales

require_once 'utils.php';
require_once 'config/database.php';
checkFileVersion(__DIR__ . '/upload_oc.php', $fileVersion, '1.0.0');

if (!defined('DEBUG')) {
    define('DEBUG', true);
}

$uploadDir = __DIR__ . '/uploads_oc/';
$sourceDir = __DIR__ . '/uploads_oc/';
ensureDirectory($uploadDir);

if (!session_id()) session_start();

debugLog("Iniciando upload_oc.php");
debugLog("Versión de upload_oc.php: $fileVersion");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_file'])) {
    $_SESSION['oc_files'] = [];
    $_SESSION['processed_files'] = [];
    $_SESSION['current_batch'] = 0;
    $_SESSION['debug_logs'] = [];

    if ($_FILES['zip_file']['error'] === UPLOAD_ERR_OK) {
        $zipFile = $_FILES['zip_file']['tmp_name'];
        $zipName = $_FILES['zip_file']['name'];
        $tempZipPath = $uploadDir . basename($zipName);

        if (move_uploaded_file($zipFile, $tempZipPath)) {
            if (unzipFile($tempZipPath, $uploadDir)) {
                $allFiles = glob($uploadDir . '*.pdf', GLOB_BRACE); // Obtener lista de archivos PDF
                $_SESSION['oc_files'] = selectFilesToProcess($allFiles); // Pasar array de archivos
                debugLog("ZIP subido y descomprimido con éxito: $zipName. Archivos PDF seleccionados: " . print_r($_SESSION['oc_files'], true));
                enqueueFiles($_SESSION['oc_files']);
                exit; // Terminar ejecución después de enviar JSON
            } else {
                $_SESSION['processed_files'][] = "Error al descomprimir el archivo ZIP: $zipName";
                debugLog("Error al descomprimir el archivo ZIP: $zipName");
                echo json_encode(['completed' => false, 'progress' => 0, 'results' => ['Error al descomprimir el archivo ZIP: ' . $zipName], 'processed_count' => 0, 'total_count' => 0]);
                exit;
            }
        } else {
            $_SESSION['processed_files'][] = "Error al mover el archivo ZIP: $zipName";
            debugLog("Error al mover el archivo ZIP: $zipName");
            echo json_encode(['completed' => false, 'progress' => 0, 'results' => ['Error al mover el archivo ZIP: ' . $zipName], 'processed_count' => 0, 'total_count' => 0]);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_from_folder'])) {
    debugLog("Cargando archivos desde la carpeta: $sourceDir");

    $_SESSION['oc_files'] = [];
    $_SESSION['processed_files'] = [];
    $_SESSION['current_batch'] = 0;
    $_SESSION['debug_logs'] = [];

    if (!is_dir($sourceDir)) {
        $_SESSION['processed_files'][] = "Error: La carpeta $sourceDir no existe o no es accesible.";
        debugLog("Error: La carpeta $sourceDir no existe o no es accesible.");
        echo json_encode(['completed' => false, 'progress' => 0, 'results' => ["Error: La carpeta $sourceDir no existe o no es accesible."], 'processed_count' => 0, 'total_count' => 0]);
        exit;
    } else {
        $zipFiles = glob($sourceDir . '*.zip');
        $pattern = '/PO_(2[5-9]|3\d)[_\s-]*\d+(_[0-2])?\.zip/i';

        if (!empty($zipFiles)) {
            foreach ($zipFiles as $zipFile) {
                $zipName = basename($zipFile);
                if (preg_match($pattern, $zipName)) {
                    debugLog("Intentando procesar: $zipName desde $zipFile");
                    if (unzipFile($zipFile, $uploadDir)) {
                        debugLog("ZIP descomprimido con éxito: $zipName");
                    } else {
                        $_SESSION['processed_files'][] = "Error al descomprimir el archivo ZIP desde carpeta: $zipName";
                        debugLog("Error al descomprimir el archivo ZIP desde carpeta: $zipName");
                    }
                }
            }
        } else {
            debugLog("No se encontraron archivos ZIP en $sourceDir. Continuando con PDFs existentes.");
        }

        $allFiles = glob($uploadDir . '*.pdf', GLOB_BRACE);
        if (!empty($allFiles) && is_array($allFiles)) {
            $_SESSION['oc_files'] = selectFilesToProcess($allFiles);
            debugLog("Archivos PDF seleccionados para procesar: " . print_r($_SESSION['oc_files'], true));
            enqueueFiles($_SESSION['oc_files']);
            exit; // Terminar ejecución después de enviar JSON
        } else {
            $_SESSION['processed_files'][] = "Advertencia: No se encontraron archivos PDF en la carpeta para procesar o $allFiles no es un array.";
            debugLog("Advertencia: No se encontraron archivos PDF en la carpeta para procesar o $allFiles no es un array. Valor de allFiles: " . var_export($allFiles, true));
            echo json_encode(['completed' => false, 'progress' => 0, 'results' => ["Advertencia: No se encontraron archivos PDF en la carpeta para procesar."], 'processed_count' => 0, 'total_count' => 0]);
            exit;
        }
    }
}

if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
    debugLog("Limpiando sesión (clear=true)");
    session_destroy();
    header("Location: upload_oc.php");
    exit;
}

if (isset($_GET['get_debug_logs'])) {
    debugLog("Obteniendo logs de depuración (get_debug_logs)");
    ob_clean();
    echo htmlspecialchars(implode("\n", $_SESSION['debug_logs'] ?? []));
    header('Content-Type: text/plain');
    ob_end_flush();
    exit;
}

function selectFilesToProcess($files) {
    $filesToProcess = [];
    if (is_array($files)) {
        foreach ($files as $file) {
            if (is_file($file)) {
                $text = extractTextFromPDF($file);
                if ($text !== false) {
                    $numero_oc = extractOCNumber($text);
                    if ($numero_oc) {
                        $filesToProcess[] = $file;
                        debugLog("Archivo seleccionado para procesar: $file (OC: $numero_oc)");
                    } else {
                        debugLog("No se pudo extraer numero_oc de $file");
                    }
                } else {
                    debugLog("No se pudo extraer texto de $file");
                }
            }
        }
    } else {
        debugLog("Error: selectFilesToProcess recibió un argumento no array: " . var_export($files, true));
    }
    return $filesToProcess;
}

function enqueueFiles($files) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'pdf_user', 'pdf_password', 'pdf_processing_vhost');
        $channel = $connection->channel();
        $channel->queue_declare('pdf_processing', false, true, false, false);

        foreach ($files as $file) {
            $msg = new \PhpAmqpLib\Message\AMQPMessage($file, ['delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT]);
            $channel->basic_publish($msg, '', 'pdf_processing');
            debugLog("Encolado archivo: $file");
        }

        $channel->close();
        $connection->close();
        echo json_encode(['completed' => true, 'progress' => 100, 'results' => ['Archivos encolados para procesamiento.'], 'processed_count' => count($files), 'total_count' => count($files)]);
    } catch (Exception $e) {
        debugLog("Error al encolar archivos: " . $e->getMessage());
        echo json_encode(['completed' => false, 'progress' => 0, 'results' => ['Error al encolar archivos: ' . $e->getMessage()], 'processed_count' => 0, 'total_count' => count($files)]);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carga Masiva de Órdenes de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress { margin-top: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .debug-section { margin-top: 20px; display: none; }
        .debug-section.active { display: block; }
        pre { white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow-y: auto; }
        .results-container { max-height: 400px; overflow-y: auto; margin-top: 20px; }
        .progress-counter { margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Carga Masiva de Órdenes de Compra</h2>
        <form id="uploadForm" method="post" enctype="multipart/form-data" class="mb-4">
            <input type="hidden" name="start_batch" value="1" id="start_batch">
            <div class="mb-3">
                <label for="zip_file" class="form-label">Sube un archivo ZIP con PDFs de OC:</label>
                <input type="file" name="zip_file" id="zip_file" class="form-control" accept=".zip" required>
            </div>
            <button type="submit" class="btn btn-primary" id="uploadButton">Iniciar Carga y Procesamiento</button>
        </form>

        <form id="loadFromFolderForm" method="post" class="mb-4">
            <input type="hidden" name="load_from_folder" value="1">
            <button type="submit" class="btn btn-success" id="loadFromFolderButton">Cargar desde Carpeta</button>
        </form>

        <div class="progress">
            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0.00%</div>
        </div>
        <div id="progressCounter" class="progress-counter">0 de 0 procesados</div>
        <div id="resultsContainer" class="results-container">
            <h3>Resultados:</h3>
            <ul id="resultsList" class="list-group"></ul>
        </div>
        <a href="index.php" class="btn btn-secondary mt-3">Volver a la Lista</a>
        <a href="?clear=true" class="btn btn-danger mt-3 ms-2" onclick="return confirm('¿Estás seguro de limpiar la sesión y reiniciar?')">Reiniciar</a>

        <div class="debug-section mt-4 active">
            <h3>Logs de Depuración</h3>
            <pre id="debugLogs"><?php echo htmlspecialchars(implode("\n", $_SESSION['debug_logs'] ?? [])); ?></pre>
            <button id="downloadLogs" class="btn btn-success mt-2">Descargar Logs</button>
            <button id="copyLogs" class="btn btn-info mt-2 ms-2">Copiar Logs al Portapapeles</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let processing = false;

            $('#uploadForm, #loadFromFolderForm').submit(function(e) {
                e.preventDefault();
                if (processing) return;

                if (this.id === 'uploadForm') {
                    const zipFiles = $('#zip_file')[0].files;
                    if (zipFiles.length === 0) {
                        alert('Por favor, selecciona un archivo ZIP.');
                        return;
                    }
                }

                processing = true;
                $('#progressBar').css('width', '0%').text('0.00%');
                $('#progressCounter').text('0 de 0 procesados');
                $('#resultsList').empty();

                const formData = new FormData(this);
                $.ajax({
                    url: 'upload_oc.php',
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
                        if (response.processed_count !== undefined && response.total_count !== undefined) {
                            $('#progressCounter').text(response.processed_count + ' de ' + response.total_count + ' procesados');
                        }
                        if (response.completed) {
                            processing = false;
                            alert('Carga masiva completada.');
                            $.ajax({
                                url: 'upload_oc.php',
                                method: 'GET',
                                data: { get_debug_logs: 1 },
                                success: function(logs) {
                                    $('#debugLogs').text(logs);
                                }
                            });
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
            });

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
    <script>
        console.log("Depuración activa (DEBUG=true). Revisar logs en C:\\xampp\\php\\logs\\php_error.log o usar los botones para descargar/copiar.");
    </script>
</body>
</html>