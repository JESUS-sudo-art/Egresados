<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Egresado;
use App\Models\Carrera;

echo "========== CORREGIR CARRERA DE USUARIO ==========\n\n";

try {
    $egresado = Egresado::where('email', 'juanOsorio23@uabjo.mx')->first();
    
    if (!$egresado) {
        echo "❌ No se encontró el egresado\n";
        exit(1);
    }
    
    // Buscar la carrera correcta "Licenciatura en Arquitectura"
    $carrera = Carrera::where('nombre', 'Licenciatura en Arquitectura')
        ->where('estatus', 'A')
        ->first();
    
    if (!$carrera) {
        echo "❌ No se encontró la carrera 'Licenciatura en Arquitectura'\n";
        echo "\nCarreras disponibles con 'Arquitectura':\n";
        $carreras = Carrera::where('nombre', 'LIKE', '%Arquitectura%')->get();
        foreach ($carreras as $c) {
            echo "  - ID: {$c->id} - {$c->nombre} (Estatus: {$c->estatus})\n";
        }
        exit(1);
    }
    
    echo "✓ Carrera encontrada: {$carrera->nombre} (ID: {$carrera->id})\n";
    
    // Actualizar
    $egresado->carrera_id = $carrera->id;
    $egresado->save();
    
    echo "\n✅ Carrera actualizada correctamente a: {$carrera->nombre}\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
