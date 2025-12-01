<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Unidad;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnidadPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('unidades.ver') || $user->hasAnyRole(['Administrador general', 'Administrador academico']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Unidad $unidad): bool
    {
        return $user->hasPermissionTo('unidades.ver') || $user->hasAnyRole(['Administrador general', 'Administrador academico']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('unidades.crear');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Unidad $unidad): bool
    {
        return $user->hasPermissionTo('unidades.editar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Unidad $unidad): bool
    {
        return $user->hasPermissionTo('unidades.eliminar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Unidad $unidad): bool
    {
        return $user->hasRole('Administrador general');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Unidad $unidad): bool
    {
        return $user->hasRole('Administrador general');
    }
}
