<?php

require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

try {
    echo "Probando generación de QR...\n";
    
    $appUrl = 'http://egresados.test';
    
    // Método simple
    $qrCode = new QrCode($appUrl);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    echo "QR generado exitosamente!\n";
    echo "Tamaño: " . strlen($result->getString()) . " bytes\n";
    echo "MIME Type: " . $result->getMimeType() . "\n";
    
    // Guardar a archivo para verificar
    file_put_contents('/tmp/test-qr.png', $result->getString());
    echo "QR guardado en /tmp/test-qr.png\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
