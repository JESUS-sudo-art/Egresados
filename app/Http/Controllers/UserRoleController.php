<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class UserRoleController extends Controller
{
    /**
     * Display a listing of users with their roles.
     */
    public function index()
    {
        // Verificar que el usuario tenga rol administrativo
        if (!auth()->user()->hasAnyRole(['Administrador general', 'Administrador de unidad'])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $users = User::with('roles')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ];
        });

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
            ];
        });

        return Inertia::render('Users/RoleManager', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Assign or sync roles to a user.
     */
    public function assignRole(Request $request, User $user)
    {
        // Solo Administrador general puede asignar roles
        if (!auth()->user()->hasRole('Administrador general')) {
            abort(403, 'Solo el Administrador general puede asignar roles.');
        }

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        // Sincronizar roles (reemplaza los roles existentes)
        $user->syncRoles($validated['roles']);

        return back()->with('success', 'Roles asignados correctamente al usuario.');
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(Request $request, User $user)
    {
        if (!auth()->user()->hasRole('Administrador general')) {
            abort(403, 'Solo el Administrador general puede remover roles.');
        }

        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->removeRole($validated['role']);

        return back()->with('success', 'Rol removido correctamente.');
    }
}
