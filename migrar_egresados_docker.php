<?php
/**
 * Script para migrar egresados, carreras y estatus desde BD antigua (bdwvexa)
 * a la BD nueva (egresados_db)
 * Se conecta al contenedor Docker
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

// Función para ejecutar comandos en Docker
function exec_mysql($db, $query) {
    $query = escapeshellarg($query);
    $cmd = "docker exec egresados-db mysql -u root -proot $db -e $query";
    $output = shell_exec($cmd . " 2>&1");
    return $output;
}

echo "=== MIGRACION DE EGRESADOS ===\n\n";

// 1. Obtener mapeo de carreras
echo "1. Obteniendo carreras antiguas...\n";
$sql = "SELECT id, nombre FROM carreras ORDER BY nombre";
$output = exec_mysql("bdwvexa", $sql);
$carreras_antiguas = array();
$carrera_map = array();

// Parsear output
$lines = explode("\n", $output);
foreach ($lines as $line) {
    if (preg_match('/(\d+)\s+(.+)/', $line, $matches)) {
        $id_vieja = $matches[1];
        $nombre_vieja = trim($matches[2]);
        $carreras_antiguas[$id_vieja] = $nombre_vieja;
        echo "  Encontrada: $nombre_vieja (ID vieja: $id_vieja)\n";
    }
}

// 2. Mapear con carreras nuevas
echo "\n2. Mapeando a carreras nuevas...\n";
$sql = "SELECT id, nombre FROM carrera WHERE deleted_at IS NULL ORDER BY nombre";
$output = exec_mysql("egresados_db", $sql);
$lines = explode("\n", $output);

foreach ($carreras_antiguas as $id_vieja => $nombre_vieja) {
    // Buscar coincidencia
    foreach ($lines as $line) {
        if (preg_match('/(\d+)\s+(.+)/', $line, $matches)) {
            $id_nueva = $matches[1];
            $nombre_nueva = trim($matches[2]);
            if (stripos($nombre_nueva, substr($nombre_vieja, 0, 15)) !== false) {
                $carrera_map[$id_vieja] = $id_nueva;
                echo "  ✓ Mapeada: $nombre_vieja -> $nombre_nueva\n";
                break;
            }
        }
    }
    if (!isset($carrera_map[$id_vieja])) {
        echo "  ✗ No mapeada: $nombre_vieja\n";
        $carrera_map[$id_vieja] = 1; // Por defecto
    }
}

// 3. Obtener egresados antiguos
echo "\n3. Obteniendo egresados antiguos...\n";
$sql = "SELECT e.id, e.matricula, e.nombre, e.apellidos, e.email, e.genero, e.fecnac, 
        e.lugarnac, e.edocivil, e.estatus, e.carreras_id, e.escuelas_id, 
        e.fechaingreso, e.ultimoingreso, e.token
        FROM egresados e 
        ORDER BY e.id";

$output = exec_mysql("bdwvexa", $sql);
echo $output;

echo "\n=== Nota: ===\n";
echo "El script debe ejecutarse en el contenedor con Laravel artisan.\n";
echo "Usa: php artisan migrate:egresados --desde-bdwvexa\n\n";

?>
