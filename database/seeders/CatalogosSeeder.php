<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // Catálogo de Géneros
        DB::table('cat_genero')->insert([
            ['id' => 1, 'nombre' => 'Masculino'],
            ['id' => 2, 'nombre' => 'Femenino'],
            ['id' => 3, 'nombre' => 'Otro'],
        ]);

        // Catálogo de Estado Civil
        DB::table('cat_estado_civil')->insert([
            ['id' => 1, 'nombre' => 'Soltero(a)'],
            ['id' => 2, 'nombre' => 'Casado(a)'],
            ['id' => 3, 'nombre' => 'Divorciado(a)'],
            ['id' => 4, 'nombre' => 'Viudo(a)'],
            ['id' => 5, 'nombre' => 'Unión Libre'],
        ]);

        // Catálogo de Estatus
        DB::table('cat_estatus')->insert([
            ['id' => 1, 'nombre' => 'Activo'],
            ['id' => 2, 'nombre' => 'Inactivo'],
            ['id' => 3, 'nombre' => 'Egresado'],
            ['id' => 4, 'nombre' => 'Baja'],
        ]);

        // Carrera de ejemplo
        DB::table('carrera')->insert([
            'id' => 1,
            'nombre' => 'Ingeniería en Sistemas Computacionales',
            'nivel' => 'Licenciatura',
            'tipo_programa' => 'Escolarizado',
            'estatus' => 'A',
        ]);

        // Generación de ejemplo (necesito verificar estructura de esta tabla también)
        DB::table('generacion')->insert([
            'id' => 1,
            'nombre' => '2019-2023',
        ]);

        // Egresado de ejemplo
        $egresadoId = DB::table('egresado')->insertGetId([
            'matricula' => '19001234',
            'curp' => 'RAMC000101HTSLRS03',
            'nombre' => 'Juan Carlos',
            'apellidos' => 'Ramírez Martínez',
            'genero_id' => 1,
            'fecha_nacimiento' => '2000-01-01',
            'lugar_nacimiento' => 'Ciudad de México',
            'domicilio' => 'Calle Principal #123, Col. Centro',
            'domicilio_actual' => 'Calle Principal #123, Col. Centro',
            'email' => 'juan.ramirez@example.com',
            'estado_civil_id' => 1,
            'tiene_hijos' => false,
            'habla_lengua_indigena' => false,
            'habla_segundo_idioma' => true,
            'pertenece_grupo_etnico' => false,
            'estatus_id' => 3,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);

        // Relación Egresado-Carrera
        DB::table('egresado_carrera')->insert([
            'egresado_id' => $egresadoId,
            'carrera_id' => 1,
            'generacion_id' => 1,
            'fecha_ingreso' => '2019-08-15',
            'fecha_egreso' => '2023-07-20',
            'tipo_egreso' => 'T',
        ]);

        // Empleos de ejemplo
        DB::table('laboral')->insert([
            [
                'egresado_id' => $egresadoId,
                'empresa' => 'Tech Solutions SA',
                'puesto' => 'Desarrollador Full Stack',
                'sector' => 'Tecnología',
                'actualmente_activo' => true,
                'fecha_inicio' => '2023-08-01',
                'fecha_fin' => null,
                'creado_en' => now(),
            ],
            [
                'egresado_id' => $egresadoId,
                'empresa' => 'StartUp Innovadora',
                'puesto' => 'Desarrollador Junior',
                'sector' => 'Tecnología',
                'actualmente_activo' => false,
                'fecha_inicio' => '2023-01-15',
                'fecha_fin' => '2023-07-30',
                'creado_en' => now(),
            ],
        ]);
    }
}
