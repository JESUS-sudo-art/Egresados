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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            // Registro público solo para Estudiantes y Egresados
            'user_type' => ['required', 'string', 'in:Estudiantes,Egresados'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Asignar el rol según el tipo de usuario seleccionado
        $user->assignRole($input['user_type']);

        // Si es Egresado o Estudiante, asegurar existencia en tabla egresado (sin duplicar)
        if (in_array($input['user_type'], ['Egresados', 'Estudiantes'])) {
            $egresado = Egresado::where('email', $input['email'])->first();

            // Separar nombre y apellidos (asumiendo formato "Nombre Apellidos")
            $nameParts = explode(' ', $input['name'], 2);
            $nombre = $nameParts[0] ?? '';
            $apellidos = $nameParts[1] ?? '';

            if (!$egresado) {
                Egresado::create([
                    'email' => $input['email'],
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'estatus_id' => 1, // Estatus activo por defecto
                ]);
            } else {
                // Actualizar nombre/apellidos si están vacíos, sin alterar otros datos
                $egresado->nombre = $egresado->nombre ?: $nombre;
                $egresado->apellidos = $egresado->apellidos ?: $apellidos;
                if (!$egresado->estatus_id) {
                    $egresado->estatus_id = 1;
                }
                $egresado->save();
            }
        }

        return $user;
    }
}
