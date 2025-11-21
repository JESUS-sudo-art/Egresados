<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Modificando la tabla opcion para permitir texto nullable...\n";

try {
    // Para SQLite, necesitamos recrear la tabla
    DB::statement('PRAGMA foreign_keys=off');
    
    // Crear tabla temporal
    DB::statement('CREATE TABLE opcion_temp (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pregunta_id INTEGER NOT NULL,
        texto TEXT NULL,
        valor INTEGER NULL,
        orden INTEGER NULL DEFAULT 0,
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
        actualizado_en DATETIME,
        eliminado_en DATETIME
    )');
    
    // Copiar datos
    DB::statement('INSERT INTO opcion_temp SELECT * FROM opcion');
    
    // Eliminar tabla original
    DB::statement('DROP TABLE opcion');
    
    // Renombrar tabla temporal
    DB::statement('ALTER TABLE opcion_temp RENAME TO opcion');
    
    // Crear índice
    DB::statement('CREATE INDEX opcion_pregunta_id_fk ON opcion(pregunta_id)');
    
    DB::statement('PRAGMA foreign_keys=on');
    
    echo "✓ Tabla modificada exitosamente!\n";
    echo "Ahora el campo 'texto' puede ser NULL.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
