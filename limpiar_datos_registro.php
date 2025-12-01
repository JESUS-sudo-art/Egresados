<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Unidad;
use App\Models\Carrera;
use Illuminate\Support\Facades\DB;

echo "========== LIMPIANDO DATOS ==========\n\n";

DB::beginTransaction();

try {
    // 1. Quitar "Medicina y Cirugía" de la Facultad de Arquitectura
    $arquitectura = Unidad::where('nombre', 'LIKE', '%Arquitectura%')->first();
    if ($arquitectura) {
        $medicinaCarrera = Carrera::where('nombre', 'Medicina y Cirugia')->first();
        if ($medicinaCarrera) {
            $arquitectura->carreras()->detach($medicinaCarrera->id);
            echo "✓ Removida 'Medicina y Cirugía' de Facultad de Arquitectura\n";
        }
    }
    
    // 2. Desactivar Escuela de Ciencias
    $escuelaCiencias = Unidad::where('nombre', 'Escuela de Ciencias')->first();
    if ($escuelaCiencias) {
        $escuelaCiencias->estatus = 'I';
        $escuelaCiencias->save();
        echo "✓ Desactivada 'Escuela de Ciencias'\n";
    }
    
    DB::commit();
    echo "\n✅ LIMPIEZA COMPLETADA\n\n";
    
    // Mostrar resumen actualizado
    echo "========== UNIDADES ACTIVAS ==========\n\n";
    $unidades = Unidad::where('estatus', 'A')->with(['carreras' => function($q) {
        $q->where('carrera.estatus', 'A')->orderBy('nombre');
    }])->orderBy('nombre')->get();
    
    foreach ($unidades as $unidad) {
        echo "{$unidad->nombre}\n";
        foreach ($unidad->carreras as $carrera) {
            echo "  • {$carrera->nombre}\n";
        }
        if ($unidad->carreras->count() == 0) {
            echo "  (sin carreras)\n";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
