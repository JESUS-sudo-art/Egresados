<?php
/**
 * Script temporal para agregar columna telefono a la tabla egresado
 * IMPORTANTE: Eliminar este archivo después de ejecutarlo
 */

// Cargar configuración de Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Ejecutar SQL directo para agregar columna
    DB::statement("ALTER TABLE egresado ADD COLUMN telefono VARCHAR(20) NULL AFTER email");
    
    echo "✅ Columna 'telefono' agregada exitosamente a la tabla 'egresado'<br><br>";
    echo "IMPORTANTE: Elimina este archivo (agregar_columna_telefono.php) del servidor ahora mismo por seguridad.";
    
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ La columna 'telefono' ya existe en la tabla 'egresado'<br><br>";
        echo "IMPORTANTE: Elimina este archivo (agregar_columna_telefono.php) del servidor ahora mismo por seguridad.";
    } else {
        echo "❌ Error: " . $e->getMessage();
    }
}
