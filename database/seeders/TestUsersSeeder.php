<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Administrador General (ya existe)
        $adminGeneral = User::firstOrCreate(
            ['email' => 'jortega8159@gmail.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminGeneral->syncRoles(['Administrador general']);

        // Usuario Administrador de Unidad
        $adminUnidad = User::firstOrCreate(
            ['email' => 'admin.unidad@example.com'],
            [
                'name' => 'Admin Unidad',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminUnidad->syncRoles(['Administrador de unidad']);

        // Usuario Administrador Académico
        $adminAcademico = User::firstOrCreate(
            ['email' => 'admin.academico@example.com'],
            [
                'name' => 'Admin Académico',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminAcademico->syncRoles(['Administrador academico']);

        $this->command->info('✅ Usuarios de prueba creados:');
        $this->command->info('   - jortega8159@gmail.com (Administrador general) - password');
        $this->command->info('   - admin.unidad@example.com (Administrador de unidad) - password');
        $this->command->info('   - admin.academico@example.com (Administrador academico) - password');
    }
}
