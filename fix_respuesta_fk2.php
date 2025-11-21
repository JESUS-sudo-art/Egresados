<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CORRIGIENDO FOREIGN KEY DE RESPUESTA ===\n\n";

// Cerrar todas las conexiones
DB::disconnect();

// Reconectar
$pdo = DB::connection()->getPdo();

// Deshabilitar foreign keys
$pdo->exec('PRAGMA foreign_keys = OFF');

try {
    // Crear tabla temporal
    $pdo->exec("DROP TABLE IF EXISTS respuesta_new");
    
    $newTableSql = <<<SQL
CREATE TABLE respuesta_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    egresado_id INTEGER NOT NULL,
    encuesta_id INTEGER NOT NULL,
    pregunta_id INTEGER NOT NULL,
    opcion_id INTEGER,
    respuesta_texto TEXT,
    respuesta_entero INTEGER,
    creado_en datetime,
    FOREIGN KEY (pregunta_id) REFERENCES pregunta(id),
    FOREIGN KEY (opcion_id) REFERENCES opcion(id),
    FOREIGN KEY (encuesta_id) REFERENCES encuesta(id),
    FOREIGN KEY (egresado_id) REFERENCES users(id)
)
SQL;

    $pdo->exec($newTableSql);
    echo "✅ Tabla respuesta_new creada\n";

    // Copiar datos
    $count = $pdo->exec("INSERT INTO respuesta_new SELECT * FROM respuesta");
    echo "✅ Datos copiados: $count registros\n";

    // Eliminar vieja
    $pdo->exec("DROP TABLE respuesta");
    echo "✅ Tabla vieja eliminada\n";

    // Renombrar
    $pdo->exec("ALTER TABLE respuesta_new RENAME TO respuesta");
    echo "✅ Tabla renombrada\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $pdo->exec('PRAGMA foreign_keys = ON');
    exit(1);
}

// Reactivar foreign keys
$pdo->exec('PRAGMA foreign_keys = ON');
echo "✅ Foreign keys reactivadas\n\n";

// Verificar
$stmt = $pdo->query("PRAGMA foreign_key_list(respuesta)");
$fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== VERIFICACIÓN ===\n";
foreach($fks as $fk) {
    echo "Columna: {$fk['from']} -> Tabla: {$fk['table']}.{$fk['to']}\n";
}

echo "\n✅ CORRECCIÓN COMPLETADA\n";
