<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Egresado;
use App\Models\Carrera;

echo "========== ACTUALIZAR CARRERA DE USUARIO ==========\n\n";

try {
    // Buscar usuario
    $user = User::where('email', 'juanOsorio23@uabjo.mx')->first();
    
    if (!$user) {
        echo "❌ No se encontró el usuario con email: juanOsorio23@uabjo.mx\n";
        exit(1);
    }
    
    echo "✓ Usuario encontrado: {$user->name}\n";
    
    // Buscar egresado asociado
    $egresado = Egresado::where('email', 'juanOsorio23@uabjo.mx')->first();
    
    if (!$egresado) {
        echo "❌ No se encontró el egresado asociado\n";
        exit(1);
    }
    
    echo "✓ Egresado encontrado: {$egresado->nombre} {$egresado->apellidos}\n";
    echo "  Carrera actual: " . ($egresado->carrera->nombre ?? 'Sin carrera') . "\n\n";
    
    // Buscar la carrera "Licenciatura en Arquitectura"
    $carrera = Carrera::where('nombre', 'LIKE', '%Arquitectura%')
        ->where('estatus', 'A')
        ->first();
    
    if (!$carrera) {
        echo "❌ No se encontró la carrera de Arquitectura\n";
        exit(1);
    }
    
    echo "✓ Carrera encontrada: {$carrera->nombre} (ID: {$carrera->id})\n\n";
    
    // Actualizar carrera del egresado
    $egresado->carrera_id = $carrera->id;
    $egresado->save();
    
    echo "✅ Carrera actualizada correctamente\n\n";
    
    // Mostrar datos actualizados
    $egresado->refresh();
    echo "========== DATOS ACTUALIZADOS ==========\n\n";
    echo "Usuario: {$user->email}\n";
    echo "Nombre: {$egresado->nombre} {$egresado->apellidos}\n";
    echo "Unidad: " . ($egresado->unidad->nombre ?? 'Sin unidad') . "\n";
    echo "Carrera: " . ($egresado->carrera->nombre ?? 'Sin carrera') . "\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
