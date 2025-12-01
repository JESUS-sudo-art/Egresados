<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Dimension;

class DimensionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('dimensiones.ver') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad', 'Administrador academico']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dimension $dimension): bool
    {
        return $user->hasPermissionTo('dimensiones.ver') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad', 'Administrador academico']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('dimensiones.crear') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dimension $dimension): bool
    {
        return $user->hasPermissionTo('dimensiones.editar') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dimension $dimension): bool
    {
        return $user->hasPermissionTo('dimensiones.eliminar') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dimension $dimension): bool
    {
        return $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dimension $dimension): bool
    {
        return $user->hasRole('Administrador general');
    }
}
