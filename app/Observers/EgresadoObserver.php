<?php

namespace App\Observers;

use App\Models\Egresado;
use Illuminate\Support\Facades\Log;

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
}
