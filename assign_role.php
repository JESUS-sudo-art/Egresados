<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'rsd1.desarrolloweb@uabjo.mx';

$user = App\Models\User::where('email', $email)->first();

if ($user) {
    // Asignar el rol de Administrador general
    $user->assignRole('Administrador general');
    
    echo "✓ Rol 'Administrador general' asignado exitosamente" . PHP_EOL;
    echo "Usuario: {$user->name}" . PHP_EOL;
    echo "Email: {$user->email}" . PHP_EOL;
    echo "Roles actuales: " . $user->roles->pluck('name')->join(', ') . PHP_EOL;
} else {
    echo "✗ Usuario no encontrado" . PHP_EOL;
}
