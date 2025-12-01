<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Verificar que el middleware role existe
echo "=== VERIFICACIÓN DE MIDDLEWARE ===\n\n";

$user = App\Models\User::where('email', 'jesus25020304@gmail.com')->first();

if ($user) {
    echo "Usuario: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n\n";
    
    echo "Roles del usuario:\n";
    foreach ($user->roles as $role) {
        echo "  - '" . $role->name . "' (id: " . $role->id . ")\n";
    }
    
    echo "\n=== PRUEBA DE VALIDACIÓN DE ROL ===\n";
    echo "hasRole('Administrador general'): ";
    echo $user->hasRole('Administrador general') ? 'TRUE' : 'FALSE';
    echo "\n";
    
    echo "hasRole('Administrador academico'): ";
    echo $user->hasRole('Administrador academico') ? 'TRUE' : 'FALSE';
    echo "\n";
    
    echo "hasAnyRole(['Administrador academico', 'Administrador general']): ";
    echo $user->hasAnyRole(['Administrador academico', 'Administrador general']) ? 'TRUE' : 'FALSE';
    echo "\n";
    
    echo "\n=== VERIFICAR ROLES EN BD ===\n";
    $roles = Spatie\Permission\Models\Role::all();
    echo "Roles totales en sistema: " . $roles->count() . "\n";
    foreach ($roles as $role) {
        echo "  - '" . $role->name . "'\n";
    }
}
