<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Egresado;

Route::get('/debug-respuestas-antiguas', function () {
    $user = Auth::user();
    
    if (!$user) {
        return response()->json([
            'status' => 'not_authenticated',
            'message' => 'Usuario no está logueado'
        ]);
    }
    
    $data = [
        'status' => 'success',
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'roles' => $user->roles->pluck('name')->toArray(),
        ]
    ];
    
    $egresado = Egresado::where('email', $user->email)->first();
    if ($egresado) {
        $data['egresado'] = [
            'id' => $egresado->id,
            'bitacoras_count' => $egresado->bitacoras()->count(),
        ];
    } else {
        $data['egresado'] = null;
        $data['warning'] = 'No se encontró egresado para este email';
    }
    
    return response()->json($data);
});
