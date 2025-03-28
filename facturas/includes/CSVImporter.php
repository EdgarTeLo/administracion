<?php
// CSVImporter.php - Versión 1.0.2
class CSVImporter {
    private $conn;
    private $isDebug;
    private $batchSize;
    private $delimiter;

    public function __construct($conn, $isDebug = false, $batchSize = 500, $delimiter = ',') {
        $this->conn = $conn;
        $this->isDebug = $isDebug || (defined('DEBUG') && DEBUG);
        $this->batchSize = $batchSize;
        $this->delimiter = $delimiter;
    }

    public function processCSV($file, $delimiter, &$totalRows, &$validRowsCount) {
        if ($this->isDebug) {
            debugLog("Iniciando procesamiento de CSV: $file, Delimitador: $delimiter");
        }

        if (!file_exists($file) || !is_readable($file)) {
            throw new Exception("El archivo CSV no existe o no es legible: $file");
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            throw new Exception("No se pudo abrir el archivo CSV: $file");
        }

        $validator = new CSVValidator($this->conn, $this->isDebug);
        $processor = new CSVProcessor($this->conn, $this->isDebug, $this->batchSize);

        $headers = fgetcsv($handle, 0, $delimiter);
        if ($headers === false) {
            fclose($handle);
            throw new Exception("No se pudieron leer los encabezados del CSV: $file");
        }

        if ($this->isDebug) {
            debugLog("Encabezados del CSV: " . implode(', ', $headers));
        }

        $headerMap = [];
        $expectedHeadersMap = [
            'fecha' => ['fecha', '﻿fecha'],
            'fact' => ['fact'],
            'folio_fiscal' => ['folio fiscal'],
            'cotizacion' => ['cotización o presupuesto', 'cotizacion o presupuesto', 'COTIZACIÓN O PRESUPUESTO', 'cotización', 'cotizacion'],
            'orden_compra' => ['orden de compra', 'ORDEN DE COMPRA'],
            'descripcion' => ['descripcion', 'descripción', 'DESCRIPCION'],
            'ubicacion' => ['obra', 'OBRA'],
            'cliente' => ['razon social', 'razón social', 'RAZON SOCIAL'],
            'contacto' => ['supervisor', 'SUPERVISOR'],
            'subtotal' => ['subtotal', 'SUBTOTAL'],
            'iva' => ['iva', 'IVA'],
            'total' => ['total', 'TOTAL'],
            'fecha_pago' => ['fecha de pago', 'FECHA DE PAGO'],
            'numero_pago' => ['no.de complemento que le corresponde', 'numero_pago', 'No.de complemento que le corresponde'],
            'fecha_cancelacion' => ['fecha de cancelacion', 'fecha cancelación', 'FECHA DE CANCELACION'],
            'interes_nafin' => ['interes nafin', 'interés nafin', 'INTERES NAFIN'],
            'importe_pagado' => ['pago recibido', 'PAGO RECIBIDO'],
            'observaciones' => ['observacion', 'observación', 'OBSERVACION'],
            'recibo' => ['receip', 'RECEIP'],
            'facel' => ['facel', 'FACEL'],
            'fecha_facel' => ['fecha facel', 'FECHA FACEL']
        ];

        foreach ($headers as $index => $header) {
            $headerClean = trim($header);
            $headerLower = strtolower($headerClean);
            foreach ($expectedHeadersMap as $field => $aliases) {
                foreach ($aliases as $alias) {
                    if ($headerClean === $alias || $headerLower === strtolower($alias)) {
                        $headerMap[$field] = $index;
                        break 2;
                    }
                }
            }
        }

        if ($this->isDebug) {
            debugLog("Mapeo de encabezados: " . print_r($headerMap, true));
        }

        if (!isset($headerMap['fact'])) {
            fclose($handle);
            throw new Exception("No se encontró el campo obligatorio 'fact' en los encabezados.");
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['headerMap'] = $headerMap;
        $_SESSION['validRows'] = [];

        $totalRows = 0;
        $validRowsCount = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $totalRows++;
            $rowNumber++;

            try {
                $isValid = $validator->isValidRow($row, $headerMap);
                if ($isValid) {
                    $_SESSION['validRows'][] = $row;
                    $validRowsCount++;
                    if ($this->isDebug) {
                        debugLog("Fila $rowNumber válida: " . implode(',', $row));
                    }
                } else {
                    if ($this->isDebug) {
                        debugLog("Fila $rowNumber inválida: " . implode(',', $row));
                    }
                }
            } catch (Exception $e) {
                if ($this->isDebug) {
                    debugLog("Error en fila $rowNumber: " . $e->getMessage());
                }
            }
        }

        fclose($handle);

        if ($this->isDebug) {
            debugLog("Procesamiento inicial completado. Total filas: $totalRows, Válidas: $validRowsCount");
        }

        return $processor->processValidRows($file, $_SESSION['validRows'], $totalRows, $validRowsCount, false);
    }
}