<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole('Administrador general')) {
            abort(403, 'Solo el Administrador general puede gestionar roles.');
        }

        $roles = Role::orderBy('name')->get(['id', 'name', 'guard_name', 'created_at']);

        return Inertia::render('modules/Roles', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Administrador general')) {
            abort(403, 'Solo el Administrador general puede crear roles.');
        }

        $data = $request->validate([
            'name' => 'required|string|min:3|max:100|unique:roles,name',
        ]);

        Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        return back()->with('success', 'Rol creado correctamente.');
    }
}
