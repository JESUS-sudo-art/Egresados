<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// Buscar usuarios con rol Admin General
$users = User::whereHas('roles', function($q) {
    $q->where('name', 'Administrador general');
})->get();

echo "=== USUARIOS CON ROL 'Administrador general' ===\n\n";

if ($users->isEmpty()) {
    echo "NO SE ENCONTRÓ NINGÚN USUARIO CON ROL 'Administrador general'\n\n";
    echo "Roles disponibles:\n";
    $roles = Spatie\Permission\Models\Role::all();
    foreach ($roles as $role) {
        echo "  - {$role->name}\n";
    }
} else {
    foreach ($users as $user) {
        echo "Email: {$user->email}\n";
        echo "ID: {$user->id}\n";
        echo "Verificado: " . ($user->email_verified_at ? 'SÍ' : 'NO') . "\n";
        echo "Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
        echo "hasRole('Administrador general'): " . ($user->hasRole('Administrador general') ? 'SÍ' : 'NO') . "\n";
        echo "\n";
    }
}
