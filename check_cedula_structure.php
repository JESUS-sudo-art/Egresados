<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ESTRUCTURA cedula_preegreso ===" . PHP_EOL;
$columns = DB::select('DESCRIBE cedula_preegreso');
foreach($columns as $col) {
    echo "{$col->Field} | {$col->Type}" . PHP_EOL;
}
