<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== REVISIÓN DE RESPUESTAS ===\n\n";

$respuestas = App\Models\Respuesta::with(['encuesta', 'pregunta'])->get();
echo "Total respuestas: " . $respuestas->count() . "\n\n";

foreach($respuestas as $r) {
    echo "ID: {$r->id}\n";
    echo "  - Encuesta ID: {$r->encuesta_id} ({$r->encuesta->nombre})\n";
    echo "  - Pregunta ID: {$r->pregunta_id} ({$r->pregunta->texto})\n";
    echo "  - Egresado ID: {$r->egresado_id}\n";
    echo "  - Opcion ID: {$r->opcion_id}\n";
    echo "  - Texto: {$r->respuesta_texto}\n";
    echo "  - Entero: {$r->respuesta_entero}\n";
    echo "  - Created: {$r->created_at}\n\n";
}

// Verificar usuarios
echo "\n=== USUARIOS ===\n\n";
$users = App\Models\User::all();
foreach($users as $u) {
    echo "User ID: {$u->id}, Email: {$u->email}\n";
}
