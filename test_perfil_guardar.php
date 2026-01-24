<?php
/**
 * Script de prueba para validar que los cambios se guardan correctamente
 * Comando: php artisan tinker < test_perfil_guardar.php
 */

use App\Models\User;
use App\Models\Egresado;
use App\Models\CatEstatus;

echo "=== Iniciando prueba de guardado de perfil ===\n\n";

try {
    // 1. Buscar un usuario egresado
    echo "1. Buscando usuario de prueba...\n";
    $user = User::with('roles')->whereHas('roles', function($q) {
        $q->where('name', 'Egresados');
    })->first();
    
    if (!$user) {
        echo "❌ No hay usuarios con rol Egresados\n";
        exit(1);
    }
    
    echo "✓ Usuario encontrado: {$user->email}\n\n";
    
    // 2. Buscar o crear egresado
    echo "2. Buscando egresado asociado...\n";
    $egresado = Egresado::where('email', $user->email)->first();
    
    if (!$egresado) {
        echo "❌ No hay egresado asociado al usuario\n";
        exit(1);
    }
    
    echo "✓ Egresado encontrado: {$egresado->id}\n\n";
    
    // 3. Intenta actualizar usando updateOrCreate (el nuevo método)
    echo "3. Actualizando datos con updateOrCreate()...\n";
    
    $datosOriginales = [
        'nombre' => $egresado->nombre,
        'telefono' => $egresado->telefono,
        'domicilio' => $egresado->domicilio,
    ];
    
    echo "   Datos originales:\n";
    echo "   - Nombre: {$datosOriginales['nombre']}\n";
    echo "   - Teléfono: {$datosOriginales['telefono']}\n";
    echo "   - Domicilio: {$datosOriginales['domicilio']}\n\n";
    
    // Crear datos de prueba
    $datosPrueba = [
        'id' => $egresado->id,
        'nombre' => $egresado->nombre,
        'apellidos' => $egresado->apellidos,
        'email' => $egresado->email,
        'telefono' => '5551234567',
        'domicilio' => 'Calle de Prueba 123',
        'estatus_id' => CatEstatus::where('nombre', 'Egresado')->value('id') ?? 2,
    ];
    
    // Guardar con updateOrCreate
    Egresado::updateOrCreate(
        ['id' => $datosPrueba['id']],
        $datosPrueba
    );
    
    echo "   ✓ updateOrCreate() ejecutado\n\n";
    
    // 4. Verificar que los cambios se guardaron
    echo "4. Verificando cambios en BD...\n";
    $egresadoActualizado = Egresado::find($egresado->id);
    
    echo "   Datos nuevos:\n";
    echo "   - Nombre: {$egresadoActualizado->nombre}\n";
    echo "   - Teléfono: {$egresadoActualizado->telefono}\n";
    echo "   - Domicilio: {$egresadoActualizado->domicilio}\n\n";
    
    if ($egresadoActualizado->telefono === '5551234567') {
        echo "✓ Cambios guardados correctamente en la BD\n\n";
    } else {
        echo "❌ Los cambios NO se guardaron\n\n";
    }
    
    // 5. Restaurar datos originales
    echo "5. Restaurando datos originales...\n";
    Egresado::updateOrCreate(
        ['id' => $egresado->id],
        $datosOriginales + ['id' => $egresado->id, 'email' => $egresado->email]
    );
    
    echo "✓ Datos restaurados\n\n";
    
    echo "=== Prueba completada exitosamente ===\n";
    
} catch (Exception $e) {
    echo "❌ Error en la prueba:\n";
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
