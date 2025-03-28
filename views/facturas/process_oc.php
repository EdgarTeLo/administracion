<?php
// process_oc.php - Versión 1.10.30
$fileVersion = '1.10.30'; // Incrementado para corregir descripción y mezcla de ítems

require_once 'utils.php';
require_once 'config/database.php';
checkFileVersion(__DIR__ . '/process_oc.php', $fileVersion, '1.10.0');

if (!defined('DEBUG')) {
    define('DEBUG', true);
}

function processOC($file) {
    global $db;

    if (!$db) {
        debugLog("Error: No se pudo conectar a la base de datos. \$db es null.");
        throw new Exception("Error fatal: No se pudo conectar a la base de datos.");
    }

    debugLog("Procesando archivo PDF: $file");
    $text = extractTextFromPDF($file);
    if ($text === false) {
        debugLog("Error al extraer texto de $file");
        throw new Exception("Error al extraer texto de $file");
    }

    debugLog("Texto extraído del PDF (primeros 5000 caracteres): " . substr($text, 0, 5000));
    $ocData = extractOCData($text);

    if (!$ocData || empty($ocData['numero_oc'])) {
        debugLog("Error al extraer datos de $file: número de orden no encontrado.");
        throw new Exception("Error al extraer datos de $file: número de orden no encontrado.");
    }

    $numero_oc = $ocData['numero_oc'];
    $total = $ocData['total'];
    $fecha_emision = $ocData['fecha_emision'];
    $proveedor = $ocData['proveedor'];
    $items = $ocData['items'];

    if (empty($items)) {
        debugLog("Error: No se extrajeron ítems para el archivo $file. Procesamiento detenido para depuración.");
        throw new Exception("Error: No se extrajeron ítems para el archivo $file. Revisa los logs para más detalles.");
    }

    try {
        $stmt = $db->prepare("SELECT id FROM ordenes_compra WHERE numero_oc = ?");
        $stmt->execute([$numero_oc]);
        $existingOrderId = $stmt->fetchColumn();

        if ($existingOrderId) {
            $stmt = $db->prepare("UPDATE ordenes_compra SET total = ?, fecha_emision = ?, proveedor = ? WHERE id = ?");
            $stmt->execute([$total, $fecha_emision, $proveedor, $existingOrderId]);
            $orden_compra_id = $existingOrderId;
            debugLog("Orden existente actualizada: $numero_oc");
        } else {
            $stmt = $db->prepare("INSERT INTO ordenes_compra (numero_oc, total, fecha_emision, proveedor) VALUES (?, ?, ?, ?)");
            $stmt->execute([$numero_oc, $total, $fecha_emision, $proveedor]);
            $orden_compra_id = $db->lastInsertId();
            debugLog("Nueva orden insertada: $numero_oc");
        }

        $db->exec("DELETE FROM items_ordenes_compra WHERE orden_compra_id = $orden_compra_id");

        if (!empty($items)) {
            $stmt = $db->prepare("INSERT INTO items_ordenes_compra (orden_compra_id, descripcion, cantidad, precio_unitario, importe) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $precio_unitario = isset($item['precio_unitario']) ? $item['precio_unitario'] : 0;
                $importe = isset($item['importe']) ? $item['importe'] : $precio_unitario * $item['cantidad'];
                $query = "INSERT INTO items_ordenes_compra (orden_compra_id, descripcion, cantidad, precio_unitario, importe) VALUES (?, ?, ?, ?, ?)";
                $values = [$orden_compra_id, $item['descripcion'], $item['cantidad'], $precio_unitario, $importe];
                debugLog("Consulta SQL generada para ítem: $query con valores: " . implode(', ', $values));
                $stmt->execute($values);
                debugLog("Ítem insertado: " . print_r($item, true));

                $checkStmt = $db->prepare("SELECT COUNT(*) FROM items_ordenes_compra WHERE orden_compra_id = ? AND descripcion = ?");
                $checkStmt->execute([$orden_compra_id, $item['descripcion']]);
                $count = $checkStmt->fetchColumn();
                if ($count == 0) {
                    debugLog("Error: El ítem no se insertó correctamente en la base de datos. Procesamiento detenido.");
                    throw new Exception("Error: El ítem no se insertó correctamente en la base de datos. Revisa los logs para más detalles.");
                }
                debugLog("Ítem verificado en la base de datos: $count fila(s) encontrada(s) para orden_compra_id=$orden_compra_id y descripcion=" . $item['descripcion']);
            }
        }

        $fileHash = md5_file($file);
        $stmt = $db->prepare("INSERT INTO processed_files (file_hash, file_name, numero_oc) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE file_name = ?");
        $query = "INSERT INTO processed_files (file_hash, file_name, numero_oc) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE file_name = ?";
        $values = [$fileHash, basename($file), $numero_oc, basename($file)];
        debugLog("Consulta SQL para processed_files: $query con valores: " . implode(', ', $values));
        $stmt->execute($values);

        debugLog("OC procesada con éxito: $numero_oc, Total: $total, Fecha Emisión: $fecha_emision, Proveedor: $proveedor, Ítems: " . print_r($items, true));
    } catch (Exception $e) {
        debugLog("Error al procesar $file: " . $e->getMessage());
        throw $e;
    }
}

