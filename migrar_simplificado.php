#!/usr/bin/env php
<?php

/**
 * Script de Migración - Versión Simplificada
 * Migra solo los datos que están listos
 */

echo "=== MIGRACIÓN SIMPLIFICADA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$dbHost = 'db';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'egresados_db';
$dbNameTemp = 'bdwvexa_temp';

try {
    $pdoOld = new PDO("mysql:host={$dbHost};dbname={$dbNameTemp}", $dbUser, $dbPass);
    $pdoOld->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdoNew = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
    $pdoNew->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Desactivar FKs para permitir carga aun con huérfanos
    $pdoNew->exec('SET FOREIGN_KEY_CHECKS=0');
    
    // === MIGRAR ACADEMICOS (sin timestamps) ===
    echo "[4/10] Migrando relaciones académicas...\n";
    $stmt = $pdoOld->query("
        SELECT id, egresados_id, escuelas_id, carreras_id, generaciones_id
        FROM academicos
        ORDER BY id
    ");
    
    $insertAcad = $pdoNew->prepare("
        INSERT INTO academico (
            id, egresado_id, unidad_id, carrera_id, generacion_id,
            created_at, updated_at
        )
        VALUES (
            :id, :egresado_id, :unidad_id, :carrera_id, :generacion_id,
            NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    
    $count = 0;
    $errors = 0;
    $acadErrors = [];
    while ($acad = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertAcad->execute([
                'id' => $acad['id'],
                'egresado_id' => $acad['egresados_id'],
                'unidad_id' => $acad['escuelas_id'],
                'carrera_id' => $acad['carreras_id'],
                'generacion_id' => $acad['generaciones_id'],
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
            if (count($acadErrors) < 10) {
                $acadErrors[] = $e->getMessage();
            }
        }
        
        if (($count + $errors) % 100 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} relaciones académicas migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n";
    if ($acadErrors) {
        echo "  Ejemplos de error acad: " . implode(' | ', $acadErrors) . "\n";
    }
    echo "\n";
    
    // === MIGRAR BITÁCORAS DE EGRESADO ===
    echo "[5/10] Migrando bitácoras de egresado...\n";
    $stmt = $pdoOld->query("
            SELECT id, egresados_id, fechaini_at, fechafin_in, ip, navegador, estatus
            FROM bitegresados
            ORDER BY id
            LIMIT 50000
        ");
    
    $insertBit = $pdoNew->prepare("
        INSERT INTO bitacora_egresado (
            id, egresado_id, fecha_inicio, fecha_fin, ip, navegador, estatus,
            created_at, updated_at
        )
        VALUES (
            :id, :egresado_id, :fecha_inicio, :fecha_fin, :ip, :navegador, :estatus,
            NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");

    $count = 0;
    $errors = 0;
    $bitErrors = [];
    while ($bit = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertBit->execute([
                'id' => $bit['id'],
                'egresado_id' => $bit['egresados_id'],
                'fecha_inicio' => $bit['fechaini_at'],
                'fecha_fin' => $bit['fechafin_in'],
                'ip' => $bit['ip'],
                'navegador' => $bit['navegador'],
                'estatus' => $bit['estatus'] ?? 'A',
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
            if (count($bitErrors) < 10) {
                $bitErrors[] = $e->getMessage();
            }
        }

        if (($count + $errors) % 1000 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} bitácoras de egresado migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n";
    if ($bitErrors) {
        echo "  Ejemplos de error bitácora: " . implode(' | ', $bitErrors) . "\n";
    }
    echo "\n";

    // === MIGRAR BITÁCORAS DE ENCUESTA ===
    echo "[6/10] Migrando bitácoras de encuesta...\n";
    $stmt = $pdoOld->query("
        SELECT id, egresados_id, ciclos_id, encuestas_id
        FROM bitencuestas
        ORDER BY id
    ");

    $insertBitEnc = $pdoNew->prepare("
        INSERT INTO bitacora_encuesta (
            id, egresado_id, ciclo_id, encuesta_id, fecha_inicio, fecha_fin, completada,
            created_at, updated_at
        )
        VALUES (
            :id, :egresado_id, :ciclo_id, :encuesta_id, :fecha_inicio, :fecha_fin, :completada,
            NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");

    $count = 0;
    $errors = 0;
    $bitEncErrors = [];
    while ($bit = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertBitEnc->execute([
                'id' => $bit['id'],
                'egresado_id' => $bit['egresados_id'],
                'ciclo_id' => $bit['ciclos_id'] == 0 ? null : $bit['ciclos_id'],
                'encuesta_id' => $bit['encuestas_id'],
                'fecha_inicio' => null,
                'fecha_fin' => null,
                'completada' => 'N',
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
            if (count($bitEncErrors) < 10) {
                $bitEncErrors[] = $e->getMessage();
            }
        }

        if (($count + $errors) % 1000 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} bitácoras de encuesta migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n";
    if ($bitEncErrors) {
        echo "  Ejemplos de error bitencuesta: " . implode(' | ', $bitEncErrors) . "\n";
    }
    echo "\n";

    // === MIGRAR RESPUESTAS INT ===
    echo "[7/10] Migrando respuestas numéricas (puede tardar)...\n";
    $stmt = $pdoOld->query("
        SELECT id, bitencuestas_id, preguntas_id, respuesta
        FROM intrespuestas
        ORDER BY id
        LIMIT 150000
    ");
    
    $insertRespInt = $pdoNew->prepare("
        INSERT INTO respuesta_int (
            id, bitacora_encuesta_id, pregunta_id, respuesta,
            created_at, updated_at
        )
        VALUES (
            :id, :bitacora_id, :pregunta_id, :respuesta,
            NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");

    $count = 0;
    $errors = 0;
    $respIntErrors = [];
    while ($resp = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertRespInt->execute([
                'id' => $resp['id'],
                'bitacora_id' => $resp['bitencuestas_id'],
                'pregunta_id' => $resp['preguntas_id'],
                'respuesta' => $resp['respuesta'],
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
            if (count($respIntErrors) < 10) {
                $respIntErrors[] = $e->getMessage();
            }
        }

        if (($count + $errors) % 5000 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} respuestas numéricas migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n";
    if ($respIntErrors) {
        echo "  Ejemplos de error resp_int: " . implode(' | ', $respIntErrors) . "\n";
    }
    echo "\n";

    // === MIGRAR RESPUESTAS TXT ===
    echo "[8/10] Migrando respuestas de texto (puede tardar)...\n";
    $stmt = $pdoOld->query("
        SELECT id, bitencuestas_id, preguntas_id, respuesta
        FROM txtrespuestas
        ORDER BY id
        LIMIT 100000
    ");
    
    $insertRespTxt = $pdoNew->prepare("
        INSERT INTO respuesta_txt (
            id, bitacora_encuesta_id, pregunta_id, respuesta,
            created_at, updated_at
        )
        VALUES (
            :id, :bitacora_id, :pregunta_id, :respuesta,
            NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");

    $count = 0;
    $errors = 0;
    $respTxtErrors = [];
    while ($resp = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertRespTxt->execute([
                'id' => $resp['id'],
                'bitacora_id' => $resp['bitencuestas_id'],
                'pregunta_id' => $resp['preguntas_id'],
                'respuesta' => $resp['respuesta'],
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
            if (count($respTxtErrors) < 10) {
                $respTxtErrors[] = $e->getMessage();
            }
        }

        if (($count + $errors) % 5000 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} respuestas de texto migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n";
    if ($respTxtErrors) {
        echo "  Ejemplos de error resp_txt: " . implode(' | ', $respTxtErrors) . "\n";
    }
    echo "\n";

    // === ESTADÍSTICAS FINALES ===
    echo "[9/10] Estadísticas finales...\n";
    
    $stats = [
        'egresados' => (int)$pdoNew->query("SELECT COUNT(*) FROM egresado")->fetchColumn(),
        'academicos' => (int)$pdoNew->query("SELECT COUNT(*) FROM academico")->fetchColumn(),
        'ciclos' => (int)$pdoNew->query("SELECT COUNT(*) FROM ciclo_escolar")->fetchColumn(),
        'generaciones' => (int)$pdoNew->query("SELECT COUNT(*) FROM generacion")->fetchColumn(),
        'bitacoras' => (int)$pdoNew->query("SELECT COUNT(*) FROM bitacora_egresado")->fetchColumn(),
        'respuestas_int' => (int)$pdoNew->query("SELECT COUNT(*) FROM respuesta_int")->fetchColumn(),
        'respuestas_txt' => (int)$pdoNew->query("SELECT COUNT(*) FROM respuesta_txt")->fetchColumn(),
    ];
    
    foreach ($stats as $tabla => $cantidad) {
        echo "  {$tabla}: {$cantidad} registros\n";
    }
    
    echo "\n[10/10] Limpieza (la base temporal se conserva para revisiones)...\n";
    $pdoNew->exec('SET FOREIGN_KEY_CHECKS=1');
    $pdoOld = null;
    echo "✓ Base temporal conservada\n\n";
    
    echo "=== MIGRACIÓN COMPLETADA ===\n";
    echo "Hora: " . date('Y-m-d H:i:s') . "\n";
    echo "Total de registros: " . array_sum($stats) . "\n\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
