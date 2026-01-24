<?php
/**
 * Script simple para agregar columna telefono
 * ELIMINAR después de usar
 */

// Configuración de la base de datos (ajusta estos valores si son diferentes)
$host = '69.6.201.239';
$dbname = 'dadfabfa_egr3sa2';
$username = 'dadfabfa_egr3sa2';
$password = '*3tV$+i0{%2k9tA';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM egresado LIKE 'telefono'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "ℹ️ La columna 'telefono' ya existe en la tabla 'egresado'<br><br>";
    } else {
        // Agregar la columna
        $pdo->exec("ALTER TABLE egresado ADD COLUMN telefono VARCHAR(20) NULL AFTER email");
        echo "✅ Columna 'telefono' agregada exitosamente a la tabla 'egresado'<br><br>";
    }
    
    echo "<strong style='color: red;'>IMPORTANTE: Elimina este archivo (agregar_telefono_simple.php) del servidor AHORA por seguridad.</strong>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
