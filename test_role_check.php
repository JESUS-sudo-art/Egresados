<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simular una petición a la ruta
$user = App\Models\User::where('email', 'jesus25020304@gmail.com')->first();

if (!$user) {
    die("Usuario no encontrado\n");
}

echo "Usuario encontrado: " . $user->name . "\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n\n";

// Verificar con el middleware CheckRole
$checkRole = new App\Http\Middleware\CheckRole();

// Simular roles requeridos
$rolesRequeridos = ['Administrador academico', 'Administrador general'];

echo "Roles requeridos: " . implode(', ', $rolesRequeridos) . "\n";
echo "¿Tiene alguno de estos roles? ";
echo $user->hasAnyRole($rolesRequeridos) ? "SÍ\n" : "NO\n";

// Verificar uno por uno
foreach ($rolesRequeridos as $rol) {
    echo "  - ¿Tiene rol '$rol'? " . ($user->hasRole($rol) ? "SÍ" : "NO") . "\n";
}
