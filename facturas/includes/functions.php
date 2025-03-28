<?php
// includes/functions.php - Versión 1.0.6
$fileVersion = '1.0.6'; // Incrementado de 1.0.5 para corregir el error de bind_param

require_once __DIR__ . '/../dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.0.0');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/debug.php';

function processCFDIXML($xmlPath) {
    $conn = getDBConnection();
    
    if ($conn === false) {
        error_log("Error: No se pudo establecer la conexión a la base de datos.");
        if (DEBUG) {
            debugLog("Error: No se pudo establecer la conexión a la base de datos. " . mysqli_connect_error());
        }
        return false;
    }

    // Load XML
    $xml = simplexml_load_file($xmlPath);
    if ($xml === false) {
        error_log("Error: No se pudo cargar el archivo XML en $xmlPath");
        if (DEBUG) {
            debugLog("Error: No se pudo cargar el archivo XML en $xmlPath");
        }
        $conn->close();
        return false;
    }

    if (DEBUG) {
        debugLog("Procesando archivo XML: " . $xmlPath);
    }

    // Register namespaces
    $ns = $xml->getNamespaces(true);
    $xml->registerXPathNamespace('cfdi', $ns['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4');
    $xml->registerXPathNamespace('tfd', $ns['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital');
    $xml->registerXPathNamespace('pago20', $ns['pago20'] ?? 'http://www.sat.gob.mx/Pagos20');

    // Identificar el tipo de comprobante
    $tipoComprobante = (string)($xml['TipoDeComprobante'] ?? '');
    if (DEBUG) {
        debugLog("Tipo de Comprobante detectado: $tipoComprobante");
    }

    if ($tipoComprobante === 'P') {
        // Procesar como Comprobante de Pago (PP)
        return processPaymentXML($xml, $conn, $xmlPath);
    } else {
        // Procesar como Factura (FF) - lógica existente
        return processInvoiceXML($xml, $conn, $xmlPath);
    }
}

function processInvoiceXML($xml, $conn, $xmlPath) {
    // Extract UUID
    $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
    $uuidNodes = $xml->xpath('//tfd:TimbreFiscalDigital/@UUID');
    if (empty($uuidNodes)) {
        error_log("Error: No se encontró el UUID en el XML");
        if (DEBUG) {
            debugLog("Error: No se encontró el UUID en el XML para $xmlPath");
        }
        $conn->close();
        return false;
    }
    $uuid = (string)$uuidNodes[0];

    // Check if UUID already exists in facturas
    $stmt_check = $conn->prepare("SELECT id FROM facturas WHERE folio_fiscal = ?");
    if (!$stmt_check) {
        error_log("Error preparando consulta de duplicado: " . $conn->error);
        if (DEBUG) {
            debugLog("Error preparando consulta de duplicado: " . $conn->error);
        }
        $conn->close();
        return false;
    }
    $stmt_check->bind_param("s", $uuid);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        if (DEBUG) {
            debugLog("El UUID $uuid ya está registrado (duplicado) en $xmlPath");
        }
        return "duplicate";
    }
    $stmt_check->close();

    $folio = (string)($xml['Folio'] ?? '');
    $issue_date = (string)($xml['Fecha'] ?? '');
    $subtotal = (float)($xml['SubTotal'] ?? 0.0);
    $total = (float)($xml['Total'] ?? 0.0);
    $payment_method = (string)($xml['MetodoPago'] ?? '');
    $payment_form = (string)($xml['FormaPago'] ?? '');

    if (DEBUG) {
        debugLog("Datos extraídos del XML FF: Folio=$folio, Fecha=$issue_date, Subtotal=$subtotal, Total=$total, MetodoPago=$payment_method, FormaPago=$payment_form");
    }

    // Extract Emisor using XPath
    $emisorNodes = $xml->xpath('//cfdi:Emisor');
    $rfc_emisor = '';
    $cliente = '';
    if (!empty($emisorNodes)) {
        $emisor = $emisorNodes[0];
        $rfc_emisor = (string)($emisor['Rfc'] ?? '');
        $cliente = (string)($emisor['Nombre'] ?? '');
        if (empty($rfc_emisor) || empty($cliente)) {
            $attributes = $emisor->attributes();
            $rfc_emisor = (string)($attributes['Rfc'] ?? '');
            $cliente = (string)($attributes['Nombre'] ?? '');
        }
        if (empty($cliente)) {
            $cliente = 'Desconocido'; // Valor por defecto para evitar NULL
        }
        if (DEBUG) {
            debugLog("Emisor extraído: RFC=$rfc_emisor, Nombre=$cliente");
        }
        if (empty($rfc_emisor)) {
            error_log("Nodo Emisor incompleto en $xmlPath");
            if (DEBUG) {
                debugLog("Nodo Emisor incompleto en $xmlPath: " . htmlspecialchars($emisor->asXML()));
            }
        }
    }

    // Extract Receptor using XPath
    $receptorNodes = $xml->xpath('//cfdi:Receptor');
    $rfc_receptor = '';
    if (!empty($receptorNodes)) {
        $receptor = $receptorNodes[0];
        $rfc_receptor = (string)($receptor['Rfc'] ?? '');
        $contacto = NULL; // Mantener como NULL para evitar violación de fk_supervisor
        if (empty($rfc_receptor)) {
            $attributes = $receptor->attributes();
            $rfc_receptor = (string)($attributes['Rfc'] ?? '');
        }
        if (DEBUG) {
            debugLog("Receptor extraído: RFC=$rfc_receptor");
        }
        if (empty($rfc_receptor)) {
            error_log("Nodo Receptor incompleto en $xmlPath");
            if (DEBUG) {
                debugLog("Nodo Receptor incompleto en $xmlPath: " . htmlspecialchars($receptor->asXML()));
            }
        }
    }

    // Extract IVA using XPath
    $ivaNodes = $xml->xpath('//cfdi:Impuestos/@TotalImpuestosTrasladados');
    $iva = !empty($ivaNodes) ? (float)$ivaNodes[0] : 0.0;

    // Normalizar fechas
    $fecha = DateTime::createFromFormat('Y-m-d\TH:i:s', $issue_date);
    if ($fecha === false) {
        error_log("Error: Formato de fecha inválido en $xmlPath: $issue_date");
        if (DEBUG) {
            debugLog("Error: Formato de fecha inválido en $xmlPath: $issue_date");
        }
        $conn->close();
        return false;
    }
    $fecha_str = $fecha->format('Y-m-d');

    // Variables para bind_param
    $fact = $folio;
    $cotizacion = null;
    $orden_compra = null;
    $descripcion = null;
    $ubicacion = null;
    $fecha_pago = null;
    $numero_pago = null;
    $uuid_pago = null;
    $uuid_cancelacion = null;
    $rfc_cancelacion = null;
    $fecha_cancelacion = null;
    $estado = 'activa'; // Parámetro explícito para bind_param
    $interes_nafin = '0.00';
    $observaciones = null;
    $recibo = null;
    $facel = null;
    $fecha_facel = null;
    $importe_pagado = '0.00';

    // Insert factura
    $stmt = $conn->prepare("
        INSERT INTO facturas (fecha, fact, folio_fiscal, cotizacion, orden_compra, descripcion, ubicacion, cliente, contacto, subtotal, iva, total, fecha_pago, numero_pago, uuid_pago, rfc_emisor, rfc_receptor, importe_pagado, uuid_cancelacion, rfc_cancelacion, fecha_cancelacion, estado, interes_nafin, observaciones, recibo, facel, fecha_facel) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        error_log("Error preparando inserción de factura: " . $conn->error);
        if (DEBUG) {
            debugLog("Error preparando inserción de factura: " . $conn->error);
        }
        $conn->close();
        return false;
    }
    // Corrección: Ajustar la cadena de tipos para que coincida con los 27 parámetros
    // sssssssss (9 strings: fecha, fact, folio_fiscal, cotizacion, orden_compra, descripcion, ubicacion, cliente, contacto)
    // dddd (4 doubles: subtotal, iva, total, importe_pagado)
    // ssssssss (8 strings: fecha_pago, numero_pago, uuid_pago, rfc_emisor, rfc_receptor, uuid_cancelacion, rfc_cancelacion, fecha_cancelacion)
    // s (1 string: estado)
    // d (1 double: interes_nafin)
    // ssss (4 strings: observaciones, recibo, facel, fecha_facel)
    $stmt->bind_param("sssssssssddddsssssssssdssss", $fecha_str, $fact, $uuid, $cotizacion, $orden_compra, $descripcion, $ubicacion, $cliente, $contacto, $subtotal, $iva, $total, $importe_pagado, $fecha_pago, $numero_pago, $uuid_pago, $rfc_emisor, $rfc_receptor, $uuid_cancelacion, $rfc_cancelacion, $fecha_cancelacion, $estado, $interes_nafin, $observaciones, $recibo, $facel, $fecha_facel);
    if (!$stmt->execute()) {
        error_log("Error ejecutando inserción de factura: " . $stmt->error);
        if (DEBUG) {
            debugLog("Error ejecutando inserción de factura: " . $stmt->error);
        }
        $stmt->close();
        $conn->close();
        return false;
    }
    $factura_id = $conn->insert_id;

    // Insert concepts and extract OC, quotation, and description
    $stmt_items = $conn->prepare("
        INSERT INTO items_factura (factura_id, clave_prod_serv, numero_identificacion, cantidad, clave_unidad, descripcion_unidad, descripcion, precio_unitario, importe, importe_iva) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt_items) {
        error_log("Error preparando inserción de ítems: " . $conn->error);
        if (DEBUG) {
            debugLog("Error preparando inserción de ítems: " . $conn->error);
        }
        $stmt->close();
        $conn->close();
        return false;
    }

    $orden_compra = null;
    $cotizacion = null;
    $descripcion_global = null;
    $conceptos = $xml->xpath('//cfdi:Concepto');

    foreach ($conceptos as $concepto) {
        $clave_prod_serv = (string)($concepto['ClaveProdServ'] ?? '');
        $numero_identificacion = (string)($concepto['NoIdentificacion'] ?? '');
        $cantidad = (float)($concepto['Cantidad'] ?? 0.0);
        $clave_unidad = (string)($concepto['ClaveUnidad'] ?? '');
        $descripcion_unidad = (string)($concepto['Unidad'] ?? '');
        $desc = (string)($concepto['Descripcion'] ?? '');
        if (empty($desc)) {
            $descNodes = $concepto->xpath('@Descripcion');
            $desc = !empty($descNodes) ? (string)$descNodes[0] : '';
        }
        if (empty($desc)) {
            $attributes = $concepto->attributes();
            $desc = (string)($attributes['Descripcion'] ?? '');
        }

        $precio_unitario = (float)($concepto['ValorUnitario'] ?? 0.0);
        $importe = (float)($concepto['Importe'] ?? 0.0);

        $impuestos = $concepto->children('cfdi', true)->Impuestos;
        $importe_iva = 0.0;
        if ($impuestos) {
            $traslados = $impuestos->children('cfdi', true)->Traslados;
            if ($traslados) {
                $traslado = $traslados->children('cfdi', true)->Traslado;
                $importe_iva = (float)($traslado['Importe'] ?? 0.0);
            }
        }

        // Store first description and extract quotation/purchase order
        if ($descripcion_global === null) {
            $descripcion_global = $desc;
            preg_match('/COT[:\-]?\s*([A-Za-z0-9\-]+)/i', $desc, $quot_matches);
            if (!empty($quot_matches[1])) {
                $cotizacion = $quot_matches[1];
            }
            preg_match('/OC[:\.]?\s*(\d{11})/i', $desc, $matches);
            if (!empty($matches[1])) {
                $orden_compra = $matches[1];
            }
        }

        if (empty($desc)) {
            error_log("No se pudo extraer descripción en factura $folio, mostrando nodo completo");
            if (DEBUG) {
                debugLog("No se pudo extraer descripción en factura $folio: " . htmlspecialchars($concepto->asXML()));
            }
        } else {
            if (DEBUG) {
                debugLog("Procesando descripción en factura $folio: '$desc'");
            }
        }

        $stmt_items->bind_param("issdsssddd", $factura_id, $clave_prod_serv, $numero_identificacion, $cantidad, $clave_unidad, $descripcion_unidad, $desc, $precio_unitario, $importe, $importe_iva);
        if (!$stmt_items->execute()) {
            error_log("Error ejecutando inserción de ítems: " . $stmt_items->error);
            if (DEBUG) {
                debugLog("Error ejecutando inserción de ítems: " . $stmt_items->error);
            }
            $stmt_items->close();
            $stmt->close();
            $conn->close();
            return false;
        }
    }

    // Actualizar facturas con datos adicionales extraídos
    $stmt_update = $conn->prepare("UPDATE facturas SET cotizacion = ?, orden_compra = ?, descripcion = ? WHERE id = ?");
    if (!$stmt_update) {
        error_log("Error preparando actualización de factura: " . $conn->error);
        if (DEBUG) {
            debugLog("Error preparando actualización de factura: " . $conn->error);
        }
        $stmt_items->close();
        $stmt->close();
        $conn->close();
        return false;
    }
    $stmt_update->bind_param("sssi", $cotizacion, $orden_compra, $descripcion_global, $factura_id);
    if (!$stmt_update->execute()) {
        error_log("Error ejecutando actualización de factura: " . $stmt_update->error);
        if (DEBUG) {
            debugLog("Error ejecutando actualización de factura: " . $stmt_update->error);
        }
        $stmt_update->close();
        $stmt_items->close();
        $stmt->close();
        $conn->close();
        return false;
    }
    $stmt_update->close();

    if (DEBUG) {
        debugLog("Factura FF insertada con éxito. ID: $factura_id, Fact: $fact, Folio Fiscal: $uuid, Cotización: " . ($cotizacion ?? 'NULL') . ", Orden de Compra: " . ($orden_compra ?? 'NULL') . ", Descripción: " . ($descripcion_global ?? 'NULL') . " en $xmlPath");
    }

    $stmt_items->close();
    $stmt->close();
    $conn->close();

    return $factura_id;
}

function processPaymentXML($xml, $conn, $xmlPath) {
    // Extract UUID of the PP
    $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
    $uuidNodes = $xml->xpath('//tfd:TimbreFiscalDigital/@UUID');
    if (empty($uuidNodes)) {
        error_log("Error: No se encontró el UUID en el XML PP");
        if (DEBUG) {
            debugLog("Error: No se encontró el UUID en el XML PP para $xmlPath");
        }
        $conn->close();
        return false;
    }
    $uuidPP = (string)$uuidNodes[0];

    // Extraer los pagos
    $xml->registerXPathNamespace('pago20', 'http://www.sat.gob.mx/Pagos20');
    $pagos = $xml->xpath('//pago20:Pago');
    if (empty($pagos)) {
        error_log("Error: No se encontraron pagos en el XML PP");
        if (DEBUG) {
            debugLog("Error: No se encontraron pagos en el XML PP para $xmlPath");
        }
        $conn->close();
        return false;
    }

    $updatedCount = 0;
    foreach ($pagos as $pago) {
        $fechaPago = (string)($pago['FechaPago'] ?? '');
        $monto = (float)($pago['Monto'] ?? 0.0);
        $formaPago = (string)($pago['FormaDePagoP'] ?? '');
        $docs = $pago->xpath('pago20:DoctoRelacionado');

        foreach ($docs as $doc) {
            $uuidFactura = (string)($doc['IdDocumento'] ?? '');
            $folioFactura = (string)($doc['Folio'] ?? '');
            $impPagado = (float)($doc['ImpPagado'] ?? 0.0);
            $impSaldoInsoluto = (float)($doc['ImpSaldoInsoluto'] ?? 0.0);
            $numParcialidad = (int)($doc['NumParcialidad'] ?? 1);

            if (empty($uuidFactura)) {
                error_log("Error: No se encontró IdDocumento en un DoctoRelacionado del XML PP");
                if (DEBUG) {
                    debugLog("Error: No se encontró IdDocumento en un DoctoRelacionado del XML PP para $xmlPath");
                }
                continue;
            }

            // Normalizar fecha de pago
            $fechaPagoFormatted = null;
            if (!empty($fechaPago)) {
                $fecha = DateTime::createFromFormat('Y-m-d\TH:i:s', $fechaPago);
                if ($fecha !== false) {
                    $fechaPagoFormatted = $fecha->format('Y-m-d');
                } else {
                    error_log("Error: Formato de FechaPago inválido en XML PP: $fechaPago");
                    if (DEBUG) {
                        debugLog("Error: Formato de FechaPago inválido en XML PP: $fechaPago para $xmlPath");
                    }
                }
            }

            // Determinar estado
            $estado = $impSaldoInsoluto == 0.0 ? 'pagada' : 'activa';

            // Actualizar la factura FF correspondiente
            $stmt = $conn->prepare("UPDATE facturas SET fecha_pago = ?, numero_pago = ?, uuid_pago = ?, importe_pagado = importe_pagado + ?, estado = ? WHERE folio_fiscal = ?");
            if (!$stmt) {
                error_log("Error preparando actualización de factura FF: " . $conn->error);
                if (DEBUG) {
                    debugLog("Error preparando actualización de factura FF: " . $conn->error);
                }
                continue;
            }

            $stmt->bind_param("ssssds", $fechaPagoFormatted, $numParcialidad, $uuidPP, $impPagado, $estado, $uuidFactura);
            if (!$stmt->execute()) {
                error_log("Error ejecutando actualización de factura FF: " . $stmt->error);
                if (DEBUG) {
                    debugLog("Error ejecutando actualización de factura FF: " . $stmt->error);
                }
            } else {
                $updatedCount++;
                if (DEBUG) {
                    debugLog("Factura FF actualizada con datos de PP: FolioFiscal=$uuidFactura, FechaPago=$fechaPagoFormatted, ImportePagado=$impPagado, Estado=$estado, UUIDPago=$uuidPP");
                }
            }
            $stmt->close();
        }
    }

    if (DEBUG) {
        debugLog("Procesamiento de XML PP completado: $updatedCount facturas actualizadas en $xmlPath");
    }

    $conn->close();
    return $updatedCount > 0;
}

function getInvoiceList($limit = 20, $offset = 0, $sort = 'fact', $order = 'DESC', $search = '') {
    $conn = getDBConnection();
    if ($conn === false) {
        error_log("Error: No se pudo establecer la conexión a la base de datos.");
        if (DEBUG) {
            debugLog("Error: No se pudo establecer la conexión a la base de datos. " . mysqli_connect_error());
        }
        return [];
    }

    // Validar el campo de ordenamiento para evitar inyección SQL
    $validColumns = ['fact', 'folio_fiscal', 'fecha', 'cliente', 'total', 'estado', 'orden_compra', 'cotizacion', 'descripcion', 'ubicacion', 'subtotal', 'iva', 'fecha_pago', 'numero_pago', 'fecha_cancelacion', 'importe_pagado'];
    $sort = in_array($sort, $validColumns) ? $sort : 'fact';

    // Validar la dirección del ordenamiento
    $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

    $query = "
        SELECT f.id, f.folio_fiscal as uuid, f.fact as folio, f.fecha as issue_date, f.cliente as receiver_name, 
               f.total, f.estado as status, f.subtotal, f.iva as vat, f.orden_compra as purchase_order, 
               f.cotizacion as quotation, f.descripcion, f.ubicacion as work_location, f.fecha_pago as payment_date, 
               f.numero_pago as complement_number, f.fecha_cancelacion as cancellation_date, f.importe_pagado as payment_received,
               f.observaciones as observations, f.recibo as receipt, f.facel as facel
        FROM facturas f
    ";

    $params = [];
    $types = '';

    if (!empty($search)) {
        $search = "%" . $conn->real_escape_string($search) . "%";
        $query .= " WHERE (f.fact LIKE ? OR f.folio_fiscal LIKE ? OR f.fecha LIKE ? OR f.cliente LIKE ? OR 
                       f.total LIKE ? OR f.subtotal LIKE ? OR f.iva LIKE ? OR f.orden_compra LIKE ? OR 
                       f.cotizacion LIKE ? OR f.descripcion LIKE ? OR f.ubicacion LIKE ? OR f.fecha_pago LIKE ? OR 
                       f.numero_pago LIKE ? OR f.fecha_cancelacion LIKE ? OR f.importe_pagado LIKE ? OR 
                       f.observaciones LIKE ? OR f.recibo LIKE ? OR f.facel LIKE ?)";
        for ($i = 0; $i < 18; $i++) { // 18 campos en el WHERE
            $params[] = $search;
            $types .= 's';
        }
    }

    // Ordenar numéricamente si es 'fact'
    if ($sort === 'fact') {
        $query .= " ORDER BY CAST(f.fact AS UNSIGNED) $order";
    } else {
        $query .= " ORDER BY f.$sort $order";
    }

    $query .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Error preparando consulta de lista de facturas: " . $conn->error);
        if (DEBUG) {
            debugLog("Error preparando consulta de lista de facturas: " . $conn->error);
        }
        $conn->close();
        return [];
    }

    if (!empty($params)) {
        $refParams = [];
        foreach ($params as $key => $value) {
            $refParams[$key] = &$params[$key];
        }
        $bindResult = call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $refParams));
        if (!$bindResult) {
            error_log("Error vinculando parámetros: " . $stmt->error);
            if (DEBUG) {
                debugLog("Error vinculando parámetros: " . $stmt->error);
            }
            $stmt->close();
            $conn->close();
            return [];
        }
    }

    if (DEBUG) {
        debugLog("Ejecutando consulta de facturas: $query con parámetros: " . (!empty($search) ? $search : "sin búsqueda") . ", limit=$limit, offset=$offset");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    if (DEBUG) {
        debugLog("Facturas obtenidas: " . print_r($invoices, true));
    }

    return $invoices;
}

