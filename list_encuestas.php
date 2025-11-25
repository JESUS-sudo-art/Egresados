<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Encuesta;

$encuestas = Encuesta::select('id','nombre','estatus')->get();
if($encuestas->isEmpty()){ echo "(Sin encuestas)\n"; exit; }
foreach($encuestas as $e){
  echo $e->id.' | '.$e->nombre.' | estatus='.$e->estatus."\n";
}
