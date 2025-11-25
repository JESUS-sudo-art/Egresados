<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Encuesta;
use App\Models\Dimension;

/*
Uso:
  Crear una dimensión:
    php create_dimensions.php <encuesta_id> nombre="Titulo" descripcion="Texto" orden=5

  Crear múltiples (separadas por ; ):
    php create_dimensions.php <encuesta_id> multi="Datos Personales|Información básica|1;Formación|Trayectoria|2;Opinión|Percepción|3"

Formato multi: nombre|descripcion|orden (descripcion puede quedar vacía ej: nombre||2)
*/

if ($argc < 3) {
    echo "Uso: php create_dimensions.php <encuesta_id> nombre=\"Titulo\" descripcion=\"Texto\" orden=1 | multi=\"Nombre|Desc|Orden;...\"\n";
    exit(1);
}

$encuestaId = (int)$argv[1];
$encuesta = Encuesta::find($encuestaId);
if(!$encuesta){ echo "Encuesta $encuestaId no encontrada\n"; exit(1);}    

$args = array_slice($argv,2);
$created = [];
foreach($args as $arg){
    if(str_starts_with($arg,'multi=')){
        $payload = substr($arg,6);
        foreach(explode(';',$payload) as $block){
            if(trim($block)==='') continue;
            [$n,$d,$o] = array_pad(explode('|',$block),3,'');
            $dim = Dimension::create([
                'nombre'=>$n,
                'descripcion'=>$d ?: null,
                'orden'=> is_numeric($o)? (int)$o : null,
                'encuesta_id'=>$encuestaId,
            ]);
            $created[] = $dim;
            echo "Creada dimensión {$dim->id} - {$dim->nombre}\n";
        }
    } else {
        // clave=valor
        $kv=[]; foreach($args as $a){ if(str_contains($a,'=')){ [$k,$v]=explode('=',$a,2); $kv[$k]=trim($v,'"'); } }
        if(isset($kv['nombre'])){
            $dim = Dimension::create([
                'nombre'=>$kv['nombre'],
                'descripcion'=>$kv['descripcion'] ?? null,
                'orden'=> isset($kv['orden'])? (int)$kv['orden'] : null,
                'encuesta_id'=>$encuestaId,
            ]);
            $created[]=$dim;
            echo "Creada dimensión {$dim->id} - {$dim->nombre}\n";
        }
        break; // evitar repetir
    }
}

echo "Total creadas: ".count($created)."\n";
exit(0);
