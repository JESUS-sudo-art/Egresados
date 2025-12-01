<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Generacion;

class GeneracionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador general', 'Administrador de unidad', 'Administrador academico']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Generacion $generacion): bool
    {
        return $user->hasAnyRole(['Administrador general', 'Administrador de unidad', 'Administrador academico']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('generaciones.crear') || $user->hasAnyRole(['Administrador general', 'Administrador academico']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Generacion $generacion): bool
    {
        return $user->hasPermissionTo('generaciones.editar') || $user->hasAnyRole(['Administrador general', 'Administrador academico']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Generacion $generacion): bool
    {
        return $user->hasPermissionTo('generaciones.eliminar') || $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Generacion $generacion): bool
    {
        return $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Generacion $generacion): bool
    {
        return $user->hasRole('Administrador general');
    }
}
