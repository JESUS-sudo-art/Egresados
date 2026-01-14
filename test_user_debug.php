<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

use App\Models\User;
use App\Models\Egresado;

$user = User::where('email', 'daniel25012025@gmail.com')->first();
if ($user) {
    echo "✓ Usuario encontrado: " . $user->email . PHP_EOL;
    echo "  ID: " . $user->id . PHP_EOL;
    echo "  Roles: " . ($user->roles->count() > 0 ? $user->roles->pluck('name')->implode(', ') : 'SIN ROLES') . PHP_EOL;
} else {
    echo "✗ Usuario NO encontrado" . PHP_EOL;
    exit(1);
}

$egresado = Egresado::where('email', $user->email)->first();
if ($egresado) {
    echo "✓ Egresado encontrado" . PHP_EOL;
    echo "  ID: " . $egresado->id . PHP_EOL;
    echo "  Bitacoras: " . $egresado->bitacoras()->count() . PHP_EOL;
} else {
    echo "✗ Egresado NO encontrado" . PHP_EOL;
}
