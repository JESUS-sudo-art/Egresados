<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREANDO ESTRUCTURA ACADÉMICA BÁSICA ===" . PHP_EOL . PHP_EOL;

// 1. Crear Unidad
$unidad = App\Models\Unidad::firstOrCreate(
    ['nombre' => 'Instituto Tecnológico del Valle de Oaxaca'],
    [
        'descripcion' => 'Unidad académica principal',
        'estatus' => 'A',
    ]
);
echo "✓ Unidad: {$unidad->nombre} (ID: {$unidad->id})" . PHP_EOL;

// 2. Crear Carreras
$carreras = [
    'Medicina y Cirugia',
    'Enfermeria y Obstetricia',
    'Economia',
    'Contaduria y Administracion',
    'Ciencias Quimicas',
    'Idiomas',
    'Arquitectura 5 de Mayo',
];

$carrerasCreadas = [];
foreach ($carreras as $nombreCarrera) {
    $carrera = App\Models\Carrera::firstOrCreate(
        ['nombre' => $nombreCarrera],
        [
            'descripcion' => 'Programa de ' . $nombreCarrera,
            'estatus' => 'A',
        ]
    );
    $carrerasCreadas[] = $carrera;
    
    // Vincular carrera con unidad (tabla: unidad_carrera)
    $existe = DB::table('unidad_carrera')
        ->where('unidad_id', $unidad->id)
        ->where('carrera_id', $carrera->id)
        ->exists();
    
    if (!$existe) {
        DB::table('unidad_carrera')->insert([
            'unidad_id' => $unidad->id,
            'carrera_id' => $carrera->id,
        ]);
    }
    
    echo "✓ Carrera: {$carrera->nombre} (ID: {$carrera->id})" . PHP_EOL;
}

echo PHP_EOL;

// 3. Crear Generaciones
$generaciones = [
    '2020-2024',
    '2021-2025',
    '2022-2026',
];

$generacionesCreadas = [];
foreach ($generaciones as $nombreGen) {
    $generacion = App\Models\Generacion::firstOrCreate(
        ['nombre' => $nombreGen],
        [
            'estatus' => 'A',
        ]
    );
    $generacionesCreadas[] = $generacion;
    echo "✓ Generación: {$generacion->nombre} (ID: {$generacion->id})" . PHP_EOL;
}

echo PHP_EOL;

// 4. Asignar carrera al egresado Armando
$egresado = App\Models\Egresado::where('email', 'armando345@gmail.com')->first();
if ($egresado) {
    $carreraPrincipal = $carrerasCreadas[0]; // Medicina y Cirugia
    $generacionActual = $generacionesCreadas[1]; // 2021-2025
    
    $existe = DB::table('egresado_carrera')
        ->where('egresado_id', $egresado->id)
        ->where('carrera_id', $carreraPrincipal->id)
        ->exists();
    
    if (!$existe) {
        DB::table('egresado_carrera')->insert([
            'egresado_id' => $egresado->id,
            'carrera_id' => $carreraPrincipal->id,
            'generacion_id' => $generacionActual->id,
        ]);
        echo "✓ Carrera asignada a Armando: {$carreraPrincipal->nombre} - {$generacionActual->nombre}" . PHP_EOL;
    } else {
        echo "○ Armando ya tiene carrera asignada" . PHP_EOL;
    }
}

echo PHP_EOL;
echo "=== ESTRUCTURA CREADA EXITOSAMENTE ===" . PHP_EOL;
