<?php
// CSVValidator.php - Versión 1.0.1
class CSVValidator {
    private $conn;
    private $isDebug;

    public function __construct($conn, $isDebug = false) {
        $this->conn = $conn;
        $this->isDebug = $isDebug || (defined('DEBUG') && DEBUG);
    }

    public function validateCSV($file, $delimiter, &$totalRows, &$validRowsCount) {
        if (!file_exists($file) || !is_readable($file)) {
            throw new Exception("El archivo CSV '$file' no existe o no es legible.");
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            throw new Exception("No se pudo abrir el archivo CSV: $file");
        }

        $header = fgetcsv($handle, 0, $delimiter);
        if ($header === false) {
            fclose($handle);
            throw new Exception("El archivo CSV está vacío o no tiene encabezados.");
        }

        if ($this->isDebug) {
            debugLog("Encabezados del CSV crudos: " . print_r($header, true));
        }

        $headerMap = $this->mapHeaders($header);
        if (!$this->hasRequiredFields($headerMap)) {
            fclose($handle);
            throw new Exception("El archivo CSV no contiene los campos requeridos: 'FACT'. 'FOLIO FISCAL' es opcional pero recomendado.");
        }

        $errors = [];
        $validRows = [];
        $rowNum = 1;
        $totalRows = 0;
        $validRowsCount = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $totalRows++;
            $rowNum++;

            if ($this->isDebug) {
                debugLog("Procesando fila $rowNum (cruda): " . print_r($row, true));
            }

            if (empty($row)) {
                $errors[] = "Fila $rowNum: La fila está vacía.";
                continue;
            }

            $normalizedRow = $this->normalizeRow($row, $headerMap);
            if ($this->isValidRow($normalizedRow, $headerMap)) {
                $validRows[] = $normalizedRow;
                $validRowsCount++;
            } else {
                $errors[] = "Fila $rowNum: Falta 'FACT' para identificar la factura.";
            }
        }

        fclose($handle);

        if ($this->isDebug) {
            debugLog("Validación completada - Total filas: $totalRows, Filas válidas: $validRowsCount, Errores: " . count($errors));
        }

        if (!empty($errors)) {
            $message = "Errores detectados en el CSV:\n" . implode("\n", $errors) . "\nHay $validRowsCount filas válidas para procesar.";
            return ['success' => false, 'message' => $message, 'hasErrors' => true, 'totalRows' => $totalRows, 'validRowsCount' => $validRowsCount, 'errors' => $errors, 'validRows' => $validRows];
        }

        $message = "CSV válido, todas las filas son válidas.";
        return ['success' => true, 'message' => $message, 'hasErrors' => false, 'totalRows' => $totalRows, 'validRowsCount' => $validRowsCount, 'validRows' => $validRows];
    }

    private function mapHeaders($header) {
        $headerMap = [];
        foreach ($header as $index => $field) {
            $normalizedField = strtolower(trim($field));
            $headerMap[$normalizedField] = $index;
        }
        if ($this->isDebug) {
            debugLog("Encabezados mapeados: " . print_r($headerMap, true));
        }
        return $headerMap;
    }

    private function hasRequiredFields($headerMap) {
        return isset($headerMap['fact']);
    }

    private function normalizeRow($row, $headerMap) {
        $normalized = [];
        foreach ($row as $index => $value) {
            $normalized[$index] = trim($value);
        }
        if ($this->isDebug) {
            debugLog("Fila normalizada - Datos crudos: " . print_r($row, true));
            debugLog("Fila normalizada - Datos normalizados: " . print_r($normalized, true));
        }
        return $normalized;
    }

    public function isValidRow($row, $headerMap) {
        $factIndex = $headerMap['fact'];
        $folioFiscalIndex = isset($headerMap['folio_fiscal']) ? $headerMap['folio_fiscal'] : null;
        $fact = $row[$factIndex] ?? '';
        $folioFiscal = $folioFiscalIndex !== null ? ($row[$folioFiscalIndex] ?? '') : '';

        $isValid = !empty($fact) || (!empty($folioFiscal) && $folioFiscalIndex !== null);

        if (!$isValid && ($folioFiscal === 'complemento' || $folioFiscal === 'complemento cancelado' || $folioFiscal === 'factura cancelada' || $fact === 'cancelada' || $fact === 'complemento')) {
            $isValid = true;
        }

        if ($this->isDebug) {
            debugLog("Validando fila - FACT: '$fact', FOLIO FISCAL: '$folioFiscal', Es válida: " . ($isValid ? 'Sí' : 'No'));
        }
        return $isValid;
    }
}