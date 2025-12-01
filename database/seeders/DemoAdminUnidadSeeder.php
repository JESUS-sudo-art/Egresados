<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DemoAdminUnidadSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles and permissions tables are ready
        // Create or retrieve the role 'Administrador de unidad'
        $role = Role::firstOrCreate(['name' => 'Administrador de unidad']);

        // Ensure key permissions exist for the demo and collect all current permissions
        $seedPermissions = [
            'encuestas.crear',
            'encuestas.editar',
            'encuestas.eliminar',
            'dimensiones.ver',
            'preguntas.ver',
            'reportes.exportar',
        ];
        foreach ($seedPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
        // Attach ALL permissions available to the role for the demo
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $role->syncPermissions($allPermissions);

        // Create or update the demo user
        $email = 'admin.unidad@demo';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Admin Unidad Demo',
                'email' => $email,
                'password' => Hash::make('demo1234'),
                'email_verified_at' => now(),
            ]);
        } else {
            // Ensure email verified and reset password for demo
            $user->email_verified_at = now();
            $user->password = Hash::make('demo1234');
            $user->save();
        }

        // Assign role and direct permissions
        if (!$user->hasRole($role->name)) {
            $user->assignRole($role);
        }

        $user->syncPermissions($allPermissions);

        // Also ensure the real Admin Unidad user is ready for demo
        $realEmail = 'eliza8159@gmail.com';
        $real = User::where('email', $realEmail)->first();
        if ($real) {
            $real->email_verified_at = now();
            $real->save();
            if (!$real->hasRole($role->name)) {
                $real->assignRole($role);
            }
            $real->syncPermissions($allPermissions);
        }
    }
}
