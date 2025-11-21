<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Egresado;

class TestComandoRoles extends Command
{
    protected $signature = 'test:comando-roles';
    protected $description = 'Prueba el comando de actualización masiva de roles';

    public function handle()
    {
        $this->info('🧪 PRUEBA DEL COMANDO DE ACTUALIZACIÓN DE ROLES');
        $this->info('==============================================');
        $this->newLine();

        // Crear 3 usuarios estudiantes con egresados validados
        $this->info('📝 Creando escenario de prueba...');
        $this->info('   - Creando 3 estudiantes validados en SICE');
        $this->newLine();

        $estudiantesCreados = [];

        for ($i = 1; $i <= 3; $i++) {
            $email = "estudiante.test{$i}@example.com";
            
            // Crear usuario
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "Estudiante Test {$i}",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            // Asegurar que tenga rol Estudiantes
            $user->syncRoles(['Estudiantes']);
            
            // Crear egresado validado (sin disparar el Observer)
            Egresado::withoutEvents(function() use ($email, $i) {
                Egresado::updateOrCreate(
                    ['email' => $email],
                    [
                        'matricula' => 'TEST' . (1000 + $i),
                        'nombre' => 'Estudiante',
                        'apellidos' => "Test {$i}",
                        'validado_sice' => true, // Ya validado en SICE
                        'estatus_id' => 1,
                    ]
                );
            });
            
            $estudiantesCreados[] = $email;
            $this->line("   ✓ Creado: {$email}");
        }

        $this->newLine();
        $this->info('✅ Escenario de prueba creado correctamente');
        $this->info('   - 3 usuarios con rol "Estudiantes"');
        $this->info('   - 3 egresados con validado_sice = true');
        $this->newLine();

        // Mostrar estado actual
        $this->info('📊 Estado actual:');
        foreach ($estudiantesCreados as $email) {
            $user = User::where('email', $email)->first();
            $rol = $user->roles->pluck('name')->first();
            $this->line("   - {$email}: {$rol}");
        }
        $this->newLine();

        // Ejecutar el comando en modo dry-run
        $this->warn('🔍 Ejecutando comando en modo DRY-RUN...');
        $this->call('egresados:actualizar-roles', ['--dry-run' => true]);
        $this->newLine();

        // Preguntar si ejecutar el comando real
        if ($this->confirm('¿Deseas ejecutar el comando real para actualizar los roles?', true)) {
            $this->newLine();
            $this->warn('🔄 Ejecutando comando real...');
            $this->call('egresados:actualizar-roles', ['--force' => true]);
            $this->newLine();

            // Verificar cambios
            $this->info('✨ Verificando cambios:');
            foreach ($estudiantesCreados as $email) {
                $user = User::where('email', $email)->first();
                $user->refresh();
                $rol = $user->roles->pluck('name')->first();
                
                if ($rol === 'Egresados') {
                    $this->info("   ✅ {$email}: {$rol}");
                } else {
                    $this->error("   ❌ {$email}: {$rol} (no cambió)");
                }
            }
        } else {
            $this->warn('⚠️  Prueba cancelada por el usuario');
        }

        $this->newLine();
        $this->comment('💡 Tip: Los usuarios de prueba quedan en la BD.');
        $this->comment('   Para limpiarlos manualmente si lo deseas.');
        $this->newLine();

        return Command::SUCCESS;
    }
}
