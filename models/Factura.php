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
            $cfdi = $xml->children($namespaces['cfdi']);
            $tfd = $xml->children($namespaces['tfd'])->TimbreFiscalDigital ?? null;

            $factura = [
                'fecha' => (string)($cfdi->attributes()['Fecha'] ?? '0000-01-01'),
                'fact' => (string)($cfdi->attributes()['Folio'] ?? ''),
                'folio_fiscal' => (string)($tfd->attributes()['UUID'] ?? ''),
                'cliente' => (string)($cfdi->Receptor->attributes()['Nombre'] ?? ''),
                'subtotal' => (float)($cfdi->attributes()['SubTotal'] ?? 0.00),
                'iva' => 0.00, // Calcular IVA desde los impuestos
                'total' => (float)($cfdi->attributes()['Total'] ?? 0.00),
                'rfc_emisor' => (string)($cfdi->Emisor->attributes()['Rfc'] ?? ''),
                'rfc_receptor' => (string)($cfdi->Receptor->attributes()['Rfc'] ?? ''),
                'estado' => 'activa'
            ];

            // Calcular IVA desde los impuestos
            if (isset($cfdi->Impuestos->Traslados->Traslado)) {
                foreach ($cfdi->Impuestos->Traslados->Traslado as $traslado) {
                    if ((string)$traslado->attributes()['Impuesto'] === '002') { // IVA
                        $factura['iva'] += (float)$traslado->attributes()['Importe'];
                    }
                }
            }

            // Guardar la factura
            $this->saveFactura($factura);

            // Procesar ítems de la factura
            if (isset($cfdi->Conceptos->Concepto)) {
                foreach ($cfdi->Conceptos->Concepto as $concepto) {
                    $item = [
                        'factura_id' => $this->getLastInsertId(),
                        'clave_prod_serv' => (string)($concepto->attributes()['ClaveProdServ'] ?? ''),
                        'numero_identificacion' => (string)($concepto->attributes()['NoIdentificacion'] ?? ''),
                        'cantidad' => (float)($concepto->attributes()['Cantidad'] ?? 0.00),
                        'clave_unidad' => (string)($concepto->attributes()['ClaveUnidad'] ?? ''),
                        'descripcion_unidad' => (string)($concepto->attributes()['Unidad'] ?? ''),
                        'descripcion' => (string)($concepto->attributes()['Descripcion'] ?? ''),
                        'precio_unitario' => (float)($concepto->attributes()['ValorUnitario'] ?? 0.00),
                        'importe' => (float)($concepto->attributes()['Importe'] ?? 0.00),
                        'importe_iva' => 0.00 // Calcular IVA por ítem si aplica
                    ];

                    // Calcular IVA por ítem (si aplica)
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
            return false;
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

                // Procesar ítems de la orden de compra (si el CSV los incluye)
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
            return false;
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
            $stmt->execute([
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
        } catch (\PDOException $e) {
            error_log("Error al guardar factura: " . $e->getMessage());
            throw $e;
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
            $stmt->execute([
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
        } catch (\PDOException $e) {
            error_log("Error al guardar ítem de factura: " . $e->getMessage());
            throw $e;
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
            $stmt->execute([
                ':numero_oc' => $ordenCompra['numero_oc'],
                ':total' => $ordenCompra['total'],
                ':fecha_emision' => $ordenCompra['fecha_emision'],
                ':proveedor' => $ordenCompra['proveedor']
            ]);
        } catch (\PDOException $e) {
            error_log("Error al guardar orden de compra: " . $e->getMessage());
            throw $e;
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
            $stmt->execute([
                ':orden_compra_id' => $item['orden_compra_id'],
                ':descripcion' => $item['descripcion'],
                ':cantidad' => $item['cantidad'],
                ':precio_unitario' => $item['precio_unitario'],
                ':importe' => $item['importe']
            ]);
        } catch (\PDOException $e) {
            error_log("Error al guardar ítem de orden de compra: " . $e->getMessage());
            throw $e;
        }
    }

    private function getLastInsertId() {
        return $this->db->lastInsertId();
    }
}