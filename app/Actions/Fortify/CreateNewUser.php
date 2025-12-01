<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Egresado;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'nombre' => [
                'required', 
                'string', 
                'max:255',
                'regex:/^[A-ZÁÉÍÓÚÑÜ][a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/', // Debe iniciar con mayúscula
            ],
            'apellidos' => [
                'required', 
                'string', 
                'max:255',
                'regex:/^[A-ZÁÉÍÓÚÑÜ][a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/', // Debe iniciar con mayúscula
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'unidad_id' => ['required', 'integer', 'exists:unidad,id'],
            'carrera_id' => ['required', 'integer', 'exists:carrera,id'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'estado_origen' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'string', 'in:Estudiantes,Egresados'],
            'anio_egreso' => ['nullable', 'integer', 'min:1980', 'max:' . date('Y'), 'required_if:user_type,Egresados'],
        ], [
            'nombre.regex' => 'El nombre debe iniciar con mayúscula y solo puede contener letras y espacios.',
            'apellidos.regex' => 'Los apellidos deben iniciar con mayúscula y solo pueden contener letras y espacios.',
            'unidad_id.required' => 'Debes seleccionar una unidad.',
            'carrera_id.required' => 'Debes seleccionar una carrera.',
            'user_type.in' => 'Tipo de usuario no válido.',
            'anio_egreso.required_if' => 'El año de egreso es obligatorio para egresados.',
            'anio_egreso.min' => 'El año de egreso debe ser mayor a 1980.',
            'anio_egreso.max' => 'El año de egreso no puede ser mayor al año actual.',
        ])->validate();

        $user = User::create([
            'name' => $input['nombre'] . ' ' . $input['apellidos'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Asignar el rol según el tipo de usuario seleccionado
        $user->assignRole($input['user_type']);

        // Crear o actualizar registro de egresado con unidad y carrera
        $egresado = Egresado::where('email', $input['email'])->first();

        if (!$egresado) {
            Egresado::create([
                'user_id' => $user->id,
                'email' => $input['email'],
                'nombre' => $input['nombre'],
                'apellidos' => $input['apellidos'],
                'fecha_nacimiento' => $input['fecha_nacimiento'] ?? null,
                'estado_origen' => $input['estado_origen'] ?? null,
                'unidad_id' => $input['unidad_id'],
                'carrera_id' => $input['carrera_id'],
                'anio_egreso' => $input['anio_egreso'] ?? null,
                'estatus_id' => 1, // Estatus activo por defecto
            ]);
        } else {
            // Actualizar datos del egresado
            $egresado->user_id = $user->id;
            $egresado->nombre = $egresado->nombre ?: $input['nombre'];
            $egresado->apellidos = $egresado->apellidos ?: $input['apellidos'];
            $egresado->fecha_nacimiento = $input['fecha_nacimiento'] ?? $egresado->fecha_nacimiento;
            $egresado->estado_origen = $input['estado_origen'] ?? $egresado->estado_origen;
            $egresado->unidad_id = $input['unidad_id'];
            $egresado->carrera_id = $input['carrera_id'];
            $egresado->anio_egreso = $input['anio_egreso'] ?? $egresado->anio_egreso;
            if (!$egresado->estatus_id) {
                $egresado->estatus_id = 1;
            }
            $egresado->save();
        }

        return $user;
    }
}
