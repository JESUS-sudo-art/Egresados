<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Egresado;
use App\Models\Respuesta;

$email = 'armando345@gmail.com';

echo "=== REVISANDO USUARIO: $email ===\n\n";

// Buscar usuario
$user = User::where('email', $email)->first();
if ($user) {
    echo "✓ Usuario encontrado:\n";
    echo "  - ID: {$user->id}\n";
    echo "  - Email: {$user->email}\n";
    echo "  - Name: {$user->name}\n\n";
} else {
    echo "✗ NO SE ENCONTRÓ USUARIO\n";
    exit(1);
}

// Buscar egresado
$egresado = Egresado::where('email', $email)->first();
if ($egresado) {
    echo "✓ Egresado encontrado:\n";
    echo "  - ID: {$egresado->id}\n";
    echo "  - Email: {$egresado->email}\n";
    echo "  - Nombre: {$egresado->nombre}\n";
    echo "  - Apellido: {$egresado->apellido_paterno}\n\n";
} else {
    echo "✗ NO SE ENCONTRÓ EGRESADO CON ESE EMAIL\n";
    echo "\nBuscando egresado por user_id...\n";
    $egresado = Egresado::where('usuario_id', $user->id)->first();
    if ($egresado) {
        echo "✓ Egresado encontrado por usuario_id:\n";
        echo "  - ID: {$egresado->id}\n";
        echo "  - Email: {$egresado->email}\n";
        echo "  - Nombre: {$egresado->nombre}\n";
        echo "  - Apellido: {$egresado->apellido_paterno}\n\n";
    } else {
        echo "✗ NO SE ENCONTRÓ EGRESADO ASOCIADO AL USUARIO\n";
        exit(1);
    }
}

// Buscar respuestas
echo "=== RESPUESTAS REGISTRADAS ===\n";
$respuestas = Respuesta::where('egresado_id', $egresado->id)->get();
echo "Total respuestas con egresado_id={$egresado->id}: " . $respuestas->count() . "\n\n";

if ($respuestas->count() > 0) {
    foreach ($respuestas as $resp) {
        echo "- Respuesta ID: {$resp->id}\n";
        echo "  Encuesta ID: {$resp->encuesta_id}\n";
        echo "  Pregunta ID: {$resp->pregunta_id}\n";
        echo "  Opción ID: {$resp->opcion_id}\n";
        echo "  Texto: " . ($resp->respuesta_texto ?? 'null') . "\n";
        echo "  Entero: " . ($resp->respuesta_entero ?? 'null') . "\n";
        echo "  Creado: {$resp->creado_en}\n\n";
    }
}

// Verificar con user_id por si acaso
echo "=== VERIFICANDO CON user_id={$user->id} (INCORRECTO) ===\n";
$respuestasUsuario = Respuesta::where('egresado_id', $user->id)->get();
echo "Total respuestas con egresado_id={$user->id}: " . $respuestasUsuario->count() . "\n";

if ($respuestasUsuario->count() > 0) {
    echo "\n⚠️ PROBLEMA: Hay respuestas guardadas con user_id en lugar de egresado_id\n";
    foreach ($respuestasUsuario as $resp) {
        echo "- Respuesta ID: {$resp->id} (Encuesta: {$resp->encuesta_id})\n";
    }
}

echo "\n=== ENCUESTAS POR RESPONDER ===\n";
$encuestas = \DB::table('encuesta_asignada')
    ->where('egresado_id', $egresado->id)
    ->get();
    
foreach ($encuestas as $encuesta) {
    echo "- Encuesta ID: {$encuesta->encuesta_id}\n";
    $yaRespondio = Respuesta::where('encuesta_id', $encuesta->encuesta_id)
        ->where('egresado_id', $egresado->id)
        ->exists();
    echo "  Ya respondida: " . ($yaRespondio ? 'SÍ' : 'NO') . "\n\n";
}
