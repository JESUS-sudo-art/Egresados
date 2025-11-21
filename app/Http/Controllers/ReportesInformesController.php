<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Generacion;
use App\Models\EncuestaLaboral;
use App\Models\Egresado;
use App\Models\EgresadoCarrera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportesInformesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Filtrar carreras y datos según el rol del usuario
        $carreras = $this->getCarrerasForUser($user);
        $generaciones = Generacion::orderBy('nombre')->get(['id','nombre']);

        return Inertia::render('modules/ReportesInformes', [
            'carreras' => $carreras,
            'generaciones' => $generaciones,
            'initial' => $this->buildStats(new Request()),
            'userRole' => $user->roles->first()->name ?? null,
            'publico' => false,
        ]);
    }

    /**
     * Vista pública para comunidad universitaria (sin autenticación)
     */
    public function publico()
    {
        // Para la vista pública mostramos todas las carreras y generaciones
        $carreras = Carrera::orderBy('nombre')->get(['id','nombre']);
        $generaciones = Generacion::orderBy('nombre')->get(['id','nombre']);

        return Inertia::render('modules/ReportesInformes', [
            'carreras' => $carreras,
            'generaciones' => $generaciones,
            'initial' => $this->buildStats(new Request()),
            'publico' => true,
        ]);
    }

    /**
     * Obtener carreras según el rol del usuario
     */
    private function getCarrerasForUser($user)
    {
        if (!$user) {
            // Público: ver todas
            return Carrera::orderBy('nombre')->get(['id','nombre']);
        }
        // Admin General: ve todo
        if ($user->hasRole('Administrador general')) {
            return Carrera::orderBy('nombre')->get(['id','nombre']);
        }

        // Admin Académico: ve todas las carreras de su ámbito
        if ($user->hasRole('Administrador academico')) {
            return Carrera::orderBy('nombre')->get(['id','nombre']);
        }

        // Admin Unidad: solo ve carreras de su unidad asignada
        if ($user->hasRole('Administrador de unidad')) {
            // Obtener las unidades asignadas al usuario
            $unidadIds = $user->unidades()->pluck('unidad.id');
            return Carrera::whereIn('unidad_id', $unidadIds)
                ->orderBy('nombre')
                ->get(['id','nombre']);
        }

        // Otros roles: ver todo por defecto
        return Carrera::orderBy('nombre')->get(['id','nombre']);
    }

    public function datos(Request $request)
    {
        return response()->json($this->buildStats($request));
    }

    public function exportar(Request $request)
    {
        $query = $this->baseQuery($request);
        $rows = $query->select([
            'encuesta_laboral.id',
            'egresado.nombre as egresado_nombre',
            'egresado.apellidos as egresado_apellidos',
            'encuesta_laboral.fecha_aplicacion',
            'encuesta_laboral.trabaja_actualmente',
            'encuesta_laboral.tiempo_primer_empleo',
            'encuesta_laboral.rango_salario',
            'encuesta_laboral.relacion_carrera',
            'encuesta_laboral.sector_empresa',
            'encuesta_laboral.calificacion_formacion',
        ])->orderByDesc('encuesta_laboral.id')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reporte_encuesta_laboral.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID', 'Nombre', 'Apellidos', 'Fecha Aplicación', 'Trabaja Actualmente', 'Tiempo Primer Empleo',
                'Rango Salario', 'Relación con Carrera', 'Sector', 'Calificación Formación',
            ]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->egresado_nombre,
                    $r->egresado_apellidos,
                    optional($r->fecha_aplicacion)->format('Y-m-d'),
                    $r->trabaja_actualmente ? 'Sí' : 'No',
                    $r->tiempo_primer_empleo,
                    $r->rango_salario,
                    $r->relacion_carrera ? 'Sí' : 'No',
                    $r->sector_empresa,
                    $r->calificacion_formacion,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildStats(Request $request)
    {
        $query = $this->baseQuery($request);

        $total = (clone $query)->count();

        $empleados = (clone $query)->where('encuesta_laboral.trabaja_actualmente', true)->count();
        $tasaEmpleabilidad = $total > 0 ? round(($empleados / $total) * 100, 1) : 0.0;

        // Salario promedio a partir del rango
        $salarioMap = [
            '$5,000-$10,000' => 7500,
            '$10,001-$15,000' => 12500,
            '$15,001-$20,000' => 17500,
            '$20,001-$30,000' => 25000,
            'Más de $30,000' => 35000,
        ];
        $salarios = (clone $query)->whereNotNull('encuesta_laboral.rango_salario')->pluck('encuesta_laboral.rango_salario');
        $vals = $salarios->map(fn ($s) => $salarioMap[$s] ?? null)->filter();
        $salarioPromedio = $vals->count() ? round($vals->avg(), 0) : 0;

        // Sector laboral (pastel)
        $sectorCounts = (clone $query)->select('encuesta_laboral.sector_empresa as sector', DB::raw('count(*) as total'))
            ->groupBy('encuesta_laboral.sector_empresa')->pluck('total', 'sector');
        $sectores = ['Público', 'Privado', 'Social', 'Otro'];
        $sectorData = [];
        foreach ($sectores as $s) { $sectorData[$s] = (int)($sectorCounts[$s] ?? 0); }

        // Tiempo para encontrar empleo (barras)
        $tiempos = [
            'Menos de 6 meses', '6-12 meses', '1-2 años', 'Más de 2 años', 'Trabajaba antes de egresar'
        ];
        $tiempoCounts = (clone $query)->select('encuesta_laboral.tiempo_primer_empleo as tiempo', DB::raw('count(*) as total'))
            ->groupBy('encuesta_laboral.tiempo_primer_empleo')->pluck('total','tiempo');
        $tiempoData = [];
        foreach ($tiempos as $t) { $tiempoData[$t] = (int)($tiempoCounts[$t] ?? 0); }

        // Relación empleo-carrera (barras)
        $relSi = (clone $query)->where('encuesta_laboral.relacion_carrera', true)->count();
        $relNo = (clone $query)->where('encuesta_laboral.relacion_carrera', false)->count();

        // Satisfacción con la formación (usamos calificacion_formacion: 1..5)
        $satisf = (clone $query)->select('encuesta_laboral.calificacion_formacion as cal', DB::raw('count(*) as total'))
            ->groupBy('encuesta_laboral.calificacion_formacion')->pluck('total','cal');
        $satisfData = [];
        for ($i=1; $i<=5; $i++) { $satisfData[$i] = (int)($satisf[$i] ?? 0); }

        return [
            'totalEncuestados' => $total,
            'tasaEmpleabilidad' => $tasaEmpleabilidad,
            'salarioPromedio' => $salarioPromedio,
            'sectorData' => $sectorData,
            'tiempoData' => $tiempoData,
            'relacionData' => ['Si' => $relSi, 'No' => $relNo],
            'satisfaccionData' => $satisfData,
        ];
    }

    private function baseQuery(Request $request)
    {
        $user = auth()->user();
        
        $q = EncuestaLaboral::query()
            ->join('egresado', 'encuesta_laboral.egresado_id', '=', 'egresado.id')
            ->leftJoin('egresado_carrera', 'egresado_carrera.egresado_id', '=', 'egresado.id');

        // Filtrar por unidad si es Admin de Unidad
        if ($user && $user->hasRole('Administrador de unidad')) {
            $unidadIds = $user->unidades()->pluck('unidad.id');
            $q->join('carrera', 'egresado_carrera.carrera_id', '=', 'carrera.id')
              ->whereIn('carrera.unidad_id', $unidadIds);
        }

        if ($request->filled('carrera_id')) {
            $q->where('egresado_carrera.carrera_id', $request->integer('carrera_id'));
        }
        if ($request->filled('generacion_id')) {
            $q->where('egresado_carrera.generacion_id', $request->integer('generacion_id'));
        }
        if ($request->filled('desde')) {
            $q->whereDate('encuesta_laboral.fecha_aplicacion', '>=', $request->date('desde'));
        }
        if ($request->filled('hasta')) {
            $q->whereDate('encuesta_laboral.fecha_aplicacion', '<=', $request->date('hasta'));
        }
        return $q;
    }
}
