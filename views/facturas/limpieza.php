<?php
// CSVProcessor.php - Versión 1.0.14
class CSVProcessor {
    private $conn;
    private $isDebug;
    private $batchSize;
    private $headerMap;

    public function __construct($conn, $isDebug = false, $batchSize = 100, $headerMap = []) {
        $this->conn = $conn;
        $this->isDebug = $isDebug || (defined('DEBUG') && DEBUG);
        $this->batchSize = $batchSize;
        $this->headerMap = $headerMap;

        if ($this->conn->connect_error) {
            throw new Exception("Error de conexión a la base de datos: " . $this->conn->connect_error);
        }
    }

    public function cleanCurrency($value) {
        if (empty($value)) return '0.00';
        $value = str_replace(['$', ',', ' '], '', $value);
        return number_format(floatval($value), 2, '.', '');
    }

    public function truncateText($text, $maxLength = 1000) {
        return substr($text, 0, $maxLength);
    }

    public function normalizeDate($dateStr, &$dbData, $field) {
        global $globalRowIndex, $errors;

        if (empty($dateStr)) return null;

        // Detectar texto de cancelación
        $cancelationKeywords = ['en proceso de cancelacion', 'cancelada', 'cancleada', 'complemento cancelado', 'factura cancelada'];
        if (in_array(strtolower($dateStr), array_map('strtolower', $cancelationKeywords))) {
            $dbData['estado'] = 'cancelada';
            return null;
        }

        // Eliminar comillas y otros caracteres no deseados
        $dateStr = preg_replace("/[']/", '', $dateStr);

        // Manejar casos como "28/1/2/0205" (interpretar como "28/1/2025")
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateStr, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[4];
            $dateStr = "$day/$month/$year";
            if ($this->isDebug) {
                debugLog("Fila $globalRowIndex - Fecha $field corregida de " . $matches[0] . " a $dateStr");
            }
        }

        // Corregir años de 3 dígitos (e.g., 205 -> 2025)
        $dateStr = preg_replace('/(\d{1,2})\/(\d{1,2})\/(\d{3})$/', '$1/$2/20$3', $dateStr);
        // Corregir formatos como 15/102024
        $dateStr = preg_replace('/(\d{1,2})\/(\d{1,2})(\d{4})$/', '$1/$2/$3', $dateStr);

