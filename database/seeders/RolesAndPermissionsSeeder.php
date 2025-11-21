<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            'ver',
            'ver_uno',
            'crear',
            'actualizar',
            'eliminar',
            'restaurar',
            'forzar_eliminacion',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $roles = [
            'Estudiantes',
            'Egresados',
            'Administrador general',
            'Administrador de unidad',
            'Administrador academico',
            'Comunidad universitaria',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Asignar todos los permisos al Administrador general
        // Puede: gestionar usuarios, roles, validar egresados SICE, acceso completo
        $adminGeneral = Role::findByName('Administrador general');
        $adminGeneral->syncPermissions(Permission::all());

        // Asignar permisos a Administrador de unidad
        // Puede: generar reportes, respaldar BD, gestionar encuestas de su unidad
        $adminUnidad = Role::findByName('Administrador de unidad');
        $adminUnidad->syncPermissions(['ver', 'ver_uno', 'crear', 'actualizar', 'eliminar']);

        // Asignar permisos a Administrador academico
        // Puede: gestionar unidades, carreras, generaciones
        $adminAcademico = Role::findByName('Administrador academico');
        $adminAcademico->syncPermissions(['ver', 'ver_uno', 'crear', 'actualizar', 'eliminar']);

        // Asignar permisos a Egresados
        // Pueden: registrarse, actualizar datos, responder encuestas, consultar encuestas previas
        $egresados = Role::findByName('Egresados');
        $egresados->syncPermissions(['ver', 'ver_uno', 'actualizar']);

        // Asignar permisos a Estudiantes
        // Pueden: registrarse, actualizar información académica
        $estudiantes = Role::findByName('Estudiantes');
        $estudiantes->syncPermissions(['ver', 'ver_uno', 'actualizar']);

        // Asignar permisos a Comunidad universitaria
        // Pueden: visualizar información de seguimiento de egresados, consultar reportes
        $comunidad = Role::findByName('Comunidad universitaria');
        $comunidad->syncPermissions(['ver']);
    }
}
