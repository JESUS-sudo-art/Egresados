<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Egresado;
use App\Models\User;

class ActualizarRolesEgresados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'egresados:actualizar-roles 
                            {--dry-run : Ejecutar en modo de prueba sin hacer cambios reales}
                            {--force : Forzar la actualización sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza roles de usuarios de Estudiantes a Egresados basándose en validación SICE';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info('🔍 Buscando egresados validados en SICE con rol de Estudiantes...');
        
        // Buscar egresados que:
        // 1. Estén validados en SICE (validado_sice = 1)
        // 2. Tengan un usuario asociado con rol de "Estudiantes"
        $egresados = Egresado::where('validado_sice', true)
            ->whereHas('user', function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'Estudiantes');
                });
            })
            ->with('user')
            ->get();

        if ($egresados->isEmpty()) {
            $this->info('✅ No se encontraron estudiantes validados para actualizar.');
            return Command::SUCCESS;
        }

        $this->info("📋 Se encontraron {$egresados->count()} estudiante(s) validado(s) para cambiar a rol Egresados:");
        $this->newLine();

        // Mostrar tabla con los usuarios a actualizar
        $tableData = [];
        foreach ($egresados as $egresado) {
            $tableData[] = [
                $egresado->id,
                $egresado->nombre . ' ' . $egresado->apellidos,
                $egresado->email,
                $egresado->user->name ?? 'N/A',
            ];
        }

        $this->table(
            ['ID Egresado', 'Nombre Completo', 'Email', 'Usuario'],
            $tableData
        );

        if ($isDryRun) {
            $this->warn('🧪 Modo DRY-RUN: No se realizarán cambios reales.');
            return Command::SUCCESS;
        }

        // Solicitar confirmación si no es forzado
        if (!$isForce) {
            if (!$this->confirm('¿Deseas continuar con el cambio de roles?', true)) {
                $this->warn('⚠️  Operación cancelada por el usuario.');
                return Command::FAILURE;
            }
        }

        $this->newLine();
        $this->info('🔄 Actualizando roles...');
        
        $actualizados = 0;
        $errores = 0;

        foreach ($egresados as $egresado) {
            try {
                $user = $egresado->user;
                
                if ($user) {
                    // Cambiar de Estudiantes a Egresados
                    $user->syncRoles(['Egresados']);
                    $actualizados++;
                    
                    $this->info("  ✓ {$user->email} → Rol actualizado a Egresados");
                } else {
                    $this->warn("  ⚠ Egresado {$egresado->id} no tiene usuario asociado");
                    $errores++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error al actualizar {$egresado->email}: {$e->getMessage()}");
                $errores++;
            }
        }

        $this->newLine();
        $this->info("✅ Proceso completado:");
        $this->info("   - Usuarios actualizados: {$actualizados}");
        
        if ($errores > 0) {
            $this->warn("   - Errores encontrados: {$errores}");
        }

        return Command::SUCCESS;
    }
}
