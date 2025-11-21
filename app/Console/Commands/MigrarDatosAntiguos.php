<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Generacion;
use App\Models\Ciclo;
use App\Models\CatEstatus;
use App\Models\CatEstadoCivil;
use App\Models\CatGenero;
use App\Models\TipoPregunta;
use App\Models\Unidad;
use App\Models\Carrera;
use App\Models\UnidadCarrera;
use App\Models\Egresado;
use App\Models\EgresadoCarrera;
use App\Models\Encuesta;
use App\Models\Dimension;
use App\Models\Pregunta;
use App\Models\Opcion;
use App\Models\Respuesta;
use App\Models\Laboral;

class MigrarDatosAntiguos extends Command
{
    protected $signature = 'migrar:datos-antiguos 
                            {--tabla= : Migrar solo una tabla específica}
                            {--dry-run : Ejecutar en modo prueba sin guardar cambios}
                            {--limpiar : Limpiar datos existentes antes de migrar}';

    protected $description = 'Migra datos de la base de datos antigua (bdwvexa) a la nueva estructura';

    private $antiguaDB = 'bdwvexa'; // Nombre de tu BD antigua
    private $isDryRun = false;
    private $contadores = [];

    public function handle()
    {
        $this->isDryRun = $this->option('dry-run');
        $tabla = $this->option('tabla');

        $this->info('🚀 MIGRACIÓN DE DATOS DE BASE DE DATOS ANTIGUA');
        $this->info('==============================================');
        $this->newLine();

        if ($this->isDryRun) {
            $this->warn('🧪 MODO DRY-RUN: No se guardarán cambios en la base de datos');
            $this->newLine();
        }

        // Verificar conexión a BD antigua
        if (!$this->verificarConexionAntiguaDB()) {
            return Command::FAILURE;
        }

        // Limpiar datos si se solicita
        if ($this->option('limpiar') && !$this->isDryRun) {
            if ($this->confirm('⚠️  ¿Estás seguro de limpiar los datos existentes?', false)) {
                $this->limpiarDatos();
            }
        }

        try {
            if ($tabla) {
                $this->migrarTablaEspecifica($tabla);
            } else {
                $this->migrarTodo();
            }

            $this->mostrarResumen();
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error en la migración: {$e->getMessage()}");
            $this->error("   Línea: {$e->getLine()}");
            $this->error("   Archivo: {$e->getFile()}");
            return Command::FAILURE;
        }
    }

    private function verificarConexionAntiguaDB(): bool
    {
        try {
            DB::connection($this->antiguaDB)->getPdo();
            $this->info("✅ Conexión exitosa a la base de datos antigua: {$this->antiguaDB}");
            $this->newLine();
            return true;
        } catch (\Exception $e) {
            $this->error("❌ No se pudo conectar a la base de datos antigua: {$this->antiguaDB}");
            $this->error("   Configura la conexión en config/database.php");
            $this->error("   Error: {$e->getMessage()}");
            return false;
        }
    }

    private function migrarTodo()
    {
        $this->info('📋 Iniciando migración completa...');
        $this->newLine();

        $pasos = [
            '1. Catálogos básicos' => 'migrarCatalogos',
            '2. Generaciones' => 'migrarGeneraciones',
            '3. Ciclos' => 'migrarCiclos',
            '4. Unidades (Escuelas)' => 'migrarUnidades',
            '5. Carreras' => 'migrarCarreras',
            '6. Egresados' => 'migrarEgresados',
            '7. Encuestas' => 'migrarEncuestas',
            '8. Dimensiones' => 'migrarDimensiones',
            '9. Preguntas' => 'migrarPreguntas',
            '10. Opciones' => 'migrarOpciones',
            '11. Respuestas' => 'migrarRespuestas',
            '12. Datos Laborales' => 'migrarLaborales',
        ];

        foreach ($pasos as $descripcion => $metodo) {
            $this->info("▶️  {$descripcion}");
            $this->$metodo();
            $this->newLine();
        }
    }

