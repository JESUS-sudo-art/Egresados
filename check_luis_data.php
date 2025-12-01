<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$egresado = App\Models\Egresado::with(['carreras.carrera.unidades'])->where('email', 'luis2502023@gmial.com')->first();

if (!$egresado) {
    echo "No se encontró el egresado con email luis2502023@gmial.com\n";
    exit;
}

echo "=== DATOS DEL EGRESADO ===\n";
echo "ID: {$egresado->id}\n";
echo "Nombre: {$egresado->nombre}\n";
echo "Apellidos: {$egresado->apellidos}\n";
echo "Matrícula: " . ($egresado->matricula ?? 'NO TIENE') . "\n";
echo "Año de egreso: " . ($egresado->anio_egreso ?? 'NO TIENE') . "\n";
echo "Carrera ID: " . ($egresado->carrera_id ?? 'NO TIENE') . "\n";

echo "\n=== CARRERAS ASOCIADAS (PIVOT) ===\n";
if ($egresado->carreras->count() > 0) {
    foreach ($egresado->carreras as $carreraRelacion) {
        echo "- Carrera: {$carreraRelacion->carrera->nombre}\n";
        echo "  ID relación: {$carreraRelacion->id}\n";
        echo "  Fecha egreso: " . ($carreraRelacion->fecha_egreso ?? 'NO TIENE') . "\n";
        
        echo "  Unidades asociadas a esta carrera:\n";
        foreach ($carreraRelacion->carrera->unidades as $unidad) {
            echo "    * {$unidad->nombre}\n";
        }
    }
} else {
    echo "No tiene carreras asociadas en la tabla pivot\n";
}

echo "\n=== CARRERA DIRECTA (carrera_id) ===\n";
if ($egresado->carrera_id) {
    $carreraDirecta = App\Models\Carrera::with('unidades')->find($egresado->carrera_id);
    if ($carreraDirecta) {
        echo "Carrera: {$carreraDirecta->nombre}\n";
        echo "Unidades:\n";
        foreach ($carreraDirecta->unidades as $unidad) {
            echo "  * {$unidad->nombre}\n";
        }
    }
} else {
    echo "No tiene carrera_id asignada\n";
}
