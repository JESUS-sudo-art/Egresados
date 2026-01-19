<?php
/**
 * Migración directa de egresados usando PDO
 * Ejecutar: docker exec -it egresados-php php /tmp/migrate_egresados.php
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

try {
    // Crear conexiones usando PDO
    $conn_vieja = new PDO(
        'mysql:host=egresados-db;dbname=bdwvexa;charset=utf8mb4',
        'root',
        'root'
    );
    $conn_vieja->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $conn_nueva = new PDO(
        'mysql:host=egresados-db;dbname=egresados_db;charset=utf8mb4',
        'root',
        'root'
    );
    $conn_nueva->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

echo "=== MIGRACION DE EGRESADOS ===\n\n";

// 1. Obtener mapeo de carreras
echo "1. Mapeando carreras...\n";
$carrera_map = array();

$sql = "SELECT id, nombre FROM carreras ORDER BY nombre";
$result = $conn_vieja->query($sql);

foreach ($result as $row) {
    $nombre = trim($row['nombre']);
    
    // Buscar carrera en BD nueva (match flexible)
    $search_name = "%{$nombre}%";
    $sql_new = "SELECT id FROM carrera WHERE UPPER(nombre) LIKE UPPER(?) AND deleted_at IS NULL LIMIT 1";
    $stmt = $conn_nueva->prepare($sql_new);
    $stmt->execute([$search_name]);
    $nueva = $stmt->fetch();
    
    if ($nueva) {
        $carrera_map[$row['id']] = $nueva['id'];
        echo "  ✓ {$nombre} (vieja: {$row['id']} -> nueva: {$nueva['id']})\n";
    } else {
        // Default a primera carrera
        $sql_first = "SELECT id FROM carrera WHERE deleted_at IS NULL LIMIT 1";
        $res_first = $conn_nueva->query($sql_first)->fetch();
        if ($res_first) {
            $carrera_map[$row['id']] = $res_first['id'];
        } else {
            $carrera_map[$row['id']] = 1;
        }
        echo "  ✗ NO ENCONTRADA: {$nombre} (usando ID {$carrera_map[$row['id']]})\n";
    }
}

// 2. Obtener mapeo de escuelas/unidades
echo "\n2. Mapeando escuelas...\n";
$escuela_map = array();

$sql = "SELECT id, nombre FROM escuelas ORDER BY nombre";
$result = $conn_vieja->query($sql);

foreach ($result as $row) {
    $nombre = trim($row['nombre']);
    $search_name = "%{$nombre}%";
    
    $sql_new = "SELECT id FROM unidad WHERE UPPER(nombre) LIKE UPPER(?) AND deleted_at IS NULL LIMIT 1";
    $stmt = $conn_nueva->prepare($sql_new);
    $stmt->execute([$search_name]);
    $nueva = $stmt->fetch();
    
    if ($nueva) {
        $escuela_map[$row['id']] = $nueva['id'];
        echo "  ✓ {$nombre} (vieja: {$row['id']} -> nueva: {$nueva['id']})\n";
    } else {
        // Default a primera unidad
        $sql_first = "SELECT id FROM unidad WHERE deleted_at IS NULL LIMIT 1";
        $res_first = $conn_nueva->query($sql_first)->fetch();
        if ($res_first) {
            $escuela_map[$row['id']] = $res_first['id'];
        } else {
            $escuela_map[$row['id']] = 1;
        }
        echo "  ✗ NO ENCONTRADA: {$nombre} (usando ID {$escuela_map[$row['id']]})\n";
    }
}

// 3. Obtener mapeo de estatus
echo "\n3. Obteniendo estatus de BD nueva...\n";
$estatus_map = array();
$estatus_defecto = 2; // Activo

$sql = "SELECT id, nombre FROM cat_estatus";
$result = $conn_nueva->query($sql);

foreach ($result as $row) {
    $estatus_map[strtoupper(substr($row['nombre'], 0, 1))] = $row['id'];
    echo "  - {$row['nombre']} (ID: {$row['id']})\n";
    if ($row['nombre'] == 'Activo') {
        $estatus_defecto = $row['id'];
    }
}

// 4. Migrar egresados
echo "\n4. Migrando egresados...\n";

$sql = "SELECT * FROM egresados ORDER BY id";
$result = $conn_vieja->query($sql);

$total = 0;
$insertados = 0;
$actualizados = 0;
$errores = 0;

foreach ($result as $row) {
    $total++;
    
    // Validar antes de insertar
    if (empty($row['email'])) {
        echo "  ✗ Egresado {$row['id']} sin email - SALTADO\n";
        $errores++;
        continue;
    }
    
    // Verificar si existe
    $sql_check = "SELECT id FROM egresado WHERE (matricula = ? OR email = ?) AND deleted_at IS NULL LIMIT 1";
    $stmt = $conn_nueva->prepare($sql_check);
    $matricula = $row['matricula'] ?? null;
    $stmt->execute([$matricula, $row['email']]);
    
    if ($stmt->fetch()) {
        $actualizados++;
        continue;
    }
    
    // Preparar datos
    $genero_id = null;
    if ($row['genero'] == 'M') $genero_id = 1;
    elseif ($row['genero'] == 'F') $genero_id = 2;
    
    $estado_civil_id = null;
    if ($row['edocivil'] == 'S') $estado_civil_id = 1;
    elseif ($row['edocivil'] == 'C') $estado_civil_id = 2;
    elseif ($row['edocivil'] == 'V') $estado_civil_id = 3;
    elseif ($row['edocivil'] == 'D') $estado_civil_id = 4;
    
    $fecha_nac = null;
    if ($row['fecnac'] && $row['fecnac'] != '0000-00-00') {
        // Validar que sea una fecha válida
        $fecha_parts = explode('-', $row['fecnac']);
        if (count($fecha_parts) == 3 && $fecha_parts[2] > 0) {
            $fecha_nac = $row['fecnac'];
        }
    }
    
    $estatus_id = $estatus_map[strtoupper($row['estatus'])] ?? $estatus_defecto;
    $carrera_id = $carrera_map[$row['carreras_id']] ?? 1;
    $unidad_id = $escuela_map[$row['escuelas_id']] ?? 1;
    $token = $row['token'] ?? md5($row['id'] . time());
    
    // Insertar
    $sql_insert = "INSERT INTO egresado 
                  (matricula, nombre, apellidos, genero_id, fecha_nacimiento, 
                   lugar_nacimiento, email, estado_civil_id, estatus_id, 
                   unidad_id, carrera_id, fecha_ingreso, ultimo_ingreso, activo, token, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn_nueva->prepare($sql_insert);
    
    try {
        $stmt->execute([
            $row['matricula'],
            $row['nombre'],
            $row['apellidos'],
            $genero_id,
            $fecha_nac,
            $row['lugarnac'],
            $row['email'],
            $estado_civil_id,
            $estatus_id,
            $unidad_id,
            $carrera_id,
            $row['fechaingreso'],
            $row['ultimoingreso'],
            $row['activo'],
            $token
        ]);
        
        $insertados++;
        if ($total % 100 == 0) {
            echo "  Procesados: {$total} egresados...\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error al insertar {$row['nombre']}: " . $e->getMessage() . "\n";
        $errores++;
    }
}

echo "\n=== RESULTADOS ===\n";
echo "Total procesados: $total\n";
echo "Insertados nuevos: $insertados\n";
echo "Ya existían: $actualizados\n";
echo "Errores: $errores\n\n";

// Verificación final
$sql_count = "SELECT COUNT(*) as total FROM egresado WHERE deleted_at IS NULL";
$result = $conn_nueva->query($sql_count);
$count_row = $result->fetch();
echo "Total egresados en BD nueva: " . $count_row['total'] . "\n\n";

$conn_vieja = null;
$conn_nueva = null;

echo "✓ ¡Migración completada!\n";
?>
