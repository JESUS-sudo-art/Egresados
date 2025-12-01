<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// Buscar admin académico
$user = User::whereHas('roles', function($q) {
    $q->where('name', 'Administrador academico');
})->first();

if (!$user) {
    echo "NO SE ENCONTRÓ USUARIO CON ROL 'Administrador academico'\n";
    echo "Roles disponibles:\n";
    $roles = Spatie\Permission\Models\Role::all();
    foreach ($roles as $role) {
        echo "  - {$role->name}\n";
    }
    exit;
}

echo "Usuario encontrado: {$user->email}\n";
echo "ID: {$user->id}\n";
echo "Email verificado: " . ($user->email_verified_at ? 'SÍ' : 'NO') . "\n\n";

echo "Roles asignados:\n";
foreach ($user->roles as $role) {
    echo "  - {$role->name}\n";
}

echo "\nPermisos directos:\n";
$directPerms = $user->permissions;
if ($directPerms->count() > 0) {
    foreach ($directPerms as $perm) {
        echo "  - {$perm->name}\n";
    }
} else {
    echo "  (ninguno)\n";
}

echo "\nPermisos vía roles:\n";
$rolePerms = $user->getPermissionsViaRoles();
if ($rolePerms->count() > 0) {
    foreach ($rolePerms as $perm) {
        echo "  - {$perm->name}\n";
    }
} else {
    echo "  (ninguno)\n";
}

echo "\n¿Puede acceder a unidades.ver? " . ($user->can('unidades.ver') ? 'SÍ' : 'NO') . "\n";
echo "¿Puede acceder a carreras.ver? " . ($user->can('carreras.ver') ? 'SÍ' : 'NO') . "\n";
echo "¿Puede acceder a generaciones.ver? " . ($user->can('generaciones.ver') ? 'SÍ' : 'NO') . "\n";
