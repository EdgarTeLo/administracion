<?php
require_once 'vendor/autoload.php';
$file = 'C:\xampp\htdocs\administracion\facturas\uploads_oc\PO_25100079132_0.pdf';
$parser = new \Smalot\PdfParser\Parser();
try {
    $pdf = $parser->parseFile($file);
    $text = $pdf->getText();
    echo $text;
} catch (Exception $e) {
    echo "Error al extraer texto: " . $e->getMessage();
}
?>