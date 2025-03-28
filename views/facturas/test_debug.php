<?php
$fileVersion = '1.0.0'; // No había FILE_VERSION en el original, pero lo añado para consistencia

require_once 'dependencies.php';
checkFileVersion(__FILE__, $fileVersion, '1.0.0');

// Define el directorio y el nombre del archivo
$debugDir = __DIR__ . '/debug';
$filename = 'test_debug.txt';
$filepath = $debugDir . '/' . $filename;

// Texto de prueba
$testContent = "Este es un texto de prueba generado el " . date('Y-m-d H:i:s') . " para verificar la escritura en el directorio debug.\n";

// Crear el directorio debug si no existe
if (!is_dir($debugDir)) {
    $created = mkdir($debugDir, 0777, true);
    if ($created) {
        echo "Directorio 'debug' creado exitosamente.<br>";
    } else {
        echo "Error al crear el directorio 'debug'. Verifica los permisos.<br>";
        exit;
    }
}

// Intentar guardar el archivo de texto
try {
    $written = file_put_contents($filepath, $testContent, FILE_APPEND);
    if ($written !== false) {
        echo "Archivo '$filename' creado y guardado exitosamente en '$debugDir'.<br>";
        echo "Contenido del archivo:<br><pre>" . htmlspecialchars(file_get_contents($filepath)) . "</pre>";
    } else {
        echo "Error al guardar el archivo '$filename'. Verifica los permisos del directorio 'debug'.<br>";
    }
} catch (Exception $e) {
    echo "Error al escribir en el archivo: " . $e->getMessage() . "<br>";
    error_log("Error al escribir en test_debug.txt: " . $e->getMessage());
}

// Verificar si el archivo existe y es legible
if (file_exists($filepath) && is_readable($filepath)) {
    echo "El archivo '$filename' existe y es legible.<br>";
} else {
    echo "El archivo '$filename' no existe o no es legible. Verifica los permisos.<br>";
}