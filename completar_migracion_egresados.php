<?php
/**
 * Script para completar la migración de datos de egresados
 * Actualiza: matricula, estatus_id, carrera_id, unidad_id, generacion_id, anio_egreso
 * desde la base de datos antigua (bdwvexa_temp.egresados)
 */

echo "=== Completando Migración de Datos de Egresados ===\n\n";

try {
    // Conectar directamente con PDO
    $host = getenv('DB_HOST') ?: 'egresados-db';
    $dbname = getenv('DB_DATABASE') ?: 'egresados_db';
    $user = getenv('DB_USERNAME') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: 'root';
    
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
    
    echo "✓ Conectado al servidor MySQL\n";
    
    // Verificar si existe bdwvexa_temp
    $stmt = $pdo->query("SHOW DATABASES LIKE 'bdwvexa_temp'");
    if ($stmt->rowCount() == 0) {
        die("✗ Base de datos bdwvexa_temp no existe. Ejecuta primero la importación.\n");
    }
    echo "✓ Base de datos bdwvexa_temp encontrada\n";
    
    // Verificar si existe egresados_db
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() == 0) {
        die("✗ Base de datos $dbname no existe.\n");
    }
    echo "✓ Base de datos $dbname encontrada\n\n";
    
    // Desactivar foreign key checks temporalmente
    echo "⚠ Desactivando verificación de foreign keys...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    echo "✓ Foreign key checks desactivados\n\n";
    
    // Paso 1: Actualizar datos básicos de egresados (matricula, estatus, fechas)
    echo "PASO 1: Actualizando datos básicos de egresados...\n";
    
    $sqlUpdateBasicos = "
        UPDATE $dbname.egresado e
        INNER JOIN bdwvexa_temp.egresados old ON e.id = old.id
        SET 
            e.matricula = CAST(old.matricula AS CHAR),
            e.curp = old.clave,
            e.estatus_id = CASE 
                WHEN old.estatus = 'A' THEN 2  -- Activo
                WHEN old.estatus = 'I' THEN 3  -- Inactivo
                WHEN old.estatus = 'E' THEN 1  -- Egresado
                ELSE NULL
            END,
            e.fecha_ingreso = old.fechaingreso,
            e.ultimo_ingreso = old.ultimoingreso,
            e.activo = old.activo,
            e.extension = old.extension
    ";
    
    $updated = $pdo->exec($sqlUpdateBasicos);
    echo "✓ {$updated} egresados actualizados con datos básicos\n\n";
    
    // Paso 2: Actualizar carrera_id y unidad_id desde la tabla egresados antigua
    echo "PASO 2: Actualizando carrera y unidad desde egresados antiguos...\n";
    
    $sqlUpdateCarreraUnidad = "
        UPDATE $dbname.egresado e
        INNER JOIN bdwvexa_temp.egresados old ON e.id = old.id
        SET 
            e.carrera_id = old.carreras_id,
            e.unidad_id = old.escuelas_id
        WHERE old.carreras_id IS NOT NULL 
        AND old.escuelas_id IS NOT NULL
    ";
    
    $updated2 = $pdo->exec($sqlUpdateCarreraUnidad);
    echo "✓ {$updated2} egresados actualizados con carrera y unidad\n\n";
    
    // Paso 3: Actualizar/Crear registros en tabla academico
    echo "PASO 3: Sincronizando tabla academico...\n";
    
    // Primero, actualizar academicos existentes
    $sqlUpdateAcademico = "
        UPDATE $dbname.academico a
        INNER JOIN bdwvexa_temp.academicos old ON a.egresado_id = old.egresados_id
        SET 
            a.unidad_id = old.escuelas_id,
            a.carrera_id = old.carreras_id,
            a.generacion_id = old.generaciones_id
        WHERE old.escuelas_id IS NOT NULL
    ";
    
    $updatedAcad = $pdo->exec($sqlUpdateAcademico);
    echo "✓ {$updatedAcad} registros académicos actualizados\n";
    
    // Insertar academicos faltantes (los que están en egresados pero no tienen registro en academicos)
    $sqlInsertAcademico = "
        INSERT IGNORE INTO $dbname.academico 
            (egresado_id, unidad_id, carrera_id, generacion_id, created_at, updated_at)
        SELECT 
            old.id,
            old.escuelas_id,
            old.carreras_id,
            old.generaciones_id,
            NOW(),
            NOW()
        FROM bdwvexa_temp.egresados old
        LEFT JOIN $dbname.academico a ON a.egresado_id = old.id
        WHERE a.id IS NULL
        AND old.escuelas_id IS NOT NULL
        AND old.carreras_id IS NOT NULL
        AND old.generaciones_id IS NOT NULL
    ";
    
    $pdo->exec($sqlInsertAcademico);
    echo "✓ Registros académicos insertados (si faltaban)\n\n";
    
    // Paso 4: Calcular año de egreso desde generaciones
    echo "PASO 4: Calculando año de egreso...\n";
    
    $sqlUpdateAnioEgreso = "
        UPDATE $dbname.egresado e
        INNER JOIN bdwvexa_temp.egresados old ON e.id = old.id
        INNER JOIN bdwvexa_temp.generaciones g ON old.generaciones_id = g.id
        SET e.anio_egreso = g.generacion
        WHERE g.generacion IS NOT NULL
    ";
    
    $updatedAnio = $pdo->exec($sqlUpdateAnioEgreso);
    echo "✓ {$updatedAnio} egresados actualizados con año de egreso\n\n";
    
    // Reactivar foreign key checks
    echo "⚠ Reactivando verificación de foreign keys...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "✓ Foreign key checks reactivados\n\n";
    
    // Estadísticas finales
    echo "=== ESTADÍSTICAS FINALES ===\n\n";
    
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(matricula) as con_matricula,
            COUNT(curp) as con_curp,
            COUNT(estatus_id) as con_estatus,
            COUNT(carrera_id) as con_carrera,
            COUNT(unidad_id) as con_unidad,
            COUNT(anio_egreso) as con_anio_egreso
        FROM $dbname.egresado
    ")->fetch();
    
    echo "Total de egresados: {$stats->total}\n";
    echo "Con matrícula: {$stats->con_matricula}\n";
    echo "Con CURP: {$stats->con_curp}\n";
    echo "Con estatus: {$stats->con_estatus}\n";
    echo "Con carrera: {$stats->con_carrera}\n";
    echo "Con unidad: {$stats->con_unidad}\n";
    echo "Con año de egreso: {$stats->con_anio_egreso}\n\n";
    
    $statsAcad = $pdo->query("SELECT COUNT(*) as total FROM $dbname.academico")->fetch();
    echo "Total de registros académicos: {$statsAcad->total}\n\n";
    
    // Verificar si hay catálogo de estatus
    echo "=== VERIFICACIÓN DE CATÁLOGOS ===\n\n";
    
    $estatusExiste = $pdo->query("
        SELECT COUNT(*) as existe 
        FROM information_schema.tables 
        WHERE table_schema = '$dbname' 
        AND table_name = 'estatus_estudiante'
    ")->fetch();
    
    if ($estatusExiste->existe == 0) {
        echo "⚠ ADVERTENCIA: No existe tabla 'estatus_estudiante'\n";
        echo "   Los estatus se guardaron como IDs numéricos:\n";
        echo "   1 = Activo, 2 = Inactivo, 3 = Egresado\n";
        echo "   Considera crear la tabla catálogo si no existe.\n\n";
    } else {
        $estatusCount = $pdo->query("SELECT COUNT(*) as total FROM $dbname.estatus_estudiante")->fetch();
        echo "✓ Tabla estatus_estudiante existe ({$estatusCount->total} registros)\n\n";
    }
    
    echo "✅ Migración completada exitosamente!\n\n";
    
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
