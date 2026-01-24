<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Estructura de cedula_preegreso ===\n";

$columns = DB::select("
    SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'cedula_preegreso' 
    AND TABLE_SCHEMA = DATABASE()
    ORDER BY ORDINAL_POSITION
");

foreach ($columns as $col) {
    echo sprintf(
        "%-20s %-15s %-10s\n", 
        $col->COLUMN_NAME, 
        $col->DATA_TYPE, 
        $col->IS_NULLABLE === 'YES' ? 'NULLABLE' : 'NOT NULL'
    );
}

echo "\n=== Verificar si existen datos ===\n";
$count = DB::table('cedula_preegreso')->count();
echo "Total de registros: $count\n";

echo "\n=== Mostrar ejemplo de registro ===\n";
$example = DB::table('cedula_preegreso')->first();
if ($example) {
    echo json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
