<?php
namespace App\Models;

use App\Config\Database;
use League\Csv\Reader;
use SimpleXMLElement;

class Factura {
    private $db;

    public function __construct() {
        $database = Database::getInstance('facturas');
        $this->db = $database->getConnection();
    }

    public function getAll() {
        try {
            $query = "
                SELECT 
                    id, 
                    fecha, 
                    fact AS numero_factura, 
                    folio_fiscal, 
                    cliente, 
                    subtotal, 
                    iva, 
                    total, 
                    fecha_pago, 
                    estado
                FROM 
                    facturas
                WHERE 
                    estado = 'activa'
                ORDER BY 
                    fecha DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error al obtener facturas: " . $e->getMessage());
            return [];
        }
    }

    public function processXml($filePath) {
        try {
            $xml = simplexml_load_file($filePath);
            if ($xml === false) {
                throw new \Exception("No se pudo leer el archivo XML.");
            }

            $namespaces = $xml->getNamespaces(true);
            error_log("Namespaces disponibles: " . print_r($namespaces, true));

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
                error_log("Nodo TimbreFiscalDigital no encontrado en el XML.");
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
                'estado' => 'activa'
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

            // Guardar la factura y verificar el resultado
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
            error_log("Error al procesar XML: " . $e->getMessage());
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
                            $this->saveItemOrdenCompra($itemData);
                        }
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log("Error al procesar CSV: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    private function saveFactura($factura) {
        try {
            $query = "
                INSERT INTO facturas (
                    fecha, fact, folio_fiscal, cliente, subtotal, iva, total, 
                    rfc_emisor, rfc_receptor, estado
                ) VALUES (
                    :fecha, :fact, :folio_fiscal, :cliente, :subtotal, :iva, :total, 
                    :rfc_emisor, :rfc_receptor, :estado
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
                ':estado' => $factura['estado']
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de inserción de factura.");
            }
        } catch (\PDOException $e) {
            error_log("Error al guardar factura: " . $e->getMessage());
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
            error_log("Error al guardar ítem de factura: " . $e->getMessage());
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
            error_log("Error al guardar orden de compra: " . $e->getMessage());
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
            error_log("Error al guardar ítem de orden de compra: " . $e->getMessage());
            throw new \Exception("Error al guardar el ítem de orden de compra: " . $e->getMessage());
        }
    }

    private function getLastInsertId() {
        return $this->db->lastInsertId();
    }
}