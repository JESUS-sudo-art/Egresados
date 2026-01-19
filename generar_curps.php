<?php
/**
 * Script para generar CURPs reales de egresados que tienen hashes SHA-1
 */

$host = 'egresados-db';
$dbname = 'egresados_db';
$user = 'root';
$pass = 'root';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
]);

echo "=== Generador de CURPs ===\n\n";

// Catálogo de estados (código de 2 letras para CURP)
$estados = [
    'AGUASCALIENTES' => 'AS',
    'BAJA CALIFORNIA' => 'BC',
    'BAJA CALIFORNIA SUR' => 'BS',
    'CAMPECHE' => 'CC',
    'CHIAPAS' => 'CS',
    'CHIHUAHUA' => 'CH',
    'CIUDAD DE MEXICO' => 'DF',
    'CDMX' => 'DF',
    'COAHUILA' => 'CL',
    'COLIMA' => 'CM',
    'DURANGO' => 'DG',
    'ESTADO DE MEXICO' => 'MC',
    'MEXICO' => 'MC',
    'GUANAJUATO' => 'GT',
    'GUERRERO' => 'GR',
    'HIDALGO' => 'HG',
    'JALISCO' => 'JC',
    'MICHOACAN' => 'MN',
    'MORELOS' => 'MS',
    'NAYARIT' => 'NT',
    'NUEVO LEON' => 'NL',
    'OAXACA' => 'OC',
    'PUEBLA' => 'PL',
    'QUERETARO' => 'QT',
    'QUINTANA ROO' => 'QR',
    'SAN LUIS POTOSI' => 'SP',
    'SINALOA' => 'SL',
    'SONORA' => 'SR',
    'TABASCO' => 'TC',
    'TAMAULIPAS' => 'TS',
    'TLAXCALA' => 'TL',
    'VERACRUZ' => 'VZ',
    'YUCATAN' => 'YN',
    'ZACATECAS' => 'ZS',
    'EXTRANJERO' => 'NE',
];

// Palabras prohibidas en CURP
$palabrasProhibidas = [
    'BACA', 'BAKA', 'BUEI', 'BUEY', 'CACA', 'CACO', 'CAGA', 'CAGO', 'CAKA', 'CAKO',
    'COGE', 'COGI', 'COJA', 'COJE', 'COJI', 'COJO', 'COLA', 'CULO', 'FALO', 'FETO',
    'GETA', 'GUEI', 'GUEY', 'JETA', 'JOTO', 'KACA', 'KACO', 'KAGA', 'KAGO', 'KAKA',
    'KAKO', 'KOGE', 'KOGI', 'KOJA', 'KOJE', 'KOJI', 'KOJO', 'KOLA', 'KULO', 'LILO',
    'LOCA', 'LOCO', 'LOKA', 'LOKO', 'MAME', 'MAMO', 'MEAR', 'MEAS', 'MEON', 'MIAR',
    'MION', 'MOCO', 'MOKO', 'MULA', 'MULO', 'NACA', 'NACO', 'PEDA', 'PEDO', 'PENE',
    'PIPI', 'PITO', 'POPO', 'PUTA', 'PUTO', 'QULO', 'RATA', 'ROBA', 'ROBE', 'ROBO',
    'RUIN', 'SENO', 'TETA', 'VACA', 'VAGA', 'VAGO', 'VAKA', 'VUEI', 'VUEY', 'WUEI',
    'WUEY'
];

function limpiarTexto($texto) {
    $texto = mb_strtoupper($texto, 'UTF-8');
    // Eliminar acentos
    $texto = str_replace(
        ['Á','É','Í','Ó','Ú','Ñ','Ü'],
        ['A','E','I','O','U','N','U'],
        $texto
    );
    // Solo letras
    return preg_replace('/[^A-Z]/', '', $texto);
}

function obtenerVocales($texto) {
    preg_match_all('/[AEIOU]/', $texto, $matches);
    return implode('', $matches[0]);
}

function obtenerConsonantes($texto) {
    preg_match_all('/[BCDFGHJKLMNPQRSTVWXYZ]/', $texto, $matches);
    return implode('', $matches[0]);
}

