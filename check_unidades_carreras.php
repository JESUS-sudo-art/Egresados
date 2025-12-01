<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========== UNIDADES ACTIVAS ==========\n\n";
$unidades = \App\Models\Unidad::where('estatus', 'A')->orderBy('nombre')->get();
foreach ($unidades as $unidad) {
    echo "ID: {$unidad->id} - {$unidad->nombre}\n";
    $carreras = $unidad->carreras()->where('carrera.estatus', 'A')->orderBy('nombre')->get();
    if ($carreras->count() > 0) {
        foreach ($carreras as $carrera) {
            echo "  └─ {$carrera->nombre}\n";
        }
    } else {
        echo "  └─ (Sin carreras asignadas)\n";
    }
    echo "\n";
}

echo "\n========== CARRERAS ACTIVAS (sin unidad asignada) ==========\n\n";
$carreras = \App\Models\Carrera::where('estatus', 'A')
    ->whereDoesntHave('unidades')
    ->orderBy('nombre')
    ->get();

foreach ($carreras as $carrera) {
    echo "ID: {$carrera->id} - {$carrera->nombre}\n";
}
