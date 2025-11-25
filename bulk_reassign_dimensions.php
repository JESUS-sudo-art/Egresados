<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Encuesta;
use App\Models\Dimension;
use App\Models\Pregunta;

/*
Uso:
  1) Redistribución automática balanceada (round-robin):
     php bulk_reassign_dimensions.php <encuesta_id> auto

  2) Aplicar mapeo desde CSV (pregunta_id,dimension_id):
     php bulk_reassign_dimensions.php <encuesta_id> csv=archivo.csv

Notas:
 - Sólo afecta preguntas de la encuesta indicada.
 - Ignora preguntas cuyo dimension_id ya coincide con el valor solicitado (en modo CSV).
*/

if ($argc < 3) {
    echo "Uso: php bulk_reassign_dimensions.php <encuesta_id> auto | csv=archivo.csv\n";
    exit(1);
}
$encuestaId = (int)$argv[1];
$modeArg = $argv[2];

$encuesta = Encuesta::find($encuestaId);
if (!$encuesta) { echo "Encuesta $encuestaId no encontrada\n"; exit(1);}    

$dimensiones = Dimension::where('encuesta_id',$encuestaId)->orderBy('orden')->get();
if ($dimensiones->count() === 0) { echo "No hay dimensiones para encuesta $encuestaId\n"; exit(1);}    

if (str_starts_with($modeArg,'csv=')) {
    $file = substr($modeArg,4);
    if (!is_file($file)) { echo "Archivo CSV $file no existe\n"; exit(1);}    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $applied=0; $skipped=0; $errors=0;
    foreach ($lines as $line) {
        [$pid,$did] = array_map('trim', explode(',', $line));
        if (!is_numeric($pid) || !is_numeric($did)) { $errors++; continue; }
        $preg = Pregunta::where('encuesta_id',$encuestaId)->find((int)$pid);
        if (!$preg) { $errors++; continue; }
        $dim = $dimensiones->firstWhere('id',(int)$did);
        if (!$dim) { $errors++; continue; }
        if ($preg->dimension_id == $dim->id) { $skipped++; continue; }
        $preg->dimension_id = $dim->id; $preg->save(); $applied++;
    }
    echo "CSV aplicado. Cambios: $applied | Saltadas: $skipped | Errores: $errors\n";
    exit(0);
}

if ($modeArg === 'auto') {
    $preguntas = Pregunta::where('encuesta_id',$encuestaId)->orderBy('orden')->get();
    if ($preguntas->isEmpty()) { echo "Sin preguntas en encuesta $encuestaId\n"; exit(1);}    
    $i=0; $total=0; $dimCount = $dimensiones->count();
    foreach ($preguntas as $p) {
        $dim = $dimensiones[$i % $dimCount];
        if ($p->dimension_id != $dim->id) {
            $p->dimension_id = $dim->id; $p->save(); $total++;    
        }
        $i++;
    }
    echo "Redistribución automática completa. Preguntas modificadas: $total\n";
    exit(0);
}

echo "Modo no reconocido. Usa 'auto' o 'csv=archivo.csv'\n";
exit(1);
