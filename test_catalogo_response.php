<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\EgresadoController();
$request = new Illuminate\Http\Request();
$response = $controller->catalogo($request);

$data = $response->getData();
$egresados = $data['page']['props']['egresados'];

echo "Total egresados: " . count($egresados) . "\n\n";

if (count($egresados) > 0) {
    echo "Primer egresado:\n";
    echo json_encode($egresados[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";
    
    if (isset($egresados[0]['encuestas_contestadas'])) {
        echo "✓ Tiene encuestas_contestadas\n";
        echo "Total: " . count($egresados[0]['encuestas_contestadas']) . "\n";
    } else {
        echo "✗ NO tiene encuestas_contestadas\n";
    }
}
