<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Opcion;

class OpcionPolicy
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
    public function view(User $user, Opcion $opcion): bool
    {
        return $user->hasAnyRole(['Administrador general', 'Administrador de unidad', 'Administrador academico']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Crear/editar opciones se asocia a gestionar preguntas
        return $user->hasPermissionTo('preguntas.editar') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Opcion $opcion): bool
    {
        return $user->hasPermissionTo('preguntas.editar') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Opcion $opcion): bool
    {
        return $user->hasPermissionTo('preguntas.editar') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Opcion $opcion): bool
    {
        return $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Opcion $opcion): bool
    {
        return $user->hasRole('Administrador general');
    }
}
