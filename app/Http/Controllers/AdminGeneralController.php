<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AdminGeneralController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['unidades', 'roles'])->get()->map(function ($usuario) {
            return [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'roles' => $usuario->roles->pluck('name')->toArray(),
                'unidades' => $usuario->unidades->map(fn($u) => [
                    'id' => $u->id,
                    'nombre' => $u->nombre,
                ]),
            ];
        });

        $unidades = Unidad::where('estatus', 'A')->get()->map(function ($unidad) {
            return [
                'id' => $unidad->id,
                'nombre' => $unidad->nombre,
            ];
        });

        return Inertia::render('modules/AdminGeneral', [
            'usuarios' => $usuarios,
            'unidades' => $unidades,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'unidades' => 'nullable|array',
            'unidades.*' => 'exists:unidad,id',
        ]);

        $usuario = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['unidades'])) {
            $usuario->unidades()->attach($validated['unidades']);
        }

        return redirect()->back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => ['nullable', Password::min(8)],
            'unidades' => 'nullable|array',
            'unidades.*' => 'exists:unidad,id',
        ]);

        $usuario->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $usuario->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Sincronizar unidades
        if (isset($validated['unidades'])) {
            $usuario->unidades()->sync($validated['unidades']);
        } else {
            $usuario->unidades()->detach();
        }

        return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        
        // Eliminar relaciones con unidades
        $usuario->unidades()->detach();
        
        // Eliminar usuario
        $usuario->delete();

        return redirect()->back()->with('success', 'Usuario eliminado correctamente.');
    }
}
