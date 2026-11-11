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

        // Cruzamos la tabla y traemos a los de la fila + los que este doctor dejó a medias
        $pacientesEnEspera = DB::table('historial_triage')
            ->join('pacientes', 'historial_triage.id_paciente', '=', 'pacientes.id_paciente')
            ->where(function($query) use ($id_medico_actual) {
                $query->where('pacientes.estado_atencion', 'Clasificado') // Los que esperan
                      ->orWhere(function($q) use ($id_medico_actual) {
                          $q->where('pacientes.estado_atencion', 'En Consultorio')
                            ->where('historial_triage.id_medico', $id_medico_actual); // Los "atorados" de este doctor
                      });
            })
            // Ordenamos para que los "En Consultorio" aparezcan hasta arriba de su lista
            ->orderByRaw("FIELD(pacientes.estado_atencion, 'En Consultorio', 'Clasificado')") 
            ->orderByRaw("FIELD(color_calculado, 'Rojo', 'Naranja', 'Amarillo', 'Verde', 'Azul')")
            ->orderBy('historial_triage.fecha_clasificacion', 'asc')
            ->select('pacientes.*', 'historial_triage.color_calculado', 'historial_triage.criterio_aplicado', 'historial_triage.fecha_clasificacion', 'historial_triage.id_triage', 'historial_triage.id_medico')
            ->get();

        return view('medico.dashboard.index', compact('pacientesEnEspera'));
    }

    public function atender($id_triage)
    {
        // Cuando el médico atiende a un paciente, marcamos la hora de atención y sacamos al paciente de la fila
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
    // Mostrar la pantalla del consultorio con el expediente completo
    public function consultorio($id_triage)
    {
        // Cruzamos las 3 tablas para que el médico vea todo el panorama
        $expediente = DB::table('historial_triage')
            ->join('pacientes', 'historial_triage.id_paciente', '=', 'pacientes.id_paciente')
            ->join('signos_vitales', 'pacientes.id_paciente', '=', 'signos_vitales.id_paciente')
            ->where('historial_triage.id_triage', $id_triage)
            ->orderBy('signos_vitales.id_signos', 'desc') // Traer los signos más recientes
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

    // Guardar las notas y dar de alta al paciente
    public function darDeAlta(Request $request, $id_triage)
    {
        $request->validate([
            'notas_medicas' => 'required|string|min:10'
        ]);

        $triage = DB::table('historial_triage')->where('id_triage', $id_triage)->first();

        // Guardamos las notas y sellamos la hora de salida
        DB::table('historial_triage')->where('id_triage', $id_triage)->update([
            'notas_medicas' => $request->notas_medicas,
            'fecha_alta' => now()
        ]);

        // Cambiamos el estado del paciente para que desaparezca de las filas de espera
        DB::table('pacientes')->where('id_paciente', $triage->id_paciente)->update([
            'estado_atencion' => 'Alta Médica'
        ]);

        return redirect()->route('medico.dashboard')
            ->with('success', 'Paciente dado de alta correctamente. Expediente cerrado.');
    }
}