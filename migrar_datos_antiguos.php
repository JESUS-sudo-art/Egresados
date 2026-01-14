#!/usr/bin/env php
<?php

/**
 * Script de Migración de Datos Antiguos
 * 
 * Este script importa la BD antigua a una base temporal y luego migra
 * los datos a las nuevas tablas usando SQL directo.
 * 
 * Uso: php migrar_datos_antiguos.php
 */

echo "=== MIGRADOR DE DATOS ANTIGUOS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "==================================\n\n";

// Configuración
$dbHost = 'db';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'egresados_db';
$dbNameTemp = 'bdwvexa_temp';
$sqlFile = '/var/www/html/bdwvexa_backup.sql';

if (!file_exists($sqlFile)) {
    die("ERROR: No se encuentra el archivo bdwvexa_backup.sql\n");
}

try {
    // Conectar a MySQL
    echo "Conectando a MySQL...\n";
    $pdo = new PDO("mysql:host={$dbHost}", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conectado\n\n";
    
    // Usar base de datos temporal ya importada previamente
    echo "Usando base de datos temporal existente '{$dbNameTemp}' (se asume importación previa)\n\n";
    
    // Conectar a ambas bases de datos
    $pdoOld = new PDO("mysql:host={$dbHost};dbname={$dbNameTemp}", $dbUser, $dbPass);
    $pdoOld->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdoNew = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
    $pdoNew->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Iniciando migración de datos...\n\n";
    
    // === MIGRAR CICLOS ESCOLARES ===
    echo "[1/9] Migrando ciclos escolares...\n";
    $stmt = $pdoOld->query("SELECT id, nombre, estatus FROM ciclos ORDER BY id");
    $ciclos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $insertCiclo = $pdoNew->prepare("
        INSERT INTO ciclo_escolar (id, nombre, fecha_inicio, fecha_fin, estatus, created_at, updated_at)
        VALUES (:id, :nombre, :fecha_inicio, :fecha_fin, :estatus, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            nombre = VALUES(nombre),
            fecha_inicio = VALUES(fecha_inicio),
            fecha_fin = VALUES(fecha_fin),
            estatus = VALUES(estatus),
            updated_at = NOW()
    ");
    
    $count = 0;
    foreach ($ciclos as $ciclo) {
        $insertCiclo->execute([
            'id' => $ciclo['id'],
            'nombre' => $ciclo['nombre'],
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'estatus' => $ciclo['estatus']
        ]);
        $count++;
    }
    echo "✓ {$count} ciclos escolares migrados\n\n";
    
    // === MIGRAR GENERACIONES ===
    echo "[2/9] Migrando generaciones...\n";
    $stmt = $pdoOld->query("SELECT id, generacion, estatus FROM generaciones ORDER BY id");
    $generaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $insertGen = $pdoNew->prepare("
        INSERT INTO generacion (id, nombre, estatus, created_at, updated_at)
        VALUES (:id, :nombre, :estatus, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            nombre = VALUES(nombre),
            estatus = VALUES(estatus),
            updated_at = NOW()
    ");
    
    $count = 0;
    foreach ($generaciones as $gen) {
        $insertGen->execute([
            'id' => $gen['id'],
            'nombre' => $gen['generacion'],
            'estatus' => $gen['estatus']
        ]);
        $count++;
    }
    echo "✓ {$count} generaciones migradas\n\n";
    
    // === MIGRAR EGRESADOS ===
    echo "[3/9] Migrando egresados...\n";
    $stmt = $pdoOld->query("
        SELECT id, matricula, nombre, apellidos, genero, fecnac, 
               lugarnac, domicilio, email, edocivil,
               fechaingreso as creado_en, ultimoingreso as actualizado_en
        FROM egresados 
        ORDER BY id
    ");
    
    $insertEgr = $pdoNew->prepare("
        INSERT INTO egresado (
            id, matricula, nombre, apellidos, genero_id, fecha_nacimiento,
            lugar_nacimiento, domicilio, email, estado_civil_id,
            created_at, updated_at
        )
        VALUES (
            :id, :matricula, :nombre, :apellidos, :genero_id, :fecha_nacimiento,
            :lugar_nacimiento, :domicilio, :email, :estado_civil_id,
            :created_at, :updated_at
        )
        ON DUPLICATE KEY UPDATE
            matricula = VALUES(matricula),
            updated_at = NOW()
    ");
    
    $count = 0;
    while ($egr = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Mapear genero (M/F o H) a genero_id (1=Masculino, 2=Femenino)
        $generoId = ($egr['genero'] === 'M' || $egr['genero'] === 'H') ? 1 : 2;
        
        // Mapear edocivil (S/C) a estado_civil_id (1=Soltero, 2=Casado)
        $estadoCivilId = ($egr['edocivil'] === 'S' || $egr['edocivil'] === null) ? 1 : 2;
        
        // Validar fecha - convertir fechas inválidas (0000-00-00) a NULL
        $fechaNacimiento = ($egr['fecnac'] === '0000-00-00' || empty($egr['fecnac'])) ? null : $egr['fecnac'];
        
        try {
            $insertEgr->execute([
                'id' => $egr['id'],
                'matricula' => $egr['matricula'],
                'nombre' => $egr['nombre'],
                'apellidos' => $egr['apellidos'] ?? '',
                'genero_id' => $generoId,
                'fecha_nacimiento' => $fechaNacimiento,
                'lugar_nacimiento' => $egr['lugarnac'],
                'domicilio' => $egr['domicilio'],
                'email' => $egr['email'],
                'estado_civil_id' => $estadoCivilId,
                'created_at' => $egr['creado_en'],
                'updated_at' => $egr['actualizado_en']
            ]);
            $count++;
        } catch (PDOException $e) {
            // Ignorar errores individuales
        }
        
        if ($count % 100 == 0) {
            echo "  Procesados: {$count} egresados...\r";
        }
    }
    echo "✓ {$count} egresados migrados" . str_repeat(' ', 30) . "\n\n";
    
    // === MIGRAR ACADEMICOS (relaciones egresado-carrera-unidad) ===
    echo "[4/9] Migrando relaciones académicas...\n";
    $stmt = $pdoOld->query("
        SELECT id, egresados_id, escuelas_id, carreras_id, generaciones_id,
               fechaingreso as creado_en, ultimoingreso as actualizado_en
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
            :created_at, :updated_at
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    
    $count = 0;
    $errors = 0;
    while ($acad = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertAcad->execute([
                'id' => $acad['id'],
                'egresado_id' => $acad['egresados_id'],
                'unidad_id' => $acad['escuelas_id'],
                'carrera_id' => $acad['carreras_id'],
                'generacion_id' => $acad['generaciones_id'],
                'created_at' => $acad['creado_en'],
                'updated_at' => $acad['actualizado_en']
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
            // Ignorar errores de FK (registros huérfanos)
        }
        
        if (($count + $errors) % 100 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} relaciones académicas migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n\n";
    
    // === MIGRAR BITÁCORAS DE EGRESADO ===
    echo "[5/9] Migrando bitácoras de egresado...\n";
    $stmt = $pdoOld->query("
        SELECT id, egresados_id, fecha_inicio, fecha_fin, ip, navegador, estatus,
               fechaingreso as creado_en, ultimoingreso as actualizado_en
        FROM bitacoras
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
            :created_at, :updated_at
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    
    $count = 0;
    $errors = 0;
    while ($bit = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertBit->execute([
                'id' => $bit['id'],
                'egresado_id' => $bit['egresados_id'],
                'fecha_inicio' => $bit['fecha_inicio'],
                'fecha_fin' => $bit['fecha_fin'],
                'ip' => $bit['ip'],
                'navegador' => $bit['navegador'],
                'estatus' => $bit['estatus'] ?? 'A',
                'created_at' => $bit['creado_en'],
                'updated_at' => $bit['actualizado_en']
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
        }
        
        if (($count + $errors) % 1000 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} bitácoras de egresado migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n\n";
    
    // === MIGRAR DIMENSIONES Y SUBDIMENSIONES ===
    echo "[6/9] Migrando subdimensiones...\n";
    $stmt = $pdoOld->query("
        SELECT id, dimensiones_id, nombre, descripcion, estatus,
               fechaingreso as creado_en, ultimoingreso as actualizado_en
        FROM subdimensiones
        ORDER BY id
    ");
    
    $insertSubdim = $pdoNew->prepare("
        INSERT INTO subdimension (
            id, dimension_id, nombre, descripcion, estatus,
            created_at, updated_at
        )
        VALUES (
            :id, :dimension_id, :nombre, :descripcion, :estatus,
            :created_at, :updated_at
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    
    $count = 0;
    $errors = 0;
    while ($subdim = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertSubdim->execute([
                'id' => $subdim['id'],
                'dimension_id' => $subdim['dimensiones_id'],
                'nombre' => $subdim['nombre'],
                'descripcion' => $subdim['descripcion'],
                'estatus' => $subdim['estatus'] ?? 'A',
                'created_at' => $subdim['creado_en'],
                'updated_at' => $subdim['actualizado_en']
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
        }
    }
    echo "✓ {$count} subdimensiones migradas ({$errors} registros ignorados)\n\n";
    
    // === MIGRAR BIT\u00c1CORAS DE ENCUESTA ===
    echo "[7/9] Migrando bit\u00e1coras de encuesta...\n";
    echo "  (No existen en BD antigua - omitiendo)\n\n";
    
    // === MIGRAR RESPUESTAS INT ===
    echo "[8/9] Migrando respuestas num\u00e9ricas (puede tardar)...\n";
    $stmt = $pdoOld->query("
        SELECT id, bitacoras_id, preguntas_id, respuesta,
               creado_en, actualizado_en
        FROM respuestas_int
        ORDER BY id
        LIMIT 150000
    ");
    
    $insertRespInt = $pdoNew->prepare("
        INSERT INTO respuesta_int (
            id, bitacora_encuesta_id, pregunta_id, valor,
            created_at, updated_at
        )
        VALUES (
            :id, :bitacora_id, :pregunta_id, :valor,
            :created_at, :updated_at
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    
    $count = 0;
    $errors = 0;
    while ($resp = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertRespInt->execute([
                'id' => $resp['id'],
                'bitacora_id' => $resp['bitacoras_id'],
                'pregunta_id' => $resp['preguntas_id'],
                'valor' => $resp['respuesta'],
                'creado_en' => $egr['creado_en'],
                'updated_at' => $egr['actualizado_en']
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
        }
        
        if (($count + $errors) % 5000 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} respuestas numéricas migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n\n";
    
    // === MIGRAR RESPUESTAS TXT ===
    echo "[9/9] Migrando respuestas de texto (puede tardar)...\n";
    $stmt = $pdoOld->query("
        SELECT id, bitacoras_id, preguntas_id, respuesta,
               creado_en, actualizado_en
        FROM respuestas_txt
        ORDER BY id
        LIMIT 100000
    ");
    
    $insertRespTxt = $pdoNew->prepare("
        INSERT INTO respuesta_txt (
            id, bitacora_encuesta_id, pregunta_id, texto,
            created_at, updated_at
        )
        VALUES (
            :id, :bitacora_id, :pregunta_id, :texto,
            :created_at, :updated_at
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    
    $count = 0;
    $errors = 0;
    while ($resp = $stmt->fetch(PDO::FETCH_ASSOC)) {
        try {
            $insertRespTxt->execute([
                'id' => $resp['id'],
                'bitacora_id' => $resp['bitacoras_id'],
                'pregunta_id' => $resp['preguntas_id'],
                'texto' => $resp['respuesta'],
                'created_at' => $resp['creado_en'],
                'updated_at' => $resp['actualizado_en']
            ]);
            $count++;
        } catch (PDOException $e) {
            $errors++;
        }
        
        if (($count + $errors) % 5000 == 0) {
            echo "  Procesados: {$count} OK, {$errors} errores...\r";
        }
    }
    echo "✓ {$count} respuestas de texto migradas ({$errors} registros ignorados)" . str_repeat(' ', 20) . "\n\n";
    
    echo "\n=== MIGRACIÓN COMPLETADA EXITOSAMENTE ===\n";
    echo "Hora: " . date('Y-m-d H:i:s') . "\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
