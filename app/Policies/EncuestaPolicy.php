<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Encuesta;

class EncuestaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ver encuesta');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Encuesta $encuesta): bool
    {
        return $user->hasPermissionTo('ver_uno encuesta');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('encuestas.crear') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Encuesta $encuesta): bool
    {
        return $user->hasPermissionTo('encuestas.editar') || $user->hasAnyRole(['Administrador general', 'Administrador de unidad']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Encuesta $encuesta): bool
    {
        return $user->hasPermissionTo('encuestas.eliminar') || $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Encuesta $encuesta): bool
    {
        return $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Encuesta $encuesta): bool
    {
        return $user->hasRole('Administrador general');
    }
}
