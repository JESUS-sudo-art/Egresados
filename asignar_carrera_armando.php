<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ASIGNANDO CARRERA A ARMANDO ===" . PHP_EOL . PHP_EOL;

// 1. Obtener egresado
$egresado = App\Models\Egresado::where('email', 'armando345@gmail.com')->first();
if (!$egresado) {
    echo "ERROR: No se encontró el egresado" . PHP_EOL;
    exit(1);
}

echo "Egresado: {$egresado->nombre} {$egresado->apellidos} (ID: {$egresado->id})" . PHP_EOL;
echo "Carreras actuales: " . $egresado->carreras()->count() . PHP_EOL . PHP_EOL;

// 2. Obtener primera carrera y generación disponibles
$carrera = App\Models\Carrera::first();
$generacion = App\Models\Generacion::first();

if (!$carrera) {
    echo "ERROR: No hay carreras en el sistema" . PHP_EOL;
    exit(1);
}

if (!$generacion) {
    echo "ERROR: No hay generaciones en el sistema" . PHP_EOL;
    exit(1);
}

echo "Carrera seleccionada: {$carrera->nombre} (ID: {$carrera->id})" . PHP_EOL;
echo "Generación seleccionada: {$generacion->nombre} (ID: {$generacion->id})" . PHP_EOL . PHP_EOL;

// 3. Asignar carrera al egresado (tabla: egresado_carrera)
$existe = DB::table('egresado_carrera')
    ->where('egresado_id', $egresado->id)
    ->where('carrera_id', $carrera->id)
    ->exists();

if ($existe) {
    echo "La carrera ya está asignada" . PHP_EOL;
} else {
    DB::table('egresado_carrera')->insert([
        'egresado_id' => $egresado->id,
        'carrera_id' => $carrera->id,
        'generacion_id' => $generacion->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Carrera asignada exitosamente" . PHP_EOL;
}

echo PHP_EOL;
echo "Carreras del egresado ahora: " . $egresado->carreras()->count() . PHP_EOL;