    private function migrarTablaEspecifica($tabla)
    {
        $metodos = [
            'catalogos' => 'migrarCatalogos',
            'generaciones' => 'migrarGeneraciones',
            'ciclos' => 'migrarCiclos',
            'unidades' => 'migrarUnidades',
            'carreras' => 'migrarCarreras',
            'egresados' => 'migrarEgresados',
            'encuestas' => 'migrarEncuestas',
            'dimensiones' => 'migrarDimensiones',
            'preguntas' => 'migrarPreguntas',
            'opciones' => 'migrarOpciones',
            'respuestas' => 'migrarRespuestas',
            'laborales' => 'migrarLaborales',
        ];

        if (isset($metodos[$tabla])) {
            $this->info("▶️  Migrando: {$tabla}");
            $this->{$metodos[$tabla]}();
        } else {
            $this->error("❌ Tabla '{$tabla}' no reconocida");
            $this->info("Tablas disponibles: " . implode(', ', array_keys($metodos)));
        }
    }

    // ==================== MÉTODOS DE MIGRACIÓN ====================

    private function migrarCatalogos()
    {
        $this->line('   → Migrando catálogos básicos...');
        
        // Cat Estatus
        if (!$this->isDryRun) {
            CatEstatus::firstOrCreate(['id' => 1], ['nombre' => 'Activo', 'descripcion' => 'Activo']);
            CatEstatus::firstOrCreate(['id' => 2], ['nombre' => 'Inactivo', 'descripcion' => 'Inactivo']);
            $this->contadores['cat_estatus'] = 2;
        }

        // Cat Género (mapear char a catálogo)
        if (!$this->isDryRun) {
            CatGenero::firstOrCreate(['id' => 1], ['nombre' => 'Masculino', 'abreviatura' => 'M']);
            CatGenero::firstOrCreate(['id' => 2], ['nombre' => 'Femenino', 'abreviatura' => 'F']);
            CatGenero::firstOrCreate(['id' => 3], ['nombre' => 'Otro', 'abreviatura' => 'O']);
            $this->contadores['cat_genero'] = 3;
        }

        // Cat Estado Civil
        if (!$this->isDryRun) {
            CatEstadoCivil::firstOrCreate(['id' => 1], ['nombre' => 'Soltero(a)']);
            CatEstadoCivil::firstOrCreate(['id' => 2], ['nombre' => 'Casado(a)']);
            CatEstadoCivil::firstOrCreate(['id' => 3], ['nombre' => 'Divorciado(a)']);
            CatEstadoCivil::firstOrCreate(['id' => 4], ['nombre' => 'Viudo(a)']);
            CatEstadoCivil::firstOrCreate(['id' => 5], ['nombre' => 'Unión Libre']);
            $this->contadores['cat_estado_civil'] = 5;
        }

        // Tipos de Pregunta (desde tabla 'tipos')
        $tipos = DB::connection($this->antiguaDB)
            ->table('tipos')
            ->where('estatus', 'A')
            ->get();

        $count = 0;
        foreach ($tipos as $tipo) {
            if (!$this->isDryRun) {
                TipoPregunta::updateOrCreate(
                    ['id' => $tipo->id],
                    ['nombre' => $tipo->descripcion]
                );
            }
            $count++;
        }
        $this->contadores['tipo_pregunta'] = $count;

        $this->info("   ✓ Catálogos migrados");
    }

    private function migrarGeneraciones()
    {
        $generaciones = DB::connection($this->antiguaDB)
            ->table('generaciones')
            ->where('estatus', 'A')
            ->get();

        $count = 0;
        foreach ($generaciones as $gen) {
            if (!$this->isDryRun) {
                Generacion::updateOrCreate(
                    ['id' => $gen->id],
                    [
                        'nombre' => $gen->generacion,
                        'estatus' => $gen->estatus
                    ]
                );
            }
            $count++;
        }

        $this->contadores['generaciones'] = $count;
        $this->info("   ✓ {$count} generaciones migradas");
    }

    private function migrarCiclos()
    {
        $ciclos = DB::connection($this->antiguaDB)
            ->table('ciclos')
            ->get();

        $count = 0;
        foreach ($ciclos as $ciclo) {
            if (!$this->isDryRun) {
                Ciclo::updateOrCreate(
                    ['id' => $ciclo->id],
                    [
                        'nombre' => $ciclo->nombre,
                        'observaciones' => $ciclo->observaciones ?? '',
                        'estatus' => $ciclo->estatus
                    ]
                );
            }
            $count++;
        }

        $this->contadores['ciclos'] = $count;
        $this->info("   ✓ {$count} ciclos migrados");
    }

