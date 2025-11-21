<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('email', 'elizabethhlopezs3@gmail.com')->first();

if ($user) {
    echo "Usuario existe: " . $user->name . PHP_EOL;
    echo "Roles: " . $user->roles->pluck('name')->join(', ') . PHP_EOL;
    echo "ID: " . $user->id . PHP_EOL;
} else {
    echo "Usuario NO existe" . PHP_EOL;
}
