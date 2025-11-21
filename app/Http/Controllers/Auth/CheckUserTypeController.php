<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CheckUserTypeController extends Controller
{
    /**
     * Verifica si un email corresponde a un usuario administrador.
     */
    public function check(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'exists' => false,
                'is_admin' => false,
            ]);
        }

        $adminRoles = ['Administrador general', 'Administrador de unidad', 'Administrador academico'];
        $isAdmin = $user->hasAnyRole($adminRoles);

        return response()->json([
            'exists' => true,
            'is_admin' => $isAdmin,
        ]);
    }
}
