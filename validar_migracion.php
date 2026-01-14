#!/usr/bin/env php
<?php

/**
 * Script de Validación de Migración
 */

echo "\n=== VALIDACIÓN DE MIGRACIÓN ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$dbHost = 'db';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'egresados_db';

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Estadísticas generales
    echo "ESTADÍSTICAS DE MIGRACIÓN:\n";
    echo str_repeat("-", 50) . "\n";
    
    $tablas = [
        'egresado' => 'Egresados',
        'academico' => 'Relaciones académicas',
        'ciclo_escolar' => 'Ciclos escolares',
        'generacion' => 'Generaciones',
        'bitacora_egresado' => 'Bitácoras de egresado',
        'respuesta_int' => 'Respuestas numéricas',
        'respuesta_txt' => 'Respuestas de texto',
        'subdimension' => 'Subdimensiones'
    ];
    
    $totalRegistros = 0;
    foreach ($tablas as $tabla => $nombre) {
        $result = $pdo->query("SELECT COUNT(*) FROM {$tabla}");
        $count = $result->fetchColumn();
        $totalRegistros += $count;
        echo sprintf("%-40s: %8d registros\n", $nombre, $count);
    }
    
    echo str_repeat("-", 50) . "\n";
    echo sprintf("%-40s: %8d registros\n", "TOTAL", $totalRegistros);
    
    // Verificaciones de integridad
    echo "\n\nVERIFICACIONES DE INTEGRIDAD:\n";
    echo str_repeat("-", 50) . "\n";
    
    // Egresados sin género
    $result = $pdo->query("SELECT COUNT(*) FROM egresado WHERE genero_id IS NULL");
    $count = $result->fetchColumn();
    echo "Egresados sin género asignado: {$count}\n";
    
    // Egresados sin estado civil
    $result = $pdo->query("SELECT COUNT(*) FROM egresado WHERE estado_civil_id IS NULL");
    $count = $result->fetchColumn();
    echo "Egresados sin estado civil: {$count}\n";
    
    // Academicos huérfanos (sin egresado)
    $result = $pdo->query("
        SELECT COUNT(*) FROM academico a
        LEFT JOIN egresado e ON a.egresado_id = e.id
        WHERE e.id IS NULL
    ");
    $count = $result->fetchColumn();
    echo "Relaciones académicas huérfanas: {$count}\n";
    
    // Bitácoras huérfanas
    $result = $pdo->query("
        SELECT COUNT(*) FROM bitacora_egresado b
        LEFT JOIN egresado e ON b.egresado_id = e.id
        WHERE e.id IS NULL
    ");
    $count = $result->fetchColumn();
    echo "Bitácoras de egresado huérfanas: {$count}\n";
    
    echo "\n=== VALIDACIÓN COMPLETADA ===\n\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
