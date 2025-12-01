<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'armando345@gmail.com')->first();

if ($user) {
    echo "=== ESTADO ACTUAL DEL USUARIO ===\n";
    echo "Usuario: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "\nRoles directos:\n";
    foreach ($user->roles as $role) {
        echo "  - " . $role->name . "\n";
    }
    
    echo "\nPermisos del usuario:\n";
    $permisos = $user->getAllPermissions()->pluck('name');
    echo "Total: " . $permisos->count() . "\n";
    
    echo "\n¿Tiene rol 'Administrador general'? ";
    echo $user->hasRole('Administrador general') ? 'SÍ' : 'NO';
    echo "\n";
    
    echo "\n¿Tiene permiso 'unidades.ver'? ";
    echo $user->hasPermissionTo('unidades.ver') ? 'SÍ' : 'NO';
    echo "\n";
    
    echo "\n=== VERIFICAR SESIONES ===\n";
    echo "Última actualización: " . $user->updated_at . "\n";
}
