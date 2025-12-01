<?php

// Conexión directa a MySQL
$host = 'localhost';
$db = 'egresados';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== REVISANDO armando345@gmail.com ===\n\n";
    
    // Buscar usuario
    $stmt = $pdo->query("SELECT id, email, name FROM users WHERE email = 'armando345@gmail.com'");
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "✓ Usuario:\n";
        echo "  ID: {$usuario['id']}\n";
        echo "  Email: {$usuario['email']}\n";
        echo "  Name: {$usuario['name']}\n\n";
    } else {
        echo "✗ Usuario no encontrado\n";
        exit(1);
    }
    
    // Buscar egresado
    $stmt = $pdo->query("SELECT id, email, nombre, apellido_paterno, usuario_id FROM egresado WHERE email = 'armando345@gmail.com'");
    $egresado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$egresado) {
        $stmt = $pdo->prepare("SELECT id, email, nombre, apellido_paterno, usuario_id FROM egresado WHERE usuario_id = ?");
        $stmt->execute([$usuario['id']]);
        $egresado = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if ($egresado) {
        echo "✓ Egresado:\n";
        echo "  ID: {$egresado['id']}\n";
        echo "  Email: {$egresado['email']}\n";
        echo "  Nombre: {$egresado['nombre']} {$egresado['apellido_paterno']}\n";
        echo "  Usuario ID: {$egresado['usuario_id']}\n\n";
    } else {
        echo "✗ Egresado no encontrado\n";
        exit(1);
    }
    
    // Buscar respuestas
    echo "=== RESPUESTAS ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, encuesta_id FROM respuesta WHERE egresado_id = ? GROUP BY encuesta_id");
    $stmt->execute([$egresado['id']]);
    $respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($respuestas) > 0) {
        echo "Respuestas con egresado_id={$egresado['id']}:\n";
        foreach ($respuestas as $r) {
            echo "  - Encuesta {$r['encuesta_id']}: {$r['total']} respuestas\n";
        }
    } else {
        echo "No hay respuestas con egresado_id={$egresado['id']}\n";
    }
    
    echo "\n";
    
    // Verificar con user_id
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, encuesta_id FROM respuesta WHERE egresado_id = ? GROUP BY encuesta_id");
    $stmt->execute([$usuario['id']]);
    $respuestasUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($respuestasUser) > 0) {
        echo "⚠️ Respuestas con egresado_id={$usuario['id']} (user_id - INCORRECTO):\n";
        foreach ($respuestasUser as $r) {
            echo "  - Encuesta {$r['encuesta_id']}: {$r['total']} respuestas\n";
        }
    }
    
    echo "\n=== ENCUESTAS ASIGNADAS ===\n";
    $stmt = $pdo->prepare("SELECT encuesta_id FROM encuesta_asignada WHERE egresado_id = ?");
    $stmt->execute([$egresado['id']]);
    $asignadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($asignadas as $enc) {
        $encuestaId = $enc['encuesta_id'];
        
        // Verificar si respondió
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM respuesta WHERE encuesta_id = ? AND egresado_id = ?");
        $stmt->execute([$encuestaId, $egresado['id']]);
        $check = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Encuesta ID {$encuestaId}: " . ($check['total'] > 0 ? "✓ RESPONDIDA ({$check['total']} respuestas)" : "✗ NO RESPONDIDA") . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
