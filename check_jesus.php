<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'jesus25020304@gmail.com')->first();

if ($user) {
    echo "=== USUARIO JESÚS ===\n";
    echo "Usuario: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "\nRoles:\n";
    foreach ($user->roles as $role) {
        echo "  - " . $role->name . "\n";
    }
    
    echo "\nPermisos totales: " . $user->getAllPermissions()->count() . "\n";
    
    echo "\n¿Tiene rol 'Administrador general'? ";
    echo $user->hasRole('Administrador general') ? 'SÍ' : 'NO';
    echo "\n";
    
    echo "\n¿Puede acceder a admin-academica? ";
    $canAccess = $user->hasRole('Administrador general') || $user->hasRole('Administrador academico');
    echo $canAccess ? 'SÍ' : 'NO';
    echo "\n";
} else {
    echo "Usuario jesus25020304@gmail.com NO ENCONTRADO\n";
}
