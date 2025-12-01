<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Unidad;
use App\Models\Carrera;
use Illuminate\Support\Facades\DB;

echo "========== POBLANDO UNIDADES Y CARRERAS ==========\n\n";

// Definir la estructura de unidades y sus carreras
$estructura = [
    'Facultad de Arquitectura 5 de Mayo' => [
        'Licenciatura en Arquitectura'
    ],
    'Facultad de Ciencias Químicas' => [
        'Licenciatura en Químico Farmacéutico Biólogo'
    ],
    'Facultad de Contaduría y Administración' => [
        'Licenciatura en Contaduría Pública',
        'Licenciatura en Administración',
        'Licenciatura en Turismo y Desarrollo Sustentable',
        'Licenciatura en Administración Pública y Gestión Municipal',
        'Licenciatura en Microfinanzas'
    ],
    'Facultad de Economía' => [
        'Licenciatura en Economía'
    ],
    'Facultad de Enfermería y Obstetricia' => [
        'Licenciatura en Enfermería'
    ],
    'Escuela de Ciencias' => [
        'Licenciatura en Ciencias Ambientales',
        'Licenciatura en Matemáticas Aplicadas'
    ],
    'Instituto de Idiomas' => [
        'Licenciatura en la Enseñanza de Idiomas'
    ],
    'Facultad de Medicina y Cirugía' => [
        'Licenciatura en Medicina y Cirugía',
        'Licenciatura en Terapia Física',
        'Licenciatura en Terapia Ocupacional'
    ]
];

DB::beginTransaction();

try {
    foreach ($estructura as $nombreUnidad => $carreras) {
        echo "Procesando: $nombreUnidad\n";
        
        // Buscar o crear unidad
        $unidad = Unidad::firstOrCreate(
            ['nombre' => $nombreUnidad],
            [
                'estatus' => 'A',
                'clave' => strtoupper(substr($nombreUnidad, 0, 10))
            ]
        );
        
        echo "  ✓ Unidad ID: {$unidad->id}\n";
        
        foreach ($carreras as $nombreCarrera) {
            echo "    → $nombreCarrera\n";
            
            // Buscar o crear carrera
            $carrera = Carrera::firstOrCreate(
                ['nombre' => $nombreCarrera],
                [
                    'nivel' => 'Licenciatura',
                    'estatus' => 'A'
                ]
            );
            
            echo "      ✓ Carrera ID: {$carrera->id}\n";
            
            // Asignar carrera a unidad (si no está asignada)
            if (!$unidad->carreras()->where('carrera_id', $carrera->id)->exists()) {
                $unidad->carreras()->attach($carrera->id, ['estatus' => 'A']);
                echo "      ✓ Asignada a unidad\n";
            } else {
                echo "      • Ya estaba asignada\n";
            }
        }
        
        echo "\n";
    }
    
    DB::commit();
    echo "\n✅ PROCESO COMPLETADO EXITOSAMENTE\n\n";
    
    // Mostrar resumen
    echo "========== RESUMEN ==========\n\n";
    $unidades = Unidad::where('estatus', 'A')->with(['carreras' => function($q) {
        $q->where('carrera.estatus', 'A');
    }])->orderBy('nombre')->get();
    
    foreach ($unidades as $unidad) {
        echo "{$unidad->nombre}\n";
        foreach ($unidad->carreras as $carrera) {
            echo "  • {$carrera->nombre}\n";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
