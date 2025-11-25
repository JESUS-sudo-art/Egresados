<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICANDO USUARIO ARMANDO ===" . PHP_EOL . PHP_EOL;

$user = App\Models\User::where('email', 'armando345@gmail.com')->first();

if ($user) {
    echo "Usuario ID: {$user->id}" . PHP_EOL;
    echo "Nombre: {$user->name}" . PHP_EOL;
    echo "Email: {$user->email}" . PHP_EOL;
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . PHP_EOL;
    
    echo PHP_EOL;
    
    $egresado = App\Models\Egresado::where('email', $user->email)->first();
    
    if ($egresado) {
        echo "✓ EGRESADO ENCONTRADO" . PHP_EOL;
        echo "Egresado ID: {$egresado->id}" . PHP_EOL;
        echo "Nombre: {$egresado->nombre} {$egresado->apellidos}" . PHP_EOL;
        echo "Estatus ID: {$egresado->estatus_id}" . PHP_EOL;
        echo "Carreras: " . $egresado->carreras()->count() . PHP_EOL;
        
        // Verificar cédula pre-egreso
        $cedula = App\Models\CedulaPreegreso::where('egresado_id', $egresado->id)->first();
        if ($cedula) {
            echo "✓ Tiene cédula pre-egreso (ID: {$cedula->id})" . PHP_EOL;
        } else {
            echo "✗ NO tiene cédula pre-egreso" . PHP_EOL;
        }
        
        // Verificar encuesta laboral
        $laboral = App\Models\EncuestaLaboral::where('egresado_id', $egresado->id)->first();
        if ($laboral) {
            echo "✓ Tiene encuesta laboral (ID: {$laboral->id})" . PHP_EOL;
        } else {
            echo "✗ NO tiene encuesta laboral" . PHP_EOL;
        }
    } else {
        echo "✗ NO HAY EGRESADO VINCULADO AL EMAIL" . PHP_EOL;
        echo "Esto significa que las encuestas no se mostrarán." . PHP_EOL;
    }
} else {
    echo "✗ Usuario no encontrado" . PHP_EOL;
}
