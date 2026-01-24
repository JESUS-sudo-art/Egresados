<?php
/**
 * Probar actualización manual y verificar sincronización
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== Prueba de Sincronización Manual ===\n\n";

// Buscar un egresado con cédula pre-egreso
$egresadoData = DB::table('egresado')
    ->join('cedula_preegreso', 'egresado.id', '=', 'cedula_preegreso.egresado_id')
    ->select('egresado.id', 'egresado.email', 'egresado.telefono', 'egresado.fecha_nacimiento', 'cedula_preegreso.id as cedula_id')
    ->first();

if (!$egresadoData) {
    echo "No se encontró ningún egresado con cédula pre-egreso para probar\n";
    exit(1);
}

echo "Egresado seleccionado: {$egresadoData->id} ({$egresadoData->email})\n";
echo "Teléfono actual: {$egresadoData->telefono}\n";
echo "Fecha nacimiento: {$egresadoData->fecha_nacimiento}\n\n";

// Ver estado actual de cédula
$cedulaAntes = DB::table('cedula_preegreso')->where('id', $egresadoData->cedula_id)->first();
echo "Estado actual de cédula pre-egreso:\n";
echo "  - Teléfono: " . ($cedulaAntes->telefono_contacto ?? 'null') . "\n";
echo "  - Edad: " . ($cedulaAntes->edad ?? 'null') . "\n\n";

// Actualizar manualmente usando raw SQL
$nuevoTelefono = '9519876543';
$nuevaFechaNacimiento = '1999-03-20';
$edadCalculada = Carbon::parse($nuevaFechaNacimiento)->age;

echo "Actualizando datos del egresado:\n";
echo "  - Nuevo teléfono: $nuevoTelefono\n";
echo "  - Nueva fecha nacimiento: $nuevaFechaNacimiento (edad: $edadCalculada)\n\n";

// Actualizar en egresado
$sql = "UPDATE `egresado` SET `telefono` = ?, `fecha_nacimiento` = ?, `updated_at` = NOW() WHERE `id` = ?";
DB::update($sql, [$nuevoTelefono, $nuevaFechaNacimiento, $egresadoData->id]);

echo "Egresado actualizado.\n\n";

// Actualizar manualmente en cédula (simulando lo que hace el Observer)
$sqlCedula = "UPDATE `cedula_preegreso` SET `telefono_contacto` = ?, `edad` = ?, `updated_at` = NOW() WHERE `id` = ?";
DB::update($sqlCedula, [$nuevoTelefono, $edadCalculada, $egresadoData->cedula_id]);

echo "Cédula pre-egreso actualizada manualmente.\n\n";

// Verificar resultado
$cedulaDespues = DB::table('cedula_preegreso')->where('id', $egresadoData->cedula_id)->first();
echo "Estado después de actualizar:\n";
echo "  - Teléfono: " . ($cedulaDespues->telefono_contacto ?? 'null') . "\n";
echo "  - Edad: " . ($cedulaDespues->edad ?? 'null') . "\n\n";

if ($cedulaDespues->telefono_contacto === $nuevoTelefono && $cedulaDespues->edad == $edadCalculada) {
    echo "✓ ¡Actualización exitosa! Los datos se sincronizaron correctamente.\n";
} else {
    echo "✗ Error en la sincronización\n";
}
