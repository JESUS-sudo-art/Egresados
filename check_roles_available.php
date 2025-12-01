<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ROLES DISPONIBLES EN EL SISTEMA ===\n\n";
$roles = Spatie\Permission\Models\Role::all();
foreach ($roles as $role) {
    echo "- {$role->name} (ID: {$role->id})\n";
}

echo "\n=== USUARIOS Y SUS ROLES ===\n\n";
$users = App\Models\User::with('roles')->get();
foreach ($users as $user) {
    echo "Usuario: {$user->email} (ID: {$user->id})\n";
    if ($user->roles->count() > 0) {
        echo "  Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
    } else {
        echo "  Sin roles asignados\n";
    }
}
