<?php
/**
 * Probar actualización automática del Observer
 * Simula guardar un perfil y verifica que se sincronice
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Egresado;
use App\Models\CedulaPreegreso;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== Prueba de Sincronización Automática ===\n\n";

// Buscar un egresado con cédula pre-egreso
$egresado = Egresado::whereHas('cedulaPreegreso')->first();

if (!$egresado) {
    echo "No se encontró ningún egresado con cédula pre-egreso para probar\n";
    exit(1);
}

echo "Egresado seleccionado: {$egresado->id} ({$egresado->email})\n";

// Ver estado actual
$cedula = CedulaPreegreso::where('egresado_id', $egresado->id)->first();
echo "Estado actual de cédula pre-egreso:\n";
echo "  - Teléfono: " . ($cedula->telefono_contacto ?? 'null') . "\n";
echo "  - Edad: " . ($cedula->edad ?? 'null') . "\n\n";

// Actualizar teléfono y fecha de nacimiento
$nuevoTelefono = '9511234567';
$nuevaFechaNacimiento = '1998-05-15';

echo "Actualizando egresado...\n";
echo "  - Nuevo teléfono: $nuevoTelefono\n";
echo "  - Nueva fecha nacimiento: $nuevaFechaNacimiento\n\n";

// Usar raw SQL para actualizar (igual que en PerfilController)
$sql = "UPDATE `egresado` SET `telefono` = ?, `fecha_nacimiento` = ?, `updated_at` = NOW() WHERE `id` = ?";
DB::update($sql, [$nuevoTelefono, $nuevaFechaNacimiento, $egresado->id]);

// Refrescar modelo para disparar el Observer
$egresado = Egresado::find($egresado->id);

echo "Esperando 2 segundos para que el Observer procese...\n";
sleep(2);

// Verificar si se sincronizó
$cedula = CedulaPreegreso::where('egresado_id', $egresado->id)->first();
echo "\nEstado después de actualizar:\n";
echo "  - Teléfono: " . ($cedula->telefono_contacto ?? 'null') . "\n";
echo "  - Edad: " . ($cedula->edad ?? 'null') . "\n";

$edadEsperada = Carbon::parse($nuevaFechaNacimiento)->age;
if ($cedula->telefono_contacto === $nuevoTelefono && $cedula->edad == $edadEsperada) {
    echo "\n✓ ¡Sincronización automática funcionando correctamente!\n";
} else {
    echo "\n✗ La sincronización no funcionó como se esperaba\n";
    echo "   Teléfono esperado: $nuevoTelefono, obtenido: {$cedula->telefono_contacto}\n";
    echo "   Edad esperada: $edadEsperada, obtenida: {$cedula->edad}\n";
}
