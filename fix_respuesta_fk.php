<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pdo = DB::connection()->getPdo();

echo "=== CORRIGIENDO FOREIGN KEY DE RESPUESTA ===\n\n";

// Deshabilitar foreign keys temporalmente
$pdo->exec('PRAGMA foreign_keys = OFF');

// Obtener la estructura actual
$stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='respuesta'");
$createTableSql = $stmt->fetch(PDO::FETCH_ASSOC)['sql'];

echo "SQL actual:\n$createTableSql\n\n";

// Crear tabla temporal sin el FK de egresado
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

// Copiar datos si existen
$pdo->exec("INSERT INTO respuesta_new SELECT * FROM respuesta");
echo "✅ Datos copiados (si existían)\n";

// Eliminar tabla vieja y renombrar
$pdo->exec("DROP TABLE respuesta");
$pdo->exec("ALTER TABLE respuesta_new RENAME TO respuesta");
echo "✅ Tabla renombrada\n";

// Reactivar foreign keys
$pdo->exec('PRAGMA foreign_keys = ON');
echo "✅ Foreign keys reactivadas\n\n";

echo "=== CORRECCIÓN COMPLETADA ===\n";
echo "Ahora egresado_id apunta correctamente a users(id)\n";
