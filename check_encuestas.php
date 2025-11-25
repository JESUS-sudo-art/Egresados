<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICANDO ENCUESTAS ===" . PHP_EOL . PHP_EOL;

// 1. Encuestas activas
$encuestas = App\Models\Encuesta::where('estatus', 'A')->get();
echo "Encuestas activas: " . $encuestas->count() . PHP_EOL;
foreach($encuestas as $e) {
    echo "  - ID: {$e->id} | {$e->nombre}" . PHP_EOL;
}

echo PHP_EOL;

// 2. Encuestas asignadas
$asignadas = App\Models\EncuestaAsignada::with('encuesta')->get();
echo "Encuestas asignadas: " . $asignadas->count() . PHP_EOL;
foreach($asignadas as $a) {
    $nombreEncuesta = $a->encuesta ? $a->encuesta->nombre : 'N/A';
    echo "  - Tipo: {$a->tipo_asignacion} | Encuesta: {$nombreEncuesta} | Estatus encuesta: " . ($a->encuesta ? $a->encuesta->estatus : 'N/A') . PHP_EOL;
}

echo PHP_EOL;

// 3. Usuario armando
$user = App\Models\User::where('email', 'armando345@gmail.com')->first();
if ($user) {
    echo "Usuario: {$user->name} ({$user->email})" . PHP_EOL;
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . PHP_EOL;
    
    $egresado = App\Models\Egresado::where('email', $user->email)->first();
    if ($egresado) {
        echo "Egresado ID: {$egresado->id}" . PHP_EOL;
        echo "Carreras del egresado: " . $egresado->carreras()->count() . PHP_EOL;
        
        $carreras = $egresado->carreras()->with(['carrera', 'generacion'])->get();
        foreach($carreras as $ce) {
            echo "  - Carrera: " . ($ce->carrera ? $ce->carrera->nombre : 'N/A') . 
                 " | Generación: " . ($ce->generacion ? $ce->generacion->nombre : 'N/A') . PHP_EOL;
        }
    } else {
        echo "NO SE ENCONTRÓ EGRESADO para {$user->email}" . PHP_EOL;
    }
} else {
    echo "NO SE ENCONTRÓ USUARIO" . PHP_EOL;
}
