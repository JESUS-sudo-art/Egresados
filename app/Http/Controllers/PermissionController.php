<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display the permission manager interface.
     */
    public function index()
    {
        // Verificar que el usuario tenga rol administrativo
        if (!auth()->user()->hasAnyRole(['Administrador general', 'Administrador de unidad', 'Administrador academico'])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return Inertia::render('Permissions/Manager', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update permissions for a specific role.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        // Solo Administrador general puede modificar permisos
        if (!auth()->user()->hasRole('Administrador general')) {
            abort(403, 'Solo el Administrador general puede modificar permisos.');
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Sincronizar permisos del rol
        $role->syncPermissions($validated['permissions']);

        return back()->with('success', 'Permisos actualizados correctamente.');
    }

    /**
     * Get all roles with their permissions.
     */
    public function getRolesWithPermissions()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Assign role to a user.
     */
    public function assignRoleToUser(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Administrador general', 'Administrador de unidad'])) {
            abort(403, 'No tienes permisos para asignar roles.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $user = \App\Models\User::findOrFail($validated['user_id']);
        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'Rol asignado correctamente.');
    }
}
