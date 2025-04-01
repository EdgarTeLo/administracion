<?php
namespace App\Models;

use App\Config\Database;
use League\Csv\Reader;
use SimpleXMLElement;
use Smalot\PdfParser\Parser;

class Factura {
    private $db;

    public function __construct() {
        $database = Database::getInstance('facturas');
        $this->db = $database->getConnection();
    }

    public function getAll($cliente = '', $estado = 'activa') {
        try {
            $query = "
                SELECT 
                    f.id, 
                    f.fecha, 
                    f.fact AS numero_factura, 
                    f.folio_fiscal, 
                    f.cliente, 
                    f.subtotal, 
                    f.iva, 
                    f.total, 
                    f.fecha_pago, 
                    f.estado,
                    f.orden_compra,
                    oc.numero_oc
                FROM 
                    facturas f
                LEFT JOIN 
                    ordenes_compra oc ON f.orden_compra = oc.numero_oc
                WHERE 
                    1=1
            ";
            $params = [];

            if ($cliente) {
                $query .= " AND f.cliente LIKE :cliente";
                $params[':cliente'] = "%$cliente%";
            }

            if ($estado) {
                $query .= " AND f.estado = :estado";
                $params[':estado'] = $estado;
            }

            $query .= " ORDER BY f.fecha DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => "Error al obtener facturas: " . $e->getMessage()];
        }
    }

    public function getById($id) {
        try {
            $query = "
                SELECT 
                    f.id, 
                    f.fecha, 
                    f.fact AS numero_factura, 
                    f.folio_fiscal, 
                    f.cliente, 
                    f.subtotal, 
                    f.iva, 
                    f.total, 
                    f.fecha_pago, 
                    f.estado,
                    f.orden_compra,
                    oc.numero_oc
                FROM 
                    facturas f
                LEFT JOIN 
                    ordenes_compra oc ON f.orden_compra = oc.numero_oc
                WHERE 
                    f.id = :id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            return ['error' => "Error al obtener factura: " . $e->getMessage()];
        }
    }

