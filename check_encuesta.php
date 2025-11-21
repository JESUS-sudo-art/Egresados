<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== REVISIÓN DE ENCUESTA ID 6 ===\n\n";

$encuesta = App\Models\Encuesta::with(['preguntas.opciones', 'preguntas.tipo'])->find(6);

if (!$encuesta) {
    echo "Encuesta no encontrada\n";
    exit;
}

echo "Encuesta: {$encuesta->nombre}\n";
echo "Descripción: {$encuesta->descripcion}\n\n";

foreach($encuesta->preguntas as $pregunta) {
    echo "Pregunta ID: {$pregunta->id}\n";
    echo "  - Texto: {$pregunta->texto}\n";
    echo "  - Tipo: {$pregunta->tipo->descripcion}\n";
    echo "  - Opciones:\n";
    
    if ($pregunta->opciones->isEmpty()) {
        echo "    (Sin opciones)\n";
    } else {
        foreach($pregunta->opciones as $opcion) {
            echo "    ID: {$opcion->id} - Texto: {$opcion->texto}\n";
        }
    }
    echo "\n";
}
