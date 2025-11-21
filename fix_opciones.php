<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Actualizar opciones vacías con texto de ejemplo
$opciones = App\Models\Opcion::where('texto', '')->orWhereNull('texto')->get();

echo "Opciones encontradas con texto vacío: " . $opciones->count() . "\n\n";

foreach ($opciones as $opcion) {
    echo "Opción ID: {$opcion->id}, Pregunta ID: {$opcion->pregunta_id}, Texto actual: '{$opcion->texto}'\n";
}

echo "\n¿Estas opciones están vacías porque no se guardaron correctamente?\n";
echo "Por favor, ve a Admin Unidad y escribe el texto en cada opción, luego presiona Tab.\n";
