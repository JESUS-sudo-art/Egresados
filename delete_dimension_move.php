<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Dimension;
use App\Models\Pregunta;

/*
Uso:
  php delete_dimension_move.php <dimension_origen_id> <dimension_destino_id|null>

Si destino es 'null' o 0, se pone dimension_id = null en las preguntas.
*/

if ($argc < 3){
    echo "Uso: php delete_dimension_move.php <origen_id> <destino_id|null>\n"; exit(1);
}
$origenId = (int)$argv[1];
$destArg = $argv[2];
$destinoId = ($destArg==='null'||$destArg==='0')? null : (int)$destArg;

$origen = Dimension::find($origenId);
if(!$origen){ echo "Dimensión origen $origenId no encontrada\n"; exit(1);}    
$destino = null;
if($destinoId){ $destino = Dimension::find($destinoId); if(!$destino){ echo "Dimensión destino $destinoId no encontrada\n"; exit(1);} }

$preguntas = Pregunta::where('dimension_id',$origenId)->get();
echo "Preguntas a mover: {$preguntas->count()}\n";
foreach($preguntas as $p){
    $p->dimension_id = $destino? $destino->id : null;
    $p->save();
}

$origen->delete();
echo "Dimensión $origenId eliminada. Preguntas movidas a ".($destino? $destino->id : 'NULL')."\n";
exit(0);
