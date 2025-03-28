<?php
$fileVersion = '1.0.3'; // Incrementado de 1.0.2 para reflejar esta corrección

require_once 'dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.0.0');

// Iniciar buffer de salida
ob_start();

// Aumentar el tiempo de ejecución y el límite de memoria
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '512M');

// Desactivar la visualización de errores para evitar salida no deseada
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Incluir todas las dependencias con manejo de errores
try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/includes/CSVImporter.php';
    require_once __DIR__ . '/includes/CSVValidator.php';
    require_once __DIR__ . '/includes/CSVProcessor.php';
    require_once __DIR__ . '/includes/debug.php';
    require_once __DIR__ . '/includes/functions.php';
} catch (Exception $e) {
    $response = ['success' => false, 'message' => "Error al incluir dependencias: " . $e->getMessage(), 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    if (defined('DEBUG') && DEBUG) {
        debugLog("Error al incluir dependencias: " . $e->getMessage());
    }
    ob_end_flush();
    exit;
}

// Habilitar depuración usando la constante DEBUG
$isDebug = defined('DEBUG') && DEBUG;

// Registrar en logs para depuración
if ($isDebug) {
    debugLog("Procesando CSV en process_csv.php con depuración activa (DEBUG)");
}

// Verificar si las constantes están definidas
if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
    $response = ['success' => false, 'message' => "Error interno: Las constantes de conexión no están definidas.", 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    if ($isDebug) {
        debugLog("Error interno: Las constantes de conexión no están definidas.");
    }
    ob_end_flush();
    exit;
}

// Probar getDBConnection() con manejo de errores
try {
    $conn = getDBConnection();
    if ($conn === false || $conn->connect_error) {
        throw new Exception("Error en la conexión a la base de datos: " . ($conn->connect_error ?? 'Desconocido'));
    }
    if ($isDebug) {
        debugLog("Conexión a la base de datos establecida");
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => "Error en la conexión a la base de datos: " . $e->getMessage(), 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    if ($isDebug) {
        debugLog("Error en la conexión a la base de datos: " . $e->getMessage());
    }
    ob_end_flush();
    exit;
}

// Procesar solicitudes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $delimiter = $_POST['delimiter'] ?? ',';
    $file = $uploadDir . basename($_FILES['csv_file']['name']);

    if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $file)) {
        if ($isDebug) {
            debugLog("Archivo CSV subido exitosamente: " . $file);
        }
        
        try {
            $importer = new CSVImporter($conn, $isDebug, 500, $delimiter);
            $totalRows = 0;
            $validRowsCount = 0;
            $result = $importer->processCSV($file, $delimiter, $totalRows, $validRowsCount);
            // Guardar totalRows en la sesión para usar en ?proceed=true
            $_SESSION['totalRows'] = $totalRows;
            if ($isDebug) {
                debugLog("Procesamiento de CSV completado. Total filas: $totalRows, Filas válidas: $validRowsCount");
            }
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => "Error al procesar el archivo CSV: " . $e->getMessage(), 'totalRows' => $totalRows, 'validRowsCount' => $validRowsCount, 'hasErrors' => true];
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            if ($isDebug) {
                debugLog("Error al procesar el archivo CSV: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            }
            ob_end_flush();
            exit;
        }

        if (!is_array($result)) {
            $response = ['success' => false, 'message' => "Error: Resultado inesperado del procesador CSV.", 'totalRows' => $totalRows, 'validRowsCount' => $validRowsCount, 'hasErrors' => true];
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            if ($isDebug) {
                debugLog("Resultado inesperado del procesador CSV.");
            }
            ob_end_flush();
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        $response = ['success' => false, 'message' => "Error al subir el archivo.", 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("Error al subir el archivo CSV.");
        }
    }
    ob_end_flush();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['proceed']) && $_GET['proceed'] === 'true') {
    if ($isDebug) {
        debugLog("Procesando ?proceed=true en process_csv.php");
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir) || !is_readable($uploadDir)) {
        $response = ['success' => false, 'message' => "El directorio de uploads no existe o no es legible: " . $uploadDir, 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("El directorio de uploads no existe o no es legible: " . $uploadDir);
        }
        ob_end_flush();
        exit;
    }

    $files = glob($uploadDir . '*.csv');
    if ($isDebug) {
        debugLog("Archivos CSV en uploads/: " . print_r($files, true));
    }
    if (empty($files)) {
        $response = ['success' => false, 'message' => "No se encontró un archivo CSV en el directorio de uploads: " . $uploadDir, 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("No se encontró un archivo CSV en el directorio de uploads: " . $uploadDir);
        }
        ob_end_flush();
        exit;
    }

    $file = end($files);
    if (!file_exists($file) || !is_readable($file)) {
        $response = ['success' => false, 'message' => "El archivo CSV '$file' no existe o no es legible.", 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("El archivo CSV '$file' no existe o no es legible.");
        }
        ob_end_flush();
        exit;
    }

    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            if ($isDebug) {
                debugLog("Sesión iniciada correctamente en ?proceed=true");
            }
        } else {
            if ($isDebug) {
                debugLog("Sesión ya iniciada en ?proceed=true");
            }
        }

        if ($isDebug) {
            debugLog("Verificando sesión - validRows: " . (isset($_SESSION['validRows']) ? count($_SESSION['validRows']) . " filas" : 'No definido'));
            debugLog("Verificando sesión - headerMap: " . (isset($_SESSION['headerMap']) ? print_r($_SESSION['headerMap'], true) : 'No definido'));
            debugLog("Verificando sesión - totalRows: " . (isset($_SESSION['totalRows']) ? $_SESSION['totalRows'] : 'No definido'));
        }

        if (!isset($_SESSION['validRows']) || !isset($_SESSION['headerMap']) || !isset($_SESSION['totalRows'])) {
            throw new Exception("No se encontraron filas válidas, mapeo de encabezados o totalRows almacenados para procesar.");
        }

        $validRows = $_SESSION['validRows'];
        $headerMap = $_SESSION['headerMap'];
        $totalRows = $_SESSION['totalRows'];
        $validRowsCount = count($validRows);

        $processor = new CSVProcessor($conn, $isDebug, 500, $headerMap);
        if ($isDebug) {
            debugLog("Instancia de CSVProcessor creada con éxito para procesar $validRowsCount filas válidas con headerMap: " . print_r($headerMap, true));
        }

        $result = $processor->processValidRows($file, $validRows, $totalRows, $validRowsCount, true);

        if ($result['success']) {
            $result['message'] = "Importación completada con éxito. Total filas válidas procesadas: $validRowsCount de $totalRows filas totales.";
        }

        // Limpiar la sesión después de procesar
        unset($_SESSION['validRows']);
        unset($_SESSION['headerMap']);
        unset($_SESSION['totalRows']);
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => "Error al procesar el archivo CSV en ?proceed=true: " . $e->getMessage(), 'totalRows' => $totalRows ?? 0, 'validRowsCount' => $validRowsCount ?? 0, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("Error al procesar el archivo CSV en ?proceed=true: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        }
        ob_end_flush();
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    ob_end_flush();
    if ($isDebug) {
        debugLog("Punto de control 22: Fin de GET proceed");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['debug_response']) && $_GET['debug_response'] === 'true') {
    if ($isDebug) {
        debugLog("Procesando ?debug_response=true en process_csv.php");
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir) || !is_readable($uploadDir)) {
        $response = ['success' => false, 'message' => "El directorio de uploads no existe o no es legible: " . $uploadDir, 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("El directorio de uploads no existe o no es legible: " . $uploadDir);
        }
        ob_end_flush();
        exit;
    }

    $files = glob($uploadDir . '*.csv');
    if ($isDebug) {
        debugLog("Archivos CSV en uploads/: " . print_r($files, true));
    }
    if (empty($files)) {
        $response = ['success' => false, 'message' => "No se encontró un archivo CSV en el directorio de uploads: " . $uploadDir, 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("No se encontró un archivo CSV en el directorio de uploads: " . $uploadDir);
        }
        ob_end_flush();
        exit;
    }

    $file = end($files);
    if (!file_exists($file) || !is_readable($file)) {
        $response = ['success' => false, 'message' => "El archivo CSV '$file' no existe o no es legible.", 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("El archivo CSV '$file' no existe o no es legible.");
        }
        ob_end_flush();
        exit;
    }

    try {
        $importer = new CSVImporter($conn, $isDebug, 500, ',');
        $totalRows = 0;
        $validRowsCount = 0;
        $result = $importer->processCSV($file, ',', $totalRows, $validRowsCount);
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => "Error al procesar el archivo CSV en ?debug_response=true: " . $e->getMessage(), 'totalRows' => $totalRows, 'validRowsCount' => $validRowsCount, 'hasErrors' => true];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        if ($isDebug) {
            debugLog("Error al procesar el archivo CSV en ?debug_response=true: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        }
        ob_end_flush();
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    ob_end_flush();
    if ($isDebug) {
        debugLog("Punto de control 23: Fin de GET debug_response");
    }
} else {
    $response = ['success' => false, 'message' => "Método o parámetros no válidos.", 'totalRows' => 0, 'validRowsCount' => 0, 'hasErrors' => true];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    if ($isDebug) {
        debugLog("Punto de control 24: Fin de solicitud no válida");
    }
    ob_end_flush();
}

if ($isDebug) {
    debugLog("Punto de control 25: Fin del script");
}
ob_end_flush();
?>