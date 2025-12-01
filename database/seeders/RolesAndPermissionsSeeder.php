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

        // Definir acciones y catálogos
        $acciones = ['crear', 'actualizar', 'eliminar', 'ver', 'ver_uno', 'restaurar', 'forzar_eliminacion'];
        $catalogos = ['egresado', 'encuesta', 'carrera', 'unidad'];

        // Crear permisos dinámicamente
        $permissions = [];
        foreach ($catalogos as $catalogo) {
            foreach ($acciones as $accion) {
                $permissions[] = "{$accion}_{$catalogo}";
            }
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles (solo administrativos)
        $roles = [
            'Administrador general',
            'Administrador de unidad',
            'Administrador academico',
            'Egresados',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Asignar todos los permisos al Administrador general
        $adminGeneral = Role::findByName('Administrador general');
        $adminGeneral->syncPermissions(Permission::all());

        // Asignar permisos a Administrador de unidad
        // Puede: gestionar encuestas
        $adminUnidad = Role::findByName('Administrador de unidad');
        $adminUnidad->syncPermissions([
            'ver_encuesta',
            'ver_uno_encuesta',
            'crear_encuesta',
            'actualizar_encuesta',
            'eliminar_encuesta',
        ]);

        // Asignar permisos a Administrador academico
        // Puede: gestionar unidades, carreras
        $adminAcademico = Role::findByName('Administrador academico');
        $adminAcademico->syncPermissions([
            'ver_unidad',
            'ver_uno_unidad',
            'crear_unidad',
            'actualizar_unidad',
            'eliminar_unidad',
            'ver_carrera',
            'ver_uno_carrera',
            'crear_carrera',
            'actualizar_carrera',
            'eliminar_carrera',
        ]);


    }
}
