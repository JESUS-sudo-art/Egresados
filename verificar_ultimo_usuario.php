<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "Verificando último usuario registrado...\n";
echo "=========================================\n\n";

$ultimoUsuario = User::latest('id')->first();

if (!$ultimoUsuario) {
    echo "No hay usuarios en la base de datos.\n";
    exit;
}

echo "ID: {$ultimoUsuario->id}\n";
echo "Nombre: {$ultimoUsuario->name}\n";
echo "Email: {$ultimoUsuario->email}\n";
echo "Creado: {$ultimoUsuario->created_at}\n\n";

echo "Roles asignados:\n";
$roles = $ultimoUsuario->roles;

if ($roles->isEmpty()) {
    echo "⚠ Este usuario NO tiene roles asignados!\n";
} else {
    foreach ($roles as $role) {
        echo "- {$role->name} (id: {$role->id})\n";
    }
}

echo "\nPermisos del usuario:\n";
$permisos = $ultimoUsuario->getAllPermissions();
if ($permisos->isEmpty()) {
    echo "- Sin permisos directos\n";
} else {
    foreach ($permisos as $permiso) {
        echo "- {$permiso->name}\n";
    }
}
