#!/usr/bin/env php
<?php
/**
 * Test simple de conexión a BD
 * Uso: php test_bd_connection.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Probando conexión a BD ===\n\n";

try {
    DB::connection()->getPdo();
    echo "✓ Conexión a BD exitosa\n\n";
    
    // Mostrar información de la BD
    $info = DB::select("SELECT VERSION()");
    echo "Versión MySQL: " . $info[0]->{'VERSION()'} . "\n\n";
    
    // Contar egresados
    $count = DB::table('egresado')->count();
    echo "Total de egresados: $count\n\n";
    
    // Mostrar primeros egresados
    $egresados = DB::table('egresado')->limit(3)->get(['id', 'nombre', 'email']);
    echo "Primeros egresados:\n";
    foreach ($egresados as $egr) {
        echo "  - ID {$egr->id}: {$egr->nombre} ({$egr->email})\n";
    }
    
    echo "\n✓ Todas las pruebas pasaron\n";
    
} catch (\Exception $e) {
    echo "❌ Error de conexión:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
?>
