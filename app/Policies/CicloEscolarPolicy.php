<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CicloEscolar;

class CicloEscolarPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador general', 'Administrador academico']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CicloEscolar $cicloEscolar): bool
    {
        return $user->hasAnyRole(['Administrador general', 'Administrador academico']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('ciclos.crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CicloEscolar $cicloEscolar): bool
    {
        return $user->hasPermissionTo('ciclos.editar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CicloEscolar $cicloEscolar): bool
    {
        return $user->hasPermissionTo('ciclos.eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CicloEscolar $cicloEscolar): bool
    {
        return $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CicloEscolar $cicloEscolar): bool
    {
        return $user->hasRole('Administrador general');
    }
}
