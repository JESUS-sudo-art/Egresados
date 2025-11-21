<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Verificar estructura de la tabla respuesta
$pdo = DB::connection()->getPdo();
$stmt = $pdo->query("PRAGMA table_info(respuesta)");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== ESTRUCTURA TABLA RESPUESTA ===\n\n";
foreach($columns as $col) {
    echo "{$col['name']} - {$col['type']} - Nullable: " . ($col['notnull'] == 0 ? 'YES' : 'NO') . "\n";
}

// Verificar foreign keys
echo "\n=== FOREIGN KEYS ===\n\n";
$stmt = $pdo->query("PRAGMA foreign_key_list(respuesta)");
$fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($fks as $fk) {
    echo "Columna: {$fk['from']} -> Tabla: {$fk['table']}.{$fk['to']}\n";
}

// Verificar que las opciones existen
echo "\n=== OPCIONES DE LA ENCUESTA 6 ===\n\n";
$opciones = App\Models\Opcion::whereHas('pregunta', function($q) {
    $q->where('encuesta_id', 6);
})->get();

foreach($opciones as $opc) {
    echo "Opcion ID: {$opc->id} - Pregunta ID: {$opc->pregunta_id} - Texto: {$opc->texto}\n";
}
