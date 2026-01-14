#!/usr/bin/env php
<?php

/**
 * Script de Importación de Base de Datos Antigua
 * 
 * Este script extrae los datos del dump SQL antiguo y los inserta directamente
 * en la base de datos nueva utilizando las nuevas tablas creadas.
 * 
 * Uso: php importar_bd_antigua.php <archivo_sql>
 */

if ($argc < 2) {
    echo "Uso: php importar_bd_antigua.php <archivo_sql>\n";
    echo "Ejemplo: php importar_bd_antigua.php bdwvexa_backup_260825.sql\n";
    exit(1);
}

$archivoSql = $argv[1];

if (!file_exists($archivoSql)) {
    echo "ERROR: El archivo {$archivoSql} no existe\n";
    exit(1);
}

echo "=== IMPORTADOR DE BASE DE DATOS ANTIGUA ===\n";
echo "Archivo: {$archivoSql}\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

// Cargar Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Leer el archivo SQL
echo "Leyendo archivo SQL...\n";
$contenidoSql = file_get_contents($archivoSql);
echo "Archivo cargado (" . number_format(strlen($contenidoSql)) . " bytes)\n\n";

/**
 * Función para extraer INSERT statements de una tabla
 */
function extraerInserts($contenido, $tabla) {
    $pattern = "/INSERT INTO `{$tabla}` VALUES\s+(.*?);/s";
    preg_match_all($pattern, $contenido, $matches);
    
    if (empty($matches[1])) {
        return [];
    }
    
    $registros = [];
    foreach ($matches[1] as $valuesBlock) {
        // Parsear los registros individuales
        // Pattern para encontrar tuplas (...)
        preg_match_all("/\(([^)]*(?:\([^)]*\)[^)]*)*)\)/", $valuesBlock, $rows);
        
        foreach ($rows[1] as $row) {
            $registros[] = $row;
        }
    }
    
    return $registros;
}

/**
 * Función para parsear un registro SQL
 */
function parsearRegistro($row) {
    $valores = [];
    $enComillas = false;
    $valorActual = '';
    $escapado = false;
    
    for ($i = 0; $i < strlen($row); $i++) {
        $char = $row[$i];
        
        if ($escapado) {
            $valorActual .= $char;
            $escapado = false;
            continue;
        }
        
        if ($char === '\\') {
            $escapado = true;
            continue;
        }
        
        if ($char === "'" && !$escapado) {
            $enComillas = !$enComillas;
            continue;
        }
        
        if ($char === ',' && !$enComillas) {
            $valores[] = trim($valorActual) === 'NULL' ? null : $valorActual;
            $valorActual = '';
            continue;
        }
        
        $valorActual .= $char;
    }
    
    // Último valor
    if ($valorActual !== '') {
        $valores[] = trim($valorActual) === 'NULL' ? null : $valorActual;
    }
    
    return $valores;
}

