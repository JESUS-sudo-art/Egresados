<?php

namespace App\Observers;

use App\Models\Egresado;
use App\Models\CedulaPreegreso;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EgresadoObserver
{
    /**
     * Handle the Egresado "updated" event.
     * 
     * Se ejecuta automáticamente cuando se actualiza un registro de Egresado.
     * Si el campo validado_sice cambia a true (1), cambia el rol del usuario
     * de "Estudiantes" a "Egresados".
     */
    public function updated(Egresado $egresado): void
    {
        // Verificar si el campo validado_sice cambió a true
        if ($egresado->isDirty('validado_sice') && $egresado->validado_sice) {
            $user = $egresado->user;
            
            if ($user) {
                // Verificar si el usuario tiene el rol de Estudiantes
                if ($user->hasRole('Estudiantes')) {
                    // Cambiar de Estudiantes a Egresados
                    $user->syncRoles(['Egresados']);
                    
                    Log::info("Usuario {$user->email} cambió de rol Estudiantes a Egresados (validado en SICE)", [
                        'egresado_id' => $egresado->id,
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                }
            } else {
                Log::warning("Egresado validado en SICE pero no se encontró usuario asociado", [
                    'egresado_id' => $egresado->id,
                    'email' => $egresado->email,
                ]);
            }
        }
        
        // Sincronizar con pre-egreso si tiene cambios en telefono o fecha_nacimiento
        $this->sincronizarConPreegreso($egresado);
    }

    /**
     * Handle the Egresado "created" event.
     * 
     * Opcionalmente, también puedes manejar cuando se crea un egresado
     * ya validado desde el inicio.
     */
    public function created(Egresado $egresado): void
    {
        // Si se crea un egresado ya validado, cambiar el rol inmediatamente
        if ($egresado->validado_sice) {
            $user = $egresado->user;
            
            if ($user && $user->hasRole('Estudiantes')) {
                $user->syncRoles(['Egresados']);
                
                Log::info("Usuario {$user->email} cambió de rol Estudiantes a Egresados (creado validado)", [
                    'egresado_id' => $egresado->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
        }
    }
    
    /**
     * Sincronizar datos del egresado con la cédula pre-egreso
     */
    private function sincronizarConPreegreso(Egresado $egresado): void
    {
        try {
            // Verificar si hay cambios en campos importantes
            $cambios = $egresado->getChanges();
            
            // Solo procesar si hay cambios en telefono o fecha_nacimiento
            if (!isset($cambios['telefono']) && !isset($cambios['fecha_nacimiento'])) {
                return;
            }
            
            // Buscar cédula pre-egreso asociada
            $cedula = CedulaPreegreso::where('egresado_id', $egresado->id)->first();
            
            if (!$cedula) {
                // Si no existe cédula pre-egreso aún, no hacer nada
                return;
            }
            
            // Preparar datos a actualizar
            $updates = [];
            $params = [];
            
            if (isset($cambios['telefono']) && $egresado->telefono) {
                $updates[] = "`telefono_contacto` = ?";
                $params[] = $egresado->telefono;
            }
            
            if (isset($cambios['fecha_nacimiento']) && $egresado->fecha_nacimiento) {
                try {
                    $edad = Carbon::parse($egresado->fecha_nacimiento)->age;
                    if ($edad >= 10 && $edad <= 100) {
                        $updates[] = "`edad` = ?";
                        $params[] = $edad;
                    }
                } catch (\Exception $e) {
                    // Ignorar errores de parseo de fecha
                }
            }
            
            if (!empty($updates)) {
                // Usar raw SQL para evitar prepared statement issues
                $params[] = $cedula->id;
                $sql = "UPDATE `cedula_preegreso` SET " . implode(', ', $updates) . " WHERE `id` = ?";
                DB::update($sql, $params);
                
                Log::info('Egresado sincronizado con pre-egreso', [
                    'egresado_id' => $egresado->id,
                    'cambios' => $cambios
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error sincronizando egresado con pre-egreso', [
                'error' => $e->getMessage(),
                'egresado_id' => $egresado->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
