<?php
// Ejecuta migración solo de respuestas leyendo credenciales desde .env
// Uso: php run_respuestas_env.php

date_default_timezone_set('America/Mexico_City');

echo "=== MIGRAR RESPUESTAS (ENV) ===\n";
echo "Fecha: ".date('Y-m-d H:i:s')."\n\n";

function loadEnv($path)
{
    $vars = [];
    if (!is_readable($path)) {
        return $vars;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
            $val = substr($val, 1, -1);
        }
        $vars[$key] = $val;
    }
    return $vars;
}

function envOrDefault($vars, $key, $default)
{
    return isset($vars[$key]) && $vars[$key] !== '' ? $vars[$key] : $default;
}

function pdoConnect($host, $port, $db, $user, $pass)
{
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    return new PDO($dsn, $user, $pass, $options);
}

$env = loadEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env');

$dstHost = envOrDefault($env, 'DB_HOST', '127.0.0.1');
$dstPort = (int) envOrDefault($env, 'DB_PORT', '3306');
$dstDb   = envOrDefault($env, 'DB_DATABASE', 'egresados_db');
$dstUser = envOrDefault($env, 'DB_USERNAME', 'root');
$dstPass = envOrDefault($env, 'DB_PASSWORD', 'root');

$srcHost = envOrDefault($env, 'TEMP_DB_HOST', $dstHost);
$srcPort = (int) envOrDefault($env, 'TEMP_DB_PORT', (string)$dstPort);
$srcDb   = envOrDefault($env, 'TEMP_DB_DATABASE', 'bdwvexa_temp');
$srcUser = envOrDefault($env, 'TEMP_DB_USERNAME', $dstUser);
$srcPass = envOrDefault($env, 'TEMP_DB_PASSWORD', $dstPass);

try {
    $src = pdoConnect($srcHost, $srcPort, $srcDb, $srcUser, $srcPass);
    $dst = pdoConnect($dstHost, $dstPort, $dstDb, $dstUser, $dstPass);
} catch (Throwable $e) {
    echo "ERROR conectando BD: ".$e->getMessage()."\n";
    echo "Origen: {$srcHost}:{$srcPort}/{$srcDb} | Destino: {$dstHost}:{$dstPort}/{$dstDb}\n";
    exit(1);
}

function migrateIntResponses(PDO $src, PDO $dst)
{
    echo "[1/2] Migrando respuestas numéricas...\n";
    $dst->exec('SET FOREIGN_KEY_CHECKS=0');
    $count = 0;

    // Ajusta nombres según tu estructura real
    $sql = "SELECT id, bitencuestas_id, preguntas_id, respuesta FROM intrespuestas";
    $stmt = $src->query($sql);

    $ins = $dst->prepare("INSERT IGNORE INTO respuesta_int (id, bitacora_encuesta_id, pregunta_id, respuesta, created_at, updated_at) VALUES (?,?,?,?,?,?)");

    while ($row = $stmt->fetch()) {
        $bit = $row['bitencuestas_id'] ?? null;
        $preg = $row['preguntas_id'] ?? null;
        $resp = $row['respuesta'] ?? null;
        $created = $row['created_at'] ?? null;
        $updated = $row['updated_at'] ?? null;
        try {
            $ins->execute([
                $row['id'], $bit, $preg, $resp, $created, $updated
            ]);
            $count++;
        } catch (Throwable $e) {
            // Log mínimo; en producción, escribir a archivo
        }
    }
    $dst->exec('SET FOREIGN_KEY_CHECKS=1');
    echo "✓ int: $count registros procesados\n";
}

function migrateTxtResponses(PDO $src, PDO $dst)
{
    echo "[2/2] Migrando respuestas de texto...\n";
    $dst->exec('SET FOREIGN_KEY_CHECKS=0');
    $count = 0;

    $sql = "SELECT id, bitencuestas_id, preguntas_id, respuesta FROM txtrespuestas";
    $stmt = $src->query($sql);

    $ins = $dst->prepare("INSERT IGNORE INTO respuesta_txt (id, bitacora_encuesta_id, pregunta_id, respuesta, created_at, updated_at) VALUES (?,?,?,?,?,?)");

    while ($row = $stmt->fetch()) {
        $bit = $row['bitencuestas_id'] ?? null;
        $preg = $row['preguntas_id'] ?? null;
        $resp = $row['respuesta'] ?? null;
        $created = null;
        $updated = null;
        try {
            $ins->execute([
                $row['id'], $bit, $preg, $resp, $created, $updated
            ]);
            $count++;
        } catch (Throwable $e) {
            // Log mínimo
        }
    }
    $dst->exec('SET FOREIGN_KEY_CHECKS=1');
    echo "✓ txt: $count registros procesados\n";
}

try {
    migrateIntResponses($src, $dst);
    migrateTxtResponses($src, $dst);
    echo "\n✓ Migración de respuestas finalizada.\n";
    exit(0);
} catch (Throwable $e) {
    echo "ERROR migrando respuestas: ".$e->getMessage()."\n";
    exit(1);
}
