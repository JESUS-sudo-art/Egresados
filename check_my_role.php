<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'armando345@gmail.com')->first();

if ($user) {
    echo "Usuario: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "\nPermisos del rol 'Administrador general':\n";
    
    $role = Spatie\Permission\Models\Role::where('name', 'Administrador general')->first();
    if ($role) {
        echo "Total permisos: " . $role->permissions->count() . "\n";
        $permisos = $role->permissions->pluck('name')->take(10);
        echo "Primeros 10: " . $permisos->implode(', ') . "\n";
    }
} else {
    echo "Usuario no encontrado\n";
}
