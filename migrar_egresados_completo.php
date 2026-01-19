<?php
/**
 * Script para migrar egresados, carreras y estatus desde BD antigua (bdwvexa)
 * a la BD nueva (egresados_db)
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

// Conexión a BD antigua
$conn_vieja = new mysqli('localhost', 'root', 'root', 'bdwvexa');
if ($conn_vieja->connect_error) {
    die("Error BD antigua: " . $conn_vieja->connect_error);
}
$conn_vieja->set_charset("utf8mb4");

// Conexión a BD nueva
$conn_nueva = new mysqli('localhost', 'root', 'root', 'egresados_db');
if ($conn_nueva->connect_error) {
    die("Error BD nueva: " . $conn_nueva->connect_error);
}
$conn_nueva->set_charset("utf8mb4");

echo "=== MIGRACION DE EGRESADOS ===\n\n";

// 1. Mapear carreras antiguas a nuevas
echo "1. Mapeando carreras...\n";
$carrera_map = array();

$sql = "SELECT DISTINCT c.id as carrera_vieja, c.nombre as carrera_nombre
        FROM bdwvexa.carreras c
        ORDER BY c.nombre";
$result = $conn_vieja->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $nombre = trim(strtoupper($row['carrera_nombre']));
        
        // Buscar carrera en BD nueva
        $sql_new = "SELECT id FROM carrera WHERE UPPER(nombre) LIKE ?";
        $stmt = $conn_nueva->prepare($sql_new);
        $busqueda = "%$nombre%";
        $stmt->bind_param("s", $busqueda);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $carrera_nueva = $res->fetch_assoc();
            $carrera_map[$row['carrera_vieja']] = $carrera_nueva['id'];
            echo "  ✓ {$row['carrera_nombre']} (vieja: {$row['carrera_vieja']} -> nueva: {$carrera_nueva['id']})\n";
        } else {
            echo "  ✗ NO ENCONTRADA: {$row['carrera_nombre']}\n";
            $carrera_map[$row['carrera_vieja']] = null;
        }
        $stmt->close();
    }
}

// 2. Mapear escuelas a unidades
echo "\n2. Mapeando escuelas a unidades...\n";
$escuela_map = array();

$sql = "SELECT DISTINCT id, nombre FROM bdwvexa.escuelas ORDER BY nombre";
$result = $conn_vieja->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $nombre = trim(strtoupper($row['nombre']));
        
        $sql_new = "SELECT id FROM unidad WHERE UPPER(nombre) LIKE ?";
        $stmt = $conn_nueva->prepare($sql_new);
        $busqueda = "%$nombre%";
        $stmt->bind_param("s", $busqueda);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $unidad_nueva = $res->fetch_assoc();
            $escuela_map[$row['id']] = $unidad_nueva['id'];
            echo "  ✓ {$row['nombre']} (vieja: {$row['id']} -> nueva: {$unidad_nueva['id']})\n";
        } else {
            echo "  ✗ NO ENCONTRADA: {$row['nombre']}\n";
            $escuela_map[$row['id']] = 1; // Por defecto unidad 1
        }
        $stmt->close();
    }
}

// 3. Mapear estatus
echo "\n3. Mapeando estatus...\n";
$estatus_map = array(
    'I' => 'INACTIVO',
    'A' => 'ACTIVO',
    'E' => 'EGRESADO'
);

// 4. Migrar egresados
echo "\n4. Migrando egresados y sus carreras...\n";

$sql = "SELECT e.*, a.carreras_id 
        FROM bdwvexa.egresados e 
        LEFT JOIN bdwvexa.academicos a ON e.id = a.egresados_id
        ORDER BY e.id";
$result = $conn_vieja->query($sql);

$total = 0;
$migrados = 0;
$existentes = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $total++;
        
        // Verificar si ya existe por matricula o email
        $sql_check = "SELECT id FROM egresado WHERE (matricula = ? OR email = ?) AND deleted_at IS NULL LIMIT 1";
        $stmt = $conn_nueva->prepare($sql_check);
        $matricula = !empty($row['matricula']) ? $row['matricula'] : null;
        $stmt->bind_param("ss", $matricula, $row['email']);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $existentes++;
            $stmt->close();
            continue;
        }
        $stmt->close();
        
        // Mapear estatus
        $estatus = isset($estatus_map[$row['estatus']]) ? $estatus_map[$row['estatus']] : 'ACTIVO';
        
        // Obtener ID de estatus
        $sql_estatus = "SELECT id FROM cat_estatus WHERE codigo = ? LIMIT 1";
        $stmt = $conn_nueva->prepare($sql_estatus);
        $stmt->bind_param("s", $estatus);
        $stmt->execute();
        $estatus_result = $stmt->get_result();
        $estatus_id = $estatus_result->num_rows > 0 ? $estatus_result->fetch_assoc()['id'] : 1;
        $stmt->close();
        
        // Obtener ID de género
        $genero_id = null;
        if (!empty($row['genero'])) {
            $genero_map = array('M' => 1, 'F' => 2);
            $genero_id = $genero_map[$row['genero']] ?? null;
        }
        
        // Obtener ID de estado civil
        $estado_civil_id = null;
        if (!empty($row['edocivil'])) {
            $estado_civil_map = array('S' => 1, 'C' => 2, 'V' => 3, 'D' => 4);
            $estado_civil_id = $estado_civil_map[$row['edocivil']] ?? null;
        }
        
        // Carrera y unidad
        $carrera_id = !empty($row['carreras_id']) ? ($carrera_map[$row['carreras_id']] ?? 1) : 1;
        $unidad_id = !empty($row['escuelas_id']) ? ($escuela_map[$row['escuelas_id']] ?? 1) : 1;
        
        // Insertar egresado en BD nueva
        $sql_insert = "INSERT INTO egresado 
                      (matricula, nombre, apellidos, genero_id, fecha_nacimiento, 
                       lugar_nacimiento, email, estado_civil_id, estatus_id, 
                       unidad_id, carrera_id, fecha_ingreso, ultimo_ingreso, activo, token)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn_nueva->prepare($sql_insert);
        
        $fecha_nac = ($row['fecnac'] == '0000-00-00') ? null : $row['fecnac'];
        $fecha_ing = $row['fechaingreso'];
        $ultimo_ing = $row['ultimoingreso'];
        $token = !empty($row['token']) ? $row['token'] : md5(time() . rand());
        
        $stmt->bind_param(
            "sssissiiisiisss",
            $matricula,
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
            $fecha_ing,
            $ultimo_ing,
            $row['activo'],
            $token
        );
        
        if ($stmt->execute()) {
            $egresado_id_nuevo = $conn_nueva->insert_id;
            $migrados++;
            
            if ($total % 50 == 0) {
                echo "  Procesados: $total egresados...\n";
            }
            
            $stmt->close();
        } else {
            echo "  ✗ Error al insertar egresado ID {$row['id']}: " . $stmt->error . "\n";
            $stmt->close();
        }
    }
}

echo "\n=== RESULTADOS ===\n";
echo "Total procesados: $total\n";
echo "Migraron: $migrados\n";
echo "Ya existían: $existentes\n";
echo "\n¡Migración completada!\n";

$conn_vieja->close();
$conn_nueva->close();
?>
