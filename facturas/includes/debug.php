<?php
// Archivo debug.php - Funciones de depuración
if (!function_exists('debugLog')) {
    function debugLog($message) {
        if (defined('DEBUG') && DEBUG) {
            $logDir = 'C:\xampp\php\logs'; // Directorio de logs
            $logFile = $logDir . '\php_error.log'; // Archivo de log

            // Crear el directorio si no existe
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
                if (DEBUG && PHP_SAPI !== 'cli') {
                    echo "<pre>DEBUG: Directorio de logs creado en $logDir</pre>";
                }
            }

            // Intentar escribir en el log
            $timestamp = date('Y-m-d H:i:s');
            $logEntry = "[$timestamp] DEBUG: $message" . PHP_EOL;
            $success = error_log($logEntry, 3, $logFile);

            // Si falla escribir en el archivo, usar salida o registro alternativo
            if ($success === false) {
                error_log("No se pudo escribir en $logFile. Usando salida como alternativa.", E_USER_WARNING);
                if (PHP_SAPI !== 'cli') {
                    echo "<pre>DEBUG (FALLBACK): $message</pre>";
                }
                // Opcional: Usar un archivo alternativo en el directorio del proyecto
                $fallbackLogFile = __DIR__ . '/../logs/debug.log';
                if (!is_dir(dirname($fallbackLogFile))) {
                    mkdir(dirname($fallbackLogFile), 0777, true);
                }
                error_log($logEntry, 3, $fallbackLogFile);
            }

            // Almacenar en sesión si es una solicitud POST que redirige, sin enviar salida al navegador
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && headers_sent()) {
                if (!session_id()) {
                    session_start();
                }
                $_SESSION['debug_logs'][] = $logEntry;
            } else if (PHP_SAPI !== 'cli' && !headers_sent()) {
                // Solo imprimir en la salida si no hay conflicto con headers
                echo "<pre>DEBUG: $message</pre>";
            }
        }
    }
}

if (!function_exists('debugVar')) {
    function debugVar($var, $return = false) {
        if (defined('DEBUG') && DEBUG) {
            $output = print_r($var, true);
            $logDir = 'C:\xampp\php\logs';
            $logFile = $logDir . '\php_error.log';

            // Crear el directorio si no existe
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
                if (DEBUG && PHP_SAPI !== 'cli') {
                    echo "<pre>DEBUG: Directorio de logs creado en $logDir</pre>";
                }
            }

            $timestamp = date('Y-m-d H:i:s');
            $logEntry = "[$timestamp] DEBUG VAR: $output" . PHP_EOL;
            $success = error_log($logEntry, 3, $logFile);

            if ($success === false) {
                error_log("No se pudo escribir en $logFile para debugVar. Usando salida como alternativa.", E_USER_WARNING);
                if (PHP_SAPI !== 'cli') {
                    echo "<pre>DEBUG VAR (FALLBACK): $output</pre>";
                }
                // Opcional: Usar un archivo alternativo en el directorio del proyecto
                $fallbackLogFile = __DIR__ . '/../logs/debug.log';
                if (!is_dir(dirname($fallbackLogFile))) {
                    mkdir(dirname($fallbackLogFile), 0777, true);
                }
                error_log($logEntry, 3, $fallbackLogFile);
            }

            // Almacenar en sesión si es una solicitud POST que redirige
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && headers_sent()) {
                if (!session_id()) {
                    session_start();
                }
                $_SESSION['debug_logs'][] = $logEntry;
            } else if (PHP_SAPI !== 'cli' && !headers_sent()) {
                // Solo imprimir en la salida si no hay conflicto con headers
                echo "<pre>DEBUG VAR: $output</pre>";
            }
            if ($return) {
                return $output;
            }
        }
        return null;
    }
}