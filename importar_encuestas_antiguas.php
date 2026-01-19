<?php

/**
 * Script de importación de encuestas, dimensiones, preguntas y opciones desde BD antigua
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Configurar conexiones
$capsule = new DB();

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'db',
    'database' => 'egresados_db',
    'username' => 'user',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
], 'nueva');

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

echo "=== IMPORTADOR DE ENCUESTAS ANTIGUAS ===\n\n";

try {
    DB::connection('antigua')->getPdo();
    echo "✓ Conectado a BD antigua (bdwvexa)\n";
    
    DB::connection('nueva')->getPdo();
    echo "✓ Conectado a BD nueva (egresados_db)\n\n";

    // 1. IMPORTAR ENCUESTAS
    echo "--- IMPORTANDO ENCUESTAS ---\n";
    $encuestas = DB::connection('antigua')->table('encuestas')->get();
    echo "Encuestas a importar: " . $encuestas->count() . "\n";
    
    $encuestasImportadas = 0;
    foreach ($encuestas as $encuesta) {
        DB::connection('nueva')->table('encuesta')->insertOrIgnore([
            'id' => $encuesta->id,
            'nombre' => $encuesta->nombre ?? 'Sin nombre',
            'descripcion' => null,
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'estatus' => 'I', // Inactiva por ser antigua
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $encuestasImportadas++;
    }
    echo "✓ Encuestas importadas: $encuestasImportadas\n\n";

    // 2. IMPORTAR DIMENSIONES
    echo "--- IMPORTANDO DIMENSIONES ---\n";
    $dimensiones = DB::connection('antigua')->table('dimensiones')->get();
    echo "Dimensiones a importar: " . $dimensiones->count() . "\n";
    
    $dimensionesImportadas = 0;
    foreach ($dimensiones as $dimension) {
        DB::connection('nueva')->table('dimension')->insertOrIgnore([
            'id' => $dimension->id,
            'nombre' => $dimension->nombre ?? 'Sin nombre',
            'descripcion' => null,
            'orden' => $dimension->orden ?? 0,
            'encuesta_id' => $dimension->encuestas_id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $dimensionesImportadas++;
    }
    echo "✓ Dimensiones importadas: $dimensionesImportadas\n\n";

    // 3. IMPORTAR SUBDIMENSIONES
    echo "--- IMPORTANDO SUBDIMENSIONES ---\n";
    $subdimensiones = DB::connection('antigua')->table('subdimensiones')->get();
    echo "Subdimensiones a importar: " . $subdimensiones->count() . "\n";
    
    $subdimensionesImportadas = 0;
    foreach ($subdimensiones as $subdimension) {
        DB::connection('nueva')->table('subdimension')->insertOrIgnore([
            'id' => $subdimension->id,
            'nombre' => $subdimension->nombre ?? 'Sin nombre',
            'dimension_id' => $subdimension->dimensiones_id ?? null,
            'orden' => $subdimension->orden ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $subdimensionesImportadas++;
    }
    echo "✓ Subdimensiones importadas: $subdimensionesImportadas\n\n";

    // 4. IMPORTAR PREGUNTAS
    echo "--- IMPORTANDO PREGUNTAS ---\n";
    $preguntas = DB::connection('antigua')->table('preguntas')->get();
    echo "Preguntas a importar: " . $preguntas->count() . "\n";
    
    $preguntasImportadas = 0;
    foreach ($preguntas as $pregunta) {
        // Mapear tipo de pregunta: 1=Opción Múltiple, 2=Abierta (texto), 3=Sí/No
        $tipoPreguntaId = 1; // Por defecto Opción Múltiple
        if (isset($pregunta->tipos_id)) {
            if ($pregunta->tipos_id == 2 || $pregunta->tipos_id == 3) {
                $tipoPreguntaId = 2; // Abierta/texto
            }
        }
        
        // Obtener encuesta_id de la dimensión
        $dimension = DB::connection('nueva')
            ->table('dimension')
            ->where('id', $pregunta->dimensiones_id)
            ->first();
        
        $encuestaId = $dimension->encuesta_id ?? null;
        
        if (!$encuestaId) {
            // Si no hay dimensión válida, saltar
            continue;
        }
        
        DB::connection('nueva')->table('pregunta')->insertOrIgnore([
            'id' => $pregunta->id,
            'encuesta_id' => $encuestaId,
            'texto' => $pregunta->pregunta ?? 'Sin texto',
            'tipo_pregunta_id' => $tipoPreguntaId,
            'orden' => $pregunta->orden ?? 0,
            'dimension_id' => $pregunta->dimensiones_id ?? null,
            'subdimension_id' => $pregunta->subdimensiones_id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $preguntasImportadas++;
    }
    echo "✓ Preguntas importadas: $preguntasImportadas\n\n";

    // 5. IMPORTAR OPCIONES
    echo "--- IMPORTANDO OPCIONES ---\n";
    $opciones = DB::connection('antigua')->table('opciones')->get();
    echo "Opciones a importar: " . $opciones->count() . "\n";
    
    $opcionesImportadas = 0;
    foreach ($opciones as $opcion) {
        DB::connection('nueva')->table('opcion')->insertOrIgnore([
            'id' => $opcion->id,
            'texto' => $opcion->opcion ?? 'Sin texto',
            'valor' => $opcion->valor ?? 0,
            'orden' => $opcion->orden ?? 0,
            'pregunta_id' => $opcion->preguntas_id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $opcionesImportadas++;
    }
    echo "✓ Opciones importadas: $opcionesImportadas\n\n";

    echo "=== RESUMEN ===\n";
    echo "Encuestas: $encuestasImportadas\n";
    echo "Dimensiones: $dimensionesImportadas\n";
    echo "Subdimensiones: $subdimensionesImportadas\n";
    echo "Preguntas: $preguntasImportadas\n";
    echo "Opciones: $opcionesImportadas\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✓ Importación completada\n";