function getTotalInvoices($search = '') {
    $conn = getDBConnection();
    if ($conn === false) {
        error_log("Error: No se pudo establecer la conexión a la base de datos.");
        if (DEBUG) {
            debugLog("Error: No se pudo establecer la conexión a la base de datos. " . mysqli_connect_error());
        }
        return 0;
    }

    $query = "SELECT COUNT(*) as total FROM facturas f";

    $params = [];
    $types = '';

    if (!empty($search)) {
        $search = "%" . $conn->real_escape_string($search) . "%";
        $query .= " WHERE (f.fact LIKE ? OR f.folio_fiscal LIKE ? OR f.fecha LIKE ? OR f.cliente LIKE ? OR 
                       f.total LIKE ? OR f.subtotal LIKE ? OR f.iva LIKE ? OR f.orden_compra LIKE ? OR 
                       f.cotizacion LIKE ? OR f.descripcion LIKE ? OR f.ubicacion LIKE ? OR f.fecha_pago LIKE ? OR 
                       f.numero_pago LIKE ? OR f.fecha_cancelacion LIKE ? OR f.importe_pagado LIKE ?)";
        for ($i = 0; $i < 15; $i++) { // 15 campos en el WHERE
            $params[] = $search;
            $types .= 's';
        }
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Error preparando consulta de total de facturas: " . $conn->error);
        if (DEBUG) {
            debugLog("Error preparando consulta de total de facturas: " . $conn->error);
        }
        $conn->close();
        return 0;
    }

    if (!empty($params)) {
        $refParams = [];
        foreach ($params as $key => $value) {
            $refParams[$key] = &$params[$key];
        }
        $bindResult = call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $refParams));
        if (!$bindResult) {
            error_log("Error vinculando parámetros: " . $stmt->error);
            if (DEBUG) {
                debugLog("Error vinculando parámetros: " . $stmt->error);
            }
            $stmt->close();
            $conn->close();
            return 0;
        }
    }

    if (DEBUG) {
        debugLog("Ejecutando consulta de total de facturas: $query con parámetros: " . (!empty($search) ? $search : "sin búsqueda"));
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $total = $result['total'] ?? 0;
    $stmt->close();
    $conn->close();

    if (DEBUG) {
        debugLog("Total de facturas: $total");
    }

    return $total;
}