<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Egresado;

class EgresadoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('egresados.ver') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad', 'Administrador academico']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Egresado $egresado): bool
    {
        // Puede ver si es admin o si es el mismo egresado
        return $user->hasAnyRole(['Administrador general', 'Administrador de unidad', 'Administrador academico']) 
            || $user->email === $egresado->email;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('egresados.crear') || $user->hasAnyRole(['Administrador general', 'Administrador academico']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Egresado $egresado): bool
    {
        // Puede actualizar si es admin o si es el mismo egresado
        return $user->hasPermissionTo('egresados.editar') || $user->hasAnyRole(['Administrador general', 'Administrador academico']) 
            || $user->email === $egresado->email;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Egresado $egresado): bool
    {
        return $user->hasPermissionTo('egresados.eliminar') || $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Egresado $egresado): bool
    {
        return $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Egresado $egresado): bool
    {
        return $user->hasRole('Administrador general');
    }
}
