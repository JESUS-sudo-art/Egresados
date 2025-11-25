<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$egresadoId = 8;

echo "=== ENCUESTAS DEL EGRESADO {$egresadoId} ===" . PHP_EOL . PHP_EOL;

// Cédulas pre-egreso
$cedulas = DB::table('cedula_preegreso')
    ->where('egresado_id', $egresadoId)
    ->get();

echo "Cédulas Pre-Egreso: " . $cedulas->count() . PHP_EOL;
foreach ($cedulas as $c) {
    echo "  - ID: {$c->id} | Fecha: {$c->fecha_aplicacion} | Estatus: {$c->estatus}" . PHP_EOL;
}

echo PHP_EOL;

// Encuestas laborales
$laborales = DB::table('encuesta_laboral')
    ->where('egresado_id', $egresadoId)
    ->get();

echo "Encuestas Laborales: " . $laborales->count() . PHP_EOL;
foreach ($laborales as $l) {
    echo "  - ID: {$l->id} | Fecha: {$l->fecha_aplicacion} | Estatus: {$l->estatus}" . PHP_EOL;
}
