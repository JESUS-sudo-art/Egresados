<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero crear roles y permisos
        $this->call([
            RolesAndPermissionsSeeder::class,
            CatalogosSeeder::class,
        ]);

        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'jortega8159@gmail.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            
        ]);

        // Asignar rol de Administrador general al usuario de prueba
        $user->assignRole('Administrador general');
    }
}