    public function getItemsByFacturaId($facturaId) {
        try {
            $query = "
                SELECT 
                    id, 
                    factura_id, 
                    clave_prod_serv, 
                    numero_identificacion, 
                    cantidad, 
                    clave_unidad, 
                    descripcion_unidad, 
                    descripcion, 
                    precio_unitario, 
                    importe, 
                    importe_iva
                FROM 
                    items_factura
                WHERE 
                    factura_id = :factura_id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':factura_id' => $facturaId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => "Error al obtener ítems de factura: " . $e->getMessage()];
        }
    }

    public function getAllOrdenesCompra() {
        try {
            $query = "
                SELECT 
                    id, 
                    numero_oc, 
                    total, 
                    fecha_emision, 
                    proveedor
                FROM 
                    ordenes_compra
                ORDER BY 
                    fecha_emision DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => "Error al obtener órdenes de compra: " . $e->getMessage()];
        }
    }

    public function getOrdenById($id) {
        try {
            $query = "
                SELECT 
                    id, 
                    numero_oc, 
                    total, 
                    fecha_emision, 
                    proveedor
                FROM 
                    ordenes_compra
                WHERE 
                    id = :id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            return ['error' => "Error al obtener orden de compra: " . $e->getMessage()];
        }
    }

    public function getItemsByOrdenId($ordenId) {
        try {
            $query = "
                SELECT 
                    id, 
                    orden_compra_id, 
                    descripcion, 
                    cantidad, 
                    precio_unitario, 
                    importe
                FROM 
                    items_ordenes_compra
                WHERE 
                    orden_compra_id = :orden_compra_id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':orden_compra_id' => $ordenId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => "Error al obtener ítems de orden de compra: " . $e->getMessage()];
        }
    }

    public function processXml($filePath) {
        try {
            $xml = simplexml_load_file($filePath);
            if ($xml === false) {
                throw new \Exception("No se pudo leer el archivo XML.");
            }

            $namespaces = $xml->getNamespaces(true);
            if (!isset($namespaces['cfdi'])) {
                throw new \Exception("El namespace 'cfdi' no está definido en el XML.");
            }

            $cfdi = $xml->children($namespaces['cfdi']);
            $tfdNamespace = $namespaces['tfd'] ?? null;
            $tfd = null;

            if ($tfdNamespace && isset($cfdi->Complemento)) {
                $complemento = $cfdi->Complemento;
                $tfd = $complemento->children($tfdNamespace)->TimbreFiscalDigital ?? null;
            }

            if ($tfd === null) {
                throw new \Exception("El XML no contiene un nodo TimbreFiscalDigital válido. Asegúrate de que sea un CFDI válido.");
            }

            $folioFiscal = (string)($tfd->attributes()['UUID'] ?? '');
            if (empty($folioFiscal)) {
                throw new \Exception("El UUID del TimbreFiscalDigital no está definido.");
            }

            $factura = [
                'fecha' => (string)($cfdi->attributes()['Fecha'] ?? '0000-01-01'),
                'fact' => (string)($cfdi->attributes()['Folio'] ?? $folioFiscal),
                'folio_fiscal' => $folioFiscal,
                'cliente' => (string)($cfdi->Receptor->attributes()['Nombre'] ?? ''),
                'subtotal' => (float)($cfdi->attributes()['SubTotal'] ?? 0.00),
                'iva' => 0.00,
                'total' => (float)($cfdi->attributes()['Total'] ?? 0.00),
                'rfc_emisor' => (string)($cfdi->Emisor->attributes()['Rfc'] ?? ''),
                'rfc_receptor' => (string)($cfdi->Receptor->attributes()['Rfc'] ?? ''),
                'estado' => 'activa',
                'orden_compra' => null
            ];

            if (empty($factura['fact'])) {
                $factura['fact'] = $folioFiscal;
            }

            $query = "SELECT COUNT(*) FROM facturas WHERE fact = :fact";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':fact' => $factura['fact']]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception("Ya existe una factura con el número {$factura['fact']}.");
            }

            if (isset($cfdi->Impuestos->Traslados->Traslado)) {
                foreach ($cfdi->Impuestos->Traslados->Traslado as $traslado) {
                    if ((string)$traslado->attributes()['Impuesto'] === '002') {
                        $factura['iva'] += (float)$traslado->attributes()['Importe'];
                    }
                }
            }

            $this->saveFactura($factura);
            $lastInsertId = $this->getLastInsertId();
            if (!$lastInsertId) {
                throw new \Exception("No se pudo guardar la factura en la base de datos.");
            }

            if (isset($cfdi->Conceptos->Concepto)) {
                foreach ($cfdi->Conceptos->Concepto as $concepto) {
                    $item = [
                        'factura_id' => $lastInsertId,
                        'clave_prod_serv' => (string)($concepto->attributes()['ClaveProdServ'] ?? ''),
                        'numero_identificacion' => (string)($concepto->attributes()['NoIdentificacion'] ?? ''),
                        'cantidad' => (float)($concepto->attributes()['Cantidad'] ?? 0.00),
                        'clave_unidad' => (string)($concepto->attributes()['ClaveUnidad'] ?? ''),
                        'descripcion_unidad' => (string)($concepto->attributes()['Unidad'] ?? ''),
                        'descripcion' => (string)($concepto->attributes()['Descripcion'] ?? ''),
                        'precio_unitario' => (float)($concepto->attributes()['ValorUnitario'] ?? 0.00),
                        'importe' => (float)($concepto->attributes()['Importe'] ?? 0.00),
                        'importe_iva' => 0.00
                    ];

                    if (isset($concepto->Impuestos->Traslados->Traslado)) {
                        foreach ($concepto->Impuestos->Traslados->Traslado as $traslado) {
                            if ((string)$traslado->attributes()['Impuesto'] === '002') {
                                $item['importe_iva'] += (float)$traslado->attributes()['Importe'];
                            }
                        }
                    }

                    $this->saveItemFactura($item);
                }
            }

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function processCsv($filePath) {
        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader();
            $requiredHeaders = ['numero_oc', 'total', 'fecha_emision', 'proveedor'];
    
            // Validar que las columnas requeridas estén presentes
            foreach ($requiredHeaders as $header) {
                if (!in_array($header, $headers)) {
                    throw new \Exception("El archivo CSV debe contener la columna '$header'.");
                }
            }
    
            $records = $csv->getRecords();
            $rowNumber = 1; // Contador de filas (excluyendo el encabezado)
    
            foreach ($records as $record) {
                $rowNumber++;
                $ordenCompra = [
                    'numero_oc' => $record['numero_oc'] ?? '',
                    'total' => (float)($record['total'] ?? 0.00),
                    'fecha_emision' => $record['fecha_emision'] ?? '0000-01-01',
                    'proveedor' => $record['proveedor'] ?? ''
                ];
    
                // Validaciones
                if (empty($ordenCompra['numero_oc'])) {
                    throw new \Exception("El número de OC es obligatorio en la fila $rowNumber.");
                }
                if (!preg_match('/^[A-Za-z0-9-]+$/', $ordenCompra['numero_oc'])) {
                    throw new \Exception("El número de OC solo puede contener letras, números y guiones en la fila $rowNumber.");
                }
                if ($ordenCompra['total'] <= 0) {
                    throw new \Exception("El total debe ser mayor a 0 en la fila $rowNumber.");
                }
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ordenCompra['fecha_emision'])) {
                    throw new \Exception("La fecha de emisión debe tener el formato YYYY-MM-DD en la fila $rowNumber.");
                }
                $fechaEmision = \DateTime::createFromFormat('Y-m-d', $ordenCompra['fecha_emision']);
                if (!$fechaEmision || $fechaEmision->format('Y-m-d') !== $ordenCompra['fecha_emision']) {
                    throw new \Exception("La fecha de emisión no es válida en la fila $rowNumber.");
                }
                $currentDate = new \DateTime();
                if ($fechaEmision > $currentDate) {
                    throw new \Exception("La fecha de emisión no puede ser una fecha futura en la fila $rowNumber.");
                }
                if (empty($ordenCompra['proveedor'])) {
                    throw new \Exception("El proveedor es obligatorio en la fila $rowNumber.");
                }
                if (strlen($ordenCompra['proveedor']) > 255) {
                    throw new \Exception("El proveedor no puede exceder los 255 caracteres en la fila $rowNumber.");
                }
                if (!preg_match('/^[A-Za-z\s]+$/', $ordenCompra['proveedor'])) {
                    throw new \Exception("El proveedor solo puede contener letras y espacios en la fila $rowNumber.");
                }
    
                // Verificar duplicados
                $query = "SELECT COUNT(*) FROM ordenes_compra WHERE numero_oc = :numero_oc";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':numero_oc' => $ordenCompra['numero_oc']]);
                if ($stmt->fetchColumn() > 0) {
                    throw new \Exception("Ya existe una orden de compra con el número {$ordenCompra['numero_oc']} en la fila $rowNumber.");
                }
    
                $this->saveOrdenCompra($ordenCompra);
    
                if (isset($record['items']) && !empty($record['items'])) {
                    $items = json_decode($record['items'], true);
                    if (is_array($items)) {
                        $itemsTotal = 0.00;
                        foreach ($items as $item) {
                            $itemData = [
                                'orden_compra_id' => $this->getLastInsertId(),
                                'descripcion' => $item['descripcion'] ?? '',
                                'cantidad' => (float)($item['cantidad'] ?? 0.00),
                                'precio_unitario' => (float)($item['precio_unitario'] ?? 0.00),
                                'importe' => (float)($item['importe'] ?? 0.00)
                            ];
    
                            // Validaciones para ítems
                            if (empty($itemData['descripcion'])) {
                                throw new \Exception("La descripción del ítem es obligatoria en la fila $rowNumber.");
                            }
                            if ($itemData['cantidad'] <= 0) {
                                throw new \Exception("La cantidad del ítem debe ser mayor a 0 en la fila $rowNumber.");
                            }
                            if ($itemData['precio_unitario'] <= 0) {
                                throw new \Exception("El precio unitario del ítem debe ser mayor a 0 en la fila $rowNumber.");
                            }
                            if ($itemData['importe'] <= 0) {
                                throw new \Exception("El importe del ítem debe ser mayor a 0 en la fila $rowNumber.");
                            }
                            $calculatedImporte = $itemData['cantidad'] * $itemData['precio_unitario'];
                            if (abs($calculatedImporte - $itemData['importe']) > 0.01) {
                                throw new \Exception("El importe del ítem ({$itemData['importe']}) no coincide con cantidad * precio unitario ({$calculatedImporte}) en la fila $rowNumber.");
                            }
    
                            $itemsTotal += $itemData['importe'];
                            $this->saveItemOrdenCompra($itemData);
                        }
    
                        // Validar que el total coincida con la suma de los ítems
                        if (abs($ordenCompra['total'] - $itemsTotal) > 0.01) {
                            throw new \Exception("El total de la orden de compra ({$ordenCompra['total']}) no coincide con la suma de los ítems ({$itemsTotal}) en la fila $rowNumber.");
                        }
                    }
                }
            }
    
            return true;
        } catch (\Exception $e) {
            // Registrar el error en el log
            $this->logError('processCsv', $e->getMessage());
            return $e->getMessage();
        }
    }

    public function processPdf($filePath) {
        try {
            // Validar que el archivo sea un PDF
            $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->file($filePath);
            if ($mimeType !== 'application/pdf') {
                throw new \Exception("El archivo debe ser un PDF válido.");
            }
    
            // Validar el tamaño del archivo (máximo 10 MB)
            $maxFileSize = 10 * 1024 * 1024; // 10 MB en bytes
            if (filesize($filePath) > $maxFileSize) {
                throw new \Exception("El archivo excede el tamaño máximo permitido de 10 MB.");
            }
    
            // Usar Smalot\PdfParser para extraer texto del PDF
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
    
            // Validar que el PDF no esté vacío
            if (empty(trim($text))) {
                throw new \Exception("El PDF está vacío o no contiene texto legible.");
            }
    
            // Extraer datos del PDF (hacer la extracción más flexible)
            $numeroOc = '';
            $total = 0.00;
            $fechaEmision = '0000-01-01';
            $proveedor = '';
            $items = [];
    
            // Intentar diferentes formatos para el número de OC
            if (preg_match('/(?:Número de OC|OC|Orden de Compra)[:\s]*([A-Za-z0-9-]+)/i', $text, $match)) {
                $numeroOc = $match[1];
            } elseif (preg_match('/^([A-Za-z0-9-]+)/', trim($text), $match)) {
                // Si no se encuentra un prefijo, asumir que el primer valor es el número de OC
                $numeroOc = $match[1];
            }
            if (preg_match('/Total[:\s]*(\d+\.\d+)/i', $text, $match)) {
                $total = (float)$match[1];
            }
            if (preg_match('/(?:Fecha de Emisión|Fecha)[:\s]*(\d{4}-\d{2}-\d{2})/i', $text, $match)) {
                $fechaEmision = $match[1];
            }
            if (preg_match('/(?:Proveedor|Cliente)[:\s]*([^\n]+)/i', $text, $match)) {
                $proveedor = trim($match[1]);
            }
    
            // Extraer ítems (ejemplo simplificado, ajusta según el formato real)
            $lines = explode("\n", $text);
            $itemsSection = false;
            $itemsTotal = 0.00;
            foreach ($lines as $line) {
                if (strpos($line, 'Ítems:') !== false) {
                    $itemsSection = true;
                    continue;
                }
                if ($itemsSection && preg_match('/(.+?)\s+(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)/', $line, $match)) {
                    $item = [
                        'descripcion' => trim($match[1]),
                        'cantidad' => (float)$match[2],
                        'precio_unitario' => (float)$match[3],
                        'importe' => (float)$match[4]
                    ];
                    $items[] = $item;
                    $itemsTotal += $item['importe'];
                }
            }
    
            // Validaciones de datos extraídos
            if (empty($numeroOc)) {
                throw new \Exception("El número de OC es obligatorio y no se encontró en el PDF. Asegúrate de que el PDF contenga 'Número de OC:', 'OC:', o 'Orden de Compra:' seguido del número.");
            }
            if (!preg_match('/^[A-Za-z0-9-]+$/', $numeroOc)) {
                throw new \Exception("El número de OC solo puede contener letras, números y guiones.");
            }
            if ($total <= 0) {
                throw new \Exception("El total debe ser mayor a 0 y no se encontró un valor válido en el PDF.");
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaEmision)) {
                throw new \Exception("La fecha de emisión debe tener el formato YYYY-MM-DD y no se encontró un valor válido en el PDF.");
            }
            $fechaEmisionDate = \DateTime::createFromFormat('Y-m-d', $fechaEmision);
            if (!$fechaEmisionDate || $fechaEmisionDate->format('Y-m-d') !== $fechaEmision) {
                throw new \Exception("La fecha de emisión no es válida.");
            }
            $currentDate = new \DateTime();
            if ($fechaEmisionDate > $currentDate) {
                throw new \Exception("La fecha de emisión no puede ser una fecha futura.");
            }
            if (empty($proveedor)) {
                throw new \Exception("El proveedor es obligatorio y no se encontró en el PDF.");
            }
            if (!preg_match('/^[A-Za-z\s]+$/', $proveedor)) {
                throw new \Exception("El proveedor solo puede contener letras y espacios.");
            }
    
            // Verificar duplicados
            $query = "SELECT COUNT(*) FROM ordenes_compra WHERE numero_oc = :numero_oc";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':numero_oc' => $numeroOc]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception("Ya existe una orden de compra con el número {$numeroOc}.");
            }
    
            // Validar ítems (si se extraen)
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (empty($item['descripcion'])) {
                        throw new \Exception("La descripción del ítem es obligatoria.");
                    }
                    if ($item['cantidad'] <= 0) {
                        throw new \Exception("La cantidad del ítem debe ser mayor a 0.");
                    }
                    if ($item['precio_unitario'] <= 0) {
                        throw new \Exception("El precio unitario del ítem debe ser mayor a 0.");
                    }
                    if ($item['importe'] <= 0) {
                        throw new \Exception("El importe del ítem debe ser mayor a 0.");
                    }
                    $calculatedImporte = $item['cantidad'] * $item['precio_unitario'];
                    if (abs($calculatedImporte - $item['importe']) > 0.01) {
                        throw new \Exception("El importe del ítem ({$item['importe']}) no coincide con cantidad * precio unitario ({$calculatedImporte}).");
                    }
                }
    
                // Validar que el total coincida con la suma de los ítems
                if (abs($total - $itemsTotal) > 0.01) {
                    throw new \Exception("El total de la orden de compra ({$total}) no coincide con la suma de los ítems ({$itemsTotal}).");
                }
            }
    
            // Guardar la orden de compra
            $ordenCompra = [
                'numero_oc' => $numeroOc,
                'total' => $total,
                'fecha_emision' => $fechaEmision,
                'proveedor' => $proveedor
            ];
            $this->saveOrdenCompra($ordenCompra);
    
            // Guardar ítems
            if (!empty($items)) {
                foreach ($items as $item) {
                    $itemData = [
                        'orden_compra_id' => $this->getLastInsertId(),
                        'descripcion' => $item['descripcion'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'importe' => $item['importe']
                    ];
                    $this->saveItemOrdenCompra($itemData);
                }
            }
    
            return true;
        } catch (\Exception $e) {
            // Registrar el error en el log
            $this->logError('processPdf', $e->getMessage());
            return $e->getMessage();
        }
    }

    public function processCsvFacturas($filePath) {
        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();
    
            foreach ($records as $record) {
                $factura = [
                    'fecha' => $record['fecha'] ?? '0000-01-01',
                    'fact' => $record['fact'] ?? '',
                    'folio_fiscal' => $record['folio_fiscal'] ?? '',
                    'cliente' => $record['cliente'] ?? '',
                    'subtotal' => (float)($record['subtotal'] ?? 0.00),
                    'iva' => (float)($record['iva'] ?? 0.00),
                    'total' => (float)($record['total'] ?? 0.00),
                    'fecha_pago' => $record['fecha_pago'] ?: null,
                    'estado' => $record['estado'] ?? 'activa',
                    'rfc_emisor' => $record['rfc_emisor'] ?? '',
                    'rfc_receptor' => $record['rfc_receptor'] ?? '',
                    'orden_compra' => $record['orden_compra'] ?: null
                ];
    
                // Validaciones
                if (empty($factura['fact'])) {
                    throw new \Exception("El número de factura es obligatorio.");
                }
                if (!preg_match('/^[A-Za-z0-9-]+$/', $factura['fact'])) {
                    throw new \Exception("El número de factura solo puede contener letras, números y guiones.");
                }
                if ($factura['subtotal'] < 0) {
                    throw new \Exception("El subtotal no puede ser negativo.");
                }
                if ($factura['iva'] < 0) {
                    throw new \Exception("El IVA no puede ser negativo.");
                }
                if ($factura['total'] <= 0) {
                    throw new \Exception("El total debe ser mayor a 0.");
                }
                $calculatedTotal = $factura['subtotal'] + $factura['iva'];
                if (abs($calculatedTotal - $factura['total']) > 0.01) {
                    throw new \Exception("El total ({$factura['total']}) no coincide con la suma de subtotal + IVA ({$calculatedTotal}).");
                }
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $factura['fecha'])) {
                    throw new \Exception("La fecha debe tener el formato YYYY-MM-DD.");
                }
                $fechaFactura = \DateTime::createFromFormat('Y-m-d', $factura['fecha']);
                if (!$fechaFactura || $fechaFactura->format('Y-m-d') !== $factura['fecha']) {
                    throw new \Exception("La fecha de la factura no es válida.");
                }
                if (!empty($factura['fecha_pago'])) {
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $factura['fecha_pago'])) {
                        throw new \Exception("La fecha de pago debe tener el formato YYYY-MM-DD.");
                    }
                    $fechaPago = \DateTime::createFromFormat('Y-m-d', $factura['fecha_pago']);
                    if (!$fechaPago || $fechaPago->format('Y-m-d') !== $factura['fecha_pago']) {
                        throw new \Exception("La fecha de pago no es válida.");
                    }
                    if ($fechaPago < $fechaFactura) {
                        throw new \Exception("La fecha de pago no puede ser anterior a la fecha de la factura.");
                    }
                }
                if (empty($factura['cliente'])) {
                    throw new \Exception("El cliente es obligatorio.");
                }
                if (strlen($factura['cliente']) > 255) {
                    throw new \Exception("El nombre del cliente no puede exceder los 255 caracteres.");
                }
                if (!in_array($factura['estado'], ['activa', 'cancelada'])) {
                    throw new \Exception("El estado debe ser 'activa' o 'cancelada'.");
                }
                if (!empty($factura['folio_fiscal']) && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $factura['folio_fiscal'])) {
                    throw new \Exception("El folio fiscal debe tener el formato de un UUID (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).");
                }
                if (!empty($factura['rfc_emisor']) && !preg_match('/^[A-Z&Ñ]{3,4}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{3}$/', $factura['rfc_emisor'])) {
                    throw new \Exception("El RFC del emisor no tiene un formato válido.");
                }
                if (!empty($factura['rfc_receptor']) && !preg_match('/^[A-Z&Ñ]{3,4}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{3}$/', $factura['rfc_receptor'])) {
                    throw new \Exception("El RFC del receptor no tiene un formato válido.");
                }
    
                // Verificar duplicados
                $query = "SELECT COUNT(*) FROM facturas WHERE fact = :fact";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':fact' => $factura['fact']]);
                if ($stmt->fetchColumn() > 0) {
                    throw new \Exception("Ya existe una factura con el número {$factura['fact']}.");
                }
    
                // Si hay una orden de compra, verificar que exista
                if (!empty($factura['orden_compra'])) {
                    $query = "SELECT COUNT(*) FROM ordenes_compra WHERE numero_oc = :numero_oc";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([':numero_oc' => $factura['orden_compra']]);
                    if ($stmt->fetchColumn() == 0) {
                        throw new \Exception("La orden de compra {$factura['orden_compra']} no existe.");
                    }
                }
    
                $this->saveFactura($factura);
    
                if (isset($record['items'])) {
                    $items = json_decode($record['items'], true);
                    if (is_array($items)) {
                        $itemsTotal = 0.00;
                        foreach ($items as $item) {
                            $itemData = [
                                'factura_id' => $this->getLastInsertId(),
                                'clave_prod_serv' => $item['clave_prod_serv'] ?? '',
                                'numero_identificacion' => $item['numero_identificacion'] ?? '',
                                'cantidad' => (float)($item['cantidad'] ?? 0.00),
                                'clave_unidad' => $item['clave_unidad'] ?? '',
                                'descripcion_unidad' => $item['descripcion_unidad'] ?? '',
                                'descripcion' => $item['descripcion'] ?? '',
                                'precio_unitario' => (float)($item['precio_unitario'] ?? 0.00),
                                'importe' => (float)($item['importe'] ?? 0.00),
                                'importe_iva' => (float)($item['importe_iva'] ?? 0.00)
                            ];
    
                            // Validaciones para ítems
                            if (empty($itemData['descripcion'])) {
                                throw new \Exception("La descripción del ítem es obligatoria.");
                            }
                            if ($itemData['cantidad'] <= 0) {
                                throw new \Exception("La cantidad del ítem debe ser mayor a 0.");
                            }
                            if ($itemData['precio_unitario'] <= 0) {
                                throw new \Exception("El precio unitario del ítem debe ser mayor a 0.");
                            }
                            if ($itemData['importe'] <= 0) {
                                throw new \Exception("El importe del ítem debe ser mayor a 0.");
                            }
                            $calculatedImporte = $itemData['cantidad'] * $itemData['precio_unitario'];
                            if (abs($calculatedImporte - $itemData['importe']) > 0.01) {
                                throw new \Exception("El importe del ítem ({$itemData['importe']}) no coincide con cantidad * precio unitario ({$calculatedImporte}).");
                            }
                            if ($itemData['importe_iva'] < 0) {
                                throw new \Exception("El importe de IVA del ítem no puede ser negativo.");
                            }
    
                            $itemsTotal += $itemData['importe'] + $itemData['importe_iva'];
                            $this->saveItemFactura($itemData);
                        }
    
                        // Validar que el total de la factura coincida con la suma de los ítems
                        if (abs($factura['total'] - $itemsTotal) > 0.01) {
                            throw new \Exception("El total de la factura ({$factura['total']}) no coincide con la suma de los ítems ({$itemsTotal}).");
                        }
                    }
                }
    
                return true;
            }} catch (\Exception $e) {
                return $e->getMessage();
            
        }
    }
    public function crearFactura($factura) {
        try {
            $query = "
                INSERT INTO facturas (
                    fecha, fact, folio_fiscal, cliente, subtotal, iva, total, 
                    fecha_pago, estado, orden_compra
                ) VALUES (
                    :fecha, :fact, :folio_fiscal, :cliente, :subtotal, :iva, :total, 
                    :fecha_pago, :estado, :orden_compra
                )
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':fecha' => $factura['fecha'],
                ':fact' => $factura['fact'],
                ':folio_fiscal' => $factura['folio_fiscal'],
                ':cliente' => $factura['cliente'],
                ':subtotal' => $factura['subtotal'],
                ':iva' => $factura['iva'],
                ':total' => $factura['total'],
                ':fecha_pago' => $factura['fecha_pago'],
                ':estado' => $factura['estado'],
                ':orden_compra' => $factura['orden_compra']
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de inserción de factura.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al crear la factura: " . $e->getMessage();
        }
    }

    public function editarFactura($id, $factura) {
        try {
            $query = "
                UPDATE facturas 
                SET 
                    fecha = :fecha, 
                    fact = :fact, 
                    folio_fiscal = :folio_fiscal, 
                    cliente = :cliente, 
                    subtotal = :subtotal, 
                    iva = :iva, 
                    total = :total, 
                    fecha_pago = :fecha_pago, 
                    estado = :estado, 
                    orden_compra = :orden_compra
                WHERE 
                    id = :id
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':fecha' => $factura['fecha'],
                ':fact' => $factura['fact'],
                ':folio_fiscal' => $factura['folio_fiscal'],
                ':cliente' => $factura['cliente'],
                ':subtotal' => $factura['subtotal'],
                ':iva' => $factura['iva'],
                ':total' => $factura['total'],
                ':fecha_pago' => $factura['fecha_pago'],
                ':estado' => $factura['estado'],
                ':orden_compra' => $factura['orden_compra'],
                ':id' => $id
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de actualización de factura.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al actualizar la factura: " . $e->getMessage();
        }
    }

    public function eliminarFactura($id) {
        try {
            $query = "DELETE FROM items_factura WHERE factura_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);

            $query = "DELETE FROM facturas WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':id' => $id]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de eliminación de factura.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al eliminar la factura: " . $e->getMessage();
        }
    }

    public function crearOrdenCompra($ordenCompra) {
        try {
            $query = "
                INSERT INTO ordenes_compra (
                    numero_oc, total, fecha_emision, proveedor
                ) VALUES (
                    :numero_oc, :total, :fecha_emision, :proveedor
                )
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':numero_oc' => $ordenCompra['numero_oc'],
                ':total' => $ordenCompra['total'],
                ':fecha_emision' => $ordenCompra['fecha_emision'],
                ':proveedor' => $ordenCompra['proveedor']
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de inserción de orden de compra.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al crear la orden de compra: " . $e->getMessage();
        }
    }

    public function editarOrdenCompra($id, $ordenCompra) {
        try {
            $query = "
                UPDATE ordenes_compra 
                SET 
                    numero_oc = :numero_oc, 
                    total = :total, 
                    fecha_emision = :fecha_emision, 
                    proveedor = :proveedor
                WHERE 
                    id = :id
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':numero_oc' => $ordenCompra['numero_oc'],
                ':total' => $ordenCompra['total'],
                ':fecha_emision' => $ordenCompra['fecha_emision'],
                ':proveedor' => $ordenCompra['proveedor'],
                ':id' => $id
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de actualización de orden de compra.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al actualizar la orden de compra: " . $e->getMessage();
        }
    }

    public function eliminarOrdenCompra($id) {
        try {
            $query = "SELECT COUNT(*) FROM facturas WHERE orden_compra = (SELECT numero_oc FROM ordenes_compra WHERE id = :id)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception("No se puede eliminar la orden de compra porque está asociada a una o más facturas.");
            }

            $query = "DELETE FROM items_ordenes_compra WHERE orden_compra_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);

            $query = "DELETE FROM ordenes_compra WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':id' => $id]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de eliminación de orden de compra.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al eliminar la orden de compra: " . $e->getMessage();
        }
    }

    public function asociarOrdenCompra($facturaId, $ordenCompra) {
        try {
            $query = "
                UPDATE facturas 
                SET orden_compra = :orden_compra 
                WHERE id = :id
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':orden_compra' => $ordenCompra,
                ':id' => $facturaId
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de actualización.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al asociar la factura: " . $e->getMessage();
        }
    }

    private function saveFactura($factura) {
        try {
            $query = "
                INSERT INTO facturas (
                    fecha, fact, folio_fiscal, cliente, subtotal, iva, total, 
                    rfc_emisor, rfc_receptor, estado, orden_compra
                ) VALUES (
                    :fecha, :fact, :folio_fiscal, :cliente, :subtotal, :iva, :total, 
                    :rfc_emisor, :rfc_receptor, :estado, :orden_compra
                )
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':fecha' => $factura['fecha'],
                ':fact' => $factura['fact'],
                ':folio_fiscal' => $factura['folio_fiscal'],
                ':cliente' => $factura['cliente'],
                ':subtotal' => $factura['subtotal'],
                ':iva' => $factura['iva'],
                ':total' => $factura['total'],
                ':rfc_emisor' => $factura['rfc_emisor'],
                ':rfc_receptor' => $factura['rfc_receptor'],
                ':estado' => $factura['estado'],
                ':orden_compra' => $factura['orden_compra']
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de inserción de factura.");
            }
        } catch (\PDOException $e) {
            throw new \Exception("Error al guardar la factura en la base de datos: " . $e->getMessage());
        }
    }

    private function saveItemFactura($item) {
        try {
            $query = "
                INSERT INTO items_factura (
                    factura_id, clave_prod_serv, numero_identificacion, cantidad, 
                    clave_unidad, descripcion_unidad, descripcion, precio_unitario, 
                    importe, importe_iva
                ) VALUES (
                    :factura_id, :clave_prod_serv, :numero_identificacion, :cantidad, 
                    :clave_unidad, :descripcion_unidad, :descripcion, :precio_unitario, 
                    :importe, :importe_iva
                )
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':factura_id' => $item['factura_id'],
                ':clave_prod_serv' => $item['clave_prod_serv'],
                ':numero_identificacion' => $item['numero_identificacion'],
                ':cantidad' => $item['cantidad'],
                ':clave_unidad' => $item['clave_unidad'],
                ':descripcion_unidad' => $item['descripcion_unidad'],
                ':descripcion' => $item['descripcion'],
                ':precio_unitario' => $item['precio_unitario'],
                ':importe' => $item['importe'],
                ':importe_iva' => $item['importe_iva']
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de inserción de ítem de factura.");
            }
        } catch (\PDOException $e) {
            throw new \Exception("Error al guardar el ítem de factura: " . $e->getMessage());
        }
    }

    private function saveOrdenCompra($ordenCompra) {
        try {
            $query = "
                INSERT INTO ordenes_compra (
                    numero_oc, total, fecha_emision, proveedor
                ) VALUES (
                    :numero_oc, :total, :fecha_emision, :proveedor
                )
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':numero_oc' => $ordenCompra['numero_oc'],
                ':total' => $ordenCompra['total'],
                ':fecha_emision' => $ordenCompra['fecha_emision'],
                ':proveedor' => $ordenCompra['proveedor']
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de inserción de orden de compra.");
            }
        } catch (\PDOException $e) {
            throw new \Exception("Error al guardar la orden de compra: " . $e->getMessage());
        }
    }

    private function saveItemOrdenCompra($item) {
        try {
            $query = "
                INSERT INTO items_ordenes_compra (
                    orden_compra_id, descripcion, cantidad, precio_unitario, importe
                ) VALUES (
                    :orden_compra_id, :descripcion, :cantidad, :precio_unitario, :importe
                )
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':orden_compra_id' => $item['orden_compra_id'],
                ':descripcion' => $item['descripcion'],
                ':cantidad' => $item['cantidad'],
                ':precio_unitario' => $item['precio_unitario'],
                ':importe' => $item['importe']
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de inserción de ítem de orden de compra.");
            }
        } catch (\PDOException $e) {
            throw new \Exception("Error al guardar el ítem de orden de compra: " . $e->getMessage());
        }
    }

    private function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    private function logError($method, $message) {
        $logFile = __DIR__ . '/../logs/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] Error en $method: $message\n";
        
        // Asegurarse de que el directorio de logs exista
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        // Escribir el mensaje en el archivo de log
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}