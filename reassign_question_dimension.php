<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pregunta;
use App\Models\Dimension;

// Usage: php reassign_question_dimension.php <pregunta_id> <dimension_id>
if ($argc < 3) {
    echo "Uso: php reassign_question_dimension.php <pregunta_id> <dimension_id>\n";
    exit(1);
}
$preguntaId = (int)$argv[1];
$dimensionId = (int)$argv[2];

$pregunta = Pregunta::find($preguntaId);
if (!$pregunta) { echo "Pregunta $preguntaId no encontrada\n"; exit(1);}    
$dimension = Dimension::find($dimensionId);
if (!$dimension) { echo "Dimensión $dimensionId no encontrada\n"; exit(1);}    

$oldDim = $pregunta->dimension_id;
$pregunta->dimension_id = $dimension->id;
$pregunta->save();

echo "Pregunta {$pregunta->id} movida de dimensión {$oldDim} a {$dimension->id} ({$dimension->nombre})\n";
echo "OK\n";
