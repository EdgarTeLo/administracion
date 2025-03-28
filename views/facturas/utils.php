<?php
// utils.php - Versión 2.7.9
$fileVersion = '2.7.9'; // Incrementado para corregir extractOCNumber

require_once __DIR__ . '/dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.1.0');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/debug.php';

if (!defined('DEBUG')) {
    define('DEBUG', true);
}

function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        error_log("Error de conexión a la base de datos: " . mysqli_connect_error());
        if (defined('DEBUG') && DEBUG) {
            debugLog("Error de conexión a la base de datos: " . mysqli_connect_error());
        }
        return false;
    }
    if (defined('DEBUG') && DEBUG) {
        debugLog("Conexión a la base de datos establecida con éxito en " . DB_HOST . "/" . DB_NAME);
    }
    return $conn;
}

function unzipFile($zipPath, $destinationDir) {
    $zip = new ZipArchive();
    if ($zip->open($zipPath) === true) {
        // Limpiar directorio antes de extraer, con manejo de errores
        $files = glob($destinationDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                try {
                    if (@unlink($file)) {
                        if (defined('DEBUG') && DEBUG) {
                            debugLog("Eliminado archivo existente: $file");
                        }
                    } else {
                        debugLog("No se pudo eliminar archivo existente: $file (continuando)");
                    }
                } catch (Exception $e) {
                    debugLog("Error al eliminar archivo $file: " . $e->getMessage() . " (continuando)");
                }
            }
        }

        // Extraer los nuevos archivos
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = $zip->getNameIndex($i);
            $destination = $destinationDir . basename($fileName);
            if ($zip->extractTo($destinationDir, [$fileName])) {
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Archivo extraído: $fileName -> $destination");
                }
            } else {
                if (defined('DEBUG') && DEBUG) {
                    debugLog("Error al extraer el archivo: $fileName");
                }
            }
        }
        $zip->close();
        // Intentar eliminar el ZIP, con manejo de errores
        try {
            if (@unlink($zipPath)) {
                debugLog("Archivo ZIP eliminado: $zipPath");
            } else {
                debugLog("No se pudo eliminar el archivo ZIP: $zipPath (continuando)");
            }
        } catch (Exception $e) {
            debugLog("Error al eliminar el archivo ZIP $zipPath: " . $e->getMessage() . " (continuando)");
        }
        return true;
    }
    if (defined('DEBUG') && DEBUG) {
        debugLog("Error al abrir el ZIP: $zipPath");
    }
    return false;
}

function ensureDirectory($dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true)) {
            die("Error: No se pudo crear el directorio: $dir");
        }
    }
    if (!is_writable($dir)) {
        chmod($dir, 0777);
        if (!is_writable($dir)) {
            die("Error: El directorio $dir no tiene permisos de escritura.");
        }
    }
}

function extractTextFromPDF($file) {
    if (!class_exists('Smalot\PdfParser\Parser')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
    $parser = new \Smalot\PdfParser\Parser();
    try {
        $pdf = $parser->parseFile($file);
        return $pdf->getText();
    } catch (Exception $e) {
        debugLog("Error al extraer texto de PDF: " . $e->getMessage());
        return false;
    }
}

function extractOCNumber($text) {
    // Buscar "Orden de compra" o "Purchase Order"
    if (preg_match('/(Orden de compra|Purchase Order)\s+(\d+)/i', $text, $matches)) {
        debugLog("Número de orden extraído: " . $matches[2]);
        return $matches[2];
    }
    debugLog("No se encontró número de orden en el texto.");
    return null;
}

function extractTotal($text) {
    if (preg_match('/Total\s+([\d,.]+)/', $text, $matches)) {
        return floatval(str_replace(',', '', $matches[1]));
    }
    return 0;
}

function extractFechaEmision($text) {
    if (preg_match('/Order Date\s+([0-9]{2}-[A-Z]{3}-[0-9]{4})/', $text, $matches)) {
        return date('Y-m-d', strtotime($matches[1]));
    }
    return null;
}

function extractProveedor($text) {
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $inSupplierSection = false;
    $proveedor = '';

    foreach ($lines as $line) {
        $line = trim($line);
        if (stripos($line, 'Supplier') !== false && stripos($line, 'Details:') === false) {
            $inSupplierSection = true;
            if (preg_match('/Supplier\s*(SEMAMUL SA DE CV)/i', $line, $matches)) {
                $proveedor = $matches[1];
                break;
            }
            continue;
        }
        if ($inSupplierSection && !empty($line) && !preg_match('/^Details:/', $line)) {
            if (stripos($line, 'SEMAMUL SA DE CV') !== false) {
                $proveedor = 'SEMAMUL SA DE CV';
                break;
            }
        }
    }

    if (empty($proveedor) && stripos($text, 'SEMAMUL SA DE CV') !== false) {
        $proveedor = 'SEMAMUL SA DE CV';
    } elseif (empty($proveedor)) {
        $proveedor = 'SEMAMUL SA DE CV'; // Valor por defecto
    }

    debugLog("Proveedor extraído: $proveedor");
    return $proveedor;
}
?>