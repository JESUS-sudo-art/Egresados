<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Egresado;

echo "Verificando CURP duplicado...\n";
echo "=============================\n\n";

$curp = 'OEGJ030225BDECR';

$egresados = Egresado::where('curp', $curp)->get();

if ($egresados->isEmpty()) {
    echo "No se encontró ningún egresado con CURP: {$curp}\n";
} else {
    echo "Se encontraron " . $egresados->count() . " egresado(s) con CURP: {$curp}\n\n";
    
    foreach ($egresados as $egresado) {
        echo "ID: {$egresado->id}\n";
        echo "Email: {$egresado->email}\n";
        echo "Nombre: {$egresado->nombre} {$egresado->apellidos}\n";
        echo "CURP: {$egresado->curp}\n";
        echo "Matrícula: {$egresado->matricula}\n";
        echo "Creado: {$egresado->created_at}\n";
        echo "Actualizado: {$egresado->updated_at}\n";
        echo str_repeat('-', 50) . "\n";
    }
}

echo "\nUsuario actual autenticado sería:\n";
$usuarioActual = \App\Models\User::where('email', 'el4643874@gmail.com')->first();
if ($usuarioActual) {
    $egresadoActual = Egresado::where('email', $usuarioActual->email)->first();
    echo "Email: {$usuarioActual->email}\n";
    if ($egresadoActual) {
        echo "Egresado ID: {$egresadoActual->id}\n";
        echo "CURP actual: " . ($egresadoActual->curp ?: 'NULL') . "\n";
    }
}
