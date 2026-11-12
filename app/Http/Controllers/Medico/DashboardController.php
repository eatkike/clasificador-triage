<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $id_medico_actual = auth()->user()->id_usuario;

        $pacientesEspera = DB::table('historial_triage')
            ->join('pacientes', 'historial_triage.id_paciente', '=', 'pacientes.id_paciente')
            ->join('signos_vitales', 'pacientes.id_paciente', '=', 'signos_vitales.id_paciente') // Agregamos este join
            ->where(function($query) use ($id_medico_actual) {
                $query->where('pacientes.estado_atencion', 'Clasificado') // Los que esperan
                      ->orWhere(function($q) use ($id_medico_actual) {
                          $q->where('pacientes.estado_atencion', 'En Consultorio')
                            ->where('historial_triage.id_medico', $id_medico_actual); // Los "atorados"
                      });
            })
            ->select(
                'pacientes.*', 
                'historial_triage.id_triage', 
                'historial_triage.color_calculado', 
                'historial_triage.criterio_aplicado', 
                'historial_triage.fecha_clasificacion', 
                'historial_triage.id_medico',
                'signos_vitales.sintomas_cualitativos' // Extraemos los síntomas para la vista
            )
            // 1° Los pacientes "atorados" van hasta arriba
            ->orderByRaw("FIELD(pacientes.estado_atencion, 'En Consultorio', 'Clasificado')") 
            // 2° Orden estricto por gravedad NOM-027
            ->orderByRaw("FIELD(historial_triage.color_calculado, 'Rojo', 'Naranja', 'Amarillo', 'Verde', 'Azul')")
            // 3° Orden por tiempo de llegada (el más antiguo primero)
            ->orderBy('historial_triage.fecha_clasificacion', 'asc')
            ->get();

        return view('medico.dashboard.index', compact('pacientesEspera'));
    }

    public function atender($id_triage)
    {
        $triage = DB::table('historial_triage')->where('id_triage', $id_triage)->first();
        
        DB::table('historial_triage')->where('id_triage', $id_triage)->update([
            'id_medico' => auth()->user()->id_usuario,
            'fecha_atencion_medica' => now()
        ]);

        DB::table('pacientes')->where('id_paciente', $triage->id_paciente)->update([
            'estado_atencion' => 'En Consultorio'
        ]);

        return redirect()->route('medico.consultorio', $id_triage)->with('success', 'El paciente ha sido ingresado a su consultorio.');
    }

    public function consultorio($id_triage)
    {
        $expediente = DB::table('historial_triage')
            ->join('pacientes', 'historial_triage.id_paciente', '=', 'pacientes.id_paciente')
            ->join('signos_vitales', 'pacientes.id_paciente', '=', 'signos_vitales.id_paciente')
            ->where('historial_triage.id_triage', $id_triage)
            ->orderBy('signos_vitales.id_signos', 'desc')
            ->select(
                'pacientes.*', 
                'historial_triage.*', 
                'signos_vitales.presion_sistolica', 
                'signos_vitales.presion_diastolica', 
                'signos_vitales.frecuencia_cardiaca', 
                'signos_vitales.frecuencia_respiratoria', 
                'signos_vitales.temperatura', 
                'signos_vitales.saturacion_oxigeno', 
                'signos_vitales.sintomas_cualitativos'
            )
            ->first();

        if (!$expediente) {
            return redirect()->route('medico.dashboard')->withErrors(['msg' => 'Expediente no encontrado.']);
        }

        return view('medico.dashboard.consultorio', compact('expediente'));
    }

    public function darDeAlta(Request $request, $id_triage)
    {
        $request->validate([
            'notas_medicas' => 'required|string|min:10'
        ]);

        $triage = DB::table('historial_triage')->where('id_triage', $id_triage)->first();

        DB::table('historial_triage')->where('id_triage', $id_triage)->update([
            'notas_medicas' => $request->notas_medicas,
            'fecha_alta' => now()
        ]);

        DB::table('pacientes')->where('id_paciente', $triage->id_paciente)->update([
            'estado_atencion' => 'Alta Médica'
        ]);

        return redirect()->route('medico.dashboard')->with('success', 'Paciente dado de alta correctamente. Expediente cerrado.');
    }
}