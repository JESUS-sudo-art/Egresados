<?php
/**
 * Sincronizar datos del perfil (egresado) a la encuesta pre-egreso (cedula_preegreso)
 * 
 * Este script toma los datos guardados en la tabla 'egresado' y los sincroniza
 * con la tabla 'cedula_preegreso' para que aparezcan en la encuesta pre-egreso
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Egresado;
use App\Models\CedulaPreegreso;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== Sincronizando Datos de Perfil a Encuesta Pre-Egreso ===\n\n";

try {
    // Obtener todos los egresados que tienen teléfono o fecha de nacimiento
    $egresados = Egresado::whereNotNull('telefono')
        ->orWhereNotNull('fecha_nacimiento')
        ->get();
    
    echo "Egresados a sincronizar: " . count($egresados) . "\n\n";
    
    $actualizados = 0;
    
    foreach ($egresados as $egresado) {
        try {
            // La edad se calcula en frontend; no se persiste en BD
            
            // Buscar o crear cédula pre-egreso
            $cedula = CedulaPreegreso::where('egresado_id', $egresado->id)->first();
            
            if ($cedula) {
                // Actualizar valores existentes
                $updates = [];
                
                if ($egresado->telefono) {
                    $updates['telefono_contacto'] = $egresado->telefono;
                }
                
                // Calcular y actualizar 'edad' si existe fecha_nacimiento
                if ($egresado->fecha_nacimiento) {
                    try {
                        $edad = Carbon::parse($egresado->fecha_nacimiento)->age;
                        // Limitar a rango razonable 10-100
                        if ($edad >= 10 && $edad <= 100) {
                            $updates['edad'] = $edad;
                        }
                    } catch (\Exception $e) {
                        // ignorar errores de parseo
                    }
                }
                
                if (!empty($updates)) {
                    // Usar raw SQL para evitar prepared statement issues
                    $setClauses = [];
                    $params = [];
                    
                    foreach ($updates as $field => $value) {
                        $setClauses[] = "`$field` = ?";
                        $params[] = $value;
                    }
                    
                    $params[] = $cedula->id;
                    
                    if (!empty($setClauses)) {
                        $sql = "UPDATE `cedula_preegreso` SET " . implode(', ', $setClauses) . " WHERE `id` = ?";
                        DB::update($sql, $params);
                        
                        echo "✓ Egresado {$egresado->id} ({$egresado->email}) sincronizado\n";
                        $actualizados++;
                    }
                }
            }
            
        } catch (\Exception $e) {
            echo "✗ Error sincronizando egresado {$egresado->id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Resultado ===\n";
    echo "Egresados sincronizados: $actualizados\n";
    echo "✓ Sincronización completada\n";
    
} catch (\Exception $e) {
    echo "❌ Error en sincronización:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
?>
