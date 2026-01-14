<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

use App\Models\User;

// Cambiar el email del usuario actual
$user = User::find(9); // ID de daniel25012025@gmail.com

if ($user) {
    $oldEmail = $user->email;
    $user->email = 'zura_jda@hotmail.com';
    $user->save();
    
    echo "✓ Email cambiado exitosamente\n";
    echo "  De: $oldEmail\n";
    echo "  A: zura_jda@hotmail.com\n";
} else {
    echo "✗ Usuario no encontrado\n";
}
