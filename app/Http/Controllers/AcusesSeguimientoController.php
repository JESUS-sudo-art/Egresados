<?php

namespace App\Http\Controllers;

use App\Models\Egresado;
use App\Models\CedulaPreegreso;
use App\Models\EncuestaLaboral;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class AcusesSeguimientoController extends Controller
{
    public function index()
    {
        $egresado = Egresado::with(['carreras.carrera'])
            ->where('email', auth()->user()->email)
            ->first();
        
        $encuestas = [];
        
        if ($egresado) {
            // Obtener Cédulas de Pre-Egreso
            $cedulasPreegreso = CedulaPreegreso::where('egresado_id', $egresado->id)
                ->orderBy('fecha_aplicacion', 'desc')
                ->get();
            
            foreach ($cedulasPreegreso as $cedula) {
                $encuestas[] = [
                    'id' => $cedula->id,
                    'tipo' => 'preegreso',
                    'nombre' => 'Cédula de Pre-Egreso',
                    'fecha' => $cedula->fecha_aplicacion->format('d/M/Y'),
                    'folio' => 'F-PRE-' . $cedula->id . '-' . $egresado->id,
                ];
            }
            
            // Obtener Encuestas Laborales
            $encuestasLaborales = EncuestaLaboral::where('egresado_id', $egresado->id)
                ->orderBy('fecha_aplicacion', 'desc')
                ->get();
            
            foreach ($encuestasLaborales as $encuesta) {
                $encuestas[] = [
                    'id' => $encuesta->id,
                    'tipo' => 'laboral',
                    'nombre' => 'Cuestionario de Seguimiento',
                    'fecha' => $encuesta->fecha_aplicacion->format('d/M/Y'),
                    'folio' => 'F-LAB-' . $encuesta->id . '-' . $egresado->id,
                ];
            }
        }
        
        // Ordenar por fecha descendente
        usort($encuestas, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
        
        return Inertia::render('modules/AcusesSeguimiento', [
            'egresado' => $egresado,
            'encuestas' => $encuestas,
        ]);
    }
    
    public function descargarAcuse($tipo, $id)
    {
        $egresado = Egresado::with(['carreras.carrera.unidades'])
            ->where('email', auth()->user()->email)
            ->first();
        
        if (!$egresado) {
            return redirect()->back()->withErrors(['error' => 'No se encontró el egresado']);
        }
        
        $encuesta = null;
        $nombreEncuesta = '';
        $folio = '';
        $fecha = '';
        
        if ($tipo === 'preegreso') {
            $encuesta = CedulaPreegreso::where('id', $id)
                ->where('egresado_id', $egresado->id)
                ->first();
            $nombreEncuesta = 'CÉDULA DE PRE-EGRESO';
            if ($encuesta) {
                $folio = 'F-PRE-' . $encuesta->id . '-' . $egresado->id;
                $fecha = $encuesta->fecha_aplicacion->format('d/M/Y');
            }
        } elseif ($tipo === 'laboral') {
            $encuesta = EncuestaLaboral::where('id', $id)
                ->where('egresado_id', $egresado->id)
                ->first();
            $nombreEncuesta = 'CUESTIONARIO DE SEGUIMIENTO';
            if ($encuesta) {
                $folio = 'F-LAB-' . $encuesta->id . '-' . $egresado->id;
                $fecha = $encuesta->fecha_aplicacion->format('d/M/Y');
            }
        }
        
        if (!$encuesta) {
            return redirect()->back()->withErrors(['error' => 'No se encontró la encuesta']);
        }
        
        $egresadoCarrera = $egresado->carreras->first();
        $carrera = $egresadoCarrera?->carrera;
        $unidad = $carrera?->unidades->first();
        $anioEgreso = $egresadoCarrera?->fecha_egreso ? $egresadoCarrera->fecha_egreso->format('Y') : 'N/A';
        
        $data = [
            'folio' => $folio,
            'fecha' => $fecha,
            'nombre' => $egresado->nombre . ' ' . $egresado->apellidos,
            'matricula' => $egresado->matricula ?? 'N/A',
            'facultad' => $unidad?->nombre ?? 'N/A',
            'licenciatura' => $carrera?->nombre ?? 'N/A',
            'anioEgreso' => $anioEgreso,
            'nombreEncuesta' => $nombreEncuesta,
        ];
        
        $pdf = Pdf::loadView('pdf.acuse', $data);
        
        return $pdf->download('Acuse-' . $folio . '.pdf');
    }
}
