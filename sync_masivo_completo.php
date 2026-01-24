<?php
/**
 * Sincronización masiva mejorada - sincroniza todos los egresados que tienen cédula
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "═══════════════════════════════════════════════════════════\n";
echo "  SINCRONIZACIÓN MASIVA: PERFIL → CÉDULA PRE-EGRESO\n";
echo "═══════════════════════════════════════════════════════════\n\n";

try {
    // Obtener todos los egresados que tienen cédula pre-egreso
    $egresados = DB::select("
        SELECT 
            e.id,
            e.email,
            e.telefono,
            e.fecha_nacimiento,
            c.id as cedula_id,
            c.telefono_contacto,
            c.edad
        FROM egresado e
        JOIN cedula_preegreso c ON e.id = c.egresado_id
        WHERE e.telefono IS NOT NULL OR e.fecha_nacimiento IS NOT NULL
    ");
    
    echo "Total de egresados con cédula a revisar: " . count($egresados) . "\n\n";
    
    $actualizados = 0;
    $sinCambios = 0;
    $errores = 0;
    
    foreach ($egresados as $eg) {
        try {
            $updates = [];
            $params = [];
            $cambios = [];
            
            // Verificar teléfono
            if ($eg->telefono && $eg->telefono !== $eg->telefono_contacto) {
                $updates[] = "`telefono_contacto` = ?";
                $params[] = $eg->telefono;
                $cambios[] = "tel: {$eg->telefono}";
            }
            
            // Verificar edad
            if ($eg->fecha_nacimiento) {
                try {
                    $edad = Carbon::parse($eg->fecha_nacimiento)->age;
                    if ($edad >= 10 && $edad <= 100 && $edad != $eg->edad) {
                        $updates[] = "`edad` = ?";
                        $params[] = $edad;
                        $cambios[] = "edad: {$edad}";
                    }
                } catch (\Exception $e) {
                    // Ignorar errores de fecha
                }
            }
            
            if (!empty($updates)) {
                $params[] = $eg->cedula_id;
                $sql = "UPDATE `cedula_preegreso` SET " . implode(', ', $updates) . ", `updated_at` = NOW() WHERE `id` = ?";
                DB::update($sql, $params);
                
                $cambiosStr = implode(', ', $cambios);
                echo "✓ Egresado {$eg->id} ({$eg->email}): {$cambiosStr}\n";
                $actualizados++;
            } else {
                $sinCambios++;
            }
            
        } catch (\Exception $e) {
            echo "✗ Error en egresado {$eg->id}: " . $e->getMessage() . "\n";
            $errores++;
        }
    }
    
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "RESULTADO:\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✓ Egresados sincronizados: $actualizados\n";
    echo "- Sin cambios necesarios: $sinCambios\n";
    if ($errores > 0) {
        echo "✗ Errores: $errores\n";
    }
    echo "\n✓ Sincronización masiva completada\n";
    
} catch (\Exception $e) {
    echo "❌ Error crítico:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
