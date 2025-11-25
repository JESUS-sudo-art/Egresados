<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$user = User::where('email', 'el4643874@gmail.com')->first();

if ($user) {
    echo "Usuario encontrado: {$user->name}\n";
    echo "Roles actuales: " . $user->roles->pluck('name')->implode(', ') . "\n\n";
    
    // Sincronizar solo con el rol de Estudiantes
    $user->syncRoles(['Estudiantes']);
    
    echo "Roles actualizados: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "✅ Roles actualizados correctamente.\n";
} else {
    echo "❌ Usuario no encontrado.\n";
}
