<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CatEstatus;

echo "Agregando estatus 'Estudiante'...\n";
echo "==================================\n\n";

// Verificar estatus actuales
echo "Estatus actuales:\n";
foreach (CatEstatus::all() as $estatus) {
    echo "- {$estatus->id}: {$estatus->nombre}\n";
}

echo "\n";

// Crear estatus Estudiante si no existe
$estudiante = CatEstatus::firstOrCreate(['nombre' => 'Estudiante']);

if ($estudiante->wasRecentlyCreated) {
    echo "✓ Estatus 'Estudiante' creado exitosamente (id: {$estudiante->id})\n";
} else {
    echo "⚠ El estatus 'Estudiante' ya existía (id: {$estudiante->id})\n";
}

echo "\nEstatus actualizados:\n";
foreach (CatEstatus::all() as $estatus) {
    echo "- {$estatus->id}: {$estatus->nombre}\n";
}
