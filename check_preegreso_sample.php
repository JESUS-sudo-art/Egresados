<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$row = DB::table('cedula_preegreso')->whereNotNull('edad')->first();
if ($row) {
    echo json_encode($row, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
} else {
    echo "No hay registros con edad aún\n";
}
