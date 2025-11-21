<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICACIÓN DE OPCIONES ===\n\n";

// Obtener todas las preguntas con sus opciones
$preguntas = App\Models\Pregunta::with(['opciones', 'tipo', 'encuesta'])
    ->whereHas('tipo', function($q) {
        $q->whereIn('descripcion', ['Opción Múltiple', 'Casillas de Verificación', 'Escala Likert']);
    })
    ->get();

echo "Total de preguntas con opciones: " . $preguntas->count() . "\n\n";

foreach ($preguntas as $pregunta) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Encuesta: {$pregunta->encuesta->nombre}\n";
    echo "Pregunta ID: {$pregunta->id}\n";
    echo "Texto: {$pregunta->texto}\n";
    echo "Tipo: {$pregunta->tipo->descripcion}\n";
    echo "Opciones: " . $pregunta->opciones->count() . "\n";
    
    if ($pregunta->opciones->count() > 0) {
        foreach ($pregunta->opciones as $opcion) {
            $textoMostrado = $opcion->texto ?: '(VACÍO)';
            $estado = empty($opcion->texto) ? '❌' : '✅';
            echo "  {$estado} Opción ID: {$opcion->id} | Texto: '{$textoMostrado}' | Valor: {$opcion->valor}\n";
        }
    } else {
        echo "  ⚠️  SIN OPCIONES\n";
    }
    echo "\n";
}

echo "=== FIN DE VERIFICACIÓN ===\n";
