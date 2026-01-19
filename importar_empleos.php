<?php

/**
 * Script de importación de empleos desde BD antigua
 * NOTA: La tabla 'laborales' en la BD antigua está VACÍA
 * Este script se proporciona como referencia para importación futura
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Configurar conexiones
$capsule = new DB();

// Conexión a BD nueva
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'db',
    'database' => 'egresados_db',
    'username' => 'user',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
], 'nueva');

// Conexión a BD antigua
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'db',
    'database' => 'bdwvexa',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
], 'antigua');

$capsule->setAsGlobal();

echo "=== IMPORTADOR DE EMPLEOS ===\n";
echo "Verificando datos en BD antigua...\n\n";

try {
    // Verificar conexión a BD antigua
    DB::connection('antigua')->getPdo();
    echo "✓ Conectado a BD antigua (bdwvexa)\n";
    
    // Verificar conexión a BD nueva
    DB::connection('nueva')->getPdo();
    echo "✓ Conectado a BD nueva (egresados_db)\n\n";

    // Obtener empleos de BD antigua
    $empleos = DB::connection('antigua')
        ->table('laborales')
        ->get();
    
    echo "Registros de empleos en BD antigua: " . $empleos->count() . "\n";
    
    if ($empleos->count() === 0) {
        echo "\n⚠️  TABLA VACÍA\n";
        echo "No hay datos de empleos en la tabla 'laborales' de la BD antigua.\n";
        echo "Esto significa que los empleos son un dato nuevo agregado en el nuevo sistema.\n";
        
        // Información de referencia
        echo "\n=== MAPEO DE CAMPOS (para referencia) ===\n";
        echo "BD Antigua → BD Nueva\n";
        echo "---\n";
        echo "egresados_id → egresado_id\n";
        echo "empresa → empresa\n";
        echo "puesto → puesto\n";
        echo "anioinicio → fecha_inicio (convertido a formato DATE: YYYY-01-01)\n";
        echo "aniofin → fecha_fin (convertido a formato DATE: YYYY-01-01)\n";
        echo "actualmente_activo → 0 (por defecto, no está en BD antigua)\n";
        echo "sector → NULL (no está en BD antigua)\n";
        
        exit(0);
    }

    echo "Iniciando inserción...\n\n";

    $insertados = 0;
    $errores = 0;

    foreach ($empleos as $empleo) {
        try {
            // Verificar que el egresado existe
            $egresadoExiste = DB::connection('nueva')
                ->table('egresado')
                ->where('id', $empleo->egresados_id)
                ->exists();
            
            if (!$egresadoExiste) {
                echo "⚠️  Empleo con egresado_id {$empleo->egresados_id} no existe en BD nueva. Saltando.\n";
                $errores++;
                continue;
            }

            // Convertir años a fechas
            $fecha_inicio = $empleo->anioinicio ? "{$empleo->anioinicio}-01-01" : null;
            $fecha_fin = $empleo->aniofin ? "{$empleo->aniofin}-01-01" : null;

            // Preparar datos
            $datos = [
                'egresado_id' => $empleo->egresados_id,
                'empresa' => $empleo->empresa ?? 'SIN NOMBRE',
                'puesto' => $empleo->puesto ?? null,
                'sector' => null,
                'actualmente_activo' => 0,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insertar en BD nueva
            DB::connection('nueva')
                ->table('laboral')
                ->insertOrIgnore($datos);

            $insertados++;

            if ($insertados % 100 === 0) {
                echo "Procesados: $insertados registros\n";
            }

        } catch (\Exception $e) {
            $errores++;
            echo "❌ Error en empleo {$empleo->id}: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== RESULTADO DE LA IMPORTACIÓN ===\n";
    echo "Registros insertados: $insertados\n";
    echo "Errores encontrados: $errores\n";

    // Verificar conteo final
    $total = DB::connection('nueva')
        ->table('laboral')
        ->count();
    
    echo "\nTotal de empleos en BD nueva: $total\n";

} catch (\Exception $e) {
    echo "❌ Error crítico: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ Proceso completado\n";
