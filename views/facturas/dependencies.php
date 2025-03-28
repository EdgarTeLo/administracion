<?php
// Definir DEBUG explícitamente como true
define(constant_name: 'DEBUG', value: true); // Cambiado de if (!defined('DEBUG')) para forzar DEBUG a true

// Función para registrar logs de depuración (evitar duplicación con debug.php)
if (!function_exists('debugLog')) {
    function debugLog($message) {
        $logFile = 'C:/xampp/php/logs/php_error.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}

// Dependencias mínimas
$MIN_PHP_VERSION = '8.2.0';
$MIN_MYSQL_VERSION = '8.0.0'; // Ajusta según tu versión de MySQL en XAMPP
$MIN_BOOTSTRAP_VERSION = '5.3.0';

// Función para verificar PHP
function checkPhpVersion($minVersion) {
    $currentVersion = phpversion();
    if (version_compare($currentVersion, $minVersion, '<')) {
        debugLog("Error: PHP $currentVersion es menor que la versión mínima requerida ($minVersion)");
        die("Error: Se requiere PHP $minVersion o superior. Versión actual: $currentVersion");
    }
    debugLog("PHP $currentVersion verificado OK (mínimo: $minVersion)");
}

// Función para verificar MySQL
function checkMySqlVersion($minVersion, $conn) {
    $currentVersion = $conn->server_info;
    if (version_compare($currentVersion, $minVersion, '<')) {
        debugLog("Error: MySQL $currentVersion es menor que la versión mínima requerida ($minVersion)");
        die("Error: Se requiere MySQL $minVersion o superior. Versión actual: $currentVersion");
    }
    debugLog("MySQL $currentVersion verificado OK (mínimo: $minVersion)");
}

// Función para verificar versión de archivo
function checkFileVersion($file, $fileVersion, $minVersion) {
    if (empty($fileVersion)) {
        debugLog("Error: Versión no especificada en $file");
        die("Error: El archivo $file no especifica su versión");
    }
    if (version_compare($fileVersion, $minVersion, '<')) {
        debugLog("Error: $file versión $fileVersion es menor que la requerida ($minVersion)");
        die("Error: $file requiere versión $minVersion o superior. Actual: $fileVersion");
    }
    debugLog("$file versión $fileVersion verificado OK (mínimo: $minVersion)");
}

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar dependencias solo si no se han revisado en esta sesión
if (!isset($_SESSION['dependencies_checked']) || $_SESSION['dependencies_checked'] !== true) {
    require_once 'config/database.php'; // Asumo que aquí está la conexión a la BD
    $conn = getDBConnection();
    if (!$conn) {
        debugLog("Error: No se pudo conectar a la base de datos para verificar dependencias");
        die("Error de conexión a la base de datos");
    }

    checkPhpVersion($MIN_PHP_VERSION);
    checkMySqlVersion($MIN_MYSQL_VERSION, $conn);
    $_SESSION['dependencies_checked'] = true;
    $conn->close();
}