function extractOCData($text) {
    $ocNumber = extractOCNumber($text);
    $total = extractTotal($text);
    $fecha_emision = extractFechaEmision($text);
    $proveedor = extractProveedor($text);
    $items = extractItems($text);

    return [
        'numero_oc' => $ocNumber ?: null,
        'total' => $total ?: 0,
        'fecha_emision' => $fecha_emision ?: null,
        'proveedor' => $proveedor ?: null,
        'items' => $items ?: []
    ];
}

function extractItems($text) {
    $items = [];
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $inItemsSection = false;
    $currentItem = [];
    $descriptionBuffer = [];

    foreach ($lines as $index => $line) {
        $line = trim($line);
        debugLog("Línea analizada [$index]: '$line'");

        // Detectar el inicio de la sección de ítems
        if (stripos($line, 'Line') !== false && stripos($line, 'Item') !== false && stripos($line, 'Price') !== false && stripos($line, 'Quantity') !== false && stripos($line, 'UOM') !== false && stripos($line, 'Ordered') !== false && stripos($line, 'Taxable') !== false) {
            $inItemsSection = true;
            if (!empty($currentItem)) {
                $currentItem['descripcion'] = implode(' ', array_filter($descriptionBuffer));
                if (!isset($currentItem['cantidad'])) $currentItem['cantidad'] = 1;
                if (!isset($currentItem['precio_unitario'])) $currentItem['precio_unitario'] = 0;
                if (!isset($currentItem['importe'])) $currentItem['importe'] = $currentItem['precio_unitario'] * $currentItem['cantidad'];
                $items[] = $currentItem;
                debugLog("Ítem guardado al iniciar nueva sección: " . print_r($currentItem, true));
                $currentItem = [];
                $descriptionBuffer = [];
            }
            debugLog("Inicio de sección de ítems detectado en línea $index: '$line'");
            continue;
        }

        if ($inItemsSection) {
            // Detectar una línea con precio y UOM para asociarla con la descripción acumulada
            $priceUOMRegex = '/^(\d+[,.]\d*)\s+([a-zA-Z]+)$/';
            if (preg_match($priceUOMRegex, $line, $priceUOMMatches)) {
                if (!empty($currentItem)) {
                    $currentItem['descripcion'] = implode(' ', array_filter($descriptionBuffer));
                    if (!isset($currentItem['cantidad'])) $currentItem['cantidad'] = 1;
                    $currentItem['precio_unitario'] = floatval(str_replace(',', '', $priceUOMMatches[1]));
                    $currentItem['importe'] = $currentItem['precio_unitario'] * $currentItem['cantidad'];
                    $currentItem['uom'] = $priceUOMMatches[2];
                    $items[] = $currentItem;
                    debugLog("Ítem completo detectado y guardado: " . print_r($currentItem, true));
                }
                $currentItem = ['line' => $index + 1];
                $descriptionBuffer[] = $line;
                debugLog("Descripción adicional acumulada: " . end($descriptionBuffer));
            } elseif (preg_match('/^Promised\s+(\d+)\s+([a-zA-Z]+)\s+([\d,.]+)/i', $line, $promisedMatches)) {
                if (empty($currentItem)) {
                    $currentItem = ['line' => $index + 1];
                }
                $currentItem['cantidad'] = floatval(str_replace(',', '', $promisedMatches[1]));
                $currentItem['uom'] = $promisedMatches[2];
                $currentItem['importe'] = floatval(str_replace(',', '', $promisedMatches[3]));
                $currentItem['precio_unitario'] = $currentItem['importe'] / $currentItem['cantidad'];
                $currentItem['descripcion'] = implode(' ', array_filter($descriptionBuffer));
                debugLog("Ítem con Promised antes de guardar: " . print_r($currentItem, true));
            } elseif (preg_match('/^Line Total\s+([\d,.]+)/', $line, $lineTotalMatches)) {
                if (!empty($currentItem)) {
                    $currentItem['importe'] = floatval(str_replace(',', '', $lineTotalMatches[1]));
                    if (isset($currentItem['cantidad']) && $currentItem['cantidad'] > 0) {
                        $currentItem['precio_unitario'] = $currentItem['importe'] / $currentItem['cantidad'];
                    } else {
                        $currentItem['precio_unitario'] = $currentItem['importe'];
                        $currentItem['cantidad'] = 1;
                    }
                    $currentItem['descripcion'] = implode(' ', array_filter($descriptionBuffer));
                    $items[] = $currentItem;
                    debugLog("Ítem con Line Total detectado y guardado: " . print_r($currentItem, true));
                }
                $currentItem = [];
                $descriptionBuffer = [];
            } elseif (stripos($line, 'Supplier Item') !== false || stripos($line, 'Notes') !== false) {
                $descriptionBuffer[] = trim(str_replace(['Supplier Item', 'Notes'], '', $line));
                debugLog("Descripción adicional (Supplier Item/Notes): " . end($descriptionBuffer));
            } elseif (preg_match('/^Total\s+([\d,.]+)/', $line, $totalMatches)) {
                if (!empty($currentItem)) {
                    $currentItem['descripcion'] = implode(' ', array_filter($descriptionBuffer));
                    if (!isset($currentItem['cantidad'])) $currentItem['cantidad'] = 1;
                    if (!isset($currentItem['precio_unitario'])) $currentItem['precio_unitario'] = 0;
                    if (!isset($currentItem['importe'])) $currentItem['importe'] = $currentItem['precio_unitario'] * $currentItem['cantidad'];
                    $items[] = $currentItem;
                    debugLog("Fin de sección de ítems por Total, ítem guardado: " . print_r($currentItem, true));
                }
                $inItemsSection = false;
                $currentItem = [];
                $descriptionBuffer = [];
                debugLog("Fin de sección de ítems detectado por Total");
                continue;
            } elseif (stripos($line, 'Requested') !== false || preg_match('/^\d{2}\/[a-zA-Z]+\/\d{4}$/', $line)) {
                // Ignorar líneas de fechas y "Requested"
                debugLog("Ignorando línea de fecha o Requested: $line");
                continue;
            } elseif (stripos($line, 'Promised') !== false || stripos($line, 'Line Total') !== false) {
                // Evitar que "Promised" o "Line Total" se acumulen en la descripción
                debugLog("Ignorando línea de Promised o Line Total para descripción: $line");
                continue;
            } elseif (stripos($line, 'Orden de compra') !== false || stripos($line, 'Purchase Order') !== false || stripos($line, 'Proprietary and Confidential') !== false) {
                // Excluir líneas que indican el inicio de una nueva sección o un nuevo documento
                if ($inItemsSection) {
                    if (!empty($currentItem)) {
                        $currentItem['descripcion'] = implode(' ', array_filter($descriptionBuffer));
                        if (!isset($currentItem['cantidad'])) $currentItem['cantidad'] = 1;
                        if (!isset($currentItem['precio_unitario'])) $currentItem['precio_unitario'] = 0;
                        if (!isset($currentItem['importe'])) $currentItem['importe'] = $currentItem['precio_unitario'] * $currentItem['cantidad'];
                        $items[] = $currentItem;
                        debugLog("Fin de sección de ítems por nueva orden, ítem guardado: " . print_r($currentItem, true));
                    }
                    $inItemsSection = false;
                    $currentItem = [];
                    $descriptionBuffer = [];
                    debugLog("Fin de sección de ítems detectado por nueva orden o sección");
                }
                continue;
            } else {
                if (!empty($line)) {
                    $descriptionBuffer[] = $line;
                    debugLog("Descripción adicional acumulada: " . end($descriptionBuffer));
                }
            }
        }
    }

    if (!empty($currentItem)) {
        $currentItem['descripcion'] = implode(' ', array_filter($descriptionBuffer));
        if (!isset($currentItem['cantidad'])) $currentItem['cantidad'] = 1;
        if (!isset($currentItem['precio_unitario'])) $currentItem['precio_unitario'] = 0;
        if (!isset($currentItem['importe'])) $currentItem['importe'] = $currentItem['precio_unitario'] * $currentItem['cantidad'];
        $items[] = $currentItem;
        debugLog("Último ítem guardado: " . print_r($currentItem, true));
    }

    debugLog("Ítems extraídos (final): " . print_r($items, true));
    return $items;
}

function processOCBatch($directory) {
    $files = glob($directory . '/*.pdf');
    foreach ($files as $file) {
        try {
            processOC($file);
            // Mover el archivo procesado a una carpeta de respaldo
            $backupDir = $directory . '/processed/';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            rename($file, $backupDir . basename($file));
            debugLog("Archivo movido a processed: $file");
        } catch (Exception $e) {
            debugLog("Error al procesar $file: " . $e->getMessage());
            // Mover a una carpeta de errores
            $errorDir = $directory . '/errors/';
            if (!is_dir($errorDir)) {
                mkdir($errorDir, 0777, true);
            }
            rename($file, $errorDir . basename($file));
            debugLog("Archivo movido a errors: $file");
        }
    }
}

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    processOCBatch(__DIR__ . '/uploads_oc/');
}
?>