<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'eliza8159@gmail.com')->first();

if (!$user) {
    echo "Usuario no encontrado\n";
    exit(1);
}

// Verificar email
$user->email_verified_at = now();
$user->save();

echo "✓ Email verificado\n";
echo "Usuario: {$user->name} ({$user->email})\n";
echo "Verificado: {$user->email_verified_at}\n";

// Verificar roles
$roles = $user->getRoleNames();
echo "Roles actuales: " . $roles->implode(', ') . "\n";

// Si no tiene rol, asignar Administrador de unidad
if ($roles->isEmpty() || !$roles->contains('Administrador de unidad')) {
    try {
        $user->assignRole('Administrador de unidad');
        echo "✓ Rol 'Administrador de unidad' asignado\n";
    } catch (\Exception $e) {
        echo "Error asignando rol: " . $e->getMessage() . "\n";
    }
}

// Dar permiso encuestas.ver
try {
    if (!\Spatie\Permission\Models\Permission::where('name', 'encuestas.ver')->exists()) {
        \Spatie\Permission\Models\Permission::create(['name' => 'encuestas.ver']);
    }
    $user->givePermissionTo('encuestas.ver');
    echo "✓ Permiso 'encuestas.ver' otorgado\n";
} catch (\Exception $e) {
    echo "Permiso ya existe o error: " . $e->getMessage() . "\n";
}

echo "\nUsuario listo para acceder a admin-unidad\n";
