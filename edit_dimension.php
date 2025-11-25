<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Dimension;

// Usage: php edit_dimension.php <dimension_id> nombre="Nuevo Nombre" descripcion="Texto" orden=5
if ($argc < 2) {
    echo "Uso: php edit_dimension.php <dimension_id> nombre=\"Nuevo\" descripcion=\"Texto opcional\" orden=3\n";
    exit(1);
}

$dimensionId = (int)$argv[1];
$dimension = Dimension::find($dimensionId);
if (!$dimension) {
    echo "Dimension $dimensionId no encontrada\n";
    exit(1);
}

$updates = [];
for ($i = 2; $i < $argc; $i++) {
    if (!str_contains($argv[$i], '=')) continue;
    [$key,$value] = explode('=', $argv[$i],2);
    $value = trim($value,'"');
    if (in_array($key, ['nombre','descripcion','orden'])) {
        if ($key === 'orden') $value = (int)$value;
        $updates[$key] = $value;
    }
}

if (empty($updates)) {
    echo "Nada que actualizar. Parámetros permitidos: nombre, descripcion, orden\n";
    exit(0);
}

$dimension->fill($updates)->save();
echo "Actualizada dimensión {$dimension->id}:\n";
echo json_encode($dimension->only(['id','nombre','descripcion','orden','encuesta_id']), JSON_PRETTY_PRINT)."\n";
echo "OK\n";
