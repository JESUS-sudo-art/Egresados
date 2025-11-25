<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;

echo "Creando rol 'Estudiantes'...\n";

try {
    $role = Role::firstOrCreate(['name' => 'Estudiantes']);
    
    if ($role->wasRecentlyCreated) {
        echo "✓ Rol 'Estudiantes' creado exitosamente (id: {$role->id})\n";
    } else {
        echo "⚠ El rol 'Estudiantes' ya existía (id: {$role->id})\n";
    }
    
    echo "\nRoles actuales:\n";
    echo "================\n";
    foreach (Role::all() as $r) {
        echo "- {$r->name} (id: {$r->id})\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
