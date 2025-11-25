<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ESTRUCTURA DE generacion ===" . PHP_EOL . PHP_EOL;

$columns = DB::select('DESCRIBE generacion');
foreach($columns as $col) {
    echo "{$col->Field} | {$col->Type} | Null: {$col->Null}" . PHP_EOL;
}
