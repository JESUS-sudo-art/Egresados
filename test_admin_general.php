<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'rsd1desarrolloweb@uabjo.mx';

$user = App\Models\User::where('email', $email)->first();

if ($user) {
    echo "Usuario encontrado: " . $user->name . PHP_EOL;
    echo "Roles: " . $user->roles->pluck('name')->join(', ') . PHP_EOL;
    echo "ID: " . $user->id . PHP_EOL;
} else {
    echo "Usuario NO encontrado" . PHP_EOL;
}
