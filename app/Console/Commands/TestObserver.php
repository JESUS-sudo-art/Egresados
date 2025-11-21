<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Egresado;

class TestObserver extends Command
{
    protected $signature = 'test:observer';
    protected $description = 'Prueba el Observer de cambio de roles Estudiantes → Egresados';

    public function handle()
    {
        $this->info('🧪 PRUEBA DEL OBSERVER DE EGRESADO');
        $this->info('====================================');
        $this->newLine();

        // 1. Buscar un usuario con rol Estudiantes
        $estudiante = User::whereHas('roles', function($q) {
            $q->where('name', 'Estudiantes');
        })->first();

        if (!$estudiante) {
            $this->warn('❌ No se encontró ningún usuario con rol Estudiantes.');
            $this->info('Creando usuario de prueba...');
            $this->newLine();
            
            $estudiante = User::create([
                'name' => 'Estudiante Prueba',
                'email' => 'estudiante.prueba@test.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $estudiante->assignRole('Estudiantes');
            $this->info("✅ Usuario creado: {$estudiante->email}");
            $this->newLine();
        }

        $this->info('👤 Usuario encontrado:');
        $this->line("   - Email: {$estudiante->email}");
        $this->line("   - Nombre: {$estudiante->name}");
        $this->line("   - Rol actual: " . $estudiante->roles->pluck('name')->implode(', '));
        $this->newLine();

        // 2. Buscar o crear egresado asociado
        $egresado = Egresado::where('email', $estudiante->email)->first();

        if (!$egresado) {
            $this->info('📝 Creando registro de egresado...');
            $egresado = Egresado::create([
                'matricula' => 'TEST' . rand(1000, 9999),
                'nombre' => 'Estudiante',
                'apellidos' => 'Prueba Test',
                'email' => $estudiante->email,
                'validado_sice' => false,
                'estatus_id' => 1,
            ]);
            $this->info("✅ Egresado creado con ID: {$egresado->id}");
            $this->newLine();
        } else {
            $this->info("📋 Egresado encontrado con ID: {$egresado->id}");
            // Asegurar que validado_sice esté en false para la prueba
            if ($egresado->validado_sice) {
                $egresado->validado_sice = false;
                $egresado->save();
                $this->warn("   ⚠️  Se reseteo validado_sice a false para la prueba");
            }
            $this->newLine();
        }

        $this->info('🔄 Estado actual del egresado:');
        $this->line("   - ID: {$egresado->id}");
        $this->line("   - Nombre: {$egresado->nombre} {$egresado->apellidos}");
        $this->line("   - Email: {$egresado->email}");
        $this->line("   - Validado SICE: " . ($egresado->validado_sice ? 'SÍ' : 'NO'));
        $this->newLine();

        $this->warn('⏳ Actualizando validado_sice = true (esto disparará el Observer)...');
        $this->newLine();

        // 3. Actualizar validado_sice (esto dispara el Observer)
        $egresado->validado_sice = true;
        $egresado->save();

        // Pequeña pausa para asegurar que el observer se ejecutó
        sleep(1);

        // 4. Recargar usuario para ver cambios
        $estudiante->refresh();

        $this->info('✨ RESULTADO:');
        $this->info('=====================================');
        $this->line("👤 Usuario: {$estudiante->email}");
        $this->line("🎓 Rol anterior: Estudiantes");
        $this->line("🎓 Rol actual: " . $estudiante->roles->pluck('name')->implode(', '));
        $this->newLine();

        if ($estudiante->hasRole('Egresados')) {
            $this->info('✅ ¡ÉXITO! El Observer cambió el rol correctamente.');
            $this->info('   El usuario ahora tiene rol de Egresados.');
        } else {
            $this->error('❌ ERROR: El rol no cambió.');
            $this->warn('   Revisa los logs en storage/logs/laravel.log');
        }

        $this->newLine();
        $this->comment('📝 Para ver los logs detallados:');
        $this->comment('   tail -20 storage/logs/laravel.log');
        $this->newLine();

        return Command::SUCCESS;
    }
}