        $formats = ['d/m/Y', 'Y-m-d', 'd/m/y'];
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateStr);
            if ($date !== false) {
                if ($format === 'd/m/y') {
                    $year = $date->format('y');
                    $fullYear = ($year >= 0 && $year <= 69) ? "20$year" : "19$year";
                    $date->setDate($fullYear, $date->format('m'), $date->format('d'));
                }
                return $date->format('Y-m-d');
            }
        }

        // Si no se puede parsear, usar 0000-00-00 y registrar error
        $errors[] = "Fila $globalRowIndex - Formato de $field inválido: " . $dateStr;
        return '0000-00-00';
    }

    public function normalizeString($str) {
        return trim($str);
    }

    public function processValidRows($file, $validRows, $totalRows, $validRowsCount, $storeInDB = false) {
        global $globalRowIndex, $errors;

        if ($this->isDebug) {
            debugLog("Procesando $validRowsCount filas válidas. Almacenar en DB: " . ($storeInDB ? 'Sí' : 'No'));
        }

        $errors = [];
        $insertedCount = 0;

        if ($storeInDB && $this->conn) {
            $chunks = array_chunk($validRows, $this->batchSize);
            foreach ($chunks as $chunkIndex => $chunk) {
                $chunkSize = count($chunk);
                if ($this->isDebug) {
                    debugLog("Procesando lote " . ($chunkIndex + 1) . " con $chunkSize filas.");
                }

                foreach ($chunk as $rowIndex => $row) {
                    $globalRowIndex = ($chunkIndex * $this->batchSize) + $rowIndex + 2;
                    try {
                        $mappedData = [];
                        foreach ($this->headerMap as $header => $index) {
                            $mappedData[$header] = isset($row[$index]) ? trim($row[$index]) : null;
                        }

                        $dbData = [
                            'fecha' => '1970-01-01',
                            'fact' => $mappedData['fact'] ?? '',
                            'folio_fiscal' => $mappedData['folio_fiscal'] ?? '',
                            'cotizacion' => $mappedData['cotizacion'] ?? '',
                            'orden_compra' => $mappedData['orden_compra'] ?? '',
                            'descripcion' => $this->truncateText($mappedData['descripcion'] ?? ''),
                            'ubicacion' => $mappedData['ubicacion'] ?? '',
                            'cliente' => !empty($mappedData['cliente']) ? $mappedData['cliente'] : 'Desconocido',
                            'contacto' => $mappedData['contacto'] ?? '',
                            'subtotal' => $this->cleanCurrency($mappedData['subtotal'] ?? '0'),
                            'iva' => $this->cleanCurrency($mappedData['iva'] ?? '0'),
                            'total' => $this->cleanCurrency($mappedData['total'] ?? '0'),
                            'fecha_pago' => null,
                            'numero_pago' => $mappedData['numero_pago'] ?? '',
                            'uuid_pago' => null,
                            'rfc_emisor' => 'XAXX010101000',
                            'rfc_receptor' => 'XAXX010101000',
                            'importe_pagado' => $this->cleanCurrency($mappedData['importe_pagado'] ?? '0'),
                            'uuid_cancelacion' => null,
                            'rfc_cancelacion' => null,
                            'fecha_cancelacion' => null,
                            'estado' => 'activa',
                            'interes_nafin' => $this->cleanCurrency($mappedData['interes_nafin'] ?? '0'),
                            'observaciones' => $mappedData['observaciones'] ?? '',
                            'recibo' => $mappedData['recibo'] ?? '',
                            'facel' => $mappedData['facel'] ?? '',
                            'fecha_facel' => null
                        ];

                        if ($this->isDebug) {
                            debugLog("Fila $globalRowIndex - Datos mapeados iniciales: " . print_r($dbData, true));
                        }

                        // Normalizar fechas
                        $dbData['fecha'] = $this->normalizeDate($mappedData['fecha'], $dbData, 'fecha') ?? $dbData['fecha'];
                        $dbData['fecha_pago'] = $this->normalizeDate($mappedData['fecha_pago'], $dbData, 'fecha_pago');
                        $dbData['fecha_cancelacion'] = $this->normalizeDate($mappedData['fecha_cancelacion'], $dbData, 'fecha_cancelacion');
                        $dbData['fecha_facel'] = $this->normalizeDate($mappedData['fecha_facel'], $dbData, 'fecha_facel');

                        // Determinar estado basado en folio_fiscal
                        $folioFiscalLower = strtolower($dbData['folio_fiscal'] ?? '');
                        if (in_array($folioFiscalLower, ['cancelada', 'cancleada', 'complemento cancelado', 'factura cancelada'])) {
                            $dbData['estado'] = 'cancelada';
                        }

                        if (empty($dbData['fact']) && empty($dbData['folio_fiscal'])) {
                            $errors[] = "Fila $globalRowIndex inválida: 'fact' y 'folio_fiscal' vacíos.";
                            continue;
                        }

                        $dbData['contacto'] = $this->normalizeString($dbData['contacto']);
                        $validatedContacto = $dbData['contacto'];
                        if (!empty($dbData['contacto'])) {
                            $stmt = $this->conn->prepare("SELECT nombre FROM supervisores WHERE UPPER(nombre) = UPPER(?)");
                            if ($stmt === false) {
                                $errors[] = "Fila $globalRowIndex - Error al preparar consulta de supervisor: " . $this->conn->error;
                                if ($this->isDebug) debugLog("Error preparando consulta de supervisor: " . $this->conn->error);
                                continue;
                            }
                            $stmt->bind_param("s", $dbData['contacto']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result === false) {
                                $errors[] = "Fila $globalRowIndex - Error al ejecutar consulta de supervisor: " . $stmt->error;
                                if ($this->isDebug) debugLog("Error ejecutando consulta de supervisor: " . $stmt->error);
                                $stmt->close();
                                continue;
                            }
                            if ($result->num_rows === 0) {
                                $errors[] = "Fila $globalRowIndex - Supervisor no válido: " . $dbData['contacto'];
                                if ($this->isDebug) debugLog("Fila $globalRowIndex - Supervisor no encontrado en supervisores: " . $dbData['contacto']);
                                $validatedContacto = null;
                                $dbData['contacto'] = null;
                            } else {
                                if ($this->isDebug) debugLog("Fila $globalRowIndex - Supervisor válido: " . $dbData['contacto']);
                            }
                            $stmt->close();
                        } else {
                            if ($this->isDebug) debugLog("Fila $globalRowIndex - Contacto vacío, seteando a NULL");
                            $validatedContacto = null;
                            $dbData['contacto'] = null;
                        }

                        if (!empty($dbData['ubicacion'])) {
                            $stmt = $this->conn->prepare("SELECT nombre FROM ubicaciones WHERE nombre = ?");
                            if ($stmt === false) {
                                $errors[] = "Fila $globalRowIndex - Error al preparar consulta de ubicación: " . $this->conn->error;
                                if ($this->isDebug) debugLog("Error preparando consulta de ubicación: " . $this->conn->error);
                                continue;
                            }
                            $stmt->bind_param("s", $dbData['ubicacion']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows === 0) {
                                $errors[] = "Fila $globalRowIndex - Ubicación no válida: " . $dbData['ubicacion'];
                                $dbData['ubicacion'] = null;
                            }
                            $stmt->close();
                        }

                        if ($this->isDebug) {
                            debugLog("Fila $globalRowIndex - Datos finales antes de SQL: " . print_r($dbData, true));
                        }

                        $sql = "INSERT INTO facturas (fecha, fact, folio_fiscal, cotizacion, orden_compra, descripcion, ubicacion, cliente, contacto, subtotal, iva, total, fecha_pago, numero_pago, uuid_pago, rfc_emisor, rfc_receptor, importe_pagado, uuid_cancelacion, rfc_cancelacion, fecha_cancelacion, estado, interes_nafin, observaciones, recibo, facel, fecha_facel) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE 
                                    contacto = VALUES(contacto), 
                                    ubicacion = IF(VALUES(ubicacion) IS NOT NULL AND VALUES(ubicacion) != '', VALUES(ubicacion), ubicacion),
                                    estado = VALUES(estado),
                                    cotizacion = IF(VALUES(cotizacion) IS NOT NULL AND VALUES(cotizacion) != '', VALUES(cotizacion), cotizacion),
                                    orden_compra = IF(VALUES(orden_compra) IS NOT NULL AND VALUES(orden_compra) != '', VALUES(orden_compra), orden_compra),
                                    fecha = VALUES(fecha),
                                    folio_fiscal = VALUES(folio_fiscal),
                                    descripcion = VALUES(descripcion),
                                    cliente = VALUES(cliente),
                                    subtotal = VALUES(subtotal),
                                    iva = VALUES(iva),
                                    total = VALUES(total),
                                    fecha_pago = VALUES(fecha_pago),
                                    numero_pago = VALUES(numero_pago),
                                    importe_pagado = VALUES(importe_pagado),
                                    fecha_cancelacion = VALUES(fecha_cancelacion),
                                    interes_nafin = VALUES(interes_nafin),
                                    observaciones = VALUES(observaciones),
                                    recibo = VALUES(recibo),
                                    facel = VALUES(facel),
                                    fecha_facel = VALUES(fecha_facel)";

                        $stmt = $this->conn->prepare($sql);
                        if ($stmt === false) {
                            $errors[] = "Fila $globalRowIndex - Error al preparar SQL: " . $this->conn->error;
                            if ($this->isDebug) debugLog("Error preparando SQL: " . $this->conn->error);
                            continue;
                        }

                        $stmt->bind_param(
                            "ssssssssssssssssssssssssssss",
                            $dbData['fecha'],
                            $dbData['fact'],
                            $dbData['folio_fiscal'],
                            $dbData['cotizacion'],
                            $dbData['orden_compra'],
                            $dbData['descripcion'],
                            $dbData['ubicacion'],
                            $dbData['cliente'],
                            $validatedContacto,
                            $dbData['subtotal'],
                            $dbData['iva'],
                            $dbData['total'],
                            $dbData['fecha_pago'],
                            $dbData['numero_pago'],
                            $dbData['uuid_pago'],
                            $dbData['rfc_emisor'],
                            $dbData['rfc_receptor'],
                            $dbData['importe_pagado'],
                            $dbData['uuid_cancelacion'],
                            $dbData['rfc_cancelacion'],
                            $dbData['fecha_cancelacion'],
                            $dbData['estado'],
                            $dbData['interes_nafin'],
                            $dbData['observaciones'],
                            $dbData['recibo'],
                            $dbData['facel'],
                            $dbData['fecha_facel'],
                            $validatedContacto
                        );

                        if (!$stmt->execute()) {
                            $errors[] = "Fila $globalRowIndex - Error al ejecutar SQL: " . $stmt->error;
                            if ($this->isDebug) debugLog("Error ejecutando SQL: " . $stmt->error);
                        } else {
                            $insertedCount++;
                            if ($this->isDebug) {
                                debugLog("Fila $globalRowIndex: Factura {$dbData['fact']} procesada. Contacto: {$dbData['contacto']}, Ubicación: {$dbData['ubicacion']}, Estado: {$dbData['estado']}");
                            }
                        }
                        $stmt->close();
                    } catch (Exception $e) {
                        $errors[] = "Fila $globalRowIndex - Error: " . $e->getMessage();
                        if ($this->isDebug) debugLog("Error procesando fila $globalRowIndex: " . $e->getMessage());
                    } catch (Error $e) {
                        $errors[] = "Fila $globalRowIndex - Error fatal: " . $e->getMessage();
                        if ($this->isDebug) debugLog("Error fatal procesando fila $globalRowIndex: " . $e->getMessage());
                    }
                }
            }

            $result = [
                'success' => empty($errors),
                'message' => empty($errors) ? "Importación completada. Filas procesadas: $insertedCount de $validRowsCount." : "Errores durante la importación: " . implode("; ", $errors),
                'totalRows' => $totalRows,
                'validRowsCount' => $validRowsCount,
                'hasErrors' => !empty($errors),
                'errors' => $errors
            ];
            if ($this->isDebug) debugLog("Resultado final: " . print_r($result, true));
            return $result;
        }

        return [
            'success' => true,
            'message' => "Validación completada. Filas válidas: $validRowsCount de $totalRows.",
            'totalRows' => $totalRows,
            'validRowsCount' => $validRowsCount,
            'hasErrors' => false,
            'errors' => []
        ];
    }
}