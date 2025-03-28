<?php
// config/database.php - Versión 1.0.0

$fileVersion = '1.1.0'; // Añadido para consistencia

require_once __DIR__ . '/../dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.0.0');

// Incluir archivo de depuración
require_once __DIR__ . '/../includes/debug.php';

// Definir constantes de conexión
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'facturas');

// Inicialización de $db con PDO para process_oc.php y upload_oc.php
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    debugLog("Conexión PDO establecida con éxito en " . DB_HOST . "/" . DB_NAME);
    global $db; // Hacer $db accesible globalmente
} catch (PDOException $e) {
    error_log("Error en la conexión PDO: " . $e->getMessage());
    debugLog("Error en la conexión PDO: " . $e->getMessage());
    // No detener la ejecución, permitir que otras partes funcionen
}
?>