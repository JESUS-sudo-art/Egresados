<?php

use Illuminate\Support\Facades\Route;

Route::get('/cambiar-email-zura', function () {
    $user = \App\Models\User::find(9);
    
    if (!$user) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }
    
    $old_email = $user->email;
    $user->email = 'zura_jda@hotmail.com';
    $user->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Email cambiado exitosamente',
        'old_email' => $old_email,
        'new_email' => $user->email,
    ]);
});
