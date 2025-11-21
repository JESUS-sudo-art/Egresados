<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'rsd1desarrolloweb@uabjo.mx';

$user = App\Models\User::where('email', $email)->first();

if ($user) {
    echo "✓ Usuario encontrado" . PHP_EOL;
    echo "ID: {$user->id}" . PHP_EOL;
    echo "Nombre: {$user->name}" . PHP_EOL;
    echo "Email: {$user->email}" . PHP_EOL;
    echo "Roles: " . $user->roles->pluck('name')->join(', ') . PHP_EOL;
    
    if ($user->roles->isEmpty()) {
        echo PHP_EOL . "⚠ Este usuario NO tiene ningún rol asignado" . PHP_EOL;
    }
} else {
    echo "✗ Usuario NO encontrado con email: {$email}" . PHP_EOL;
    echo PHP_EOL . "Usuarios disponibles:" . PHP_EOL;
    $users = App\Models\User::limit(10)->get();
    foreach ($users as $u) {
        echo "  - {$u->email} | Roles: " . $u->roles->pluck('name')->join(', ') . PHP_EOL;
    }
}
