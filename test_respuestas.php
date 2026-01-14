<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Egresado;
use App\Models\BitacoraEncuesta;
use App\Models\RespuestaInt;
use App\Models\RespuestaTxt;

// Buscar el egresado
$egresado = Egresado::where('email', 'zura_jda@hotmail.com')->first();

echo "=== DEBUG RESPUESTAS ANTIGUAS ===\n\n";

if (!$egresado) {
    echo "❌ NO SE ENCONTRÓ EGRESADO CON EMAIL: zura_jda@hotmail.com\n";
    
    // Listar todos los egresados con 'zura'
    echo "\n📋 Egresados con 'zura':\n";
    $similares = Egresado::where('email', 'LIKE', '%zura%')->get(['id', 'nombre', 'email']);
    foreach ($similares as $e) {
        echo "  - ID: {$e->id}, Email: {$e->email}, Nombre: {$e->nombre}\n";
    }
} else {
    echo "✅ EGRESADO ENCONTRADO:\n";
    echo "  - ID: {$egresado->id}\n";
    echo "  - Nombre: {$egresado->nombre} {$egresado->apellidos}\n";
    echo "  - Email: {$egresado->email}\n\n";
    
    // Obtener bitácoras
    $bitacoras = BitacoraEncuesta::where('egresado_id', $egresado->id)->get();
    echo "📊 BITÁCORAS ENCONTRADAS: {$bitacoras->count()}\n";
    
    foreach ($bitacoras as $b) {
        echo "\n  Bitácora ID: {$b->id}\n";
        echo "    - Encuesta ID: {$b->encuesta_id}\n";
        echo "    - Ciclo ID: {$b->ciclo_id}\n";
        echo "    - Fecha Inicio: {$b->fecha_inicio}\n";
        echo "    - Completada: " . ($b->completada ? 'Sí' : 'No') . "\n";
        
        // Verificar relaciones
        $encuesta = $b->encuesta;
        $ciclo = $b->ciclo;
        
        echo "    - Encuesta nombre: " . ($encuesta ? $encuesta->nombre : "❌ NULL") . "\n";
        echo "    - Ciclo nombre: " . ($ciclo ? $ciclo->nombre : "❌ NULL") . "\n";
        
        // Contar respuestas
        $respInt = RespuestaInt::where('bitacora_encuesta_id', $b->id)->count();
        $respTxt = RespuestaTxt::where('bitacora_encuesta_id', $b->id)->count();
        echo "    - Respuestas: {$respInt} numéricas + {$respTxt} texto\n";
    }
}

echo "\n";
