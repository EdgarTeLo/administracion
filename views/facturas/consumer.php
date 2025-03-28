<?php
// consumer.php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/process_oc.php';

// Simular procesamiento de archivos desde una carpeta en lugar de RabbitMQ
function processFilesFromFolder($folderPath) {
    global $db; // Asegúrate de que $db esté disponible desde config/database.php

    if (!$db) {
        debugLog("Error: No se pudo conectar a la base de datos.");
        exit("Error fatal: No se pudo conectar a la base de datos.");
    }

    $files = glob($folderPath . '/*.pdf');
    if (empty($files)) {
        debugLog("No se encontraron archivos PDF en $folderPath");
        exit("No se encontraron archivos PDF en $folderPath");
    }

    foreach ($files as $file) {
        try {
            debugLog("Procesando archivo: $file");
            processOC($file);
        } catch (Exception $e) {
            debugLog("Error al procesar $file: " . $e->getMessage());
            exit("Procesamiento detenido debido a un error: " . $e->getMessage());
        }
    }
}

// Ejecutar el procesamiento desde la carpeta uploads_oc
processFilesFromFolder(__DIR__ . '/uploads_oc');
?>