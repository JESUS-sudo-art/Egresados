<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICANDO CATÁLOGOS ===" . PHP_EOL . PHP_EOL;

// 1. Estados civiles
$estadosCiviles = DB::table('cat_estado_civil')->get();
echo "Estados Civiles: " . $estadosCiviles->count() . PHP_EOL;
if ($estadosCiviles->count() === 0) {
    echo "Creando estados civiles..." . PHP_EOL;
    $estados = ['Soltero/a', 'Casado/a', 'Divorciado/a', 'Viudo/a', 'Unión libre'];
    foreach ($estados as $estado) {
        DB::table('cat_estado_civil')->insert([
            'nombre' => $estado,
        ]);
        echo "  ✓ {$estado}" . PHP_EOL;
    }
} else {
    foreach ($estadosCiviles as $ec) {
        echo "  - {$ec->nombre}" . PHP_EOL;
    }
}

echo PHP_EOL;

// 2. Géneros
$generos = DB::table('cat_genero')->get();
echo "Géneros: " . $generos->count() . PHP_EOL;
if ($generos->count() === 0) {
    echo "Creando géneros..." . PHP_EOL;
    $generosLista = ['Masculino', 'Femenino', 'No binario', 'Prefiero no decirlo'];
    foreach ($generosLista as $genero) {
        DB::table('cat_genero')->insert([
            'nombre' => $genero,
        ]);
        echo "  ✓ {$genero}" . PHP_EOL;
    }
} else {
    foreach ($generos as $g) {
        echo "  - {$g->nombre}" . PHP_EOL;
    }
}

echo PHP_EOL;

// 3. Estatus
$estatuses = DB::table('cat_estatus')->get();
echo "Estatus: " . $estatuses->count() . PHP_EOL;
if ($estatuses->count() === 0) {
    echo "Creando estatus..." . PHP_EOL;
    $estatusLista = ['ACTIVO', 'INACTIVO', 'EGRESADO', 'TITULADO'];
    foreach ($estatusLista as $estatus) {
        DB::table('cat_estatus')->insert([
            'nombre' => $estatus,
        ]);
        echo "  ✓ {$estatus}" . PHP_EOL;
    }
} else {
    foreach ($estatuses as $e) {
        echo "  - {$e->nombre}" . PHP_EOL;
    }
}

echo PHP_EOL;
echo "=== CATÁLOGOS VERIFICADOS ===" . PHP_EOL;
