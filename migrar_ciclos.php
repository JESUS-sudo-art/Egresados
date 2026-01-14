<?php
/**
 * Script to migrate ciclos data from bdwvexa_temp to egresados_db
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== MIGRANDO TABLA CICLOS ===\n\n";
    
    // Check if ciclo table exists
    $tablesCheck = DB::select("SHOW TABLES LIKE 'ciclo'");
    
    if (empty($tablesCheck)) {
        echo "1. Creando tabla 'ciclo'...\n";
        
        DB::statement("
            CREATE TABLE ciclo (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                observaciones TEXT NULL,
                estatus CHAR(1) DEFAULT 'A',
                creado_en DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                eliminado_en DATETIME NULL
            )
        ");
        
        echo "   ✓ Tabla 'ciclo' creada exitosamente\n\n";
    } else {
        echo "1. Tabla 'ciclo' ya existe, omitiendo creación\n\n";
    }
    
    // Check if data already exists
    $count = DB::table('ciclo')->count();
    
    if ($count > 0) {
        echo "2. La tabla 'ciclo' ya tiene $count registros\n";
        echo "   ¿Desea continuar y agregar/actualizar? (Los datos existentes se preservarán)\n\n";
    }
    
    // Get ciclos data from old database
    echo "3. Obteniendo datos de bdwvexa_temp.ciclos...\n";
    
    $ciclos = DB::connection('mysql')->select("
        SELECT id, nombre, observaciones, estatus 
        FROM bdwvexa_temp.ciclos 
        ORDER BY id
    ");
    
    echo "   ✓ " . count($ciclos) . " ciclos encontrados\n\n";
    
    // Insert or update ciclos
    echo "4. Insertando/actualizando ciclos en egresados_db...\n";
    
    $inserted = 0;
    $updated = 0;
    
    foreach ($ciclos as $ciclo) {
        $exists = DB::table('ciclo')->where('id', $ciclo->id)->first();
        
        if ($exists) {
            DB::table('ciclo')
                ->where('id', $ciclo->id)
                ->update([
                    'nombre' => $ciclo->nombre,
                    'observaciones' => $ciclo->observaciones,
                    'estatus' => $ciclo->estatus,
                    'actualizado_en' => now()
                ]);
            $updated++;
        } else {
            DB::table('ciclo')->insert([
                'id' => $ciclo->id,
                'nombre' => $ciclo->nombre,
                'observaciones' => $ciclo->observaciones,
                'estatus' => $ciclo->estatus,
                'creado_en' => now()
            ]);
            $inserted++;
        }
    }
    
    echo "   ✓ $inserted ciclos insertados\n";
    echo "   ✓ $updated ciclos actualizados\n\n";
    
    // Verify migration
    echo "5. Verificando migración...\n";
    
    $totalCiclos = DB::table('ciclo')->count();
    echo "   ✓ Total de ciclos en egresados_db: $totalCiclos\n";
    
    // Show sample of migrated data
    echo "\n6. Muestra de datos migrados:\n";
    $sample = DB::table('ciclo')->orderBy('id')->limit(5)->get();
    
    foreach ($sample as $c) {
        echo "   - ID: {$c->id}, Nombre: {$c->nombre}, Estatus: {$c->estatus}\n";
    }
    
    // Check bitacora_encuesta references
    echo "\n7. Verificando referencias en bitacora_encuesta...\n";
    
    $bitacorasConCiclo = DB::table('bitacora_encuesta')->whereNotNull('ciclo_id')->count();
    $ciclosReferenciados = DB::table('bitacora_encuesta')
        ->select('ciclo_id', DB::raw('COUNT(*) as total'))
        ->groupBy('ciclo_id')
        ->orderBy('ciclo_id')
        ->get();
    
    echo "   ✓ $bitacorasConCiclo bitácoras tienen ciclo_id\n";
    echo "   ✓ Ciclos referenciados:\n";
    
    foreach ($ciclosReferenciados as $ref) {
        $cicloNombre = DB::table('ciclo')->where('id', $ref->ciclo_id)->value('nombre');
        echo "     - Ciclo ID {$ref->ciclo_id} ({$cicloNombre}): {$ref->total} bitácoras\n";
    }
    
    echo "\n=== MIGRACIÓN COMPLETADA CON ÉXITO ===\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}
