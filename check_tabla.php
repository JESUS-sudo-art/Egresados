<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ESTRUCTURA DE egresado_carrera ===" . PHP_EOL . PHP_EOL;

$columns = DB::select('DESCRIBE egresado_carrera');
foreach($columns as $col) {
    echo "{$col->Field} | {$col->Type} | Null: {$col->Null}" . PHP_EOL;
}
