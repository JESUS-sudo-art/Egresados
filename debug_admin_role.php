<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Verificar usuarios con rol Admin General
$admins = App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'Admin General');
})->get();

echo "Usuarios con rol 'Admin General':\n";
foreach ($admins as $admin) {
    echo "- {$admin->email} (ID: {$admin->id})\n";
    echo "  Roles: " . $admin->roles->pluck('name')->join(', ') . "\n";
}

// Verificar el usuario actual si está autenticado
if (auth()->check()) {
    $user = auth()->user();
    echo "\nUsuario autenticado actual:\n";
    echo "- {$user->email}\n";
    echo "  Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
    echo "  hasRole('Admin General'): " . ($user->hasRole('Admin General') ? 'Sí' : 'No') . "\n";
}

echo "\nVerificación completada.\n";
