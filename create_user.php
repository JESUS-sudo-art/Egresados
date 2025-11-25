<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Egresado;

if ($argc < 4) {
    echo "Usage: php create_user.php <email> <name> <password> [role]" . PHP_EOL;
    echo "Example: php create_user.php user@example.com 'Juan Perez' 'Temp1234!' Egresados" . PHP_EOL;
    exit(1);
}

$email = $argv[1];
$name = $argv[2];
$password = $argv[3];
$role = $argv[4] ?? 'Egresados';

$user = User::where('email', $email)->first();

if (!$user) {
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
    ]);
    echo "✓ Usuario creado: {$user->email}" . PHP_EOL;
} else {
    echo "• Usuario ya existía: {$user->email}. Actualizando nombre/contraseña..." . PHP_EOL;
    $user->name = $name ?: $user->name;
    if ($password) {
        $user->password = Hash::make($password);
    }
    $user->save();
}

// Asignar rol si no lo tiene
if (!$user->hasRole($role)) {
    $user->assignRole($role);
    echo "✓ Rol asignado: {$role}" . PHP_EOL;
} else {
    echo "• Usuario ya tiene el rol: {$role}" . PHP_EOL;
}

// Asegurar registro egresado si el rol implica egresado/estudiante
if (in_array($role, ['Egresados','Estudiantes'])) {
    $eg = Egresado::where('email', $email)->first();
    $parts = explode(' ', $name, 2);
    $nombre = $parts[0] ?? '';
    $apellidos = $parts[1] ?? '';

    if (!$eg) {
        Egresado::create([
            'email' => $email,
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'estatus_id' => 1,
        ]);
        echo "✓ Registro egresado creado" . PHP_EOL;
    } else {
        $eg->nombre = $eg->nombre ?: $nombre;
        $eg->apellidos = $eg->apellidos ?: $apellidos;
        if (!$eg->estatus_id) { $eg->estatus_id = 1; }
        $eg->save();
        echo "• Registro egresado actualizado" . PHP_EOL;
    }
}

echo "Listo." . PHP_EOL;
