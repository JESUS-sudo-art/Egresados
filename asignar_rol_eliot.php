<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "Asignando rol 'Estudiantes' al usuario eliot...\n";
echo "================================================\n\n";

$usuario = User::where('email', 'el4643874@gmail.com')->first();

if (!$usuario) {
    echo "✗ Usuario no encontrado.\n";
    exit;
}

echo "Usuario encontrado: {$usuario->name} ({$usuario->email})\n";
echo "Roles actuales: " . ($usuario->roles->isEmpty() ? "Ninguno" : $usuario->roles->pluck('name')->implode(', ')) . "\n\n";

// Asignar rol Estudiantes
$usuario->assignRole('Estudiantes');

echo "✓ Rol 'Estudiantes' asignado correctamente.\n\n";

// Verificar
$usuario->refresh();
echo "Roles después de asignar:\n";
foreach ($usuario->roles as $role) {
    echo "- {$role->name} (id: {$role->id})\n";
}
