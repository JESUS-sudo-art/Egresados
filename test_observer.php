<?php

/**
 * Script de prueba para el Observer de Egresado
 * 
 * Ejecutar con: php artisan tinker test_observer.php
 * O mejor: php artisan test:observer
 */

// Este script debe ejecutarse desde artisan tinker o como comando artisan

use App\Models\User;
use App\Models\Egresado;

echo "🧪 PRUEBA DEL OBSERVER DE EGRESADO\n";
echo "====================================\n\n";

// 1. Buscar un usuario con rol Estudiantes
$estudiante = User::whereHas('roles', function($q) {
    $q->where('name', 'Estudiantes');
})->first();

if (!$estudiante) {
    echo "❌ No se encontró ningún usuario con rol Estudiantes.\n";
    echo "Creando usuario de prueba...\n\n";
    
    $estudiante = User::create([
        'name' => 'Estudiante Prueba',
        'email' => 'estudiante.prueba@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
    $estudiante->assignRole('Estudiantes');
    echo "✅ Usuario creado: {$estudiante->email}\n\n";
}

echo "👤 Usuario encontrado:\n";
echo "   - Email: {$estudiante->email}\n";
echo "   - Nombre: {$estudiante->name}\n";
echo "   - Rol actual: " . $estudiante->roles->pluck('name')->implode(', ') . "\n\n";

// 2. Buscar o crear egresado asociado
$egresado = Egresado::where('email', $estudiante->email)->first();

if (!$egresado) {
    echo "📝 Creando registro de egresado...\n";
    $egresado = Egresado::create([
        'matricula' => 'TEST' . rand(1000, 9999),
        'nombre' => 'Estudiante',
        'apellidos' => 'Prueba Test',
        'email' => $estudiante->email,
        'validado_sice' => false,
        'estatus_id' => 1,
    ]);
    echo "✅ Egresado creado con ID: {$egresado->id}\n\n";
} else {
    echo "📋 Egresado encontrado con ID: {$egresado->id}\n\n";
}

echo "🔄 Estado actual del egresado:\n";
echo "   - ID: {$egresado->id}\n";
echo "   - Nombre: {$egresado->nombre} {$egresado->apellidos}\n";
echo "   - Email: {$egresado->email}\n";
echo "   - Validado SICE: " . ($egresado->validado_sice ? 'SÍ' : 'NO') . "\n\n";

echo "⏳ Actualizando validado_sice = true...\n\n";

// 3. Actualizar validado_sice (esto dispara el Observer)
$egresado->validado_sice = true;
$egresado->save();

// 4. Recargar usuario para ver cambios
$estudiante->refresh();

echo "✨ RESULTADO:\n";
echo "=====================================\n";
echo "👤 Usuario: {$estudiante->email}\n";
echo "🎓 Rol anterior: Estudiantes\n";
echo "🎓 Rol actual: " . $estudiante->roles->pluck('name')->implode(', ') . "\n\n";

if ($estudiante->hasRole('Egresados')) {
    echo "✅ ¡ÉXITO! El Observer cambió el rol correctamente.\n";
    echo "   El usuario ahora tiene rol de Egresados.\n";
} else {
    echo "❌ ERROR: El rol no cambió.\n";
    echo "   Revisa los logs en storage/logs/laravel.log\n";
}

echo "\n📝 Revisa los logs para más detalles:\n";
echo "   tail -f storage/logs/laravel.log | grep 'cambió de rol'\n\n";
