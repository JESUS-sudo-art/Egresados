<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Egresado;

echo "Buscando egresados con 'Estudiante' en el nombre...\n";

$egresados = Egresado::where('nombre', 'like', '%Estudiante%')->get();

echo "Encontrados: " . $egresados->count() . " registros\n";

foreach ($egresados as $egresado) {
    echo "- Eliminando: {$egresado->matricula} - {$egresado->nombre} ({$egresado->email})\n";
    $egresado->delete();
}

echo "\n✓ Limpieza completada.\n";
