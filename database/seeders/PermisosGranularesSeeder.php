<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisosGranularesSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ENCUESTAS =====
        $encuestasPermisos = [
            'encuestas.ver',
            'encuestas.crear',
            'encuestas.editar',
            'encuestas.eliminar',
            'encuestas.asignar',
            'encuestas.ver_respuestas',
            'encuestas.exportar',
        ];

        // ===== PREGUNTAS Y DIMENSIONES =====
        $preguntasPermisos = [
            'preguntas.ver',
            'preguntas.crear',
            'preguntas.editar',
            'preguntas.eliminar',
            'dimensiones.ver',
            'dimensiones.crear',
            'dimensiones.editar',
            'dimensiones.eliminar',
        ];

        // ===== UNIDADES =====
        $unidadesPermisos = [
            'unidades.ver',
            'unidades.crear',
            'unidades.editar',
            'unidades.eliminar',
            'unidades.asignar_carreras',
        ];

        // ===== CARRERAS =====
        $carrerasPermisos = [
            'carreras.ver',
            'carreras.crear',
            'carreras.editar',
            'carreras.eliminar',
        ];

        // ===== GENERACIONES =====
        $generacionesPermisos = [
            'generaciones.ver',
            'generaciones.crear',
            'generaciones.editar',
            'generaciones.eliminar',
        ];

        // ===== NIVELES DE ESTUDIO =====
        $nivelesPermisos = [
            'niveles.ver',
            'niveles.crear',
            'niveles.editar',
            'niveles.eliminar',
        ];

        // ===== CICLOS ESCOLARES =====
        $ciclosPermisos = [
            'ciclos.ver',
            'ciclos.crear',
            'ciclos.editar',
            'ciclos.eliminar',
        ];

        // ===== USUARIOS =====
        $usuariosPermisos = [
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'usuarios.asignar_roles',
            'usuarios.cambiar_password',
        ];

        // ===== EGRESADOS =====
        $egresadosPermisos = [
            'egresados.ver',
            'egresados.crear',
            'egresados.editar',
            'egresados.eliminar',
            'egresados.ver_perfil',
            'egresados.editar_perfil',
            'egresados.asignar_carreras',
        ];

        // ===== REPORTES =====
        $reportesPermisos = [
            'reportes.ver',
            'reportes.exportar',
            'reportes.estadisticas',
        ];

        // ===== RESPALDOS =====
        $respaldosPermisos = [
            'respaldos.crear',
            'respaldos.descargar',
        ];

        // ===== RESPUESTAS DE ENCUESTAS =====
        $respuestasPermisos = [
            'respuestas.crear',
            'respuestas.ver',
            'respuestas.editar',
        ];

        // Crear todos los permisos
        $todosLosPermisos = array_merge(
            $encuestasPermisos,
            $preguntasPermisos,
            $unidadesPermisos,
            $carrerasPermisos,
            $generacionesPermisos,
            $nivelesPermisos,
            $ciclosPermisos,
            $usuariosPermisos,
            $egresadosPermisos,
            $reportesPermisos,
            $respaldosPermisos,
            $respuestasPermisos
        );

        foreach ($todosLosPermisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ===== ASIGNAR PERMISOS A ROLES =====

        // ADMINISTRADOR GENERAL: Todos los permisos
        $adminGeneral = Role::where('name', 'Administrador general')->first();
        if ($adminGeneral) {
            $adminGeneral->syncPermissions($todosLosPermisos);
        }

        // ADMINISTRADOR DE UNIDAD: Solo encuestas y preguntas
        $adminUnidad = Role::where('name', 'Administrador de unidad')->first();
        if ($adminUnidad) {
            $adminUnidad->syncPermissions(array_merge(
                $encuestasPermisos,
                $preguntasPermisos,
                ['reportes.ver', 'reportes.exportar'],
                ['respaldos.crear', 'respaldos.descargar']
            ));
        }

        // ADMINISTRADOR ACADÉMICO: Unidades, carreras, generaciones, niveles, ciclos
        $adminAcademico = Role::where('name', 'Administrador academico')->first();
        if ($adminAcademico) {
            $adminAcademico->syncPermissions(array_merge(
                $unidadesPermisos,
                $carrerasPermisos,
                $generacionesPermisos,
                $nivelesPermisos,
                $ciclosPermisos,
                ['reportes.ver', 'reportes.exportar'],
                ['respaldos.crear', 'respaldos.descargar']
            ));
        }

        // EGRESADOS: Solo ver su perfil
        $egresados = Role::where('name', 'Egresados')->first();
        if ($egresados) {
            $egresados->syncPermissions([
                'egresados.ver_perfil',
                'egresados.editar_perfil',
                'respuestas.crear',
                'respuestas.ver',
            ]);
        }

        // ESTUDIANTES: Solo ver su perfil
        $estudiantes = Role::where('name', 'Estudiantes')->first();
        if ($estudiantes) {
            $estudiantes->syncPermissions([
                'egresados.ver_perfil',
                'egresados.editar_perfil',
                'respuestas.crear',
                'respuestas.ver',
            ]);
        }

        $this->command->info('✓ Permisos granulares creados y asignados correctamente');
    }
}
