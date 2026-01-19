<?php

/**
 * Script de importación de egresados desde BD antigua
 * Extrae los datos de la tabla 'egresados' y los inserta en la nueva tabla 'egresado'
 */

require_once __DIR__ . '/vendor/autoload.php';

// Configurar el entorno
putenv('DB_CONNECTION=mysql');
putenv('DB_HOST=db');
putenv('DB_PORT=3306');
putenv('DB_DATABASE=egresados_db');
putenv('DB_USERNAME=user');
putenv('DB_PASSWORD=password');

use Illuminate\Database\Capsule\Manager as DB;

// Configurar conexiones
$capsule = new DB();

// Conexión a BD nueva
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'db',
    'database' => 'egresados_db',
    'username' => 'user',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
], 'nueva');

// Conexión a BD antigua
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'db',
    'database' => 'bdwvexa',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
], 'antigua');

$capsule->setAsGlobal();

echo "=== IMPORTADOR DE EGRESADOS ===\n";
echo "Iniciando importación desde BD antigua...\n\n";

try {
    // Verificar conexión a BD antigua
    DB::connection('antigua')->getPdo();
    echo "✓ Conectado a BD antigua (bdwvexa)\n";
    
    // Verificar conexión a BD nueva
    DB::connection('nueva')->getPdo();
    echo "✓ Conectado a BD nueva (egresados_db)\n\n";

    // Obtener egresados de BD antigua
    $egresados = DB::connection('antigua')
        ->table('egresados')
        ->get();
    
    echo "Registros a importar: " . $egresados->count() . "\n";
    echo "Iniciando inserción...\n\n";

    $insertados = 0;
    $errores = 0;
    $errores_list = [];

    foreach ($egresados as $egresado) {
        try {
            // Mapeo de campos
            $genero_id = null;
            if ($egresado->sexo === 'M') {
                $genero_id = 1; // Masculino
            } elseif ($egresado->sexo === 'F') {
                $genero_id = 2; // Femenino
            }

            $estado_civil_id = null;
            if ($egresado->estado_civil === 'S') {
                $estado_civil_id = 1; // Soltero
            } elseif ($egresado->estado_civil === 'C') {
                $estado_civil_id = 2; // Casado
            }

            // Preparar datos para insertar
            $datos = [
                'matricula' => $egresado->matricula ?? null,
                'curp' => $egresado->curp ?? null,
                'nombre' => $egresado->nombre ?? 'SIN NOMBRE',
                'apellidos' => $egresado->apellidos ?? 'SIN APELLIDOS',
                'genero_id' => $genero_id,
                'fecha_nacimiento' => $egresado->fecha_nacimiento !== '0000-00-00' ? $egresado->fecha_nacimiento : null,
                'lugar_nacimiento' => $egresado->lugar_nacimiento ?? null,
                'estado_origen' => $egresado->estado_origen ?? null,
                'domicilio' => $egresado->domicilio ?? null,
                'domicilio_actual' => $egresado->domicilio_actual ?? null,
                'email' => $egresado->email ?? 'sin-email@temp.com',
                'extension' => $egresado->extension ?? null,
                'estado_civil_id' => $estado_civil_id,
                'tiene_hijos' => isset($egresado->tiene_hijos) ? ($egresado->tiene_hijos ? 1 : 0) : null,
                'habla_lengua_indigena' => isset($egresado->habla_lengua_indigena) ? ($egresado->habla_lengua_indigena ? 1 : 0) : null,
                'habla_segundo_idioma' => isset($egresado->habla_segundo_idioma) ? ($egresado->habla_segundo_idioma ? 1 : 0) : null,
                'pertenece_grupo_etnico' => isset($egresado->pertenece_grupo_etnico) ? ($egresado->pertenece_grupo_etnico ? 1 : 0) : null,
                'facebook_url' => $egresado->facebook_url ?? null,
                'tipo_estudiante' => $egresado->tipo_estudiante ?? null,
                'validado_sice' => $egresado->validado_sice ?? 'N',
                'estatus_id' => 1, // Activo por defecto
                'activo' => 'I',
                'anio_egreso' => $egresado->anio_egreso ?? date('Y'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insertar en BD nueva
            DB::connection('nueva')
                ->table('egresado')
                ->insertOrIgnore($datos);

            $insertados++;

            if ($insertados % 100 === 0) {
                echo "Procesados: $insertados registros\n";
            }

        } catch (\Exception $e) {
            $errores++;
            $errores_list[] = [
                'matricula' => $egresado->matricula ?? 'N/A',
                'error' => $e->getMessage(),
            ];

            if ($errores <= 10) {
                echo "❌ Error en registro {$egresado->matricula}: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n=== RESULTADO DE LA IMPORTACIÓN ===\n";
    echo "Registros insertados: $insertados\n";
    echo "Errores encontrados: $errores\n";
    
    if (!empty($errores_list)) {
        echo "\nDetalle de errores:\n";
        foreach (array_slice($errores_list, 0, 10) as $error) {
            echo "  - Matrícula {$error['matricula']}: {$error['error']}\n";
        }
        if ($errores > 10) {
            echo "  ... y " . ($errores - 10) . " errores más\n";
        }
    }

    // Verificar conteo final
    $total = DB::connection('nueva')
        ->table('egresado')
        ->count();
    
    echo "\nTotal de egresados en BD nueva: $total\n";

} catch (\Exception $e) {
    echo "❌ Error crítico: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ Proceso completado\n";