function generarCURP($nombre, $apellidos, $fechaNac, $sexo, $estado, $palabrasProhibidas, $estados) {
    // Parsear nombre y apellidos
    $apellidos = trim($apellidos);
    $partes = preg_split('/\s+/', $apellidos);
    
    $apellidoPaterno = isset($partes[0]) ? limpiarTexto($partes[0]) : 'X';
    $apellidoMaterno = isset($partes[1]) ? limpiarTexto($partes[1]) : 'X';
    $primerNombre = limpiarTexto(explode(' ', trim($nombre))[0]);
    
    // 1. Primera letra del apellido paterno
    $letra1 = substr($apellidoPaterno, 0, 1);
    
    // 2. Primera vocal interna del apellido paterno
    $vocales = obtenerVocales(substr($apellidoPaterno, 1));
    $letra2 = $vocales ? substr($vocales, 0, 1) : 'X';
    
    // 3. Primera letra del apellido materno
    $letra3 = substr($apellidoMaterno, 0, 1);
    
    // 4. Primera letra del primer nombre
    $letra4 = substr($primerNombre, 0, 1);
    
    // Verificar palabras prohibidas
    $codigo = $letra1 . $letra2 . $letra3 . $letra4;
    if (in_array($codigo, $palabrasProhibidas)) {
        $letra2 = 'X';
        $codigo = $letra1 . $letra2 . $letra3 . $letra4;
    }
    
    // 5-10. Fecha de nacimiento AAMMDD
    $fecha = date('ymd', strtotime($fechaNac));
    
    // 11. Sexo
    $letraSexo = ($sexo == 1) ? 'H' : 'M';
    
    // 12-13. Estado
    $estado = mb_strtoupper(trim($estado), 'UTF-8');
    $codigoEstado = 'OC'; // Default Oaxaca (mayoría de egresados)
    
    foreach ($estados as $nombreEstado => $codigo) {
        if (strpos($estado, $nombreEstado) !== false) {
            $codigoEstado = $codigo;
            break;
        }
    }
    
    // 14-16. Consonantes internas
    $consonantes = '';
    
    // Primera consonante interna del apellido paterno
    $consPaterno = obtenerConsonantes(substr($apellidoPaterno, 1));
    $consonantes .= $consPaterno ? substr($consPaterno, 0, 1) : 'X';
    
    // Primera consonante interna del apellido materno
    $consMaterno = obtenerConsonantes(substr($apellidoMaterno, 1));
    $consonantes .= $consMaterno ? substr($consMaterno, 0, 1) : 'X';
    
    // Primera consonante interna del nombre
    $consNombre = obtenerConsonantes(substr($primerNombre, 1));
    $consonantes .= $consNombre ? substr($consNombre, 0, 1) : 'X';
    
    // 17-18. Homoclave (usamos dígitos aleatorios por ahora)
    $homoclave = sprintf('%02d', rand(0, 99));
    
    return $codigo . $fecha . $letraSexo . $codigoEstado . $consonantes . $homoclave;
}

// Obtener egresados con hash en CURP que tengan datos suficientes
$query = "
    SELECT id, nombre, apellidos, genero_id, fecha_nacimiento, 
           COALESCE(estado_origen, lugar_nacimiento, 'OAXACA') as estado
    FROM egresado 
    WHERE LENGTH(curp) > 18
    AND fecha_nacimiento IS NOT NULL
    AND genero_id IS NOT NULL
    ORDER BY id
";

$egresados = $pdo->query($query)->fetchAll();
echo "Egresados a procesar: " . count($egresados) . "\n\n";

$actualizados = 0;
$errores = 0;

foreach ($egresados as $egresado) {
    try {
        $curp = generarCURP(
            $egresado->nombre,
            $egresado->apellidos,
            $egresado->fecha_nacimiento,
            $egresado->genero_id,
            $egresado->estado,
            $palabrasProhibidas,
            $estados
        );
        
        // Actualizar en base de datos
        $stmt = $pdo->prepare("UPDATE egresado SET curp = ? WHERE id = ?");
        $stmt->execute([$curp, $egresado->id]);
        
        $actualizados++;
        
        if ($actualizados <= 10) {
            echo "ID {$egresado->id}: {$egresado->nombre} {$egresado->apellidos} -> $curp\n";
        } elseif ($actualizados % 100 == 0) {
            echo "Procesados: $actualizados...\n";
        }
        
    } catch (Exception $e) {
        $errores++;
        if ($errores <= 5) {
            echo "ERROR en ID {$egresado->id}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== RESUMEN ===\n";
echo "✓ CURPs generadas: $actualizados\n";
echo "✗ Errores: $errores\n";

// Estadísticas finales
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN LENGTH(curp) = 18 THEN 1 END) as curps_reales,
        COUNT(CASE WHEN LENGTH(curp) > 18 THEN 1 END) as curps_hash
    FROM egresado 
    WHERE curp IS NOT NULL
")->fetch();

echo "\nESTADÍSTICAS FINALES:\n";
echo "Total con CURP: {$stats->total}\n";
echo "CURPs reales (18 chars): {$stats->curps_reales}\n";
echo "CURPs hash (>18 chars): {$stats->curps_hash}\n";
echo "\n✅ Proceso completado!\n";
