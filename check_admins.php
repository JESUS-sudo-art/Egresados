<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Usuarios con rol Administrador general ===" . PHP_EOL . PHP_EOL;

$users = App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'Administrador general');
})->get();

if ($users->count() > 0) {
    foreach ($users as $user) {
        echo "ID: {$user->id} | Nombre: {$user->name} | Email: {$user->email}" . PHP_EOL;
    }
} else {
    echo "No hay usuarios con rol Administrador general" . PHP_EOL;
}

echo PHP_EOL . "=== Todos los roles disponibles ===" . PHP_EOL . PHP_EOL;
$roles = Spatie\Permission\Models\Role::all();
foreach ($roles as $role) {
    echo "- {$role->name}" . PHP_EOL;
}
