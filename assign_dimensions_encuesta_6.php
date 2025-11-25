<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Dimension;
use App\Models\Pregunta;
use Illuminate\Support\Facades\DB;

$encuestaId = 6;

echo "== Asignando dimensiones para encuesta $encuestaId ==\n";

$existingDims = Dimension::where('encuesta_id',$encuestaId)->orderBy('orden')->get();
if($existingDims->count() === 0){
    echo "No hay dimensiones, creando...\n";
    $names = [
        'Datos Personales' => 'Información básica del encuestado',
        'Formación Académica' => 'Trayectoria y estudios',
        'Situación Laboral' => 'Empleo y actividad profesional',
        'Opinión y Satisfacción' => 'Percepción y evaluación'
    ];
    $orden = 1;
    foreach($names as $n=>$d){
        $dim = Dimension::create([
            'nombre'=>$n,
            'descripcion'=>$d,
            'orden'=>$orden++,
            'encuesta_id'=>$encuestaId
        ]);
        echo "Creada dimensión {$dim->id} - {$dim->nombre}\n";
    }
    $existingDims = Dimension::where('encuesta_id',$encuestaId)->orderBy('orden')->get();
} else {
    echo "Dimensiones existentes encontradas: {$existingDims->count()}\n";
}

$preguntas = Pregunta::where('encuesta_id',$encuestaId)->orderBy('orden')->get();
if($preguntas->isEmpty()){
    echo "No hay preguntas para la encuesta $encuestaId. Abortando.\n";
    exit;}

// Filtrar preguntas ya asignadas
$sinDimension = $preguntas->filter(fn($p)=>empty($p->dimension_id));
if($sinDimension->isEmpty()){
    echo "Todas las preguntas ya tienen dimension_id. Nada que hacer.\n";
    exit;}

$dimCount = $existingDims->count();
$chunkSize = (int) ceil($sinDimension->count() / $dimCount);
$chunks = $sinDimension->chunk($chunkSize);

$i=0; $asignadas=0;
foreach($chunks as $chunk){
    $dim = $existingDims[$i % $dimCount];
    foreach($chunk as $preg){
        $preg->dimension_id = $dim->id;
        $preg->save();
        $asignadas++;
    }
    echo "Asignadas " . $chunk->count() . " preguntas a dimensión {$dim->id} - {$dim->nombre}\n";
    $i++;
}

echo "Total preguntas asignadas: $asignadas\n";

// Resumen final
$summary = Pregunta::where('encuesta_id',$encuestaId)
    ->orderBy('dimension_id')
    ->orderBy('orden')
    ->get()
    ->map(function($p){ return $p->id.'=>dim '.$p->dimension_id; });

echo "Resumen IDs pregunta: \n" . implode("\n", $summary->toArray()) . "\n";

echo "== Completado ==\n";
