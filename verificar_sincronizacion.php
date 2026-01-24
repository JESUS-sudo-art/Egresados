<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Verificación de Datos Sincronizados ===\n\n";

// Contar registros con edad
$conEdad = DB::table('cedula_preegreso')->whereNotNull('edad')->count();
echo "Registros con edad: $conEdad\n";

// Contar registros con teléfono
$conTelefono = DB::table('cedula_preegreso')->whereNotNull('telefono_contacto')->count();
echo "Registros con teléfono: $conTelefono\n\n";

// Mostrar algunos ejemplos
echo "=== Ejemplos de Registros Sincronizados ===\n";
$ejemplos = DB::table('cedula_preegreso')
    ->whereNotNull('edad')
    ->orWhereNotNull('telefono_contacto')
    ->limit(3)
    ->get();

foreach ($ejemplos as $ej) {
    echo "ID: {$ej->id} | Egresado: {$ej->egresado_id} | Tel: {$ej->telefono_contacto} | Edad: {$ej->edad}\n";
}
