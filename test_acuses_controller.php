<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SIMULANDO CONTROLADOR ACUSES ===" . PHP_EOL . PHP_EOL;

$email = 'armando345@gmail.com';

$egresado = App\Models\Egresado::with(['carreras.carrera'])
    ->where('email', $email)
    ->first();

if (!$egresado) {
    echo "✗ No se encontró egresado con email {$email}" . PHP_EOL;
    exit;
}

echo "✓ Egresado encontrado: {$egresado->nombre} {$egresado->apellidos} (ID: {$egresado->id})" . PHP_EOL . PHP_EOL;

$encuestas = [];

// Obtener Cédulas de Pre-Egreso
$cedulasPreegreso = App\Models\CedulaPreegreso::where('egresado_id', $egresado->id)
    ->orderBy('fecha_aplicacion', 'desc')
    ->get();

echo "Cédulas Pre-Egreso encontradas: " . $cedulasPreegreso->count() . PHP_EOL;

foreach ($cedulasPreegreso as $cedula) {
    try {
        $fecha = $cedula->fecha_aplicacion->format('d/M/Y');
        $folio = 'F-PRE-' . $cedula->id . '-' . $egresado->id;
        
        $encuestas[] = [
            'id' => $cedula->id,
            'tipo' => 'preegreso',
            'nombre' => 'Cédula de Pre-Egreso',
            'fecha' => $fecha,
            'folio' => $folio,
        ];
        
        echo "  ✓ Cédula ID {$cedula->id} - Fecha: {$fecha} - Folio: {$folio}" . PHP_EOL;
    } catch (Exception $e) {
        echo "  ✗ Error procesando cédula {$cedula->id}: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL;
echo "Total encuestas procesadas: " . count($encuestas) . PHP_EOL;

if (count($encuestas) > 0) {
    echo PHP_EOL . "Encuestas array:" . PHP_EOL;
    print_r($encuestas);
}