    private function migrarUnidades()
    {
        $escuelas = DB::connection($this->antiguaDB)
            ->table('escuelas')
            ->where('estatus', 'A')
            ->get();

        $count = 0;
        foreach ($escuelas as $escuela) {
            if (!$this->isDryRun) {
                Unidad::updateOrCreate(
                    ['id' => $escuela->id],
                    [
                        'nombre' => $escuela->nombre,
                        'nombre_corto' => $escuela->nomcto,
                        'domicilio' => $escuela->domicilio,
                        'sitio_web' => $escuela->web,
                        'email' => $escuela->email,
                        'estatus' => $escuela->estatus
                    ]
                );
            }
            $count++;
        }

        $this->contadores['unidades'] = $count;
        $this->info("   ✓ {$count} unidades (escuelas) migradas");
    }

    private function migrarCarreras()
    {
        $this->line('   → Migrando carreras...');
        
        // Migrar carreras
        $carreras = DB::connection($this->antiguaDB)
            ->table('carreras')
            ->where('estatus', 'A')
            ->get();

        $countCarreras = 0;
        foreach ($carreras as $carrera) {
            if (!$this->isDryRun) {
                Carrera::updateOrCreate(
                    ['id' => $carrera->id],
                    [
                        'nombre' => $carrera->nombre,
                        'nombre_corto' => $carrera->nomcto,
                        'estatus' => $carrera->estatus
                    ]
                );
            }
            $countCarreras++;
        }

        // Migrar relación escuelas-carreras
        $escucarreras = DB::connection($this->antiguaDB)
            ->table('escucarreras')
            ->where('estatus', 'A')
            ->get();

        $countRelaciones = 0;
        foreach ($escucarreras as $rel) {
            if (!$this->isDryRun) {
                UnidadCarrera::updateOrCreate(
                    [
                        'unidad_id' => $rel->escuelas_id,
                        'carrera_id' => $rel->carreras_id
                    ],
                    ['estatus' => $rel->estatus]
                );
            }
            $countRelaciones++;
        }

        $this->contadores['carreras'] = $countCarreras;
        $this->contadores['unidad_carrera'] = $countRelaciones;
        $this->info("   ✓ {$countCarreras} carreras y {$countRelaciones} relaciones migradas");
    }

