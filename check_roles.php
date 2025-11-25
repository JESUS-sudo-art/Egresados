<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;

echo "Roles existentes en la base de datos:\n";
echo "=====================================\n";

$roles = Role::all();

if ($roles->isEmpty()) {
    echo "No hay roles registrados.\n";
} else {
    foreach ($roles as $role) {
        echo "- {$role->name} (id: {$role->id})\n";
    }
}

echo "\nTotal de roles: " . $roles->count() . "\n";
