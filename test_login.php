<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'elizabethhlopezs3@gmail.com';
$password = '12345678'; // Cambia esto por la contraseña correcta

$user = App\Models\User::where('email', $email)->first();

if ($user) {
    echo "Usuario encontrado: " . $user->name . PHP_EOL;
    echo "Roles: " . $user->roles->pluck('name')->join(', ') . PHP_EOL;
    
    if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
        echo "✓ La contraseña es correcta" . PHP_EOL;
    } else {
        echo "✗ La contraseña es INCORRECTA" . PHP_EOL;
    }
    
    $adminRoles = ['Administrador general', 'Administrador de unidad', 'Administrador academico'];
    $hasAdminRole = $user->hasAnyRole($adminRoles);
    
    if ($hasAdminRole) {
        echo "✓ Es usuario administrador - SE REQUIERE CÓDIGO" . PHP_EOL;
        $expectedCode = config('app.admin_registration_code');
        echo "Código esperado: " . $expectedCode . PHP_EOL;
    } else {
        echo "✗ NO es usuario administrador" . PHP_EOL;
    }
} else {
    echo "Usuario NO encontrado" . PHP_EOL;
}