    private function migrarEgresados()
    {
        $this->line('   → Migrando egresados (esto puede tardar)...');
        
        $egresados = DB::connection($this->antiguaDB)
            ->table('egresados')
            ->where('estatus', 'A')
            ->get();

        $count = 0;
        $bar = $this->output->createProgressBar($egresados->count());
        $bar->start();

        foreach ($egresados as $old) {
            if (!$this->isDryRun) {
                // Mapear género
                $generoId = match(strtoupper($old->genero)) {
                    'M', 'H' => 1,
                    'F', 'M' => 2,
                    default => 3
                };

                // Mapear estado civil
                $estadoCivilId = match(strtoupper($old->edocivil ?? 'S')) {
                    'S' => 1, // Soltero
                    'C' => 2, // Casado
                    'D' => 3, // Divorciado
                    'V' => 4, // Viudo
                    'U' => 5, // Unión Libre
                    default => 1
                };

                // Crear/actualizar egresado
                $egresado = Egresado::updateOrCreate(
                    ['id' => $old->id],
                    [
                        'matricula' => $old->matricula,
                        'nombre' => $old->nombre,
                        'apellidos' => $old->apellidos ?? '',
                        'genero_id' => $generoId,
                        'fecha_nacimiento' => $old->fecnac,
                        'lugar_nacimiento' => $old->lugarnac,
                        'domicilio' => $old->domicilio,
                        'email' => $old->email,
                        'estado_civil_id' => $estadoCivilId,
                        'token' => $old->token,
                        'estatus_id' => $old->estatus == 'A' ? 1 : 2,
                        'validado_sice' => $old->activo == 'A' ? 1 : 0,
                    ]
                );

                // Crear usuario si no existe
                $user = User::firstOrCreate(
                    ['email' => $old->email],
                    [
                        'name' => trim($old->nombre . ' ' . ($old->apellidos ?? '')),
                        'password' => Hash::make($old->clave ?? 'password'),
                        'email_verified_at' => $old->ultimoingreso ?? now(),
                    ]
                );

                // Asignar rol según validación
                if ($old->activo == 'A') {
                    $user->syncRoles(['Egresados']);
                } else {
                    $user->syncRoles(['Estudiantes']);
                }

                // Crear relación egresado-carrera
                if ($old->carreras_id && $old->generaciones_id) {
                    EgresadoCarrera::updateOrCreate(
                        [
                            'egresado_id' => $egresado->id,
                            'carrera_id' => $old->carreras_id,
                            'generacion_id' => $old->generaciones_id
                        ],
                        [
                            'fecha_ingreso' => $old->fechaingreso,
                            'estatus' => 'A'
                        ]
                    );
                }
            }
            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->contadores['egresados'] = $count;
        $this->info("   ✓ {$count} egresados migrados");
    }

    private function migrarEncuestas()
    {
        $this->line('   → Migrando encuestas...');
        
        $encuestas = DB::connection($this->antiguaDB)
            ->table('encuestas')
            ->get();

        $count = 0;
        foreach ($encuestas as $enc) {
            // Obtener dirigida
            $dirigida = DB::connection($this->antiguaDB)
                ->table('dirigidas')
                ->where('id', $enc->dirigidas_id)
                ->first();

            if (!$this->isDryRun) {
                Encuesta::updateOrCreate(
                    ['id' => $enc->id],
                    [
                        'ciclo_id' => $enc->ciclos_id,
                        'nombre' => $enc->nombre,
                        'tipo_cuestionario' => $dirigida->descripcion ?? 'General',
                        'fecha_inicio' => $enc->fecini,
                        'fecha_fin' => $enc->fecfin,
                        'descripcion' => $enc->descripcion,
                        'instrucciones' => $enc->instrucciones,
                        'estatus' => $enc->estatus
                    ]
                );
            }
            $count++;
        }

        $this->contadores['encuestas'] = $count;
        $this->info("   ✓ {$count} encuestas migradas");
    }

    private function migrarDimensiones()
    {
        $this->line('   → Migrando dimensiones...');
        
        $dimensiones = DB::connection($this->antiguaDB)
            ->table('dimensiones')
            ->get();

        $count = 0;
        foreach ($dimensiones as $dim) {
            if (!$this->isDryRun) {
                Dimension::updateOrCreate(
                    ['id' => $dim->id],
                    [
                        'encuesta_id' => $dim->encuestas_id,
                        'nombre' => $dim->nombre,
                        'descripcion' => $dim->descripcion,
                        'orden' => $dim->orden
                    ]
                );
            }
            $count++;
        }

        $this->contadores['dimensiones'] = $count;
        $this->info("   ✓ {$count} dimensiones migradas");
    }

    private function migrarPreguntas()
    {
        $this->line('   → Migrando preguntas...');
        
        $preguntas = DB::connection($this->antiguaDB)
            ->table('preguntas')
            ->get();

        $count = 0;
        foreach ($preguntas as $preg) {
            if (!$this->isDryRun) {
                Pregunta::updateOrCreate(
                    ['id' => $preg->id],
                    [
                        'encuesta_id' => $preg->encuestas_id,
                        'texto' => $preg->pregunta,
                        'dimension_id' => $preg->dimensiones_id,
                        'tipo_pregunta_id' => $preg->tipos_id,
                        'orden' => $preg->orden,
                        'tamanio' => $preg->tamanio,
                        'presentacion' => $preg->presentacion,
                        'orientacion' => $preg->orientacion,
                        'tips' => $preg->tips,
                        'instruccion' => $preg->instruccion
                    ]
                );
            }
            $count++;
        }

        $this->contadores['preguntas'] = $count;
        $this->info("   ✓ {$count} preguntas migradas");
    }

    private function migrarOpciones()
    {
        $this->line('   → Migrando opciones...');
        
        $opciones = DB::connection($this->antiguaDB)
            ->table('opciones')
            ->get();

        $count = 0;
        foreach ($opciones as $opc) {
            if (!$this->isDryRun) {
                Opcion::updateOrCreate(
                    ['id' => $opc->id],
                    [
                        'pregunta_id' => $opc->preguntas_id,
                        'texto' => $opc->opcion,
                        'valor' => $opc->valor,
                        'orden' => $opc->orden
                    ]
                );
            }
            $count++;
        }

        $this->contadores['opciones'] = $count;
        $this->info("   ✓ {$count} opciones migradas");
    }

    private function migrarRespuestas()
    {
        $this->line('   → Migrando respuestas (unificando int y txt)...');
        $this->warn('   ⚠️  Esta es la parte más compleja, puede tardar varios minutos...');
        
        // Migrar respuestas de tipo entero/opción
        $intRespuestas = DB::connection($this->antiguaDB)
            ->table('intrespuestas')
            ->get();

        $countInt = 0;
        $bar = $this->output->createProgressBar($intRespuestas->count());
        $bar->start();

        foreach ($intRespuestas as $resp) {
            if (!$this->isDryRun) {
                // Buscar la opción correspondiente por valor
                $opcion = Opcion::where('pregunta_id', $resp->preguntas_id)
                    ->where('valor', $resp->respuesta)
                    ->first();

                if ($opcion) {
                    Respuesta::create([
                        'pregunta_id' => $resp->preguntas_id,
                        'opcion_id' => $opcion->id,
                        'egresado_id' => $resp->bitencuestas_id, // Nota: Necesita mapeo más complejo
                        'texto' => null
                    ]);
                }
            }
            $countInt++;
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        // Migrar respuestas de tipo texto
        $txtRespuestas = DB::connection($this->antiguaDB)
            ->table('txtrespuestas')
            ->get();

        $countTxt = 0;
        $bar = $this->output->createProgressBar($txtRespuestas->count());
        $bar->start();

        foreach ($txtRespuestas as $resp) {
            if (!$this->isDryRun) {
                Respuesta::create([
                    'pregunta_id' => $resp->preguntas_id,
                    'opcion_id' => null,
                    'egresado_id' => $resp->bitencuestas_id, // Nota: Necesita mapeo más complejo
                    'texto' => $resp->respuesta
                ]);
            }
            $countTxt++;
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->contadores['respuestas_int'] = $countInt;
        $this->contadores['respuestas_txt'] = $countTxt;
        $this->info("   ✓ {$countInt} respuestas de opción + {$countTxt} respuestas de texto migradas");
    }

    private function migrarLaborales()
    {
        $this->line('   → Migrando datos laborales...');
        
        $laborales = DB::connection($this->antiguaDB)
            ->table('laborales')
            ->get();

        $count = 0;
        foreach ($laborales as $lab) {
            if (!$this->isDryRun) {
                Laboral::create([
                    'egresado_id' => $lab->egresados_id,
                    'empresa' => $lab->empresa,
                    'puesto' => $lab->puesto,
                    'fecha_inicio' => "{$lab->anioinicio}-01-01",
                    'fecha_fin' => $lab->aniofin > 0 ? "{$lab->aniofin}-12-31" : null,
                    'actualmente_trabaja' => $lab->aniofin == 0 ? 1 : 0
                ]);
            }
            $count++;
        }

        $this->contadores['laborales'] = $count;
        $this->info("   ✓ {$count} registros laborales migrados");
    }

    private function limpiarDatos()
    {
        $this->warn('🗑️  Limpiando datos existentes...');
        
        $tablas = [
            'respuesta', 'opcion', 'pregunta', 'dimension', 
            'encuesta_asignada', 'encuesta', 'laboral', 
            'egresado_carrera', 'egresado', 'unidad_carrera', 
            'carrera', 'unidad', 'ciclo', 'generacion'
        ];

        foreach ($tablas as $tabla) {
            DB::table($tabla)->truncate();
            $this->line("   ✓ {$tabla}");
        }
        
        $this->info('✅ Datos limpiados');
        $this->newLine();
    }

    private function mostrarResumen()
    {
        $this->newLine();
        $this->info('📊 RESUMEN DE MIGRACIÓN');
        $this->info('=======================');
        
        $tableData = [];
        foreach ($this->contadores as $tabla => $count) {
            $tableData[] = [ucfirst(str_replace('_', ' ', $tabla)), $count];
        }

        $this->table(['Tabla', 'Registros Migrados'], $tableData);
        
        if ($this->isDryRun) {
            $this->warn('🧪 Esto fue una simulación. Ejecuta sin --dry-run para guardar los cambios.');
        } else {
            $this->info('✅ Migración completada exitosamente');
        }
    }
}