try {
    DB::beginTransaction();
    
    // FASE 1: Catálogos base
    echo "=== FASE 1: CATÁLOGOS ===\n";
    
    // Cat Dirigida (primero, manual)
    echo "Insertando catálogo de dirigidas...\n";
    $dirigidas = [
        ['id' => 1, 'descripcion' => 'Todos', 'estatus' => 'A'],
        ['id' => 2, 'descripcion' => 'Escuelas', 'estatus' => 'A'],
        ['id' => 3, 'descripcion' => 'Carrera', 'estatus' => 'A'],
        ['id' => 4, 'descripcion' => 'Nivel de Estudios', 'estatus' => 'A'],
        ['id' => 5, 'descripcion' => 'Generación', 'estatus' => 'A'],
        ['id' => 6, 'descripcion' => 'Específica', 'estatus' => 'A'],
    ];
    foreach ($dirigidas as $d) {
        DB::table('cat_dirigida')->insertOrIgnore($d);
    }
    echo "  ✓ 6 dirigidas insertadas\n";
    
    // Generaciones
    echo "Migrando generaciones...\n";
    $inserts = extraerInserts($contenidoSql, 'generaciones');
    $contador = 0;
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        DB::table('generacion')->insertOrIgnore([
            'id' => (int)$campos[0],
            'nombre' => $campos[1],
            'estatus' => $campos[2] ?? 'A',
        ]);
        $contador++;
    }
    echo "  ✓ {$contador} generaciones migradas\n";
    
    // Ciclos
    echo "Migrando ciclos...\n";
    $inserts = extraerInserts($contenidoSql, 'ciclos');
    $contador = 0;
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        DB::table('ciclo_escolar')->insertOrIgnore([
            'id' => (int)$campos[0],
            'nombre' => $campos[1],
            'estatus' => $campos[2] ?? 'A',
        ]);
        $contador++;
    }
    echo "  ✓ {$contador} ciclos migrados\n";
    
    // Unidades (Escuelas)
    echo "Migrando unidades (escuelas)...\n";
    $inserts = extraerInserts($contenidoSql, 'escuelas');
    $contador = 0;
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        DB::table('unidad')->insertOrIgnore([
            'id' => (int)$campos[0],
            'nombre' => $campos[1],
            'clave' => $campos[2] ?? null,
            'domicilio' => $campos[3] ?? null,
            'web' => $campos[4] ?? null,
            'email' => $campos[5] ?? null,
            'estatus' => $campos[7] ?? 'A',
        ]);
        $contador++;
    }
    echo "  ✓ {$contador} unidades migradas\n";
    
    // Carreras
    echo "Migrando carreras...\n";
    $nivelesMap = [
        1 => 'TECNICO',
        2 => 'BACHILLERATO',
        3 => 'LICENCIATURA',
        4 => 'MAESTRIA',
        5 => 'DOCTORADO',
        7 => 'ESPECIALIDAD',
    ];
    $inserts = extraerInserts($contenidoSql, 'carreras');
    $contador = 0;
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        $nivelId = (int)$campos[3];
        DB::table('carrera')->insertOrIgnore([
            'id' => (int)$campos[0],
            'nombre' => $campos[1],
            'nivel' => $nivelesMap[$nivelId] ?? 'LICENCIATURA',
            'estatus' => $campos[4] ?? 'A',
        ]);
        $contador++;
    }
    echo "  ✓ {$contador} carreras migradas\n";
    
    echo "\n=== FASE 2: EGRESADOS ===\n";
    
    // Egresados
    echo "Migrando egresados...\n";
    echo "ADVERTENCIA: Esta operación puede tardar varios minutos...\n";
    $inserts = extraerInserts($contenidoSql, 'egresados');
    $total = count($inserts);
    echo "Total de egresados a migrar: {$total}\n";
    
    $contador = 0;
    $lote = [];
    $tamanoLote = 100;
    
    foreach ($inserts as $idx => $row) {
        $campos = parsearRegistro($row);
        
        // Mapear género
        $generoChar = $campos[6] ?? null;
        $generoId = null;
        if ($generoChar === 'M') $generoId = 1;
        elseif ($generoChar === 'F') $generoId = 2;
        
        // Mapear estado civil
        $estadoCivilChar = $campos[11] ?? null;
        $estadoCivilId = null;
        if ($estadoCivilChar === 'S') $estadoCivilId = 1;
        elseif ($estadoCivilChar === 'C') $estadoCivilId = 2;
        
        $lote[] = [
            'id' => (int)$campos[0],
            'matricula' => $campos[1],
            'nombre' => $campos[3] ?? '',
            'apellidos' => $campos[4] ?? '',
            'genero_id' => $generoId,
            'fecha_nacimiento' => $campos[7] ?? null,
            'lugar_nacimiento' => $campos[8] ?? null,
            'domicilio' => $campos[9] ?? null,
            'email' => $campos[10] ?? "egresado{$campos[0]}@temp.com",
            'estado_civil_id' => $estadoCivilId,
            'extension' => $campos[19] ?? null,
            'token' => $campos[14] ?? null,
            'activo' => $campos[18] ?? 'I',
            'fecha_ingreso' => $campos[11] ?? null,
            'ultimo_ingreso' => $campos[12] ?? null,
        ];
        
        $contador++;
        
        if (count($lote) >= $tamanoLote) {
            DB::table('egresado')->insertOrIgnore($lote);
            $lote = [];
            echo "\r  Progreso: {$contador}/{$total}";
        }
    }
    
    if (!empty($lote)) {
        DB::table('egresado')->insertOrIgnore($lote);
    }
    echo "\r  ✓ {$contador} egresados migrados     \n";
    
    // Académicos
    echo "Migrando relaciones académicas...\n";
    $inserts = extraerInserts($contenidoSql, 'academicos');
    $contador = 0;
    $lote = [];
    
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        $lote[] = [
            'id' => (int)$campos[0],
            'egresado_id' => (int)$campos[1],
            'unidad_id' => (int)$campos[2],
            'carrera_id' => (int)$campos[3],
            'generacion_id' => (int)$campos[4],
        ];
        
        $contador++;
        if (count($lote) >= 100) {
            DB::table('academico')->insertOrIgnore($lote);
            $lote = [];
        }
    }
    if (!empty($lote)) {
        DB::table('academico')->insertOrIgnore($lote);
    }
    echo "  ✓ {$contador} relaciones académicas migradas\n";
    
    echo "\n=== FASE 3: ENCUESTAS ===\n";
    
    // Encuestas
    echo "Migrando encuestas...\n";
    $inserts = extraerInserts($contenidoSql, 'encuestas');
    $contador = 0;
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        DB::table('encuesta')->insertOrIgnore([
            'id' => (int)$campos[0],
            'ciclo_id' => (int)$campos[1],
            'nombre' => $campos[2],
            'nombre_corto' => $campos[3] ?? null,
            'dirigida_id' => isset($campos[4]) ? (int)$campos[4] : null,
            'fecha_inicio' => $campos[5] ?? null,
            'fecha_fin' => $campos[6] ?? null,
            'estatus' => $campos[7] ?? 'A',
            'descripcion' => $campos[8] ?? null,
            'instrucciones' => $campos[9] ?? null,
        ]);
        $contador++;
    }
    echo "  ✓ {$contador} encuestas migradas\n";
    
    // Dimensiones
    echo "Migrando dimensiones...\n";
    $inserts = extraerInserts($contenidoSql, 'dimensiones');
    $contador = 0;
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        DB::table('dimension')->insertOrIgnore([
            'id' => (int)$campos[0],
            'nombre' => $campos[1],
            'descripcion' => $campos[2] ?? null,
            'orden' => (int)$campos[3],
            'encuesta_id' => (int)$campos[4],
        ]);
        $contador++;
    }
    echo "  ✓ {$contador} dimensiones migradas\n";
    
    // Subdimensiones
    echo "Migrando subdimensiones...\n";
    $inserts = extraerInserts($contenidoSql, 'subdimensiones');
    $contador = 0;
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        DB::table('subdimension')->insertOrIgnore([
            'id' => (int)$campos[0],
            'nombre' => $campos[1],
            'descripcion' => $campos[2] ?? null,
            'orden' => (int)$campos[3],
            'dimension_id' => (int)$campos[4],
        ]);
        $contador++;
    }
    echo "  ✓ {$contador} subdimensiones migradas\n";
    
    // Preguntas
    echo "Migrando preguntas...\n";
    echo "ADVERTENCIA: Esta operación puede tardar varios minutos...\n";
    $tiposMap = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7];
    $inserts = extraerInserts($contenidoSql, 'preguntas');
    $total = count($inserts);
    $contador = 0;
    $lote = [];
    
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        $tipoId = (int)$campos[5];
        
        $lote[] = [
            'id' => (int)$campos[0],
            'encuesta_id' => (int)$campos[1],
            'texto' => $campos[2],
            'dimension_id' => isset($campos[3]) && $campos[3] !== null ? (int)$campos[3] : null,
            'subdimension_id' => isset($campos[4]) && $campos[4] !== null ? (int)$campos[4] : null,
            'tipo_pregunta_id' => $tiposMap[$tipoId] ?? 1,
            'tamanio' => isset($campos[6]) ? (int)$campos[6] : null,
            'orden' => (int)$campos[7],
            'presentacion' => $campos[8] ?? null,
            'orientacion' => $campos[9] ?? null,
            'pregunta_padre_id' => isset($campos[10]) && $campos[10] !== null ? (int)$campos[10] : null,
            'tips' => $campos[11] ?? null,
            'instruccion' => $campos[12] ?? null,
        ];
        
        $contador++;
        if (count($lote) >= 100) {
            DB::table('pregunta')->insertOrIgnore($lote);
            $lote = [];
            echo "\r  Progreso: {$contador}/{$total}";
        }
    }
    if (!empty($lote)) {
        DB::table('pregunta')->insertOrIgnore($lote);
    }
    echo "\r  ✓ {$contador} preguntas migradas     \n";
    
    // Opciones
    echo "Migrando opciones...\n";
    echo "ADVERTENCIA: Esta operación puede tardar varios minutos...\n";
    $inserts = extraerInserts($contenidoSql, 'opciones');
    $total = count($inserts);
    $contador = 0;
    $lote = [];
    
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        
        $lote[] = [
            'id' => (int)$campos[0],
            'pregunta_id' => (int)$campos[1],
            'valor' => isset($campos[2]) ? (int)$campos[2] : null,
            'orden' => (int)$campos[3],
            'texto' => $campos[4] ?? '',
        ];
        
        $contador++;
        if (count($lote) >= 200) {
            DB::table('opcion')->insertOrIgnore($lote);
            $lote = [];
            echo "\r  Progreso: {$contador}/{$total}";
        }
    }
    if (!empty($lote)) {
        DB::table('opcion')->insertOrIgnore($lote);
    }
    echo "\r  ✓ {$contador} opciones migradas     \n";
    
    echo "\n=== FASE 4: RESPUESTAS ===\n";
    echo "ADVERTENCIA: Las siguientes operaciones son MUY PESADAS\n\n";
    
    // Bitácora de encuestas
    echo "Migrando bitácora de encuestas...\n";
    $inserts = extraerInserts($contenidoSql, 'bitencuestas');
    $total = count($inserts);
    $contador = 0;
    $lote = [];
    
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        
        $lote[] = [
            'id' => (int)$campos[0],
            'egresado_id' => (int)$campos[1],
            'ciclo_id' => (int)$campos[2],
            'encuesta_id' => (int)$campos[3],
            'completada' => 'S',
        ];
        
        $contador++;
        if (count($lote) >= 500) {
            DB::table('bitacora_encuesta')->insertOrIgnore($lote);
            $lote = [];
            echo "\r  Progreso: {$contador}/{$total}";
        }
    }
    if (!empty($lote)) {
        DB::table('bitacora_encuesta')->insertOrIgnore($lote);
    }
    echo "\r  ✓ {$contador} bitácoras de encuesta migradas     \n";
    
    // Respuestas Int (LA MÁS PESADA)
    echo "Migrando respuestas numéricas...\n";
    echo "ADVERTENCIA: Esta es la operación MÁS PESADA, puede tardar 30+ minutos\n";
    $inserts = extraerInserts($contenidoSql, 'intrespuestas');
    $total = count($inserts);
    echo "Total de respuestas: {$total}\n";
    
    $contador = 0;
    $lote = [];
    $tamanoLote = 1000;
    
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        
        $lote[] = [
            'id' => (int)$campos[0],
            'bitacora_encuesta_id' => (int)$campos[1],
            'respuesta' => $campos[2] ?? null,
            'pregunta_id' => (int)$campos[3],
        ];
        
        $contador++;
        if (count($lote) >= $tamanoLote) {
            DB::table('respuesta_int')->insertOrIgnore($lote);
            $lote = [];
            $porcentaje = round(($contador / $total) * 100, 2);
            echo "\r  Progreso: {$contador}/{$total} ({$porcentaje}%)";
        }
    }
    if (!empty($lote)) {
        DB::table('respuesta_int')->insertOrIgnore($lote);
    }
    echo "\r  ✓ {$contador} respuestas int migradas          \n";
    
    // Respuestas Txt
    echo "Migrando respuestas de texto...\n";
    echo "ADVERTENCIA: Esta operación es muy pesada, puede tardar 20+ minutos\n";
    $inserts = extraerInserts($contenidoSql, 'txtrespuestas');
    $total = count($inserts);
    echo "Total de respuestas: {$total}\n";
    
    $contador = 0;
    $lote = [];
    $tamanoLote = 500;
    
    foreach ($inserts as $row) {
        $campos = parsearRegistro($row);
        
        $lote[] = [
            'id' => (int)$campos[0],
            'bitacora_encuesta_id' => (int)$campos[1],
            'respuesta' => $campos[2] ?? null,
            'pregunta_id' => (int)$campos[3],
        ];
        
        $contador++;
        if (count($lote) >= $tamanoLote) {
            DB::table('respuesta_txt')->insertOrIgnore($lote);
            $lote = [];
            $porcentaje = round(($contador / $total) * 100, 2);
            echo "\r  Progreso: {$contador}/{$total} ({$porcentaje}%)";
        }
    }
    if (!empty($lote)) {
        DB::table('respuesta_txt')->insertOrIgnore($lote);
    }
    echo "\r  ✓ {$contador} respuestas txt migradas          \n";
    
    DB::commit();
    
    echo "\n===========================================\n";
    echo "✓✓✓ MIGRACIÓN COMPLETADA EXITOSAMENTE ✓✓✓\n";
    echo "===========================================\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\n\n❌ ERROR DURANTE LA MIGRACIÓN ❌\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
