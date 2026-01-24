<?php
/**
 * Script para sincronizar cambios del perfil a la BD remota
 * Útil cuando hay problemas de conexión intermitente
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Egresado;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

\Log::info('=== Iniciando sincronización de cambios ===');

try {
    // Verificar conexión a la BD
    DB::connection()->getPdo();
    \Log::info('Conexión a BD establecida correctamente');
    
    // Obtener egresados que tienen cambios pendientes
    $egresados = Egresado::all();
    
    \Log::info('Total de egresados encontrados: ' . count($egresados));
    
    foreach ($egresados as $egresado) {
        try {
            // Forzar resguardo de cada egresado
            $egresado->save();
            \Log::info('Egresado sincronizado: ' . $egresado->id . ' - ' . $egresado->email);
        } catch (\Exception $e) {
            \Log::error('Error sincronizando egresado ' . $egresado->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    \Log::info('=== Sincronización completada ===');
    echo "✓ Sincronización completada correctamente\n";
    
} catch (\Exception $e) {
    \Log::error('Error en sincronización:', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
