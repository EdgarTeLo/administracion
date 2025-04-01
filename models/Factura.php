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
            $records = $csv->getRecords();

            foreach ($records as $record) {
                $ordenCompra = [
                    'numero_oc' => $record['numero_oc'] ?? '',
                    'total' => (float)($record['total'] ?? 0.00),
                    'fecha_emision' => $record['fecha_emision'] ?? '0000-01-01',
                    'proveedor' => $record['proveedor'] ?? ''
                ];

                // Validaciones
                if (empty($ordenCompra['numero_oc'])) {
                    throw new \Exception("El número de OC es obligatorio.");
                }
                if ($ordenCompra['total'] <= 0) {
                    throw new \Exception("El total debe ser mayor a 0.");
                }
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ordenCompra['fecha_emision'])) {
                    throw new \Exception("La fecha de emisión debe tener el formato YYYY-MM-DD.");
                }
                if (empty($ordenCompra['proveedor'])) {
                    throw new \Exception("El proveedor es obligatorio.");
                }

                // Verificar duplicados
                $query = "SELECT COUNT(*) FROM ordenes_compra WHERE numero_oc = :numero_oc";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':numero_oc' => $ordenCompra['numero_oc']]);
                if ($stmt->fetchColumn() > 0) {
                    throw new \Exception("Ya existe una orden de compra con el número {$ordenCompra['numero_oc']}.");
                }

                $this->saveOrdenCompra($ordenCompra);

                if (isset($record['items'])) {
                    $items = json_decode($record['items'], true);
                    if (is_array($items)) {
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

                            $this->saveItemOrdenCompra($itemData);
                        }
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
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

            // Usar Smalot\PdfParser para extraer texto del PDF
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            // Extraer datos del PDF (esto dependerá del formato del PDF)
            // Ejemplo: Buscar patrones específicos en el texto
            $numeroOc = '';
            $total = 0.00;
            $fechaEmision = '0000-01-01';
            $proveedor = '';
            $items = [];

            // Ejemplo de extracción (ajusta según el formato real de tus PDFs)
            if (preg_match('/Número de OC:\s*(\S+)/i', $text, $match)) {
                $numeroOc = $match[1];
            }
            if (preg_match('/Total:\s*(\d+\.\d+)/i', $text, $match)) {
                $total = (float)$match[1];
            }
            if (preg_match('/Fecha de Emisión:\s*(\d{4}-\d{2}-\d{2})/i', $text, $match)) {
                $fechaEmision = $match[1];
            }
            if (preg_match('/Proveedor:\s*([^\n]+)/i', $text, $match)) {
                $proveedor = trim($match[1]);
            }

            // Extraer ítems (ejemplo simplificado, ajusta según el formato real)
            $lines = explode("\n", $text);
            $itemsSection = false;
            foreach ($lines as $line) {
                if (strpos($line, 'Ítems:') !== false) {
                    $itemsSection = true;
                    continue;
                }
                if ($itemsSection && preg_match('/(.+?)\s+(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)/', $line, $match)) {
                    $items[] = [
                        'descripcion' => trim($match[1]),
                        'cantidad' => (float)$match[2],
                        'precio_unitario' => (float)$match[3],
                        'importe' => (float)$match[4]
                    ];
                }
            }

            // Validaciones
            if (empty($numeroOc)) {
                throw new \Exception("El número de OC es obligatorio y no se encontró en el PDF.");
            }
            if ($total <= 0) {
                throw new \Exception("El total debe ser mayor a 0 y no se encontró un valor válido en el PDF.");
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaEmision)) {
                throw new \Exception("La fecha de emisión debe tener el formato YYYY-MM-DD y no se encontró un valor válido en el PDF.");
            }
            if (empty($proveedor)) {
                throw new \Exception("El proveedor es obligatorio y no se encontró en el PDF.");
            }

            // Verificar duplicados
            $query = "SELECT COUNT(*) FROM ordenes_compra WHERE numero_oc = :numero_oc";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':numero_oc' => $numeroOc]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception("Ya existe una orden de compra con el número {$numeroOc}.");
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
                    // Validaciones para ítems
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
                if ($factura['subtotal'] < 0) {
                    throw new \Exception("El subtotal no puede ser negativo.");
                }
                if ($factura['iva'] < 0) {
                    throw new \Exception("El IVA no puede ser negativo.");
                }
                if ($factura['total'] <= 0) {
                    throw new \Exception("El total debe ser mayor a 0.");
                }
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $factura['fecha'])) {
                    throw new \Exception("La fecha debe tener el formato YYYY-MM-DD.");
                }
                if (empty($factura['cliente'])) {
                    throw new \Exception("El cliente es obligatorio.");
                }
                if (!in_array($factura['estado'], ['activa', 'cancelada'])) {
                    throw new \Exception("El estado debe ser 'activa' o 'cancelada'.");
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
    
                            $this->saveItemFactura($itemData);
                        }
                    }
                }
            }
    
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}